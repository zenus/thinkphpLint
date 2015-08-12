<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\Globals;
//use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
//use it\icosaedro\lint\ParseException;

/**
 * Parses the "clone OBJ" operator.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:10:15 $
 */
class CloneOperator {
	
	
	/**
	 * Parses the "clone OBJ" operator.
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		$scanner = $globals->curr_pkg->scanner;
		if( $globals->isPHP(4) )
			$globals->logger->error($scanner->here(),
			"no `clone' operator (PHP 5)");
		$scanner->readSym();
		$r = Expression::parse($globals);
		if( $r->isUnknown() ){
			// ignore
		} else if( $r->isClass() ){
			# Account for usage of method __clone():
			$c = cast(ClassType::NAME, $r->getType());
			$m = $c->searchMethod(ClassMethod::$CLONE_NAME);
			if( $m !== NULL )
				$globals->accountMethod($m);

		} else {
			$globals->logger->error($scanner->here(),
			"invalid type for `clone' operator: " . $r->getType());
			$r = Result::getUnknown();
		}
		return $r;
	}

}
