<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\CaseInsensitiveString;
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\GuessType;
use it\icosaedro\lint\types\VoidType;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\ParseException;

/**
 * Parses a class method or interface method.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/04 12:11:23 $
 */
class ClassMethodStatement {

//VAR
//	specialMethods: ARRAY OF RECORD
//		name, name_lower, sign: STRING
//		php4: BOOLEAN
//		not_supported: BOOLEAN
//	}
//private static function CheckSpecialMethodSignature($m: Method)
////VAR i: INTEGER  sign: STRING
//{
//	if( (length($m->name) <= 2) || ($m->name[0,2] <> "__") ){
//		return
//	}
//
//	if( specialMethods = NULL ){
//	specialMethods = {
//
//#{ "__construct",  "__construct",  "public void()", FALSE, FALSE },
//{ "__destruct",   "__destruct",   "public void()", FALSE, FALSE },
//
//# "clone" operator:
//{ "__clone",  "__clone",      "public void()", FALSE, FALSE },
//
//{ "__set_static", "__set_static", "public static void(mixed->string)", FALSE, FALSE },
//
//# serialize()/Unserialize():
//{ "__sleep",  "__sleep",  "public string->int()", TRUE, FALSE },
//{ "__wakeup", "__wakeup", "public void()", TRUE, FALSE },
//
//{ "__toString", "__tostring", "public string()", FALSE, FALSE },
//
//# Dynamic properties/methods handling not supported by PHPLint:
//{ "__set",    "__set",   "public void(string, mixed)", FALSE, TRUE },
//{ "__get",    "__get",   "public mixed(string)", FALSE, TRUE },
//{ "__isset",  "__isset", "public boolean(string)", FALSE, TRUE },
//{ "__unset",  "__unset", "public void(string)", FALSE, TRUE },
//{ "__call",   "__call",  "public mixed(string, mixed[])", FALSE, TRUE },
//{ "__invoke", "__invoke", "public void(...)", FALSE, FALSE },
//{ "__callStatic", "__callstatic",  "public static mixed(string, mixed[])", FALSE, TRUE },
//{ "__set_state", "__set_state", "public object(mixed->string)", FALSE, TRUE }
//
//	}
//	}
//
//	/* Search between the known special methods: */
//	i = count(specialMethods)-1
//	do {
//		if( i < 0 ){
//			break;
//		}
//		if( $m->name_lower = specialMethods->i->name_lower ){
//			if( $m->name <> specialMethods->i->name ){
//				$globals->logger->notice($scanner->here(), "the special method `" . $m->name
//				. "' should be written as `" . specialMethods->i->name . "'");
//			}
//			break;
//		}
//		inc(i, -1);
//	}
//
//	if( i < 0 ){ # method not found
//		if( (length($m->name) >= 2) && ($m->name[0,2] = "__") ){
//			Warning2($m->decl_in, "unknown special method `" . $m->name
//			. "'. Methods whose name begins with `__' are reserved for future use by the language");
//		}
//		return
//	}
//
//	/* Found method specialMethods->i. */
//
//	if( (php_ver = php4) && ! specialMethods->i[php4] ){
//		Warning2($m->decl_in, "the name `" . specialMethods->i->name . "' is reserved for a special method in PHP5, use another name under PHP4");
//		return
//	} else if( specialMethods->i->not_supported ){
//		Warning2($m->decl_in, "special method `" . specialMethods->i->name
//		. "' not supported by PHPLint");
//	}
//
//	/* Allow final special method */
//	if( $m->is_final ){
//		#Notice2($m->decl_in, "special method `" . $m->name . "': method is `final'");
//		$m->is_final = FALSE
//		sign = MethodSignatureToString($m);
//		$m->is_final = TRUE
//	} else {
//		sign = MethodSignatureToString($m);
//	}
//
//	if( sign <> specialMethods->i->sign ){
//		Error2($m->decl_in, "special method `" . $m->name
//		. "': invalid signature `" . sign . "', expected `"
//		. specialMethods->i->sign . "'");
//	}
//}
//private static function SameMethodSignature(m1: Method, m2: Method): BOOLEAN
//{
//	return eq(m1->is_final, m2->is_final);
//		&& eq(m1->is_static, m2->is_static);
//		&& (m1->visibility = m2->visibility);
//		&& SameSign(m1->sign, m2->sign);
//}
//
//
//
//
//	private static function ParentConstructor($c: Class): Class
//	/*
//		Returns the parent class containing the constructor, or
//		NULL if no constructors are defined in parent classes.
//	*/
//	{
//		$c = $c->extends
//		WHILE ($c <> NULL) && ($c->construct = NULL) DO
//			$c = $c->extends
//		}
//		return $c
//	}
//
//	private static function ParentDestructor($c: Class): Class
//	/*
//		Returns the parent class containing the destructor, or
//		NULL if no destructors are defined in parent classes.
//	*/
//	{
//		$c = $c->extends
//		WHILE ($c <> NULL) && ($c->destruct = NULL) DO
//			$c = $c->extends
//		}
//		return $c
//	}

