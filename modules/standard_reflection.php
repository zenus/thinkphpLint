<?php

/**
	Reflection.

	Actually this module should be part of the standard module, and
	it is available on any PHP 5 installation. I made a separated one
	for performance reasons, since it is seldom used.
	<p>

	See: {@link http://www.php.net/manual/en/book.reflection.php}
	@package standard_reflection
*/

/*. if_php_ver_4 .*/

	The_spl_module_is_only_for_PHP_5)

/*. end_if_php_ver .*/

/*. require_module 'standard'; .*/


/*. forward class ReflectionClass{}
	forward class ReflectionFunction{}
	forward class ReflectionExtension{}
.*/


class ReflectionException extends Exception { }


interface Reflector
{
	#static /*. string .*/ function export(
	#	/*. string .*/ $name,
	#	$return_ = FALSE)
	#	/*. throws ReflectionException .*/ ;
	/*. string .*/ function __toString() ;
}


class ReflectionParameter implements Reflector
{
	/**
	 * Creates an object that represents a parameter.
	 * @param mixed $function_or_method  A string giving the name of a
	 * function, or an array[int]string giving the name of the class and the
	 * name of the method in this order, example: array("MyClass", "myMethod").
	 * @param string $parameter_name  Name of the parameter.
	 * @return void
	 * @throws ReflectionException  If the function, class method or
	 * parameter does not exist.
	 */
	public function __construct( $function_or_method, $parameter_name){}

	public /*. string .*/ function __toString(){}

	/**
	 * Returns a short description of a parameter.
	 * @param mixed $function_or_method  A string giving the name of a
	 * function, or an array[int]string giving the name of the class and the
	 * name of the method, example: array("MyClass", "myMethod").
	 * @param string $parameter_name  Name of the parameter.
	 * @param boolean $return_  If TRUE return a string, otherwise the string
	 * is emitted on standard output and return NULL.
	 * @return string  The description of the parameter if $return_ is TRUE,
	 * or NULL if $return_ is FALSE.
	 * @throws ReflectionException  If the function, class method or
	 * parameter does not exist.
	 */
	public static function export(
		$function_or_method, $parameter_name, $return_ = FALSE){}

	public /*. string .*/ function getName(){}

	/**
	 * Returns the function to which this parameter belongs.
	 * @return mixed  Returns an instance of {@link ReflectionFunction} if the
	 * parameter belongs to a function, and returns an associative array
	 * array("name"=&gt;"MethodName", "class"=&gt;"ClassName") if the parameter
	 * belongs to a method.
	 */
	public function getDeclaringFunction(){}

	/**
	 * Returns the class to which the method of this parameter belongs.
	 * @return ReflectionClass  The class to which the method of this
	 * parameter belongs, or NULL if the parameter refers to a function.
	 */
	public function getDeclaringClass(){}

	/**
	 * This method always return NULL.
	 * @return mixed  Always returns NULL.
	 */
	public function getClass(){}

	public /*. bool .*/ function isArray(){}
	public /*. bool .*/ function allowsNull(){}
	public /*. bool .*/ function isPassedByReference(){}
	public /*. bool .*/ function isOptional(){}
	public /*. bool .*/ function isDefaultValueAvailable(){}

	/**
	 * Returns the default value of this parameter.
	 * @return mixed  The default value of the parameter.
	 * @throws ReflectionException  If the parameter does not has a default
	 * value.
	 */
	public function getDefaultValue(){}

	/**
	 * Returns the ordinal position occupied by this parameter.
	 * @return int  Ordinal position occupied by this parameter, the first
	 * parameter being the number 0.
	 */
	public function getPosition(){}
}

