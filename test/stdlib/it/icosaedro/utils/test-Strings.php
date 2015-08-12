<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\TestUnit as TU;

class testStrings extends TU {
	function run(){

		// Test isASCII() with single byte:
		for($i = 0; $i < 128; $i++)
			if( ! Strings::isASCII (chr($i)) )
				throw new \RuntimeException("$i not ASCII ?!");

		for($i = 128; $i < 256; $i++)
			if( Strings::isASCII (chr($i)) )
				throw new \RuntimeException("$i ASCII ?!");

		// Test isASCII() with 2 bytes:
		for($i = 0; $i < 128; $i++)
			if( ! Strings::isASCII ("a".chr($i)) )
				throw new \RuntimeException("$i not ASCII ?!");

		for($i = 128; $i < 256; $i++)
			if( Strings::isASCII ("a".chr($i)) )
				throw new \RuntimeException("$i ASCII ?!");

		//$t = new Timer();
		//$t->start();
		//for($i = 0; $i < 100000; $i++)
			if( ! Strings::isASCII("azazazazazazaazazazazazazazazazazaz") )
				throw new \RuntimeException("not ascii");
		//echo "t=$t\n";
		
		#TU::test(Strings::substring(NULL, 0, 0), "");

		TU::test(Strings::substring("", 0, 0), "");
		TU::test(Strings::substring("abc", 0, 0), "");
		TU::test(Strings::substring("abc", 3, 3), "");

		TU::test(Strings::substring("", 0, 0), "");
		TU::test(Strings::substring("abc", 0, 1), "a");
		TU::test(Strings::substring("abc", 0, 3), "abc");
		TU::test(Strings::substring("abc", 1, 3), "bc");

		TU::test(Strings::startsWith("", NULL), true);
		TU::test(Strings::startsWith("", ""), true);
		TU::test(Strings::startsWith("", "a"), false);
		TU::test(Strings::startsWith("abc", NULL), true);
		TU::test(Strings::startsWith("abc", ""), true);
		TU::test(Strings::startsWith("abc", "a"), true);
		TU::test(Strings::startsWith("abc", "ab"), true);
		TU::test(Strings::startsWith("abc", "abc"), true);
		TU::test(Strings::startsWith("abc", "abcd"), false);

		TU::test(Strings::endsWith("", NULL), true);
		TU::test(Strings::endsWith("", ""), true);
		TU::test(Strings::endsWith("", "a"), false);
		TU::test(Strings::endsWith("abc", NULL), true);
		TU::test(Strings::endsWith("abc", ""), true);
		TU::test(Strings::endsWith("abc", "c"), true);
		TU::test(Strings::endsWith("abc", "bc"), true);
		TU::test(Strings::endsWith("abc", "abc"), true);
		TU::test(Strings::endsWith("abc", "zabc"), false);

		TU::test(Strings::toLiteral(NULL), "NULL");
		TU::test(Strings::toLiteral(""), "\"\"");
		TU::test(Strings::toLiteral("abc"), "\"abc\"");
		TU::test(Strings::toLiteral("abc\n"), "\"abc\\n\"");
	}
}
$tu = new testStrings();
$tu->start();
