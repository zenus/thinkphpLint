<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\ParseException;

/**
 * Parses from sym_close_tag <code>?&gt;</code> up to the next PHP open tag or
 * the end of the file.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class TextBlockStatement {
	 
	/**
	 * Parses from sym_close_tag <code>?&gt;</code> up to the next PHP open
	 * tag or the end of the file.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		while(TRUE){
			if( $scanner->sym === Symbol::$sym_text ){
				// FIXME: check encoding?
				$s = $scanner->s;
				if( $pkg->scope == 0
				&& strlen($s) > 0 && ! ($s === "\r\n" || $s === "\n") ){
					$pkg->notLibrary("Found textual content in global scope at line " . $scanner->here()->getLineNo() . ".");
				}
				$scanner->readSym();
				
			} else if( $scanner->sym === Symbol::$sym_open_tag_with_echo ){
				EchoBlockStatement::parse($globals);
				
			} else if( $scanner->sym === Symbol::$sym_open_tag ){
				$scanner->readSym();
				return;
			
			} else if( $scanner->sym === Symbol::$sym_eof ){
				return;
				
			} else {
				throw new ParseException($scanner->here(), "unexpected symbol " . $scanner->sym);
			}
		}
	}
	
}

