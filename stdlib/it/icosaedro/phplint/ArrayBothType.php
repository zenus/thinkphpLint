<?php

namespace it\icosaedro\phplint;

/*. require_module 'standard'; .*/

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/TypeInterface.php";

/**
	Represents the type array[]E that is an array with indeces that may be
	both int and string.
	The Types::parseType() method uses this class to represent the result of
	the compilation of a textual type descriptor.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/08/13 18:36:07 $
*/
class ArrayBothType implements TypeInterface {

	private /*. TypeInterface .*/ $element_type;

	/**
		Creates a new array type array[]E.
		@param TypeInterface $element_type Type of the elements.
		@return void
	*/
	function __construct($element_type)
	{
		$this->element_type = $element_type;
	}


	/**
		Returns the type of the elements.
		@return TypeInterface Type of the elements.
	*/
	function getElementType()
	{
		return $this->element_type;
	}


	/**
		Checks if the value is an array of type array[]E.
		@param mixed $v Any expression or variable.
		@return bool True if the value is either NULL or an array whose
		elements are all of the type expected as set by the constructor.
	*/
	function check($v)
	{
		if( $v === NULL )
			return TRUE;

		foreach(/*. (__phplint_forced_typecast__ array[]) .*/ $v as &$e){
			if( ! $this->element_type->check($e) )
				return FALSE;
		}
		return TRUE;
	}


	/**
		Returns this type descriptor.
		@return string The string "E[]" where E is the type set in the
		constructor.
	*/
	function __toString()
	{
		$e = $this->element_type->__toString();
		$i = strpos($e, "[");
		if( $i === FALSE )
			return "$e" . "[]";
		else
			return substr($e, 0, $i) . "[]" . substr($e, $i);
	}

}
