<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\expressions\StaticExpression;
use it\icosaedro\lint\expressions\SkipUnknown;
use it\icosaedro\lint\expressions\ClassStaticAccess;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/23 18:06:12 $
 */
class StaticStatement {
	 
	/**
	 * Parses "static [T] $v1 [= EXPR]".
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;

		if( $pkg->scope == 0 )
			$logger->error($scanner->here(),
			"static declaration at global scope has no effect");
		$scanner->readSym();

		if( $scanner->sym === Symbol::$sym_double_colon ){
			$scanner->readSym();
			if( $globals->isPHP(4) ){
				$logger->error($scanner->here(), "invalid `static::' (PHP 5)");
			}
			if( $pkg->curr_class === NULL ){
				$logger->error($scanner->here(),
				"invalid `static::': not inside a class");
				SkipUnknown::anything($globals);
				return;
			} else {
				// FIXME: also parses static:: !!!
				$r = ClassStaticAccess::parse($globals, $pkg->curr_class);
				return;
			}
		}

		$t = TypeDecl::parse($globals, FALSE);

		while(TRUE){
			$globals->expect(Symbol::$sym_variable,
			"expected variable name in static declaration");
			$name = $scanner->s;

			# Check if already exists in scope:
			$v = $globals->searchVar($name);
			if( $v === NULL ){
				$v = new Variable($name, FALSE, $scanner->here(), $pkg->scope);
				$globals->addVar($v);
				$v->type = $t; // might be NULL: later, this tells it was a new var
			} else {
				if( $v->scope == $pkg->scope ){
					$logger->error($scanner->here(),
					"`static $v': variable already exists in scope");
				}
			}
			// "static" vars are always initialized:
			$v->assigned = TRUE;
			$v->assigned_once = TRUE;

			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_assign ){
				// static [T] $v = EXPR;
				// =====================
				$scanner->readSym();
				$r = StaticExpression::parse($globals);
				if( $r->isUnknown() ){
					if( $v->type === NULL )
						$v->type = Globals::$unknown_type;
				} else {
					if( $v->type === NULL ){
						$v->type = $r->getType();
					} else if( ! $r->assignableTo($v->type) ){
						$logger->error($scanner->here(),
						"cannot assign " . $r->getType()
						. " to $v of type " . $v->type);
					}
				}
				
			} else if( $v->type === NULL ){
				// static $v;
				// ==========
				$logger->error($scanner->here(),
				"undefined type for static variable $v. Hint: you may"
				. " indicate an explicit type (example: `static /*.string.*/ $v')"
				. " or assign an initial value (example: `static $v=\"abc\"').");
				$v->type = Globals::$unknown_type;
				
			} else if( ! Globals::$null_type->assignableTo($v->type) ){
				// static [T] $v;  (with NULL not allowed for T)
				// ==============
				$logger->error($scanner->here(),
				"local variable $v of type " . $v->type
				. " requires an initial value, otherwise it would be initialized to the invalid value NULL at runtime (PHPLint safety safety restriction)");
			}
			
			if( $scanner->sym === Symbol::$sym_comma ){
				$scanner->readSym();
			} else {
				break;
			}
		}
	}
	
}

