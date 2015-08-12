<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\lint\types\ClassType;

/**
 * Accounts for exceptions thrown in the current context of the parsing.
 * Exceptions are thrown when the "throw" statement is found, or when a
 * function or method that throws exception is called.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/11 10:11:30 $
 */
class ThrowExceptions {

	/**
	 * Accounts for a single exceptions thrown.
	 * @param Globals $globals
	 * @param ClassType $e Exceptions thrown.
	 * @return void
	 */
	public static function single($globals, $e)
	{
		if( $e->is_unchecked )
			return;
		
		$pkg = $globals->curr_pkg;
		
		if( $globals->logger->print_notices )
			$globals->logger->notice($pkg->scanner->here(), "throwing $e");

		if( $pkg->try_block_nesting_level > 0 ){
			// Inside "try{}" block. Collect exceptions here:
			$pkg->exceptions->put($e);
			
		} else if( $pkg->curr_func !== NULL ){
			$f = $pkg->curr_func;
			$sign = $f->sign;
			if( $sign->exceptions->includes($e) )
				return;
			$globals->logger->error($pkg->scanner->here(),
			"$f: exception $e must be caught or declared to be thrown");
		
		} else if( $pkg->curr_method !== NULL ){
			$m = $pkg->curr_method;
			$sign = $m->sign;
			if( $sign->exceptions->includes($e) )
				return;
			$globals->logger->error($pkg->scanner->here(),
			"$m: exception $e must be caught or declared to be thrown");
			
		} else {
			$globals->logger->warning($pkg->scanner->here(),
			"uncaught exception $e at global scope");
			$pkg->notLibrary("Uncaught exception $e at global scope in line "
			. $pkg->scanner->here()->getLineNo() . ".");
		}
	}
	
	
	/**
	 * Accounts for a set of exceptions thrown.
	 * @param Globals $globals
	 * @param ExceptionsSet $es Exceptions thrown.
	 * @return void
	 */
	public static function all($globals, $es)
	{
		foreach($es as $e)
			self::single($globals, cast(ClassType::NAME, $e));
	}

}
