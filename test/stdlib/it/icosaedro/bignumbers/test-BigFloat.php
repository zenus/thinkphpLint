<?php

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

use it\icosaedro\bignumbers\BigInt;
use it\icosaedro\bignumbers\BigFloat;

require_once __DIR__ . '/test-util.php';

ini_set("precision", "12");

define("EPSILON", 1e-9);

BigInt::$optimize = TRUE;


$n = new BigFloat("12.34");
$b = new BigFloat("7");
$q = $n->div_rem($b, 2, $r);
test("div low precision",        "$q rem=$r", "0 rem=12.34");
$q = $n->div_rem($b, 1, $r);
test("div low-border precision", "$q rem=$r", "0 rem=12.34");
$q = $n->div_rem($b, -1, $r);
test("div med precision",        "$q rem=$r", "1.7 rem=0.44");
$q = $n->div_rem($b, -2, $r);
test("div med-border precision", "$q rem=$r", "1.76 rem=0.02");

/********
$price = new BigFloat("56.78");
$VAT_rate = new BigFloat("0.20");
$VAT = $price->mul($VAT_rate)->round(-2);
$total = $price->add($VAT);
echo "Price: ", $price->format(2), "\n";
echo "VAT  : ", $VAT->format(2), "\n";
echo "Total: ", $total->format(2), "\n";
*******/


$n = new BigFloat("12.345");
test("trunc1", $n->trunc_rem(-2, $r)->__toString(), "12.34");
test("trunc_rem1", $r->__toString(), "0.005");
test("trunc2", $n->trunc_rem( 1, $r)->__toString(), "10");
test("trunc_rem2", $r->__toString(), "2.345");
test("trunc3", $n->trunc_rem( 2, $r)->__toString(), "0");
test("trunc_rem3", $r->__toString(), "12.345");
test("trunc4", $n->trunc_rem(-9, $r)->__toString(), "12.345");
test("trunc_rem4", $r->__toString(), "0");
$n = new BigFloat("-12.345");
test("trunc5", $n->trunc_rem(-2, $r)->__toString(), "-12.34");
test("trunc_rem5", $r->__toString(), "-0.005");

$n = new BigFloat("12.345");
test("round1", $n->round(-2)->__toString(), "12.35");
test("round1.1", $n->round(-1)->__toString(), "12.3");

$n = new BigFloat("45.678");
test("round2", $n->round(-2)->__toString(), "45.68");

$n = new BigFloat("-12.345");
test("-round1", $n->round(-2)->__toString(), "-12.35");
test("-round1.1", $n->round(-1)->__toString(), "-12.3");

$n = new BigFloat("-45.678");
test("-round2", $n->round(-2)->__toString(), "-45.68");

$n = new BigFloat("12.456");
test("fmt1", $n->format(2), "12.45");

$n = new BigFloat("12.456");
test("fmt1", $n->format(2), "12.45");
test("fmt2", $n->format(0), "12");

$n = new BigFloat("1200");
test("fmt3", $n->format(2), "1,200.00");
test("fmt3-", $n->minus()->format(2), "-1,200.00");

$n = new BigFloat("0.012");
test("fmt4", $n->format(2), "0.01");
test("fmt5", $n->format(3), "0.012");
test("fmt6", $n->format(4), "0.0120");

$n = new BigFloat("100");
test("fmt7", $n->div( new BigFloat("3"), -5 )->format(2), "33.33");
test("fmt8", $n->div( new BigFloat("3"), -2 )->format(2), "33.33");
test("fmt9", $n->div( new BigFloat("3"), -1 )->format(2), "33.30");

$n = new BigFloat("4");
$d = new BigFloat("7");
test("fmt10", $n->div($d, -6)->format(6), "0.571428");
test("fmt11", $n->div($d, -6)->format(5), "0.57142");
test("fmt12", $n->div($d, -6)->round(-5)->format(5), "0.57143");

/*********
$n = new BigFloat("100e20000000");
$d = new BigFloat("30");
$q = $n->div_rem($d, -2, $r);
echo $q, " resto ", $r;
exit;
**********/

$n = new BigFloat("1234");
$d = new BigFloat("890");
$q = $n->div_rem($d, -3, $r);
echo "$n/$d = $q ($r)\n";
echo "$q*$d + $r - $n = ", $q->mul($d)->add($r)->sub($n), "\n";

$n = new BigFloat("12340");
$d = new BigFloat("1.53");
$q = $n->div_rem($d, -3, $r);
echo "$n/$d = $q ($r)\n";
echo "$q*$d + $r - $n = ", $q->mul($d)->add($r)->sub($n), "\n";

$n = new BigFloat("4");
$d = new BigFloat("7");
$q = $n->div_rem($d, -3, $r);
echo "$n/$d = $q ($r)\n";
echo "$q*$d + $r - $n = ", $q->mul($d)->add($r)->sub($n), "\n";


$n = new BigFloat("0");
test("", $n->__toString(), "0");

$n = new BigFloat("000");
test("", $n->__toString(), "0");

$n = new BigFloat("00.0000");
test("", $n->__toString(), "0");

$n = new BigFloat("1");
test("", $n->__toString(), "1");

$n = new BigFloat("-1");
test("", $n->__toString(), "-1");

$n = new BigFloat("1.9");
test("", $n->__toString(), "1.9");

$n = new BigFloat("1e6");
test("", $n->__toString(), "1000000");

$n = new BigFloat("0e6");
test("", $n->__toString(), "0");

$n = new BigFloat("0.0010");
test("", $n->__toString(), "0.001");

$n = new BigFloat("-1.23");
test("", $n->__toString(), "-1.23");

$n = new BigFloat("12.34e+5");
test("", $n->__toString(), "1234000");

