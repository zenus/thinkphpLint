<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\NamespaceResolver;
use it\icosaedro\lint\Function_;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\GuessType;
use it\icosaedro\lint\ParseException;

/**
 * Parses function declaration.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/26 21:15:04 $
 */
class FunctionStatement {
	
	 
	/**
	 * @param Globals $globals
	 * @param boolean $is_private
	 * @param Type $return_type
	 * @return void
	 */
	public static function parse($globals, $is_private, $return_type)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$dbw = $pkg->curr_docblock;
		$db = $dbw->getDocBlock();
		
		$dbw->checkLineTagsForFunction();
		
		if( $db !== NULL ){
			
			// Determines private attribute from DocBlock or meta-code:
			if( $is_private && $dbw->isPrivate() )
				$logger->error($scanner->here(),
				"`private' attribute both in DocBlock and PHPLint meta-code");
			$is_private = $is_private || $dbw->isPrivate();
		
			// Determines return type from DocBlock or meta-code:
			if( $return_type !== NULL && $dbw->getReturnType() !== NULL )
				$logger->error($scanner->here(),
				"return type declaration both in DocBlock and PHPLint meta-code");
			$return_type = $return_type !== NULL? $return_type
				: $dbw->getReturnType();
		}
		
		$scanner->readSym();
		
		$sign = new Signature();
		if( $return_type !== NULL )
			$sign->returns = $return_type;
		
		if( $scanner->sym === Symbol::$sym_bit_and ){
			$sign->reference = TRUE;
			if( $globals->isPHP(5) )
				$logger->warning($scanner->here(),
				"obsolete return by reference, don't use in PHP 5");
			$scanner->readSym();
		}

		/* Parse function name: */

		$globals->expect(Symbol::$sym_identifier,
		"expected function name after `function'");

		$here = $scanner->here();
		$s = $scanner->s;
		if( $pkg->scope > 0 ){
			$logger->error($here, "function $s nested inside another function."
			. " The scope of this function is still global but it will exist only if"
			. " the parent function is be called. If the parent function is"
			. " called once more, this will give a fatal error for function re-definition.");
		}
		if( ! NamespaceResolver::isIdentifier($s) )
			throw new ParseException($here, "function name must be a simple identifier");
		
		$s = $pkg->resolver->absolute($s);
		$fqn = new FullyQualifiedName($s, FALSE);
		
		$proto = $globals->getFunc($fqn);
		if( $proto !== NULL ){
			if( $proto->is_forward ){
				// will compare proto/actual signature at the end
			} else {
				$logger->error($here, "function $proto already declared in "
				. $logger->reference($here, $proto->decl_in) );
				$proto = NULL;
			}
		}
		
		$f = new Function_($fqn);
		$f->is_forward = FALSE;
		$f->is_private = $is_private;
		$f->decl_in = $here;
		$f->sign = $sign;
		$f->docblock = $dbw->getDocBlock();
		
		// If nested func, save current context:
		$parent_func = $pkg->curr_func;

		$pkg->curr_func = $proto === NULL? $f : $proto;
		$pkg->scope++;
		
		// Skip func name:
		$scanner->readSym();

		FormalArguments::parse($globals, $dbw, $sign);
		
		TriggersAndThrows::parse($globals, $dbw, $sign);
		
		$dbw->clear();

		// Compare signature of the prototype:
		if( $proto !== NULL ){
			if( $f->sign->returns instanceof GuessType )
				$f->sign->returns = $proto->sign->returns;
			if( !  $proto->equalsPrototypeOf($f) )
				$logger->error($f->decl_in, "function $f with prototype\n"
				. $f->prototype() ."\n"
				. "does not match the forward declaration in "
				. $logger->reference($f->decl_in, $proto->decl_in)
				. " with prototype\n" . $proto->prototype());
		}
		
		// Add function:
		if ($proto === NULL) {
			$globals->functions->put($f->name, $f);
			
		} else {
			// Prototype becomes actual function:
			$proto->is_forward = FALSE;
			$proto->docblock = $f->docblock;
			$proto->decl_in = $f->decl_in;
			// Copy signature too because default values are not specified
			// in the proto:
			$proto->sign = $f->sign;
			$f = $proto;
			$proto = NULL;
		}

		// Parse function body:
		$globals->expect(Symbol::$sym_lbrace, "expected '{' in function body declaration");
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
			$logger->error($f->decl_in, "missing `return' in at least one execution path in non-void function " . $f->name);
		}
		
		FormalArguments::checkFormalArgsByReference($globals, FALSE);

//		CheckAutoloadSignature($f);
//		CheckCastSignature($f);

		$globals->cleanCurrentScope();
		$pkg->scope--;
		$pkg->curr_func = $parent_func;
	}
		
		// FIXME: to do
//	private static function CheckSpecialFunc(f: Function)
//	{
//		if( f[name_lower] = "cast" ){
//			if( php_ver = php4 ){
//				Package::$curr->scanner->here()->warning("function name " . f[name]
//				. " is reserved for special use by PHPLint under PHP 5")
//			}
//		} else if( (length(f[name]) >= 2) && (f[name][0,2] = "__") ){
//			if( f[name_lower] = "__autoload" ){
//				if( php_ver = php4 ){
//					Package::$curr->scanner->here()->warning("function name " . f[name]
//					. " is reserved for special use in PHP 5")
//				} else {
//					autoload_function = f
//				}
//			} else {
//				Package::$curr->scanner->here()->warning("function " . f[name]
//				. ": names beginning with two underscores are reserved for future extensions of the language, do not use")
//			}
//		}
//	}
//
//
//	private static function CheckAutoloadSignature(f: Function)
//	CONST sign_expected = "void(string)"
//	VAR sign_actual: string
//	{
//		if( f[name_lower] !== "__autoload" ){
//			return
//		}
//		sign_actual = FunctionSignatureToString(f[sign])
//		if( sign_actual !== sign_expected ){
//			Error2(f[decl_in], "invalid signature " . sign_actual
//			. " for special function __autoload(), expected signature is "
//			. sign_expected)
//		}
//	}
//
//
//	private static function CheckCastSignature(f: Function)
//	CONST sign_expected = "mixed(string, mixed)"
//	VAR sign_actual: string
//	{
//		if( f[name_lower] !== "cast" ){
//			return
//		}
//		sign_actual = FunctionSignatureToString(f[sign])
//		if( sign_actual !== sign_expected ){
//			Error2(f[decl_in], "invalid signature " . sign_actual
//			. " for special function cast(), expected signature is "
//			. sign_expected)
//		}
//	}

	
}

