<?php

namespace it\icosaedro\regex;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\regex\Pattern;
use it\icosaedro\utils\Timer;


/*. string .*/ function test(/*. string .*/ $re, /*. string .*/ $s)
{
	#echo "----\n";
	$p = new Pattern($re);
	if( $p->match($s) )
		return $p->resultAsString(" ");
	else
		return NULL;
}


/*. string .*/ function getResult(/*. Pattern .*/ $p, /*. string .*/ $s){
	if( $p->match($s) )
		return $p->resultAsString(" ");
	else
		return NULL;
}


function genericTests(){
	echo __FUNCTION__, "\n";
	TU::test(test(".**ab", "aaaab"), '0 "aaaab"');
	TU::test(test("^(a|aa|aaa|aaaa)ab", "aaaaab"), '0 "aaaaab" 0.0 "aaaa"');

	TU::test(test("^a[1,9]?ab\$", "aaaaaab"), '0 "aaaaaab"');
	TU::test(test("^(a[1,3]*)[1,2]*ab\$", "aaaaaab"), '0 "aaaaaab" 0.0 "aaa" 0.0 "aa"');

	$p = new Pattern("^{-\\+}?*{0-9}+*\$");
	TU::test(getResult($p, "0"), '0 "0"');
	TU::test(getResult($p, "123"), '0 "123"');
	TU::test(getResult($p, "+123"), '0 "+123"');
	TU::test(getResult($p, "-123"), '0 "-123"');
	TU::test(getResult($p, "-123x"), NULL);
	TU::test(getResult($p, "-123x"), NULL);
	TU::test(getResult($p, "x123x"), NULL);
	TU::test(getResult($p, ""), NULL);
	TU::test(getResult($p, " "), NULL);

	# Compare greedy and reluctant:
	$line = "This is a <EM>first</EM> test";
	TU::test(test(".**(<.**>)", $line), '0 "This is a <EM>first</EM>" 0.0 "</EM>"');
	TU::test(test(".*?(<.**>)", $line), '0 "This is a <EM>first</EM>" 0.0 "<EM>first</EM>"');
	TU::test(test(".*?(<.*?>)", $line), '0 "This is a <EM>" 0.0 "<EM>"');
	TU::test(test("{!<}*((<{!>}*>){!<}*)*", $line), '0 "This is a <EM>first</EM> test" 0.0 "<EM>first" 0.0.0 "<EM>" 0.0 "</EM> test" 0.0.0 "</EM>"');
	TU::test(test("({!<}*)?((<{!>}*>)({!<}*))*", $line),
		'0 "This is a <EM>first</EM> test" 0.0 "This is a " 0.1 "<EM>first" 0.1.0 "<EM>" 0.1.1 "first" 0.1 "</EM> test" 0.1.0 "</EM>" 0.1.1 " test"');
	
	// 2014-01-21 Non-regression test on bug found today:
	$p = new Pattern("(B|(C))*A");
	$p->match("BCZ"); // should not crash

}


function testSubexpressions(){
	echo __FUNCTION__, "\n";
	$w = "{a-zA-Z0-9_}+";
	$sp = "{ \t}*";
	$re = "$sp($w)$sp(,$sp($w)$sp)*\$";
	$p = new Pattern($re);
	$s = " alfa  ,  beta  ,  gamma  ";
	TU::test("$p", $re);
	TU::test(getResult($p, $s), '0 " alfa  ,  beta  ,  gamma  " 0.0 "alfa" 0.1 ",  beta  " 0.1.0 "beta" 0.1 ",  gamma  " 0.1.0 "gamma"');
	TU::test($p->value(), " alfa  ,  beta  ,  gamma  ");
	TU::test($p->group(0)->elem(0)->value(), "alfa");
	TU::test($p->group(1)->elem(0)->value(), ",  beta  ");
	TU::test($p->group(1)->elem(0)->group(0)->elem(0)->value(), "beta");
	TU::test($p->group(1)->elem(1)->value(), ",  gamma  ");
	TU::test($p->group(1)->elem(1)->group(0)->elem(0)->value(), "gamma");
	TU::test($p->group(0)->count(), 1);
	TU::test($p->group(1)->count(), 2);
	TU::test($p->group(1)->elem(0)->group(0)->count(), 1);
	TU::test($p->group(1)->elem(1)->group(0)->count(), 1);


	$K = "{a-z}{a-zA-Z_0-9}*";
	$V = "{-\\+}?{0-9}+";
	$SP = "{ \t}*";
	$re = "$SP($K)$SP=$SP($V)$SP(,$SP($K)$SP=$SP($V))*$SP\$";
	$p = new Pattern($re);
	TU::test("$p", $re);
	$line = "alpha = 1, beta = 2, gamma = 3";
	TU::test(getResult($p, $line), '0 "alpha = 1, beta = 2, gamma = 3" 0.0 "alpha" 0.1 "1" 0.2 ", beta = 2" 0.2.0 "beta" 0.2.1 "2" 0.2 ", gamma = 3" 0.2.0 "gamma" 0.2.1 "3"');

	# Repeat latter test to check if internal stata had been properly reset:
	TU::test(getResult($p, $line), '0 "alpha = 1, beta = 2, gamma = 3" 0.0 "alpha" 0.1 "1" 0.2 ", beta = 2" 0.2.0 "beta" 0.2.1 "2" 0.2 ", gamma = 3" 0.2.0 "gamma" 0.2.1 "3"');
}


