<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";

/**
 * Accounts for errors triggered in the current context of the parser. Errors
 * are triggered when the "trigger_error" statement is found or when a function
 * or method that triggers errors is called.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/11 10:11:55 $
 */
class TriggerErrors {

	/**
	 *
	 * @param Globals $globals
	 * @param int $err 
	 * @return void
	 */
	public static function single($globals, $err)
	{
		$pkg = $globals->curr_pkg;
		
		if( $pkg->silencer_level > 0 )
			return;
		
		if( $globals->error_throws_exception !== NULL ){
			ThrowExceptions::single($globals, $globals->error_throws_exception);
			return;
		}
		
		if( $globals->logger->print_notices )
			$globals->logger->notice($pkg->scanner->here(),
			"triggering " . ErrorsSet::nameOf($err));
		
		// Remember that somewhere errors are used, then cannot be remapped
		// to exception anymore:
		if( $globals->first_error_source_found === NULL )
			$globals->first_error_source_found = $pkg->scanner->here();

		if( $pkg->curr_func !== NULL ){
			$f = $pkg->curr_func;
			$sign = $f->sign;
			if( $sign->errors->contains($err) )
				return;
			$name = ErrorsSet::nameOf($err);
			$globals->logger->error($pkg->scanner->here(),
			"$f: error $name must be handled or declared to be triggered");
		
		} else if( $pkg->curr_method !== NULL ){
			$m = $pkg->curr_method;
			$sign = $m->sign;
			if( $sign->errors->contains($err) )
				return;
			$name = ErrorsSet::nameOf($err);
			$globals->logger->error($pkg->scanner->here(),
			"$m: error $name must be handled or declared to be triggered");
			
		} else {
			$here = $pkg->scanner->here();
			$name = ErrorsSet::nameOf($err);
			$globals->logger->warning($pkg->scanner->here(),
			"unhandled error $name at global scope");
			$pkg->notLibrary("Unhandled error $name at global scope in line "
			. $here->getLineNo() . ".");
		}
	}

	/**
	 *
	 * @param Globals $globals
	 * @param ErrorsSet $errs 
	 * @return void
	 */
	public static function all($globals, $errs)
	{
		if( $errs->isEmpty() )
			return;
		
		if( $globals->error_throws_exception !== NULL ){
			ThrowExceptions::single($globals, $globals->error_throws_exception);
			return;
		}
		
		$mask = 1; // scans every error bit
		$all = $errs->getErrors();
		while( $all != 0 ){
			if( ($all & $mask) != 0 ){
				self::single($globals, $mask);
				$all &= ~$mask; // reset bit $mask from $all
			}
			$mask = $mask << 1; // next bit $mask
		}
	}

}
