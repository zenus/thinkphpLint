<?php

namespace it\icosaedro\lint;

require_once __DIR__ . "/../../../autoload.php";

use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\ArrayType;

/**
 * Parses the type declaration, that may be part PHP code and part PHPLint
 * meta-code. Example:
 * <blockquote><pre>
 * /&#42;. int .&#42;/ function indexOf(array/&#42;. [int]string .&#42;/ $s) { ... }
 * </pre></blockquote>
 * Note that the argument of the function uses PHP type-hint, so we may have to
 * parse mixed PHP/PHPLint code.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:47:53 $
 */
class TypeDecl {
	
	/*. forward public static Type function parse(Globals $globals, boolean $allow_type_hinting); .*/

	/**
	 * Attempts to parse a type name. Type names may have several forms
	 * that ranges from a simple "int", up to a fully qualified class name,
	 * "namespace" operator or even "self", "parent", and may appear both in
	 * PHP code as type hint, or PHPLint meta-code. We enter with an arbitrary
	 * symbol.
	 * @param Globals $globals
	 * @param boolean $allow_type_hinting
	 * @return Type If the current symbol(s) look like a type, returns that
	 * type, possibly <code>UnknownType</code> if something went wrong and the
	 * name found cannot be recognized or it is not defined. Returns NULL if
	 * there is not a type at all here, which is perfectly valid in most cases
	 * encountered by the parser: client code must establish if this is allowed
	 * or not.
	 */
	private static function parseName($globals, $allow_type_hinting) {
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if ($globals->isPHP(4)) {
			$allow_type_hinting = FALSE;
		}

		if ($scanner->sym === Symbol::$sym_namespace) {
			$globals->resolveNamespaceOperator();
		} else if ($scanner->sym === Symbol::$sym_x_namespace) {
			$globals->resolveNamespaceOperatorInMetaCode();
		}

		switch ($scanner->sym->__toString()) {

			case "sym_x_void": $scanner->readSym();
				return Globals::$void_type;
			case "sym_x_boolean": $scanner->readSym();
				return Globals::$boolean_type;
			case "sym_x_int": $scanner->readSym();
				return Globals::$int_type;
			case "sym_x_float": $scanner->readSym();
				return Globals::$float_type;
			case "sym_x_string": $scanner->readSym();
				return Globals::$string_type;
			case "sym_x_mixed": $scanner->readSym();
				return Globals::$mixed_type;
			case "sym_x_resource": $scanner->readSym();
				return Globals::$resource_type;
			case "sym_x_object": $scanner->readSym();
				return Globals::$object_type;

			case "sym_object":
				$globals->logger->error($scanner->here(), "`object' keyword not allowed as type, allowed only as typecast `(object)'");
				if (!$allow_type_hinting)
					$globals->logger->error($scanner->here(), "invalid syntax");
				$scanner->readSym();
				return Globals::$object_type;

			case "sym_x_identifier":
				$c = $globals->searchClass($scanner->s);
				if ($c === NULL) {
					$globals->logger->error($scanner->here(), "undefined identifier `" . $scanner->s . "'");
					$scanner->readSym();
					return Globals::$unknown_type;
				} else {
					$globals->accountClass($c);
					$scanner->readSym();
					return $c;
				}
			case "sym_identifier":
				if (!$allow_type_hinting)
					$globals->logger->error($scanner->here(), "invalid syntax");
				$c = $globals->searchClass($scanner->s);
				if ($c === NULL) {
					$globals->logger->error($scanner->here(), "undefined identifier `" . $scanner->s . "'");
					$scanner->readSym();
					return Globals::$unknown_type;
				} else {
					$globals->accountClass($c);
					$scanner->readSym();
					return $c;
				}
			case "sym_self":
			case "sym_x_self":
				if ( $scanner->sym === Symbol::$sym_self && ! $allow_type_hinting )
					// Maybe "self::..." ?
					return NULL;
				if ($pkg->curr_class == NULL) {
					$globals->logger->error($scanner->here(), "`self': not inside a class");
					return NULL;
				}
				$scanner->readSym();
				return $pkg->curr_class;

			case "sym_parent":
			case "sym_x_parent":
				if ( $scanner->sym === Symbol::$sym_parent && ! $allow_type_hinting )
					// Maybe "parent::..." ?
					return NULL;
				if ($pkg->curr_class === NULL) {
					$globals->logger->error($scanner->here(), "`parent': not inside a class");
					return NULL;
				}
				if ($pkg->curr_class->extended === NULL) {
					$globals->logger->error($scanner->here(), "`parent': no parent class");
					return NULL;
				}
				$scanner->readSym();
				$globals->accountClass($pkg->curr_class->extended);
				return $pkg->curr_class->extended;

			default:
				return NULL;
		}
	}
	

