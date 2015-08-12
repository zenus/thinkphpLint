<?php

namespace it\icosaedro\lint\expressions;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\UnknownType;
use it\icosaedro\lint\types\ArrayType;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\ParseException;

/**
 * Parses an assignable item, that is a left-hand side (LHS) variable.
 * LHS can be a variable, a property, or an element of an array.
 * LHS appears as actual argument passed by reference,
 * as argument of isset() and inside Expression::e18() (++/-- operators).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:16:03 $
 */
class Assignable {
	
	/*. forward public static void function
		parse(Globals $globals, Type $type, boolean $gets_assigned); .*/
	
	/**
	 * Applying the "[" operator to type $type.
	 * @param Globals $globals
	 * @param Type $type Type of the dereferenced array.
	 * @return Type Type of its elements.
	 */
	private static function dereferenceArray($globals, $type)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		if( $type instanceof UnknownType )
			return SkipUnknown::anything($globals);
		
		if( ! ($type instanceof ArrayType) ){
			$globals->logger->error($scanner->here(), "`[' operator applied to non-array type $type");
			return SkipUnknown::anything($globals);
		}
		
		$a = cast(ArrayType::NAME, $type);
		
		$scanner->readSym();
		
//		if( $scanner->sym === Symbol::$sym_rsquare ){
//			// Found ...[] = EXPR
//			if( $a->getIndex() === Globals::$string_type )
//				$globals->logger->error($scanner->here(),
//				"array index is not int: " . $a->getIndex());
//			$scanner->readSym();
//			$globals->expect(Symbol::$sym_assign,
//			"expected array element assignment `[] = EXPR'");
//			return self::parse($globals, $a->getElem(), TRUE);
//		}
		
		// Found ...[EXPR]
		$r = Expression::parse($globals);
		$index = $a->getIndex();
		
		if( $r->isUnknown() ){
			// ignore
			
		} else if( $r->isInt() || $r->isString() ){
			if( ! $r->assignableTo($index) )
				$globals->logger->error($scanner->here(),
				"invalid array key of type ". $r->getType() . ", expected $index");
			
		} else {
			$globals->logger->error($scanner->here(),
			"invalid array key of type ". $r->getType() . ", expected $index");
		}
		
