<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../../../stdlib/all.php";

/*. require_module 'zlib'; .*/

use Exception;
use ErrorException;
use it\icosaedro\utils\TestUnit;
use it\icosaedro\utils\UString;


class testGZIPInputStream extends TestUnit {
	
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
		$name = UString::fromASCII("test.gz");
		$comment = UString::fromASCII("Testing GZIPOutputStream and GZIPInputStream.");
		
		$out = new StringOutputStream();
		$gzip = new GZIPOutputStream($out, $name, $comment, -1);
		//$gzip = new GZIPOutputStream($out, NULL, NULL, 6);
		self::copy(new StringInputStream($uncompressed), $gzip, $chunk_size);
		$compressed = $out->__toString();
		//echo "compressed: ", rawurlencode($compressed), "\n";
		
		// Try decompressing with the PHP's internal function:
		$uncompressed2 = gzdecode($compressed);
		self::test($uncompressed, $uncompressed2);
		
		$gunzip = new GZIPInputStream(new StringInputStream($compressed));
		$out = new StringOutputStream();
		self::copy($gunzip, $out, $chunk_size);
		$uncompressed2 = $out->__toString();
		//echo "uncompressed: ", rawurlencode($uncompressed2), "\n";
		
		self::test($gunzip->NAME, $name);
		self::test($gunzip->COMMENT, $comment);
		self::test($uncompressed2, $uncompressed);
	}
	

	/**
	 * @throws Exception
	 */
	public function run()
	{
		$chunk_size = 100;
		for($chunk_size = 1; $chunk_size < 10; $chunk_size++){
			self::sample("", $chunk_size);
			self::sample("a", $chunk_size);
			self::sample("ab", $chunk_size);
			self::sample("abc", $chunk_size);
			self::sample("AabcdabcdabcdabcdabcdabcdZ\n", $chunk_size);
			self::sample(file_get_contents(__FILE__), $chunk_size);
		}
		
		$s = "\0\0\0\0\0\0\0\0\0\0";
		$buf = new StringOutputStream();
		for($i = 1000; $i > 0; $i--)
			$buf->writeBytes ($s);
		self::sample($buf->__toString(), 512);
		
		$s = "AmBnCoDpEqFrGsHtIuJvKwL";
		$buf = new StringOutputStream();
		for($i = 1000; $i > 0; $i--)
			$buf->writeBytes ($s);
		self::sample($buf->__toString(), 512);
	}
	
}


$t = new testGZIPInputStream();
$t->start();
