<?php
/*. require_module 'streams'; .*/

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use ErrorException;
use InvalidArgumentException;


/**
 * Output stream filter that compresses in the ZLIB format (RFC 1950).
 * The resulting stream contains an header (2 bytes), a compressed content using
 * the DEFLATE algorithm (RFC 1950), and a trailer containing the ADLER32
 * hash (4 bytes). This compression format is that used for files having
 * the ".gz" extension; most Internet browsers also supports the ZLIB Compressed
 * format as content encoding "deflate", with the only exception of MS Internet
 * Explorer that accepts DEFLATE instead (see RFC 2616 for more).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/25 20:41:46 $
 */
class ZLIBCompressOutputStream extends ResourceOutputStream {

	/**
	 * @var resource
	 */
	private $gz;


	/**
	 * Creates a new ZLIB Compressed output stream.
	 * @param OutputStream $out Destination of the compressed stream.
	 * @param int $level Compression level ranging from 0 (no compression) up to
	 * 9 (maximum compression level, possibly slower); -1 is the internal
	 * default of the library.
	 * @throws IOException
	 * @throws InvalidArgumentException Invalid level.
	 */
	public function __construct($out, $level = -1)
	{
		if( $level < -1 || $level > 9 )
			throw new InvalidArgumentException("level=$level");
		try {
			$this->gz = OutputStreamAsResource::get($out);
			
			stream_filter_append($this->gz, 'zlib.deflate',
				STREAM_FILTER_WRITE,
				array('level' => $level, 'window' => 15, 'memory' => 9) );
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage(), 0, $e);
		}
		parent::__construct($this->gz);
	}


	/**
	 * Closes the file. Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		if( $this->gz === NULL )
			return;
		try {
			fclose($this->gz);
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage(), 0, $e);
		}
		$this->gz = NULL;
	}

}
