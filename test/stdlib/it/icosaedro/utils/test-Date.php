<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\Date;
use it\icosaedro\utils\TestUnit as TU;

const CN = "it\\icosaedro\\utils\\Date";

class testDate extends TU {
	function run() /*. throws \Exception .*/
	{
		$d = new Date(0, 1, 1);
		TU::test($d."", "0000-01-01");

		$d = new Date(9999, 12, 31);
		TU::test($d."", "9999-12-31");

		$d = new Date(2012, 2, 29);
		TU::test($d."", "2012-02-29");

		$d = Date::parse("2012-02-29");
		TU::test($d."", "2012-02-29");

		$d = Date::parse("2012-2-29");
		TU::test($d."", "2012-02-29");

		$a = new Date(2012, 02, 29);
		$a2 = new Date(2012, 02, 29);
		$b = new Date(2012, 03, 01);
		TU::test($a->compareTo($b) < 0, TRUE);
		TU::test($b->compareTo($a) > 0, TRUE);
		TU::test($a->compareTo($a) == 0, TRUE);
		TU::test($a->equals($a), TRUE);
		TU::test($a->equals($b), FALSE);
		TU::test($b->equals($b), TRUE);
		TU::test($a->equals($a2), TRUE);
		TU::test($a2->equals($a), TRUE);

		TU::test( cast(CN, unserialize( serialize($a) ) )->equals($a), TRUE);
		TU::test( cast(CN, unserialize( serialize($b) ) )->equals($b), TRUE);
		TU::test( cast(CN, unserialize( serialize($a2) ) )->equals($a), TRUE);
	}
}
$tu = new testDate();
$tu->start();