		$globals->expect(Symbol::$sym_rsquare, "expected `]'");
		$scanner->readSym();
		return $a->getElem();
	}
	
	
	/**
	 * Static access to class property.
	 * We enter with the name of the property sym_variable.
	 * @param Globals $globals
	 * @param ClassType $c Dereferenced class.
	 * @return Type Type of the property.
	 */
	private static function dereferenceClass($globals, $c)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		$globals->expect(Symbol::$sym_variable, "expected property \$PROP");
		$name = $scanner->s;
		$p = $c->searchProperty($name);
		if( $p === NULL ){
			$globals->logger->error($scanner->here(),
			"unknown property $c::\$$name");
			$t = Globals::$unknown_type;
		} else {
			$t = $p->value->getType();
			$globals->accountProperty($p);
		}
		$scanner->readSym();
		return $t;
	}
	
	
	/**
	 * Parses object dereferencing $o-&gt;.
	 * @param Globals $globals
	 * @param Type $type Type of the dereferenced value.
	 * @return Type
	 */
	private static function dereferenceObject($globals, $type)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		if( ! ($type instanceof ClassType) ){
			if( $type !== Globals::$unknown_type )
				$globals->logger->error($scanner->here(),
				"`->' operator applied to non-object type $type");
			if( SkipUnknown::canSkip($scanner->sym) )
				return SkipUnknown::anything($globals);
			return Globals::$unknown_type;
		}
		
		$scanner->readSym(); // skip `->'
		$c = cast(ClassType::NAME, $type);

		$globals->expect(Symbol::$sym_identifier,
		"expected method name or property name");
		$name = $scanner->s;
		$scanner->readSym();
		
		$p = $c->searchProperty($name);
		if( $p === NULL ){
			$globals->logger->error($scanner->here(),
			"unknown property $c::\$$name");
			$t = Globals::$unknown_type;
		} else {
			if( $p->is_static )
				$globals->logger->error($scanner->here(),
				"non-static access to static property $p");
			$t = $p->value->getType();
			$globals->accountProperty($p);
		}
		return $t;
	}
	

	/**
	 * Parses a LHS. We enter in this function "blindly", so these is not an
	 * expected symbol. The simplest thing we may found is a variable or the
	 * element of an array.
	 * @param Globals $globals
	 * @param Type $type Expected type of the variable. If it gets assigned (see
	 * argument below) and the variable does not exist, a new variable is
	 * created with this type.
	 * @param boolean $gets_assigned If the variable gets assigned because the
	 * argument of the formal argument of the function or method is declared
	 * <code>/&#42;. return TYPE .&#42;/ &amp; $x</code> so an assignment is
	 * garanteed returning from the function or method.
	 * @return void
	 */
	public static function parse($globals, $type, $gets_assigned)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;
		if( $scanner->sym === Symbol::$sym_variable ){
			$name = $scanner->s;
			$v = $globals->searchVar($name);
			$where = $scanner->here();
			if( $v === NULL ){
				if( $gets_assigned ){
					$v = new Variable($name, FALSE, $where, $pkg->scope);
					$v->type = $type;
					$v->assigned = TRUE;
					$v->assigned_once = TRUE;
					$globals->addVar($v);
					$scanner->readSym();
					return;
				} else {
					$logger->error($where, "variable \$$name does not exist");
					SkipUnknown::anything($globals);
					return;
				}
				
			} else {
				$scanner->readSym();
				$found = $v->type;
				if( $scanner->sym === Symbol::$sym_arrow
				|| $scanner->sym === Symbol::$sym_lsquare ){
					$globals->accountVarRHS($v);
					if( ! $v->assigned )
						$logger->error($where, "variable $v might not have been assigned");
					$found = $v->type;
					
				} else {
					$globals->accountVarLHS($v);
					if( $gets_assigned )
						$globals->accountVarLHS($v);
					else if( ! $v->assigned )
						$logger->error($where, "variable $v might not have been assigned");
					$found = $v->type;
				}
				
			}
		
		} else if( $scanner->sym === Symbol::$sym_self ){
			$c = $pkg->curr_class;
			if( $c === NULL )
				throw new ParseException($scanner->here(), "`self' not inside a class");
			$scanner->readSym();
			$globals->expect(Symbol::$sym_double_colon, "expected `self::'");
			$scanner->readSym();
			$found = self::dereferenceClass($globals, $c);
		
		} else if( $scanner->sym === Symbol::$sym_parent ){
			$c = $pkg->curr_class;
			if( $c === NULL )
				throw new ParseException($scanner->here(), "`parent' not inside a class");
			if( $c->extended === NULL )
				throw new ParseException($scanner->here(), "class $c has no parent");
			$c = $c->extended;
			$scanner->readSym();
			$globals->expect(Symbol::$sym_double_colon, "expected `parent::'");
			$scanner->readSym();
			$found = self::dereferenceClass($globals, $c);
			
		} else {
			$logger->error($scanner->here(), "unexpected symbol "
			. $scanner->sym);
			if( SkipUnknown::canSkip($scanner->sym) )
				SkipUnknown::anything($globals);
			$found = Globals::$unknown_type;
		}
		
		do {
			if( $scanner->sym === Symbol::$sym_lsquare ){
				$found = self::dereferenceArray($globals, $found);
			} else if( $scanner->sym === Symbol::$sym_arrow ){
				$found = self::dereferenceObject($globals, $found);
			} else {
				break;
			}
		} while(TRUE);
				
		if( $found !== Globals::$unknown_type
		&& $type !== Globals::$unknown_type
		&& ! $type->equals($found) )
			$logger->error($scanner->here(), "expected $type but found $found");
	}

}
