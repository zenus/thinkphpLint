<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Constant;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\regex\Pattern;

/**
 * Parses the `define(CONST, VALUE)' statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/23 18:04:45 $
 */
class DefineStatement {
	
	/**
	 * Cached constant ID pattern parser.
	 * @var Pattern 
	 */
	private static $ID_PATTERN = NULL;
	 
	/**
	 * @param Globals $globals
	 * @param boolean $is_private
	 * @return void
	 */
	public static function parse($globals, $is_private)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		
		if( $pkg->scope > 0 )
			$logger->error($scanner->here(),
			"constants must be defined at global scope (PHPLint restriction)");
		
		$dbw = $pkg->curr_docblock;
		$dbw->checkLineTagsForConstant();
		if( $is_private && $dbw->isPrivate() )
			$logger->error($scanner->here(), "`private' attribute both in DocBlock and PHPLint meta-code");
		$is_private = $is_private || $dbw->isPrivate();

		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "missing '(' after 'define'");

		/* Parse $name of the constant, NULL if not valid: */
		$scanner->readSym();
		$name = Expression::parse($globals);
		$here = $scanner->here();
		$name->checkExpectedType($logger, $here, Globals::$string_type);
		if( $name->isString() ){
			if( $name->getValue() === NULL ){
				$logger->error($here, "unable to parse the name of the constant as a value statically determined, will ignore");
				$name = NULL;
			} else {
				if( self::$ID_PATTERN === NULL )
					self::$ID_PATTERN = new Pattern("{a-zA-Z_\x80-\xFF}{a-zA-Z_0-9\x80-\xFF}*\$");
				if( ! self::$ID_PATTERN->match($name->getValue()) ){
					$logger->error($here, "invalid characters in constant name `". $name->getValue() ."'");
					$name = NULL;
				// Useless check - we will get error when const will be used,
				// along with non-ASCII chars
				// FIXME: we should check anyway: it might be unused but exported by a library
//				} else if( SearchPhpKeyword($zzz) !== Symbol::$sym_unknown ){
//					$logger->error($here, "constant name `" . $zzz . "' is a keyword");
				}
			}
		}

		$globals->expect(Symbol::$sym_comma, "expexted `,' in define()");
		$scanner->readSym();

		// Parse $value of the constant, NULL if not valid:
		$value = Expression::parse($globals);
		if( $value->isUnknown() ){
			#$logger->warning($scanner->here(), "cannot parse the value of the constant as a statically determinable value")
			$value = NULL;
		} else {
			if( $value->isNull() || $value->isBoolean() || $value->isInt()
			|| $value->isFloat() || $value->isString() ){
				if( $value->getValue() === NULL ){
					$logger->error($scanner->here(),
					"the expression giving the value of the constant is not statically deteminable (PHPLint safety restriction). Hint: variables and function cannot appear in the expression. If you really need that non-constant value, define a variable instead.");
					$value = NULL;
				}
			} else {
				$logger->error($scanner->here(), "invalid constant value of type "
				. $value->getType()
				. ". It must be boolean, int, float or string");
				$value = NULL;
			}
		}

		// Registers the constant only if valid:
		if( $name !== NULL && $value !== NULL ){
			$abs = $pkg->resolver->absolute($name->getValue());
			$fqn = new FullyQualifiedName($abs, TRUE);
			$c = $globals->getConstant($fqn);
			if( $c === NULL ){
				$c = new Constant($fqn);
				$c->is_private = $is_private;
				$c->decl_in = $here;
				$c->value = $value;
				$c->docblock = $dbw->getDocBlock();
				$globals->addConstant($c);
			} else {
				$logger->error($here, "constant $fqn already declared in "
				. $logger->reference($here, $c->decl_in));
			}
		}

		if( $scanner->sym === Symbol::$sym_comma ){
			$scanner->readSym();
			$logger->error($scanner->here(), "will ignore third argument of define(): constants are always case-sensitive under PHPLint");
			$r = Expression::parse($globals);
		}

		$globals->expect(Symbol::$sym_rround, "expected closing ')' in 'define'");
		$scanner->readSym();
		
		$dbw->clear();

	}
	
}

