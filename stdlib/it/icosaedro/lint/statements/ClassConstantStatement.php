<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use it\icosaedro\lint\types\ClassConstant;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\expressions\StaticExpression;

/**
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/24 20:03:13 $
 */
class ClassConstantStatement {

	/**
	 * Passes the "const" statement. All the constants take the same DocBlock
	 * and visibility.
	 * @param Globals $globals Context of the parser.
	 * @param DocBlockWrapper $dbw DocBlock wrapper, possibly containing the
	 * empty DocBlock if not available.
	 * @param Visibility $visibility Visibility modifier found as meta-code;
	 * should be public by default.
	 * @return void
	 */
	public static function parse($globals, $dbw, $visibility) {
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$scanner->readSym();

		$c = $pkg->curr_class;

		// Scans list of constants:
		do {
			$globals->expect(Symbol::$sym_identifier, "expected constant name");
			$name = $scanner->s;
			$here = $scanner->here();
			// Check multiple definitions or re-definition of inherited consts:
			$co = $c->searchConstant($name);
			if ($co !== NULL) {
				// FIXME: constants can be declared "forward" - keep this?
//			if( ! $c->class_->is_forward ){
				if ($co->class_ === $c) {
					$logger->error($scanner->here(),
					"class constant $name already defined in "
					. $logger->reference($here, $co->decl_in));
				} else if ($co->class_->is_abstract || $co->class_->is_interface) {
					$logger->error($scanner->here(),
					"cannot re-define the constant $co inherited from interface or abstract class");
				}
//			}
			}

			$co = new ClassConstant($here, $c, $dbw->getDocBlock(), $visibility, $name, Result::getUnknown());
			if (!$globals->report_unused)
				$co->used = 100;
			$scanner->readSym();
			$globals->expect(Symbol::$sym_assign, "expected `=' after constant name");
			$scanner->readSym();
			$r = StaticExpression::parse($globals);
			if ($r->isArray()) {
				$logger->error($scanner->here(),
				"arrays are not allowed in class constants");
				$r = Result::getUnknown();
			}
			$co->value = $r;
			$c->constants[$co->name] = $co;

			if ($scanner->sym === Symbol::$sym_comma) {
				$scanner->readSym();
			} else {
				break;
			}
		} while (TRUE);

		$globals->expect(Symbol::$sym_semicolon, "missing `;'");
		$scanner->readSym();
	}

}
