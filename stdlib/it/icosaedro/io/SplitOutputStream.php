<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";
use OutOfBoundsException;

/**
 * Just like an OutputStream, but also adds a given separator between chuncks
 * of written data of a given size.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:17:53 $
 */
class SplitOutputStream extends OutputStream {
	
	/**
	 * @var OutputStream
	 */
	private $out;
	private $chunk_len = 0;
	private $separator = "";
	private $bytes_written = 0;
	
	/**
	 * Creates a new stream chunk splitter output stream filter.
	 * @param OutputStream $out Destination chunked stream.
	 * @param int $chunk_len Chunk length, 1 byte or greater.
	 * @param string $separator Chunks separator.
	 * @throws OutOfBoundsException Chunk length is less than 1.
	 */
	public function __construct($out, $chunk_len, $separator)
	{
		if( $chunk_len < 1 )
			throw new OutOfBoundsException("\$chunk_len = $chunk_len");
		$this->out = $out;
		$this->chunk_len = $chunk_len;
		$this->separator = $separator;
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
		if( $this->bytes_written >= $this->chunk_len ){
			$this->out->writeBytes($this->separator);
			$this->bytes_written = 0;
		}
		$this->out->writeByte($b);
		$this->bytes_written++;
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
		do {
			$len = strlen($bytes);
			if( $len == 0 )
				return;
			$available = $this->chunk_len - $this->bytes_written;
			if( $len <= $available ){
				$this->out->writeBytes($bytes);
				$this->bytes_written += $len;
				return;
			} else {
				$this->out->writeBytes(substr($bytes, 0, $available));
				$this->out->writeBytes($this->separator);
				$this->bytes_written = 0;
				$bytes = substr($bytes, $available);
			}
		} while(true);
	}


	/*. void .*/ function flush()
		/*. throws IOException .*/
	{
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
		$this->out->close();
	}

}
