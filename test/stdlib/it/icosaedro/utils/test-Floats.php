<?php

/*
Papers describing the algorithms for floats <=> strings conversions:
- Guy L. Steele, Jr., Jon L. White. "How to print floating-point numbers 
accurately". 
http://portal.acm.org/citation.cfm?id=93559&coll=portal&dl=ACM&CFID=551188&CFTOKEN=64149307
- William D. Clinger. "How to read floating point numbers accurately" 
http://portal.acm.org/citation.cfm?id=93557&coll=portal&dl=ACM&CFID=1476301&CFTOKEN=64297675

*/

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\TestUnit as TU;

class testFloats extends TU {
	function run(){

		#ini_set("precision", "17");

		$zn = -1 * 0.0; // yields the zero negative -0.0

		TU::test( Floats::compare(-INF, -INF) == 0, true );
		TU::test( Floats::compare(-INF, $zn) < 0, true );
		TU::test( Floats::compare(-INF, 0.0) < 0, true );
		TU::test( Floats::compare(-INF, 1.0) < 0, true );
		TU::test( Floats::compare(-INF, NAN) < 0, true );
		TU::test( Floats::compare($zn, -INF) > 0, true );
		TU::test( Floats::compare($zn, $zn) == 0, true );
		TU::test( Floats::compare($zn, 0.0) < 0, true );
		TU::test( Floats::compare($zn, 1.0) < 0, true );
		TU::test( Floats::compare($zn, NAN) < 0, true );
		TU::test( Floats::compare(0.0, -INF) > 0, true );
		TU::test( Floats::compare(0.0, $zn) > 0, true );
		TU::test( Floats::compare(0.0, 0.0) == 0, true );
		TU::test( Floats::compare(0.0, 1.0) < 0, true );
		TU::test( Floats::compare(0.0, NAN) < 0, true );
		TU::test( Floats::compare(1.0, -INF) > 0, true );
		TU::test( Floats::compare(1.0, $zn) > 0, true );
		TU::test( Floats::compare(1.0, 0.0) > 0, true );
		TU::test( Floats::compare(1.0, 1.0) == 0, true );
		TU::test( Floats::compare(1.0, NAN) < 0, true );
		TU::test( Floats::compare(NAN, -INF) > 0, true );
		TU::test( Floats::compare(NAN, $zn) > 0, true );
		TU::test( Floats::compare(NAN, 0.0) > 0, true );
		TU::test( Floats::compare(NAN, 1.0) > 0, true );
		TU::test( Floats::compare(NAN, NAN) == 0, true );

		TU::test( Floats::fromHex("0x0"), 0.0 );
		TU::test( Floats::fromHex("0x0.0"), 0.0 );
		TU::test( Floats::fromHex("0x0p32"), 0.0 );
		TU::test( Floats::fromHex("0x1.0p-3"), 0.125 );

		TU::test( Floats::toHex($zn), "-0x0.0");
		TU::test( Floats::fromHex( Floats::toHex(0.0) ), 0.0);
		TU::test( Floats::fromHex( Floats::toHex($zn) ), $zn);
		TU::test( Floats::fromHex( Floats::toHex(INF) ), INF);
		TU::test( Floats::fromHex( Floats::toHex(-INF) ), -INF);
		TU::test( Floats::fromHex( Floats::toHex(NAN) ), NAN);
		TU::test( Floats::fromHex( Floats::toHex(-1.0) ), -1.0);
		TU::test( Floats::fromHex( Floats::toHex(1.0) ), 1.0);
		TU::test( Floats::fromHex( Floats::toHex(0.1) ), 0.1);
		TU::test( Floats::fromHex( Floats::toHex(1e25 * 1e-25) ), 1e25 * 1e-25);
		TU::test( Floats::fromHex( Floats::toHex(M_PI) ), M_PI);
		TU::test( Floats::fromHex( Floats::toHex(1.0e300) ), 1.0e300);
		TU::test( Floats::fromHex( Floats::toHex(-1.0e-300) ), -1.0e-300);

		TU::test(Floats::toLiteral(1.0), "1.0");
		TU::test(Floats::toLiteral(1.5), "1.5");
		TU::test(Floats::toLiteral(1e0), "1.0");
		TU::test(Floats::toLiteral(1e10), "10000000000.0");
		TU::test(Floats::toLiteral(0.125), "0.125");
		TU::test(Floats::toLiteral(0.0625), "0.0625");
		TU::test(Floats::toLiteral(0.1), "0.1000000000000000055511151231257827021181583404541015625");

		TU::test(Floats::parseFloat("0"), 0.0);
		TU::test(Floats::parseFloat("0.0"), 0.0);
		TU::test(Floats::parseFloat("+0.0"), 0.0);
		TU::test(Floats::parseFloat("-0"), $zn);
		TU::test(Floats::parseFloat("1.5e12"), 1.5e12);
		TU::test(Floats::parseFloat("1.5e-12"), 1.5e-12);
		TU::test(Floats::parseFloat("1.5e+12"), 1.5e+12);
		TU::test(Floats::parseFloat("nan"), NAN);
		TU::test(Floats::parseFloat("inf"), INF);
		TU::test(Floats::parseFloat("-inf"), -INF);
	}
}
$tu = new testFloats();
$tu->start();
