<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\expressions\Expression;

/**
 * Parses the <code>do...while()</code> statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 23:05:30 $
 */
class DoWhileStatement {
	 
	/**
	 * Parses the <code>do...while()</code> statement.
	 * @param Globals $globals
	 * @return int See {@link it\icosaedro\lint\Flow}
	 */
	public static function parse($globals)
	{
		/*
			do{
				P;
			}while(EXPR);

			is equivalent to two statements inside a loop:

			do{
				P;
				if( EXPR )
					continue;
				else
					break;
			}while(TRUE);

			We start evaluating the first statement P:
		*/
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$pkg->loop_level++;
		$res = CompoundStatement::parse($globals);
		$pkg->loop_level--;
		$globals->expect(Symbol::$sym_while, "expected 'while' in do...while() statement");
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "expected '('");
		$scanner->readSym();
		$r = Expression::parse($globals);
		$r->checkExpectedType($globals->logger, $scanner->here(), Globals::$boolean_type);
		$globals->expect(Symbol::$sym_rround, "expected closing ')' of while(...)");
		$scanner->readSym();
		# Add execution path for the hidden if() statement.
		# If P has no `next' path, if() is never executed:
		if( ($res & Flow::NEXT_MASK) == 0 ){
			#
		} else if( $r->isTrue() ){
			# If EXPR=TRUE the if() statement reduces to `continue':
			$res = $res & ~Flow::NEXT_MASK | Flow::CONTINUE_MASK;
		} else if( $r->isFalse() ){
			# If EXPR=FALSE the if() statement reduces to `break':
			$res = $res & ~ Flow::NEXT_MASK | Flow::BREAK_MASK;
		} else {
			# General case continue or break:
			$res = $res | Flow::CONTINUE_MASK | Flow::BREAK_MASK;
		}

		# Compute resulting execution path:
		$res = $res & ~(Flow::NEXT_MASK | Flow::CONTINUE_MASK);
		if( ($res & Flow::BREAK_MASK) != 0 ){
			$res = $res & ~ Flow::BREAK_MASK | Flow::NEXT_MASK;
		}
		return $res;
	}
	
}

