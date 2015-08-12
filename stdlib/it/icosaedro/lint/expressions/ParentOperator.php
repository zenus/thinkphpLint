<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;

/**
 * Parses the "parent::" operator.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:10:15 $
 */
class ParentOperator {
	
	/**
	 * Parses the "parent::" operator.
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_double_colon, "expected `::' after `parent'");
		$c = $pkg->curr_class;
		if( $c !== NULL ){
			$parent_ = $c->extended;
			if( $parent_ === NULL ){
				$logger->error($scanner->here(), "no parent class for $c");
			}
		} else {
			$parent_ = NULL;
			$logger->error($scanner->here(), "invalid `parent::': not inside a class");
		}
		return ClassStaticAccess::parse($globals, $parent_);
	}

}
