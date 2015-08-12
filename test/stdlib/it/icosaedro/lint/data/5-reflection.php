<?php

/*
	2007-06-02  These examples come from
	www.php.net/manual/en/language.oop5.reflection.php
	They are all about reflection classes. Only minor changes
	made to make IDs different from PHPLint keywords:

	String --> String_
	Class  --> Class_
	$class --> $class_
	Object --> Object_
*/

/*.
	require_module 'standard';
	require_module 'standard_reflection';
.*/

# Example 19.33. Basic usage of the reflection API

Reflection::export(new ReflectionClass('Exception'));




# Example 19.34. Using the ReflectionFunction class

/**
 * A simple counter
 *
 * @return    int
 */
function counter() 
{
    static $c = 0;
    return $c++;
}

// Create an instance of the Reflection_Function class
$func = new ReflectionFunction('counter');

// Print out basic information
printf(
    "===> The %s function '%s'\n".
    "     declared in %s\n".
    "     lines %d to %d\n",
    $func->isInternal() ? 'internal' : 'user-defined',
    $func->getName(),
    $func->getFileName(),
    $func->getStartLine(),
    $func->getEndline()
);

// Print documentation comment
printf("---> Documentation:\n %s\n", var_export($func->getDocComment(), 1));

// Print static variables if existant
if ($statics = $func->getStaticVariables())
{
    printf("---> Static variables: %s\n", var_export($statics, 1));
}

// Invoke the function
printf("---> Invokation results in: ");
var_dump($func->invoke());


// you may prefer to use the export() method
echo "\nReflectionFunction::export() results:\n";
echo ReflectionFunction::export('counter', false);





#### Example 19.35. Using the ReflectionParameter class

function foo($a, $b, $c) { }
function bar(Exception $a, &$b, $c) { }
function baz(ReflectionFunction $a, $b = 1, $c = null) { }
function abc() { }

// Create an instance of Reflection_Function with the
// parameter given from the command line.    
$reflect = new ReflectionFunction($argv[1]);

echo $reflect;

foreach ($reflect->getParameters() as $i => $param) {
    printf(
        "-- Parameter #%d: %s {\n".
        "   Class: %s\n".
        "   Allows NULL: %s\n".
        "   Passed to by reference: %s\n".
        "   Is optional?: %s\n".
        "}\n",
        $i, 
        $param->getName(),
        var_export($param->getClass(), 1),
        var_export($param->allowsNull(), 1),
        var_export($param->isPassedByReference(), 1),
        $param->isOptional() ? 'yes' : 'no'
    );
}



#### Example 19.36. Using the ReflectionClass class

interface Serializable
{
    // ...
}

class Object_
{
    // ...
}

/**
 * A counter class
 */
class Counter extends Object_ implements Serializable
{
    const START = 0;
    private static $c = Counter::START;

    /**
     * Invoke counter
     *
     * @access  public
     * @return  int
     */
    public function count() {
        return self::$c++;
    }
}

// Create an instance of the ReflectionClass class
$class_ = new ReflectionClass('Counter');

// Print out basic information
printf(
    "===> The %s%s%s %s '%s' [extends %s]\n" .
    "     declared in %s\n" .
    "     lines %d to %d\n" .
    "     having the modifiers %d [%s]\n",
        $class_->isInternal() ? 'internal' : 'user-defined',
        $class_->isAbstract() ? ' abstract' : '',
        $class_->isFinal() ? ' final' : '',
        $class_->isInterface() ? 'interface' : 'class',
        $class_->getName(),
        var_export($class_->getParentClass(), 1),
        $class_->getFileName(),
        $class_->getStartLine(),
        $class_->getEndline(),
        $class_->getModifiers(),
        implode(' ', Reflection::getModifierNames($class_->getModifiers()))
);

// Print documentation comment
printf("---> Documentation:\n %s\n", var_export($class_->getDocComment(), 1));

// Print which interfaces are implemented by this class
printf("---> Implements:\n %s\n", var_export($class_->getInterfaces(), 1));

// Print class constants
printf("---> Constants: %s\n", var_export($class_->getConstants(), 1));

// Print class properties
printf("---> Properties: %s\n", var_export($class_->getProperties(), 1));

// Print class methods
printf("---> Methods: %s\n", var_export($class_->getMethods(), 1));

