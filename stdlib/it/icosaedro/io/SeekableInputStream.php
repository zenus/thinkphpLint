<?php

namespace it\icosaedro\io;
/*. require_module 'spl'; .*/
require_once __DIR__ . "/IOException.php";
require_once __DIR__ . "/InputStream.php";
use InvalidArgumentException;

/**
 * Seekable input stream of bytes. Implements the basic interface to a stream
 * of bytes that support the concept of a current position from which the next
 * data can be read. The client can move freely back an forth over the stream.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/23 15:30:18 $
 */
abstract class SeekableInputStream extends InputStream {
	
	/**
	 * Moves the read position to the specified offset in the stream.
	 * The next read operation will be performed starting at this new offset.
	 * @param int $position New location of the cursor as number of bytes from
	 * the beginning of the stream. Valid values are between 0 and
	 * {@link self::getPosition()}-1.
	 * @return void
	 * @throws InvalidArgumentException Negative position or beyond the end
	 * of the stream.
	 * @throws IOException
	 */
	public abstract function setPosition($position);
	
	
	/**
	 * Returns the current read position.
	 * @return int Current read position as number of bytes from the beginning
	 * of the stream.
	 * @throws IOException
	 */
	public abstract function getPosition();
	
	
	/**
	 * Returns the length of the stream.
	 * @return int Length of the stream (bytes).
	 * @throws IOException
	 */
	public abstract function length();
	
}