abstract class ReflectionFunctionAbstract implements Reflector
{
	public /*. string .*/ function getName(){}
	public /*. bool .*/ function isInternal(){}
	public /*. bool .*/ function isUserDefined(){}
	public /*. string .*/ function getFileName(){}
	public /*. int .*/ function getStartLine(){}
	public /*. int .*/ function getEndLine(){}
	public /*. string .*/ function getDocComment(){}
	public /*. array[string]mixed .*/ function getStaticVariables(){}
	public /*. bool .*/ function returnsReference(){}
	public /*. array[int]ReflectionParameter .*/ function getParameters(){}
	public /*. int .*/ function getNumberOfParameters(){}
	public /*. int .*/ function getNumberOfRequiredParameters(){}
	public /*. ReflectionExtension .*/ function getExtension(){}
	public /*. string .*/ function getExtensionName(){}
	public /*. string .*/ function getNamespaceName(){}
	public /*. string .*/ function getShortName(){}
	public /*. boolean .*/ function inNamespace(){}
	public /*. boolean .*/ function isClosure(){}
	public /*. boolean .*/ function isDeprecated(){}
}


class ReflectionFunction extends ReflectionFunctionAbstract
{
	const IS_DEPRECATED = 262144;

	public /*. void .*/ function __construct(/*. string .*/ $name){}
	public static /*. string .*/ function export(
		/*. string .*/ $func_name,
		$return_ = FALSE)
		/*. throws ReflectionException .*/ {}
	public /*. mixed .*/ function invoke(/*. args .*/){}
	public /*. mixed .*/ function invokeArgs(/*. array[int]mixed .*/ $args_){}
	public /*. string .*/ function __toString(){}
}

class ReflectionExtension implements Reflector {
	public /*. void .*/ function __clone(){}
	public /*. void .*/ function __construct(/*. string .*/ $name)
		/*. throws ReflectionException .*/{}
	public /*. string .*/ function __toString(){}
	public static /*. string .*/ function export( /*. string .*/ $extension_name, $return_ = FALSE){}
	public /*. string .*/ function getName(){}
	public /*. string .*/ function getVersion(){}
	public /*. array[int]ReflectionFunction .*/ function getFunctions(){}
	public /*. array[string]mixed .*/ function getConstants(){}
	public /*. array[string]string .*/ function getINIEntries(){}
	public /*. array[int]ReflectionClass .*/ function getClasses(){}
	public /*. array[int]string .*/ function getClassNames(){}
	public /*. string .*/ function info(){}
	public /*. array[string]string .*/ function getDependencies(){}
}

class ReflectionMethod extends ReflectionFunctionAbstract
{
	const
		IS_STATIC = 1,
		IS_PUBLIC = 256,
		IS_PROTECTED = 512,
		IS_PRIVATE = 1024,
		IS_ABSTRACT = 2,
		IS_FINAL = 4;
	
	public /*. string .*/ $name;
	public /*. string .*/ $class_;

	/**
	 * Creates a new ReflectionMethod object.
	 * @param string $class_name  Name of the class.
	 * @param string $method_name  Name of the method.
	 * @return void
	 * @throws ReflectionException  If the class or the method does not
	 * exist.  Note that class autoloading is performed in order to try
	 * resolving undefined classes.
	 */
	public function __construct($class_name, $method_name){}

	public /*. string .*/ function __toString(){}
	public static /*. string .*/ function export(
		/*. string .*/ $class_name,
		/*. string .*/ $method_name,
		$return_ = FALSE)
		/*. throws ReflectionException .*/ {}
	
	/**
	 * Invoke the method.
	 * @param object $object_  The instance of the class to which the
	 * method belongs. If the method is static this parameter is ignored
	 * and NULL can be passed instead.
	 * @return mixed  Return value from the method.
	 * @throws ReflectionException  If the method is non-static and
	 * the object passed isn't instance of the class this method was
	 * declared in.  If the number of parameters passed does not match
	 * the signature of the method. If the method is private or protected and
	 * cannot be invoked from the current context.
	 */
	public function invoke($object_ /*., args .*/){}

	/**
	 * Invoke the method.
	 * @param object $object_  The instance of the class to which the
	 * method belongs. If the method is static this parameter is ignored
	 * and NULL can be passed instead.
	 * @param array[int]mixed $args_  Arguments to be passed.
	 * @return mixed  Return value from the method.
	 * @throws ReflectionException  If the method is non-static and
	 * the object passed isn't instance of the class this method was
	 * declared in.  If the number of parameters passed does not match
	 * the signature of the method. If the method is private or protected and
	 * cannot be invoked from the current context.
	 */
	public function invokeArgs($object_, $args_){}

