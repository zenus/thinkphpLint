<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\ErrorsSet;
use it\icosaedro\lint\ExceptionsSet;
use it\icosaedro\lint\docblock\DocBlock;
use it\icosaedro\lint\docblock\DocBlockWrapper;

/**
 * Collects triggered errors and thrown exceptions from DocBlock and PHPLint
 * meta-code.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:10:50 $
 */
class TriggersAndThrows {
	
	/**
	 * @param Globals $globals
	 * @param DocBlock $db
	 * @param Signature $sign
	 * @return void
	 */
	private static function collectsErrorsFromDocBlock($globals, $db, $sign)
	{
		$pkg = $globals->curr_pkg;
		if( count($db->triggers_names) == 0 )
			return;
		if( $sign->errors === ErrorsSet::getEmpty() )
			$sign->errors = new ErrorsSet();
		foreach($db->triggers_names as $name){
			try {
				$err = ErrorsSet::parse($name);
				$sign->errors->put($err);
			}
			catch(\InvalidArgumentException $ex){
				$globals->logger->error($db->decl_in,
				"unknown error: $name");
			}
		}
	}

	/**
	 * @param Globals $globals
	 * @param DocBlock $db
	 * @param Signature $sign
	 * @return void
	 */
	private static function collectsExceptionsFromDocBlock($globals, $db, $sign)
	{
		$pkg = $globals->curr_pkg;
		if( count($db->throws_exceptions) == 0 )
			return;
		if( $sign->exceptions === ExceptionsSet::getEmpty() )
			$sign->exceptions = new ExceptionsSet();
		foreach($db->throws_exceptions as $e){
			$sign->exceptions->put($e);
		}
	}

	/**
	 * @param Globals $globals
	 * @param Signature $sign
	 * @return void
	 */
	private static function parseTriggers($globals, $sign)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if( $sign->errors === ErrorsSet::getEmpty() )
			$sign->errors = new ErrorsSet();
		do {
			$scanner->readSym();
			$globals->expect(Symbol::$sym_x_identifier, "expected error name");
			try {
				$err = ErrorsSet::parse($scanner->s);
				$sign->errors->put($err);
			}
			catch(\InvalidArgumentException $ex){
				$globals->logger->error($scanner->here(),
				"unknown error: " . $scanner->s);
			}
			$scanner->readSym();
		} while( $scanner->sym === Symbol::$sym_x_comma );
	}

	/**
	 * @param Globals $globals
	 * @param Signature $sign
	 * @return void
	 */
	private static function parseThrows($globals, $sign)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if( $sign->exceptions === ExceptionsSet::getEmpty() )
			$sign->exceptions = new ExceptionsSet();
		do {
			$scanner->readSym();
			$globals->expect(Symbol::$sym_x_identifier, "expected exception name");
			$c = $globals->searchClass($scanner->s);
			if( $c === NULL )
				$globals->logger->error($scanner->here(),
				"unresolved class " . $scanner->s);
			else if( $c->is_exception )
				$sign->exceptions->put($c);
			else
				$globals->logger->error($scanner->here(),
				"$c: not an exception");
			$scanner->readSym();
		} while( $scanner->sym === Symbol::$sym_x_comma );
	}

	/**
	 * Parses PHPLint meta-code declaring triggered errors and thrown exceptions.
	 * Also adds errors and exceptions from the DocBlock.
	 * Called to parse: function, method, function prototype, method prototype.
	 * @param Globals $globals
	 * @param DocBlockWrapper $dbw Can be NULL if not available.
	 * @param Signature $sign Signature of the function or method to which
	 * errors and exceptions must be added.
	 * @return void
	 */
	public static function parse($globals, $dbw, $sign)
	{
		if( $dbw !== NULL ){
			$db = $dbw->getDocBlock();
			if( $db !== NULL ){
				self::collectsErrorsFromDocBlock($globals, $db, $sign);
				self::collectsExceptionsFromDocBlock($globals, $db, $sign);
			}
		}
		
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		// "triggers" and "throws" can appear in any order, also
		// several times:
		do {
			if( $scanner->sym === Symbol::$sym_x_triggers ){
				self::parseTriggers($globals, $sign);

			} else if( $scanner->sym === Symbol::$sym_x_throws ){
				self::parseThrows($globals, $sign);
				
			} else {
				break;
			}
		} while(TRUE);
		$sign->errors->close();
		$sign->exceptions->close();
	}

}
