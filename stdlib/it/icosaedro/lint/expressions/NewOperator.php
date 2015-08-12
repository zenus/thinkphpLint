<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\ParseException;

/**
 * Parses the <code>new</code> operator.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:37:30 $
 */
class NewOperator {
	
	/**
	 * Parses the <code>new</code> operator.
	 * @param Globals $globals
	 * @return Result
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$scanner->readSym();

		if( $scanner->sym === Symbol::$sym_namespace )
			$globals->resolveNamespaceOperator();

		// Resolve the class of the "new" operator:
		/*. ClassType .*/ $c = NULL;
		
		if( $scanner->sym === Symbol::$sym_identifier ){
			$s = $scanner->s;
			$c = $globals->searchClass($s);
			if( $c === NULL )
				$logger->error($scanner->here(), "unknown class $s");

		} else if( $scanner->sym === Symbol::$sym_self ){
			$c = $pkg->curr_class;
			if( $c === NULL )
				$logger->error($scanner->here(), "`self': not inside a class");

		} else if( $scanner->sym === Symbol::$sym_static ){
			$c = $pkg->curr_class;
			if( $c === NULL ){
				$logger->error($scanner->here(), "`static': not inside a class");
			} else {
				$logger->error($scanner->here(), "`new static' is not supported by PHPLint");
			}

		} else if( $scanner->sym === Symbol::$sym_parent ){
			$c = $pkg->curr_class;
			if( $c === NULL ){
				$logger->error($scanner->here(), "`parent': we are not inside a class");
			} else {
				$c = $c->extended;
				if( $c === NULL ){
					$logger->error($scanner->here(), "`parent': no parent class");
					# FIXME: stdClass is the parent of every class
				}
			}

		} else if( $scanner->sym === Symbol::$sym_variable ){
			$logger->error($scanner->here(), "expected static class name after `new', variable class name not allowed (PHPLint restriction)");
			
		} else {
			throw new ParseException($scanner->here(), "expected class name or `self', `static' or `parent' after `new'");
		}
		
		if( $c === NULL ){
			$scanner->readSym();
			if( SkipUnknown::canSkip($scanner->sym) )
				SkipUnknown::anything($globals);
			return Result::getUnknown();
		}

		if( $c->is_abstract ){
			$logger->error($scanner->here(), "cannot instantiate abstract class $c");
		}

		if( $c->is_interface ){
			$logger->error($scanner->here(), "cannot instantiate interface class $c");
		}
		
		$globals->accountClass($c);

		/*
			Search the constructor of `class'; mark as invoked the default
			or inherited constructor of any extended class, so we may
			detect if any actual constructor gets parsed only after its
			usage:
		*/
		$ctor = $c->constructor;
		if( $ctor === NULL )
			$ctor = $c->parentConstructor();

		if( $ctor === NULL ){ # no constructor for this class
			/* Invoke default constructor void(): */
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_lround ){
				$scanner->readSym();
				if( $scanner->sym !== Symbol::$sym_rround ){
					$logger->error($scanner->here(), "expected `)', found " . $scanner->sym
					. ". The class $c does not have a constructor, so no arguments are required");
					# skip unexpected args and continue:
					while(TRUE){
						/* $ignore = */ Expression::parse($globals);
						if( $scanner->sym === Symbol::$sym_comma ){
							$scanner->readSym();
						} else {
							break;
						}
					}
				}
				$globals->expect(Symbol::$sym_rround, "expected `)'");
				$scanner->readSym();
			}

		} else { # there is a constructor for this class
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_lround ){
				Call::parseMethodCall($globals, $ctor->class_, $ctor->name, FALSE, TRUE);
			} else if( $ctor->sign->mandatory > 0 ){
				$logger->error($scanner->here(),
				"missing required arguments for constructor $ctor declared in "
				. $logger->reference($scanner->here(), $ctor->decl_in));
			} else {
				$logger->notice($scanner->here(),
				"missing parentheses after class name. Although "
				. "the constructor $ctor"
				. " has no mandatory arguments, it's a good habit"
				. " to provide these parentheses.");
			}

		}
		return Result::factory($c);
	}
	
}
