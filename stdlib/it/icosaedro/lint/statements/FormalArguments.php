<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\FormalArgument;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use it\icosaedro\lint\expressions\StaticExpression;
use it\icosaedro\lint\ParseException;

/**
 * Parses a list of formal arguments of function or method.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:17:42 $
 */
class FormalArguments {

	/**
	 * Parses a formal argument.
	 * @param Globals $globals
	 * @param DocBlockWrapper $dbw
	 * @param boolean $opt_arg True if this argument is optional.
	 * @return FormalArgument The formal argument.
	 */
	private static function parseArg($globals, $dbw, $opt_arg)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;

		$a = new FormalArgument();

		// Check `return' attribute:
		if ($scanner->sym === Symbol::$sym_x_return) {
			$a->reference_return = TRUE;
			$scanner->readSym();
		}

		// Parse type of the formal arg:
		$a->type = TypeDecl::parse($globals, TRUE);

		// Parse passing method by value or by reference:
		if ($scanner->sym === Symbol::$sym_bit_and) {
			$a->reference = TRUE;
			$scanner->readSym();
		} else {
			if ($a->reference_return) {
				$globals->logger->error($scanner->here(),
				"invalid `return' attribute for argument passed by value");
				$a->reference_return = FALSE;
			}
		}

		// Parse name of the formal arg:
		$globals->expect(Symbol::$sym_variable, "expected name of the formal argument in function declaration");
		$a->name = $scanner->s;

		$db = $dbw->getDocBlock();

		// Collects DocBlock data for this parameter:
		$i = $dbw->getParamIndex($a->name);
		if( $i < 0 ){
//			if( $db !== NULL )
//				$db->where->error("missing parameter \$" . $a->name . " in DocBlock");

		} else if( $a->type === NULL ){
			$a->type = $db->params_types[$i];
			if( $a->reference != $db->params_byref[$i] )
				$globals->logger->error($scanner->here(),
				"the by-reference passing mode `&' for parameter \$"
				. $a->name . ", if really required, must be indicated both in the DocBlock @param TYPE & \$VAR and in PHP code");

		} else {
			$globals->logger->error($scanner->here(),
			"parameter \$" . $a->name .
			" is defined both in DocBlock and in PHPLint meta-code");

		}

//		if ($p !== NULL) {
//			$p->used = TRUE;
//		}

		// Formal arguments are accounted as local variables.
		$v = $globals->searchVar($a->name);
		if ($v !== NULL)
			$globals->logger->error($scanner->here(),
			"duplicated parameter $" . $a->name);
		
		$v = new Variable($a->name, FALSE, $scanner->here(), $pkg->scope);
		$globals->addVar($v);
		
		$scanner->readSym();

		// Parse default value:
		$a->is_mandatory = TRUE;
		if ($scanner->sym === Symbol::$sym_assign) {
			$a->is_mandatory = FALSE;
			if ($globals->isPHP(4) && $a->reference) {
				$globals->logger->error($scanner->here(), "can't assign default value to formal argument passed by reference (PHP 5)");
			}
			$scanner->readSym();
			$r = StaticExpression::parse($globals);
			$a->value = $r;
			if ($r === Globals::$unknown_type) {
				// error already signaled
			} else if ($a->type === NULL) {
				$a->type = $r->getType();
			} else {
				if (!$r->assignableTo($a->type))
					$globals->logger->error($scanner->here(),
					"parameter \$" . $a->name . ": the type " . $r->getType()
					. " of the default value is incompatible with the declared type "
					. $a->type);
			}
		} else if ($opt_arg) {
			$globals->logger->error($scanner->here(),
			"missing default value for argument \$" . $a->name
			. ". Hint: mandatory arguments can't follow the default ones.");
		}

		if ($a->type === NULL) {
			$globals->logger->error($scanner->here(),
			"undefined type for argument $" . $a->name
			. ". Hint: you may"
			. " indicate an explicit type (example: `/*.int.*/ $" . $a->name . "')"
			. " or assign a default value (example: `$" . $a->name . "=123')"
			. " or add a DocBlock line tag (example: `@param int $"
			. $a->name . "').");
			$a->type = Globals::$unknown_type;
		}
		
		$v->type = $a->type;
		
