<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../all.php";

use RuntimeException;
use it\icosaedro\utils\Floats;

/**
	Implements the concept of equality. Provides methods for strong values
	comparison by equality that overcome the ambiguity of the PHP type
	juggling. Moreover, supports objects that implements the {@link
    it\icosaedro\containers\Comparable} class.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/03/05 10:36:54 $
*/
class Equality {

	/**
		Determinates if two values are "equal". On pure rational base,
		such a method should belong to the Comparable class, but
		interfaces cannot define static methods, so it gone here.
		@param mixed $a First value to compare.
		@param mixed $b Second value to compare.
		@return bool True if the two values are equal.
		For values that are both of the type null,
		or both of the type boolean,
		or both of the type int,
		or both of the type float,
		or both of the type string,
		or both of the type array,
		returns true if the two values contain the same value.

		Floating point values are compared using {@link
		it\icosaedro\utils\Floats::compare()}.

		Arrays are compared recursively, key by key and element by element.

		Two resources are equal only if are the same instance of a resource.

		Two objects are equal if they are the same instance or, if they
		implement {@link it\icosaedro\containers\Comparable::equals()},
		the value of this latter method
		is returned instead.
	*/
	public static function areEqual($a, $b)
	{
		if( is_object($a) ){
			if( ! is_object($b) )
				return FALSE;
			if( $a === $b )
				return TRUE;
			# Objects are different instances. Compare values:
			$a_obj = cast("object", $a);
			if( ! $a_obj instanceof Comparable )
				return FALSE;
			$b_obj = cast("object", $b);
			if( ! $b_obj instanceof Comparable )
				return FALSE;
			$a_cmp = cast("it\\icosaedro\\containers\\Comparable", $a_obj);
			$b_cmp = cast("it\\icosaedro\\containers\\Comparable", $b_obj);
			return $a_cmp->equals($b_cmp);
		} else if( is_null($a) ){
			return is_null($b);
		} else if( is_bool($a) ){
			return $a === $b;
		} else if( is_int($a) ){
			return $a === $b;
		} else if( is_string($a) ){
			return $a === $b;
		} else if( is_float($a) ){
			if( is_float($b) )
				return Floats::compare((float) $a, (float) $b) == 0;
			else
				return FALSE;
		} else if( is_array($a) ){
			if( ! is_array($b) )
				return FALSE;
			$a_arr = /*. (__phplint_forced_typecast__ array) .*/ $a;
			$b_arr = /*. (__phplint_forced_typecast__ array) .*/ $b;
			if( count($a_arr) != count($b_arr) )
				return FALSE;
			foreach($a_arr as $k => $e){
				if( ! array_key_exists($k, $b_arr) )
					return FALSE;
				if( is_int($k) ){
					if( ! self::areEqual($e, $b_arr[(int)$k]) )
						return FALSE;
				} else {
					if( ! self::areEqual($e, $b_arr[(string)$k]) )
						return FALSE;
				}
			}
			return TRUE;
		} else if( is_resource($a) ){
			return is_resource($b) && $a === $b;
		} else {
			throw new RuntimeException("cannot compare unexpected type " . gettype($a));
		}
		
	}
}
