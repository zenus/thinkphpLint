<?php

namespace it\icosaedro\lint;

require_once __DIR__ . "/../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\ClassMethod;

/**
 * Reports unused private items, unused packages and required packages.
 * The only public static function provided should be called at the end of the
 * PHPLint program. Note that the report includes all the packages parsed, so
 * if the command line with which the program was invoked contains 2 or more
 * packages to parse, then the report involves all the packages required.
 * (FIXME: Last statement is false: support for platform-dependend int size
 * 32 or 64 bits is still missing; no int nor float overflow implemented yet;
 * INF and NAN are not supported yet and may give unexpected results).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:14:07 $
 */
class Report {

	/**
	 * Reports undefined and unused constants.
	 * @param Globals $globals
	 * @return void
	 */
	private static function reportUnusedPrivateConstants($globals) {
		$logger = $globals->logger;
		foreach ($globals->constants as $c) {
			$co = cast(Constant::NAME, $c);
			if ($co->is_private && $co->used == 0
			&& $logger->main_file_name->equals($co->decl_in->getFile())) {
				$logger->notice($co->decl_in,
				"unused private constant " . $co->name);
			}
		}
	}

	/**
	 * Reports undefined and unused vars.
	 * @param Globals $globals
	 * @return void
	 */
	private static function reportUnusedPrivateVariables($globals) {
		$logger = $globals->logger;
		for($i = $globals->vars_n - 1; $i >= 0; $i--){
			$v = $globals->vars[$i];
			if ($v !== NULL && $v->is_private && $v->used == 0
			&& $logger->main_file_name->equals($v->decl_in->getFile())) {
				$logger->notice($v->decl_in,
				"unused global private variable $v");
			}
		}
	}

	/**
	 * Reports undefined and unused funcs.
	 * @param Globals $globals
	 * @return void
	 */
	private static function reportUnusedPrivateFunctions($globals) {
		$logger = $globals->logger;
		foreach($globals->functions as $m){
			$f = cast(Function_::NAME, $m);
			if ($f->is_private && $f->used == 0
			&& $logger->main_file_name->equals($f->decl_in->getFile())) {
				$logger->notice($f->decl_in,
				"unused private function $f");
			}
		}
	}

	/**
	 * Reports undefined and unused classes, class constants, class properties,
	 * class methods.
	 * @param Globals $globals
	 * @return void
	 */
	private static function reportUnusedPrivateClasses($globals) {
		$logger = $globals->logger;
		foreach($globals->classes as $class_){
			$c = cast(ClassType::NAME, $class_);
			if( ! $logger->main_file_name->equals($c->decl_in->getFile()) )
				continue;
			if ($c->is_private && $c->used === 0) {
				$logger->notice($c->decl_in,
				"unused class " . $c->name);
			} else {

				foreach ($c->constants as $co) {
					if ($co->visibility === Visibility::$private_ && $co->used == 0) {
						$logger->notice($co->decl_in,
						"unused private constant $co");
					}
				}

				foreach ($c->properties as $p) {
					if ($p->visibility === Visibility::$private_ && $p->used == 0){
						$logger->notice($p->decl_in,
						"unused private property $p");
					}
				}

				foreach ($c->methods as $mm) {
					$m = cast(ClassMethod::NAME, $mm);
					if ($m->visibility === Visibility::$private_ && $m->used == 0) {
						$logger->notice($m->decl_in,
						"unused private method $m");
					}
				}
			}
		}
	}

	/**
	 * @param Globals $globals
	 * @return void
	 */
	private static function reportUnusedAndRequiredPackages($globals) {
		$logger = $globals->logger;
		// Report unused packages:
		foreach ($globals->packages as $p) {
			$pkg = cast(Package::NAME, $p);
			if ( $pkg->used == 0 && ! $pkg->fn->equals($logger->main_file_name) ) {
				if ($pkg->is_module)
					$logger->notice(NULL, "unused module "
						. $pkg->fn->getName()->toASCII());
				else
					$logger->notice(NULL, "unused package "
						. $logger->formatFileName($pkg->fn));
			}
		}

		// Report required packages:
		foreach ($globals->packages as $p) {
			$pkg = cast(Package::NAME, $p);
			if ($pkg->used > 0  && ! $pkg->fn->equals($logger->main_file_name) ) {
				if ($pkg->is_module)
					$logger->notice(NULL, "required module "
					. $pkg->fn->getName()->toASCII());
				else
					$logger->notice(NULL, "required package "
					. $logger->formatFileName($pkg->fn));
			}
		}
	}
	

	/**
	 *
	 * @param Globals $globals
	 * @return void 
	 */
	public static function reportUndeclaredUnusedRequired($globals) {
		if (!$globals->report_unused || ! $globals->logger->print_notices)
			return;
		self::reportUnusedPrivateConstants($globals);
		self::reportUnusedPrivateVariables($globals);
		self::reportUnusedPrivateFunctions($globals);
		self::reportUnusedPrivateClasses($globals);
		self::reportUnusedAndRequiredPackages($globals);
	}

}