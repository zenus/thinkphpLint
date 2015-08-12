<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\Function_;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class ForwardFunctionStatement {

	/**
	 * Parses a forward function declaration (also known as function prototype).
	 * @param Globals $globals
	 * @param boolean $is_private
	 * @param Type $t
	 * @return void
	 */
	public static function parse($globals, $is_private, $t)
	{
		$pkg = $globals->curr_pkg;
		$logger = $globals->logger;
		$scanner = $pkg->scanner;

		if ($t === NULL) {
			$logger->error($scanner->here(), "missing return type in function prototype");
			$t = Globals::$unknown_type;
		}
		$sign = new Signature();
		$sign->returns = $t;
		$scanner->readSym();

		if ($scanner->sym === Symbol::$sym_x_bit_and) {
			$sign->reference = TRUE;
			if ($globals->isPHP(5))
				$logger->warning($scanner->here(), "obsolete syntax `function &func()', don't use in PHP 5");
			$scanner->readSym();
		}

		// Parse name:
		$globals->expect(Symbol::$sym_x_identifier, "expected function name after `function'");
		$here = $scanner->here();
		$s = $pkg->resolver->absolute($scanner->s);
		$fqn = new FullyQualifiedName($s, FALSE);
		$proto = $globals->getFunc($fqn);
		if ($proto !== NULL) {
			$logger->error($scanner->here(), "function $fqn already declared in "
			. $logger->reference($here, $proto->decl_in));
			// FIXME: fix proto, must replace old
			// This proto will replace the existing function.
			//$globals->functions->remove($fqn);
		}

		$f = new Function_($fqn);
		$f->is_forward = TRUE;
		$f->is_private = $is_private;
		$f->decl_in = $here;
		$f->sign = $sign;
		$globals->functions->put($f->name, $f);

		$nested_func = $pkg->curr_func;
		$pkg->curr_func = $f;
		$pkg->scope++;
		$scanner->readSym();

		ForwardArgumentsList::parse($globals, $sign, "function");
		
		TriggersAndThrows::parse($globals, NULL, $sign);

		$globals->expect(Symbol::$sym_x_semicolon, "expected `;' after function prototype");
		$scanner->readSym();

		$globals->cleanCurrentScope();
		$pkg->scope--;
		$pkg->curr_func = $nested_func;
	}

}
