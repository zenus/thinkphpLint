<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\CaseInsensitiveString;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\ParseException;

/**
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:36:46 $
 */
class ClassStaticAccess {
	
	/*.
		forward public static Result function parse(
			Globals $globals, ClassType $c);
	.*/
	
	/**
	 *
	 * @param Globals $globals
	 * @param ClassType $c
	 * @param string $name
	 * @return Result
	 */
	private static function parseConstantAccess($globals, $c, $name)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$co = $c->searchConstant($name);
		if( $co == NULL ){
			$globals->logger->error($scanner->here(),
				"constant $c::$name does not exist");
			return Result::getUnknown();
		}
		$globals->accountClassConstant($co);
		return $co->value;
	}
	
	
	/**
	 * Parses access to static property and possible following dereferencing
	 * operators and assignment.
	 * @param Globals $globals
	 * @param ClassType $c Dereferenced class.
	 * @param string $name Name of the property.
	 * @return Type Final result of this sub-expression.
	 */
	private static function parseStaticPropertyAccess($globals, $c, $name)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$p = $c->searchProperty($name);
		if( $p === NULL ){
			$globals->logger->error($scanner->here(),
				"unknown property $c::\$$name");
			$t = Globals::$unknown_type;
		} else {
			/*
			 * For some reason I don't understand, non-static methods are
			 * accessible with self::nonStaticMethod(), but non-static
			 * properties are not accessible this way.
			 */
			if( ! $p->is_static )
				$globals->logger->error($scanner->here(),
					"static access to non-static property $p");
			$t = $p->value->getType();
			$globals->accountProperty($p);
		}
		$scanner->readSym();
		return Dereference::parse($globals, $t, TRUE);
	}
	

	/**
	 * Parse access to a class entity through the `::' operator.
	 * Handles:
	 * <blockquote><pre>
	 * ::const
	 * ::$property possibly followed by [ { -&gt; = .= *= ... .. -- instanceof
	 * ::method() possibly followed by -&gt;
	 * ::class (PHP 5.4+).
	 * </pre></blockquote>
	 * 
	 * @param Globals $globals
	 * @param ClassType $c
	 * @return Result The result of the sub-expression.
	 */
	public static function parse($globals, $c)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();

		if( $scanner->sym === Symbol::$sym_variable ){ # a property
			$t = self::parseStaticPropertyAccess($globals, $c, $scanner->s);
			return Result::factory($t);

		} else if( $scanner->sym === Symbol::$sym_identifier ){
			$name = $scanner->s;
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_lround ){
				$t = Call::parseMethodCall($globals, $c,
					new CaseInsensitiveString($name), TRUE);
				return Result::factory($t);
			} else
				return self::parseConstantAccess($globals, $c, $name);
		
		} else if( $scanner->sym === Symbol::$sym_class ){
			if( $globals->isPHP(4) )
				$globals->logger->error($scanner->here(),
				"unsupported CLASSNAME::class constant (PHP 5)");
			$scanner->readSym();
			// Consts cannot be dereferenced by '['.
			return Result::factory(Globals::$string_type, $c->__toString());

		} else {
			throw new ParseException($scanner->here(),
			"expected class item after `::', found " . $scanner->sym);
		}
	}

}
