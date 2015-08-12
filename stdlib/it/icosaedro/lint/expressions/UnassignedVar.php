<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\types\ArrayType;

/**
 * Parses a known but unassigned variable. Known variables have a type
 * which is already established, but the flow analysis marked that
 * variable as currently unassigned.
 * Unassigned variable must be followed by assignment, or must be followed
 * by array dereferencing operators (one or more) for arrays, and then an
 * assignment must follow. Examples:
 * <blockquote><pre>
 * $v1 = EXPR;
 * $v2[0][0] = EXPR;
 * $v3[0][] = EXPR;
 * </pre></blockquote>
 * Unassigned variables can also occur in the middle of an expression, but
 * here too an assignment must follow anyway; the result of the
 * sub-expression is the result of the evaluation of the assigned expression.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:50:32 $
 */
class UnassignedVar {

	/**
	 * Parses a known but unassigned variable.
	 * This function then marks the variable as assigned.
	 * We enter with the symbol sym_variable.
	 * @param Globals $globals Context of this parser.
	 * @param Variable $v Unassigned variable.
	 * @return Result Value assigned, possibly the unknown result if the parsing
	 * failed.
	 */
	public static function parse($globals, $v)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		$where = $scanner->here();
		$scanner->readSym();
		
		if( $scanner->sym === Symbol::$sym_assign ){
			// Most common case: $x = EXPR.
			$scanner->readSym();
			$r = Expression::parse($globals);
			if( ! $r->isUnknown() && ! $r->assignableTo($v->type) )
				$globals->logger->error($scanner->here(),
				"cannot assign ". $r->getType() . " to $v of type " . $v->type);
			$globals->accountVarLHS($v);
			return $r;
			
		} else if( $scanner->sym === Symbol::$sym_lsquare ){
			// Implicit array declaration: $x[?][?] = EXPR.
			// Collects keys:
			$scanner->readSym();
			$keys = /*.(Type[int]).*/ array();
			do {
				if( $scanner->sym === Symbol::$sym_rsquare ){
					$key = Globals::$int_type;
					$scanner->readSym();
					break;
				} else {
					$r = Expression::parse($globals);
					if( $r->isUnknown() ){
						$key = Globals::$mixed_type;
					} else if( $r->isInt() || $r->isString() ){
						$key = $r->getType();
					} else {
						$key = Globals::$mixed_type;
						$globals->logger->error($scanner->here(),
						"invalid array key of type " . $r->getType()
						. ". Hint: array keys must be int or string.");
					}
				}
				$keys[] = $key;
				$scanner->readSym();
				if( $scanner->sym === Symbol::$sym_lsquare ){
					$scanner->readSym();
				} else {
					break;
				}
			} while(TRUE);
			
			// Parse assignment:
			if( $scanner->sym !== Symbol::$sym_assign ){
				$globals->logger->error($where,
				"variable $v might not have been assigned");
				if( SkipUnknown::canSkip($scanner->sym) )
					/* $type = */ SkipUnknown::anything($globals);
				return Result::getUnknown();
			}
			$scanner->readSym();
			$r = Expression::parse($globals);
			
			// Build array type assigned:
			$a = $r->getType();
			for($i = count($keys) - 1; $i >= 0; $i--)
				$a = ArrayType::factory($keys[$i], $a);
			
			// Check type compatibility:
			if( ! $r->isUnknown() && ! $a->assignableTo($v->type) )
				$globals->logger->error($scanner->here(), "cannot assign $a to $v of type ".$v->type .". Hint: check number, type and order of the keys; check type of the assigned expression.");
			$globals->accountVarLHS($v);
			return $r;

		} else if( $scanner->sym === Symbol::$sym_lround ){
			// Catch common error "$v()" (variable function name):
			$globals->logger->error($scanner->here(),
			"invalid variable-name function (PHPLint restriction)");
			/* $type = */ SkipUnknown::anything($globals);
			return Result::getUnknown();
			
		} else {
			$globals->logger->error($where,
			"variable $v might not have been assigned");
			if( SkipUnknown::canSkip($scanner->sym) )
				/* $type = */ SkipUnknown::anything($globals);
			return Result::getUnknown();
		}
	}

}