$n = new BigFloat("-12.34e-5");
test("", $n->__toString(), "-0.0001234");

$n = new BigFloat("1230000");
$n = $n->add( new BigFloat("456") );
test("", $n->__toString(), "1230456");

$n = new BigFloat("12.30000");
$n = $n->add( new BigFloat("0.00456") );
test("", $n->__toString(), "12.30456");

$n = new BigFloat("0.01230000");
$n = $n->add( new BigFloat("0.00000456") );
test("", $n->__toString(), "0.01230456");

$n = new BigFloat( new BigInt(1000) );
test("", $n->__toString(), "1000");

$n = new BigFloat("120");
test("ceil 1", $n->ceil()->__toString(), "120");

$n = new BigFloat("1.2");
test("ceil 2", $n->ceil()->__toString(), "2");

$n = new BigFloat("-1.2");
test("ceil 3", $n->ceil()->__toString(), "-1");

$n = new BigFloat("-120");
test("ceil 4", $n->ceil()->__toString(), "-120");

$n = new BigFloat("120");
test("floor 1", $n->floor()->__toString(), "120");

$n = new BigFloat("1.2");
test("floor 2", $n->floor()->__toString(), "1");

$n = new BigFloat("-1.2");
test("floor 3", $n->floor()->__toString(), "-2");

$n = new BigFloat("-120");
test("floor 4", $n->floor()->__toString(), "-120");

$n = new BigFloat("1.9");
test("toBigInt 1", $n->toBigInt()->__toString(), "1");

$n = new BigFloat("-1.9");
test("toBigInt 2", $n->toBigInt()->__toString(), "-1");

$n = new BigFloat("1.9");
test("toInt 1", "".$n->toInt(), "1");

$n = new BigFloat("-1.9");
test("toInt 2", "".$n->toInt(), "-1");

$start = time();
$range_a = -123;
$range_b = +123;
$steps_n = $range_b-$range_a+1;
$scale = 0.01;
echo "Performing extensive tests in the range $range_a..$range_b:\n";

for( $i=$range_a; $i <= $range_b; $i++ ){

	$step = $i-$range_a+1;
	echo "Step $step of $steps_n (", (int)($step/$steps_n*100), "%)\n";

	set_time_limit(100);

	$f = $scale * $i;

	$a = new BigFloat( (string) $f );

	# Testing conversion from/to string:
	if( $a->__toString() !== (string) $f ){
		echo "test __toString(): expected $f got $a\n";
		$err++;
	}

	# Testing minus():
	$c = $a->minus();
	if( $c->__toString() !== (string) (-$f) ){
		echo "test minus(): expected ", (-$f), ", got $c\n";
		$err++;
	}

	for( $j=$range_a; $j <= $range_b; $j++ ){

		$g = $scale * $j;

		$b = new BigFloat( (string) $g );

		# Testing comparison:
		$cmp = $a->cmp($b);
		if( $cmp < 0  and  $f >= $g
		or $cmp == 0 and $f != $g
		or $cmp > 0 and $f <= $g ){
			echo "test cmp(): error comparing $f with $g: got $cmp\n";
			$err++;
		}

		# Testing addition:
		$c = $a->add($b);
		###if( abs( (float) $c->__toString() - ($f+$g) ) > EPSILON ){
		if( $c->__toString() !== (string) ($f+$g) ){
			echo "test add(): error ($f)+($g) gives $c rather than ", $f+$g, "\n";
			$err++;
		}
		
		# Testing subtraction:
		$c = $a->sub($b);
		###if( abs((float) $c->__toString() - ($f-$g) ) > EPSILON ){
		if( $c->__toString() !== (string) ($f-$g) ){
			echo "test sub(): error ($f)-($g) gives $c rather than ", $f-$g, "\n";
			$err++;
		}
		
		# Testing multiplication:
		$c = $a->mul($b);
		if( abs( (float) $c->__toString() - ($f*$g) ) > EPSILON ){
		# FIXME: -1*0 da' "-0", per cui questo non funziona:
		###if( $c->__toString() !== (string) (float) (string) ($f*$g) ){
			echo "test mul(): error ($f)*($g) gives $c rather than ", $f*$g, "\n";
			$err++;
		}

		# Testing division:
		if( $j != 0 ){
			$c = $a->div_rem($b, -5, $rem);
			/*****
			if( abs( (float) $c->__toString() - ((float)$i/$j) ) > EPSILON ){
				echo "test div_rem(): error (",
					$scale*$i, ")/(", $scale*$j, ") gives $c\n";
				$err++;
			}
			*****/
			if( $c->mul($b)->add($rem)->sub($a)->sign() != 0 ){
				echo "test rem(): error ($f)%($g): invalid remainder: ",
					" $c*$b+$rem-$a = ", $c->mul($b)->add($rem)->sub($a), "\n";
				$err++;
			}
		}

	}

}

/***

echo "Testing sqrt():\n";
$inc = new BigFloat("0.001");
$x = new BigFloat("0");
$precision = -1;
$epsilon = new BigFloat("1e$precision");
for( $i = 0; $i <= 20000; $i++ ){
	set_time_limit(10);
	$y2 = $x->sqrt($precision);
	if( $y2->mul($y2)->cmp($x) > 0
	or $y2->add($epsilon)->mul( $y2->add($epsilon) )->cmp($x) <= 0 ){
		$y2 = sqrt2($x, $precision, TRUE);
		echo "ERROR: sqrt2($x) -> $y2\n";
		$err++;
	}
	$x = $x->add($inc);
}
****/



echo "Time: ", (time()-$start), " s\n";
echo "Last required time was: 125 s (with BigInt::\$optimize = TRUE)\n";
echo "Errors: $err\n";

exit( $err==0? 0:1 );
