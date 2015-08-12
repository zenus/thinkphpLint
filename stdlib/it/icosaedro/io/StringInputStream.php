<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/SeekableInputStream.php";
use InvalidArgumentException;

/**
 * Implements seekable buffer of bytes provided as a string.
 * The read cursor is initially positioned at the beginning of the buffer.
 * Example:
 * <blockquote><pre>
 * $b = new StringInputStream("large buffer of data here");
 * // Read data by chunks:
 * while( ($chunk = $b-&gt;readBytes(10)) !== NULL )
 *	echo "read: $chunk\n";
 * $b-&gt;setPosition(6);
 * echo $b-&gt;readBytes(4); // displays: "buff"
 * $b-&gt;close();
 * </pre></blockquote>
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/23 15:43:15 $
 */
class StringInputStream extends SeekableInputStream {
	
	/**
	 * Buffer of bytes to read.
	 * @var string
	 */
	private $buffer;
	
	/**
	 * Offset of the next byte to read from buffer.
	 * @var int 
	 */
	private $pos = 0;
	
	
	/**
	 * Creates a new input buffer of bytes. The cursor is positioned at the
	 * beginning of the buffer.
	 * @param string $s Bytes to be read.
	 * @return void
	 */
	public function __construct($s)
	{
		$this->buffer = $s;
		$this->pos = 0;
	}
	

	/**
	 * Reads one byte.
	 * @return int Byte read in [0,255], or -1 on end of file.
	 * @throws IOException
	 */
	public function readByte()
	{
		if( $this->pos < strlen($this->buffer) )
			return ord($this->buffer[$this->pos++]);
		else
			return -1;
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
		else if( $this->pos < strlen($this->buffer) ){
			if( $this->pos + $n > strlen($this->buffer) )
				$n = strlen($this->buffer) - $this->pos;
			$res = substr($this->buffer, $this->pos, $n);
			$this->pos += $n;
			return $res;
		} else
			return NULL;
	}
	
	
	/**
	 * Moves the read position to the specified offset in the stream.
	 * The next read operation will be performed starting at this new offset.
	 * @param int $position New location of the cursor as number of bytes from
	 * the beginning of the stream.
	 * @return void
	 * @throws InvalidArgumentException Negative position.
	 * @throws IOException
	 */
	public function setPosition($position)
	{
		if( !( 0 <= $position && $position < strlen($this->buffer) ) )
			throw new InvalidArgumentException("position = $position");
		$this->pos = $position;
	}
	
	
	/**
	 * Returns the current read position.
	 * @return int Current read position as number of bytes from the beginning
	 * of the stream.
	 */
	public function getPosition()
	{
		return $this->pos;
	}
	
	
	/**
	 * Returns the length of the stream.
	 * @return int Length of the stream (bytes).
	 */
	public function length()
	{
		return strlen($this->buffer);
	}


	/**
	 * Closes the stream. Does nothing if the stream has been already closed.
	 * @return void
	 * @throws IOException
	 */
	public function close(){
		$this->buffer = NULL; // immediately releases memory
		$this->pos = 0;
	}

	
}
