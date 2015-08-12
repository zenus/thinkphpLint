<?php

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

/*.
	require_module 'standard';
.*/

require_once __DIR__ . '/test-util.php';
use it\icosaedro\bignumbers\BigInt;

BigInt::$optimize = TRUE;

$n = new BigInt("1");
test("", $n->__toString(), "1");
test("", $n->format(0), "1");
test("", $n->format(1), "0.1");
test("", $n->format(2), "0.01");

$n = new BigInt("-1");
test("", $n->__toString(), "-1");
test("", $n->format(0), "-1");
test("", $n->format(1), "-0.1");
test("", $n->format(2), "-0.01");

$n = new BigInt("0000");
test("", $n->__toString(), "0");
test("", $n->format(0), "0");
test("", $n->format(1), "0.0");
test("", $n->format(2), "0.00");

$n = new BigInt("1");
test("", $n->format(3), "0.001");

$n = new BigInt("12345678900");
test("", $n->format(), "12,345,678,900");
$m = $n->mul($n);
test("", $m->__toString(), "152415787501905210000");
test("", $m->div($n)->__toString(), "12345678900");
test("", $m->div(new BigInt("321"))->__toString(), "474815537389112803");

$n = new BigInt("2");
test("", $n->pow(0)->__toString(), "1");
test("", $n->pow(1)->__toString(), "2");
test("", $n->pow(2)->__toString(), "4");
test("", $n->pow(3)->__toString(), "8");


$start = time();
$range_a = -123;
$range_b = +123;
$steps_n = $range_b-$range_a+1;
echo "Performing extensive tests in the range $range_a..$range_b:\n";

for( $i=$range_a; $i <= $range_b; $i++ ){

	$step = $i-$range_a+1;
	echo "Step $step of $steps_n (", (int)($step/$steps_n*100), "%)\n";

	set_time_limit(100);

	$a = new BigInt( $i );

	# Testing conversion from/to string:
	if( $a->__toString() !== (string) $i ){
		echo "test __toString(): expected $i, got ", $a->__toString(), "\n";
		$err++;
	}

	# Testing minus():
	$c = $a->minus();
	if( $c->__toString() !== (string) (-$i) ){
		echo "test minus(): expected ", (-$i), ", got ", $c->__toString(), "\n";
		$err++;
	}

	for( $j=$range_a; $j <= $range_b; $j++ ){
		#echo "i=$i j=$j\n";

		$b = new BigInt( $j );

		# Testing comparison:
		$cmp = $a->cmp($b);
		if( $cmp < 0  and  $i >= $j
		or $cmp == 0 and $i != $j
		or $cmp > 0 and $i <= $j ){
			echo "test cmp(): error comparing $i with $j: got $cmp\n";
			$err++;
		}

		# Testing addition:
		$c = $a->add($b);
		if( $c->__toString() !== (string) ($i+$j) ){
			echo "test add(): error ($i)+($j) gives ", $c->__toString(), "\n";
			$err++;
		}
		
		# Testing subtraction:
		$c = $a->sub($b);
		if( $c->__toString() !== (string) ($i-$j) ){
			echo "test sub(): error ($i)-($j) gives ", $c->__toString(), "\n";
			$err++;
		}
		
		# Testing multiplication:
		$c = $a->mul($b);
		if( $c->__toString() !== (string) ($i*$j) ){
			echo "test mul(): error ($i)*($j) gives ", $c->__toString(), "\n";
			$err++;
		}

		# Testing division:
		if( $j != 0 ){
			$c = $a->div_rem($b, $rem);
			if( $c->__toString() !== (string) ((int)($i/$j)) ){
				echo "test div_rem(): error ($i)/($j) gives ", $c->__toString(), "\n";
				$err++;
			}
			if( $rem->__toString() !== (string) ($i%$j) ){
				echo "test div_rem(): error ($i)%($j) gives ", $rem->__toString(), "\n";
				$err++;
			}
		}
		
	}
}

echo "Time: ", (time()-$start), " s\n";
echo "Last required time: 25 s (8 s with optimization)\n";
echo "Errors: $err\n";

exit( $err==0? 0:1 );

?>
