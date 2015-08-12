<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\ParseException;

/**
 * Parses a prototype of function, class, interface, method.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class ForwardStatement {

	/**
	 * Parses a prototype of function, class, method. If called in the global
	 * context of parsing, looks for function or class; if called in the
	 * context of parsing of a class, looks for a member.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if ($pkg->scope > 0)
			$globals->logger->error($scanner->here(), "forward declarations allowed only in global scope");

		$scanner->readSym();

		// Parse modifiers:
		$is_abstract = FALSE;
		$is_final = FALSE;
		$is_static = FALSE;
		$is_unchecked = FALSE;
		$private_count = 0;
		$protected_count = 0;
		$public_count = 0;
		do {
			$sym = $scanner->sym;
			if ($sym === Symbol::$sym_x_abstract)
				$is_abstract = TRUE;
			else if ($sym === Symbol::$sym_x_final)
				$is_final = TRUE;
			else if ($sym === Symbol::$sym_x_static)
				$is_static = TRUE;
			else if ($sym === Symbol::$sym_x_unchecked)
				$is_unchecked = TRUE;
			else if ($sym === Symbol::$sym_x_private)
				$private_count++;
			else if ($sym === Symbol::$sym_x_protected)
				$protected_count++;
			else if ($sym === Symbol::$sym_x_public)
				$public_count++;
			else
				break;
			$scanner->readSym();
		} while (TRUE);

		if ($private_count + $protected_count + $public_count > 1)
			$globals->logger->error($scanner->here(), "multiple visibility modifiers");

		if ($private_count > 0)
			$visibility = Visibility::$private_;
		else if ($protected_count > 0)
			$visibility = Visibility::$protected_;
		else
			$visibility = Visibility::$public_;

		// Parse return type (returns NULL if not func or method):
		$t = TypeDecl::parse($globals, FALSE);

		if ($pkg->curr_class === NULL && $pkg->scope == 0) {
			/* Only function and class proto allowed here. */

			if ($scanner->sym === Symbol::$sym_x_function) {
				# FIXME: "public" should not be allowed.
				if ($visibility === Visibility::$protected_
						|| $is_static || $is_final || $is_abstract
						|| $is_unchecked) {
					$globals->logger->error($scanner->here(), "invalid attributes for function prototype");
				}
				ForwardFunctionStatement::parse($globals, $visibility === Visibility::$private_, $t);
			} else if ($scanner->sym === Symbol::$sym_x_interface) {
				if( $visibility === Visibility::$protected_ || $is_static || $is_final || $is_abstract
				|| $is_unchecked ) {
					$globals->logger->error($scanner->here(), "invalid attributes for interface prototype");
				}
				if ($t !== NULL) {
					$globals->logger->error($scanner->here(), "invalid return type in interface prototype");
				}
				ForwardInterfaceStatement::parse($globals, $visibility === Visibility::$private_);
				
			} else if ($scanner->sym === Symbol::$sym_x_class) {
				if( $visibility === Visibility::$protected_ || $is_static ) {
					$globals->logger->error($scanner->here(), "protected and static modifiers not allowed for class");
				}
				if ($t !== NULL)
					$globals->logger->error($scanner->here(), "class cannot have a return type");
				ForwardClassStatement::parse($globals, $visibility === Visibility::$private_, $is_abstract, $is_final, $is_unchecked);
						
			} else {
				throw new ParseException($scanner->here(), "expected function, interface or class prototype");
			}
			
		} else
			if ( $pkg->curr_class !== NULL && $pkg->scope == 0 ) {
			/* Only method proto allowed here. */

			if ($scanner->sym !== Symbol::$sym_x_function) {
				throw new ParseException($scanner->here(), "expected method prototype");
			}

			ForwardMethodStatement::parse($globals, $visibility, $is_abstract, $is_final, $is_static, $t);
		} else {
			throw new ParseException($scanner->here(), "forward declaration not allowed here");
		}
	}

}
