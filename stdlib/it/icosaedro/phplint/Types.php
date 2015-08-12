<?php

namespace it\icosaedro\phplint;

/*. require_module 'spl'; .*/

#require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/../../../AutoloadException.php";
require_once __DIR__ . "/MixedType.php";
require_once __DIR__ . "/ArrayBothType.php";
require_once __DIR__ . "/ArrayIntType.php";
require_once __DIR__ . "/ArrayStringType.php";
require_once __DIR__ . "/BooleanType.php";
require_once __DIR__ . "/ClassType.php";
require_once __DIR__ . "/FloatType.php";
require_once __DIR__ . "/IntType.php";
require_once __DIR__ . "/NullType.php";
require_once __DIR__ . "/ObjectType.php";
require_once __DIR__ . "/ResourceType.php";
require_once __DIR__ . "/StringType.php";
require_once __DIR__ . "/TypeInterface.php";

use RuntimeException;
use InvalidArgumentException;
use AutoloadException;


/**
	Utilities to manipulate types. Mostly intended for being used by the magic
	cast() function.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/03/03 16:49:03 $
*/
class Types {


	/* Maps precompiled type descriptors into types. */
	private static /*. array[string]TypeInterface .*/ $types_cache = NULL;


	/**
		Parses a type descriptor and retrieves a type object that corresponds to
		it. The object result of this compilation implements the {@link
		it\icosaedro\phplint\TypeInterface} interface, and then provides thecheck() method that
		allows to verify the actual type of a generic mixed value.

		Compiled type descriptor expressions are cached for better runtime
		performances, so for every distinct type descriptor string the
		relatively expensive parsing process takes place only the first time
		that string is seen. Classes specified in the descriptor must be
		already defined or, if the class autoloading feature is enabled,
		classes are automatically loaded.
		Example:

	<pre>
	# Checks if a variable really holds an array of strings:
	use it\icosaedro\phplint\Types;
	$type = Types::parseType("string[int]");
	if( $type-&gt;check($anyvalue) ){
		die("not an array of strings");
	}
	</pre>

		A <b>type descriptor</b> follows a syntax very much similar to that
		PHPLint uses to indicate a type: null, int, float, string, resource,
		object, any class type (with fully qualified name if it belongs to a
		namespace) or T[I] where I is the type of the index (int, string
		or empty if both) and T is the type of the elements; multidimensional
		arrays may take several indices, for example float[int][int]. The
		detailed syntax in EBNF form:

	<pre>
	type = type_name { index } | array;

	type_name = "mixed" | "boolean" | "bool" | "int"
	            | "float" | "double" | "real"
	            | "string" | "resource"
	            | "object" | FULLY_QUALIFIED_CLASS_NAME;

	array = "array" index { index } type_name;

	index = "[]" | "[int]" | "[string]";
	</pre>
		
		White spaces are not allowed. Note that there is not a "mixed" type,
		and that arrays must always specify the index and the type of the
		elements.

		@param string $type_descr Descriptor of the type.
		@return TypeInterface The object that describes this type.
		@throws InvalidArgumentException The type descriptor has an invalid
		syntax. The class or interface is not defined.
		@throws AutoloadException Autoloading is enabled, but the given
		class or interface cannot be loaded.
	*/
	static function parseType($type_descr)
	{
		if( self::$types_cache === NULL ){
			self::$types_cache["mixed"] = MixedType::factory();
			self::$types_cache["null"] = NullType::factory();
			self::$types_cache["boolean"] = BooleanType::factory();
			self::$types_cache["bool"] = BooleanType::factory();
			self::$types_cache["int"] = IntType::factory();
			self::$types_cache["integer"] = IntType::factory();
			self::$types_cache["float"] = FloatType::factory();
			self::$types_cache["real"] = FloatType::factory();
			self::$types_cache["double"] = FloatType::factory();
			self::$types_cache["string"] = StringType::factory();
			self::$types_cache["resource"] = ResourceType::factory();
			self::$types_cache["object"] = ObjectType::factory();
			self::$types_cache["array"] = new ArrayBothType(MixedType::factory());
		}

		if( ! is_string($type_descr) )
			throw new InvalidArgumentException("invalid type descriptor: not string");
		
		if( isset(self::$types_cache[$type_descr]) )
			return self::$types_cache[$type_descr];

		$type = /*. (TypeInterface) .*/ NULL;

		// Check simple type first, no array:
		if( strpos($type_descr, "[") === FALSE ){
			if( class_exists($type_descr) or interface_exists($type_descr) ){
				$type = new ClassType($type_descr);
			} else {
				throw new InvalidArgumentException("unknown type or unknown class: $type_descr");
			}

		} else {

			// Ok, it is an array.
			// Explode at "]" (this latter char then removed)
			// so that "T[int][string]" becomes array("T", "int]", "string]")
			// and "array[int]T" becomes array("array", "int]T").
			// Note that in this latter case the last element "int]T"
			// contains both the index and a type: will fix this next.
			$a = explode("[", $type_descr);

			if( $a[0] === "array" ){
				// Old syntax "array[]T".

				// Last index requires to be separated from type:
				// "int]T" --> "int]", "T":
				$last = count($a)-1;
				$i = strpos($a[$last], "]");
				if( $i !== FALSE ){
					$a[] = substr($a[$last], $i+1);
					$a[$last] = substr($a[$last], 0, $i+1);
				}

				if( count($a) < 3 )
					throw new InvalidArgumentException("invalid type descriptor: $type_descr");
				$s = $a[count($a)-1];
				if( strlen($s) == 0 )
					$type = MixedType::factory();
				else
					$type = self::parseType($s);
				$i1 = 1;
				$i2 = count($a) - 2;
			} else {
				// New syntax "T[]".
				$type = self::parseType($a[0]);
				$i1 = 1;
				$i2 = count($a) - 1;
			}
			for($i = $i2; $i >= $i1; $i--){
				if( $a[$i] === "int]" )
					$type = new ArrayIntType($type);
				else if( $a[$i] === "string]" )
					$type = new ArrayStringType($type);
				else if( $a[$i] === "]" )
					$type = new ArrayBothType($type);
				else
					throw new InvalidArgumentException("invalid type descriptor: $type_descr");
			}

		}

		self::$types_cache[$type_descr] = $type;
		return $type;
	}


