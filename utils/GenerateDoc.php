<?php
require_once __DIR__ . "/../stdlib/all.php";
use it\icosaedro\utils\Strings;
use it\icosaedro\io\File;
use it\icosaedro\io\IOException;
use it\icosaedro\io\OutputStream;
use it\icosaedro\io\ResourceOutputStream;
use it\icosaedro\regex\UPattern;
use it\icosaedro\utils\UString;
use it\icosaedro\lint\Linter;

/**
 * Program that generates the HTML documentation about the specified PHP
 * source files. Syntax of the command:
 * <blockquote><pre>
 * php GenerateDoc.php FILE(s)
 * </pre></blockquote>
 * where FILE(s) is a list of files or directories for which the document(s)
 * must be generated. If a directory is specified, all the source files with
 * <code>.php</code> extension beneath it are processed. The generated documents
 * are saved in the same directory of the corresponding source, so every
 * MyProg.php source file will have its own MyProg.html document generated.
 * @package GenerateDoc
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/27 19:22:20 $
 */

/*. private .*/ class GenerateDoc {
	
	/**
	 * Report and errors sent here.
	 * @var OutputStream 
	 */
	private static $os;
	
	
	/**
	 * If the files are modules.
	 */
	private static $is_module = FALSE;
	
	/**
	 * If set, exclude files whose filename matches this pattern.
	 * @var UPattern 
	 */
	private static $exclude_pattern;
	
	/**
	 * If set, only files that matches this pattern are included.
	 * @var UPattern 
	 */
	private static $include_pattern;
	
	/**
	 * Extension of the HTML files. Default ".html".
	 */
	private static $extension = ".html";
	
	/**
	 * If not null, the sources root set by "--move SRCROOT DOCROOT".
	 * @var File 
	 */
	private static $move_from;
	
	/**
	 * If not null, the docs root set by "--move SRCROOT DOCROOT".
	 * @var File 
	 */
	private static $move_to;
	
	
	/**
	 * Moves the generated document to its destination set with the --move
	 * command line option. If not set, does nothing.
	 * @param File $doc 
	 * @throws IOException
	 */
	private static function moveToDocRoot($doc)
	{
		if( self::$move_from === NULL )
			return;
		$p = $doc->getParentFile();
		$intermediate_dirs = /*.(UString[int]).*/ array();
		while( ! ($p === NULL || $p->equals(self::$move_from) ) ){
			$intermediate_dirs[] = $p->getName();
			$p = $p->getParentFile();
		}
		if( $p === NULL ){
			self::$os->writeBytes("ERROR: $doc is not sub-file of "
			. self::$move_from);
			$doc->delete();
		}
		// Creates intermediate dirs:
		$dest = self::$move_to;
		for($i = count($intermediate_dirs)-1; $i >= 0; $i--){
			$dest = new File($dest->toUString()
				->append( UString::fromASCII("/") )
				->append( $intermediate_dirs[$i] ));
			if( ! $dest->exists() ){
				//echo "making directory $dest\n";
				mkdir($dest->getLocaleEncoded());
			}
		}
		$dest = new File( $dest->toUString()
			->append( UString::fromASCII("/") )
			->append( $doc->getName() ));
		//echo "move to $dest\n";
		$doc->rename($dest);
	}
	
	
	/**
	 *
	 * @param File $f 
	 * @return int Number of errors.
	 * @throws IOException
	 */
	private static function docFile($f)
	{
		self::$os->writeBytes("$f ...\n");
		if( self::$exclude_pattern !== NULL
		&& self::$exclude_pattern->match($f->getName()) )
			return 0;
		
		if( self::$include_pattern !== NULL
		&& ! self::$include_pattern->match($f->getName()) )
			return 0;
		
		//self::$os->writeBytes("Generating HTML for $f ...\n");
		$err = Linter::main(self::$os,
			array(
				"PHPLint",
				"--php-version", "5",
				self::$is_module? "--is-module" : "--no-is-module",
				"--print-path", "relative",
				"--print-errors",
				"--print-warnings",
				"--no-print-notices",
				"--ascii-ext-check",
				"--ctrl-check",
				"--no-print-source",
				"--print-context",
				"--print-line-numbers",
				"--no-overall",
				"--modules-path", __DIR__ . "/../modules",
				"--doc",
				"--doc-extension", self::$extension,
				$f->getLocaleEncoded()
			)
		);
		
		$doc = new File($f->getBaseName()->append( UString::fromASCII(self::$extension) ));
		if( $doc->exists() )
			self::moveToDocRoot($doc);
		
		return $err;
	}
	
	/**
	 *
	 * @param File $d
	 * @return int Number of errors.
	 * @throws IOException
	 */
	private static function docDir($d)
	{
		$err = 0;
		$files = $d->listFiles();
		foreach($files as $f){
			if( $f->isFile() ){
				if( $f->getExtension() === ".php" )
					$err += self::docFile($f);
			} else if( $f->isDirectory() ){
				$err += self::docDir($f);
			}
		}
		return $err;
	}
	
	
	/**
	 * Generate the document(s) about a file or directory. If a directory is
	 * specified, all the files with ".php" extension beneath that directory are
	 * documented.
	 * @param string $what Path of the file or directory.
	 * @return int Exit status: 0 = success, 1 = failure. 
	 * @throws IOException
	 */
	private static function doDoc($what)
	{
		$w = File::fromLocaleEncoded($what, File::getCWD());
		if( ! $w->exists() ){
			self::$os->writeBytes("ERROR: file or directory $w does not exist\n");
			return 1;
		}
		if( $w->isFile() )
			return self::docFile($w);
		else
			return self::docDir($w);
	}
	
	/**
	 * @throws IOException
	 */
	private static function help()
	{
		self::$os->writeBytes("GenerateDoc\n"
."--help               This help.\n"
."--exclude-pattern P  Exclude files whose filename matches the given pattern.\n"
."                     Use a valid UPattern. Example: to exclude test files\n"
."                     set P = \"test-\". Files without \".php\" extension are\n"
."                     excluded anyway.\n"
."--include-pattern P  Include only those files whose filename matches the\n"
."                     the pattern. The \".php\" extension is mandatory.\n"
."--[no-]is-module     If files parsed are modules (FALSE).\n"
."--doc-extension EXT  Extension for generated HTML docs (default: \".html\").\n"
."--move SRCROOT DOCROOT\n"
."       Moves the generated documents from the sources subtree directory to the\n"
."       documents directory, creating any intermediate subdirectory inside the\n"
."       documents directory. DOCROOT must already exist. Example:\n"
."       --move /my/src /my/docs/src\n"
."       will move /my/src/lib/MyLib.html to /my/docs/src/lib/MyLib.html\n"
."       creating the directory /my/docs/src/lib if it does not exist.\n");
	}
	

	/**
	 * @param OutputStream $os Report and errors sent here.
	 * @param string[int] $argv 
	 * @return int Number of errors.
	 * @throws IOException
	 */
	public static function main($os, $argv)
	{
		self::$os = $os;
		$err = 0; // errors counter
		for($i = 1; $i < count($argv); $i++){
			$a = $argv[$i];
			switch($a){
				
			case "--help":  self::help(); break;
			
			case "--exclude-pattern":
				$i++;
				if( $i >= count($argv) ){
					$err++;
					$os->writeBytes("ERROR: missing argument to --exclude-pattern\n");
				} else {
					$p = UString::fromASCII($argv[$i]);
					self::$exclude_pattern = new UPattern($p);
				}
				break;
			
			case "--include-pattern":
				$i++;
				if( $i >= count($argv) ){
					$err++;
					$os->writeBytes("ERROR: missing argument to --include-pattern\n");
				} else {
					$p = UString::fromASCII($argv[$i]);
					self::$include_pattern = new UPattern($p);
				}
				break;
			
			case "--is-module":
				self::$is_module = TRUE;
				break;
			
			case "--no-is-module":
				self::$is_module = FALSE;
				break;
			
			case "--doc-extension":
				$i++;
				if( $i >= count($argv) ){
					$os->writeBytes("ERROR: missing argument to --doc-extension\n");
				} else {
					self::$extension = $argv[$i];
				}
				break;
			
			case "--move":
				if( $i + 2 >= count($argv) ){
					$err++;
					$os->writeBytes("ERROR: missing argument(s) to --move\n");
					return $err; // stop immediately, does not pollute src dir!
				}
				self::$move_from = File::fromLocaleEncoded($argv[$i+1], File::getCWD());
				self::$move_to   = File::fromLocaleEncoded($argv[$i+2], File::getCWD());
				if( ! self::$move_from->isDirectory() ){
					$err++;
					$os->writeBytes("ERROR: source tree does not exist or it is not a directory\n");
					return $err; // stop immediately, does not pollute src dir!
				}
				if( ! self::$move_to->isDirectory() ){
					$err++;
					$os->writeBytes("ERROR: docs tree does not exist or it is not a directory\n");
					return $err; // stop immediately, does not pollute src dir!
				}
				$i += 2;
				break;
				
			default:
				if( Strings::startsWith($a, "-") ){
					$os->writeBytes("ERROR: unknown option $a. Try --help.\n");
					$err++;
				} else {
					try {
						$err += self::doDoc($a);
					}
					catch(IOException $e){
						$os->writeBytes("ERROR: $e\n");
						$err++;
					}
				}
			}
		}
		return $err;
	}

}

$os = new ResourceOutputStream( fopen("php://stdout", "wb") );
GenerateDoc::main($os, $argv);