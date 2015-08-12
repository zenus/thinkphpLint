<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\Variable;
use it\icosaedro\regex\Pattern;

/**
 * Parses a known, assigned variable. Known variables are those already listed
 * in $globals-&gt;vars[] array. Assigned variables are those known variables
 * that the flow analysis established are assigned. Being already assigned,
 * the variable can be dereferenced as array or object and used as value (RHS)
 * according to its type, or it may be assigned then used as LHS, so the
 * syntax that may follow an assigned variable is quite articulated.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/24 20:03:02 $
 */
class AssignedVar {
	
	/*. forward 
	public static Result function parse(Globals $globals, Variable $v); .*/
	
	/**
	 * Variable name pattern.
	 * @access private
	 */
	const ID = "{a-zA-Z_}{a-zA-Z_0-9\x80-\xff}+\$";
	
	/*  . forward public static Result function parse(Globals $globals, Variable $v); .  */

	
	/**
	 * Resolves $GLOBALS[EXPR] into a global variable name. We enter with
	 * symbol "[". EXPR is expected to statically resolve to the name of the
	 * referred global variable.
	 * @param Globals $globals
	 * @return string Resolved name of the global variable named in EXPR. If
	 * no valid name is resolved, displays error and returns NULL. Does not
	 * verify if that variable does really exist in global scope.
	 */
	private static function dereferenceGLOBALS($globals){
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$r = Expression::parse($globals);
		$globals->expect(Symbol::$sym_rsquare, "expected `]'");
		$scanner->readSym();
		if( $r === NULL ){
			$globals->logger->error($scanner->here(), "can't parse the name of the global variable");
			return NULL;
		} else if( ! $r->isString() ){
			$globals->logger->error($scanner->here(), "\$GLOBALS[?]: required string, found "
			. $r->getType());
			return NULL;
		}
		
		$n = $r->getValue();
		if( $n === NULL ){
			$globals->logger->error($scanner->here(), "\$GLOBALS[?]: undetermined variable name");
			return NULL;
		} else if( strlen($n) == 0 ){
			$globals->logger->error($scanner->here(), "\$GLOBALS['']: invalid empty string");
			return NULL;
		} else if( ! Pattern::matches(self::ID, $n) ){
			$globals->logger->error($scanner->here(), "\$GLOBALS['$n']: invalid name, can't be a variable!");
			return NULL;
		}
		return $n;
	}
	
	
	/**
	 * Parses a known, assigned variable and possibly the following
	 * dereferencing operators "[" and "-&gt;" and assignment.
	 * @param Globals $globals
	 * @param Variable $v 
	 * @return Result
	 */
	public static function parse($globals, $v)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();

		# Resolves $GLOBALS["varname"]:
		if( $v->name === "GLOBALS" && $scanner->sym === Symbol::$sym_lsquare ){
			
			$id = self::dereferenceGLOBALS($globals);
			
			if( $id === NULL ){
				if( SkipUnknown::canSkip($scanner->sym) )
					SkipUnknown::anything($globals);
				return Result::getUnknown();
			}
			
			$v = $globals->searchVarInScope($id, 0);
			if( $v === NULL ){
				$globals->logger->error($scanner->here(),
				"\$GLOBALS['$id']: undefined global variable");
				if( SkipUnknown::canSkip($scanner->sym) )
					SkipUnknown::anything($globals);
				return Result::getUnknown();
			}
			
		}
	
		# Catch common error "$v()" (variable function name):
		if( $scanner->sym === Symbol::$sym_lround ){
			$globals->logger->error($scanner->here(), "invalid variable-name function (PHPLint safety restriction)");
			SkipUnknown::anything($globals);
			return Result::getUnknown();
		}
		
		if( $scanner->sym === Symbol::$sym_assign )
			$globals->accountVarLHS($v);
		else
			$globals->accountVarRHS($v);
		
		$type = Dereference::parse($globals, $v->type, TRUE);
		return Result::factory($type);
	}

}
