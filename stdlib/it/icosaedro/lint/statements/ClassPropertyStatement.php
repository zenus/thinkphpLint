<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\expressions\StaticExpression;
use it\icosaedro\lint\types\ClassProperty;

/**
 * Parses a property declaration.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/24 20:03:13 $
 */
class ClassPropertyStatement {

	/**
	 * Parses a property declaration.
	 * We enter with sym_variable.
	 * @param Globals $globals Context of the parser.
	 * @param DocBlockWrapper $dbw DocBlock wrapper, possibly containing the
	 * empty DocBlock if not available.
	 * @param Visibility $visibility Visibility modifier.
	 * @param boolean $is_static Found static modifier.
	 * @param Type $t Type declared in meta-code, or NULL if not available.
	 * @return void
	 */
	public static function parse($globals, $dbw, $visibility, $is_static, $t)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$logger = $globals->logger;

		$c = $pkg->curr_class;

		// Scans list of properties:
		do {
			$globals->expect(Symbol::$sym_variable, "expected property name \$NAME");
			$name = $scanner->s;
			$here = $scanner->here();

			$p = $c->searchProperty($name);
			if( $p !== NULL ){
				// PHP4: properties can be re-defined, but this is not allowed
				// in PHPLint (useless: should have the same type).
				// PHP5: private properties are distinct from non-private
				// properties, so derived classes can re-define inherited
				// private properties. Then an object may store several props
				// with same name "p": the inherited private ones from parent
				// classes, and the local one.
				if( $p->class_ === $c || $globals->isPHP(4) )
					$logger->error($scanner->here(),
					"property $p already defined in "
					. $logger->reference($here, $p->decl_in));
				else if( $p->visibility !== Visibility::$private_ )
					$logger->error($scanner->here(),
					"cannot redefine inherited non-private property $p");
			}

			$p = new ClassProperty($here, $c, $dbw->getDocBlock(), $visibility, $name, Result::getUnknown());

			# Staticity:
			$p->is_static = $is_static;

			if (!$globals->report_unused || $pkg->is_module)
				$p->used = 100;

			$scanner->readSym(); // skip name
			# Get property initial value:
			if ($scanner->sym === Symbol::$sym_assign) {
				$scanner->readSym();
				$r = StaticExpression::parse($globals);
				if ($t === NULL) {
					$p->value = $r;
					if ($r->isNull())
						$logger->error($scanner->here(), "NULL value must be cast to some specific type. Examples:\n"
						. "/*.(resource).*/ NULL\n"
						. "/*.(string).*/ NULL\n"
						. "/*.(string[int]).*/ NULL\n"
						. "/*.(float[int][int]).*/ NULL\n"
						. "/*.(MyClass).*/ NULL.");
				} else {
					if ($r->assignableTo($t)) {
						$p->value = Result::factory($t, $r->getValue());
					} else {
						$p->value = Result::factory($t);
						$logger->error($scanner->here(), "incompatible value of type " . $r->getType());
					}
				}
			} else if ($t === NULL) {
				$logger->error($scanner->here(), "undefined type for property `\$$name'. Hint: you may indicate an explicit type (example: `/*.int.*/ \$$name') or assign a default value (example: `\$$name=123') or add a DocBlock line tag (example: `@var int').");
			} else {
				$p->value = Result::factory($t);
				if ( ! Globals::$null_type->assignableTo($t)) {
					$logger->error($scanner->here(), "property \$$name of type $t requires an initial value, otherwise it would be initialized to the invalid value NULL at runtime (PHPLint safety restriction)");
				}
			}

			$c->properties[$p->name] = $p;

			# More properties in list?
			if ($scanner->sym === Symbol::$sym_comma) {
				$scanner->readSym();
			} else {
				break;
			}
		} while (TRUE);

		$globals->expect(Symbol::$sym_semicolon, "expected ';'");
		$scanner->readSym();
	}

}
