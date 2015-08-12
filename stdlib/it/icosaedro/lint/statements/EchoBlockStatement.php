<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\expressions\Expression;

/**
 * Parses "&lt;?= EXPR, EXPR, EXPR [;] ?&gt;".
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class EchoBlockStatement {
	
	/**
	 * Parses "&lt;?= EXPR, EXPR, EXPR [;] ?&gt;".
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals){
		$scanner = $globals->curr_pkg->scanner;
		$scanner->readSym();
		while(TRUE){
			$r = Expression::parse($globals);
			if( $r->isUnknown() ){
				# Expression class already gave error, no need for this:
				#$this->globals->curr_pkg->scanner->here()->warning("can't determine type of the argument of `<?= ... ?".">'")
			} else {
				$t = $r->getType();
				if( ! ( $r->isInt() || $r->isFloat() || $r->isString() ) ){
					// FIMXE: object implementing __toString() also allowed?
					$globals->logger->error($scanner->here(), "found " . $r->getType()
					. ". The arguments of the `<?= ... ?>' block must be of"
					. " type int, float or string.");
				}
			}
			
			if( $scanner->sym === Symbol::$sym_comma ){
				$scanner->readSym();
				
			} else if( $scanner->sym === Symbol::$sym_close_tag ){
				$scanner->readSym();
				return;
				
			} else if( $scanner->sym === Symbol::$sym_eof ){
				return;
				
			} else if( $scanner->sym === Symbol::$sym_semicolon ){
				$globals->logger->notice($scanner->here(), "useless `;' symbol");
				$scanner->readSym();
				$globals->expect(Symbol::$sym_close_tag, "missing closing tag");
				$scanner->readSym();
				return;
				
			} else {
				throw new ParseException($scanner->here(), "unexpected symbol " . $scanner->sym);
			}
		}
	}

}