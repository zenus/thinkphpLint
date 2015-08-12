<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\ArrayType;
use it\icosaedro\lint\types\UnknownType;
use it\icosaedro\lint\types\MixedType;
use it\icosaedro\lint\types\StringType;
use it\icosaedro\lint\types\IntType;
use it\icosaedro\lint\types\FloatType;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\CaseInsensitiveString;
use it\icosaedro\lint\ParseException;

/**
 * Resolves a chain of array and object dereferencing operators, assignment
 * operators and more.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/16 08:22:09 $
 */
class Dereference {
	
	/*. forward public static Type function parse(Globals $globals, Type $type, boolean $is_lhs); .*/
	
	
	/**
	 * Applying the "[" operator to type $type.
	 * @param Globals $globals
	 * @param Type $type
	 * @return Type
	 */
	private static function dereferenceArray($globals, $type)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		if( $type instanceof UnknownType )
			return SkipUnknown::anything($globals);
		
		if( ! ($type instanceof ArrayType) ){
			if( $type !== Globals::$unknown_type )
				$globals->logger->error($scanner->here(),
				"`[' operator applied to non-array type $type");
			return SkipUnknown::anything($globals);
		}
		
		$a = cast(ArrayType::NAME, $type);
		
		$scanner->readSym();
		
		if( $scanner->sym === Symbol::$sym_rsquare ){
			// Found ...[] = EXPR
			if( $a->getIndex() === Globals::$string_type )
				$globals->logger->error($scanner->here(),
				"array index is not int: " . $a->getIndex());
			$scanner->readSym();
			$globals->expect(Symbol::$sym_assign,
			"expected array element assignment `[] = EXPR'");
			return self::parse($globals, $a->getElem(), TRUE);
		}
		
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
		return self::parse($globals, $a->getElem(), TRUE);
	}
	
	
	/**
	 * Applying the "[" operator to string.
	 * @param Globals $globals
	 * @return Type
	 */
	private static function dereferenceString($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$r = Expression::parse($globals);
		
		if( $r->isUnknown() ){
			// ignore
			
		} else if( $r->isInt() ){
			// ok.
			
		} else {
			$globals->logger->error($scanner->here(),
			"invalid array key of type ". $r->getType() . ", expected int");
		}
		
		$globals->expect(Symbol::$sym_rsquare, "expected `]'");
		$scanner->readSym();
		return Globals::$string_type;
	}
	
	
	/**
	 * Parses non-static access to property and possible following dereferencing
	 * operators and assignment.
	 * @param Globals $globals
	 * @param ClassType $c Dereferenced class.
	 * @param string $name Name of the property.
	 * @return Type Final result of this sub-expression.
	 */
	private static function parsePropertyAccess($globals, $c, $name)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$p = $c->searchProperty($name);
		if( $p === NULL ){
			$globals->logger->error($scanner->here(),
			"unknown property $c::\$$name");
			$t = Globals::$unknown_type;
		} else {
			// $obj->static_property is allowed.
//			if( $p->is_static )
//				$globals->logger->error($scanner->here(),
//				"non-static access to static property $p");
			$t = $p->value->getType();
			$globals->accountProperty($p);
		}
		return self::parse($globals, $t, TRUE);
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
			return SkipUnknown::anything($globals);
		}
		
		$scanner->readSym(); // skip `->'
		$c = cast(ClassType::NAME, $type);

		$globals->expect(Symbol::$sym_identifier,
		"expected method name or property name");
		$name = $scanner->s;
		$scanner->readSym();
		if( $scanner->sym === Symbol::$sym_lround ){
			$type = Call::parseMethodCall($globals, $c,
				new CaseInsensitiveString($name), FALSE);
			return self::parse($globals, $type, FALSE);
		} else {
			return self::parsePropertyAccess($globals, $c, $name);
		}
	}
	
	
	/**
	 *
	 * @param Globals $globals
	 * @param Type $lhs
	 * @param string $op
	 * @return Type 
	 */
	private static function evaluateAssign($globals, $lhs, $op)
	{
		$scanner = $globals->curr_pkg->scanner;
		$scanner->readSym();
		
		$rhs = Expression::parse($globals);
		if( $lhs instanceof UnknownType || $rhs->isUnknown() )
			return Globals::$unknown_type;
		
		if( $op === "=" ){
			if( $rhs->assignableTo($lhs) )
				return $rhs->getType();
			
		} else if( $op === "+=" || $op === "-=" || $op === "*=" ){
			if( $rhs->isInt() && ($lhs instanceof IntType || $lhs instanceof FloatType)
			|| $rhs->isFloat() && $lhs instanceof FloatType ){
				return $rhs->getType();
			}
		} else if( $op === "/=" ){
			if( $lhs instanceof FloatType && $rhs->isInt())
				return Globals::$float_type;
		
		} else if( $op === ".=" ){
			if( $lhs instanceof StringType ){
				// Call this method only to trigger type RHS type checking:
				/* ignore = */ $rhs->convertToString($globals->logger, $scanner->here());
				return Globals::$string_type;
			}
			
		} else if( strpos("%= &= |= ^= <<= >>=", $op) !== FALSE ){
			if( $lhs instanceof IntType && $rhs->isInt() )
				return Globals::$int_type;
			
		} else {
			throw new \RuntimeException($op);
		}
		$globals->logger->error($scanner->here(),
		"($lhs) $op (" . $rhs->getType() . "): incompatible types");
		return Globals::$unknown_type;
	}
	
	
	/**
	 *
	 * @param Globals $globals
	 * @param Type $type 
	 * @return Type
	 */
	private static function evaluateInstanceOf($globals, $type)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if ($globals->isPHP(4)) {
			$globals->logger->error($scanner->here(),
			"invalid operator `instanceof' (PHP 5). Hint: use `is_a()' instead.");
		}
		if( ! ( $type instanceof UnknownType || $type instanceof ClassType
		|| $type instanceof MixedType) ){
			$globals->logger->error($scanner->here(),
			"the left side of `instanceof' is of type $type, expected object or mixed");
		}
		$scanner->readSym();
		if ($scanner->sym === Symbol::$sym_namespace) {
			$globals->resolveNamespaceOperator();
		}
		if ($scanner->sym === Symbol::$sym_identifier) {
			$c = $globals->searchClass($scanner->s);
			if ($c === NULL) {
				$globals->logger->warning($scanner->here(),
				"class " . $scanner->s . " (still) undefined");
			} else {
				$globals->accountClass($c);
			}
		} else if ($scanner->sym === Symbol::$sym_self) {
			if ($pkg->curr_class === NULL) {
				$globals->logger->error($scanner->here(),
				"`self' undefined outside class body");
			}
		} else if ($scanner->sym === Symbol::$sym_parent) {
			if ($pkg->curr_class === NULL) {
				$globals->logger->error($scanner->here(),
				"`parent' undefined outside class body");
			} else if( $pkg->curr_class->extended === NULL ){
				$globals->logger->error($scanner->here(),
				"current class has no parent");
			}
		} else if ($scanner->sym === Symbol::$sym_variable) {
			// FIXME: might be something more complicated than a bare variable?
			$v = $globals->searchVar($scanner->s);
			if ($v === NULL) {
				throw new ParseException($scanner->here(),
				"unknown variable \$" . $scanner->s . " after `instanceof'");
				
			} else if( ! $v->assigned ){
				$globals->logger->error($scanner->here(),
				"variable $v might not have been assigned");
				
			} else if ($v->type !== Globals::$string_type
			&& !($v->type instanceof ClassType)) {
				throw new ParseException($scanner->here(),
				"variable after `instanceof' must be string or object, "
				. $v->type . " found for $v");
			}
			if( $v !== NULL )
				$globals->accountVarRHS($v);
		} else {
			throw new ParseException($scanner->here(),
			"expected class name or variable, found symbol " . $scanner->sym);
		}
		$scanner->readSym();
		return Globals::$boolean_type;
	}
	
	
	/**
	 * Resolves dereferencing operators to a value of a given type, including
	 * array, object or even simple type. Also consumes possible assignment.
	 * 
	 * Can parse a chain of these operators:
	 * <blockquote><pre>
	 * [index]
	 * -&gt;property
	 * -&gt;method()
	 * </pre></blockquote>
	 * 
	 * The parsing also consumes any final assignment or increment operator,
	 * if present:
	 * <blockquote><pre>
	 * = EXPR
	 * += EXPR
	 * (all the others assignment operators)
	 * ++
	 * --
	 * instanceof EXPR
	 * </pre></blockquote>
	 * 
	 * We enter with the first dereferencing or assignment operator to resolve.
	 * If no dereferencing or assignment operator is found, does nothing and
	 * returns the current same type passed by argument.
	 * 
	 * @param Globals $globals
	 * @param Type $type
	 * @param boolean $is_lhs If assignment can be made on this value.
	 * @return Type Final type of the result.
	 */
	public static function parse($globals, $type, $is_lhs)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		$sym = $scanner->sym;
		if( $sym === Symbol::$sym_lsquare ){
			if( $type instanceof StringType ){
				// Index applied to string. Single bytes can be assigned in PHP,
				// but this is forbidden in PHPLint. Stop here an return:
				return self::dereferenceString($globals);
			} else {
				return self::dereferenceArray($globals, $type);
			}

		} else if( $sym === Symbol::$sym_arrow ){
			return self::dereferenceObject($globals, $type);

		} else if( $sym === Symbol::$sym_instanceof ){
			return self::evaluateInstanceOf($globals, $type);

		} else if( $is_lhs ){

			if( $sym === Symbol::$sym_incr
			|| $scanner->sym === Symbol::$sym_decr ){
				if( $type instanceof IntType ){
					$scanner->readSym();
					return $type;
				} else if( $type instanceof UnknownType ){
					$scanner->readSym();
					return $type;
				} else {
					$globals->logger->error($scanner->here(),
					"cannot apply increment/decrement operator to $type");
					$scanner->readSym();
					return Globals::$unknown_type;
				}
			} else if( $sym === Symbol::$sym_assign ){
				return self::evaluateAssign($globals, $type, "=");
			} else if( $sym === Symbol::$sym_plus_assign ){
				return self::evaluateAssign($globals, $type, "+=");
			} else if( $sym === Symbol::$sym_minus_assign ){
				return self::evaluateAssign($globals, $type, "-=");
			} else if( $sym === Symbol::$sym_times_assign ){
				return self::evaluateAssign($globals, $type, "*=");
			} else if( $sym === Symbol::$sym_div_assign ){
				return self::evaluateAssign($globals, $type, "/=");
			} else if( $sym === Symbol::$sym_mod_assign ){
				return self::evaluateAssign($globals, $type, "%=");
			} else if( $sym === Symbol::$sym_period_assign ){
				return self::evaluateAssign($globals, $type, ".=");
			} else if( $sym === Symbol::$sym_bit_and_assign ){
				return self::evaluateAssign($globals, $type, "&=");
			} else if( $sym === Symbol::$sym_bit_or_assign ){
				return self::evaluateAssign($globals, $type, "|=");
			} else if( $sym === Symbol::$sym_bit_xor_assign ){
				return self::evaluateAssign($globals, $type, "^=");
			} else if( $sym === Symbol::$sym_lshift_assign ){
				return self::evaluateAssign($globals, $type, "<<=");
			} else if( $sym === Symbol::$sym_rshift_assign ){
				return self::evaluateAssign($globals, $type, ">>=");
			} else {
				return $type;
			}

		} else {
			return $type;
		}
	}

}
