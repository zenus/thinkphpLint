<?php
/*.
	require_module 'streams';
	require_module 'hash';
.*/

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use ErrorException;


/**
 * Input stream filter that decompresses using the BZIP2 algorithm.
 * @deprecated Unfinished class, does not check CRC, unsafe.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 22:13:13 $
 */
class BZip2InputStream extends ResourceInputStream {

	/**
	 * @var resource
	 */
	private $crc;

	/**
	 * Creates a new BZIP2 decompressor stream reader.
	 * @param InputStream $in Compressed input stream to read.
	 * @throws IOException
	 */
	public function __construct($in)
	{
		try {
			$stream = InputStreamAsResource::get($in);
			stream_filter_append($stream, 'bzip2.decompress', STREAM_FILTER_READ);
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage(), 0, $e);
		}
		$this->crc = hash_init("crc32b");
		parent::__construct($stream);
	}
	
	
	/**
	 * Reads one byte.
	 * @return int Byte read in [0,255], or -1 on end of file.
	 * @throws IOException
	 */
	function readByte()
	{
		$b = parent::readByte();
		if( $b >= 0 )
			hash_update($this->crc, chr($b));
		return $b;
	}


	/**
	 * Reads bytes.
	 * @param int $n Maximum number of bytes to read.
	 * @return string Bytes read, possibly in a number less than requested,
	 * either because the end of the file has been reached, or the input
	 * buffer is short but still data are available. If $n &le; 0 does nothing
	 * and the empty string is returned. If $n &gt; 0 and the returned string
	 * is NULL, the end of the file is reached.
	 * @throws IOException
	 */
	function readBytes($n)
	{
		$bytes = parent::readBytes($n);
		if( strlen($bytes) > 0 )
			hash_update($this->crc, $bytes);
		return $bytes;
	}


	/**
	 * Closes the stream. Does nothing if the stream has been already closed.
	 * @return void
	 * @throws IOException
	 */
	function close()
	{
		if( $this->crc === NULL )
			return;
		parent::close();
		$crc = hash_final($this->crc, TRUE);
		// FIXME: check computed CRC vs. read CRC.
//		echo "[CRC ", rawurlencode($crc), "]";
		$this->crc = NULL;
	}

}
