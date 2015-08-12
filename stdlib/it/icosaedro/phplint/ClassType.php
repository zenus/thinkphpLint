<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\phplint;

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/TypeInterface.php";


/**
	Represents an instance of a given class or interface.
	The Types::parseType() method uses this class to represent the result
	of the compilation of a textual type descriptor.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/02/09 10:12:26 $
*/
class ClassType implements TypeInterface {

	/**
		Fully qualified class name.
		@var string
	*/
	private $class_name;


	/**
		Creates a type that represents the class or the interface.
		@param string $class_name Fully qualified name of the class or
		interface. No check if performed on the value passed or its encoding,
		and no class autoloading is triggered.
		@return void
	*/
	function __construct($class_name)
	{
		$this->class_name = $class_name;
	}


	/**
		Returns the fully qualified class name as set in the constructor.
		@return string Fully qualified name of the class as set in the
		constructor.
	*/
	function getClassName()
	{
		return $this->class_name;
	}


	/**
		Checks if the expression or variable passed is an object of the
		expected class. May trigger class autoloading.
		@param mixed $v Any expression or variable.
		@return bool True if the expression is either the NULL value or an
		object of the class as set in the constructor.
	*/
	function check($v)
	{
		if( $v === NULL )
			return TRUE;

		if( ! is_object($v) )
			return FALSE;

		# FIXME: BUG in PHPLint: "... instanceof EXPR" requires EXPR to
		# be a simple var, so "$this->..." is not allowed. Workaround:
		$class_name = $this->class_name;
		return $v instanceof $class_name;
	}


	/**
		Returns the descriptor of this type, that is the fully qualified
		name of the class.
		@return string The fully qualified name of the class.
	*/
	function __toString()
	{
		return $this->class_name;
	}

}
