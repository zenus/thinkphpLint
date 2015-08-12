<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\ParseException;

/**
 * Parses "self::".
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:10:15 $
 */
class SelfOperator {
	
	
	/**
	 * Parses "self::".
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_double_colon, "expected `::' after `self'");
		if( $globals->isPHP(4) ){
			$logger->error($scanner->here(), "invalid `self::' (PHP 5)");
		}
		if( $pkg->curr_class === NULL ){
			throw new ParseException($scanner->here(), "invalid `self::': not inside a class");
		} else {
			return ClassStaticAccess::parse($globals, $pkg->curr_class);
		}
	}

}