	public /*. bool .*/ function isFinal(){}
	public /*. bool .*/ function isAbstract(){}
	public /*. bool .*/ function isPublic(){}
	public /*. bool .*/ function isPrivate(){}
	public /*. bool .*/ function isProtected(){}
	public /*. bool .*/ function isStatic(){}
	public /*. bool .*/ function isConstructor(){}
	public /*. bool .*/ function isDestructor(){}
	public /*. int .*/ function getModifiers(){}
	public /*. ReflectionClass .*/ function getDeclaringClass(){}
	public /*. void .*/ function setAccessible(/*. boolean .*/ $accessible){}
}


class ReflectionProperty implements Reflector
{
	const
		IS_STATIC = 1,
		IS_PUBLIC = 256,
		IS_PROTECTED = 512,
		IS_PRIVATE = 1024;
	
	/**
	 * Creates an instance of a property. This property is intended not
	 * bound to any particular instance of the class; instead, it refers
	 * to the property as defined in the source code.
	 * @param mixed $class_or_instance  String giving the name of the class,
	 * or object instance of the class.
	 * @param string $property_name  Name of the property.
	 * @return void
	 * @throws ReflectionException
	 */
	public function __construct($class_or_instance, $property_name){}

	public /*. string .*/ function __toString(){}

	/**
	 * Returns a short description of a class' parameter.
	 * @param mixed $class_or_instance  A string giving the name of a
	 * class, or any instance of that class.
	 * @param string $property_name  Name of the property.
	 * @param boolean $return_  If TRUE return a string, otherwise the string
	 * is emitted on standard output and return NULL.
	 * @return string  The description of the property if $return_ is TRUE,
	 * or NULL if $return_ is FALSE.
	 * @throws ReflectionException  If the class does not exit, or the
	 * property does not exist in that class or instance.
	 */
	public static function export(
		$class_or_instance, $property_name, $return_ = FALSE){}

	public /*. string .*/ function getName(){}
	public /*. bool .*/ function isPublic(){}
	public /*. bool .*/ function isPrivate(){}
	public /*. bool .*/ function isProtected(){}
	public /*. bool .*/ function isStatic(){}
	public /*. bool .*/ function isDefault(){}
	public /*. int .*/ function getModifiers(){}

	/**
	 * Returns the value of the property.
	 * @param object $obj  If the property is non-static, this is the instance
	 * from which the value is retrieved. If the property is static this
	 * argument is ignored and NULL can be passed instead.
	 * <br><b>Warning.</b> No check is performed on the class to which the
	 * passed object belongs, then blindly returns any property with the
	 * same name or NULL if that property does not exist in the object.
	 * @return mixed
	 * @throws ErrorException  NULL passed as argument for non-static property.
	 * @throws ReflectionException  The property is private or protected and
	 * cannot be accessed from the current context.
	 */
	public function getValue($obj = NULL){}

	/**
	 * Sets the value of the property.
	 * @param object $obj  If the property is non-static, this is the instance
	 * from which the property is retrieved. If the property is static this
	 * argument is ignored and NULL can be passed instead.
	 * <br><b>Warning.</b> No check is performed on the class to which the
	 * passed object belongs, then blindly creates a property with the same
	 * name if it does not currently exist.
	 * <br><b>Warning.</b> PHPLint cannot control the type of the value
	 * assigned to the property be compatible with its declaration, so you
	 * must be very careful when using this function.
	 * @param mixed $value
	 * @return void
	 * @throws ReflectionException  The property is private or protected and
	 * cannot be accessed from the current context.
	 */
	public function setValue($obj, $value){}

	public /*. void .*/ function setAccessible(){}
	public /*. ReflectionClass .*/ function getDeclaringClass(){}
	public /*. string .*/ function getDocComment(){}
}

class ReflectionClass implements Reflector
{
	const
		IS_IMPLICIT_ABSTRACT = 16,
		IS_EXPLICIT_ABSTRACT = 32,
		IS_FINAL = 64;