	/**
	 * Parses a sequence of indeces "...[K][K]" possibly ending with a elements
	 * type if new syntax or "...[K][K]E" if old syntax. We enter here with
	 * sym="[".
	 * @param Globals $globals
	 * @param Type $e With the new array syntax, this is the type of the
	 * elements, which is known from the very beginning of the parsing of the
	 * type. If it is the old array syntax, set to NULL and then the type of
	 * the elements has to be parsed at the end.
	 * @return Type
	 */
	private static function parseIndeces($globals, $e) {
		// Parse type of this index "[int]", "[string]" or "[]":
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$index_type = /*.(Type).*/ NULL;
		if ($scanner->sym === Symbol::$sym_x_int) {
			$index_type = Globals::$int_type;
			$scanner->readSym();
		} else if ($scanner->sym === Symbol::$sym_x_string) {
			$index_type = Globals::$string_type;
			$scanner->readSym();
		} else if ($scanner->sym === Symbol::$sym_x_rsquare) {
			$index_type = Globals::$mixed_type;
		}
		$globals->expect(Symbol::$sym_x_rsquare, "expected index type `int', `string' or `]'");
		$scanner->readSym();

		// Parse next index or elements type:
		if ($scanner->sym === Symbol::$sym_x_lsquare) {
			$elem_type = self::parseIndeces($globals, $e);
		} else if ($e === NULL) {
			// Old syntax. Looks for an elements type:
			$elem_type = self::parseName($globals, FALSE);
			if ($elem_type === NULL)
			// Found "array[k][k]" without elements type.
				$elem_type = Globals::$mixed_type;
		} else {
			// Finished new array type decl.
			$elem_type = $e;
		}
		return ArrayType::factory($index_type, $elem_type);
	}

	/**
	 * Attempts to parse a declaration of type. Type names may have several forms
	 * that ranges from a simple "int", up to a fully qualified class name,
	 * "namespace" operator or even "self", "parent", and may appear both in
	 * PHP code as type hint, or PHPLint meta-code. We enter with an arbitrary
	 * symbol.
	 * 
	 * <p>
	 * May trigger class autoloading, if enabled.
	 * 
	 * <blockquote><pre>
	 * type = T { index } | "array" [ index {index} T ];
	 * </pre></blockquote>
	 *
	 * BUG: void[] should be forbidden.
	 * 
	 * @param Globals $globals
	 * @param boolean $allow_type_hinting Set to true while parsing formal
	 * arguments of function or method. The first symbol may then be either
	 * "array" or the name of a class in PHP code, the rest, if any, must still
	 * be PHPLint meta-code. Example: <code>function f(array/&#42;. [int]string
	 * .&#42;/ \$a){}</code>.
	 * @return Type Type parsed, possibly UnknownType if an error was
	 * detected (and then reported). Returns NULL if there is not a type at all
	 * here, which is perfectly valid in most cases encountered by the parser:
	 * client code must establish if this is allowed or not.
	 */
	public static function parse($globals, $allow_type_hinting) {
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		if (($scanner->sym === Symbol::$sym_x_array)
				|| ($scanner->sym === Symbol::$sym_array)) {
			# Old type syntax array[K][K]E:
			if ($scanner->sym === Symbol::$sym_array && !$allow_type_hinting)
				return NULL;
			$scanner->readSym();
			if ($scanner->sym !== Symbol::$sym_x_lsquare)
				return ArrayType::factory(Globals::$mixed_type, Globals::$mixed_type);
			return self::parseIndeces($globals, NULL);
			
		} else {
			# Type T or new array syntax T[][]:
			$t = self::parseName($globals, $allow_type_hinting);
			if ($t == NULL)
				return NULL;
			if ($scanner->sym === Symbol::$sym_x_lsquare)
				$t = self::parseIndeces($globals, $t);
			return $t;
		}
	}

}
