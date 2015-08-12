<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\ArrayType;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\statements\ExitStatement;
use RuntimeException;
use it\icosaedro\lint\ParseException;

/**
 * Parser for non-static expressions. Expressions ranges from a simple literal
 * value (number, string, etc.) up to complex variable assignment, implicit
 * array creation and more. Expressions are the core of the PHP language.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/23 16:03:39 $
 */
class Expression {

	/*. forward static Result function parse(Globals $globals); .*/

	/**
	 * Parses a double quoted literal string or here-doc, possibly with
	 * embedded variables.
	 * @param Globals $globals
	 * @return Result Resulting string. If there are no variables inside,
	 * the result also contains the value of the string.
	 */
	private static function parseDoubleQuotedStringWithEmbeddedVars($globals)
	{
		$scanner = $globals->curr_pkg->scanner;
		$value = $scanner->s;
		$scanner->readSym();
		while( $scanner->sym === Symbol::$sym_embedded_variable ){
			# Embedded vars found, cannot determine the resulting value:
			$value = NULL;
			$v = $globals->searchVar($scanner->s);
			if( $v === NULL ){
				$globals->logger->error($scanner->here(), "undefined variable \$" . $scanner->s);
			} else {
				// Check implicit conversion of $v to string:
				$r = Result::factory($v->type);
				$r->convertToString($globals->logger, $scanner->here());
				$globals->accountVarRHS($v);
			}
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_continuing_double_quoted_string ){
				$scanner->readSym();
			}
		}
		return Result::factory(Globals::$string_type, $value);
	}
	
	
	/**
	 * Parses a term: literal value, variable (and following dereferencing and
	 * assignment operators), function call, special operators (new, clone),
	 * value cast, formal cast, and much more.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function parseTerm($globals){
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if( $scanner->sym === Symbol::$sym_namespace )
			$globals->resolveNamespaceOperator();

		//echo "EXPR FIRST SYM = ", $scanner->sym, "\n";
		switch( $scanner->sym->__toString() ){

		case "sym_variable":
			$v = $globals->searchVar($scanner->s);
			if( $v === NULL )
				$r = UnknownVar::parse($globals, NULL, FALSE, NULL);
			else if( $v->assigned )
				$r = AssignedVar::parse($globals, $v);
			else
				$r = UnassignedVar::parse($globals, $v);
			break;

		case "sym_identifier":
			$id = $scanner->s;
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_lround ){ # function call
				$t = Call::parseFuncCall($globals, $id);
				if( $scanner->sym === Symbol::$sym_arrow
				|| $scanner->sym === Symbol::$sym_lsquare ){
					// FIXME: f()-> not allowed in PHP 4
					// FIXME: f()[] allowed only since PHP 5.x (check x)
					$t = Dereference::parse($globals, $t, FALSE);
				}
				$r = Result::factory($t);

			} else if( $scanner->sym === Symbol::$sym_double_colon ){ # static access to class
				$c = $globals->searchClass($id);
				if( $c === NULL ){
					$globals->logger->error($scanner->here(),
					"unknown class $id");
					SkipUnknown::anything($globals);
					$r = Result::getUnknown();
				} else {
					$r = ClassStaticAccess::parse($globals, $c);
				}

			} else { # constant
				$co = $globals->searchConstant($id);
				if( $co === NULL ){
					$globals->logger->error($scanner->here(),
					"unknown constant $id");
					$r = Result::getUnknown();
				} else {
					$globals->accountConstant($co);
					if( $co->is_magic )
						$r = MagicConstants::resolve($globals, $co);
					else
						$r = $co->value;
				}
			}
			break;

		case "sym_single_quoted_string":
			$r = Result::factory(Globals::$string_type, $scanner->s);
			$scanner->readSym();
			break;

		case "sym_double_quoted_string":
			$r = self::parseDoubleQuotedStringWithEmbeddedVars($globals);
			break;

		case "sym_null":
			$r = Result::factory(Globals::$null_type, "NULL");
			$scanner->readSym();
			break;

		case "sym_false":
			$r = Result::factory(Globals::$boolean_type, "FALSE");
			$scanner->readSym();
			break;

		case "sym_true":
			$r = Result::factory(Globals::$boolean_type, "TRUE");
			$scanner->readSym();
			break;

		case "sym_lit_int":
			$r = Result::factory(Globals::$int_type, $scanner->s);
			$scanner->readSym();
			break;

		case "sym_lit_float":
			$r = Result::factory(Globals::$float_type, $scanner->s);
			$scanner->readSym();
			break;

		case "sym_inf":
			$r = Result::factory(Globals::$float_type, "INF");
			$scanner->readSym();
			break;

		case "sym_nan":
			$r = Result::factory(Globals::$float_type, "NAN");
			$scanner->readSym();
			break;

		case "sym_here_doc":
			$r = self::parseDoubleQuotedStringWithEmbeddedVars($globals);
			break;

		case "sym_bit_and":
			$scanner->readSym();
			$r = self::parseTerm($globals);
			break;

		case "sym_exit":
			$globals->logger->error($scanner->here(), "`exit()' (aka `die()') isn't a function, it is a statement. Trying to continue anyway, but probably the result of the expression will be of the wrong type.");
			ExitStatement::parse($globals);
			$r = Result::getUnknown(); // FIXME: or exit() returns void?
			break;

		case "sym_self":    $r = SelfOperator::parse($globals);  break;

		case "sym_static":  $r = StaticOperator::parse($globals);  break;

		case "sym_parent":  $r = ParentOperator::parse($globals);  break;

		case "sym_new":     $r = NewOperator::parse($globals);  break;

		case "sym_clone":   $r = CloneOperator::parse($globals);  break;

		case "sym_isset":   $r = IssetFunction::parse($globals);  break;

		case "sym_list":    $r = ListFunction::parse($globals);  break;

		case "sym_array":
			$scanner->readSym();
			$globals->expect(Symbol::$sym_lround, "expected `(' after `array'");
			$r = ArrayConstructor::parse($globals, FALSE, FALSE);
			break;

		case "sym_lsquare":
			$r = ArrayConstructor::parse($globals, FALSE, TRUE);
			break;

		case "sym_lround":
			# Sub-expression "(...)" or value cast operator "(T)".
			$scanner->readSym();
			switch( $scanner->sym->__toString() ){

			case "sym_boolean":
				$scanner->readSym();
				$globals->expect(Symbol::$sym_rround, "expected closing `)' in typecast");
				$scanner->readSym();
				$r = self::parseTerm($globals);
				$r = $r->valueCast($globals->logger, $scanner->here(), Globals::$boolean_type);
				break;
			
			case "sym_int":
				$scanner->readSym();
				$globals->expect(Symbol::$sym_rround, "expected closing `)' in typecast");
				$scanner->readSym();
				$r = self::parseTerm($globals);
				$r = $r->valueCast($globals->logger, $scanner->here(), Globals::$int_type);
				break;
			
			case "sym_float":
				$scanner->readSym();
				$globals->expect(Symbol::$sym_rround, "expected closing `)' in typecast");
				$scanner->readSym();
				$r = self::parseTerm($globals);
				$r = $r->valueCast($globals->logger, $scanner->here(), Globals::$float_type);
				break;
			
			case "sym_string":
				$scanner->readSym();
				$globals->expect(Symbol::$sym_rround, "expected closing `)' in typecast");
				$scanner->readSym();
				$r = self::parseTerm($globals);
				$r = $r->valueCast($globals->logger, $scanner->here(), Globals::$string_type);
				break;
			
			case "sym_array":
				# FIXME: it might be an expression
				# some guys used to write "return (array(...)); which confuses the parser
				$scanner->readSym();
				$globals->expect(Symbol::$sym_rround, "expected closing `)' in typecast");
				$globals->logger->error($scanner->here(), "forbidden `(array)' typecast");
				$scanner->readSym();
				$r = self::parseTerm($globals);
				$t = ArrayType::factory(Globals::$mixed_type, Globals::$mixed_type);
				$r = Result::factory($t);
				break;
			
			case "sym_object":
				$scanner->readSym();
				$globals->expect(Symbol::$sym_rround, "expected `)' after typecast");
				$globals->logger->error($scanner->here(), "forbidden `(object)' typecast");
				$scanner->readSym();
				$r = self::parseTerm($globals);
				$r = Result::factory(ClassType::getObject());
				break;

			default: # Sub-expression:
				$r = self::parse($globals);
				$globals->expect(Symbol::$sym_rround, "missing `)'");
				$scanner->readSym();
				if( $scanner->sym === Symbol::$sym_arrow ){
					$t = Dereference::parse($globals, $r->getType(), FALSE);
					$r = Result::factory($t);
				}
			}
			break;

		case "sym_x_lround":
			/* Formal typecast. */
			$forced_typecast = FALSE;
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_x_identifier
			&& $scanner->s === "__phplint_forced_typecast__" ){
				$forced_typecast = TRUE;
				$scanner->readSym();
			}
			$t = TypeDecl::parse($globals, FALSE);
			if( $t === NULL ){
				if( ! $forced_typecast )
					$globals->logger->error($scanner->here(),
					"missing type specifier");
				$t = Globals::$unknown_type;
			}
			$globals->expect(Symbol::$sym_x_rround,
			"expected closing `)' in formal typecast");
			$scanner->readSym();
			$r = self::parseTerm($globals);
			/*
			 * Under PHP 4, formal typecast is always permitted, but no runtime
			 * check is performed.
			 * 
			 * Under PHP 5, only formal typecast on empty array() and NULL is
			 * allowed in non-static expressions because this can be made
			 * safely at validation time and is harmless at runtime.
			 * An exception is made when the special keyword
			 * "__phplint_forced_typecast__" precedes the
			 * actual type, required to implements the magic methods of the
			 * it\icosaedro\phplint\TypeXxx classes.
			 */
			if( $globals->isPHP(5) && ! $forced_typecast ){
				if( $r->isNull() || $r->isEmptyArray() ){
					# ok
				} else {
					$globals->logger->error($scanner->here(), "formal typecast allowed only if applied to NULL or empty array array(). Hint: have a look at the PHPLint magic function cast().");
				}
			}
			$r = $r->typeCast($globals->logger, $scanner->here(), $t);
			break;

		default:
			throw new ParseException($scanner->here(),
			"unexpected symbol " . $scanner->sym);

		}

		return $r;

	}
	

	/**
	 * Parses pre-increment "++" and pre-decrement "--" operators.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e18($globals){
		$scanner = $globals->curr_pkg->scanner;
		if( $scanner->sym === Symbol::$sym_incr
		|| ($scanner->sym === Symbol::$sym_decr) ){
			$scanner->readSym();
			Assignable::parse($globals, Globals::$int_type, FALSE);
			$r = Result::factory(Globals::$int_type, NULL);
		} else {
			$r = self::parseTerm($globals);
		}
		return $r;
	}


	/**
	 * Parses unary operators.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e17($globals){
		$scanner = $globals->curr_pkg->scanner;
		
		if( $scanner->sym === Symbol::$sym_not ){
			$scanner->readSym();
			$r = self::e17($globals)->booleanNot($globals->logger, $scanner->here());
			
		} else if( $scanner->sym === Symbol::$sym_plus ){
			$scanner->readSym();
			$r = self::e17($globals)->unaryPlus($globals->logger, $scanner->here());
			
		} else if( $scanner->sym === Symbol::$sym_minus ){
			$scanner->readSym();
			$r = self::e17($globals)->unaryMinus($globals->logger, $scanner->here());
			
		} else if( $scanner->sym === Symbol::$sym_bit_not ){
			$scanner->readSym();
			$r = self::e17($globals)->bitNot($globals->logger, $scanner->here());
			
		} else if( $scanner->sym === Symbol::$sym_at ){
			$globals->curr_pkg->enteringSilencer();
			$scanner->readSym();
			$r = self::e17($globals);
			$globals->curr_pkg->exitingSilencer();
			
		} else {
			$r = self::e18($globals);
		}
		return $r;
	}


	/**
	 * Parses multiplicative operators.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e16($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e17($globals);
		while( $scanner->sym === Symbol::$sym_times
		|| $scanner->sym === Symbol::$sym_div
		|| $scanner->sym === Symbol::$sym_mod ){
			$op = $scanner->sym;
			$scanner->readSym();
			$t = self::e17($globals);
			switch( $op->__toString() ){
			case "sym_times": $r = $r->times($globals->logger, $scanner->here(), $t);  break;
			case "sym_div":   $r = $r->divide($globals->logger, $scanner->here(), $t);  break;
			case "sym_mod":   $r = $r->modulus($globals->logger, $scanner->here(), $t);  break;
			default: throw new RuntimeException();
			}
		}
		return $r;
	}


	/**
	 * Parses additive operators.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e15($globals)
	{
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e16($globals);
		while( $scanner->sym === Symbol::$sym_plus
		|| $scanner->sym === Symbol::$sym_minus
		|| ($scanner->sym === Symbol::$sym_period) ){
			$op = $scanner->sym;
			$where = $scanner->here();
			$scanner->readSym();
			$q = self::e16($globals);
			switch( $op->__toString() ){
			case "sym_plus":   $r = $r->plus($globals->logger, $where, $q);  break;
			case "sym_minus":  $r = $r->minus($globals->logger, $where, $q);  break;
			case "sym_period": $r = $r->dot($globals->logger, $where, $q);  break;
			default: throw new RuntimeException("$op");
			}
		}
		return $r;
	}


	/**
	 * Parses bitwise shift operators.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e14($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e15($globals);
		while( $scanner->sym === Symbol::$sym_lshift
		|| $scanner->sym === Symbol::$sym_rshift ){
			$op = $scanner->sym;
			$scanner->readSym();
			$t = self::e15($globals);
			if( $op === Symbol::$sym_lshift ){
				$r = $r->leftShift($globals->logger, $scanner->here(), $t);
			} else {
				$r = $r->rightShift($globals->logger, $scanner->here(), $t);
			}
		}
		return $r;
	}


	/**
	 * Parses weak sorting operators.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e13($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e14($globals);
		switch( $scanner->sym->__toString() ){
		case "sym_lt":  $n = "<";  break;
		case "sym_le":  $n = "<=";  break;
		case "sym_gt":  $n = ">";  break;
		case "sym_ge":  $n = ">=";  break;
		default: return $r;
		}
		$scanner->readSym();
		return $r->weakCompare($globals->logger, $scanner->here(), self::e14($globals), $n);
	}


	/**
	 * Parses weak and strong equality operators.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e12($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e13($globals);
		switch( $scanner->sym->__toString() ){

		case "sym_eq":
			$scanner->readSym();
			return $r->weakCompare($globals->logger, $scanner->here(), self::e13($globals), "==");

		case "sym_ne":
			$scanner->readSym();
			return $r->weakCompare($globals->logger, $scanner->here(), self::e13($globals), "!=");

		case "sym_eeq":
			$scanner->readSym();
			return $r->strongCompare($globals->logger, $scanner->here(), self::e13($globals), "===");
			
		case "sym_nee":
			$scanner->readSym();
			return $r->strongCompare($globals->logger, $scanner->here(), self::e13($globals), "!==");

		default:
			return $r;
		}
	}


	/**
	 * Parses bitwise "&amp;".
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e11($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e12($globals);
		while( $scanner->sym === Symbol::$sym_bit_and ){
			$scanner->readSym();
			$r = $r->bitAnd($globals->logger, $scanner->here(), self::e12($globals));
		}
		return $r;
	}


	/**
	 * Parses bitwise "^".
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e10($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e11($globals);
		while( $scanner->sym === Symbol::$sym_bit_xor ){
			$scanner->readSym();
			$r = $r->bitXor($globals->logger, $scanner->here(), self::e11($globals));
		}
		return $r;
	}


	/**
	 * Parses bitwise "|".
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e9($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e10($globals);
		while( $scanner->sym === Symbol::$sym_bit_or ){
			$scanner->readSym();
			$r = $r->bitOr($globals->logger, $scanner->here(), self::e10($globals));
		}
		return $r;
	}


	/**
	 * Parses logic "&amp;&amp;".
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e8($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e9($globals);
		while( $scanner->sym === Symbol::$sym_and ){
			$scanner->readSym();
			$r = $r->booleanAnd($globals->logger, $scanner->here(), self::e9($globals), "&&");
		}
		return $r;
	}


	/**
	 * Parses logic "||".
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e7($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e8($globals);
		while( $scanner->sym === Symbol::$sym_or ){
			$scanner->readSym();
			$r = $r->booleanOr($globals->logger, $scanner->here(), self::e8($globals), "||");
		}
		return $r;
	}


	/**
	 * Parses print() function and ternary operator E?B:C.
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e6($globals){
		$scanner = $globals->curr_pkg->scanner;
		if( $scanner->sym === Symbol::$sym_print ){
			// "print" function.
			$scanner->readSym();
			$r = self::e6($globals);
			$r = $r->convertToString($globals->logger, $scanner->here());
			return Result::factory(Globals::$int_type, "1");
		}

		$r = self::e7($globals);
		while( $scanner->sym === Symbol::$sym_question ){
			$r->checkExpectedType($globals->logger, $scanner->here(), Globals::$boolean_type);
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_colon ){
				throw new ParseException($scanner->here(), "unsupported short ternary operator ?:");
			}
			$a = self::parse($globals);
			$globals->expect(Symbol::$sym_colon, "expected `:'");
			$scanner->readSym();
			$b = self::parse($globals);
			if( ! $a->getType()->equals($b->getType()) ){
				$globals->logger->error($scanner->here(), "`...? EXPR1 : EXPR2': type mismatch: EXPR1 is "
				. $a->getType() . ", EXPR2 is " . $b->getType());
			}
			$r = Result::factory($a->getType());
		}
		return $r;
	}


//	/**
//	 * @param Globals $globals
//	 * @return Result
//	 */
//	private static function e5($globals){
//		$scanner = $globals->curr_pkg->scanner;
//		$r = self::e6($globals);
//		if( Utils::isAssignOp($scanner->sym) ){
//			$globals->logger->error($scanner->here(), "invalid left hand side in assignment. Hint: you might want to use the comparison operators `==' or `==='.");
//			$scanner->readSym();
//			$r = self::e6($globals); # recursive call because they are right-associative
//		}
//		return $r;
//	}


	/**
	 * Parse logic "and".
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e4($globals){
		$scanner = $globals->curr_pkg->scanner;
//		$r = self::e5($globals);
		$r = self::e6($globals);
		while( $scanner->sym === Symbol::$sym_and2 ){
			$scanner->readSym();
//			$r = $r->booleanAnd($globals->logger, $scanner->here(), self::e5($globals), "and");
			$r = $r->booleanAnd($globals->logger, $scanner->here(), self::e6($globals), "and");
		}
		return $r;
	}


	/**
	 * Parses logic "xor".
	 * @param Globals $globals
	 * @return Result
	 */
	private static function e3($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e4($globals);
		while( $scanner->sym === Symbol::$sym_xor ){
			$scanner->readSym();
			$r = $r->booleanXor($globals->logger, $scanner->here(), self::e4($globals));
		}
		return $r;
	}

	
	/**
	 * Parses an expression. At this higher level its form is "x or y or z...".
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals){
		$scanner = $globals->curr_pkg->scanner;
		$r = self::e3($globals);
		while( $scanner->sym === Symbol::$sym_or2 ){
			$scanner->readSym();
			$r = $r->booleanOr($globals->logger, $scanner->here(), self::e3($globals), "or");
		}
		return $r;
	}
	
}
