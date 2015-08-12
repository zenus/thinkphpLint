<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\IntType;
use it\icosaedro\lint\types\StringType;
use it\icosaedro\lint\types\ArrayType;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;

/**
 * Parses the array constructor <code>array(E1, E2, E3)</code> (long syntax)
 * or <code>[E1, E2, E3]</code> (short syntax).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:12:16 $
 */
class ArrayConstructor {
	
	/**
	 * Parses literal array "array(...)". We enter with symbol "(" (long
	 * syntax) or "[" (short syntax).
	 * @param Globals $globals
	 * @param boolean $is_static True is the expression appears in a static
	 * context, then only statically determined values are allowed.
	 * @param boolean $is_short_syntax Uses short symtax <code>[...]</code>.
	 * @return Result Resulting type and value. If the parsing succeeds,
	 * the ArrayType is returned and the value <code>"array()"</code> is
	 * set if the array is empty, otherwise the value <code>"array(...)"</code>
	 * is set if the array contains entries. If the resulting array is
	 * empty, the formal type-cast can then be applied blindly, no need
	 * to use <code>cast(T,V)</code>; moreover, if the array is empty,
	 * the value is assignment-compatible with any variable of type array.
	 */
	public static function parse($globals, $is_static, $is_short_syntax)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		if( $is_short_syntax && $globals->isPHP(4) )
			$logger->error($scanner->here(),
			"array short syntax not allowed (PHP 5)");
		$scanner->readSym();
		if( $is_short_syntax && $scanner->sym === Symbol::$sym_rsquare
		|| ! $is_short_syntax && $scanner->sym === Symbol::$sym_rround ){
			$scanner->readSym();
			# Empty array, can't guess type.
			$a = ArrayType::factory(Globals::$mixed_type, Globals::$mixed_type);
			# Set the value so that caller may then allow formal typecast
			# on empty array:
			return Result::factory($a, "array()");
		}

		// Guess the structure from the first element.
		$k = Globals::$int_type;
		
		if( $is_static )
			$r = StaticExpression::parse($globals);
		else
			$r = Expression::parse($globals);
		if( $scanner->sym === Symbol::$sym_rarrow ){
			if( $r->isUnknown() ){
				#$logger->warning($scanner->here(), "can'$t detect the type of the key");
				$k = Globals::$mixed_type;
			} else if( $r->isInt() ){
				$k = Globals::$int_type;
			} else if( $r->isString() ){
				$k = Globals::$string_type;
			} else {
				$logger->error($scanner->here(),
				"invalid key of type " . $r->getType());
				$k = Globals::$mixed_type;
			}
			$scanner->readSym();
			if( $is_static )
				$r = StaticExpression::parse($globals);
			else
				$r = Expression::parse($globals);
		}
		
		if( $r->isUnknown() ){
			#$logger->error($scanner->here(), "can't detect the type of the array element");
			$e = Globals::$unknown_type;
		} else {
			$e = $r->getType();
		}

		# Parse next elements, comparing the key/elem types with
		# those already scanned:
		while( $scanner->sym === Symbol::$sym_comma ){
			$scanner->readSym();

			if( $scanner->sym === Symbol::$sym_rsquare
			|| $scanner->sym === Symbol::$sym_rround ){
				#$logger->warning($scanner->here(), "missing array element after `,'");
				break;
			}
			
			if( $is_static )
				$r = StaticExpression::parse($globals);
			else
				$r = Expression::parse($globals);
			
			if( $scanner->sym === Symbol::$sym_rarrow ){  #  ..., k => e, ...
				if( $r->isUnknown() ){
					// ignore
				} else if( $r->isInt() ){
					if( $k instanceof IntType ){
						// ok
					} else if( $k instanceof StringType ){
						$logger->error($scanner->here(),
						"mixing keys of different types in array: first key was string");
						$k = Globals::$mixed_type;
					}
				} else if( $r->isString() ){
					if( $k instanceof IntType ){
						$logger->warning($scanner->here(),
						"mixing keys of different types in array: first key was int");
						$k = Globals::$mixed_type;
					}
				} else {
					$logger->error($scanner->here(),
					"invalid array key of type " . $r->getType());
					$k = Globals::$mixed_type;
				}
				$scanner->readSym();
			
				if( $is_static )
					$r = StaticExpression::parse($globals);
				else
					$r = Expression::parse($globals);
			
			} else {
				# No key provided: that means the index type must be
				# either int or mixed:
				if( $k instanceof StringType ){
					$logger->warning($scanner->here(),
					"mixing keys of different types in array: first key was string");
					$k = Globals::$mixed_type;
				}
			}
			
			if( ! $r->assignableTo($e) ){
				$logger->warning($scanner->here(),
				"mixing elements of different types in array: found "
				. $r->getType() . ", expected $e");
				$e = Globals::$mixed_type;
			}
		}
		
		if( $is_short_syntax )
			$globals->expect(Symbol::$sym_rsquare, "expected `]'");
		else
			$globals->expect(Symbol::$sym_rround, "expected `)'");
		$scanner->readSym();
		$a = ArrayType::factory($k, $e);
		return Result::factory($a, "array(...)");
	}
	
}
