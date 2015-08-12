<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;

/**
 * Parses the "static::" access operator.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:10:15 $
 */
class StaticOperator {
	
	/**
	 * Parses the "static::" access operator.
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_double_colon, "expected `::' after `static'");
		if( $globals->isPHP(4) ){
			$logger->error($scanner->here(), "invalid `static::' (PHP 5)");
		}
		if( $pkg->curr_class == NULL ){
			$logger->error($scanner->here(), "invalid `static::': not inside a class");
			SkipUnknown::anything($globals);
			return Result::getUnknown();
		} else {
			return ClassStaticAccess::parse($globals, $pkg->curr_class);
		}
	}

}