	public /*. void .*/ function __clone(){}
	public /*. void .*/ function __construct(/*. string .*/ $name){}
	public /*. string .*/ function __toString(){}
	#public static /*. string .*/ function export(/*. string .*/ $class_)
	#public static /*. string .*/ function export(/*. mixed .*/ $class_name /*., args .*/)
	public static /*. string .*/ function export(
		/*. mixed .*/ $class_name,
		$return_ = FALSE)
		/*. throws ReflectionException .*/ {}
	public /*. string .*/ function getName(){}
	public /*. bool .*/ function isInternal(){}
	public /*. bool .*/ function isUserDefined(){}
	public /*. bool .*/ function isInstantiable(){}
	public /*. bool .*/ function hasConstant(/*. string .*/ $name){}
	public /*. bool .*/ function hasMethod(/*. string .*/ $name){}
	public /*. bool .*/ function hasProperty(/*. string .*/ $name){}
	public /*. string .*/ function getFileName(){}
	public /*. int .*/ function getStartLine(){}
	public /*. int .*/ function getEndLine(){}
	public /*. string .*/ function getDocComment(){}
	public /*. ReflectionMethod .*/ function getConstructor(){}
	public /*. ReflectionMethod .*/ function getMethod(/*. string .*/ $name)
		/*. throws ReflectionException .*/ {}
	public /*. array[int]ReflectionMethod .*/ function getMethods(){}
	public /*. ReflectionProperty .*/ function getProperty(/*. string .*/ $name)
		/*. throws ReflectionException .*/ {}
	public /*. array[int]ReflectionProperty .*/ function getProperties($filter = 0){}
	/**
	 * Returns the list of class constants:
	 * the key is the constants' name, the value is its value.
	 */
	public /*. array[string]mixed .*/ function getConstants(){}
	public /*. mixed .*/ function getConstant(/*. string .*/ $name){}
	public /*. array[int]ReflectionClass .*/ function getInterfaces(){}
	public /*. bool .*/ function isInterface(){}
	public /*. bool .*/ function isAbstract(){}
	public /*. bool .*/ function isFinal(){}
	public /*. int .*/ function getModifiers(){}
	public /*. bool .*/ function isInstance(/*. object .*/ $obj){}
	public /*. object .*/ function newInstance(/*. args .*/){}
	public /*. object .*/ function newInstanceArgs(/*. array[int]mixed .*/ $args_){}
	public /*. ReflectionClass .*/ function getParentClass(){}
	public /*. bool .*/ function isSubclassOf(ReflectionClass $class_){}
	/**
	 * Returns the static properties.
	 * Static properties that lack an initial value explicitly defined
	 * are set to NULL.
	 */
	public /*. array[string]mixed .*/ function getStaticProperties(){}
	public /*. mixed .*/ function getStaticPropertyValue(/*. string .*/ $name /*., args .*/){}
	public /*. void .*/ function setStaticPropertyValue(/*. string .*/ $name, /*. mixed .*/ $value){}
	public /*. array[string]mixed .*/ function getDefaultProperties(){}
	public /*. bool .*/ function isIterateable(){}
	public /*. bool .*/ function implementsInterface(/*. string .*/ $name){}
	public /*. ReflectionExtension .*/ function getExtension(){}
	public /*. string .*/ function getExtensionName(){}
	public /*. string .*/ function getNamespaceName(){}
	public /*. string .*/ function getShortName(){}
	public /*. boolean .*/ function inNamespace(){}
	public /*. array[int]string .*/ function getInterfaceNames(){}
}

class Reflection
{
	static /*. string .*/ function export(/*. Reflector .*/ $reflector,
		$return_ = FALSE){}
	static /*. array[int]string .*/ function getModifierNames(/*. int .*/ $modifiers){}
}


class ReflectionObject extends ReflectionClass
{
	const
		IS_IMPLICIT_ABSTRACT = 16,
		IS_EXPLICIT_ABSTRACT = 32,
		IS_FINAL = 64;

	public /*. void .*/ function __construct(/*. object .*/ $object_)
	{ parent::__construct(""); }
	public /*. string .*/ function __toString(){}
	// First arg must be mixed to comply with ReflectionClass::export():
	public static /*. string .*/ function export(
		/*. mixed .*/ $object_,
		$return_ = FALSE){}
}

