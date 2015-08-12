<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\ParseException;

/**
 * Parses a single statement or a compound block.
 * Basically, this is just like ParseStatement() with the only difference
 * that it is called in structured statements where the old syntax with
 * Symbol::$sym_semicolon may appear. This old syntax is not supported by
 * PHPLint, so if this case is detected, simply raises a fatal error.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 23:05:30 $
 */
class CompoundStatement {

	/**
	 * Parses a single statement or a compound block.
	 * @param Globals $globals
	 * @return int Execution path (see {@link it\icosaedro\lint\Flow}.
	 */
	public static function parse($globals) {
		$scanner = $globals->curr_pkg->scanner;
		if ($scanner->sym === Symbol::$sym_colon) {
			throw new ParseException($scanner->here(), "unsupported old-style syntax. Please use {...} instead.");
		} else if ($scanner->sym === Symbol::$sym_lbrace) {
			// Compound "{...}":
			$scanner->readSym();
			$res = Flow::NEXT_MASK;
			while ($scanner->sym !== Symbol::$sym_rbrace) {
				if (($res & Flow::NEXT_MASK) == 0)
					$globals->logger->error($scanner->here(),
					"unreachable statement");
				$p = Statement::parse($globals);
				$res = ($res & ~Flow::NEXT_MASK) | $p;
			}
			$scanner->readSym();
			return $res;
		} else {
			return Statement::parse($globals);
		}
	}

}
