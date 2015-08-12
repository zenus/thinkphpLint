<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\docblock\DocBlock;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\types\VoidType;
use it\icosaedro\lint\types\ArrayType;

/**
 * Parses an unknown variable.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:38:42 $
 */
class UnknownVar {

	/**
	 * Parses an unknown variable. Caller must check the consistency between
	 * DocBlock (if present) and PHPLint meta-code (if present), for example
	 * allowing either the first or the second but not both.
	 * Unknown variable must be followed by assignment, or must be followed
	 * by array dereferencing operators (one or more) for arrays and then
	 * assigned.
	 * Examples:
	 * <blockquote><pre>
	 * $unknown1 = "abc"; // implicit string type
	 * $unknown2[0][0] = "abc"; // implicit string[int][int] type
	 * $unknown3[] = "abc"; // implicit string[int] type
	 * </pre></blockquote>
	 * Unknown variables can also occur in the middle of an expression, but
	 * here too an assignment must follow.
	 * This function parses also the assigned expression.
	 * This function also adds the new variable to the context, but also checks
	 * the same variable be not defined in the expression itself, because
	 * recursive variable implicit declaration is not supported and gives error.
	 * <br>
	 * We enter with the symbol sym_variable.
	 * @param Globals $globals Context of this parser.
	 * @param DocBlock $docblock DocBlock or NULL if not available.
	 * @param boolean $is_private If declared private either in the DocBlock
	 * or in PHPLint meta-code.
	 * @param Type $type Declared type, either in DocBlock or PHPLint meta-code,
	 * or NULL if not available.
	 * @return Result Value assigned, possibly the unknown result if parsing
	 * failed for some reason or no assignment follows because simply the
	 * name of the variable was misspelled.
	 */
	public static function parse($globals, $docblock, $is_private, $type)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		$where = $scanner->here();
		$name = $scanner->s;
		
		$scanner->readSym();
		if( $scanner->sym === Symbol::$sym_assign ){
			// Most common case: $x = EXPR.
			$scanner->readSym();
			$r = Expression::parse($globals);
			$v = $globals->searchVar($name);
			if( $v === NULL ){
				$v = new Variable($name, $is_private, $where, $pkg->scope);
				if( $type === NULL ){
					if( $r->isVoid() )
						$globals->logger->error($scanner->here(), "cannot assign void");
					else
						$v->type = $r->getType();
				} else if( $type instanceof VoidType ){
					$globals->logger->error($scanner->here(), "vvoid type not allowed for variables");
				} else {
					$v->type = $type;
					if( ! $r->isUnknown() && ! $r->assignableTo($v->type) )
						$globals->logger->error($scanner->here(),
						"cannot assign " . $r->getType() . " to $v of type ".$v->type);
				}
				$v->docblock = $docblock;
				$v->assigned = TRUE;
				$v->assigned_once = TRUE;
				$globals->addVar($v);
			} else {
				$globals->logger->error($where,
				"undefined variable $v recursively assigned in the expression that follows");
			}
			return $r;
			
		} else if( $scanner->sym === Symbol::$sym_lsquare ){
			// Implicit array declaration: $x[?][?] = EXPR:
			$scanner->readSym();
			$keys = /*.(Type[int]).*/ array();
			do {
				if( $scanner->sym === Symbol::$sym_rsquare ){
					$keys[] = Globals::$int_type;
					$scanner->readSym();
					break;
				}
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
				$globals->logger->error($where, "variable \$$name does not exist");
				return Result::getUnknown();
			}
			$scanner->readSym();
			$r = Expression::parse($globals);
			
			$a = $r->getType();
			for($i = count($keys) - 1; $i >= 0; $i--)
				$a = ArrayType::factory($keys[$i], $a);
			
			$v = $globals->searchVar($name);
			if( $v === NULL ){
				// Creates a new variable:
				$v = new Variable($name, $is_private, $where, $pkg->scope);
				$v->decl_in = $where;
				$v->scope = $pkg->scope;
				if( $type === NULL ){
					$v->type = $a;
				} else {
					$v->type = $type;
					if( ! $r->isUnknown() && ! $a->assignableTo($type) )
						$globals->logger->error($scanner->here(), "cannot assign $a to $v of type $type. Hint: check number, type and order of the keys; check type of the assigned expression.");
				}
				$v->docblock = $docblock;
				$v->is_private = $is_private;
				$v->assigned = TRUE;
				$v->assigned_once = TRUE;
				$globals->addVar($v);
			} else {
				$globals->logger->error($where, "undefined variable $v recursively assigned in the expression that follows (PHPLint limitation)");
			}
			return $r;

		} else if( $scanner->sym === Symbol::$sym_lround ){
			// Catch common error "$v()" (variable function name):
			$globals->logger->error($scanner->here(), "invalid variable-name function (PHPLint restriction)");
			/* $type = */ SkipUnknown::anything($globals);
			return Result::getUnknown();
			
		} else {
			$globals->logger->error($where, "variable \$$name does not exist");
			if( SkipUnknown::canSkip($scanner->sym) )
				/* $type = */ SkipUnknown::anything($globals);
			return Result::getUnknown();
		}
	}

}
