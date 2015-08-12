<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\types\ClassConstant;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class ForwardClassConstantStatement {
	
	/**
	 *
	 * @param Globals $globals
	 * @return void
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$c = $pkg->curr_class;

	while(TRUE){
		$globals->expect(Symbol::$sym_x_identifier, "expected constant name");
		$name = $scanner->s;
		$here = $scanner->here();
		// FIXME: check bare ID

		// Check multiple definitions or re-definition of inherited consts:
		$co = $c->searchConstant($name);
		if( $co !== NULL ){
			if( $co->class_ !== $c ){
				$logger->error($scanner->here(), "class constant $name already defined in "
				. $logger->reference($here, $co->class_->decl_in));
			} else if( $co->class_->is_abstract || $co->class_->is_interface ){
				$logger->error($scanner->here(), "cannot re-define constant $co inherited from interface or abstract class");
			}
		}

		$co = new ClassConstant($here, $c, NULL, Visibility::$public_, $name, Result::getUnknown());
		if (!$globals->report_unused)
			$co->used = 100;
		$scanner->readSym(); // skip name
		$c->constants[$co->name] = $co;

		if( $scanner->sym === Symbol::$sym_x_comma ){
			$scanner->readSym();
		} else if( $scanner->sym === Symbol::$sym_assign ){
			// FIXME: if cannot define a value, the const proto cannot be used!
			throw new ParseException($scanner->here(), "cannot assign value to constant prototype");
		} else {
			break;
		}
	}

	$globals->expect(Symbol::$sym_x_semicolon, "missing `;'");
	$scanner->readSym();

}

}
