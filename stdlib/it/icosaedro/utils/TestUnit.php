<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../all.php";

use it\icosaedro\containers\Equality;
use it\icosaedro\containers\Printable;

/**
	Helps in testing and debugging programs. Basically, this class provides the
	test($got, $expected) function that checks the got value against the
	$expected value: if they do not match exactly, a RuntimeException is
	thrown.

	Another useful pattern of usage of this class is to implement the run()
	method and do your test inside that method, then you may instantiate the
	implemented class and call the start() method that, in turn, calls run().
	The start() method catches any exception and generates a stack trace on
	stderr, then the program terminates with an exit status of 1. If, instead,
	the test succeeded, the exit status is 0.

	Suppose for example you have a class MyClass. The test unit file
	test-MyClass.php may look like this:

	<pre>
	&lt;?php
	namespace com\acme\tools;
	require_once __DIR__ . "/MyClass.php";
	require_once __DIR__ . "/../../../it/icosaedro/utils/TestUnit.php";
	use it\icosaedro\utils\TestUnit;
	class testMyClass extends TestUnit {
		function run() /&#42;. throws \Exception .&#42;/
		{
			# Do any test you want here. You may send
			# messages to stdout, and generate any
			# exception on error. Exceptions are caught
			# by the start() method, displayed with stack
			# trace, and then exit(1).
		}
	}
	$tu = new testMyClass();
	$tu-&gt;start();
	</pre>

	This script now either exits with status code 0 on success, or it generates
	a stack trace and exits with state 1 on failure. On PHP fatal error the
	exit status is 255.
	
	Manual tests may then be
	performed launching the script from the command line:
	<pre>
	$ php test-MyClass.php
	</pre>

	Automated scripts may discard any message sent to stdout
	and may generate a summary (Bash):

	<pre>
	#!/bin/bash
	count=0
	err=0
	for f in test-*.php; do
		count=$((count + 1))
		php $f
		if [ $? -ne 0 ]; then
			errcount=$((err + 1))
		fi
	done
	echo "Overall test result: $count performed, $err failed"
	if [ $err -gt 0 ]; then
		exit 1
	fi
	</pre>
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:20:37 $
*/
abstract class TestUnit {


	/**
		Retrieves a detailed textual description of a generic value.
		@param mixed $v Generic value.
		@param int $max_string_length Truncates strings longer than that.
		@param int $max_deep Maximum recursion deep on arrays.
		@param int $max_elements Maximum number of elements to display in arrays.
		@return string Readable representation of the passed value. Strings of
		characters are rendered as PHP literal strings enclosed between
		double-quotes with control characters and non-ASCII characters in
		octal notation "\000". Arrays are rendered recursively along with their
		keys and elements. Float values always have a decimal point or the
		scientific notation. Objects are rendered as class name and properties
		with values; objects that implements the
		{@link it\icosaedro\containers\Printable}
		interface are rendered with __toString().
	*/
	static function dump($v, $max_string_length = 100, $max_deep = 5, $max_elements = 10)
	{
		if( is_null($v) ){
			return "null(NULL)";
		} else if( is_bool($v) ){
			if( $v === FALSE )
				return "FALSE";
			else
				return "TRUE";
		} else if( is_int($v) ){
			return "".(int)$v;
		} else if( is_float($v) ){
			$s = (string) $v;
			if( strpos($s, ".") === FALSE && strpos($s, "e") === FALSE )
				// Makes float recognizable from int adding decimal:
				$s .= ".0";
			return $s;
		} else if( is_string($v) ){
			$s = (string) $v;
			if( strlen($s) > $max_string_length)
				$s = substr($s, 0, $max_string_length) . "[...]";
			return "\"" . addcslashes($s, "\000..\037\\\$\"\177..\377") . "\"";
		} else if( is_resource($v) ){
			return "resource(" . get_resource_type( cast("resource", $v) ) . ")";
		} else if( is_object($v) ){
			$obj = cast("object", $v);
			if( $obj instanceof \it\icosaedro\containers\Printable ){
				$p = cast("it\\icosaedro\\containers\\Printable", $obj);
				return $p->__toString();
			} else {
				$a = get_object_vars(cast("object", $v));
				$s = get_class($obj) . "{";
				if( $max_deep <= 0 )
					return $s . "...}";
				$n = 0;
				foreach($a as $property_name => $property_value){
					if( $n > 0 )
						$s .= " ";
					if( $n < $max_elements )
						$s .= "$" . $property_name . "="
						. self::dump($property_value, $max_string_length, $max_deep-1, $max_elements) . ";";
					else {
						$s .= "...";
						break;
					}
					$n++;
				}
				return $s . "}";
			}
		} else if( is_array($v) ){
			$s = "array(";
			if( $max_deep <= 0 )
				return $s . "...)";
			$n = 0;
			foreach(cast("array[]", $v) as $k => $e){
				if( $n > 0 )
					$s .= ", ";
				if( $n < $max_elements )
					$s .= self::dump($k) . "=>" . self::dump($e, $max_string_length, $max_deep-1, $max_elements);
				else {
					$s .= "...";
					break;
				}
				$n++;
			}
			$s .= ")";
			return $s;
		} else {
			return gettype($v) . "(...)";
		}
			
	}


	/**
		Checks if the two values passed as argument are the same value. Uses
		{@link it\icosaedro\containers\Equality::areEqual()} as comparator.
		@param mixed $got Actual value generated by the program under test.
		@param mixed $expected Expected value.
		@return void
		@throws \RuntimeException If the comparison fails, either because the
		two values are of different type or carry different values. The message
		of the exception includes the expected value and the actual value.
	*/
	static function test($got, $expected)
	{
		if( ! Equality::areEqual($got, $expected) ){
			throw new \RuntimeException(
				  "\n     GOT: " . self::dump($got)
				. "\nEXPECTED: " . self::dump($expected) . "\n");
		}
	}
	
	
	/**
		Performs any test. You may implement this method an put all your tests
		here. Inside this implemented method, you may generate any diagnostic
		message. If a test fails, you should throw an exception. This method,
		in turns, gets called by the start() method that "wraps" run(),
		handles exceptions and sets a proper exit status code.
		@return void
		@throws \Exception
	*/
	abstract function run();
	
	
	/**
		Calls the implemented run() method. On exception, displays on stderr
		the stack trace and exits the script with status code 1.
		@return void
	*/
	function start()
	{
		try {
			$this->run();
		}
		catch(\Exception $e){
			error_log($e->__toString());
			error_log("==== TEST FAILED ====\n");
			exit(1);
		}
	}

}
