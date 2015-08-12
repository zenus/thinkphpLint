<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\ParseException;

/**
 * Parses a statement beginning with the "namespace" keyword.
 * There are several cases to be considered:
 * 1. namespace NAME;
 * 2. namespace NAME { ... }
 * 3. namespace\NAME... (resolve NS operator).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/08/16 05:27:53 $
 */
class NamespaceStatement {
	
	/**
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals){
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if( $globals->isPHP(4) )
			throw new ParseException($scanner->here(), "namespace not available (PHP 5)");
		$scanner->readSym();

		if( $scanner->sym === Symbol::$sym_identifier ){
			if( NamespaceResolver::isAbsolute($scanner->s) ){
				// Detected: namespace\xyz, resolve "namespace" operator:
//				if( $pkg_ns !== NULL )
					$scanner->s = "\\" . $pkg->resolver->name . $scanner->s;
				return;
				
			} else {
				// Detected: namespace xyz
				$pkg->resolver->close($globals->logger);
				$pkg->resolver->open($scanner->s);
				$scanner->readSym();
			}

		} else if( $scanner->sym === Symbol::$sym_lbrace ){
			# Detected: namespace {
			$pkg->resolver->close($globals->logger);
			$pkg->resolver->open("");

		} else {
			throw new ParseException($scanner->here(), "unexpected symbol " . $scanner->sym);
		}

		// It's a new namespace definition.
		if( $scanner->sym === Symbol::$sym_semicolon ){
			// Detected: namespace NS ;
			$scanner->readSym();
			
		} else if( $scanner->sym === Symbol::$sym_lbrace ){
			// Detected: namespace NS {
			$res = Statement::parse($globals);
			// FIXME: should check $res?
			$pkg->resolver->close($globals->logger);
			
		} else {
			throw new ParseException($scanner->here(), "unexpected symbol " . $scanner->sym);
		}
		
	}
}
