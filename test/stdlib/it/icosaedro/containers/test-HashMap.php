<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

use RuntimeException;
use it\icosaedro\containers\Arrays;
use it\icosaedro\utils\Date;
use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\utils\Timer;
use it\icosaedro\utils\Statistics1D;


function testWithStringKeys()
{
	$m = new HashMap();

	$m->put("one", 1);
	$m->put("two", 2);
	$m->put("three", 3);
	$m->put("four", 4);

	$m->remove("three");

	TU::test($m->count(), 3);

	$keys = cast("string[int]", $m->getKeys());
	TU::test(
		Arrays::sortArrayOfString($keys),
		array("four", "one", "two"));

	$values = cast("int[int]", $m->getElements());
	TU::test(
		Arrays::sortArrayOfInt($values),
		array(1, 2, 4));

	if( ! ( $m->containsKey("one") and ! $m->containsKey("five") ) )
		throw new RuntimeException("failed");
}


function testWithDateKeys()
{
	echo "Today: ", Date::today(), "\n";
	$m = new HashMap();

	$m->put(Date::today(), "today");
	$m->put(new Date(2012, 1, 1), "year 2012 begins");
	$m->put(new Date(2011, 12, 31), "year 2011 ends");
	$m->put(new Date(2012, 2, 29), "leap day of the 2012");

	$m->remove(Date::today());

	echo "Count=", $m->count(), "\n";

	echo "Keys:\n";
	$keys = $m->getKeys();
	foreach($keys as $k)
		echo "   ", (string) $k, "\n";

	echo "Values:\n";
	$values = $m->getElements();
	foreach($values as $v)
		echo "   ", (int) $v, "\n";
	
	echo "Pairs:\n";
	$pairs = $m->getPairs();
	foreach($pairs as $pair)
		echo "   ", (string) $pair[0], ": ", (string) $pair[1], "\n";

	if( ! ( $m->containsKey(new Date(2012, 1, 1)) and ! $m->containsKey(Date::today()) ) )
		throw new RuntimeException("failed");
}


function testWithRandomNums()
{
	srand(12345);
	$m = new HashMap();
	$stat = new Statistics1D();
	$t = new Timer(TRUE);
	echo "Inserting... ";
	for($i = 10000; $i > 0; $i--){
		$r = rand();
		$stat->put($r);
		$k = "s" . $r;
		$v = $r;
		$m->put($k, $v);
	}
	echo "elapsed ", $t->elapsedMilliseconds(), " ms\n";
	echo "Random nums stat: ", $stat, "\n";

	srand(12345);
	echo "Scanning... ";
	$t->reset();
	$t->start();
	for($i = 10000; $i > 0; $i--){
		$r = rand();
		$k = "s" . $r;
		$v = $r;
		$v2 = $m->get($k);
		if( $v2 !== $v ){
			echo "ERROR: values differs: expected $v but found $v2\n";
		}
	}
	echo "elapsed ", $t->elapsedMilliseconds(), " ms\n";
}

class testHashMap extends TU {
	function run()
	{
		testWithStringKeys();
		testWithDateKeys();
		testWithRandomNums();
	}
}

$tu = new testHashMap();
$tu->start();
