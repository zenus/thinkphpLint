<?php

/*. require_module 'spl'; .*/

require_once __DIR__ . "/it/icosaedro/phplint/Types.php";
require_once __DIR__ . "/CastException.php";
require_once __DIR__ . "/AutoloadException.php";

use it\icosaedro\phplint\Types;


/**
	Checks if a given expression matches the expected type. Does not perform any
	value convertion, but instead either returns the same value, or fails with
	exception if the value does not match the expected type. This function is
	"magic" in the sense that PHPLint is aware of its special meaning, so that
	the type it returns is not that declared here (mixed), but the type
	specified in the first argument. So, for example:

	<pre>$date = cast("DateTime", unserialize($somevar));</pre>

	retrieves the variable <code>$date</code> as an object of the class DateTime,
	or it fails throwing an exception if the data passed in the second argument
	does not represent an instance of that class. Another example slightly more
	complicated where we try to recover an array of objects from a generic
	"mixed" variable:

	<pre>$cars = cast("array[int]com\\automotive\\MyCar", $anothervar);</pre>

	In this latter example, note how the name of the class has to be specified
	with its fully qualified name (reason: cast() is anaware of the context
	from which it is called, so the function cannot resolve incomplete class
	names agaist the 'use' statements declared in the client code.)

	A <b>type descriptor</b> follows a syntax very much similar to that PHPLint
	uses to indicate a type: null, int, float, string, resource, object, any
	class type (with fully qualified name if it belongs to a namespace) or
	array[I]T where I is the type of the index (int, string or empty if both)
	and T is the type of the elements; multidimensional arrays may take several
	indices, for example float[int][int]. For arrays both the old syntax
	array[]T and the new syntax T[] are allowed. The detailed syntax in EBNF
	form:

	<pre>
	type = type_name { index } | array;

	type_name = "boolean" | "int" | "float" | "string" | "resource"
		| "object" | FULLY_QUALIFIED_CLASS_NAME;

	array = "array" [ index { index } ] type_name;

	index = "[]" | "[int]" | "[string]";
	</pre>
		
	White spaces are not allowed. Note that there is not a "mixed" type,
	and that arrays must always specify the index and the type of the
	elements.

	Classes specified in the descriptor must be already defined or, if the
	class autoloading feature is enabled, classes are automatically loaded.

	Type descriptors strings are parsed by the {@link it\icosaedro\phplint\Types::parseType()}
	method, and the result is cached for better runtime performances. While the
	test over single values may be quite fast, testing arrays may require much
	more time, as all the indeces and elements must by scanned and compared
	with the expected type. Read the documentation about that method for
	further details.

	@param string $type_descr Descriptor of the expected type.
	@param mixed $value Any expression. Note that the NULL value matches any
	reference type, that is string, array, resource and object of any class.
	@return mixed Merely returns the $value passed, but only if it matches the
	specified type. The type returned according to PHPLint is that specified in
	the type descriptor, which must be a literal string or an expression
	statically evaluable, and do not really return the dummy mixed type
	indicated here.
	@throws CastException The value does not match the expected type descriptor.
	@throws InvalidArgumentException The type descriptor has an invalid syntax.
	The class or interface is not defined.
	@throws AutoloadException The given class is not defined, class autoloading
	is enabled but the class or interface cannot be loaded.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/21 23:02:33 $
*/
function cast($type_descr, $value)
{
	$type = Types::parseType($type_descr);
	if( ! $type->check($value) )
		throw new CastException("expected $type_descr but found "
		. Types::typeOf($value));
	return $value;
}


