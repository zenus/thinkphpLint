<?php

namespace it\icosaedro\lint;

require_once __DIR__ . "/../../../all.php";

//use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\ClassType;

/**
 * Class name resolver. Only the Globals class implements this interface, but
 * passing that class here and there in the code would create huge circular
 * depencies PHPLint cannot resolve. An interface breaks these circular
 * dependencies.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/09 19:33:27 $
 */
interface ClassResolver {

	/**
	 * Seach a class, resolving its name in the current namespace.
	 * @param string $name Name as found in the source, that might need to
	 * be resolved in the current namespace context.
	 * @param boolean $is_fqn If true, assumes the name be already fully
	 * qualified or absolute and does not applies the namespace resolution
	 * algorithm. Set to true only to resolve classes in the magic
	 * <code>cast(T,V)</code>, where <code>T</code> must be resolvable
	 * at runtime outside the current namespace context.
	 * @return ClassType Resolved class, or NULL if not found.
	 */
	public function searchClass($name, $is_fqn = FALSE);
	

	/**
	 * Accounts class usage, checks visibility context and deprecation.
	 * @param ClassType $c
	 * @return void
	 */
	public function accountClass($c);

}
