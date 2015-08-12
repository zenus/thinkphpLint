<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use it\icosaedro\utils\TestUnit as TU;


class testFileOutputStream extends TU {

	function run() /*. throws \Exception .*/
	{
		$tmp = __DIR__ . "/write-test.txt";
		/*. OutputStream .*/ $out = new FileOutputStream(File::fromLocaleEncoded(($tmp)));
		for($i = 0; $i < 10; $i++)
			$out->writeBytes("xxxxxxxxx\n");
		$out->close();
		if( filesize($tmp) != 100 )
			throw new \Exception("$tmp: invalid len");
		else
			unlink($tmp);
	}

}

$tu = new testFileOutputStream();
$tu->start();