		// By-ref + default ==> implicit 'return' modifier:
		if( $a->reference && ! $a->is_mandatory )
			$a->reference_return = TRUE;
		
		$v->assigned = $v->assigned_once =
			! $a->is_mandatory
			|| ! $a->reference
			|| ! $a->reference_return;
		
		if ($pkg->is_module
		|| $pkg->curr_method !== NULL && $pkg->curr_method->is_abstract
		|| $a->reference_return
		) {
			$v->used = 100;
		}
		return $a;
	}

	
	/**
	 * Parses a list of formal args of a function or method.
	 * Formal arguments are accounted and added to the local scope, so the
	 * `scope' global variable must be properly initialized.
	 * Formal arguments passed by value are marked as assigned.
	 * Formal arguments passed by return+reference are marked as unassigned.
	 * Formal arguments passed by reference are marked as assigned.
	 * function_or_module: "function" or "method".
	 * @param Globals $globals
	 * @param DocBlockWrapper $dbw
	 * @param Signature $sign
	 * @return void
	 */
	public static function parse($globals, $dbw, $sign) {
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;

		$globals->expect(Symbol::$sym_lround, "expected '(' in function declaration");
		$scanner->readSym();

		if ($scanner->sym === Symbol::$sym_rround) {
			# f().
		} else if ($scanner->sym === Symbol::$sym_x_args) {
			# f(args):
			$sign->more_args = TRUE;
			$scanner->readSym();
		} else {
			# f(one or more args):
			$opt_arg = FALSE;
			while (TRUE) {

				if ($scanner->sym === Symbol::$sym_x_args) {
					throw new ParseException($scanner->here(), "there must be a meta-code comma `,' separating the special symbol args from the other arguments");
				}

				$a = self::parseArg($globals, $dbw, $opt_arg);
				$sign->arguments[] = $a;
				if ($a->is_mandatory) {
					$sign->mandatory++;
				} else {
					$opt_arg = TRUE;
				}

				if ($scanner->sym === Symbol::$sym_comma) {
					$scanner->readSym();
				} else if ($scanner->sym === Symbol::$sym_x_comma) {
					$scanner->readSym();
					$globals->expect(Symbol::$sym_x_args, "expected `args'");
					$sign->more_args = TRUE;
					$scanner->readSym();
					break;
				} else {
					break;
				}
			}
		}
		
		// Check all DocBlock @param are actual arguments:
		$db = $dbw->getDocBlock();
		if( $db !== NULL && $db->params_names !== NULL ){
			foreach($db->params_names as $param_name){
				$found = FALSE;
				foreach($sign->arguments as $arg){
					if( $arg->name === $param_name ){
						$found = TRUE;
						break;
					}
				}
				if( ! $found ){
					$globals->logger->error($db->decl_in,
					"@param \$$param_name is not an argument");
				}
			}
		}

		$globals->expect(Symbol::$sym_rround, "expected ')' or ',' in function declaration");
		$scanner->readSym();
	}
	
	
	
	/**
	 * Checks that all the formal arguments passed by reference and with the
	 * `return' attribute be actually assigned. This check should be performed
	 * after every `return' statement and at the end of the function body or
	 * method body.
	 * @param Globals $globals
	 * @param boolean $on_return Set to true if the check occurs on a `return'
	 * statement, false if the check occurs at the end of the body of the
	 * function or method. On return, the error message makes reference to the
	 * return statement, otherwise the error message makes reference to the
	 * position where the formal argument is declared.
	 * @return void
	 */
	public static function checkFormalArgsByReference($globals, $on_return)
	{
		$pkg = $globals->curr_pkg;
		if( $pkg->is_module )
			return;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;
		if( $pkg->curr_func !== NULL ){
			$arguments = $pkg->curr_func->sign->arguments;
		} else if( $pkg->curr_method !== NULL ){
			$arguments = $pkg->curr_method->sign->arguments;
		} else {
			return;
		}
		if( $arguments === NULL )
			return;
		foreach($arguments as $a){
			if( $a->reference_return ){
				$v = $globals->searchVarInScope($a->name, $pkg->scope);
				if( ! $v->assigned ){
					
					if( $on_return )
						$where = $scanner->here();
					else
						$where = $v->decl_in;
					$logger->error($where, "formal argument that returns by reference $v might not have been assigned");
				}
			}
		}
	}

}