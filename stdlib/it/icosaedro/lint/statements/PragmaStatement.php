<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Where;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\TypeDescriptor;
use it\icosaedro\io\File;

/**
 * Parsing of the meta-code <code>pragma</code> statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class PragmaStatement {
	
	/**
	 * Parses the class autoloader parameters.
	 * @param Globals $globals
	 * @param Where $where
	 * @param string[int] $a 
	 * @return void
	 */
	private static function parseAutoload($globals, $where, $a)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		if( $pkg->curr_func === NULL
		|| ! $pkg->curr_func->name->equals(Globals::$AUTOLOAD_FQN) ){
			$logger->error($where, "autoload pragma allowed only inside "
			. Globals::$AUTOLOAD_FQN . " magic function");
			return;
		}
		if( count($a) === 1 ){
			$logger->error($where, "missing required arguments in autoload pragma");
		} else if( $a[1] === "schema1" ){
			if( count($a) !== 5 ){
				$logger->error($where, "expected 5 arguments for pragma autoload, but " . count($a) . " found");
				return;
			}
			if( $globals->autoload_function !== NULL ){
				$logger->error($where, "pragma autoload already set in "
				. $logger->reference($where, $globals->autoload_function->decl_in));
				return;
			}
			if( ! $globals->recursive_parsing ){
				$logger->error($where, "recursive parsing disabled by --no-recursive option");
				return;
			}
			$globals->autoload_function = $pkg->curr_func;
			$globals->autoload_prepend = File::fromLocaleEncoded($a[2], $where->getFile()->getParentFile());
			$globals->autoload_separator = $a[3];
			$globals->autoload_append = $a[4];
			
		} else {
			$logger->error($where, "undefined autoload schema: " . $a[1]);
		}
	}
	
	
	/**
	 * Parses the error-to-exception remapping.
	 * @param Globals $globals
	 * @param Where $where
	 * @param string[int] $a 
	 * @return void
	 */
	private static function parseErrorThrowsException($globals, $where, $a)
	{
		$logger = $globals->logger;
		
		if( count($a) != 2 ){
			$logger->error($where, "expected 1 argument for pragma error_throws_exception");
			return;
		}
		
		if( $globals->error_throws_exception !== NULL ){
			$logger->error($where, "pragma already set");
			return;
		}
		
		if( $globals->first_error_source_found !== NULL ){
			$logger->error($where,
			"error mapping into exception cannot be applied here"
			. " because an error source was already encountered"
			. " in source code before, the first one seen in "
			. $logger->reference($where, $globals->first_error_source_found));
		}
		$t = TypeDescriptor::parse($globals->logger, $where, $a[1], TRUE, $globals, FALSE);
		if( $t === Globals::$unknown_type ){
			//
		} else if( $t instanceof ClassType ){
			$c = cast(ClassType::NAME, $t);
			if( $c->is_exception ){
				$globals->error_throws_exception = $c;
			} else {
				$logger->error($where, "$c is not an exception");
			}
		} else {
			$logger->error($where, "$t: not an exception");
		}
	}
	
	 
	/**
	 * Parses the <code>pragma</code> meta-code statement.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$where = $scanner->here();
		# Retrieve all the arguments of the pragma:
		$scanner->readSym();
		
		$a = /*. (string[int]) .*/ array();
		while( $scanner->sym === Symbol::$sym_x_single_quoted_string ){
			$a[] = $scanner->s;
			$scanner->readSym();
		}
		
		$globals->expect(Symbol::$sym_x_semicolon, "expected `;'");
		$scanner->readSym();

		if( count($a) == 0 ){
			$logger->error($scanner->here(), "expected one or more pragma arguments in single quoted string");
			
		} else if( $a[0] === "autoload" ){
			self::parseAutoload($globals, $where, $a);

		} else if( $a[0] === "error_throws_exception" ){
			//Errors.ParseErrorThrowsExceptionPragma(where, a)
			// FIXME: to do
			self::parseErrorThrowsException($globals, $where, $a);

		} else {
			$logger->error($where, "unknown pragma: " . $a[0]);
		}
	}
	
}

