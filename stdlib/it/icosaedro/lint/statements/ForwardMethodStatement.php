<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\CaseInsensitiveString;

/**
 * Parse method prototype.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/04 12:11:23 $
 */
class ForwardMethodStatement {

	/**
	 * Parse method prototype.
	 * @param Globals $globals
	 * @param Visibility $visibility
	 * @param boolean $is_abstract
	 * @param boolean $is_final
	 * @param boolean $is_static
	 * @param Type $t
	 * @return void
	 */
	public static function parse($globals, $visibility, $is_abstract, $is_final, $is_static, $t)
	{
		// FIXME
//VAR
//	$m, old_m: Method
//	$i: INTEGER
//	$sign: SIGNATURE
//	this: VARIABLE
//	guess: BOOLEAN
//	is_constructor, is_destructor: BOOLEAN
//
//
//	/************
//
//	private static function ParentConstructor($c: Class): Class
//	/*
//		FIXME: not used
//		Returns the parent class containing the constructor, or
//		NULL if no constructors are defined in parent classes.
//	*/
//	{
//		$c = $c->extends
//		while( ($c <> NULL) && ($c->construct = NULL) ){
//			$c = $c->extends
//		}
//		return $c
//	}
//
//	private static function ParentDestructor($c: Class): Class
//	/*
//		FIXME: not used
//		Returns the parent class containing the destructor, or
//		NULL if no constructors are defined in parent classes.
//	*/
//	{
//		$c = $c->extends
//		while( ($c <> NULL) && ($c->destruct = NULL) ){
//			$c = $c->extends
//		}
//		return $c
//	}
//
//	***************/
//

		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$c = $pkg->curr_class;
		
		// Check and sanitize flags:
		if( $c->is_interface ){
			$is_abstract = TRUE;
			if( $visibility !== Visibility::$public_ ){
				$logger->error($scanner->here(),
				"interface methods must be `public'");
				$visibility = Visibility::$public_;
			}
			if( $is_final ){
				$logger->error($scanner->here(),
				"interface methods cannot be `final'");
				$is_final = FALSE;
			}

		} else if( $c->is_abstract ){
			if( $is_abstract ){
				if( $visibility === Visibility::$private_ ){
					$logger->error($scanner->here(),
					"abstract methods cannot be `private'");
					$visibility = Visibility::$protected_;
				}
				if( $is_static ){
					$logger->error($scanner->here(),
					"abstract methods cannot be static");
					$is_static = FALSE;
				}
				if( $is_final ){
					$logger->error($scanner->here(),
					"abstract methods cannot be `final'");
					$is_final = FALSE;
				}
			} else {
				if( $is_final && ($visibility === Visibility::$private_) ){
					$logger->warning($scanner->here(),
					"a private method is implicitly `final'");
					$is_final = FALSE;
				}
			}

		} else { /* regular class */
			if( $is_abstract ){
				$logger->error($scanner->here(),
				"abstract method in non-abstract class");
				$is_abstract = FALSE;
			}
			if( $is_final && $visibility === Visibility::$private_ ){
				$logger->warning($scanner->here(),
				"a private method is implicitly `final'");
				$is_final = FALSE;
			}

		}

		if( $t === NULL ){
			$logger->error($scanner->here(),
			"missing return type in method prototype -- assuming `void' and trying to continue anyway");
			$t = Globals::$void_type;
		}

		$scanner->readSym();

		/*
			&
		*/
		$by_reference = FALSE;
		if( $scanner->sym === Symbol::$sym_x_bit_and ){
			if( $globals->isPHP(5) ){
				$logger->warning($scanner->here(),
				"obsolete return by reference, don't use in PHP 5");
			}
			$by_reference = TRUE;
			$scanner->readSym();
		}

		/*
			Parse name:
		*/
		$globals->expect(Symbol::$sym_x_identifier, "expected method name");
		$name = new CaseInsensitiveString($scanner->s);
		$here = $scanner->here();
		$old_m = $c->searchMethod($name);
		if( $old_m !== NULL ){
			$logger->error($scanner->here(),
			"method $name already defined in "
			. $logger->reference($here, $old_m->decl_in));
			// FIXME: what if forward method already declared?
		}

		$sign = new Signature();
		$sign->reference = $by_reference;
		$sign->returns = $t;

		$m = new ClassMethod($here, $c, NULL, $visibility, $name, $sign);
		$m->is_forward = TRUE;
		$m->is_abstract = $is_abstract;
		$m->is_static = $is_static;
		$m->is_final = $is_final;

		/*
			Check constructor and set is_constructor:
		*/
		$is_constructor = FALSE;
		if( $globals->isPHP(4) ){

			if( $m->name->equals(ClassMethod::$CONSTRUCT_NAME) ){
				$logger->warning($here, "constructor " . $m->name
				. ": this name is reserved for PHP 5 constructors");
				
			} else if( $m->name->getLowercase() === strtolower($c->name->__toString()) ){
				$is_constructor = TRUE;
			}

		} else { /* php5 */

			if( $m->name->equals(ClassMethod::$CONSTRUCT_NAME) ){
				$is_constructor = TRUE;

			} else if( $m->name->getLowercase() === strtolower($c->name->__toString()) ){
				$is_constructor = TRUE;
				$logger->warning($here, "the constructor `" . $m->name
				. "' has the same name of the class. PHP 5 states"
				. " it should be called `__construct()'");
				$is_constructor = TRUE;
			}

		}

		if( $is_constructor ){
			if( $c->constructor === NULL ){
				$c->constructor = $m;
			} else {
				$logger->error($scanner->here(),
				"constructor $m already declared as "
				. $c->constructor->name . " in line "
				. $c->constructor->decl_in->getLineNo());
			}
		}

		/*
			Check destructor:
		*/
		$is_destructor = $globals->isPHP(5)
			&& $m->name->equals(ClassMethod::$DESTRUCT_NAME);
		if( $is_destructor ){
			$c->destructor = $m;
		}

		if( ! $globals->report_unused
		|| $is_constructor || $is_destructor || $is_abstract ){
			$m->used = 100;
		}

		$pkg->curr_method = $m;
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

		if( ! $is_constructor ){
			# FIXME:
			# CheckSpecialMethodSignature($m)

		} else {
			/*
				Check $visibility/static properties and signature
				of the constructor (since it may or may not have
				arguments, its signature cannot be checked by
				CheckSpecialMethodSignature()):
			*/
			if( $m->is_static ){
				$logger->error($scanner->here(),
				"constructor `$m' cannot be `static'");
				$m->is_static = FALSE;
			}
			if( $sign->returns !== Globals::$void_type ){
				$logger->error($scanner->here(),
				"constructor `$m' must return void");
				$sign->returns = Globals::$void_type;
			}
		}

		ForwardArgumentsList::parse($globals, $sign, "method");
		
		TriggersAndThrows::parse($globals, NULL, $sign);
		
		$c->methods->put($m->name, $m);

		$globals->expect(Symbol::$sym_x_semicolon,
			"expected `;' at the end of the method prototype");
		$scanner->readSym();

		# FIXME:
		#CheckOverriddenMethod($c, $m)

		$globals->cleanCurrentScope();
		$pkg->scope--;
		$pkg->curr_method = NULL;
	}

}
