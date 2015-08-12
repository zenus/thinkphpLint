<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";

/**
 * Unrecoverable scanner exception. Scanning of the file cannot continue;
 * the source file must be closed immediately and no more symbols can be
 * requested or unpredictable results may return, then this exception
 * should be captured only at package parse level.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/07/07 17:36:38 $
 */
/*. unchecked .*/ class ScannerException extends \Exception {
	
	/**
	 * Location in the source where the error has been detected.
	 * @var Where 
	 */
	private $where;

	/**
	 * Creates a scanner exception.
	 * @param Where $where Location in the source where the error has been
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
	 * Returns the location in the source where the error has been
	 * detected.
	 * @return Where Location in the source where the error has been
	 * detected.
	 */
	public function getWhere()
	{
		return $this->where;
	}

}
