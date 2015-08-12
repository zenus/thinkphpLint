<?php
require_once __DIR__ . "/../../stdlib/errors.php";

// Expected exception names:
const EE = "ErrorException", IE = "InternalException";

/**
 * Checks if the given chunk of code throws the expected exception.
 * @param string $php_code Chunk of PHP code.
 * @param string $ex Name of the expected exception.
 * @return void
 * @throws RuntimeException
 */
function test($php_code, $ex) {
	try { eval($php_code); }
	catch(Exception $e) {
		echo $php_code . " ===> " . $e->getMessage(), "\n";
		if(get_class($e) === $ex )
			return;
		else
			throw new RuntimeException("test failed:"
				. "\ngot: ". get_class($e)
				. "\nexp: $ex"
				. "\ndetails: $e");
	}
	throw new RuntimeException("test: failed: missing expected exception $ex");
}

// unchecked:
test("echo 1 / 0;", IE);
test("echo 1 % 0;", IE);
test("function f2(\$x){} f2();", IE); // should never happen on a validated source, but see call_user_func()
test("\$a=[1,2,3]; echo \$a[999];", IE);
test("\$a=[1,2,3]; echo \$a[fopen('".__FILE__."', 'r')];", IE);
test("\$a=[1,2,3]; echo \$a[array()];", IE);
//test("\$a=NULL; \$a[0];", IE); // PASSES! (there is already a bug open for that marked as feature)
test("echo \$undef_var;", IE);
test("\$a=NULL; \$a->p;", IE);
//test("\$a=NULL; \$a->m();", IE);  // FATAL!
//test("require '???';", IE); // FATAL!
test("include '???';", IE);
test("\$o=new stdClass(); echo \$o;", IE);
test("const M_PI = 0;", IE);
test("echo \$unknownvar;", IE); // should never happen on a validated source
test("echo \$unknownvar[0];", IE); // should never happen on a validated source
test("echo \$_GET[0];", IE);
test("echo array();", IE);
test("call_user_func('??');", IE);

// checked:
test("fopen(NULL, 'r');", EE);
test("fopen(array(), 'r');", EE);
test("fopen(3.14, 'r');", EE);
test("fopen('', 'r');", EE);
test("fopen('???', 'r');", EE);
test("unserialize('???');", EE);
test("hex2bin('??');", EE);
test("array_chunk(array(),-1);", EE);
