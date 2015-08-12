<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\TriggerErrors;
use it\icosaedro\lint\expressions\Expression;

/**
 * Parses the "trigger_error()" function-like statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class TriggerErrorStatement {
	 
	/**
	 * Parses the "trigger_error(string [, code])" statement.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
//	private static function ParseTriggerError()
//	CONST
//		E_USER_NOTICE = 1024
//	VAR
//		r: Result
//		err: INTEGER
//		err_name: string
//	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "expected `('");
		$scanner->readSym();

		$r = Expression::parse($globals);
		$r->checkExpectedType($globals->logger, $scanner->here(), Globals::$string_type);

		# Optional error code, default E_USER_NOTICE:
		if( $scanner->sym === Symbol::$sym_comma ){
			$scanner->readSym();
			$r = Expression::parse($globals);
			$r->checkExpectedType($globals->logger, $scanner->here(), Globals::$int_type);
			if( $r->isUnknown() ){
				# bad expr
				$err = 0;
			} else if( $r->getValue() === NULL ){
				# can't evaluate runtime value
				$globals->logger->error($scanner->here(), "error code is not statically determinabel - cannot check proper signature of function/method");
				$err = 0;
			} else {
				$err = (int) $r->getValue();
				
				// FIXME: must be user error if not a module
//				err_name = Errors.CodeToName(err)
//				if( err_name = NULL ){
//					$globals->logger->error($scanner->here(), "invalid error code: " . err)
//					err = 0
//				} else if( ! Package::$curr->is_module && ! Errors.IsUser$scanner->error(err) ){
//					$globals->logger->error($scanner->here(), "error code forbidden in user's program: " . err_name)
//				}
			}

		} else {
			$err = E_USER_NOTICE;
		}

		TriggerErrors::single($globals, $err);

		$globals->expect(Symbol::$sym_rround, "expected `)'");
		$scanner->readSym();
	}
	
}

