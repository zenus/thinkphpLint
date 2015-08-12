<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\phplint;

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/TypeInterface.php";


/**
	Singleton object that represents the boolean type.
	The Types::parseType() method uses this class to represent the result
	of the compilation of a textual type descriptor.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/18 22:39:24 $
*/
final class BooleanType implements TypeInterface {

	private static /*. self .*/ $singleton;

	private /*. void .*/ function __construct(){}

	/**
		Return the instance that represents the boolean type.
		@return self
	*/
	static function factory()
	{
		if( self::$singleton === NULL )
			self::$singleton = new BooleanType();
		return self::$singleton;
	}


	/**
		Checks if the expression or variable passed is of type boolean.
		@param mixed $v Any expression or variable.
		@return bool True if the expression is of the type boolean, that
		is its value is either FALSE or TRUE.
	*/
	function check($v)
	{
		return is_bool($v);
	}


	/**
		Returns the descriptor of this type, that is "boolean".
		@return string The string "boolean".
	*/
	function __toString()
	{
		return "boolean";
	}

}
