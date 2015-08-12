<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\expressions\Expression;

/**
 * Parses the <code>while()</code> statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 23:05:30 $
 */
class WhileStatement {
	 
	/**
	 * Parses the <code>while()</code> statement.
	 * @param Globals $globals
	 * @return int See {@link it\icosaedro\lint\Flow}
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "expected '('");
		$scanner->readSym();
		$r = Expression::parse($globals);
		$globals->expect(Symbol::$sym_rround, "expected closing ')' of the 'while' statement");
		$r->checkExpectedType($globals->logger, $scanner->here(), Globals::$boolean_type);
		$scanner->readSym();

		/*
			The statement
		 
				while(expr)
					P;

			is equivalent to 3 sequential statements:

				do{
					if(!EXPR) break;
					P;
					continue;
				}

			We start evaluating the execution path res for the first statement:

				if(!EXPR) break;
		*/
		if( $r->isTrue() ){
			$res = Flow::NEXT_MASK;
		} else if( $r->isFalse() ){
			$res = Flow::BREAK_MASK;
		} else {
			$res = Flow::NEXT_MASK | Flow::BREAK_MASK;
		}

		# Add execution path for the P statement:
		if( ($res & Flow::NEXT_MASK) == 0 )
			$globals->logger->error($scanner->here(), "unreachable statement");
		
		$pkg->loop_level++;
		if( $r->isTrue() ){
			$p = CompoundStatement::parse($globals);
		} else {
			$s = new AssignedVars($globals);
			$p = CompoundStatement::parse($globals);
			$s->restore();
		}
		$pkg->loop_level--;
		$res = $res | $p;

		# Add execution path for the "continue" statement:
		if( ($res & Flow::NEXT_MASK) != 0 ){
			$res = ($res & ~Flow::NEXT_MASK) | Flow::CONTINUE_MASK;
		}

		# Final result: translate "break" into "next":
		if( ($res & Flow::BREAK_MASK) != 0 ){
			$res = $res | Flow::NEXT_MASK;
		}
		$res = $res & (Flow::RETURN_MASK | Flow::NEXT_MASK);
		# FIXME: if res=0 it is an infinite loop.
		return $res;
	}
	
}

