<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Package;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Scanner;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\lint\expressions\StaticExpression;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 23:05:30 $
 */
final class SwitchStatement {
	
	/*. forward static int function parse(Globals $globals); .*/
	
	/**
	 *
	 * @var Globals 
	 */
	private $globals;
	
	/**
	 * Current package.
	 * @var Package
	 */
	private $pkg;
	
	/**
	 *
	 * @var Scanner 
	 */
	private $scanner;
	
	/**
	 * Type of the expression in "switch(EXPR)". It is int, or string or
	 * UnknownType if the type cannot be determined or it is not valid.
	 * @var Type 
	 */
	private $t;
	
	/**
	 * "case" labels found.
	 * @var Result[int] 
	 */
	private $labels;
	
	/**
	 * Variables that were assigned before the switch() statement.
	 * @var AssignedVars 
	 */
	private $before_set;
	
	
	/**
	 *
	 * @param Globals $globals 
	 * @return void
	 */
	private function __construct($globals)
	{
		$this->globals = $globals;
		$this->pkg = $globals->curr_pkg;
		$this->scanner = $globals->curr_pkg->scanner;
		$this->labels = /*.(Result[int]).*/ array();
	}
	
	
	/**
	 * Parses "switch(EXPR){".
	 * @return void
	 */
	private function doHead()
	{
		$this->scanner->readSym();
		$this->globals->expect(Symbol::$sym_lround, "expected `('");
		$this->scanner->readSym();
		$r = Expression::parse($this->globals);
		$this->globals->expect(Symbol::$sym_rround, "expected `)'");

		# "case EXPR" expressions must have the same type of the switch(EXPR):
		$this->t = $r->getType();
		if( $r->isUnknown() ){
			//$this->globals->logger->error($this->scanner->here,
			//"switch(EXPR): cannot determine the type of the expression");
			$this->t = NULL;
		} else if( $r->isInt() || $r->isString() ){
			// OK, check "case EXPR" type.
		} else {
			$this->globals->logger->error($this->scanner->here(),
			"switch(EXPR): invalid expression of type "
			. $r->getType() . ". Expected int or string.");
			$this->t = NULL;
		}

		$this->scanner->readSym();
		if( $this->scanner->sym === Symbol::$sym_colon )
			throw new ParseException($this->scanner->here(),
				"unsupported old-style syntax; use {...} instead");
		$this->globals->expect(Symbol::$sym_lbrace, "expected `{'");
		$this->scanner->readSym();
		$this->pkg->loop_level++;
		$this->before_set = new AssignedVars($this->globals);
	}


	/**
	 * Checks and accounts a "case" label found. Detects repeated entries.
	 * @param Result $l
	 * @return void
	 */
	private function checkLabel($l)
	{
		if( $this->t === NULL )
			// Can't do any check.
			return;
		
		if( $l->isUnknown() )
			// Already signaled by expression parser.
			return;
		
		if( ! $l->getType()->equals($this->t) ){
			$this->globals->logger->error($this->scanner->here(),
			"invalid type " . $l->getType()
			. " for the `case' branch. Expected " . $this->t);
		}
		
		if( $l->getValue() === NULL )
			// Already signaled as error by expression parser.
			return;
		
		foreach($this->labels as $r){
			if( $l->getValue() === $r->getValue() ){
				$this->globals->logger->error($this->scanner->here(),
				"duplicated `case' value: " . $r->getValue());
				return;
			}
		}
		$this->labels[] = $l;
	}
	

	/**
	 * @return void
	 */
	private function checkAndDiscardUnreachableStatements()
	{
		$sym = $this->scanner->sym;
		if( $sym === Symbol::$sym_case
		|| $sym === Symbol::$sym_default
		|| $sym === Symbol::$sym_x_missing_default
		|| $sym === Symbol::$sym_rbrace
		)
			# `switch' branch is properly terminated.
			return;

		$this->globals->logger->error($this->scanner->here(),
		"unreachable statement");
		# Skip unreachable statements:
		do {
			/* $ignore = */ Statement::parse($this->globals);
		} while( ! ( $sym === Symbol::$sym_case
			|| $sym === Symbol::$sym_default
			|| $sym === Symbol::$sym_x_missing_default
			|| $sym === Symbol::$sym_rbrace ) );
	}


	
	
