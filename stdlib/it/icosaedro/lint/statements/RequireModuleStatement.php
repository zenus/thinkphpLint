<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Where;
use it\icosaedro\regex\Pattern;
use it\icosaedro\io\IOException;
use it\icosaedro\io\File;
use InvalidArgumentException;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class RequireModuleStatement {
	
	/**
	 * Resolves the module name into a file searching in the list of modules
	 * directories.
	 * @param Globals $globals
	 * @param Where $where
	 * @param string $m
	 * @return File
	 */
	private static function resolveModule($globals, $where, $m)
	{
		$m = "$m.php";
		foreach($globals->modules_dirs as $d){
			try {
				$fn = File::fromLocaleEncoded($m, $d);
				if( $fn->exists() )
					return $fn;
			}
			catch(InvalidArgumentException $e){
				$globals->logger->error($where, "$d/$m: " . $e->getMessage());
			}
			catch(IOException $e){
				$globals->logger->error($where, "$d/$m: " . $e->getMessage());
			}
		}
		return NULL;
	}
	 
	/**
	 * Parses the <code>/&#42;.&nbsp;require_module 'MODULE';&nbsp;.&#42;/</code>
	 * statement.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if( $pkg->scope > 0 )
			$globals->logger->error($scanner->here(), "found `require_module' in scope level > 0");
		$scanner->readSym();
		$globals->expect(Symbol::$sym_x_single_quoted_string, "expected single quoted string after `require_module'");
		$m = $scanner->s;
		$here = $scanner->here();
		if( ! Pattern::matches("{a-zA-Z0-9_}+\$", $m) ){
			$globals->logger->error($here, "require_module '$m': module name contains invalid characters. Hint: only letters, digits and underscore allowed; path not allowed.");
		} else {
			$fn = self::resolveModule($globals, $here, $m);
			if( $fn === NULL )
				$globals->logger->error($here, "$m: module file not found");
			else
				$globals->loadPackage($fn, TRUE);
		}
		$scanner->readSym();
		$globals->expect(Symbol::$sym_x_semicolon, "missing `;'");
		$scanner->readSym();
	}
	
}

