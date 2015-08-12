<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\utils\Bytes;

/*. Bytes .*/ function BF(/*. string .*/ $s)
{
	return Bytes::factory($s);
}


class testBytes extends TU {
	function run() /*. throws \Exception .*/
	{
		$empty = BF("");

		TU::test(BF("")->__toString(), "");
		TU::test(BF("") === BF(""), TRUE);
		TU::test(BF("a")->__toString(), "a");
		TU::test(BF("a") === BF("a"), TRUE);
		TU::test(BF("a"), BF("a"));
		TU::test(BF("a")->equals(BF("z")), FALSE);
		TU::test(BF("\377")->__toString(), "\377");
		TU::test(BF("abc")->__toString(), "abc");
		TU::test(BF("abc") !== BF("abc"), TRUE);

		$b = BF("hello");
		TU::test($b->length(), 5);
		TU::test($b, $b);
		TU::test($b, BF("hello"));
		TU::test($b->equals( BF("hello") ), TRUE);
		TU::test($b->getByte(0), ord("h"));
		TU::test($b->getByte(4), ord("o"));
		TU::test($b->substring(0,0)->__toString(), "");
		TU::test($b->substring(0,1)->__toString(), "h");
		TU::test($b->substring(0,2)->__toString(), "he");
		TU::test($b->substring(0,5)->__toString(), "hello");
		TU::test($b->substring(1,5)->__toString(), "ello");
		TU::test($b->substring(4,5)->__toString(), "o");
		TU::test($b->substring(5,5)->__toString(), "");

		TU::test($b->startsWith( BF("") ), TRUE);
		TU::test($b->startsWith( BF("h") ), TRUE);
		TU::test($b->startsWith( BF("he") ), TRUE);
		TU::test($b->startsWith( BF("hello") ), TRUE);
		TU::test($b->startsWith( BF("hello???") ), FALSE);
		TU::test($b->startsWith( BF("???") ), FALSE);
		TU::test($b->startsWith( $b ), TRUE);

		TU::test($b->endsWith( BF("") ), TRUE);
		TU::test($b->endsWith( BF("o") ), TRUE);
		TU::test($b->endsWith( BF("lo") ), TRUE);
		TU::test($b->endsWith( BF("hello") ), TRUE);
		TU::test($b->endsWith( BF("???hello") ), FALSE);
		TU::test($b->endsWith( $b ), TRUE);

		TU::test(BF("")->indexOf( BF("") ), 0);
		TU::test($b->indexOf( BF("") ), 0);
		TU::test($b->indexOf( BF("h") ), 0);
		TU::test($b->indexOf( BF("he") ), 0);
		TU::test($b->indexOf( BF("e") ), 1);
		TU::test($b->indexOf( BF("o") ), 4);
		TU::test($b->indexOf( BF("???") ), -1);
		TU::test($b->indexOf( BF("l"), 0 ), 2);
		TU::test($b->indexOf( BF("l"), 3 ), 3);
		TU::test($b->indexOf( BF("o"), 3 ), 4);
		TU::test($b->indexOf( BF("o"), 5 ), -1);

		TU::test(BF("")->lastIndexOf( BF(""), 0 ), 0);
		TU::test($b->lastIndexOf( BF(""), 5 ), 5);
		TU::test($b->lastIndexOf( BF("h"), 5 ), 0);
		TU::test($b->lastIndexOf( BF("he"), 5 ), 0);
		TU::test($b->lastIndexOf( BF("e"), 5 ), 1);
		TU::test($b->lastIndexOf( BF("o"), 5 ), 4);
		TU::test($b->lastIndexOf( BF("???"), 5 ), -1);
		TU::test($b->lastIndexOf( BF("l"), 0 ), -1);
		TU::test($b->lastIndexOf( BF("l"), 3 ), 2);
		TU::test($b->lastIndexOf( BF("o"), 5 ), 4);
		TU::test($b->lastIndexOf( BF("h"), 1 ), 0);
		TU::test($b->lastIndexOf( BF("h"), 0 ), -1);
		TU::test($b->lastIndexOf( BF(""), 0 ), 0);

		TU::test(BF(" a ")->trim()."", "a");

		# replace():
		TU::test($b->replace(BF("l"), BF("L"))."", "heLLo");
		TU::test($b->replace(BF("he"), BF("be"))."", "bello");
		TU::test($b->replace(BF("hello"), BF("bye"))."", "bye");
		TU::test($b->replace(BF("l"), BF(""))."", "heo");
		TU::test(BF("")->replace(BF("x"), BF("y"))."", "");
		TU::test(BF("x")->replace(BF("x"), BF(""))."", "");
		# Check replacement order left to right:
		TU::test(BF("AAA")->replace(BF("AA"), BF("ZZ"))."", "ZZA");
		# Check replacement order right to left:
		#TU::test(BF("AAA")->replace(BF("AA"), BF("ZZ"))."", "AZZ");

		# remove():
		TU::test( $empty->remove(0,0), $empty );
		$abc = BF("abc");
		TU::test( $abc->remove(0,0), $abc );
		TU::test( $abc->remove(1,1), $abc );
		TU::test( $abc->remove(3,3), $abc );
		TU::test( $abc->remove(0,3), $empty );
		TU::test( $abc->remove(0,1), BF("bc") );
		TU::test( $abc->remove(1,2), BF("ac") );
		TU::test( $abc->remove(2,3), BF("ab") );

		# insert():
		$abc = BF("abc");
		TU::test( $empty->insert($abc, 0), $abc );
		TU::test( $abc->insert($empty, 0), $abc );
		TU::test( $abc->insert($empty, 1), $abc );
		TU::test( $abc->insert($empty, 3), $abc );
		TU::test( $abc->insert(BF("z"), 0), BF("zabc") );
		TU::test( $abc->insert(BF("z"), 1), BF("azbc") );
		TU::test( $abc->insert(BF("z"), 3), BF("abcz") );

		# explode(), implode():
		TU::test( Bytes::implode( BF("a;b;c")->explode(BF(";")), BF("-"))."",   "a-b-c");
		TU::test( Bytes::implode( BF(";b;")->explode(BF(";")), BF("-"))."",   "-b-");
		TU::test( Bytes::implode( BF(";;")->explode(BF(";")), BF("-"))."",   "--");

		$b_serialized = serialize($b);
		$b_unserialized = cast("it\\icosaedro\\utils\\Bytes", unserialize($b_serialized));
		TU::test($b, $b_unserialized);
	}
}
$tu = new testBytes();
$tu->start();