function testResultAsString(){
	echo __FUNCTION__, "\n";

	# trim() implemented with regex:
	$sp = "{ \t\n\r\0\x0b}";
	$trim_regex = "$sp*(.*?)$sp*\$";
	$p = new Pattern($trim_regex);
	TU::test(getResult($p, ""), '0 "" 0.0 ""' );
	TU::test(getResult($p, "a"), '0 "a" 0.0 "a"' );
	TU::test(getResult($p, " a"), '0 " a" 0.0 "a"' );
	TU::test(getResult($p, " a"), '0 " a" 0.0 "a"' );
	TU::test(getResult($p, "  a"), '0 "  a" 0.0 "a"' );
	TU::test(getResult($p, "  a "), '0 "  a " 0.0 "a"' );
	TU::test(getResult($p, "\t  a b "), '0 "\\t  a b " 0.0 "a b"' );

	# Counting recurrences of a given sub-string:
	$sub = Pattern::escape("abc");
	$p = new Pattern("(.*?$sub)*.*\$");

	$p->match("");
	TU::test($p->group(0)->count(), 0);

	$p->match("$sub");
	TU::test($p->group(0)->count(), 1);

	$p->match("$sub$sub");
	TU::test($p->group(0)->count(), 2);

	$p->match(".");
	TU::test($p->group(0)->count(), 0);

	$p->match(".$sub.");
	TU::test($p->group(0)->count(), 1);

	$p->match(".$sub.$sub.");
	TU::test($p->group(0)->count(), 2);
}


function performancesTest(){
	echo __FUNCTION__, "\n";
	$t = new Timer();
	$subject = "+123456789";

	$p = new Pattern("^{-\\+}?{0-9}+\$");
	$t->reset();
	$t->start();
	$n = 0;
	do {
		for($i = 1000; $i >= 1; $i--){
			if( ! $p->match($subject) )
				echo "BAD Pattern\n";
			$n++;
		}
	} while( $t->elapsedMilliseconds() < 500 );
	$dt = $t->elapsedMilliseconds();
	echo "Pattern class performance: ", (int) ($n * 1000 / $dt), " matches/s\n";
	# ==> 7500 matches/s on my Pentium 4 at 1600 MHz

	$t->reset();
	$t->start();
	$n = 0;
	$re = "/^[-+][0-9]+\$/";
	do {
		for($i = 1000; $i >= 1; $i--){
			if( preg_match($re, $subject) !== 1 )
				echo "BAD pcre\n";
			$n++;
		}
	} while( $t->elapsedMilliseconds() < 500 );
	$dt = $t->elapsedMilliseconds();
	echo "preg_match() function performance: ", (int) ($n * 1000 / $dt), " matches/s\n";
	# ==> 276000 matches/s on my Pentium 4 at 1600 MHz
}


function countWords(){
	echo __FUNCTION__, "\n";
	$p = new Pattern("(.*?({a-zA-Z}+))*");

	$p->match("");
	TU::test($p->group(0)->count(), 0);

	$p->match("  ");
	TU::test($p->group(0)->count(), 0);

	$p->match("Counting words, every word being a sequence of latin letters.");
	TU::test($p->group(0)->count(), 10);
	$words = /*. (string[int]) .*/ array();
	for($i = 0; $i < $p->group(0)->count(); $i++)
		$words[] = $p->group(0)->elem($i)->group(0)->elem(0)->value();
	TU::test($words, array("Counting", "words", "every", "word", "being", "a",
		"sequence", "of", "latin", "letters"));
}


class TestPattern extends TU {

	public /*. void .*/ function run(){
		genericTests();
		testSubexpressions();
		performancesTest();
		testResultAsString();
		countWords();
	}
}
$tu = new TestPattern();
$tu->start();
