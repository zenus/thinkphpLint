<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\utils\Timer;


/**
 * Eratosthenes sieve algorithm.
 * @param int $n Search prime numbers up to this limit.
 * @return BitSet Found prime numbers.
 */
function primes($n)
{
	$b = new BitSet();
	for($i = 2; $i <= $n; $i++)
		$b->set($i);
	$i = 2;
	while( $i * $i <= $n ){
		if( $b->get($i) ){
			$k = 2 * $i;
			while( $k <= $n ){
				$b->clear($k);
				$k += $i;
			}
		}
		$i++;
	}
	return $b;
}


class testBitSet extends TU {

	function run()
	{
		set_time_limit(600);

		#$t = new Timer();
		#$t->start();
		$b = primes(100);
		#echo "$t\n";

		#echo $b, "\n";
		TU::test("$b", "{2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97}");

		#echo "magnitude = ", $b->magnitude(), "\n";
		TU::test($b->magnitude(), 98);

		#echo "size = ", $b->size(), "\n";
		TU::test($b->size(), 101);

		#echo "cardinality = ", $b->cardinality(), "\n";
		TU::test($b->cardinality(), 25);

		$b2 = new BitSet();
		for($i = $b->magnitude() - 1; $i >= 0; $i--)
			if( $b->get($i) )
				$b2->set($i);
		TU::test($b->getHash(), $b2->getHash());
		TU::test($b2->equals($b), TRUE);

		$b2 = BitSet::parse( $b->__toString() );
		TU::test($b->getHash(), $b2->getHash());
		TU::test($b2->equals($b), TRUE);

		$b2 = clone $b;
		TU::test($b->getHash(), $b2->getHash());
		TU::test($b2->equals($b), TRUE);

		$a = BitSet::parse("{0, 3, 5, 45, 46}");
		$b = BitSet::parse("{1, 2, 3, 45, 99}");

		$c = clone $a;
		$c->union($b);
		TU::test("$c", "{0, 1, 2, 3, 5, 45, 46, 99}");

		$c = clone $a;
		$c->intersection($b);
		TU::test("$c", "{3, 45}");

		$c = clone $a;
		$c->reverse($b);
		TU::test("$c", "{0, 1, 2, 5, 46, 99}");

		# Empty set:
		$e = new BitSet();
		TU::test($e->cardinality(), 0);
		TU::test($e->magnitude(), 0);

		# Emtpty set combined with $a:
		
		$c = clone $e;
		$c->union($a);
		TU::test($c, $a);

		$c = clone $e;
		$c->intersection($a);
		TU::test($c, $e);

		$c = clone $e;
		$c->reverse($a);
		TU::test($c, $a);

		# $a combined with empty set:
		
		$c = clone $a;
		$c->union($e);
		TU::test($c, $a);

		$c = clone $a;
		$c->intersection($e);
		TU::test($c, $e);

		$c = clone $a;
		$c->reverse($e);
		TU::test($c, $a);
	}

}

$tu = new testBitSet();
$tu->start();