	/**
	 * Returns the type guessed from a value. This function is used by the
	 * cast() function to display the actual type that does not match the
	 * expected type in order to give a meaningful message to the user.
	 * There are still two cases were this function cannot provide a
	 * satisfactory result:
	 * 
	 * 1. The NULL value may belong to any reference type according to
	 * the model of types of PHPLint, so in this case a dummy "null" type
	 * is returned.
	 * 
	 * 2. An array. Here only the type of the indeces is checked, but
	 * not the type of the elements, that may be objects belonging to
	 * different classes and related only by some shared implemented or
	 * extended class. Moreover, for empty arrays array() nothing can be
	 * guessed. So this function always returns a dummy array of objects
	 * for any type of array, which may be quite misleading.
	 *
	 * @param mixed $v  Any value.
	 * @return TypeInterface Compiled description of the type.
	 */
	static function typeOf($v)
	{
		if( is_null($v) )
			return NullType::factory();
		else if( is_bool($v) )
			return BooleanType::factory();
		else if( is_int($v) )
			return IntType::factory();
		else if( is_float($v) )
			return FloatType::factory();
		else if( is_resource($v) )
			return ResourceType::factory();
		else if( is_string($v) )
			return StringType::factory();
		else if( is_object($v) )
			return self::parseType( get_class( /*. (__phplint_forced_typecast__ object) .*/ $v) );
		else if( is_array($v) ){
			$found_index = FALSE;
			$found_index_int = FALSE;
			$found_index_string = FALSE;
			$found_elem = FALSE;
			$found_elem_type = "mixed";
			foreach(/*. (__phplint_forced_typecast__ array[]) .*/ $v as $k => &$e){
				if( $found_index ){
					if( is_int($k) )
						$found_index_string = FALSE;
					else
						$found_index_int = FALSE;
				} else {
					$found_index = TRUE;
					if( is_int($k) )
						$found_index_int = TRUE;
					else
						$found_index_string = TRUE;
				}
				if( $found_elem ){
					if( $found_elem_type !== gettype($e) )
						$found_elem_type = "mixed";
				} else {
					$found_elem = TRUE;
					$found_elem_type = gettype($e);
				}
			}
			if( $found_index_int )
				return self::parseType("array[int]".$found_elem_type);
			else if( $found_index_string )
				return self::parseType("array[string]".$found_elem_type);
			else
				return self::parseType("array[]".$found_elem_type);
		} else {
			throw new RuntimeException("unexpected type: " . gettype($v));
		}
	}

}
