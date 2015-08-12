<?php
/**
 *  Unqualified forward references NOT allowed in PHPLint/PHP 5:
 *  {@link MY_CONST},
 *  {@link MyFunc()},
 *  {@link MyClass},
 *  {@link MyClass::MyMethod()}.
 *
 *  Fully qualified forward references allowed in PHPLint/PHP 5:
 *  {@link \MY_CONST},
 *  {@link \MyFunc()},
 *  {@link \MyClass},
 *  {@link \MyClass::MyMethod()},
 *  {@link namespace\MY_CONST},
 *  {@link namespace\MyFunc()},
 *  {@link namespace\MyClass},
 *  {@link namespace\MyClass::MyMethod()}.
 *
 *  @package ForwardDeclsTest
 */

define("MY_CONST", 123);

/**
 *  @return MyClass $wrongParam  Not resolvable.
 *  @return \MyClass $wrongParam  Not resolvable.
 *  @return namespace\MyClass $wrongParam  Not resolvable.
 */
function MyFunc(){}

class MyClass {
	/*. void .*/ function MyMethod(){}
}
