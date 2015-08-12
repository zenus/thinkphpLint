<?php

/*. require_module 'standard'; .*/

class NotAnException {}
class AException extends Exception {}
class BException extends AException {}
class CException extends BException {}

throw new NotAnException();

function f1()
/*. throws AException, BException .*/
{ }


function test_good()
# More specialized exceptions should be cought first.
{
	try { f1(); }
	catch( BException $e ){}
	catch( AException $e ){}
}

function test_wrong_order()
# More general (i.e. parent) exceptions hide the more specialized ones.
{
	try { f1(); }
	catch( AException $e ){}
	catch( BException $e ){}  # this branch never used
}

function test_catch_all()
# Lazy programmers prefers to catch Exception.
{
	try { f1(); }
	catch( Exception $e ){}
}

function test_catch_partial()
# Uncought exceptions are exported by the func/method.
{
	try { f1(); }
	catch( BException $e ){}
}

class C1 {
	public function m() /*. throws BException .*/{}
}

class C2 extends C1 {
	public function m() /*. throws BException .*/{}
}

class C3 extends C1 {
	public function m(){}
}

class C4 extends C1 {
	public function m() /*. throws AException, BException, CException .*/{}
}

class C5 extends C1 {
	public function m() /*. throws CException .*/{}
}

class C6 extends C1 {
	public function m() /*. throws AException .*/{}
}

interface I1 {
	public function m() /*. throws BException .*/ ;
}

class CI1 implements I1 {
	public function m() /*. throws BException .*/{}
}

class CI2 implements I1 {
	public function m(){}
}

class CI3 implements I1 {
	public function m() /*. throws AException, BException, CException .*/{}
}

/*. forward void function f2() throws BException, CException; .*/

/*. void .*/ function f2()
/*. throws AException, BException .*/
{
}

class C7 {
	/*.
		forward void function m() throws BException, CException;
		forward void function n() throws BException, CException;
	.*/

	public function m()
	/*. throws BException .*/
	{}

	public function n()
	/*. throws AException, BException .*/
	{}
}


# Guessed functions cannot throw exceptions:
# Ok:
guessed_func();
function guessed_func(){}
# BAD:
guessed_func2();
function guessed_func2(){ throw new Exception(); }
guessed_func3();
function guessed_func3()/*. throws Exception .*/{ throw new Exception(); }


# Guessed methods cannot throw exceptions:
class CheckExceptionsInGuessedMethods {

	function m1(){
		$this->m2();
		$this->m3();
		$this->m4();
	}

	# Ok:
	function m2(){}

	# BAD:
	function m3(){ throw new Exception(); }
	function m4() /*. throws Exception .*/ { throw new Exception(); }
}


?>
