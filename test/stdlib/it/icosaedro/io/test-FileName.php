<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../../../stdlib/autoload.php";
use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\utils\UString;
use it\icosaedro\io\codepage\CodePageInterface;
use it\icosaedro\io\codepage\WindowsCodePage;
use it\icosaedro\io\codepage\GenericCodePage;

class testFileName extends TU {

	function run() /*. throws \Exception .*/
	{
		echo "ASCII code page...\n";
		/*. CodePageInterface .*/ $cp = new GenericCodePage("ASCII");
		$s = UString::fromUTF8("foo.txt");
		TU::test( $cp->encode($s), "foo.txt" );
		TU::test( $cp->decode("foo.txt"), $s );

		echo "ISO-8859-1 code page...\n";
		$cp = new GenericCodePage("ISO-8859-1");
		$s = UString::fromUTF8("Caffé Brillì.txt");
		TU::test( $cp->encode($s), "Caff\xe9 Brill\xec.txt" );
		TU::test( $cp->decode("Caff\xe9 Brill\xec.txt"), $s );

		echo "Windows 1252 code page (western europe)...\n";
		$cp = new WindowsCodePage("1252");
		$s = UString::fromUTF8("Caffé Brillì.txt");
		TU::test( $cp->encode($s), "Caff\xe9 Brill\xec.txt" );
		TU::test( $cp->decode("Caff\xe9 Brill\xec.txt"), $s );

		echo "Windows 932 code page (Japanese)...\n";
		$cp = new WindowsCodePage("932");
		$s = UString::fromUTF8("日本語");
		TU::test( $cp->encode($s), "\x93\xfa\x96\x7b\x8c\xea" );
		TU::test( $cp->decode("\x93\xfa\x96\x7b\x8c\xea"), $s );

		echo "Current code page: ", FileName::getEncoding(), "\n";
	}

}

$tu = new testFileName();
$tu->start();
