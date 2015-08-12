<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

/**
 * Output stream filter that does not close its destination stream.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/16 17:53:39 $
 */
class UncloseableOutputStream extends OutputStream {
	
	/**
	 * @var OutputStream
	 */
	private $out;
	
	/**
	 * @param OutputStream $out
	 */
	public function __construct($out) {
		$this->out = $out;
	}
	

	/**
	 * Writes a single byte.
	 * @param int $b Byte to write. Only the lower 8 bits are actually
	 * written.
	 * @return void
	 * @throws IOException
	 */
	public function writeByte($b){
		$this->out->writeByte($b);
	}
	
	
	/**
	 * Writes a string of bytes. Does nothing if the string is NULL
	 * or empty.
	 * @param string $bytes
	 * @return void
	 * @throws IOException
	 */
	public function writeBytes($bytes){
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
	 * Does nothing. As a safety measure, this object is disabled and cannot be
	 * used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		$this->out = NULL;
	}
	
}
