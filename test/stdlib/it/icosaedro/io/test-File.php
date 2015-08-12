<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";
use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\io\File;

/**
 */
class testFile extends TU {
	
	function run() /*. throws \Exception .*/
	{
		if( DIRECTORY_SEPARATOR === "/" ){
			$D = "";
		} else {
			$D = "C:";
		}
		
		// test constructor():
		$f = new File( UString::fromASCII("$D/a/../b") );
		TU::test($f->getLocaleEncoded(), "$D/b");
		
		$f = new File( UString::fromASCII("$D/a/b/../..//c") );
		TU::test($f->getLocaleEncoded(), "$D/c");
		
		$cwd = File::getCWD();
		$f = new File( UString::fromASCII("a/b/../..//c"), $cwd );
		TU::test($f->getLocaleEncoded(), "$cwd/c");
		
		// test getExtension() and getBaseName():
		$f = File::fromLocaleEncoded("$D/a/b/data.txt");
		TU::test($f->getExtension(), ".txt");
		TU::test($f->getBaseName()->toASCII(), "$D/a/b/data");
		
		$f = File::fromLocaleEncoded("$D/a/b/data.");
		TU::test($f->getExtension(), "");
		TU::test($f->getBaseName()->toASCII(), "$D/a/b/data.");
		
		$f = File::fromLocaleEncoded("$D/a/b/data..");
		TU::test($f->getExtension(), "");
		TU::test($f->getBaseName()->toASCII(), "$D/a/b/data..");
		
		$f = File::fromLocaleEncoded("$D/a/b/.txt");
		TU::test($f->getExtension(), "");
		TU::test($f->getBaseName()->toASCII(), "$D/a/b/.txt");
		
		$f = File::fromLocaleEncoded("$D/a/b/..txt");
		TU::test($f->getExtension(), "");
		TU::test($f->getBaseName()->toASCII(), "$D/a/b/..txt");
		
		$f = File::fromLocaleEncoded("$D/a/b/...txt");
		TU::test($f->getExtension(), "");
		TU::test($f->getBaseName()->toASCII(), "$D/a/b/...txt");
		
		$f = File::fromLocaleEncoded("$D/a/b/....txt");
		TU::test($f->getExtension(), ".txt");
		TU::test($f->getBaseName()->toASCII(), "$D/a/b/...");
		
		// test relativeTo():
		$data = new File( UString::fromASCII("$D/a/b/c/d") );
		$base = new File( UString::fromASCII("$D/a/b/e/f") );
		TU::test($data->relativeTo($base)->toASCII(), "../../c/d");
		
		$data = new File( UString::fromASCII("$D/a/b/c/d") );
		$base = new File( UString::fromASCII("$D/a/b/e") );
		TU::test($data->relativeTo($base)->toASCII(), "../c/d");
		
		$data = new File( UString::fromASCII("$D/a/b/c/d") );
		$base = new File( UString::fromASCII("$D/a/b") );
		TU::test($data->relativeTo($base)->toASCII(), "c/d");
		
		$data = new File( UString::fromASCII("$D/a/b/c/d") );
		$base = new File( UString::fromASCII("$D/a") );
		TU::test($data->relativeTo($base)->toASCII(), "b/c/d");
		
		$data = new File( UString::fromASCII("$D/a/b/c/d") );
		if( DIRECTORY_SEPARATOR === "\\" ){
			$base = new File( UString::fromASCII("$D") );
			TU::test($data->relativeTo($base)->toASCII(), "a/b/c/d");

			$data = new File( UString::fromASCII("$D/a/b/c/d") );
			$base = new File( UString::fromASCII("Z:") );
			TU::test($data->relativeTo($base)->toASCII(), "$D/a/b/c/d");
		}
	}
	

}


$tu = new testFile();
$tu->start();
