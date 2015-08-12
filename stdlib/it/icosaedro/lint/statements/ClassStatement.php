<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\docblock\DocBlockScanner;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use it\icosaedro\lint\types\ClassType;

/**
 * Parses class declaration.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/08/16 05:30:49 $
 */
class ClassStatement {

	/*. forward public static void function parse(
			Globals $globals, boolean $is_private); .*/


	/**
	 * Check implementation of iterators in PHP 5.
	 * @param Globals $globals
	 * @param ClassType $c
	 * @return void
	 */
	private static function checkTraversableUsage($globals, $c)
	{
		if (!$c->isSubclassOf($globals->builtin->TraversableClass))
			return;

		$b1 = $c->isSubclassOf($globals->builtin->IteratorClass);
		$b2 = $c->isSubclassOf($globals->builtin->IteratorAggregateClass);

		if ($b1 && $b2)
			$globals->logger->error($c->decl_in,
			"cannot implement both Iterator and IteratorAggregate");

		if ($b1 || $b2)
			// OK: Traversable with one of real implementations
			return;

		$globals->logger->error($c->decl_in, "classes that implements `Traversable' must also either implement `Iterator' or `IteratorAggregate'");
	}
	
		
	/**
	 * Parses class constant, property or method.
	 * @param Globals $globals
	 * @return void
	 */
	private static function parseMember($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$c = $pkg->curr_class;

		// Get DocBlock, or use default values from DocBlock wrapper:
		if ($scanner->sym === Symbol::$sym_x_docBlock) {
			if( $globals->parse_phpdoc ){
				$db = DocBlockScanner::parse($globals->logger, $scanner->here(), $scanner->s, $globals);
				$dbw = new DocBlockWrapper($globals->logger, $db, $globals->isPHP(4));
			} else {
				$dbw = new DocBlockWrapper($logger);
			}
			$scanner->readSym();
		} else {
			$dbw = new DocBlockWrapper($logger);
		}

		# Meta-code or DocBlock modifiers:
		$x_visibility = /*.(Visibility).*/ NULL;
		if ($dbw->isPrivate())
			$x_visibility = Visibility::$private_;
		else if ($dbw->isProtected())
			$x_visibility = Visibility::$protected_;
		else if ($dbw->isPublic())
			$x_visibility = Visibility::$public_;
		$x_abstract = $dbw->isAbstract();
		$x_final = $dbw->isFinal();
		$x_static = $dbw->isStatic();

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
					if ($x_visibility !== NULL)
						$logger->error($scanner->here(),
						"visibility modifier already set");
					$x_visibility = Visibility::$public_;
					break;

				case "sym_x_protected":
					if ($x_visibility !== NULL)
						$logger->error($scanner->here(),
						"visibility modifier already set");
					$x_visibility = Visibility::$protected_;
					break;

				case "sym_x_private":
					if ($x_visibility !== NULL)
						$logger->error($scanner->here(),
						"visibility modifier already set");
					$x_visibility = Visibility::$private_;
					break;

				case "sym_x_abstract":
					if ($x_abstract)
						$logger->error($scanner->here(),
						"abstract modifier already set");
					$x_abstract = TRUE;
					break;

				case "sym_x_final":
					if ($x_final)
						$logger->error($scanner->here(),
						"final modifier already set");
					$x_final = TRUE;
					break;

				case "sym_x_static":
					if ($x_static)
						$logger->error($scanner->here(),
						"static modifier already set");
					$x_static = TRUE;
					break;

				case "sym_abstract":
					if ($is_abstract)
						$logger->error($scanner->here(),
						"abstract modifier already set");
					$is_abstract = TRUE;
					break;

				case "sym_public":
					if ($visibility !== NULL)
						$logger->error($scanner->here(),
						"visibility modifier already set");
					$visibility = Visibility::$public_;
					break;

				case "sym_protected":
					if ($visibility !== NULL)
						$logger->error($scanner->here(),
						"visibility modifier already set");
					$visibility = Visibility::$protected_;
					break;

				case "sym_private":
					if ($visibility !== NULL)
						$logger->error($scanner->here(),
						"visibility modifier already set");
					$visibility = Visibility::$private_;
					break;

				case "sym_static":
					if ($is_static)
						$logger->error($scanner->here(),
						"static modifier already set");
					$is_static = TRUE;
					break;

				case "sym_final":
					if ($is_final)
						$logger->error($scanner->here(),
						"final modifier already set");
					$is_final = TRUE;
					break;

				default:
					$done = TRUE;
			}

