<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\types\ClassType;

/**
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class ForwardInterfaceStatement {

	/**
	 * @param Globals $globals
	 * @param boolean $is_private
	 * @return void
	 */
	public static function parse($globals, $is_private)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();

		// Class name:
		$globals->expect(Symbol::$sym_x_identifier, "expected class name");
		$here = $scanner->here();
		$s = $scanner->s;
		if (!NamespaceResolver::isIdentifier($s))
			throw new ParseException($here, "class name must be a simple identifier");
		$s = $pkg->resolver->absolute($s);
		$fqn = new FullyQualifiedName($s, FALSE);

		// Check proto or re-definition of the same class:
		$proto = $globals->getClass($fqn);
		if ($proto !== NULL) {
			//FIXME: to do
//			if (!$proto->is_forward || !$proto->is_interface)
			$globals->logger->error($here, "class " . $proto->name . " already declared in "
			. $globals->logger->reference($here, $proto->decl_in));
			// FIXME: handle class proto
			// FIXME: check it is an interface and not a class
			// check visibility
			// check unchecked attribute
			// check abstract
		}

		// Create new class object:
		if ($proto === NULL) {
			$c = new ClassType($fqn, $here);
			$globals->classes->put($c->name, $c);
		} else {
			$c = $proto;
		}
		$c->is_forward = TRUE;
		$c->decl_in = $here;
		$c->is_abstract = TRUE;
		$c->is_interface = TRUE;
//		$c->is_unchecked = $is_unchecked;
		$c->is_private = $is_private;
//		$c->is_final = $is_final;
//		$c->is_abstract = $is_abstract;
		if (!$globals->report_unused)
			$c->used = 100;

		$scanner->readSym();

		if ($scanner->sym === Symbol::$sym_x_extends)
		# FIXME: to do
			throw new ParseException($scanner->here(), "`extends' keyword still unimplemented in interface prototype, sorry");

		$globals->expect(Symbol::$sym_x_lbrace, "expected '{' in interface prototype");
		$scanner->readSym();

		$pkg->curr_class = $c;
		while ($scanner->sym !== Symbol::$sym_x_rbrace) {
			if ($scanner->sym === Symbol::$sym_x_const) {
				ForwardClassConstantStatement::parse($globals);
				
			} else {
				$is_static = FALSE;
				do {
					if( $scanner->sym === Symbol::$sym_x_public ){
						$scanner->readSym();
					} else if( $scanner->sym === Symbol::$sym_static ){
						$is_static = TRUE;
						$scanner->readSym();
					} else {
						break;
					}
				} while(TRUE);
				$t = TypeDecl::parse($globals, FALSE);
				if( $t === NULL )
					throw new ParseException($scanner->here(), "missing mandatory return type in method prototype");
				ForwardMethodStatement::parse($globals, Visibility::$public_, TRUE, FALSE, $is_static, $t);
			}
		}
		$pkg->curr_class = NULL;

		$globals->expect(Symbol::$sym_x_rbrace, "expected `}' in interface prototype");
		$scanner->readSym();
	}

}
