<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../../../stdlib/all.php";

/*. require_module 'zlib'; .*/

use Exception;
use ErrorException;
use it\icosaedro\utils\TestUnit;


class testZLIBCompressInputStream extends TestUnit {
	
	/**
	 * @param InputStream $in
	 * @param OutputStream $out
	 * @param int $chunk_size
	 * @throws IOException
	 */
	private static function copy($in, $out, $chunk_size)
	{
		do {
			$chunk = $in->readBytes($chunk_size);
			if( $chunk === NULL )
				break;
			$out->writeBytes($chunk);
		} while(TRUE);
		$out->close();
		$in->close();
	}
	
	
	/**
	 * @param string $uncompressed
	 * @param int $chunk_size
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function sample($uncompressed, $chunk_size)
	{
		$out = new StringOutputStream();
		$zlib = new ZLIBCompressOutputStream($out);
		self::copy(new StringInputStream($uncompressed), $zlib, $chunk_size);
		$compressed = $out->__toString();
		//echo "compressed: ", rawurlencode($compressed), "\n";
		
		// Try decompressing with the PHP's internal function:
		$uncompressed2 = gzuncompress($compressed);
		self::test($uncompressed2, $uncompressed);
		
		$unzlib = new ZLIBCompressInputStream(new StringInputStream($compressed));
		$out = new StringOutputStream();
		self::copy($unzlib, $out, $chunk_size);
		$uncompressed2 = $out->__toString();
		//echo "uncompressed: ", rawurlencode($uncompressed2), "\n";
		self::test($uncompressed2, $uncompressed);
	}
	

	/**
	 * @throws Exception
	 */
	public function run()
	{
		$chunk_size = 100;
		self::sample("", $chunk_size);
		self::sample("a", $chunk_size);
		self::sample("ab", $chunk_size);
		self::sample("abc", $chunk_size);
		self::sample("AabcdabcdabcdabcdabcdabcdZ\n", $chunk_size);
		self::sample(file_get_contents(__FILE__), $chunk_size);
		
		$s = "\0\0\0\0\0\0\0\0\0\0";
		$buf = new StringOutputStream();
		for($i = 100000; $i > 0; $i--)
			$buf->writeBytes($s);
		self::sample($buf->__toString(), 4*1024);
		
		$s = "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff";
		$buf = new StringOutputStream();
		for($i = 100000; $i > 0; $i--)
			$buf->writeBytes($s);
		self::sample($buf->__toString(), 4*1024);
	}
	
}

$t = new testZLIBCompressInputStream();
$t->start();
