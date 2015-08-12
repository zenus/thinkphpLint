<?php

if( function_exists("bcadd") ){
	echo "bcadd extension already available - skip tests\n";
	exit(0);
}

require_once __DIR__ . "/../../stdlib/bcmath-for-int-replacement.php";


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
test(bcadd(3.14, "0"), "3"); // 3.14 --> 3

test(bcadd("0", "0"), "0");
test(bcadd("0", 0), "0");
test(bcadd("0", 0.0), "0");
test(bcadd("0", -0.9), "0");

test(bcadd("2","3"), "5");
test(bcsub("2","3"), "-1");
test(bcmul("2","3"), "6");
test(bcdiv("7","3"), "2");
test(bcmod("7","3"), "1");
//test(bcmod("1","0"), "?"); // InvalidArgumentException

// THE END