	/**
	 * Parses a class method or interface method.
	 * @param Globals $globals Context of the parser.
	 * @param DocBlockWrapper $dbw DocBlock wrapper, possibly containing the
	 * empty DocBlock if not available.
	 * @param boolean $is_abstract Found the abstract modifier.
	 * @param Visibility $visibility Visibility modifier.
	 * @param boolean $is_static Found the static modifier.
	 * @param boolean $is_final Found the final modifier.
	 * @param Type $t Returned type found in meta-code, or NULL if not
	 * specified.
	 * @return void
	 */
	public static function parse($globals, $dbw, $is_abstract, $visibility, $is_static, $is_final, $t)
	{
		# The caller already parsed DocBlock line tags and set the $is_abstract,
		# visibility, static, final and $t arguments.
		# @param tags will be checked by ParseArgListDecl().
		# @return tag will be checked below.
		# @triggers tag will be checked below.
		# @throw tags will be collected below.

		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;

		$c = $pkg->curr_class;

		// Check modifiers:
		if ($c->is_interface) {
			$is_abstract = TRUE;
			if ($visibility !== Visibility::$public_) {
				$logger->error($scanner->here(), "interface method must be `public'");
				$visibility = Visibility::$public_;
			}
			if ($is_final) {
				$logger->error($scanner->here(), "interface method cannot be `final'");
				$is_final = FALSE;
			}
		} else if ($pkg->curr_class->is_abstract) {
			if ($is_abstract) {
				if ($visibility === Visibility::$private_) {
					$logger->error($scanner->here(), "abstract method cannot be `private'");
					$visibility = Visibility::$protected_;
				}
				if ($is_static) {
					$logger->error($scanner->here(), "abstract method cannot be static");
					$is_static = FALSE;
				}
				if ($is_final) {
					$logger->error($scanner->here(), "abstract method cannot be `final'");
					$is_final = FALSE;
				}
			} else {
				if ($is_final && $visibility === Visibility::$private_) {
					$logger->warning($scanner->here(), "a private method is implicitly `final'");
					$is_final = FALSE;
				}
			}
		} else { /* regular class */
			if ($is_abstract) {
				$logger->error($scanner->here(), "abstract method in non-abstract class");
				$is_abstract = FALSE;
			}
			if ($is_final && $visibility === Visibility::$private_) {
				$logger->warning($scanner->here(), "a private method is implicitly `final'");
				$is_final = FALSE;
			}
		}

		$scanner->readSym();

		// &
		$by_reference = FALSE;
		if ($scanner->sym === Symbol::$sym_bit_and) {
			$by_reference = TRUE;
			if ($globals->isPHP(5)) {
				$logger->warning($scanner->here(), "obsolete return by reference `function &func()', don't use in PHP 5");
			}
			$scanner->readSym();
		}

		// Parse name:
		$globals->expect(Symbol::$sym_identifier, "expected method name");
		$name = new CaseInsensitiveString($scanner->s);
		$here = $scanner->here();

		/*
		  Search prev. decl of this method inside this class. If found, it is
		  forward decl or it is duplicated.
		  If forward decl or guessed, its signature will be checked against the
		  actual signature we parse next.
		 */
		$proto = $c->searchMethod($name);
		if ($proto !== NULL) {
			if ($proto->class_ !== $c) {
				$proto = NULL; // inherited, not really a proto.
			} else if ($proto->is_forward) {
				// is a proto - we will check it at the end.
			} else {
				$logger->error($here, "method $proto already defined in "
				. $logger->reference($here, $proto->decl_in));
				$proto = NULL;
			}
		}

		$sign = new Signature();
		$sign->reference = $by_reference;
		if ($t !== NULL)
			$sign->returns = $t;

		$m = new ClassMethod($here, $c, $dbw->getDocBlock(), $visibility, $name, $sign);
		$m->is_abstract = $is_abstract;
		$m->is_static = $is_static;
		$m->is_final = $is_final;
		
		if( $proto === NULL ){
			// Add new method to the class:
			$c->methods->put($m->name, $m);
		}

		// Check constructor and set $is_constructor:
		$is_constructor = FALSE;
		if ($globals->isPHP(4)) {

			if ($m->name->equals(ClassMethod::$CONSTRUCT_NAME)) {
				$logger->warning($here, "this method name is reserved for PHP 5 constructors");
				// FIXME: avoid strtolower()
			} else if ($m->name->getLowercase() === strtolower($c->name->getName())) {
				$is_constructor = TRUE;
			}
		} else { /* php5 */

			if ($m->name->equals(ClassMethod::$CONSTRUCT_NAME)) {
				$is_constructor = TRUE;

			} else if ($m->name->getLowercase() === strtolower($c->name->__toString())) {
				$is_constructor = TRUE;
				$logger->warning($here, "the constructor has the same name of the class. PHP 5 states it should be called `__construct()'");

			}
		}
		
		// Constructor always returns void:
		if( $is_constructor ){
			$m->is_constructor = TRUE;
			if( ! ($sign->returns instanceof GuessType
			|| $sign->returns instanceof VoidType) )
				$logger->error($m->decl_in, "constructor always returns void");
			$sign->returns = Globals::$void_type;
			
			if( $m->visibility === Visibility::$private_ ){
				if( $c->is_abstract )
					$logger->error($c->decl_in, "private constructor in abstrac class $c");
				else if( ! $c->is_final )
					$logger->warning($c->decl_in, "class $c with private constructor should be final");
			}
		}

		// FIXME: to do
		/*
		  Every class has 2 pre-defined methods: the constructor and the
		  destructor. This latter has a fixed signature, but the former may
		  accept different arguments, may raise differente errors and thrown
		  exceptions. If a custom constructor is found, we must check if somewere
		  in the code just parsed before we erroneusly called the pre-defined or
		  the inherited constructor. So, if it is a constructor, and there is not
		  previous forward decl., and the constructor of this class had called
		  before, raise an error:
		 */
//	if( is_constructor && (old_m = NULL);
//	&& ($pkg->curr_class->constructor_last_used_here <> NULL) ){
//		$logger->error($scanner->here(), "the default or inherited constructor for this class was already invoked before declaration in " . $pkg->curr_class->constructor_last_used_here->reference($scanner->here()) . ". Hint: change the order of the declarations or declare a `forward' class or `forward' constructor, ensuring that everything be defined before usage.");
//	}

		// Check destructor and sets $is_destructor:
		$is_destructor = $globals->isPHP(5)
			&& $m->name->equals(ClassMethod::$DESTRUCT_NAME);
		
		// Destructor always returns void:
		if( $is_destructor ){
			$m->is_destructor = TRUE;
			if( ! ($sign->returns instanceof GuessType
			|| $sign->returns instanceof VoidType) )
				$logger->error($m->decl_in, "destructor always returns void");
			$sign->returns = Globals::$void_type;
		}

		if( ! $globals->report_unused || $is_constructor || $is_destructor || $is_abstract ){
			$m->used = 100;
		}

		$pkg->curr_method = $proto === NULL? $m : $proto;
		
		$pkg->scope++;

		$scanner->readSym(); // skip name

		// Adds implicit "this" argument to non-static method:
		$_this_ = /*.(Variable).*/ NULL;
		if (!$is_static) {
			// Virtual declaration of the $this variable:
			$_this_ = new Variable("this", FALSE, $here, $pkg->scope);
			$_this_->type = $c;
			$_this_->assigned = TRUE;
			$_this_->assigned_once = TRUE;
			$_this_->used = 100;
			$globals->addVar($_this_);
		}

		FormalArguments::parse($globals, $dbw, $sign);
		
		TriggersAndThrows::parse($globals, $dbw, $sign);

		// FIXME: to do
//	if( ! is_constructor ){
//		/*
//			Check signature of special methods __xxx() only if it is fully
//			provided. If the signature has to be guessed, we need to parse
//			the body looking the 'return EXPR' statement before this check
//			can be made.
//		*/
//		if( ! guess ){
//			CheckSpecialMethodSignature($m);
//		}
//
//	} else {
//		/*
//			Check visibility/static properties and signature
//			of the constructor (since it may or may not have
//			arguments, its signature cannot be checked by
//			CheckSpecialMethodSignature()):
//		*/
//		if( $m->is_static ){
//			$logger->error($scanner->here(), "constructor `" . $m->name
//			. "': a constructor cannot be `static'");
//			$m->is_static = FALSE
//		}
//		if( (sign->return <> NULL) && (sign->return <> void_type) ){
//			$logger->error($scanner->here(), "constructor `" . $m->name . "': a constructor cannot"
//			. " return a value. It must be declared `void'.");
//		}
//		sign->return = void_type
//	}
		
		$dbw->clear();

		// Compare signature of the prototype:
		if( $proto !== NULL ){
			if( $m->sign->returns instanceof GuessType )
				$m->sign->returns = $proto->sign->returns;
			if( ! (
				$proto->is_final == $m->is_final
				&& $proto->is_abstract == $m->is_abstract
				&& $proto->equalsPrototypeOf($m)
			) )
				$logger->error($m->decl_in, "method $m with prototype\n"
				. $m->prototype() ."\n"
				. "does not match the forward declaration in "
				. $logger->reference($m->decl_in, $proto->decl_in)
				. " with prototype\n" . $proto->prototype());
		}
		
		if ($proto === NULL) {
			if ($is_constructor) {
				if ($c->constructor === NULL) {
					$c->constructor = $m;
				} else {
					$logger->error($scanner->here(), "constructor " . $m->name
					. " already declared as "
					. $c->constructor->name . " in line "
					. $c->constructor->decl_in->getLineNo());
				}
			}

			if ($is_destructor) {
				if ($c->destructor === NULL) {
					$c->destructor = $m;
				} else {
					$logger->error($scanner->here(), "destructor " . $m->name
					. " already declared as in line "
					. $c->destructor->decl_in->getLineNo());
				}
			}
			
		} else {
			// Prototype becomes actual method:
			$proto->is_forward = FALSE;
			$proto->decl_in = $m->decl_in;
			$proto->docblock = $m->docblock;
			// Copy signature too because default values are not specified
			// in the proto:
			$proto->sign = $m->sign;
			$m = $proto;
			$proto = NULL;
		}

		// Parse method body:

		if ($scanner->sym === Symbol::$sym_semicolon) {

			if ($globals->isPHP(4)) {
				if ($is_abstract) {
					$logger->error($scanner->here(), "expected empty body `{}' for abstract method");
				} else {
					$logger->error($scanner->here(), "expected method body");
				}
			} else {
				if (!$is_abstract) {
					$logger->error($scanner->here(), "missing method body in non-abstract method");
				}
			}

			$scanner->readSym();
				
			if( $sign->returns instanceof GuessType )
				$sign->returns = Globals::$void_type;

		} else if ($scanner->sym === Symbol::$sym_lbrace) {
			
			if( $is_abstract ){

				if ($globals->isPHP(4)) {
					$scanner->readSym();
					$globals->expect(Symbol::$sym_rbrace,
					"expected `}'. The body of an abstract method must be empty.");
					$scanner->readSym();
					if (!$is_static)
						$_this_->used = 100;
				} else {
					$logger->error($scanner->here(),
					"expected `;'. Abstract method cannot contain a body.");
					// skip unexpected body:
					/* $res = */ CompoundStatement::parse($globals);
				}
				
				if( $sign->returns instanceof GuessType )
					$sign->returns = Globals::$void_type;
			
			} else {
				$res = CompoundStatement::parse($globals);
		
				if( $sign->returns instanceof GuessType ){
					# No `return' statement found in body - guess `void':
					$sign->returns = Globals::$void_type;

				} else if( $sign->returns !== Globals::$void_type
					&& ($res & Flow::NEXT_MASK) != 0
					# Avoid to log error on module packages, since they use dummy
					# code and no proper `return EXPR' statements:
					&& ! $pkg->is_module
				){
					$logger->error($m->decl_in,
					"missing `return' in at least one execution path in non-void method $m");
				}
				
				FormalArguments::checkFormalArgsByReference($globals, FALSE);
				
				if ($is_constructor
				&& $c->parentConstructor() !== NULL
				&& !$c->parent_constructor_called
				) {
					$logger->error($m->decl_in,
					"missing call to the parent constructor");
				}

				if ($is_destructor
				&& $c->parentDestructor() !== NULL
				&& ! $c->parent_destructor_called
				) {
					$logger->error($m->decl_in,
					"missing call to the parent destructor");
				}
			}
		} else {
			throw new ParseException($scanner->here(), "unexpected symbol "
				. $scanner->sym);
		}

		// FIXME: to do
//	/*
//		Check signature of special methods __xxx();
//		if it was not fully provided:
//	*/
//	if( guess && ! is_constructor ){
//		CheckSpecialMethodSignature($m);
//	}
//
//	/*
//		Check usage of $this in static (forbidden) and non-static
//		methods (allowed). Guess the static modifier for PHP4.
//	*/
////	if( ! static && (this->used = 0) ){
////		# $this not used in non-static method:
////		if( php_ver = php4 ){
////			if( print_notices ){
////				Notice2($m->decl_in, "the method " . mn($pkg->curr_class, $m);
////				. " does not use `$this': guessing `static' modifier. Hint: you can add `/*. static .*/' to prevent this message to be shown.");
////				static = TRUE
////				$m->is_static = TRUE
////				guess = TRUE
////			}
////		} else {
////			if( print_notices ){
////				Notice2($m->decl_in, "the method " . mn($pkg->curr_class, $m);
////				. " does not use `$this'. Is it `static'?");
////			}
////		}
////	}
//
//	this->used = 100  # prevent "unused var. $this"

		$globals->cleanCurrentScope();
		$pkg->scope--;
		$pkg->curr_method = NULL;

		$err = ClassInheritance::checkImplementedOrOverridden($m);
		if( $err !== "" )
			$logger->error($m->decl_in, "method $m with prototype\n"
				. $m->prototype() . $err);

		// FIXME: to do
//	if( old_m <> NULL ){
//		if( ! SameMethodSignature($m, old_m) ){
//			if( old_m->forward ){ /* forward decl. method */
//				Error2($m->decl_in, "method " . mn($pkg->curr_class, $m);
//				. " with signature `" . MethodSignatureToString($m);
//				. "' does not match forward signature `"
//				. MethodSignatureToString(old_m);
//				. "' as declared in "
//				. old_m->decl_in->reference($scanner->here()));
//			} else { /* guessed signature */
//				Error2($m->decl_in, "method " . mn($pkg->curr_class, $m);
//				. " with signature `" . MethodSignatureToString($m);
//				. "' does not match the signature `"
//				. MethodSignatureToString(old_m);
//				. "' as guessed in "
//				. old_m->guessed_sign_in->reference($scanner->here()));
//			}
//		}
//	}
	}

}
