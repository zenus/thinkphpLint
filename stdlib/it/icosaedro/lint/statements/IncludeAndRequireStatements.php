<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\io\File;
use InvalidArgumentException;

/**
 * Parses the "include", "include_once" and "require" statements.
 * The specified files are not loaded recursively, nor are they parsed:
 * only "require_once" is allowed by PHPLint to load libraries.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class IncludeAndRequireStatements {
	 
	/**
	 * @param Globals $globals
	 * @param string $what Must one of "include", "include_once" or "require".
	 * Remember: "require_once" has its own specific parser.
	 * @return void
	 */
	public static function parse($globals, $what)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$r = Expression::parse($globals);
		$here = $scanner->here();
		$r->checkExpectedType($globals->logger, $here, Globals::$string_type);
		if( $r->isString() ){
			if( $r->getValue() === NULL ){
				// Cannot evaluate statically the file name.
				// Should we log a message?
				
			} else {
				$s = $r->getValue();
				try {
					$fn = File::fromLocaleEncoded($s);
				}
				catch(InvalidArgumentException $e){
					$globals->logger->warning($here, "$what \"$s\": "
					. $e->getMessage() . ". Hint: if using relative pathfile, check `include_path' in php.ini. Under PHP5 the magic constant __DIR__ gives the directory of the current source.");
				}
			}
		}
	}
}
