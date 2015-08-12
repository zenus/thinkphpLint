<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\FormalArgument;
use it\icosaedro\lint\TypeDecl;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class ForwardArgumentsList {

	/**
	 * Parses the argument list, triggered errors and thrown exceptions
	 * of a function's prototype or method's prototype.
	 * @param Globals $globals
	 * @param Signature $sign
	 * @param string $function_or_method "function" or "method" string, just to
	 * compose meaningful error messages.
	 * @return void
	 */
	public static function parse($globals, $sign, $function_or_method)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;

		$globals->expect(Symbol::$sym_x_lround, "expected '(' in $function_or_method declaration");
		$scanner->readSym();
		
		$opt_arg = FALSE; // TRUE past first default argument "$a=EXPR"

		while (TRUE) {
			if ($scanner->sym === Symbol::$sym_x_rround)
				break;

			if ($scanner->sym === Symbol::$sym_x_args) {
				$sign->more_args = TRUE;
				$scanner->readSym();
				break;
			}

			$a = new FormalArgument();

			// Check `return' attribute:
			if ($scanner->sym === Symbol::$sym_x_return) {
				$a->reference_return = TRUE;
				$scanner->readSym();
			}

			// Parse type of the formal arg:
			$a->type = TypeDecl::parse($globals, FALSE);
			if ($a->type === NULL) {
				$globals->logger->error($scanner->here(),
				"missing type of the argument");
				$a->type = Globals::$unknown_type;
			} else if ($a->type === Globals::$void_type) {
				$globals->logger->error($scanner->here(),
				"argument of type `void' not allowed");
				$a->type = Globals::$unknown_type;
			}

			// Parse passing method:
			if ($scanner->sym === Symbol::$sym_x_bit_and) {
				$a->reference = TRUE;
				$scanner->readSym();
			} else {
				if ($a->reference_return) {
					$globals->logger->error($scanner->here(),
					"invalid `return' attribute for argument passed by value");
					$a->reference_return = FALSE;
				}
			}

			/*
			  Parse name of the formal arg, currently ignored:
			  FIXME: check for duplicated names.
			 */
			$globals->expect(Symbol::$sym_x_variable,
			"expected name of the formal argument in $function_or_method declaration");
			$a->name = $scanner->s;
			$scanner->readSym();

			// Parse default value:
			$a->is_mandatory = TRUE;
			if ($scanner->sym === Symbol::$sym_x_assign) {
				if ($globals->isPHP(4) && $a->reference) {
					$globals->logger->error($scanner->here(),
					"can't assign default value to formal argument passed by reference (PHP 5)");
				}
				$opt_arg = TRUE;
				$a->is_mandatory = FALSE;
				$scanner->readSym();
			} else if ($opt_arg) {
				$globals->logger->error($scanner->here(), "missing default value for argument $" . $a->name . ". Hint: mandatory arguments can't follow the default ones.");
			}

			$sign->arguments[] = $a;
		
			// By-ref + default ==> implicit 'return' modifier:
			if( $a->reference && ! $a->is_mandatory )
				$a->reference_return = TRUE;
			
			if ($a->is_mandatory)
				$sign->mandatory++;

			if ($scanner->sym === Symbol::$sym_x_comma) {
				$scanner->readSym();
				if ($scanner->sym === Symbol::$sym_x_args) {
					$sign->more_args = TRUE;
					$scanner->readSym();
					break;
				}
			} else {
				break;
			}
		}
		$globals->expect(Symbol::$sym_x_rround, "expected ')' or ',' in $function_or_method declaration");
		$scanner->readSym();
	}

}
