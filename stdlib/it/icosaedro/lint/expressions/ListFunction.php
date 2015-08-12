<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\ParseException;

/**
 * Parses the "list()" function.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/10/06 19:16:09 $
 */
class ListFunction {
	
	/**
	 * Parses the "list()" function.
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		// FIXME: todo
		throw new ParseException($globals->curr_pkg->scanner->here(),
			"list() is unimplemented");
//private static function ParseList()
//// : Result
//
/////* VAR */
////	$r: Result
////	$t: Type
//{
//	$scanner->readSym();
//	Expect(Symbol::$sym_lround, "expected '(' after 'list'");
//	LOOP
//		$scanner->readSym();
//		if( $scanner->sym == Symbol::$sym_variable ){
//			$t = NULL
//			ParseLHS($t);
//			# FIXME: assign the type to v
//		}
//		if( $scanner->sym == Symbol::$sym_comma ){
//			/* more elements in list */
//		} else if( $scanner->sym == Symbol::$sym_rround ){
//			$scanner->readSym();
//			EXIT
//		} else {
//			$scanner->here()->fatal("expected variable name or closing ')' inside list()");
//		}
//	}
//	Expect(Symbol::$sym_assign, "expected '=' after list()");
//	$scanner->readSym();
//	$r = Expression::parse();
//	if( $r == NULL ){
//		$logger->warning($scanner->here(), "unknown type assigned to the list()");
//	} else if( $r->type[basetype] !== array ){
//		$logger->error($scanner->here(), "invalid value assigned to list(): " . TypeToString($r->type));
//	}
//	return {{array, void, NULL, NULL}, NULL} # FIXME: which return type?;
//}
	}

}
