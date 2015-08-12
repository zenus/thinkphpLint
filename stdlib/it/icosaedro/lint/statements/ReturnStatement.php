<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\types\GuessType;
use it\icosaedro\lint\types\VoidType;
use it\icosaedro\lint\expressions\Expression;

/**
 * Parses the `return' statement. If the function or method where this statement
 * appears is still in guess mode (the returned type is set to {@link it\icosaedro\lint\types\GuessType}
 * then the type of the returned expression is set in the signature.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/10/13 18:16:14 $
 */
class ReturnStatement {
	 
	/**
	 * Parses the `return' statement.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		
		if( $pkg->curr_func !== NULL ){
			// Inside function.
			$sign = $pkg->curr_func->sign;
			$n = "function " . $pkg->curr_func->name;
			
		} else if( $pkg->curr_method !== NULL ){
			// Inside method.
			$sign = $pkg->curr_method->sign;
			$n = "method " . $pkg->curr_method;
			
		} else {
			// In global scope.
			$here = $scanner->here();
			$globals->logger->error($here, "`return' in global scope");
			$pkg->notLibrary("Contains `return' statement in global scope in line "
			. $here->getLineNo() . ".");
			if( $scanner->sym !== Symbol::$sym_semicolon ){
				/* $ignore = */ Expression::parse($globals);
			}
			return;
		}

		if( $scanner->sym === Symbol::$sym_semicolon ){
			// return [void]
			if( $sign->returns === GuessType::getInstance() ){
				$sign->returns = Globals::$void_type;
				//$globals->logger->notice($scanner->here(), "from this `return;' we guess the $n returns void");
			
			} else if( $sign->returns instanceof VoidType ){
				// ok.
			
			} else {
				$globals->logger->error($scanner->here(), "$n: missing return value");
			}
			
			
		} else {
			// return EXPR
			$r = Expression::parse($globals);
			if( $r->isUnknown() ){
				// ignore
				
			} else if( $sign->returns instanceof GuessType ){
				$sign->returns = $r->getType();
				//$globals->logger->notice($scanner->here(), "from this `return;' we guess the $n returns $returned");
			
			} else if( $r->assignableTo($sign->returns) ){
				// Ok.
				
			} else {
				$globals->logger->error($scanner->here(),
				"$n: expected return type is " . $sign->returns
				. ", found " . $r->getType());
			}
				
		}

		FormalArguments::checkFormalArgsByReference($globals, TRUE);
	}
	
}

