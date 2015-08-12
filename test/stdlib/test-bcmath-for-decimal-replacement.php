<?php

if( function_exists("bcadd") ){
	echo "bcadd extension already available - skip tests\n";
	exit(0);
}

require_once __DIR__ . "/../../stdlib/bcmath-for-decimal-replacement.php";


use it\icosaedro\utils\Strings;

/**
 * @param string $got
 * @param string $exp
 * @throws \RuntimeException
 */
function test($got, $exp)
{
	if( $got !== $exp )
		throw new \RuntimeException("test failed:\n"
			."got=".Strings::toLiteral($got)."\n"
			."exp=".Strings::toLiteral($exp)."\n");
}

bcscale(0);
bcscale("0");
//bcscale("2"); // exception
bcadd(0,0,0);
//bcadd(0,0,8); // exception

test(bcadd("0", "0"), "0");
test(bcadd("0", 0), "0");
test(bcadd("0", 0.0), "0");
test(bcadd("0", -0.9), "0");

test(bcadd("2","3"), "5");
test(bcsub("2","3"), "-1");
test(bcmul("2","3"), "6");
test(bcdiv("7","3"), "2");

// Reset default scale:
bcscale(0);
test(bcadd("1.234", "5"), "6");
test(bcadd("1.234", "5", 4), "6.2340");

test( (string) bccomp('1', '2'), "-1");
test( (string) bccomp('1.00001', '1', 3), "0");
test( (string) bccomp('1.00001', '1', 5), "1");


test(bcdiv('105', '6.55957', 3), "16.007");

test(bcmul('1.34747474747', '35', 3), "47.161");
test(bcmul('2', '4'), "8");

test(bcsqrt('2', 3), "1.414");
//test(bcsqrt('-1'), "?"); // InvalidArgumentException

test(bcsub('1.234', '5'), "-3");
test(bcsub('1.234', '5', 4), "-3.7660");

// THE END