	/**
	 *
	 * @return int Execution flow (see {@link Flow}). 
	 */
	private function doBody()
	{
		// FIXME: must ignore comments between elements of the switch()/case.
		$found_default = FALSE;
		$found_x_default = FALSE;
		$scanner = $this->scanner;
		// Logical "and" of the defined vars in all branches:
		$result_set = /*.(AssignedVars).*/ NULL;
		$res = 0;
		while(TRUE){
			if( $scanner->sym === Symbol::$sym_case
			|| $scanner->sym === Symbol::$sym_default ){

				if( $scanner->sym === Symbol::$sym_case ){
					do {
						$scanner->readSym();
						$r = StaticExpression::parse($this->globals);
						$this->checkLabel($r);
						$this->globals->expect(Symbol::$sym_colon, "expected `:' after `case' expression");
						$scanner->readSym();
					} while( $scanner->sym === Symbol::$sym_case );

				} else { /* `default' branch */
					if( $found_default || $found_x_default )
						$this->globals->logger->error($scanner->here(),
						"multiple default branches");
					$found_default = TRUE;
					$scanner->readSym();
					$this->globals->expect(Symbol::$sym_colon, "expected `:' after `default'");
					$scanner->readSym();

				}

				/*
				 * Missing `break' in branch (i.e. branch fall-through) is
				 * allowed only if the branch is empty. Instead, if the branch
				 * contains at least one statement, fall-through requires
				 * `missing_break'.
				 */
				$found_statements = FALSE;
				$b = Flow::NEXT_MASK;
				while( ($b & Flow::NEXT_MASK) != 0
				&& $scanner->sym !== Symbol::$sym_case
				&& $scanner->sym !== Symbol::$sym_default
				&& $scanner->sym !== Symbol::$sym_x_missing_break
				&& $scanner->sym !== Symbol::$sym_x_missing_default
				&& $scanner->sym !== Symbol::$sym_rbrace
				){
					$found_statements = TRUE;
					$p = Statement::parse($this->globals);
					$b = ($b & ~Flow::NEXT_MASK) | $p;
				}

				if( ($b & Flow::NEXT_MASK) == 0 ){
					$this->checkAndDiscardUnreachableStatements();

				} else if( $scanner->sym === Symbol::$sym_case
				|| $scanner->sym === Symbol::$sym_default ){
					if( $found_statements )
						$this->globals->logger->warning($scanner->here(), "improperly terminated non-empty `switch' branch -- missing `break;'?");

				} else if( $scanner->sym === Symbol::$sym_x_missing_break ){
					$scanner->readSym();
					$this->globals->expect(Symbol::$sym_x_semicolon, "expected `;'");
					$scanner->readSym();
					$this->checkAndDiscardUnreachableStatements();

				} else if( $scanner->sym === Symbol::$sym_rbrace
				|| $scanner->sym === Symbol::$sym_x_missing_default ){
					$b = $b | Flow::BREAK_MASK;

				}

				$res = $res | $b & (Flow::RETURN_MASK | Flow::CONTINUE_MASK);
				# Translate branch `break' into `switch()' `next' execution path:
				if( ($b & Flow::BREAK_MASK) != 0 )
					$res = $res | Flow::NEXT_MASK;

				if( ($b & Flow::BREAK_MASK) != 0 ){
					$branch_set = new AssignedVars($this->globals);
					if( $result_set === NULL ){
						$result_set = $branch_set;
					} else {
						$result_set->intersection($branch_set);
					}
				}
				$this->before_set->restore();

			} else if( $scanner->sym === Symbol::$sym_x_missing_default ){
				if( $found_default || $found_x_default )
					$this->globals->logger->error($scanner->here(),
					"multiple default branches");
				$found_x_default = TRUE;
				$scanner->readSym();
				$this->globals->expect(Symbol::$sym_x_colon, "expected `:' in meta-code");
				$scanner->readSym();
				$res = $res | Flow::NEXT_MASK;
				$result_set = clone $this->before_set;

			} else if( $scanner->sym === Symbol::$sym_rbrace ){
				if( $found_default || $found_x_default ){
					if( $result_set === NULL )
						// no branches.
						$this->before_set->restore();
					else
						$result_set->restore();
				} else {
					$this->globals->logger->warning($scanner->here(), "missing `default:' branch in `switch'");
					$res = $res | Flow::NEXT_MASK;  # avoid "unreachable statement"
					$this->before_set->restore();
				}
				$scanner->readSym();
				break;

			} else {
				throw new ParseException($scanner->here(), "unexpected symbol " . $scanner->sym);

			}
		}
		$this->pkg->loop_level--;

		return $res;
	}
	
	 
	/**
	 * @param Globals $globals
	 * @return int See {@link it\icosaedro\lint\Flow}
	 */
	public static function parse($globals)
	{
		$parser = new self($globals);
		$parser->doHead();
		return $parser->doBody();
	}
	
}

