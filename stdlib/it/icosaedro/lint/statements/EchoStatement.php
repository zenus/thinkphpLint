<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\lint\Symbol;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class EchoStatement {
	 
	/**
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		while(TRUE){
			$r = Expression::parse($globals);
			$r = $r->convertToString($globals->logger, $scanner->here());
			//echo "FIXME echo argument is: r=$r\n";
			if( $scanner->sym !== Symbol::$sym_comma )
				break;
			$scanner->readSym();
		}
	}
	
}

