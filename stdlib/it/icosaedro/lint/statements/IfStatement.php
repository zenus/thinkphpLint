<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\expressions\Expression;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 23:05:30 $
 */
class IfStatement {
	 
	/**
	 * Parses the if/else/elsif statement. We enter with the symbol "if".
	 * @param Globals $globals
	 * @return int See {@link it\icosaedro\lint\Flow}.
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "expected '(' after `if'");
		$scanner->readSym();
		$if_expr = Expression::parse($globals);
		$globals->expect(Symbol::$sym_rround, "expected closing ')' after 'if' condition");
		$if_expr->checkExpectedType($globals->logger, $scanner->here(), Globals::$boolean_type);
		$pkg->scanner->readSym();

		# Parse `then' branch:
		$before_set = new AssignedVars($globals);
		$then_path = CompoundStatement::parse($globals);

		# Parse `else' or `elseif' branch:
		if( $scanner->sym === Symbol::$sym_else
		|| $scanner->sym === Symbol::$sym_elseif ){
			
			# $then_set = assigned vars if "then" branch gets executed
			# $else_set = assigned vars if "else" branch gets executed
			# Final set will be the intersection $then_set, $else_set.

			if( ($then_path & Flow::NEXT_MASK) == 0 ){
				$then_set = $before_set;
			} else {
				$then_set = new AssignedVars($globals);
			}
			$before_set->restore();

			if( $scanner->sym === Symbol::$sym_elseif ){
				$else_path = self::parse($globals);
			} else {
				$scanner->readSym();
				$else_path = CompoundStatement::parse($globals);
			}
			if( ($else_path & Flow::NEXT_MASK) == 0 ){
				$else_set = clone $before_set;
			} else {
				$else_set = new AssignedVars($globals);
			}

			if( $if_expr->isTrue() ){
				$then_set->restore();
				$res = $then_path;
			} else if( $if_expr->isFalse() ){
				$else_set->restore();
				$res = $else_path;
			} else { # general case: non-static boolean expr
				if( ($then_path & Flow::NEXT_MASK) == 0 ){
					if( ($else_path & Flow::NEXT_MASK) == 0 ){
						// Useless: can't continue after if/else statement.
						$before_set->restore();
					} else {
						$else_set->restore();
					}
				} else {
					if( ($else_path & Flow::NEXT_MASK) == 0 ){
						$then_set->restore();
					} else {
						$then_set->intersection($else_set);
						$then_set->restore();
					}
				}
				$res = $then_path | $else_path;
			}
		} else {
			if( $if_expr->isTrue() ){
				# keep current assigned vars.
				$res = $then_path;
			} else {
				$before_set->restore();
				$res = $then_path | Flow::NEXT_MASK;
			}
		}

		return $res;
	}
	
}

