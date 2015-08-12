<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";
use InvalidArgumentException;

/**
 * Stream output filter that skips the latest N bytes of from the source stream.
 * In other words, the last N bytes of the written stream are not sent to the
 * output stream.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:58:27 $
 */
class SkipTailOutputStream extends OutputStream {
	
	/**
	 * @var OutputStream
	 */
	private $out;
	
	private $tail_length = 0;
	
	private $buf = "";
	
	/**
	 * 
	 * @param OutputStream $out Filtered stream.
	 * @param int $tail_length Number of bytes to skip from the end of the
	 * written stream.
	 * @throws InvalidArgumentException Negative tail length.
	 */
	public function __construct($out, $tail_length)
	{
		if( $tail_length < 0 )
			throw new InvalidArgumentException("tail_length=$tail_length");
		$this->out = $out;
		$this->tail_length = $tail_length;
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
		$c = chr($b & 255);
		if( strlen($this->buf) < $this->tail_length ){
			$this->buf .= $c;
			return;
		}
		$w = ord($this->buf);
		$this->buf = substr($this->buf . $c, 1);
		$this->out->writeByte($w);
	}


	/**
	 * Writes a string of bytes. Does nothing if the string is NULL
	 * or empty.
	 * @param string $bytes
	 * @return void
	 * @throws IOException
	 */
	public function writeBytes($bytes)
	{
		$bytes_len = strlen($bytes);
		if( $bytes_len == 0 )
			return;
		// Tries to minimize I/O operations and string manipulations:
		$buf_len = strlen($this->buf);
		if( $bytes_len >= $this->tail_length ){
			// Common case: the client writes large chunks, where "large" means
			// longher than the tail length.
			if( $buf_len > 0 )
				$this->out->writeBytes($this->buf);
			$this->buf = $bytes;
		} else {
			// Unusual case: the client writes small chunks at a time.
			$this->buf .= $bytes;
			$buf_len = strlen($this->buf);
			if( $buf_len > $this->tail_length ){
				$this->out->writeBytes(substr($this->buf, 0, $buf_len - $this->tail_length));
				$this->buf = substr($this->buf, $buf_len - $this->tail_length);
			}
		}
	}


	/**
	 * Does its best to actually write any pending data stored the internal
	 * buffer.
	 * @return void
	 * @throws IOException
	 */
	function flush()
	{
		if( strlen($this->buf) > $this->tail_length ){
			$w = substr($this->buf, 0, strlen($this->buf) - $this->tail_length);
			$this->buf = substr($this->buf, strlen($w));
			$this->out->writeBytes($w);
		}
		$this->out->flush();
	}


	/**
	 * Closes the file. Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		if( $this->out === NULL )
			return;
		$this->flush();
		$this->out->close();
		$this->out = NULL;
	}
	
}
