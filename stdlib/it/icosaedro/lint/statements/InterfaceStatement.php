<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\docblock\DocBlockScanner;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use it\icosaedro\lint\ParseException;

/**
 * Parses an interface declaration.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/08/16 05:31:06 $
 */
class InterfaceStatement {
	
	/*. forward public static void function parse(
			Globals $globals, boolean $is_private); .*/
	

	/**
	 * Parses member of interface: constant or method.
	 * @param Globals $globals
	 * @return void
	 */
	private static function parseMember($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$c = $pkg->curr_class;


		$dbw = new DocBlockWrapper($logger);
		if ($scanner->sym === Symbol::$sym_x_docBlock) {
			if( $globals->parse_phpdoc ){
				$db = DocBlockScanner::parse($logger, $scanner->here(), $scanner->s, $globals);
				$dbw = new DocBlockWrapper($logger, $db, $globals->isPHP(4));
			}
			$scanner->readSym();
		}

		if ($scanner->sym === Symbol::$sym_const) {
			// Only "@access public" allowed, although useless:
			$dbw->checkLineTagsForClassConstant();
			if( $dbw->isPrivate() )
				$logger->error($scanner->here(), "invalid `private' attribute");
			ClassConstantStatement::parse($globals, $dbw, Visibility::$public_);
			
		} else {
			// Attributes (only `public' and `static' allowed):
			$dbw->checkLineTagsForMethod();
			$is_static = FALSE;
			while (TRUE) {
				if ($scanner->sym === Symbol::$sym_public) {
					$scanner->readSym();
				} else if ($scanner->sym === Symbol::$sym_static) {
					$is_static = TRUE;
					$scanner->readSym();
				} else {
					break;
				}
			}

			# Parses type:
			$t = TypeDecl::parse($globals, FALSE);
			$t2 = $dbw->getVarType();
			if ($t2 === NULL)
				$t2 = $dbw->getReturnType();
			if ($t !== NULL && $t2 !== NULL) {
				$logger->error($scanner->here(),
				"type declaration both in DocBlock and PHPLint meta-code");
			}
			if ($t === NULL)
				$t = $t2;

			if ($scanner->sym === Symbol::$sym_variable)
				throw new ParseException($scanner->here(),
				"properties cannot be defined in interfaces");

			$globals->expect(Symbol::$sym_function,
			"expected `function' declaration in interface class");
			ClassMethodStatement::parse($globals, $dbw, TRUE, Visibility::$public_, $is_static, FALSE, $t);
		}
	}
	

	/**
	 * Parses an interface declaration.
	 * @param Globals $globals
	 * @param boolean $is_private
	 * @return void
	 */
	public static function parse($globals, $is_private)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		if ($pkg->scope > 0) {
			$logger->warning($scanner->here(), "class declaration inside a function. The namespace of the classes is still global so the function cannot be called once again.");
		}
		if( $globals->isPHP(4) )
			$logger->error($scanner->here(), "invalid keyword `interface' (PHP 5)");
		
		if ($pkg->curr_class !== NULL)
			throw new ParseException($scanner->here(), "nested classes are not allowed");

		$dbw = $pkg->curr_docblock;
		$dbw->checkLineTagsForClass();

		if ($is_private && $dbw->isPrivate())
			$logger->error($scanner->here(), "`private' modifier both in DocBlock and PHPLint meta-code");
		$is_private = $is_private || $dbw->isPrivate();
		
		$scanner->readSym(); // skip 'interface'

		// Interface name:
		$globals->expect(Symbol::$sym_identifier, "expected interface name");
		$here = $scanner->here();
		$s = $scanner->s;
		if (!NamespaceResolver::isIdentifier($s))
			throw new ParseException($here, "interface name must be a simple identifier");
		$s = $pkg->resolver->absolute($s);
		$fqn = new FullyQualifiedName($s, FALSE);

		// Check proto or re-definition of the same interface:
		$proto = $globals->getClass($fqn);
		if ($proto !== NULL) {
			if( $proto->is_forward && $proto->is_interface ) {
				if( $proto->is_private !== $is_private ) {
					$logger->error($here, "interface $s: attribute `private' does not match the forward declaration in "
					. $logger->reference($here, $proto->decl_in));
				}
				$c = $proto;
				$c->is_forward = FALSE;
			} else {
				$logger->error($scanner->here(), "class $s already declared in "
				. $logger->reference($here, $proto->decl_in));
				$proto = NULL;
			}
		}
		
		// Create new interface object:
		if( $proto === NULL ){
			$c = new ClassType($fqn, $here);
			$globals->classes->put($c->name, $c);
			$globals->builtin->detect($c);
		} else {
			$c = $proto;
			$c->decl_in = $here;
		}
		$c->is_interface = TRUE;
		$c->is_unchecked = FALSE;
		$c->is_private = $is_private;
		$c->is_final = FALSE;
		$c->is_abstract = FALSE;
		if( ! $globals->report_unused )
			$c->used = 100;
		$c->docblock = $dbw->getDocBlock();
		$dbw->clear();
		
		$globals->classes->put($c->name, $c);

		$pkg->curr_class = $c;
		
		$scanner->readSym(); // skip name

		// Extends?
		if ($scanner->sym === Symbol::$sym_extends) {
			$scanner->readSym();
			while (TRUE) {
				$globals->expect(Symbol::$sym_identifier,
				"expected interface class name");
				$s = $scanner->s;
				$iface = $globals->searchClass($s);
				if( $iface === NULL ) {
					$logger->error($scanner->here(),
					"undeclared interface class $s");
				} else if( $iface->isSubclassOf($c) ) {
					$logger->error($scanner->here(),
					"interface $c cannot extend interface $iface: forbidden circular reference");
				} else if( ! $iface->is_interface ) {
					$logger->error($scanner->here(),
					"interface cannot extend non-interface class $iface");
				} else if( $c->isSubclassOf($iface) ) {
					$logger->notice($scanner->here(),
					"interface $c redundantly extends interface $iface -- ignoring");
				} else {
					$err = ClassInheritance::addInterfaceToClass($c, $iface);
					if( $err !== "" )
						$logger->error($scanner->here(), $err);
					$globals->accountClass($iface);
				}
				$scanner->readSym();
				if ($scanner->sym === Symbol::$sym_comma) {
					$scanner->readSym();
				} else {
					break;
				}
			}
		}
		
		$err = ClassInheritance::checkCollidingConstants($c);
		if( $err !== "" )
			$logger->error($c->decl_in,
			"colliding inherited constants:$err");
		
		$err = ClassInheritance::checkIncompatibleInheritedMethods($c);
		if( $err !== "" )
			$logger->error($c->decl_in,
			"incompatible inherited methods:$err");

		$globals->expect(Symbol::$sym_lbrace, "expected '{' in class declaration");
		$scanner->readSym();

		do {

			if ($scanner->sym === Symbol::$sym_rbrace) {
				$scanner->readSym();
				break;
			}

			self::parseMember($globals);

		} while (TRUE);

		$pkg->curr_class = NULL;

// FIXME: check all the forward methods be implemented

		$pkg->curr_class = NULL;
		
	}

}