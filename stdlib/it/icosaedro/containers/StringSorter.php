<?php

namespace it\icosaedro\containers;

/**
	Generic interface that defines a sort criteria among strings.
	For example, you may define a custom comparator that depends on a
	particular encoding of the strings, or you define a case-insensitive
	comparator, a phone book sort, etc. Custom comparators can be used with
	the sortArrayOfStringByComparator() method of the Arrays class.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/01/28 20:27:32 $
*/
interface StringSorter {

	/**
		Compare two strings.
		@param string $a The first string.
		@param string $b The second string.
		@return int Negative, zero or positive if $a is less, equal or
		greater than $a respectively.
	*/
	function compare($a, $b);

	/**
		Tells if the $this string equals the $other.
		@param object $other The other string. Note that this parameter is
		of the generic type object rather than string so that this interface
		is compatible with the it\icosaedro\containers\Hashable interface;
		implementations must then check for the actual type of the parameter
		passed.
		@return bool True if the two strings are equal.
	*/
	function equals($other);

}
