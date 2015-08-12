<?php
/*.
	require_module 'standard_reflection';
 .*/

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../autoload.php";

use ReflectionClass;

/**
 * Invalid ZIP file format.
 * 
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 21:04:07 $
 */
class ZipFileException extends IOException
{
	
	/**
	 * Returns the ZipArchive error name given its code.
	 * @param int $code
	 * @return string Name of the ZipArchive::ER_XXX constant if found, or the
	 * error code as a string if not found.
	 */
	private static function getErrorName($code)
	{
		$r = new ReflectionClass("ZipArchive");
		$constants = $r->getConstants();
		foreach($constants as $name => $value)
			if ( substr($name, 0, 3) === "ER_" && $value === $code )
					return $name;
		return "$code";
	}
	
	
	/**
	 * @param string $message Context where the error happened.
	 * @param mixed $code If this argument is provided, it is expected to be
	 * the value returned by the ZipArchive::open() method.
	 */
	public function __construct($message, $code = NULL)
	{
		if( func_num_args() == 1 )
			parent::__construct($message, 0);
		else if( is_int($code) )
			parent::__construct($message . " (code " . self::getErrorName((int) $code) . ")", (int) $code);
		else
			parent::__construct($message . " (unexpected value returned)", 0);
	}
	
}