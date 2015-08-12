<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\Constant;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\expressions\StaticExpression;

/**
 * Parses the <code>const</code> statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/24 20:03:13 $
 */
class ConstantStatement {
	 
	/**
	 * Parses the <code>const</code> statement (not in class).
	 * @param Globals $globals Context of the parser.
	 * @param boolean $is_private Private modifier in meta-code found.
	 * @return void
	 */
	public static function parse($globals, $is_private)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;
		
		if( $globals->isPHP(4) ){
			$logger->error($scanner->here(),
			"`const' keyword is reserved by PHP 5");
		} else if( $pkg->scope > 0 ){
			$logger->error($scanner->here(),
			"`const' declaration allowed only in classes and in global scope");
		}
		
		$dbw = $pkg->curr_docblock;
		$dbw->checkLineTagsForConstant();
		if( $is_private && $dbw->isPrivate() )
			$logger->error($scanner->here(), "`private' attribute both in DocBlock and PHPLint meta-code");
		$is_private = $is_private || $dbw->isPrivate();

		$scanner->readSym();

		while(TRUE){

			$globals->expect(Symbol::$sym_identifier, "expected name of the constant");
			$name = $scanner->s;
			$here = $scanner->here();
			if( ! NamespaceResolver::isIdentifier($name) )
				throw new ParseException($here, "constant name must be a simple identifier");
			$name = $pkg->resolver->absolute($name);
			$fqn = new FullyQualifiedName($name, TRUE);
			$c = $globals->getConstant($fqn);
			if( $c === NULL ){
				$c = new Constant($fqn);
				$c->is_private = $is_private;
				$c->decl_in = $here;
				//$c->value = $value;
				$c->docblock = $dbw->getDocBlock();
				$globals->addConstant($c);
			} else {
				$logger->error($here, "constant $fqn already declared in "
				. $logger->reference($here, $c->decl_in));
			}
			$scanner->readSym();

			$globals->expect(Symbol::$sym_assign, "expected `='");
			$scanner->readSym();

			$r = StaticExpression::parse($globals);
			if( $r->isUnknown() ){
				#$scanner->here()->warning("can't parse the value of the constant as a statically determinable value")
			} else {
				if( ! ( $r->isNull() || $r->isBoolean() || $r->isInt()
				|| $r->isFloat() || $r->isString() ) ){
					$logger->error($scanner->here(),
					"invalid constant value of type " . $r->getType()
					. ". It must be boolean, int, float or string");
					$r = Result::getUnknown();
				} else if( $r->getValue() === NULL ){
					$logger->error($scanner->here(), "can't parse the value of the constant as a statically determinable value");
				}
			}
			$c->value = $r;

			if( $scanner->sym === Symbol::$sym_comma ){
				$scanner->readSym();

			} else {
				break;

			}

		}

		$dbw->clear();
	}
	
}

