<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\ParseException;

/**
 * Parses a "forward" class statement.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class ForwardClassStatement {
	
	
	/**
	 * Parses a member of a forward class. Currently supported:
	 * constant "const X =;" without value and method prototype with
	 * default arguments omitted: "public void m($x=);".
	 * @param Globals $globals 
	 * @return void
	 */
	private static function parseMember($globals)
	{
		$pkg = $globals->curr_pkg;
		$c = $pkg->curr_class;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;
		
		if ($scanner->sym === Symbol::$sym_x_const) {
			ForwardClassConstantStatement::parse($globals);

		} else {
			// Method.
			# PHP5 modifiers:
			$visibility = /*.(Visibility).*/ NULL;
			$is_abstract = FALSE;
			$is_static = FALSE;
			$is_final = FALSE;

			// Loops over modifiers of this member:
			$done = FALSE;
			do {

				switch ($scanner->sym->__toString()) {

					case "sym_x_public":
						if ($visibility !== NULL)
							$logger->error($scanner->here(),
							"visibility modifier already set");
						$visibility = Visibility::$public_;
						break;

					case "sym_x_protected":
						if ($visibility !== NULL)
							$logger->error($scanner->here(),
							"visibility modifier already set");
						$visibility = Visibility::$protected_;
						break;

					case "sym_x_private":
						if ($visibility !== NULL)
							$logger->error($scanner->here(),
							"visibility modifier already set");
						$visibility = Visibility::$private_;
						break;

					case "sym_x_abstract":
						if ($is_abstract)
							$logger->error($scanner->here(),
							"abstract modifier already set");
						$is_abstract = TRUE;
						break;

					case "sym_x_final":
						if ($is_final)
							$logger->error($scanner->here(),
							"final modifier already set");
						$is_final = TRUE;
						break;

					case "sym_x_static":
						if ($is_static)
							$logger->error($scanner->here(),
							"static modifier already set");
						$is_static = TRUE;
						break;

					default:
						$done = TRUE;
				}

				if ($done)
					break;
				else
					$scanner->readSym();
			} while (TRUE);
			
			$t = TypeDecl::parse($globals, FALSE);
			if( $t === NULL )
				$logger->error($scanner->here(),
				"missing return type of method");
			
			if ($globals->isPHP(4)) {
				if ($is_abstract || $visibility !== NULL || $is_static || $is_final) {
					$logger->error($scanner->here(),
					"invalid modifiers. Only abstract public|protected|private, final, static are allowed for a method.");
				}
				if ($is_abstract && !$c->is_abstract) {
					$logger->error($scanner->here(),
					"abstract method in non-abstract class");
					$is_abstract = FALSE;
				}
				ForwardMethodStatement::parse($globals,
					$visibility === NULL? Visibility::$public_ : $visibility,
					$is_abstract, $is_final, $is_static, $t);
			} else {
				if ($is_abstract && !$c->is_abstract) {
					$logger->error($scanner->here(),
					"abstract method in non-abstract class");
					$is_abstract = FALSE;
				}
				ForwardMethodStatement::parse($globals,
					$visibility === NULL? Visibility::$public_ : $visibility,
					$is_abstract, $is_final, $is_static, $t);
			}
		}
	}

	/**
	 * @param Globals $globals
	 * @param boolean $is_private
	 * @param boolean $is_abstract
	 * @param boolean $is_final
	 * @param boolean $is_unchecked
	 * @return void
	 */
	public static function parse($globals, $is_private, $is_abstract, $is_final, $is_unchecked)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;

		if ($is_final && $is_abstract) {
			$logger->error($scanner->here(), "a class cannot be both final and abstract");
			$is_final = FALSE; // keeps consistency
		}

		$scanner->readSym();
		$globals->expect(Symbol::$sym_x_identifier, "expected class name");
		$here = $scanner->here();
		$s = $scanner->s;
		if (!NamespaceResolver::isIdentifier($s))
			throw new ParseException($here, "class name must be a simple identifier");
		$s = $pkg->resolver->absolute($s);
		$fqn = new FullyQualifiedName($s, FALSE);

		// Check proto or re-definition of the same class:
		$c = $globals->getClass($fqn);
		if ($c !== NULL) {
			if (!$c->is_forward)
				throw new ParseException($here, "class " . $c->name . " already declared in "
				. $logger->reference($here, $c->decl_in));
			$globals->classes->remove($fqn);
		}
		
		if ($c !== NULL) {
			#if( ! $c->forward ){
			$logger->error($scanner->here(), "class $fqn already declared in "
			. $logger->reference($here, $c->decl_in));
			#}
		}

		// Create new class object:
		$c = new ClassType($fqn, $here);
		$c->is_forward = TRUE;
		$c->is_unchecked = $is_unchecked;
		$c->is_private = $is_private;
		$c->is_final = $is_final;
		$c->is_abstract = $is_abstract;
		$globals->classes->put($c->name, $c);
		$globals->builtin->detect($c);
		
		$scanner->readSym(); // skip name
			
		/*
		* Abstract and concrete classes extend object. This is true only
		* inside PHPLint: the test "$v instanceof object"
		* always fails; use is_class($v) to test if $v is an object.
		*/
		$c->extended = ClassType::getObject();

		// Extends?
		if ($scanner->sym === Symbol::$sym_x_extends) {
			$scanner->readSym();
			$globals->expect(Symbol::$sym_x_identifier,
			"expected parent class name after `extends'");
			$parent_ = $globals->searchClass($scanner->s);
			if ($parent_ === NULL) {
				$logger->error($scanner->here(),
				"undeclared parent class " . $scanner->s);
			} else if( $parent_->isSubclassOf($c) ){
				$logger->error($scanner->here(),
				"class $c cannot extend child class $parent_: forbidden circular reference");
			} else if ($parent_->is_final) {
				$logger->error($scanner->here(),
				"cannot extend final class $parent_");
			} else
				if( $parent_->is_interface ){
				$logger->error($scanner->here(),
				"cannot extend interface class $parent_");
			} else {
				$c->extended = $parent_;
				$globals->accountClass($parent_);
				$c->is_exception = $parent_->is_exception;
			}
			$scanner->readSym(); // skip extended class name
		}

		// Check "unchecked" modifier:
		if( $c->is_unchecked ){
			if( ! $c->is_exception ){
				$logger->error($scanner->here(),
				"invalid `unchecked' modifier for non-exception class");
				$c->is_unchecked = FALSE;
			}

		} else {
			if( $c->is_exception
			&& $c->extended->is_unchecked ){
				$logger->error($scanner->here(),
				"missing `unchecked' modifier for exception extending uncheked exception");
				$c->is_unchecked = TRUE;
			}

		}

		// Implements?
		if( $scanner->sym === Symbol::$sym_x_implements ){
			if( $globals->isPHP(4) )
				$logger->error($scanner->here(),
				"no interface classes (PHP 5)");
			$scanner->readSym();
			do {
				$globals->expect(Symbol::$sym_x_identifier,
				"expected interface name");
				$s = $scanner->s;
				$interf = $globals->searchClass($s);
				if( $interf === NULL ){
					$logger->error($scanner->here(),
					"undeclared interface class `$s'");
				} else if( ! $interf->is_interface ){
					$logger->error($scanner->here(),
					"the class $interf isn't an interface");
				} else if( $interf === $globals->builtin->TraversableClass
				&& ! $pkg->is_module ){
					// Traversable can be implemented only in modules, not in
					// user's code:
					$logger->error($scanner->here(),
					"cannot implement abstract interface Traversable, use Iterator or IteratorAggregate instead");
				} else if( $c->isSubclassOf($interf) ){
					$logger->notice($scanner->here(),
					"class $c redundantly implements $interf -- ignoring");
				} else {
					$c->implemented[] = $interf;
					$globals->accountClass($interf);
				}
				$scanner->readSym();
				if( $scanner->sym === Symbol::$sym_x_comma ){
					$scanner->readSym();
				} else {
					break;
				}
			} while(TRUE);
		}

		$globals->expect(Symbol::$sym_x_lbrace, "expected `{' in class prototype");
		$scanner->readSym();

		$pkg->curr_class = $c;
		while ($scanner->sym !== Symbol::$sym_x_rbrace) {
			self::parseMember($globals);
		}
		$pkg->curr_class = NULL;

		$globals->expect(Symbol::$sym_x_rbrace, "expected `}' in class prototype");
		$scanner->readSym();
	}

}