// If this class is instantiable, create an instance
if ($class_->isInstantiable()) {
    $counter = $class_->newInstance();

    echo '---> $counter is instance? '; 
    echo $class_->isInstance($counter) ? 'yes' : 'no';

    echo "\n---> new Object_() is instance? ";
    echo $class_->isInstance(new Object_()) ? 'yes' : 'no';
}



#### Example 19.37. Using the ReflectionMethod class

class Counter2
{
    private static $c = 0;

    /**
     * Increment counter
     *
     * @final
     * @static
     * @access  public
     * @return  int
     */
    final public static function increment()
    {
        return ++self::$c;
    }
}

// Create an instance of the Reflection_Method class
$method = new ReflectionMethod('Counter2', 'increment');

// Print out basic information
printf(
    "===> The %s%s%s%s%s%s%s method '%s' (which is %s)\n" .
    "     declared in %s\n" .
    "     lines %d to %d\n" .
    "     having the modifiers %d[%s]\n",
        $method->isInternal() ? 'internal' : 'user-defined',
        $method->isAbstract() ? ' abstract' : '',
        $method->isFinal() ? ' final' : '',
        $method->isPublic() ? ' public' : '',
        $method->isPrivate() ? ' private' : '',
        $method->isProtected() ? ' protected' : '',
        $method->isStatic() ? ' static' : '',
        $method->getName(),
        $method->isConstructor() ? 'the constructor' : 'a regular method',
        $method->getFileName(),
        $method->getStartLine(),
        $method->getEndline(),
        $method->getModifiers(),
        implode(' ', Reflection::getModifierNames($method->getModifiers()))
);

// Print documentation comment
printf("---> Documentation:\n %s\n", var_export($method->getDocComment(), 1));

// Print static variables if existant
if ($statics= $method->getStaticVariables()) {
    printf("---> Static variables: %s\n", var_export($statics, 1));
}

// Invoke the method
printf("---> Invokation results in: ");
var_dump($method->invoke(NULL));



#### Example 19.38. Using the ReflectionProperty class

class String_
{
    public $length  = 5;
}

// Create an instance of the ReflectionProperty class
$prop = new ReflectionProperty('String_', 'length');

// Print out basic information
printf(
    "===> The%s%s%s%s property '%s' (which was %s)\n" .
    "     having the modifiers %s\n",
        $prop->isPublic() ? ' public' : '',
        $prop->isPrivate() ? ' private' : '',
        $prop->isProtected() ? ' protected' : '',
        $prop->isStatic() ? ' static' : '',
        $prop->getName(),
        $prop->isDefault() ? 'declared at compile-time' : 'created at run-time',
        var_export(Reflection::getModifierNames($prop->getModifiers()), 1)
);

// Create an instance of String_
$obj= new String_();

// Get current value
printf("---> Value is: ");
var_dump($prop->getValue($obj));

// Change value
$prop->setValue($obj, 10);
printf("---> Setting value to 10, new value is: ");
var_dump($prop->getValue($obj));

// Dump object
var_dump($obj);



#### Example 19.39. Using the ReflectionExtension class

// Create an instance of the ReflectionProperty class
$ext = new ReflectionExtension('standard');

// Print out basic information
printf(
    "Name        : %s\n" .
    "Version     : %s\n" .
    "Functions   : [%d] %s\n" .
    "Constants   : [%d] %s\n" .
    "INI entries : [%d] %s\n" .
    "Classes     : [%d] %s\n",
        $ext->getName(),
        $ext->getVersion() ? $ext->getVersion() : 'NO_VERSION',
        sizeof($ext->getFunctions()),
        var_export($ext->getFunctions(), 1),

        sizeof($ext->getConstants()),
        var_export($ext->getConstants(), 1),

        sizeof($ext->getINIEntries()),
        var_export($ext->getINIEntries(), 1),

        sizeof($ext->getClassNames()),
        var_export($ext->getClassNames(), 1)
);



#### Example 19.40. Extending the built-in classes

/**
 * My Reflection_Method class
 */
class My_Reflection_Method extends ReflectionMethod
{
    public $visibility = '';

    public function __construct($o, $m)
    {
        parent::__construct($o, $m);
        $this->visibility= Reflection::getModifierNames($this->getModifiers());
    }
}

/**
 * Demo class #1
 *
 */
class T {
    protected function x() {}
}

/**
 * Demo class #2
 *
 */
class U extends T {
    function x() {}
}

// Print out information
var_dump(new My_Reflection_Method('U', 'x'));

?>
