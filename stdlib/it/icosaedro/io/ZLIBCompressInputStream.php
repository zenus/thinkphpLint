<?php
/*. require_module 'hash'; .*/

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";
use ErrorException;


/**
 * Input stream filter that decompresses using the ZLIB algorithm (RFC 1950).
 * There is no guarantee the data read are correct until the stream gets closed:
 * in fact, only at that moment the final hash is read from the stream and
 * verified.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:05:44 $
 */
class ZLIBCompressInputStream extends DeflateInputStream {
	
	/**
	 * We must keep the last 4 bytes of the input stream with the Adler-32 hash
	 * to be checked later, on close().
	 * @var KeepTailInputStream
	 */
	private $in;
	
	/**
	 * Adler-32 hash context.
	 * @var resource
	 */
	private $adler32;

	/**
	 * Creates a new ZLIB Compressed input stream decompressor.
	 * @param InputStream $in
	 * @throws IOException
	 * @throws CorruptedException
	 */
	public function __construct($in)
	{
		$header = $in->readBytes(2);
		if( strlen($header) != 2 )
			throw new CorruptedException("premature end");
		// FIXME: parse and check $header
		$this->adler32 = hash_init("adler32");
		$this->in = new KeepTailInputStream($in, 4);
		parent::__construct($this->in);
	}


	/**
	 * Reads one byte.
	 * @return int Byte read in [0,255], or -1 on end of file.
	 * @throws IOException
	 */
	public function readByte()
	{
		$b = parent::readByte();
		if( $b >= 0 )
			hash_update($this->adler32, chr($b));
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
	public function readBytes($n)
	{
		if( $n <= 0 )
			return "";
		$bytes = parent::readBytes($n);
		if( $bytes === NULL )
			return NULL;
		hash_update($this->adler32, $bytes);
		return $bytes;
	}


	/**
	 * Closes the file and checks the final Adler-32 hash.
	 * Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 * @throws CorruptedException
	 */
	public function close()
	{
		if( $this->in === NULL )
			return;
		// Skip to the end, just in case the client stopped to read:
		while( $this->readBytes(512) !== NULL ) ;
		$tail = $this->in->getTail();
		if( strlen($tail) != 4 )
			throw new CorruptedException("premature end");
		
		// Note that both the hash_final() result and the tail's hash
		// are in big-endian order:
		$adler32 = hash_final($this->adler32, TRUE);
		if( $adler32 !== $tail )
			throw new CorruptedException("invalid hash");
		
		parent::close();
		$this->in = NULL;
	}

}
