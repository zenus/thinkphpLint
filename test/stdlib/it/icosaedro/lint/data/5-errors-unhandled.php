<?php

/*. require_module 'standard'; .*/


trigger_error("", 0);

trigger_error("", E_ERROR);

trigger_error("", E_USER_ERROR);

fopen("", "");


function f()
{
	trigger_error("", 0);

	trigger_error("", E_ERROR);

	trigger_error("", E_USER_WARNING);

	trigger_error("", E_USER_ERROR);

	fopen("", "");
}


class MyClass {
	/*. void .*/ function m()
	{
		trigger_error("", 0);

		trigger_error("", E_ERROR);

		trigger_error("", E_USER_WARNING);

		trigger_error("", E_USER_ERROR);

		fopen("", "");
	}
}


f();

$o = new MyClass();
$o->m();

@f();

@$o->m();

trigger_error("", 12345);

