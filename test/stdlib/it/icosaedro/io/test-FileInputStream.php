<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

use it\icosaedro\utils\TestUnit as TU;


class testFileInputStream extends TU {

	function run() /*. throws \Exception .*/
	{
		/*. InputStream .*/ $in = new FileInputStream( File::fromLocaleEncoded(__FILE__));
		$len = 0;
		do {
			$buf = $in->readBytes(10);
			if( $buf === NULL )
				break;
			$len += strlen($buf);
		} while(true);
		$in->close();
		if( $len != filesize(__FILE__) )
			throw new \Exception("read only $len");
	}

}

$tu = new testFileInputStream();
$tu->start();
