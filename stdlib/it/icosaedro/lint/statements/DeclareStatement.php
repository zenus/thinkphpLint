<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\expressions\StaticExpression;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class DeclareStatement {

	 
	/**
	 * @param Globals $globals
	 * @return void
	 */
	private static function parseDirective($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;
		$globals->expect(Symbol::$sym_identifier, "expected identifier");
		$s = $scanner->s;
		if( $s !== "ticks" && $s !== "encoding" ){
			$logger->warning($scanner->here(),
			"unknown directive \"$s\"");
		}
		$scanner->readSym();

		$globals->expect(Symbol::$sym_assign, "expected `='");
		$scanner->readSym();

		$r = StaticExpression::parse($globals);
		// FIXME: what with $r?
	}
	
	 
	/**
	 * @param Globals $globals
	 * @return int See Flow class.
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "expected `('");
		$scanner->readSym();
		while(TRUE){
			self::parseDirective($globals);
			if( $scanner->sym === Symbol::$sym_comma ){
				$scanner->readSym();
			} else {
				break;
			}
		}
		$globals->expect(Symbol::$sym_rround, "expected `,' or `)'");
		$scanner->readSym();
		return CompoundStatement::parse($globals);
	}
	
}

