<?php
/**
 *  Forward references allowed in PHPLint/PHP 4: {@link MY_CONST},
 *  {@link MyFunc()}, {@link MyClass}, {@link MyClass::MyMethod()}.
 *  @package ForwardDeclsTest
 */

define("MY_CONST", 123);

/**
 *  @return MyClass $wrongParam  In this case the class must be resolvable,
 *          and because it is not, gives error.
 */
function MyFunc(){}

class MyClass {
	/*. void .*/ function MyMethod(){}
}
