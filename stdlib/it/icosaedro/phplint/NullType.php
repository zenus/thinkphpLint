<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\phplint;

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/TypeInterface.php";


/**
	Singleton object that represents the null type. The null type has only a
	value: NULL. Under PHPLint this type does not really exists and it is
	provided by this class only for completeness when a generic value has to be
	mapped into a type.
	The Types::parseType() method uses this class to represent the result
	of the compilation of a textual type descriptor.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/18 22:39:24 $
*/
final class NullType implements TypeInterface {

	private static /*. self .*/ $singleton;

	private /*. void .*/ function __construct(){}

	/**
		Return the instance that represents the null type.
		@return self
	*/
	static function factory()
	{
		if( self::$singleton === NULL )
			self::$singleton = new NullType();
		return self::$singleton;
	}


	/**
		Checks if the expression or variable passed is the NULL value. Note
		that, under PHPLint, there are several types that allows NULL as a
		special value: string, resource, array, object of any class. So, there
		is no way to detect the original type of a variable whose value is
		currently NULL.
		@param mixed $v Any expression or variable.
		@return bool True if the expression is NULL.
	*/
	function check($v)
	{
		return is_null($v);
	}


	/**
		Returns the descriptor of this type, that is "null".
		@return string The string "null".
	*/
	function __toString()
	{
		return "null";
	}

}
