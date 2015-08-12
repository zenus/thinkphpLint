<?php
namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";
use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\io\Base64InputStream;
use it\icosaedro\io\Base64OutputStream;
use it\icosaedro\io\StringInputStream;
use it\icosaedro\io\StringOutputStream;
use it\icosaedro\io\InputStream;
use it\icosaedro\io\OutputStream;
use it\icosaedro\io\IOException;
use it\icosaedro\io\FileInputStream;
use it\icosaedro\io\File;
use it\icosaedro\io\SplitOutputStream;

/**
 */
class testBase64InputStream extends TU {
	
	/**
	 * @param InputStream $in
	 * @param OutputStream $out
	 * @throws IOException
	 */
	private function copy($in, $out)
	{
		do {
			$s = $in->readBytes(1);
			if( $s === NULL ){
				$out->close();
				return;
			}
			$out->writeBytes($s);
		} while(true);
	}
	
	/**
	 * 
	 * @param string $s
	 * @return string
	 * @throws IOException
	 */
	private function loop($s)
	{
		$out = new StringOutputStream();
		$this->copy(new StringInputStream($s),
				new Base64OutputStream(
					new SplitOutputStream($out, 75, "\n")));
		//echo "$s ENCODING--> $out\n";
		
		$out2 = new StringOutputStream();
		$this->copy(
			new Base64InputStream(new StringInputStream($out->__toString())),
			$out2
		);
		//echo " DECODING--> $out2\n";
		return $out2->__toString();
	}
	
	
	function run() /*. throws \Exception .*/
	{
		TU::test($this->loop(""), "");
		TU::test($this->loop("a"), "a");
		TU::test($this->loop("ab"), "ab");
		TU::test($this->loop("abc"), "abc");
		TU::test($this->loop("abcd"), "abcd");
		TU::test($this->loop("abcde"), "abcde");
		TU::test($this->loop("abcdef"), "abcdef");
		
		$fn = File::fromLocaleEncoded(__FILE__);
		$in = new FileInputStream($fn);
		$out = new StringOutputStream();
		$this->copy($in, $out);
		$in->close();
		$out->close();
		$s = $out->__toString();
		TU::test($this->loop($s), $s);
	}
	

}


$tu = new testBase64InputStream();
$tu->start();
