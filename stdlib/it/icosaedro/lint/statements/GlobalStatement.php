<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Variable;

/**
 * Parses the "global $v1, $v2" statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/09/14 10:13:47 $
 */
class GlobalStatement {
	 
	/**
	 * Parses the "global $v1, $v2" statement.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if( $pkg->scope === 0 ){
			$globals->logger->warning($scanner->here(), "`global' declaration at global scope has no effect.");
		}
		$scanner->readSym();
		while(TRUE){
			
			$globals->expect(Symbol::$sym_variable, "expected variable name in global declaration");
			if( $pkg->scope === 0 ){  # erroneus 'global' at global scope:
				// ignore.
				
			} else {
				$name = $scanner->s;
				$g = $globals->searchVarInScope($name, 0);
				
				if( $g === NULL ){
					$globals->logger->error($scanner->here(), "variable \$$name still not found in global scope. Hint: declare this variable in global scope assigning a value to it.");
					
				} else {
					$v = $globals->searchVarInScope($name, $pkg->scope);
					if( $v === NULL ){
						$v = new Variable($name, FALSE, $scanner->here(), $pkg->scope);
						$v->type = $g->type;
						$v->is_global = TRUE;
						$v->assigned = TRUE;
						// We will set this only if it gets assigned:
						$v->assigned_once = FALSE;
						$globals->addVar($v);
						$globals->accountVarLHS($v);
					} else {
						$globals->logger->error($scanner->here(), "variable \$name: a local variable with the same name already exists");
					}
				}
			}
			
			$scanner->readSym();
			
			if( $scanner->sym === Symbol::$sym_comma ){
				$scanner->readSym();
			} else {
				break;
			}
		}
	}
	
}

