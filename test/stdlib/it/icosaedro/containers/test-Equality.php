<?php

namespace it\icosaedro\containers;

use it\icosaedro\containers\Equality;
use it\icosaedro\utils\TestUnit as TU;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

use it\icosaedro\utils\Date;

class testEquality extends TU {

	function run()
	{
		TU::test( Equality::areEqual(true, true), true);
		TU::test( Equality::areEqual(false, false), true);
		TU::test( Equality::areEqual(false, true), false);
		TU::test( Equality::areEqual(true, 1), false);
		TU::test( Equality::areEqual(false, 0), false);
		TU::test( Equality::areEqual(1.0, 1.0), true);
		TU::test( Equality::areEqual(NULL, NULL), true);
		TU::test( Equality::areEqual(NULL, ""), false);
		TU::test( Equality::areEqual(0, "0"), false);

		$today = Date::today();
		$d1 = new Date(2012, 2, 3);
		$d2 = new Date(2012, 2, 3);
		$d3 = new Date(2012, 2, 4);
		TU::test( Equality::areEqual($today, $today), true);
		TU::test( Equality::areEqual($d1, $d1), true);
		TU::test( Equality::areEqual($d1, $d2), true);
		TU::test( Equality::areEqual($d1, $d3), false);
	}

}

$tu = new testEquality();
$tu->start();
