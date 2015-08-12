<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\ParseException;

/**
 * Parses the isset($v1, $v2, ...) function.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:10:15 $
 */
class IssetFunction {
	

	/**
	 * Parses the isset($v1, $v2, ...) function.
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		$logger = $globals->logger;
		$scanner = $globals->curr_pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "expected `(' after `isset'");
		do {
			$scanner->readSym();
			Assignable::parse($globals, Globals::$unknown_type, FALSE);
			if( $scanner->sym === Symbol::$sym_comma ){
				/* more elements in list */
			} else if( $scanner->sym === Symbol::$sym_rround ){
				$scanner->readSym();
				return Result::factory(Globals::$boolean_type);
			} else {
				throw new ParseException($scanner->here(), "expected variable name or closing ')' after isset() args, found " . $scanner->sym);
			}
		} while(TRUE);
	}

}
