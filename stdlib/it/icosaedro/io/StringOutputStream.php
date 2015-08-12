<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../errors.php";
require_once __DIR__ . "/IOException.php";
require_once __DIR__ . "/../utils/StringBuffer.php";
use it\icosaedro\utils\StringBuffer;

/**
 * Writes bytes to an internal buffer of bytes. The internal buffer is
 * initially empty. The __toString() method can be called at any time to
 * get the current content of the internal buffer.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:59:58 $
 */
class StringOutputStream extends OutputStream {
	
	/**
	 * Destination buffer;
	 * @var StringBuffer
	 */
	private $buffer;
	
	
	/**
	 * Creates a new, empty, internal buffer. 
	 * @return void
	 */
	public function __construct(){
		$this->buffer = new StringBuffer();
	}

	
	/**
	 * Writes a single byte.
	 * @param int $b Byte to write. Only the lower 8 bits are actually
	 * written.
	 * @return void
	 * @throws IOException
	 */
	public function writeByte($b){
		$this->buffer->append(chr($b & 255));
	}


	/**
	 * Writes a string of bytes. Does nothing if the string is NULL
	 * or empty.
	 * @param string $bytes
	 * @return void
	 * @throws IOException
	 */
	public function writeBytes($bytes) {
		$this->buffer->append($bytes);
	}


	/**
	 * Does nothing in this implementation.
	 * @return void 
	 * @throws IOException
	 */
	function flush(){}


	/**
	 * Closes the file. Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close() {}
	
	
	/**
	 * Returns the current, updated, content of the internal buffer.
	 * @return string
	 */
	public function __toString(){
		return $this->buffer->__toString();
	}


}
