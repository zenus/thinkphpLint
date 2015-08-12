<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/InputStream.php";
require_once __DIR__ . "/IOException.php";

/**
 * Adds a method to {@link it\icosaedro\io\InputStream} that allows to read
 * line by line.
 * A line is any sequence of bytes terminated either by the end of the file or
 * by the new line control code (ASCII 10, '\n'). Example:
 * <pre>
 *		$in = new LineInputWrapper(
 *			new ResourceInputStream(
 *				fopen("php://stdin", "r") ) );
 *		echo "Type 'exit' to terminate:\n";
 *		do {
 *			$line = $in-&gt;readLine();
 *			if( $line === NULL )
 *				break; // input source closed
 *			$line = rtrim($line);
 *			echo "   you entered: $line\n";
 *			if( $line === "exit" )
 *				break;
 *		} while(TRUE);
 * </pre>
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/25 20:40:52 $
 */
class LineInputWrapper extends InputStream {

	/**
	 * @var InputStream 
	 */
	private $is;
	
	/**
	 * Internal buffer used only to scan lines. Normally, NULL.
	 * Methods of this class always look here first before asking more bytes
	 * from InputStream.
	 * @var string
	 */
	private $buffer;
	
	/**
	 * Offset of the next available byte in the internal buffer.
	 * @var int
	 */
	private $pos = 0;
	
	
	/**
	 * Initializes reading lines from input stream.
	 * @param InputStream $is
	 * @return void
	 */
	public function __construct($is){
		$this->is = $is;
	}


	/**
	 * Reads one byte.
	 * @return int Byte read in [0,255], or -1 on end of file.
	 * @throws IOException
	 */
	function readByte() {
		if( $this->pos < strlen($this->buffer) )
			return ord($this->buffer[$this->pos++]);
		
		return $this->is->readByte();
	}


	/**
	 * Reads several bytes.
	 * @param int $n Maximum number of bytes to read.
	 * @return string Bytes read, possibly in a number less than requested,
	 * either because the end of the file has been reached, or the input
	 * buffer is short but still data are available. If $n &le; 0 does nothing
	 * and the empty string is returned. If $n &gt; 0 and the returned string
	 * is NULL, the end of the file is reached.
	 * @throws IOException
	 */
	function readBytes($n) {
		if( $n <= 0 )
			return "";
		
		if( $this->pos < strlen($this->buffer) ){
			if( $this->pos + $n > strlen($this->buffer) )
				$n = strlen($this->buffer) - $this->pos;
			$res = substr($this->buffer, $this->pos, $n);
			$this->pos += $n;
			return $res;
		}

		return $this->is->readBytes($n);
	}
	
	
	/**
	 * Returns the next line. 
	 * Note that if the returned value is not NULL, then it contains at least
	 * one byte, possibly the new line code alone.
	 * @return string Next line read, or NULL at the end of the file.
	 * The new-line byte(s), if present, are returned along with the string.
	 * The standard <code>rtrim()</code> function can be used to remove these
	 * trailing  new-line characters (and possibly other white spaces).
	 * This method always returns at least one byte.
	 * @throws IOException
	 */
	public function readLine(){
		do {
			if( $this->pos >= strlen($this->buffer) ){
				$this->buffer = $this->is->readBytes(1024);
				$this->pos = 0;
				if( $this->buffer === NULL )
					return NULL;
			} else {
				$nl = strpos($this->buffer, "\n", $this->pos);
				if( $nl === FALSE ){
					$more = $this->is->readBytes(1024);
					if( strlen($more) == 0 ){
						$line = substr($this->buffer, $this->pos);
						$this->buffer = NULL;
						$this->pos = 0;
						return $line;
					} else {
						$this->buffer .= $more;
					}
				} else {
					$len = $nl + 1 - $this->pos;
					$line = substr($this->buffer, $this->pos, $len);
					$this->pos += $len;
					return $line;
				}
			}
		} while(TRUE);
	}


	/**
	 * Closes the input stream. Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	function close() {
		if( $this->is !== NULL ){
			$this->is->close();
			$this->is = NULL;
			$this->buffer = NULL;
			$this->pos = 0;
		}
	}
	
}

