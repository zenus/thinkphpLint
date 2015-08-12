<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\io\File;
use InvalidArgumentException;

/**
 * Parses the require_once 'PACKAGE'; statement. Normally this statement is
 * allowed only at scope level 0, but we must make an exception for the magic
 * function __autoload().
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 21:23:35 $
 */
class RequireOnceStatement {

	/**
	 * Parses the require_once 'PACKAGE'; statement.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals) {
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$autoload = $pkg->curr_func === $globals->autoload_function;
		if (!$autoload && $pkg->scope > 0)
			$globals->logger->error($scanner->here(),
			"`require_once' allowed only in global scope");
		$scanner->readSym();
		$r = Expression::parse($globals);
		$r->checkExpectedType($globals->logger, $scanner->here(), Globals::$string_type);
		if (!$r->isString()) {
			# bad expr - ignore statement
			
		} else if ($r->getValue() === NULL) {
			if ($autoload) {
				// autoload func can use variables - pass
			} else {
				$globals->logger->error($scanner->here(),
				"require_once: can't check file name, value undetermined");
			}
			
		} else if (strlen($r->getValue()) == 0) {
			$globals->logger->error($scanner->here(),
			"require_once: empty file name");
			
		} else if (!$globals->recursive_parsing && $globals->recursion_level == 1) {
			$globals->logger->error($scanner->here(),
			"cannot load package: recursive parsing disabled by --no-recursive option");
			
		} else {

			try {
				$fn = File::fromLocaleEncoded($r->getValue());
			} catch (InvalidArgumentException $e) {
				$globals->logger->error($scanner->here(),
				"invalid file \"" . $r->getValue() . "\": "
				. $e->getMessage() . "\nHint: expected absolute path (PHPLint safety restriction); under PHP5 the magic constant __DIR__ gives the directory of the current source.");
				$fn = NULL;
			}
			if( $fn != NULL ){
				if($globals->logger->main_file_name == $globals->logger->current_file_name){
					$globals->loadPackage($fn, FALSE);
					$pkg = $globals->getPackage($fn);
					if( $pkg !== NULL && ! $pkg->is_library ){
						$globals->logger->error($scanner->here(),
							"package " . $globals->logger->formatFileName($fn)
							." is not a library:\n" . $pkg->why_not_library);
					}
				}
			}
		}
	}

}
