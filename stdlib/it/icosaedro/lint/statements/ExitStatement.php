<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\expressions\Expression;

/**
 * Parses the exit() and die() statements.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class ExitStatement {
	 
	/**
	 * Parses the exit() and die() statements.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		if( $scanner->sym === Symbol::$sym_lround ){
			$scanner->readSym();
			if( $scanner->sym !== Symbol::$sym_rround ){
				$r = Expression::parse($globals);
				if( $r->isUnknown() ){
					// ignore
				} else if( ! ($r->isInt() || $r->isString()) ){
					$globals->logger->error($scanner->here(),
					"the exit status must be int or string");
				}
				# FIXME: check the int value in [0,254]
			}
			$globals->expect(Symbol::$sym_rround, "expected `)'");
			$scanner->readSym();
		}
	}
	
}

