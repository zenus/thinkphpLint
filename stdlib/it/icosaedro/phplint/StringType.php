<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\phplint;

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/TypeInterface.php";


/**
	Singleton object that represents the string type.
	The Types::parseType() method uses this class to represent the result
	of the compilation of a textual type descriptor.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/18 22:39:24 $
*/
final class StringType implements TypeInterface {

	private static /*. self .*/ $singleton;

	private /*. void .*/ function __construct(){}

	/**
		Return the instance that represents the string type.
		@return self
	*/
	static function factory()
	{
		if( self::$singleton === NULL )
			self::$singleton = new StringType();
		return self::$singleton;
	}


	/**
		Checks if the expression or variable passed is of type string.
		@param mixed $v Any expression or variable.
		@return bool True if the expression is either the NULL value or a value
		of the type string.
	*/
	function check($v)
	{
		return is_null($v) or is_string($v);
	}


	/**
		Returns the descriptor of this type, that is "string".
		@return string The string "string".
	*/
	function __toString()
	{
		return "string";
	}

}
