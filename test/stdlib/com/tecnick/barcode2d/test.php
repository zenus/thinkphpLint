<?php

namespace com\tecnick\barcode2d;

require_once __DIR__ . "/../../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../../stdlib/bcmath-for-int-replacement.php";
require_once __DIR__ . "/../../../../../stdlib/utf8.php";

use it\icosaedro\utils\Strings;
use it\icosaedro\utils\UString;

/**
 * @param string $got
 * @param string $exp
 * @throws \RuntimeException
 */
function test($got, $exp)
{
	if( $got !== $exp ){
		throw new \RuntimeException("test failed:\n"
			."got=".Strings::toLiteral($got)."\n"
			."exp=".Strings::toLiteral($exp)."\n"
			."(got result as base64_encode(gzcompress(\$got)): "
				. base64_encode(gzcompress($got)) . "\n");
	}
}


// Also testing binary data carrying Unicode strings:
//$BOM = "\xEF\xBB\xBF";
$unicode_string = u("\\ufeff123abcABC"); // U+FEFF BOM + some some ASCII
for($cp = 0x2654; $cp <= 0x2667; $cp++) // chess and poker symbols :-)
	$unicode_string = $unicode_string->append(UString::chr($cp));


/*. Barcode2D .*/ $q = NULL;

$q = new DataMatrix("abc def 123");
test("$q", gzuncompress(base64_decode("eJxVjjEOgDAMA7/CAxjchY2XICZWpEow8H1IbFc
	h7ZA6ztXb0c97bcs8Xf1h0/A/VPyMSoU3BCA9nNn0KcMfy+lRT5s5CeAP9gg/OKqcQEp6gJJHg
	aDMYkg1R2kKxzSMzGgOxDy19heaczse")));
//file_put_contents(__DIR__ . "/datamatrix.png", $q->getPNG());

$q = new DataMatrix($unicode_string->toUTF8());
test("$q", gzuncompress(base64_decode("eJyNVEtuwkAUuwoH6MKjSt1xkooVW6RI7aLXb8jz
	LysIEiGTN/bzs4fv+/b4vX5+fVx+tr/5sfD6M1X7td9mhff90vJae9WadzieF38eT155VkHISzA
	G51YyzgKq0F8g1qywPZIstmQsbXCtNXAnSqPaDpYoISySQhpNNk/WeObVKCh2qgZXbVih6ojVs2
	/NLD3mZUYjir9mj3RudmsujVqlK3GG1p368mu7JdFHX5F+8jmdjo+vrrezqoDE5LNp6EwkBPFdY
	Wf37Uds9ySnits5N05N5fJRU7R6V5KoGaHQNZ2wKnHJixBnJxk7g2nTosQIWH4dUBUrOXATSZXz
	D01VBYmZ4438T1QSUUpp0yQ6MMlfue3c1xByCnySZ142OSeugkKNZbNHFzS8m9XbPzrLBns=")));
//file_put_contents(__DIR__ . "/datamatrix-utf8.png", $q->getPNG());

$q = new DataMatrix($unicode_string->toUCS2BE());
test("$q", gzuncompress(base64_decode("eJyNUztuQzEMu0oO0IFK55wk6NQ1QIB2yPXzbPEj
	dEjjAO9ZfrJEUsz1+377vXyeP04/90dvCq9/O+NYO8B6rtc+4smRUYyRRPBwbVcNsF5nVhcq5Qo
	H/tRQz12DHVS5Ot1XdpfKxwGKSHYNkIE4uM+KGimYJCT7focgUh4BGKi6ItlKNqKctKKp32X2+9
	GasjVyXeCai0Q1KxbpuLtAd6KH+bWmr9Zb/igJ7k3Ido0YwnILiz0WKtHNViMXCou4xzY02/JIB
	hT7IyERIWaAVI9nLGz8MRTUCIdxOZcxqViVFzLb4eDYCcEBT9VQmDpnO/5uModwSIGIMqzaqgOT
	hp0tn/6zvp46gNGJ")));
//file_put_contents(__DIR__ . "/datamatrix-ucs2be.png", $q->getPNG());

//// DataMatrix - testing binary sequences of 1 byte
//for($b = 0; $b < 256; $b++)
//	$q = new DataMatrix(chr($b));

//// DataMatrix - testing binary sequences of 2 bytes
//for($b = 0; $b < 256; $b++)
//	for($c = 0; $c < 256; $c++)
//		$q = new DataMatrix(chr($b).chr($c));

//// DataMatrix - testing random sequences of 3 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255);
//	//echo rawurlencode($random_string), "\n";
//	$q = new DataMatrix($random_string);
//}

//// DataMatrix - testing random sequences of 4 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256-1);
//	$random_string = chr(($r >> 8)&255) . chr($r&255);
//	$q = new DataMatrix($random_string . $random_string);
//}

//// DataMatrix - testing random sequences of 5 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$h = mt_rand(0, 256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255)
//			. chr(($h >> 8)&255) . chr($h&255);
//	$q = new DataMatrix($random_string);
//}

//// DataMatrix - testing random sequences of 6 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255);
//	//echo rawurlencode($random_string), "\n";
//	$q = new DataMatrix($random_string . $random_string);
//}



$q = new PDF417("abcdefgh");
test("$q", gzuncompress(base64_decode("eJzt1L0OAjEIB/BX8QEcYDFxuCcxTq4ml3iDr+8
	H8IdeXFrSSWqMtUd/XELgclvv23Km4+GxPrfl9N7QtDXbZl0kHzmQ7Xf3+cETuYbocKC3SG+W3
	WUHjo1Ui9sIOSs7Y1uhDLXHlqhJLYdlJ2yn2mNrlpipt5Zl/7ApMiFl6CGLYC9p2aN2o5M1DPx
	Qa2IqO2uTziO1vGHsawEob9kZW8MYqqTACSoZ36HsUVsqZaQPJ5tMiPAXKTtpx9YIoPxBjzUZy
	h60w7AidAb6BvPK2qbsrK0xcUhJzTQBe6BeLTthExxykDXfro1CtcsetEOYF2xfY+ypsy//3Z6
	1ZtrXF0Q1FvI=")));
//file_put_contents(__DIR__ . "/pdf417.png", $q->getPNG());

$q = new PDF417("abcdefgh", -1, 2.0,
	["file_id" => "F123", "segment_index" => "0", "segment_total" => "4",
	"option_0" => "MyData"]);
test("$q", gzuncompress(base64_decode("eJztlDFuA0EMA7/iB7gQC8eVXxKkShvAgF3k+wE
	iSqQfYF2jO2BvsbviCFjyPr/vP88b4no+Pe6/z9vl43yKuWccBj6Rby7k9H8WuZE7WQaO9VUJK
	0KaPIeFvRumudGRsjlo3trhqwsbhvVlSyrq+lF+KdHsyRtd2AEwGCzlyaEzMs6dacvzwg6CVYK
	pZxGu/25toH/EXbOwQ2Dtj9a0FWghDCOPVPwXNglrP4BjRI/mh/aMlZC3sGkYa6OkzBV1/SbNo
	1RRlwubhOVtWzmLCliSbKNtEzqxsFlY2aRCiw4wPWJD6IRwCxuHwR3QKS8LEIx4oahGPlnYHKw
	ibH6ATGLe6Nyryy5Z2DxMX79+nkSWQ7voJgRd2LtgY88o7OsPv/iGAg==")));
//file_put_contents(__DIR__ . "/pdf417-macro.png", $q->getPNG());

// To trigger a numeric sequence, at least 13 digits are required;
// for a textual one at least 5:
$q = new PDF417("123456789012345ABCDEF");
//file_put_contents(__DIR__ . "/pdf417-num-alpha.png", $q->getPNG());
test("$q", gzuncompress(base64_decode("eJztlMEKg0AMRH/FD/CQAaEnv6T01GtBqIf+vmi
	SiUu9FGq8ZIXtbtaZt+Ck9+f0mkfIre/e02ceh6HvJG+kw2BD9NkKuloXshXWPbi3M9gkvhOrC
	E31cHu/YCfDEGsq3UxharKH8YYFuwTGj23ebTL2/rBic9GCpcMc5FJEIsS7XKNiEyUiBbsGFt0
	riO71v+PDLv9q+IIlw1wLwhgKUG7GTBIzErcrWBpMmp5lSjQHOCxQ49cpWC7M3vIYMCWRGKgcD
	cxtfCpYJkyNXC3myNwQ0gojGT8FpGD/gDlS7Ie58AoQ55ad0BTsfFjaSIU9FkbijtA=")));

$q = new PDF417($unicode_string->toUTF8());
//file_put_contents(__DIR__ . "/pdf417-utf8.png", $q->getPNG());

$q = new PDF417($unicode_string->toUCS2BE());
//file_put_contents(__DIR__ . "/pdf417-ucs2be.png", $q->getPNG());

//// PDF417 - testing binary sequences of 1 byte
//for($b = 0; $b < 256; $b++)
//	$q = new PDF417(chr($b));

//// PDF417 - testing binary sequences of 2 bytes
//for($b = 0; $b < 256; $b++)
//	for($c = 0; $c < 256; $c++)
//		$q = new PDF417(chr($b).chr($c));

//// PDF417 - testing random sequences of 3 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255);
//	//echo rawurlencode($random_string), "\n";
//	$q = new PDF417($random_string);
//}

//// PDF417 - testing random sequences of 4 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256-1);
//	$random_string = chr(($r >> 8)&255) . chr($r&255);
//	$q = new PDF417($random_string . $random_string);
//}

//// PDF417 - testing random sequences of 5 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$h = mt_rand(0, 256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255)
//			. chr(($h >> 8)&255) . chr($h&255);
//	$q = new PDF417($random_string);
//}

//// PDF417 - testing random sequences of 6 bytes
//mt_srand(1777);
//for($i = 10000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255);
//	//echo rawurlencode($random_string), "\n";
//	$q = new PDF417($random_string . $random_string);
//}




mt_srand(1234); // to get a reproducible result
$q = new QRCODE("ABCDEFGHIJ", 'L');
//file_put_contents(__DIR__ . "/qrcode.png", $q->getPNG());
test("$q", gzuncompress(base64_decode("eJxdUEEOgzAM+8oewMHmzksQp10nIW0Hvg+Jk7S
	ZhdrGJJbj/X1+ftvK5fU9Lz0oAATtczw0DOLs9spoOhMDqgaNPDpN/HWHNkI+tMMJs1dOEAibg
	nXnPPXTHi6CJIAYkEj5oCq4Qa2S0lY+3UyBEQu6k1ph8i153WPL2FvHHFVm2IMtGXAOtrie4LR
	kT5AV7nEDlq1fhw==")));

// Testing charset mode switching:

mt_srand(1234);
$q = new QRCODE("9@", 'L');
test("$q", "[cols=21, rows=21, 111111100010101111111, 100000101010101000001, 101110101011001011101, 101110100000101011101, 101110101111101011101, 100000101110001000001, 111111101010101111111, 000000001000000000000, 110100110011101110110, 010110011011010101010, 101001100101001110101, 000111010110111110001, 011100111101011100100, 000000001010011010111, 111111101111101011000, 100000100000001000111, 101110100100111000111, 101110101000011100011, 101110100111011101101, 100000101111100101000, 111111101010010100110]");

mt_srand(1234);
$q = new QRCODE("A@", 'L');
test("$q", gzuncompress(base64_decode("eJxlkTEOwzAMA7/SB3Qgu+clRaeuBQI0Q76fyCJlpZWH2AxBneXne/1sy4P323fdc8MsAIyVdcqApbHGKeTwphPQacqYMRc364uerbbOZvk6CVREq3CHMiioZA55phd8ZgO+qrqc2WU3pEl0aUXLbRL/CUvjrltmS1PT9BpsGxX/ZHa9uWt8vxMUHa4keofYvQ6hG1+X")));

mt_srand(1234);
$q = new QRCODE("9@9", 'L');
test("$q", gzuncompress(base64_decode("eJxdUEEOwzAI+8oesIO9+15S9dRrpUrbYd9vwYYmQ1UDjgHHy3bs3/eLz8fn+CmhAiAYX8YFI0JYnFkFzETcoOqGUb8ZJv7Yng2P92wrYXGlBA7LVAS7+qnLSILN6s4dOeuCRbobUEpaW+9J9shTNirxOFvVuvtVKdCvtHB5PFhlOVlPxrKfOxpbLmJ2sK60kr2rXCHWE5kMX48=")));

mt_srand(1234);
$q = new QRCODE("A@9", 'L');
test("$q", gzuncompress(base64_decode("eJxdkEsOwkAMQ6/CAbqwu+ckiBVbpEqw4PqQ2J5hGlXtjJvPi2+P4/m+7twur+OjAxUAwXo6fjIqpNW3byWzFRfoNmXktcrEKdu94fbubRImVyRwGFNR2amnftZBvRtOssZ0E4mq0fiMDAwmSZYPS8sjY8ykSML9t9Xc0ol0k2lVMO33MJZj3cVYu4jVwXh4JpmE9y+a/1+P")));
mt_srand(1234);
$q = new QRCODE("9A9A", 'L');
test("$q", gzuncompress(base64_decode("eJxVUbkNw0AMWyUDuCDTZ5IgVdoABpzC69t6qdPBOB9BkJT0/u6//+vJ7XHsZ/wwCgDtRN0wUJAffxls3GAC+RIMySxs9o2pnbalzebNJMgiRhnbEE/BVCZcO2WldMMQFoxMwnRnhfQkYTZDu2UnCQ3UqDo32hPqUh8i4BhVzX0ZlTqYbDW5bKf2kDPpJNrD5wKfXV+T")));
mt_srand(1234);
$q = new QRCODE("9A9A", 'L');
test("$q", gzuncompress(base64_decode("eJxVUbkNw0AMWyUDuCDTZ5IgVdoABpzC69t6qdPBOB9BkJT0/u6//+vJ7XHsZ/wwCgDtRN0wUJAffxls3GAC+RIMySxs9o2pnbalzebNJMgiRhnbEE/BVCZcO2WldMMQFoxMwnRnhfQkYTZDu2UnCQ3UqDo32hPqUh8i4BhVzX0ZlTqYbDW5bKf2kDPpJNrD5wKfXV+T")));
// QRCODE - testing Unicode

mt_srand(1234);
$q = new QRCODE($unicode_string->toUTF8());
test("$q", gzuncompress(base64_decode("eJxtVDFuAzEM+0ofkIFE5ryk6NQ1QIB2yPcTWyLF
	C87J2YatIylavu/fx/3/dr1evv4ez5qwGoD97Cmx+2rvEAC1yI6CJ3tnhaxN1gJ7n722+gnpPSj
	+JGT3JuMxpLVAr0vvaOmMTn7KCN1ordBQbaEIHXKnSUodpKXWOVjsP0zUrvSLtnmN75CDXRalhI
	Ri7qRC21TWyQKHW9gmaldJJyM9TbyIGAhjiZ0oFCgNw1jgRpmYJEki0vwSFb5xhygHl02US1ln2
	5gyXRSQu4N8dkYHdtqhmS2UqO3Q4PWSC0bOphqUqZe4RmFW1K7Fxhk66UhVWFHDedVGwozMEJMc
	7kMTUTXVxRlfhc8vgw7HVatiyIyyZpz7zwtart3o")));
//file_put_contents(__DIR__ . "/example-QRCODE-chess-poker-utf8-bom.png", $q->getPNG());

mt_srand(1234);
$q = new QRCODE($unicode_string->toUCS2BE());
//file_put_contents(__DIR__ . "/example-QRCODE-chess-poker-ucs2-bom.png", $q->getPNG());
test("$q", gzuncompress(base64_decode("eJxtU0GOwkAM+8o+gIMtzvuS1Z64IiGxB77PNomd
	pGKgtM2YjGMnP7fH/e/7er18PR+vfGAuAHExbrFq5x+SrwhYgJmBwgYkwxWIx/m3AYlvIqksJwi
	dgEYbUklrq/k0l6row0cVucRm0nUd68hSR+t8FKFkQjiL2CHjVjMgfnd8/4qu5Ia0pRQ86IqGNG
	HrHZGDC4c0nBYkuriM9HaqZIazWFKezkIYQCts3dk6REVwsD2HhciDKvNnp1KXTq9j26q0UT2CB
	auNgsgbt6zZzn5Zabry1MV62Lx9VjUmpXxPkpUaXeeWGHSyolZ2GCON9jR2I7gzM+EaNe1z3IA5
	akDzGi6NLCfZ7NqaxjWpVIfPivYcyX38vgFand3m")));

//// QRCODE - testing binary sequences of 1 byte
//for($b = 0; $b < 256; $b++)
//	$q = new QRCODE(chr($b));

//// QRCODE - testing binary sequences of 2 bytes
//for($b = 0; $b < 256; $b += 5){
//	echo "$b\n";
//	for($c = 0; $c < 256; $c += 5){
//		$q = new QRCODE(chr($b).chr($c));
//	}
//}

//// QRCODE - testing random sequences of 3 bytes
//mt_srand(1777);
//for($i = 1000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255);
//	//echo rawurlencode($random_string), "\n";
//	$q = new QRCODE($random_string);
//}

//// QRCODE - testing random sequences of 4 bytes
//mt_srand(1777);
//for($i = 1000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256-1);
//	$random_string = chr(($r >> 8)&255) . chr($r&255);
//	$q = new QRCODE($random_string . $random_string);
//}

//// QRCODE - testing random sequences of 5 bytes
//mt_srand(1777);
//for($i = 1000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$h = mt_rand(0, 256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255)
//			. chr(($h >> 8)&255) . chr($h&255);
//	$q = new QRCODE($random_string);
//}

//// QRCODE - testing random sequences of 6 bytes
//mt_srand(1777);
//for($i = 1000; $i >= 1; $i--){
//	$r = mt_rand(0, 256*256*256-1);
//	$random_string = chr(($r >> 16)&255) . chr(($r >> 8)&255) . chr($r&255);
//	//echo rawurlencode($random_string), "\n";
//	$q = new QRCODE($random_string . $random_string);
//}

/*
// Detects max data limit using bisection method.
$mode = "H";
// min/max len of data:
$a = 100;
$b = 10000;
// Source for data:
$random = "";
for($i = 0; $i < $b; $i++)
	$random .= chr($i & 15);
do {
	// Detect end:
	if($a + 1 == $b){
		echo "max allowed data for $mode mode: $a\n";
		break;
	}
	// Data len for next test:
	$n = (int) (($a + $b) / 2);
	$data = substr($random, 0, $n);
	echo "testing $n in [$a,$b] bytes... ";
	// Testing if throws exception:
	$gotException = false;
	try {
		mt_srand(1234);
		$q = new QRCODE($data, $mode);
		file_put_contents(__DIR__ . "/qrcode.png", $q->getPNG());
	}
	catch(QRCODECapacityException $e){
		$gotException = true;
	}
	// Check result:
	if( $gotException ){
		echo "failed\n";
		$b = $n;
	} else {
		echo "success\n";
		$a = $n;
	}
} while(true);
 * 
 * 
 */