<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\phplint;

/*. require_module 'standard'; .*/

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/TypeInterface.php";

/**
	Represents the type array[int]E that is an array with int indeces.
	The Types::parseType() method uses this class to represent the result
	of the compilation of a textual type descriptor.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/08/13 18:37:13 $
*/
class ArrayIntType implements TypeInterface {

	private /*. TypeInterface .*/ $element_type;

	/**
		Creates a new array type array[int]E.
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
		Checks if the value is an array of type array[int]E.
		@param mixed $v Any expression or variable.
		@return bool True if the value is either NULL or an array whose entries
		have a key of type int and a value of the type as set by the
		constructor.
	*/
	function check($v)
	{
		if( $v === NULL )
			return TRUE;

		foreach(/*. (__phplint_forced_typecast__) .*/ $v as $k => &$e){
			if( ! is_int($k) )
				return FALSE;
			if( ! $this->element_type->check($e) )
				return FALSE;
		}
		return TRUE;
	}


	/**
		Returns this type descriptor.
		@return string The string "E[int]" where E is the type set in the
		constructor.
	*/
	function __toString()
	{
		$e = $this->element_type->__toString();
		$i = strpos($e, "[");
		if( $i === FALSE )
			return "$e"."[int]";
		else
			return substr($e, 0, $i) . "[int]" . substr($e, $i);
	}

}
