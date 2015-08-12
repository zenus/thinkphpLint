<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";

/**
 * Exception thrown on fatal parsing error. The parser throws this exception
 * if a fatal syntax error is found that prevents from continuing parsing.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:14:07 $
 */
/*. unchecked .*/ class ParseException extends \Exception {
	
	/**
	 * Location where the fatal parsing error was detected.
	 * @var Where 
	 */
	private $where;

	/**
	 * Builds a new parser exception.
	 * @param Where $where Location where the fatal parsing error has been
	 * detected.
	 * @param string $msg Description of the error detected.
	 * @return void
	 */
	public function __construct($where, $msg)
	{
		parent::__construct($msg);
		$this->where = $where;
	}
	
	
	/**
	 * Returns the location where this fatal parsing error was detected.
	 * @return Where 
	 */
	public function getWhere()
	{
		return $this->where;
	}

}
