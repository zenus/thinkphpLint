<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../all.php";

/**
 * Failed accessing a file or directory, because it does not exists, invalid
 * permissions or some other more specific reason.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:45:37 $
 */
class FileException extends IOException
{
	/**
	 * @var File
	 */
	private $name;
	
	/**
	 * @param File $name Name of the involved file.
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($name, $message, $code = 0, $previous = NULL) {
		$this->name = $name;
		parent::__construct($message, $code, $previous);
	}
	
	/**
	 * Returns the involved file or directory.
	 * @return File
	 */
	public function getName()
	{
		return $this->name;
	}
	
}

