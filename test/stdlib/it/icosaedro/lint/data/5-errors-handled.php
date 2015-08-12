<?php

/*. require_module 'standard'; .*/
require_once __DIR__ . "/../../../../../../stdlib/errors.php";


trigger_error("", 0);

trigger_error("", E_ERROR);

trigger_error("", E_USER_ERROR);

fopen("", "");


function f() /*. throws ErrorException .*/
{
	trigger_error("", 0);

	trigger_error("", E_ERROR);

	trigger_error("", E_USER_WARNING);

	trigger_error("", E_USER_ERROR);

	fopen("", "");
}


function g()
{
	f();
}


function h()
/*. throws ErrorException .*/
{
	f();
}


class MyClass {
	/*. void .*/ function m() /*. throws ErrorException .*/
	{
		trigger_error("", 0);

		trigger_error("", E_ERROR);

		trigger_error("", E_USER_WARNING);

		trigger_error("", E_USER_ERROR);

		fopen("", "");
	}
}


class MySecondClass extends MyClass {
	function m()
	{ }
}

class MyThirdClass extends MyClass {
	function m() /*. throws ErrorException .*/
	{ }
}


f();

$o = new MyClass();
$o->m();

@f();

@$o->m();

trigger_error("", 12345);

