<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;

/**
 * Stream filter that skips N bytes from the source stream. In other words,
 * the first N bytes written are not sent to the destination output stream.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:58:17 $
 */
class SkipHeadOutputStream extends OutputStream {
	
	/**
	 *
	 * @var OutputStream
	 */
	private $out;

	
	/**
	 * Number of initial bytes to skip.
	 * @var int
	 */
	private $skip = 0;


	/**
	 * @param OutputStream $out
	 * @param int $skip Number of bytes to skip from the written stream. Must
	 * be non-negative.
	 * @throws InvalidArgumentException $skip is negative.
	 */
	public function __construct($out, $skip) {
		if( $skip < 0 )
			throw new InvalidArgumentException("skip=$skip");
		$this->out = $out;
		$this->skip = $skip;
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
		if( $this->skip > 0 ){
			$this->skip--;
			return;
		}
		$this->writeByte($b);
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
		if( $this->skip > 0 ){
			if( strlen($bytes) <= $this->skip ){
				$this->skip -= strlen($bytes);
				return;
			}
			$bytes = substr($bytes, $this->skip);
			$this->skip = 0;
		}
		$this->out->writeBytes($bytes);
	}


	/**
	 * Does its best to actually write any pending data stored the internal
	 * buffer.
	 * @return void
	 * @throws IOException
	 */
	public function flush()
	{
		$this->out->flush();
	}


	/**
	 * Closes the file, flushing internal buffers.
	 * Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		if( $this->out === NULL )
			return;
		$this->out->close();
		$this->out = NULL;
	}
	
}