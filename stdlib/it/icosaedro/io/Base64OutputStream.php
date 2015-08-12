<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

/**
 * Output stream filter that performs Base64 encoding. Please note that the
 * close() method MUST be called in order to properly complete the writing of
 * the Base64 stream; calling flush() is not enough to this end.
 * Implements the RFC 2045 encoding scheme.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/16 17:27:24 $
 */
class Base64OutputStream extends OutputStream {
	
	/**
	 * @var OutputStream
	 */
	private $out;
	
	/**
	 * @var string
	 */
	private $buf = "";
	
	/**
	 * Creates a new Base64 stream encoder.
	 * @param OutputStream $out Output stream, Base64 encoded.
	 */
	public function __construct($out)
	{
		$this->out = $out;
	}

	
	/**
	 * Writes a single byte.
	 * @param int $b Byte to write. Only the lower 8 bits are actually
	 * written.
	 * @return void
	 * @throws IOException
	 */
	public function writeByte($b)
	{
		$this->buf .= chr($b & 255);
		$this->flush();
	}


	/**
	 * Writes a string of bytes. Does nothing if the string is NULL
	 * or empty.
	 * @param string $bytes
	 * @return void
	 * @throws IOException
	 */
	public function writeBytes($bytes) {
		if( strlen($bytes) == 0 )
			return;
		$this->buf .= $bytes;
		$this->flush();
	}


	/**
	 * Writes to the output stream as many bytes as possible from the internal
	 * buffer. Please note that, because of the way the Base64 encoding works,
	 * still some bytes may remain in the internal buffer as they can be written
	 * only at the close().
	 * @return void
	 * @throws IOException
	 */
	function flush()
	{
		$len = strlen($this->buf);
		if( $len < 3 )
			return;
		$w = 3 * (int) ($len / 3);
		$this->out->writeBytes(base64_encode(substr($this->buf, 0, $w)));
		if( $w < $len )
			$this->buf = substr($this->buf, $w, $len - $w);
		else
			$this->buf = "";
		parent::flush();
	}


	/**
	 * Closes the file. Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * This method MUST be called in order to complete the writing of the
	 * Base64 last bytes; if not called, the latest 1 or 2 bytes of the stream
	 * might be lost.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		if( $this->out === NULL )
			return;
		if( strlen($this->buf) > 0 )
			$this->out->writeBytes(base64_encode($this->buf));
		$this->out->close();
		$this->out = NULL;
	}

}
