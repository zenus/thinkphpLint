<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\TestUnit as TU;


/*. void .*/ function test_exception(/*. string .*/ $value, /*. string .*/ $expected)
	/*. throws \Exception .*/
{
	try {
		$i = Integers::parseInt($value);
	}
	catch( \InvalidArgumentException $e ){
		$actual = $e->getMessage();
		if( $actual !== $expected )
			throw new \Exception("test failed for $value: expected \"$expected\" but got \"$actual\"");
		return;
	}
	throw new \Exception("missing expected exception");
}



class testIntegers extends TU {
	function run() /*. throws \Exception .*/ {
		TU::test(Integers::parseInt("0"), 0);
		TU::test(Integers::parseInt("-1"), -1);
		TU::test(Integers::parseInt("+1"), +1);
		TU::test(Integers::parseInt("+1234"), +1234);
		TU::test(Integers::parseInt("-1"), -1);
		TU::test(Integers::parseInt("-1234"), -1234);
		TU::test(Integers::parseInt((string) PHP_INT_MAX), PHP_INT_MAX);
		TU::test(Integers::parseInt((string) (-1 - PHP_INT_MAX)), (-1 - PHP_INT_MAX));
		TU::test(Integers::parseInt("00"), 0);
		TU::test(Integers::parseInt("-00"), 0);
		TU::test(Integers::parseInt("0000000000000000100"), 100);
		TU::test(Integers::parseInt("-0000000000000000100"), -100);
		TU::test(Integers::parseInt("+0000000000000000100"), 100);
		TU::test(Integers::parseInt((string) PHP_INT_MAX), PHP_INT_MAX);
		TU::test(Integers::parseInt((string) (-PHP_INT_MAX-1)), -PHP_INT_MAX-1);


		TU::test(Integers::bitCount(0), 0);
		TU::test(Integers::bitCount(1), 1);
		TU::test(Integers::bitCount(2), 1);
		TU::test(Integers::bitCount(3), 2);
		TU::test(Integers::bitCount(-1), 8*PHP_INT_SIZE);
		TU::test(Integers::bitCount(PHP_INT_MAX), 8*PHP_INT_SIZE - 1);
		TU::test(Integers::bitCount(~PHP_INT_MAX), 1);

		TU::test(Integers::magnitude(0), 0);
		TU::test(Integers::magnitude(1), 1);
		TU::test(Integers::magnitude(2), 2);
		TU::test(Integers::magnitude(3), 2);
		TU::test(Integers::magnitude(5), 3);
		TU::test(Integers::magnitude(PHP_INT_MAX), 8*PHP_INT_SIZE - 1);
		TU::test(Integers::magnitude(~PHP_INT_MAX), 8*PHP_INT_SIZE);

		$BIG = str_repeat("9", strlen((string) PHP_INT_MAX));
		test_exception($BIG, "int out of range: $BIG");
		test_exception("-$BIG", "int out of range: -$BIG");
	}
}
$tu = new testIntegers();
$tu->start();
