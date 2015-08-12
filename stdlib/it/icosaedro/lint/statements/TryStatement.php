<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\ExceptionsSet;
use it\icosaedro\lint\ThrowExceptions;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/05 15:11:04 $
 */
class TryStatement {
	
	private static $catch_nesting_level = 0;
	 
	/**
	 * Parses the <code>try/catch/finally</code> statement.
	 * @param Globals $globals
	 * @return int See {@link it\icosaedro\lint\Flow}.
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		
		$try_location = $scanner->here();
		if( $globals->isPHP(4) )
			$logger->error($try_location, "`try' statement not available (PHP 5)");
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lbrace, "expected `{' after try");

		/* Prepare to collect exceptions thrown inside try{}: */
		$pkg->try_block_nesting_level++;
		$saved_exceptions = $pkg->exceptions;
		$pkg->exceptions = new ExceptionsSet();

		/* Parse try{} block: */
		$before = new AssignedVars($globals);
		$res = CompoundStatement::parse($globals);
		if( ($res & Flow::NEXT_MASK) == 0 )
			$after = $before;
		else
			$after = new AssignedVars($globals);

		/* Gets exceptions thrown inside try{}: */
		$pkg->try_block_nesting_level--;
		$thrown = $pkg->exceptions;
		$pkg->exceptions = $saved_exceptions;

		/* Parses catch() branches: */
		$found_catch = FALSE;
		if( $scanner->sym === Symbol::$sym_catch ){
			$found_catch = TRUE;
			$caught = new ExceptionsSet();
			do {
				$scanner->readSym();

				$globals->expect(Symbol::$sym_lround, "expected `(' after `catch'");
				$scanner->readSym();

				// Parse caught exception:
				$globals->expect(Symbol::$sym_identifier, "expected exception name");
				$name = $scanner->s;
				$c = $globals->searchClass($name);
				if( $c === NULL ){
					$logger->error($scanner->here(), "undefined class $name");
				} else if( $c->is_exception ){

					// Remove $c from thrown set:
					if( ! $c->is_unchecked
					&& $thrown->removeWithSubclasses($c) == 0 ){
						$logger->error($scanner->here(),
						"exception $c not thrown or already caught");

					} else {
						// Add $c to caught set:
						if( ! $caught->put($c) )
							$logger->error($scanner->here(),
							"exception $c already caught");
					}

					$globals->accountClass($c);

				} else {
					$logger->error($scanner->here(),
					"the class $c is not an exception");
					$c = NULL;
				}
				$scanner->readSym();

				/*
					Parse exception variable.

					We have a problem here: this variable is the only variable in
					PHPLint that may change type dynamically:

					try{}
					catch(E1 $e){}  # $e of type E1
					catch(E2 $e){}  # $e of type E2

					Then:

					1. $e cannot exist before try{}.
					2. $e must be destroyed after the catch{} branch.

					Variables cannot be easily destroyed because this would
					invalidate all the existing AssignedVars sets, so we simply
					change its name to an invalid name
					"$name#"+catch_nesting_level" to accounts for possible
					nested try/catch statements. This solution if quite
					imperfect because:

					a. Cought exception variables $e cannot be used outside
					the catch() branches.

					b. If the try/catch statement appears in global scope,
					then the documentation reports these dummy variables
					$name#123 etc. We mitigate this latter problem marking
					these dummy variables as `private'.
				*/

				$before->restore();
				$globals->expect(Symbol::$sym_variable, "expected variable name");
				$name = $scanner->s;
				$v = $globals->searchVar($name);
				if( $v === NULL ){
					$v = new Variable($name, TRUE, $scanner->here(), $pkg->scope);
					if( $c === NULL )
						$v->type = Globals::$unknown_type;
					else
						$v->type = $c;
					$v->assigned = TRUE;
					$v->assigned_once = TRUE;
					$globals->addVar($v);
				} else {
					$logger->error($scanner->here(),
					"variable $v is already in use, it cannot be used in catch() branch (PHPLint restriction)");
					$v = NULL;
				}
				$scanner->readSym();

				$globals->expect(Symbol::$sym_rround, "expected `)'");
				$scanner->readSym();

				$globals->expect(Symbol::$sym_lbrace, "expected `{'");
				$p = CompoundStatement::parse($globals);

				// Checks the variable has been used.
				if( $v !== NULL  ){
					if( $v->used == 0 && $globals->report_unused )
						$logger->notice($v->decl_in,
						"caught exception variable $v not used");
					$v->used = 100;  # Avoids "unused variable $catch#1" messages

					// Invalidate the variable, so that cannot be used:
					$v->name .= "#" . self::$catch_nesting_level++;
				}

				$res = $res | $p;
				if( ($p & Flow::NEXT_MASK) != 0 )
					$after->intersection(new AssignedVars($globals));

			} while($scanner->sym === Symbol::$sym_catch);
		}

		// Throws remaining uncaught exceptions as usual:
		ThrowExceptions::all($globals, $thrown);
		
		$found_finally = FALSE;
		if( $scanner->sym === Symbol::$sym_finally ){
			$found_finally = TRUE;
			$after->restore();
			$scanner->readSym();
			$globals->expect(Symbol::$sym_lbrace, "expected `{' after finally");

			/* Parse finally{} block: */
			$p = CompoundStatement::parse($globals);
			/*
			 * If paths after finally{} does not include NEXT_MASK, the
			 * final paths are those set by finally{} because "return", "throw",
			 * "break" or "continue" statements in finally{} prevails over
			 * those in try{}catch(){}, that is, control never return to the
			 * try{} or catch(){} last statement.
			 * If finally{} sets NEXT_MASK path, then all the paths set by
			 * finally{} and by try{}catch(){} are possible.
			 */
			if( ($p & Flow::NEXT_MASK) == 0 )
				$res = $p;
			else
				$res = $res | $p;
			
			$after = new AssignedVars($globals);
		}
		
		if( !( $found_catch || $found_finally) )
			$logger->error($scanner->here(),
			"there must be at least one catch{} or finally{} branch after try{}");
		
		$after->restore();

		return $res;
	}
	
}