			if ($done)
				break;
			else
				$scanner->readSym();
		} while (TRUE);

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

		if ($scanner->sym === Symbol::$sym_x_forward) {
			if ($dbw->getDocBlock() !== NULL)
				$logger->error($scanner->here(),
				"unexpected DocBlock for forward declaration");
			if ($x_visibility !== NULL || $x_abstract || $x_final || $x_static
					|| $is_abstract || $visibility !== NULL || $is_static || $is_final)
				$logger->error($scanner->here(),
				"unexpected modifiers for forward declaration");
			if ($t !== NULL)
				$logger->error($scanner->here(),
				"unexpected type for forward declaration");
			ForwardStatement::parse($globals);

			# Const (PHP5):
		} else if ($scanner->sym === Symbol::$sym_const) {
			$dbw->checkLineTagsForClassConstant();
			if ($globals->isPHP(4))
				$logger->error($scanner->here(),
				"invalid `const' declaration (PHP5)");
			if ($t !== NULL) {
				$logger->error($scanner->here(),
				"explicit type declaration not allowed for class constant");
			}
			if ($x_abstract || $x_final || $x_static
					|| $is_abstract || $visibility !== NULL || $is_static || $is_final)
				$logger->error($scanner->here(), "invalid modifiers. Only /*.public|protected|private.*/ is allowed for class constant.");
			if ($x_visibility === NULL)
				$x_visibility = Visibility::$public_;
			ClassConstantStatement::parse($globals, $dbw, $x_visibility);

			# Property (PHP4):
		} else if ($scanner->sym === Symbol::$sym_var) {
			$dbw->checkLineTagsForProperty();
			if ($globals->isPHP(4)) {
				if ($x_abstract || $x_final || $x_static
						|| $is_abstract || $visibility !== NULL || $is_static || $is_final) {
					$logger->error($scanner->here(), "invalid modifiers. Only /*.public|protected|private.*/ are allowed for a property.");
				}
			} else {
				$logger->error($scanner->here(), "invalid modifiers `var' (PHP 4), use `public'");
			}
			$scanner->readSym();
			if ($t === NULL)
				$t = TypeDecl::parse($globals, FALSE);
			$globals->expect(Symbol::$sym_variable,
			"expected property name \$xxx");
			if ($x_visibility !== NULL)
				$visibility = $x_visibility;
			if ($visibility === NULL)
				$visibility = Visibility::$public_;
			ClassPropertyStatement::parse($globals, $dbw, $visibility, $x_static || $is_static, $t);

			# Property (PHP5):
		} else if ($scanner->sym === Symbol::$sym_variable) {
			$dbw->checkLineTagsForProperty();
			if ($globals->isPHP(4)) {
				$logger->error($scanner->here(), "missing `var' before property name");
			} else {
				if ($x_visibility !== NULL || $x_abstract || $x_final || $x_static) {
					$logger->error($scanner->here(), "cannot use meta-code or DocBlock to set visibility|abstract|final modifiers, use proper language keywords");
				} else if ($is_abstract || $is_final) {
					$logger->error($scanner->here(), "properties cannot be abstract nor final");
				} else if ($visibility === NULL && !$is_static) {
					$logger->error($scanner->here(),
					"property requires visibility modifier or static modifier");
				}
			}
			ClassPropertyStatement::parse($globals, $dbw,
				$visibility === NULL? Visibility::$public_ : $visibility,
				$is_static, $t);

			# Method:
		} else if ($scanner->sym === Symbol::$sym_function) {
			$dbw->checkLineTagsForMethod();
			if ($globals->isPHP(4)) {
				if ($is_abstract || $visibility !== NULL || $is_static || $is_final) {
					$logger->error($scanner->here(), "invalid modifiers. Only /*.abstract public|protected|private final static.*/ are allowed for a method.");
				}
				if ($x_abstract && !$c->is_abstract) {
					$logger->error($scanner->here(), "abstract method in non-abstract class");
					$x_abstract = FALSE;
				}
				ClassMethodStatement::parse($globals, $dbw, $x_abstract, $x_visibility, $x_static, $x_final, $t);
			} else {
				if ($x_visibility !== NULL || $x_abstract || $x_static || $x_final) {
					$logger->error($scanner->here(), "invalid meta-code or DocBlock visibility|abstract|static|final modifier, use proper language keywords");
				}
				if ($is_abstract && !$c->is_abstract) {
					$logger->error($scanner->here(), "abstract method in non-abstract class");
					$is_abstract = FALSE;
				}
				ClassMethodStatement::parse($globals, $dbw, $is_abstract,
					$visibility === NULL? Visibility::$public_ : $visibility,
					$is_static, $is_final, $t);
			}
		} else {
			throw new ParseException($scanner->here(),
			"unexpected symbol " . $scanner->sym);
		}
		
	}

	/**
	 * Parses class declaration.
	 * @param Globals $globals Context of the parser.
	 * @param boolean $is_private Found the meta-code "private" modifier (PHP 4
	 * only).
	 * @return void
	 */
	public static function parse($globals, $is_private) {

		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;

		if ($pkg->scope > 0)
			$logger->warning($scanner->here(), "class declaration inside a function. The namespace of the classes is still global so the function cannot be called once more.");
		if ($pkg->curr_class !== NULL)
			throw new ParseException($scanner->here(), "nested classes are not allowed");

		$dbw = $pkg->curr_docblock;
		$dbw->checkLineTagsForClass();

		if ($is_private && $dbw->isPrivate())
			$logger->error($scanner->here(), "`private' modifier both in DocBlock and PHPLint meta-code");
		$is_private = $is_private || $dbw->isPrivate();

		$is_abstract = $dbw->isAbstract(); // PHP4 only may set this in DocBlock
		$is_unchecked = FALSE;
		$is_final = $dbw->isFinal(); // PHP4 only may set this in DocBlock
		// Collects abstract, final and unchecked modifiers from PHP5 and
		// meta-code:
		do {
			if ($scanner->sym === Symbol::$sym_x_abstract) {
				if ($globals->isPHP(5))
					$logger->error($scanner->here(), "invalid `/*.abstract.*/' modifier, use `abstract' instead");
				if ($is_abstract)
					$logger->notice($scanner->here(), "multiple `abstract' modifiers");
				$is_abstract = TRUE;
				$scanner->readSym();
			} else if ($scanner->sym === Symbol::$sym_x_final) {
				if ($globals->isPHP(5))
					$logger->error($scanner->here(), "invalid `/*.final.*/' modifier, use `final' instead");
				if ($is_final) {
					$logger->notice($scanner->here(), "multiple `final' modifiers");
				}
				$is_final = TRUE;
				$scanner->readSym();
			} else if ($scanner->sym === Symbol::$sym_abstract) {
				if ($globals->isPHP(4))
					$logger->error($scanner->here(), "invalid `abstract' modifier, use `/*.abstract.*/' instead");
				if ($is_abstract)
					$logger->notice($scanner->here(), "multiple `abstract' modifiers");
				$is_abstract = TRUE;
				$scanner->readSym();
			} else if ($scanner->sym === Symbol::$sym_final) {
				if ($globals->isPHP(4))
					$logger->error($scanner->here(), "invalid `final' modifier, use `/*.final.*/' instead");
				if ($is_final) {
					$logger->notice($scanner->here(), "multiple `final' modifiers");
				}
				$is_final = TRUE;
				$scanner->readSym();
			} else if ($scanner->sym === Symbol::$sym_x_unchecked) {
				if ($globals->isPHP(4)) {
					$logger->error($scanner->here(), "invalid `/*.unchecked.*/' modifier (PHP 5)");
				}
				if ($is_unchecked)
					$logger->notice($scanner->here(), "multiple `unchecked' modifiers");
				$is_unchecked = TRUE;
				$scanner->readSym();
			} else {
				break;
			}
		} while (TRUE);

		if ($is_final && $is_abstract) {
			$logger->error($scanner->here(), "a class cannot be both final and abstract");
			$is_final = FALSE; // keeps consistency
		}

		$globals->expect(Symbol::$sym_class, "expected `class'");
		$scanner->readSym();

		// Class name:
		$globals->expect(Symbol::$sym_identifier, "expected class name");
		$here = $scanner->here();
		$s = $scanner->s;
		if (!NamespaceResolver::isIdentifier($s))
			throw new ParseException($here, "class name must be a simple identifier");
		$s = $pkg->resolver->absolute($s);
		$fqn = new FullyQualifiedName($s, FALSE);

		// Check proto or re-definition of the same class:
		$proto = $globals->getClass($fqn);
		if( $proto !== NULL ){
			if( ! $proto->is_forward ){
				$logger->error($here, "class " . $proto->name
				. " already declared in "
				. $logger->reference($here, $proto->decl_in));
				$proto = NULL;
			}
		}

		$c = new ClassType($fqn, $here);
		$c->decl_in = $here;
		$c->is_unchecked = $is_unchecked;
		$c->is_private = $is_private;
		$c->is_final = $is_final;
		$c->is_abstract = $is_abstract;
		if( ! $globals->report_unused )
			$c->used = 100;
		$c->docblock = $dbw->getDocBlock();
		$dbw->clear();
		

//	private static function CheckForwardAttribute($c: Class,
//		a_attribute: BOOLEAN, b_attribute: BOOLEAN, modifier: STRING)
//	/*
//		Check if a class modifier of the actual implementation matches the
//		same modifier as set in the forward declaration.
//		Parameters:
//		$c: forward class.
//		a_attribute: modifier in the forward declaration.
//		b_attribute: modifier in the actual declaration.
//		modifier: name of the modifier.
//	*/
//	{
//		if( a_attribute && b_attribute
//		|| ! a_attribute && ! b_attribute ){
//			return
//		}
//		$logger->error($scanner->here(), "class " . $c->name . ": modifier `" . modifier
//		. "' does not match the forward declaration in "
//		. $c->decl_in->reference($scanner->here()));
//	}

//	/*
//		Check collision with classes imported with `use' from another
//		NS (collisions with classes declared in the current NS are
//		detected next). Note that we can't use SearchClass() here
//		because it might trigger class autoloading.
//	*/
//	
//	colliding = SearchClassByAbsName(Namespace.ApplyUse($scanner->s, TRUE), FALSE);
//	if( (colliding <> NULL) && (colliding->name_lower <> class_name_lower) ){
//		$logger->error($scanner->here(), "cannot declare " . class_name
//		. " because the name is already accessible as " . colliding->name
//		. " declared in " . colliding->decl_in->reference($scanner->here()));
//	}
//
//	/*
//		Check if we are implementing a forward declaration.
//	*/
//
//	forward = SearchClassByAbsName(class_name, FALSE);
//	if( forward = NULL ){ /* new class - store it: */
//		$class_->name = class_name
//		$class_->name_lower = class_name_lower
//		$class_->type = {object, void, NULL, class}
//		classes[] = class
//
//	} else if( forward->forward ){  /* found actual decl. of a forward class */
//		CheckForwardAttribute(forward, forward->private, $class_->private, "private");
//		CheckForwardAttribute(forward, forward->is_abstract, $class_->is_abstract, "$is_abstract");
//		CheckForwardAttribute(forward, forward->is_final, $class_->is_final, "final");
//		CheckForwardAttribute(forward, forward->unchecked, $class_->unchecked, "unchecked");
//
//		###forward->decl_in = $class_->decl_in
//		should_implement = forward->implements
//		forward->implements = NULL
//		forward->used = $class_->used
//		forward->forward = FALSE
//		class = forward
//
//	} else {
//		$logger->error($scanner->here(), "class `" . class_name . "' already declared in "
//			. forward->decl_in->reference($scanner->here()));
//	}


		$scanner->readSym(); // skip class name
		
		/*
		 * Abstract and concrete classes extend object. This is true only
		 * inside PHPLint: the test "$v instanceof object"
		 * always fails; use is_class($v) to test if $v is an object.
		 */
		$c->extended = ClassType::getObject();
		
		/*
			Extends?
		*/
		if( $scanner->sym === Symbol::$sym_extends ){
			$scanner->readSym();
			$globals->expect(Symbol::$sym_identifier,
			"expected parent class name after `extends'");
			$parent_ = $globals->searchClass($scanner->s);
			if( $parent_ === NULL ){
				$logger->error($scanner->here(),
				"undeclared parent class " . $scanner->s);
			} else if( $parent_->isSubclassOf($c) ){
				$logger->error($scanner->here(),
				"class $c cannot extend child class $parent_: forbidden circular reference");
			} else if( $parent_->is_final ){
				$logger->error($scanner->here(),
				"cannot extend final class $parent_");
			} else if( $parent_->is_interface ){
				$logger->error($scanner->here(),
				"cannot extend interface class $parent_");
			} else {
				if( $proto !== NULL
				&& $proto->extended !== NULL
				&& $proto->extended !== $parent_
				&& ! $parent_->isSubclassOf($proto->extended)
				){
					$logger->error($scanner->here(),
					"$parent_ is not subclass of "
					. $proto->extended
					. " according to the forward declaration in "
					. $logger->reference($scanner->here(), $proto->decl_in));
				}
				$c->extended = $parent_;
				$globals->accountClass($parent_);
				$c->is_exception = $parent_->is_exception;
			}
			$scanner->readSym();
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
			&& $c->extended !== NULL
			&& $c->extended->is_unchecked ){
				$logger->error($scanner->here(),
				"missing `unchecked' modifier for exception extending uncheked exception");
				$c->is_unchecked = TRUE;
			}
			
		}

		// Implements?
		if( $scanner->sym === Symbol::$sym_implements ){
			if( $globals->isPHP(4) )
				$logger->error($scanner->here(),
				"no interface classes (PHP 5)");
			$scanner->readSym();
			do {
				$globals->expect(Symbol::$sym_identifier,
				"expected interface name");
				$s = $scanner->s;
				$iface = $globals->searchClass($s);
				if( $iface === NULL ){
					$logger->error($scanner->here(),
					"undeclared interface class `$s'");
				} else if( ! $iface->is_interface ){
					$logger->error($scanner->here(),
					"the class $iface isn't an interface");
				} else if( $iface === $globals->builtin->TraversableClass
				&& ! $pkg->is_module ){
					// Traversable can be implemented only in modules, not in
					// user's code:
					$logger->error($scanner->here(),
					"cannot implement abstract interface Traversable, use Iterator or IteratorAggregate instead");
				} else if( $c->isSubclassOf($iface) ){
					$logger->notice($scanner->here(),
					"class $c redundantly implements $iface -- ignoring");
				} else {
					$err = ClassInheritance::addInterfaceToClass($c, $iface);
					if( $err !== "" )
						$logger->error($scanner->here(), $err);
					$globals->accountClass($iface);
				}
				$scanner->readSym();
				if( $scanner->sym === Symbol::$sym_comma ){
					$scanner->readSym();
				} else {
					break;
				}
			} while(TRUE);
		}
		
		// FIXME: to do
