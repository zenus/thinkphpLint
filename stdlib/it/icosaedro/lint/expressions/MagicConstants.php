<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Constant;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Result;
use RuntimeException;

/**
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/08/13 18:33:23 $
 */
class MagicConstants {

	/**
	 * 
	 * @param Globals $globals
	 * @param Constant $c One of the magic constants that follow:
	 * __DIR__, __FILE__, __LINE__, __NAMESPACE__, __FUNCTION__,
	 * __CLASS__, __METHOD__.
	 * @return Result Current value of the magic constant.
	 */
	public static function resolve($globals, $c)
	{
		$pkg = $globals->curr_pkg;

		switch( $c->__toString() ){

		case "__DIR__":
			if( $globals->isPHP(4) )
				$globals->logger->error($pkg->scanner->here(),
				"no magic $c constant (PHP 5)");
			return Result::factory(Globals::$string_type,
				$pkg->fn->getParentFile()->__toString());

		case "__FILE__":
			return Result::factory(Globals::$string_type,
				$pkg->fn->__toString());

		case "__LINE__":
			return Result::factory(Globals::$int_type,
				"".$pkg->scanner->here()->getLineNo());

		case "__NAMESPACE__":
			if( $globals->isPHP(4) )
				$globals->logger->error($pkg->scanner->here(),
				"no magic $c constant (PHP 5)");
			return Result::factory(Globals::$string_type,
				$pkg->resolver->name);

		case "__FUNCTION__":
			if( $pkg->curr_func === NULL ){
				return Result::factory(Globals::$string_type, "");
			} else {
				if( $globals->isPHP(4) )
					return Result::factory(Globals::$string_type,
						strtolower( $pkg->curr_func->name->__toString() ));
				else
					return Result::factory(Globals::$string_type,
						$pkg->curr_func->name->__toString());
			}

		case "__CLASS__":
			if( $pkg->curr_class === NULL ){
				return Result::factory(Globals::$string_type, "");
			} else {
				if( $globals->isPHP(4) )
					return Result::factory(Globals::$string_type,
						strtolower( $pkg->curr_class->name->__toString() ));
				else
					return Result::factory(Globals::$string_type,
						$pkg->curr_class->name->__toString());
			}

		case "__METHOD__":
			if( $globals->isPHP(4) )
				$globals->logger->error($pkg->scanner->here(),
				"no magic $c constant (PHP 5)");
			if( $pkg->curr_method === NULL ){
				return Result::factory(Globals::$string_type, "");
			} else {
				return Result::factory(Globals::$string_type,
					$pkg->curr_method->name->__toString());
			}

		default:
			throw new RuntimeException("not a magic constant: $c");
		}

	}

}
