<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use InvalidArgumentException;
use it\icosaedro\io\File;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\Thinkphp;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:45:10 $
 */
class UseStatement {
	
	/**
	 * Parse "use TARGET [ as ALIAS];".
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals){
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if( $globals->isPHP(4) ){
			throw new ParseException($scanner->here(), "`use' not available (PHP 5)");
		}
		$scanner->readSym();
		while(TRUE){
			/* Parse target: */
			$globals->expect(Symbol::$sym_identifier, "expected namespace name");
			$target = $scanner->s;
			if( $pkg->resolver->inNamespace() ){
				if( NamespaceResolver::isAbsolute($target) ){
					$globals->logger->notice($scanner->here(),
						"useless leading `\\' in path namespace: path namespaces are always absolute");
					$target = substr($target, 1);
				}
			} else {
				if( NamespaceResolver::isAbsolute($target) ){
					$target = substr($target, 1);
					die($target);
				} else if( ! NamespaceResolver::isQualified($target) ){
					$globals->logger->error($scanner->here(),
						"the use statement with non-compound name '$target' has no effect."
						. " This is what PHP would write to stderr for unqualified,"
						. " non-absolute names, I don't understand why. Fix: either add"
						. " a leading back-slash, or simply remove this statement"
						. " if it does not define an alias name.");
				}
			}
			$scanner->readSym();

			/* Parse alias: */
			if( $scanner->sym === Symbol::$sym_as ){
				$scanner->readSym();
				$globals->expect(Symbol::$sym_identifier, "expected identifier");
				$alias = $scanner->s;
				if( ! NamespaceResolver::isIdentifier($alias) ){
					$globals->logger->error($scanner->here(), "expected identifier, found $alias");
					$alias = NULL; // recover
				}
				$scanner->readSym();
			} else {
				$alias = NULL;
			}
			$pkg->resolver->addUse($target, $alias, $scanner->here());

			if( $scanner->sym === Symbol::$sym_comma ){
				$scanner->readSym();
			} else {
				break;
			}
		}
		$globals->expect(Symbol::$sym_semicolon, "expected `;'");
		$scanner->readSym();
	}
	
}

