<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\lint\ThrowExceptions;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:39 $
 */
class ThrowStatement {
	 
	/**
	 * Parses the <code>throw</code> statement.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$r = Expression::parse($globals);
		
		if( $r->isUnknown() ){
			#$globals->logger->warning($scanner->here(), "can't determine the type of the expression")
			return;
		}
		
		if( ! $r->isClass() ){
			$globals->logger->error($scanner->here(), "expected exception but found " . $r->getType());
			return;
		}
		
		$c = cast(ClassType::NAME, $r->getType());
		if( ! $c->is_exception ){
			$globals->logger->error($scanner->here(), "not an exception: " . $c);
			return;
		}
		ThrowExceptions::single($globals, $c);
	}
	
}