//	FOR i=0 TO count(should_implement)-1 DO
//		if = should_implement->i
//		if( ! IsSubclassOf(class, if) ){
//			$logger->error($scanner->here(), "class " . $class_->name . " must implement " . if->name
//			. " according to the forward declaration");
//		}
//	}
//
		
		if( $proto === NULL ){
			$globals->classes->put($c->name, $c);
			
		} else {
			if( ! $c->extendsPrototype($proto) )
				$logger->error($c->decl_in, "declaration of class $c as\n"
				. $c->prototype()
				. "\ndoes not match the forward declaration in "
				. $logger->reference($c->decl_in, $proto->decl_in) . " as\n"
				. $proto->prototype());
			$proto->is_forward = FALSE;
			$proto->decl_in = $c->decl_in;
			$proto->docblock = $c->docblock;
			if( $c->extended->isSubclassOf($proto) ){
				$logger->error($c->decl_in,
				"$c: detected circular reference with " . $c->extended);
				$c->extended = ClassType::getObject();
				// FIXME: check circular references also in interfaces
			}
			$proto->extended = $c->extended;
			$proto->implemented = $c->implemented;
			$c = $proto;
		}
		
		$pkg->curr_class = $c;
		
		$globals->builtin->detect($c);
		
		self::checkTraversableUsage($globals, $c);
		
		$colliding = ClassInheritance::checkCollidingConstants($c);
		if( $colliding !== "" )
			$logger->error($c->decl_in,
			"colliding inherited constants:$colliding");
		
		ClassInheritance::checkIncompatibleInheritedMethods($c);

		$globals->expect(Symbol::$sym_lbrace, "expected '{' in class declaration");
		$scanner->readSym();

		do {

			if ($scanner->sym === Symbol::$sym_rbrace) {
				$scanner->readSym();
				break;
			}

			self::parseMember($globals);

		} while (TRUE);

//	if( ! $class_->is_abstract ){
//		CheckImplementedMethods(class);
//	}
// FIXME: check all the forward props and methods be implemented
//	FOR i = 0 TO count($class_->methods)-1 DO
//		$m = $class_->methods->i
//		if( $m->forward ){
//			Error2(here(), "missing method `" . $m->name
//				. "()' declared forward in " . $m->decl_in->reference($scanner->here()));
//		}
//	}
//
//	Template.MangleNamesOfSurrogateClasses(class, surrogates);

		$pkg->curr_class = NULL;

		$missing = ClassInheritance::missingImplementations($c);
		if( $missing !== "" )
			$logger->error($c->decl_in, "missing implementations in $c:$missing");
		
	}

}

