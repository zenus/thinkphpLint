<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\ParseException;

/**
 * Parses static expression in constant definition and default value of
 * formal argument.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/15 10:13:58 $
 */
class StaticExpression {

	/**
	 * @param Globals $globals
	 * @param ClassType $c
	 * @return Result
	 */
	private static function parseClassConst($globals, $c)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		
		if( $scanner->sym === Symbol::$sym_class ){
			if( $globals->isPHP(4) )
				$globals->logger->error($scanner->here(),
				"unsupported CLASSNAME::class constant (PHP 5)");
			$scanner->readSym();
			// Consts cannot be dereferenced by '['.
			return Result::factory(Globals::$string_type, $c->__toString());
		
		} else if( $scanner->sym === Symbol::$sym_identifier ){
			$co = $c->searchConstant($scanner->s);
			if( $co === NULL ){
				$globals->logger->error($scanner->here(),
				"unknown constant $c::" . $scanner->s);
				$scanner->readSym();
				return Result::getUnknown();
			} else {
				$globals->accountClassConstant($co);
			}
			$scanner->readSym();
			return $co->value;
		
		} else {
			throw new ParseException($scanner->here(),
			"expected name of class constant");
		}
	}
	 
	
	/**
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		
		if( $scanner->sym === Symbol::$sym_namespace )
			$globals->resolveNamespaceOperator();

		if( $scanner->sym === Symbol::$sym_null ){
			$r = Result::factory(Globals::$null_type, "NULL");
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_false ){
			$r = Result::factory(Globals::$boolean_type, "FALSE");
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_true ){
			$r = Result::factory(Globals::$boolean_type, "TRUE");
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_plus ){
			$scanner->readSym();
			$r = self::parse($globals);
			$r = $r->unaryPlus($globals->logger, $scanner->here());
		
		} else if( $scanner->sym === Symbol::$sym_minus ){
			$scanner->readSym();
			$r = self::parse($globals);
			$r = $r->unaryMinus($globals->logger, $scanner->here());

		} else if( $scanner->sym === Symbol::$sym_lit_int ){
			$r = Result::factory(Globals::$int_type, $scanner->s);
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_lit_float ){
			$r = Result::factory(Globals::$float_type, $scanner->s);
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_single_quoted_string ){
			$r = Result::factory(Globals::$string_type, $scanner->s);
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_double_quoted_string ){
			$r = Result::factory(Globals::$string_type, $scanner->s);
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_here_doc ){
			$r = Result::factory(Globals::$string_type, $scanner->s);
			$scanner->readSym();

		} else if( $scanner->sym === Symbol::$sym_identifier ){
			$id = $scanner->s;
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_double_colon ){
				$c = $globals->searchClass($id);
				if( $c === NULL ){
					$globals->logger->error($scanner->here(),
					"unknown class $id");
					SkipUnknown::anything($globals);
					$r = Result::getUnknown();
				} else {
					$r = self::parseClassConst($globals, $c);
				}

			} else {
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

		} else if( $scanner->sym === Symbol::$sym_self ){
			if( $globals->isPHP(4) )
				$globals->logger->error($scanner->here(),
				"invalid `self::' (PHP 5)");
			if( $pkg->curr_class === NULL )
				throw new ParseException($scanner->here(),
				"`self::': not inside a class");
			$scanner->readSym();
			$globals->expect(Symbol::$sym_double_colon, "expected `::'");
			$r = self::parseClassConst($globals, $pkg->curr_class);

		} else if( $scanner->sym === Symbol::$sym_parent ){
			if( $globals->isPHP(4) )
				$globals->logger->error($scanner->here(),
				"invalid `parent::' (PHP 5)");
			else
				$globals->logger->error($scanner->here(),
				"`parent::' in static expression cannot be resolved at parse time (PHP limitation)");
			if( $pkg->curr_class === NULL ){
				$globals->logger->error($scanner->here(),
				"`parent::': not inside a class");
				$r = Result::getUnknown();
			} else {
				$parent_ = $pkg->curr_class->extended;
				if( $parent_ === NULL )
					$globals->logger->error($scanner->here(),
					"invalid `parent::': class `"
					. $pkg->curr_class->name . "' has not parent");
				$scanner->readSym();
				$globals->expect(Symbol::$sym_double_colon, "expected `::'");
				$r = self::parseClassConst($globals, $parent_);
			}

		} else if( $scanner->sym === Symbol::$sym_array ){
			$scanner->readSym();
			if( $scanner->sym !== Symbol::$sym_lround )
				throw new ParseException($scanner->here(),
				"expected `(' after `array'");
			$r = ArrayConstructor::parse($globals, TRUE, FALSE);

		} else if( $scanner->sym === Symbol::$sym_lsquare ){
			$r = ArrayConstructor::parse($globals, TRUE, TRUE);

		} else if( $scanner->sym === Symbol::$sym_x_lround ){
			/* Formal typecast. */
			$scanner->readSym();
			$t = TypeDecl::parse($globals, FALSE);
			if( $t === NULL ){
				$globals->logger->error($scanner->here(),
				"missing type specifier");
				$t = Globals::$unknown_type;
			}
			$globals->expect(Symbol::$sym_x_rround,
			"expected closing `)' in formal typecast");
			$scanner->readSym();
			$r = self::parse($globals);
			if( $globals->isPHP(5) ){
				if( $r->isNull() || $r->isEmptyArray() ){
					# ok
				} else {
					$globals->logger->error($scanner->here(), "formal typecast allowed only if applied to NULL or empty array array()");
				}
			}
			$r = $r->typeCast($globals->logger, $scanner->here(), $t);

		} else {
			throw new ParseException($scanner->here(), "invalid static expression -- expected string, constant or static array");
		}

		return $r;
	}
	
	
}
