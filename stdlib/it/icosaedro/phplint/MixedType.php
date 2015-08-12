<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\phplint;

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/TypeInterface.php";


/**
	Singleton object that represents the mixed type, that is any type.
	The Types::parseType() method uses this class to represent the result
	of the compilation of a textual type descriptor.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/08/13 18:41:22 $
*/
final class MixedType implements TypeInterface {

	private static /*. self .*/ $singleton;

	private /*. void .*/ function __construct(){}

	/**
		Return the instance that represents the int type.
		@return self
	*/
	static function factory()
	{
		if( self::$singleton === NULL )
			self::$singleton = new MixedType();
		return self::$singleton;
	}


	/**
		Checks if the expression or variable passed is of type mixed.
		@param mixed $v Any expression or variable.
		@return bool Always returns true.
	*/
	function check($v)
	{
		return true;
	}


	/**
		Returns the descriptor of this type, that is "mixed".
		@return string The string "mixed".
	*/
	function __toString()
	{
		return "mixed";
	}

}
