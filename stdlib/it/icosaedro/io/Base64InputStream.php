<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";
/*. require_module 'pcre'; .*/

/**
 * Input stream filter that performs Base64 decoding.
 * Implements the RFC 2045 encoding scheme. In particular:
 * Invalid characters are ignored.
 * The decoding process terminates either at the end of the Base64 stream
 * if available (two equal signs "==" or a single equal sign) or the end of the
 * input stream is detected.
 * Once the decoding process is terminated, a "end of file" condition is raised,
 * although more bytes might be available from the input stream.
 * Since an internal buffer reads forward the input file, some bytes beyond the
 * end of the input stream might be read.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:05:09 $
 */
class Base64InputStream extends InputStream {
	
	/**
	 * Source of the Base64 encoded stream.
	 * @var InputStream
	 */
	private $in;
	
	/**
	 * Encoded input buffer. Only multiple of 4 bytes can de decoded, with
	 * special handling of the "==" and "=" trailing markers.
	 * @var string
	 */
	private $encoded = "";
	
	/**
	 * Decoded bytes are stored here.
	 * @var string
	 */
	private $decoded = "";
	
	/**
	 * Offset of the next byte to read from the decoded buffer.
	 * @var int
	 */
	private $idx = 0;
	
	/**
	 * Either the end of the Base64 stream ("==" or "=") or the end of the
	 * input stream has been detected.
	 * @var boolean
	 */
	private $eof = false;
	
	/**
	 * Creates a new Base64 decoder from the given input stream.
	 * @param InputStream $in
	 */
	public function __construct($in)
	{
		$this->in = $in;
	}
	
	
	/**
	 * Tries to read more encoded bytes from the input stream; store them in the
	 * encoded buffer then tries to decode as many bytes as possible.
	 * @return boolean True if at least 1 decoded byte is available in the
	 * decoded buffer.
	 * @throws IOException
	 */
	private function read()
	{
		if( $this->eof )
			return false;
		$encoded = $this->encoded;
		do {
			$s = $this->in->readBytes(4*1024);
			if( $s === NULL ){
				$this->eof = true;
				break;
			}
			$s = preg_replace("/[^a-zA-Z0-9+\\/=]/s", "", $s);
			$encoded .= $s;
			$eq = strpos($s, "=");
			if( $eq !== FALSE ){
				if( $eq == strlen($s) - 1 )
					$s .= $this->in->readBytes(1);
				$this->eof = true;
				break;
			}
		} while(strlen($encoded) < 5);
		
		$this->idx = 0;
		if( $this->eof ){
			$this->decoded = base64_decode($encoded);
			$this->encoded = "";
			return strlen($this->decoded) > 0;
		} else {
			// At least 5 encoded bytes available, and no "=" present.
			$r = 4 * (int) (strlen($s) / 4);
			if( $r < strlen($encoded) ){
				$this->decoded = base64_decode(substr($encoded, 0, $r));
				$this->encoded = substr($encoded, $r);
			} else {
				$this->decoded = base64_decode($encoded);
				$this->encoded = "";
			}
			return true;
		}
	}


	/**
	 * Reads one decoded byte.
	 * @return int Decoded byte read in [0,255], or -1 on end of file.
	 * @throws IOException
	 */
	public function readByte()
	{
		if( $this->idx >= strlen($this->decoded) ){
			if( ! $this->read() )
				return -1;
		}
		return ord($this->decoded[$this->idx++]);
	}


	/**
	 * Reads decoded bytes.
	 * @param int $n Maximum number of bytes to read.
	 * @return string Decoded bytes read, possibly in a number less than
	 * requested, because the end of the input stream or the end of the Base64
	 * stream has been reached, or the input buffer is short but still data are
	 * available. If $n &le; 0 does nothing and the empty string is returned.
	 * If $n &gt; 0 and the returned string is NULL, the end of the input stream
	 * or the end os the Base64 stream has been reached.
	 * @throws IOException
	 */
	public function readBytes($n)
	{
		if( $n <= 0 )
			return "";
		
		if( $this->idx >= strlen($this->decoded) ){
			if( ! $this->read() )
				return NULL;
		}
		$n = (int) min($n, strlen($this->decoded) - $this->idx);
		$res = substr($this->decoded, $this->idx, $n);
		$this->idx += $n;
		return $res;
	}


	/**
	 * Closes the stream. Does nothing if the stream has been already closed.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		if( $this->in !== NULL ){
			$this->in->close();
			$this->in = NULL;
			$this->eof = true;
		}
	}

}
