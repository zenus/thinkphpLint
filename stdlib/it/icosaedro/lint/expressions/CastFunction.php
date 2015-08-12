<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\UnknownType;
use it\icosaedro\lint\types\IntType;
use it\icosaedro\lint\types\FloatType;
use it\icosaedro\lint\types\TypeDescriptor;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\utils\Strings;

/**
 * Parses the <code>cast(T,E)</code> magic function.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/08/13 18:26:29 $
 */
class CastFunction {
	
	
	/**
	 * Checks if T is an allowed type. Void and null are not allowed.
	 * Array must always indicate the key type (integer, string or both).
	 * @param string $t Value of T.
	 * @return string Empty string, or the reason why this type isn't allowed.
	 */
	private static function checkType($t)
	{
		// void and null are not allowed:
		if( $t === "void"
		|| Strings::endsWith($t, "]void")
		|| Strings::startsWith($t, "void[") )
			return "void not allowed in cast()";
		if( $t === "null"
		|| Strings::endsWith($t, "]null")
		|| Strings::startsWith($t, "null[") )
			return "null not allowed in cast()";
		
		// Absolute class names not allowed:
		if( Strings::startsWith($t, "\\")
		|| strpos($t, "]\\") !== FALSE )
			return "absolute class name not allowed in cast()";
		
		return "";
	}
	
	
	/**
	 * Parses the <code>cast(T,E)</code> magic function.
	 * @param Globals $globals
	 * @return Type
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;
		
		$scanner->readSym();

		/*
		 * Parses T.
		 * T must be a static expr only in the user code. cast() is used also
		 * in its actual runtime implementation, where T is a variable.
		 * Since PHPLint must be able to parse also the implementation itself
		 * of this magic function, we must relax some requirement. In this
		 * specific case, T may not be statically evaluable.
		 */
		$inside_cast = $pkg->curr_func !== NULL
			&& $pkg->curr_func->name->equals(Globals::$CAST_FQN);
		if( $inside_cast ){
			// Inside cast() itself, T can be any expression, possibly
			// including variable parts: anything allowed here:
			/* $ignore = */ Expression::parse($globals);
			$t = Globals::$unknown_type;
			
		} else {
			// Called in user code: T must be a statically determinable string:
			$r = Expression::parse($globals);
			$t = Globals::$unknown_type;
			
			if( $r->isUnknown() ){
				//
			} else if( ! $r->isString() ){
				$logger->error($scanner->here(),
				"invalid type: expected string but found $t");

			} else if( $r->getValue() === NULL ){
				$logger->error($scanner->here(),
				"cannot evaluate type descriptor statically");

			} else {
				$t = TypeDescriptor::parse($logger, $scanner->here(),
					$r->getValue(), FALSE, $globals, TRUE);
				// Note that the $resolve_ns arg above is FALSE because cast()
				// must resolve classes at runtime, when the function cannot
				// know the NS of the caller.
				if( $t === Globals::$void_type
				|| $t === Globals::$mixed_type ){
					$logger->error($scanner->here(), "cannot cast to $t");
					$t = Globals::$unknown_type;
				}
				if( !($t instanceof UnknownType) ){
					$err = self::checkType($r->getValue());
					if( $err !== "" )
						$logger->error($scanner->here(), $err);
				}
			}
		}

		$globals->expect(Symbol::$sym_comma, "expected `,'");
		$scanner->readSym();
		
		// Parses E:
		$e = Expression::parse($globals);

		/*
		 * Basically, T must be assignable to E with the only exception of
		 * T=int that cannot be assigned to E=float:
		 */
		if( ! (
			$inside_cast
			
			|| $t->assignableTo($e->getType())
				&& !( $t instanceof IntType && $e instanceof FloatType)
		) ){
			$logger->error($scanner->here(),
				"cast($t, " . $e->getType() . "): invalid typecast");
		}

		$globals->expect(Symbol::$sym_rround, "expected `)'");
		$scanner->readSym();
		return $t;
	}

}
