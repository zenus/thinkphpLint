<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\UString;
use it\icosaedro\utils\TestUnit as TU;


/*. UString .*/ function U(/*. string .*/ $s)
{
	return UString::fromUTF8($s);
}

const W_UTF8 = "perch\303\251"; # "why" in italian, UTF-8 encoding
const W_ISO = "perch\351"; # "why" in italian, ISO-8859-1
const W_ASCII = "perch?"; # "why" in italian, ASCII


function main() /*. throws \Exception .*/
{
	# Test invalid UTF-8 encodings:
	# ----------------------------
	# Invalid start of sequence:
	TU::test( U("\x80zz")->toUTF8(), "?zz");
	TU::test( U("\xbfzz")->toUTF8(), "?zz");
	TU::test( U("\xc0zz")->toUTF8(), "?zz");
	TU::test( U("\xf0zz")->toUTF8(), "?zz");
	# Non-minimal 2-bytes encoding:
	TU::test( U("a\xc0\x80")->toUTF8(), "a??");
	# Non-minimal 3-bytes encoding:
	TU::test( U("a\xe0\x80\x80")->toUTF8(), "a???");
	# Trunked 2-bytes sequence:
	TU::test( U("A\xc2")->toUTF8(), "A?");
	# Invalid cont. in 2-bytes sequence:
	TU::test( U("A\xc2Z")->toUTF8(), "A?Z");
	# Trunked 3-bytes sequence:
	TU::test( U("A\xe1")->toUTF8(), "A?");
	TU::test( U("A\xe1\x80Z")->toUTF8(), "A??Z");
	# Invalid cont. in 3-bytes sequence:
	TU::test( U("A\xe1YZ")->toUTF8(), "A?YZ");
	TU::test( U("A\xe1\x80Z")->toUTF8(), "A??Z");

	$empty = U("");

	$us = $empty;
	TU::test($us->toUTF8(), "");

	$us = U(W_UTF8);
	TU::test($us->length(), 6);
	TU::test($us->toUTF8(), W_UTF8);
	TU::test($us->toISO88591(), W_ISO);
	TU::test($us->toASCII(), W_ASCII);

	$us = UString::fromISO88591(W_ISO);
	TU::test($us->toUTF8(), W_UTF8);
	TU::test($us->toISO88591(), W_ISO);
	TU::test($us->toASCII(), W_ASCII);

	# charAt():
	$res = $empty;
	for($i = 0; $i < $us->length(); $i++)
		$res = $res->append( $us->charAt($i) );
	TU::test( $res, $us );

	# substring():
	TU::test($empty->substring(0,0)->equals($empty), TRUE);
	TU::test($us->substring(0,0), $empty);
	TU::test($us->substring(1,1), $empty);
	TU::test($us->substring(6,6), $empty);
	TU::test($us->substring(0,6), $us);

	# append():
	TU::test( U("a")->append(U(""))->append(U("b")), U("ab"));
	$us = $us->append($us)->append($us);
	$us = $us->append($us)->append($us);
	$us = $us->append($us)->append($us);
	$us = $us->append($us)->append($us);
	#echo "Building char by char, ", $us->length(), " codepoints: ";
	#$t = new Timer(TRUE);
	$res = $empty;
	for($i = 0; $i < $us->length(); $i++){
		$c = $us->substring($i, $i+1);
		$res = $res->append($c);
	}
	TU::test($res->equals($us), TRUE);
	#$t->stop();
	#echo $t, "\n";

	# remove():
	TU::test( $empty->remove(0,0), $empty );
	$abc = U("abc");
	TU::test( $abc->remove(0,0), $abc );
	TU::test( $abc->remove(1,1), $abc );
	TU::test( $abc->remove(3,3), $abc );
	TU::test( $abc->remove(0,3), $empty );
	TU::test( $abc->remove(0,1), U("bc") );
	TU::test( $abc->remove(1,2), U("ac") );
	TU::test( $abc->remove(2,3), U("ab") );

	# insert():
	$abc = U("abc");
	TU::test( $empty->insert($abc, 0), $abc );
	TU::test( $abc->insert($empty, 0), $abc );
	TU::test( $abc->insert($empty, 1), $abc );
	TU::test( $abc->insert($empty, 3), $abc );
	TU::test( $abc->insert(U("z"), 0), U("zabc") );
	TU::test( $abc->insert(U("z"), 1), U("azbc") );
	TU::test( $abc->insert(U("z"), 3), U("abcz") );

	# startsWith():
	TU::test( $empty->startsWith($empty), TRUE );
	TU::test( $empty->startsWith($us), FALSE );
	TU::test( $us->startsWith($empty), TRUE );
	TU::test( $us->startsWith($us), TRUE );
	TU::test( $us->startsWith(U("p")), TRUE );
	TU::test( $us->startsWith(U("per")), TRUE );
	TU::test( $us->startsWith(U("xxx")), FALSE );

	# endsWith():
	TU::test( $empty->endsWith($empty), TRUE );
	TU::test( $us->endsWith($empty), TRUE );
	TU::test( $us->endsWith($us), TRUE );
	TU::test( $us->endsWith(U("\303\251")), TRUE );
	TU::test( $us->endsWith(U("ch\303\251")), TRUE );
	TU::test( $us->endsWith(U("xxx")), FALSE );

	# indexOf():
	TU::test( $empty->indexOf($empty), 0 );
	$q = U(W_UTF8 . W_UTF8);
	TU::test( $empty->indexOf($q), -1 );
	TU::test( $q->indexOf($empty), 0 );
	TU::test( $q->indexOf(U("p")), 0 );
	TU::test( $q->indexOf(U("per")), 0 );
	TU::test( $q->indexOf(U("er")), 1 );
	TU::test( $q->indexOf(U("h\303\251")), 4 );
	TU::test( $q->indexOf(U("\303\251p")), 5 );
	TU::test( $q->indexOf(U("\303\251pe")), 5 );
	TU::test( $q->indexOf(U("\303\251perch\303\251")), 5 );
	TU::test( $q->indexOf($q), 0 );
	TU::test( $q->indexOf(U("xxx")), -1 );
	TU::test( $q->indexOf(U("p"), 0), 0);
	TU::test( $q->indexOf(U("p"), 1), 6);
	TU::test( $q->indexOf(U("p"), 6), 6);

	# lastIndexOf():
	TU::test( $empty->lastIndexOf($empty, 0), 0 );
	TU::test( $empty->lastIndexOf(U("xx"), 0), -1 );
	TU::test( $q->lastIndexOf($empty, 12), 12 );
	TU::test( $q->lastIndexOf($empty, 11), 11 );
	TU::test( $q->lastIndexOf(U("p"), 12), 6 );
	TU::test( $q->lastIndexOf(U("p"), 7), 6 );
	TU::test( $q->lastIndexOf(U("p"), 6), 0 );
	TU::test( $q->lastIndexOf(U("\303\251"), 12), 11 );
	TU::test( $q->lastIndexOf(U("\303\251"), 6), 5 );
	TU::test( $q->lastIndexOf($q, 12), 0 );

	# trim():
	TU::test( U("abc")->trim(), U("abc") );
	TU::test( U(" abc ")->trim(), U("abc") );
	TU::test( U(" abc ")->trim(U(" ")), U("abc") );
	TU::test( U(" abc ")->trim(U(" ac")), U("b") );
	TU::test( U(" abc ")->trim(U(" a..z")), U("") );
	$fffd = UString::chr(0xfffd);
	$fffe = UString::chr(0xfffe);
	$ffff = UString::chr(65535);
	$subj = $fffd->append($fffe)->append(U("abc"))->append($ffff);
	$black =  $fffd->append(U(".."))->append($ffff);
	TU::test( $subj->trim($black), U("abc") );

	# replace():
	# TU::test( U("abaco")->replace("a", ""), U("bco"));
	
	# UCS2 encoding conversions:
	$c = U("AB");
	TU::test( $c->toUCS2LE(), "A\000B\000");
	TU::test( $c->toUCS2BE(), "\000A\000B");
	TU::test( UString::fromUCS2LE( $us->toUCS2LE() ), $us );
	TU::test( UString::fromUCS2BE( $us->toUCS2BE() ), $us );

	# Build very long string with samples of any codepoint in [0,65535]:
	$big = $empty;
	for($cp = 0; $cp < 65536; $cp += 100 ){
		$big = $big->append( UString::chr($cp) );
	}
	#   Check resulting length:
	TU::test( $big->length(), (int) (65536 / 100) + 1 );
	#   Scan and check every codepoint:
	for($i = 0; $i < $big->length(); $i++ ){
		TU::test( $big->codepointAt($i), 100*$i );
	}
	#   Convert to and from any full-range capable encoding:
	TU::test( U( $big->toUTF8() ), $big );
	TU::test( UString::fromUCS2LE( $big->toUCS2LE() ), $big );
	TU::test( UString::fromUCS2BE( $big->toUCS2BE() ), $big );

	# Case-sensitive routines:
	TU::test( U("AbCÈè")->toUpperCase(), U("ABCÈÈ"));
	TU::test( U("AbCÈè")->toLowerCase(), U("abcèè"));
	TU::test( U("AbCÈè")->equalsIgnoreCase(U("abcèè")), TRUE);
	TU::test( U("AbCÈè")->equalsIgnoreCase(U("xbcèè")), FALSE);

	# serialize(), unserialize():
	TU::test( unserialize( serialize($us) ), $us );
}

class testUString extends TU {
	function run() /*. throws \Exception .*/
	{
		main();
	}
}

$tu = new testUString();
$tu->start();


// THE END
