<?php

namespace it\icosaedro\lint;

require_once __DIR__ . "/../../../all.php";


/**
 * Holds the status of the current namespace and resolves identifiers and
 * qualified names into fully qualified names. The methods <code>open()</code>
 * and <code>close()</code> must be called entering and exiting from a
 * namespace section of the source code; the <code>addUse()</code> method
 * must be called for each <code>use</code> statement encountered.
 * 
 * <p>
 * A name, here, is a sequence of one or more identifiers separated by
 * back-slash. If the name starts with a back-slash, then it is absolute and
 * no resolution is needed because the fully qualified name is simply
 * that absolute name with the leading back-slash dropped. Example:
 * <code>\strlen</code> resolves into the well known function
 * <code>strlen</code>. For qualified names and bare identifiers, the
 * resolution algorithm differs for functions and classes -- see the
 * <code>resolve()</code> method for more.
 *
 * <p>
 * <b>Name:</b> a sequence of one or more identifiers spearated by "\",
 * possibly with a leading back-slash:
 * 
 * <blockquote><pre>
 * name = ["\"] identifier { "\" identifier };
 * </pre></blockquote>
 *
 * <b>Qualified name:</b> two or more identifiers separated by "\":
 *
 * <blockquote><pre>
 * qualified_name = identifier "\" identifier { "\" identifier };
 * </pre></blockquote>
 *
 * <b>Absolute name:</b> a name starting with a "\":
 *
 * <blockquote><pre>
 * absolute_name = "\" identifier { "\" identifier };
 * </pre></blockquote>
 * 
 * <b>Fully qualified name:</b> identifier or qualified name that identifies
 * univocally an item:
 *
 * <blockquote><pre>
 * fully_qualified_name = identifier | qualified_name;
 * </pre></blockquote>
 * 
 * For example, <code>it\icosaedro\bignumbers\BigInt</code> is the FQN of a
 * class that might also be written in several shorter forms like
 * <code>bignumbers\BigInt</code> (qualified name) or even only
 * <code>BigInt</code>. Resolution of these shorter forms into a FQN is just
 * the main task of this class.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:48:04 $
 */
class NamespaceResolver {

	/**
	 * Absolute name of this namespace, for example "it\icosaedro\lint";
	 * empty string for the global ns.
	 * @var string
	 */
	public $name;

	/**
	 * List of `use' clauses.
	 * @var UseEntry[int]
	 */
	public $namespace_use;

	/**
	 * Returns TRUE if the name is absolute. Absolute names start with a
	 * back-slash.
	 * @param string $name Name.
	 * @return boolean 
	 */
	public static function isAbsolute($name) {
		return strlen($name) > 1 && $name[0] === "\\";
	}

	/**
	 * Returns TRUE if name is a qualified name. Qualified names are two or
	 * more identifiers separated by back-slash.
	 * @param string $name Name.
	 * @return boolean TRUE if name is a qualified name.
	 */
	public static function isQualified($name) {
		$back = strpos($name, "\\");
		return $back !== FALSE && $back > 0;
	}

	/**
	 * Returns TRUE if name is a bare identifier.
	 * @param string $name Name.
	 * @return boolean 
	 */
	public static function isIdentifier($name) {
		return strpos($name, "\\") === FALSE;
	}
	
	
	/**
	 * @return void 
	 */
	private function setGlobalNS(){
		$this->name = "";
		$this->namespace_use = /*.(UseEntry[int]).*/ array();
	}
	
	
	/**
	 * Closes current namespace. Call this method at the end of a
	 * <code>namespace NS { }</code> compound or at the end of the package.
	 * Restores the resolver to the global namespace.
	 * Also reposts unused <code>use</code> statements.
	 * If the current NS was already closed, does nothing.
	 * @param Logger $logger
	 * @return void
	 */
	public function close($logger){
		// Reports unused `use' statements:
		if( $logger->print_notices ){
			foreach ($this->namespace_use as $u) {
				if ($u->used == 0) {
					$logger->notice($u->decl_in, "unused clause `use "
					. $u->target . " as " . $u->alias . "'");
				}
			}
		}
		
		$this->setGlobalNS();
	}

	/**
	 * Raises an error if another NS was defined that differs only by
	 * lowercase and uppercase letters.
	 * @param string $ns Fully qualified name, qualified name or identifier;
	 * @return void
	 */
	private static function checkSpelling($ns) {
		if( $ns === "" )
			return;
		if (self::isAbsolute($ns)) {
			$ns = substr($ns, 1);
		}
		$ns_lower = strtolower($ns);
		$path = $ns_lower . "\\";

		// FIXME: todo

//	# Search between constants:
//	FOR i = 0 TO count(consts)-1 DO
//		 if( str.starts_with(consts[i][name_normalized], path)  ){
//			 if( NOT str.starts_with(consts[i][name], ns)  ){
//				Error("namespace was declared `"
//				. consts[i][name][0,length(ns)]
//				. "' near " + reference(consts[i][decl_in])
//				. " that differ by upper/lower-case letters only");
//			 }
//			return
//		 }
//	 }
//
//	# Search between functions:
//	FOR i = 0 TO count(funcs)-1 DO
//		 if( str.starts_with(funcs[i][name_lower], path)  ){
//			 if( NOT str.starts_with(funcs[i][name], ns)  ){
//				Error("namespace was declared `"
//				. funcs[i][name][0,length(ns)]
//				. "' near " + reference(funcs[i][decl_in])
//				. " that differ by upper/lower-case letters only");
//			 }
//			return;
//		 }
//	 }
//
//	# Search between classes:
//	FOR i = 0 TO count(classes)-1 DO
//		 if( (classes[i][name_lower] = ns_lower)
//		OR str.starts_with(classes[i][name_lower], path)  ){
//			 if( NOT str.starts_with(classes[i][name], ns)  ){
//				Error("namespace was declared `"
//				. classes[i][name][0,length(ns)]
//				. "' near " + reference(classes[i][decl_in])
//				. " that differ by upper/lower-case letters only");
//			 }
//			return;
//		 }
//	 }
	}
	
	
	/**
	 * Opens a new namespace. Call this method at the beginning of a
	 * <code>namespace NS { }</code> compound, or on the
	 * <code>namespace NS;</code> statement.
	 * @param string $name The new namespace.
	 * @return void
	 */
	public function open($name){
		if( $this->name !== "" )
			throw new \RuntimeException("unclosed namespace: " . $this->name);
		self::checkSpelling($name);
		$this->setGlobalNS();
		$this->name = $name;
	}
	
	
	/**
	 * Creates a new namespace resolver in global namespace.
	 * @return void
	 */
	public function __construct(){
		$this->setGlobalNS();
	}
	

	/**
	 * Returns TRUE if we are inside a namespace.
	 * @return boolean TRUE if we are inside a namespace.
	 */
	public function inNamespace() {
		return $this->name !== "";
	}

	/**
	 * Makes absolute the identifier according to the current
	 * namespace. For example, if the current namespace is a\b then
	 * absolute("f") ==&gt; "a\b\f"; if the current namespace is global
	 * then simply returns id.
	 * @param string $id Identifier.
	 * @return string
	 */
	public function absolute($id) {
		if( $this->name === "" )
			return $id;
		else
			return $this->name ."\\". $id;
	}

//	/**
//	 * Displays an error if the ID contains at least a \, that is is not
//	 * a bare ID. Returns a valid, quite arbitrarily built, bare ID just to
//	 * continue parsing.
//	 * @param string $id Identifier.
//	 * @return string
//	 */
//	public static function checkBareID($id) {
//		if (self::isIdentifier($id)) {
//			return $id;
//		}
//		Package::$curr->scanner->here()->error("qualified identifier not allowed in declaration");
//		return (string) str_replace("\\", "_", $id);
//	}

	/**
	 * Add `use TARGET as ALIAS' clause to the current namespace.
	 * @param string $target Target NS (identifier or qualified name).
	 * @param string $alias ID of the alias NS; if NULL, the last identifier of
	 * the NS is assumed instead.
	 * @param Where $where Location of the 'use' statement.
	 * @return void
	 */
	public function addUse($target, $alias, $where) {
		// FIXME: TARGET in global NS must be absolute? check
		// FIXME: check duplicated use statement as it causes a fatal error
		if( self::isAbsolute($target) )
			$target = substr($target, 1);
		$u = new UseEntry($target, $alias, $where);
		$this->namespace_use[] = $u;
	}

	/**
	 * If the name is absolute, return the normalized fully qualified name
	 * (that is, the leading \ is removed).
	 * Otherwise, apply current `use' clauses to the non-absolute name.
	 * Only for classes, the `use' target is applied also to bare identifiers,
	 * not only to the leading identifier.
	 * Only the first matching `use' clause is applied.
	 * Inside a NS, if no `use' clause applies, then the current NS is
	 * prepended and the resulting fully qualified name is returned.
	 * Otherwise, simply returns the name.
	 * Example:
	 * 
	 * <blockquote><pre>
	 * namespace myns;
	 * use a\b\c as z;
	 * </pre></blockquote>
	 * 
	 * then
	 * 
	 * <blockquote><pre>
	 * resolve("\\x\\y", ?) ==&gt; "x\\y"
	 * resolve("z", FALSE) ==&gt; "z"
	 * resolve("z", TRUE) ==&gt; "a\\b\\c"
	 * resolve("z\\x", ?) ==&gt; "a\\b\\c\\x"
	 * resolve("f", ?) ==&gt; "myns\\f"
	 * </pre></blockquote>
	 * 
	 * @param string $name
	 * @param boolean $is_const Is it a constant? From this flag depends the
	 * internal normalization of the name, because the name part of a constant
	 * is case-sensitive; for functions and classes the whole name is
	 * case-insensitive.
	 * @param boolean $is_class True to resolve a class name, false to resolve
	 * a function name.
	 * @return FullyQualifiedName
	 */
	private function resolve($name, $is_const, $is_class) {
		// FIXME: original name was "applyUse()".
		$fqn = /*.(string).*/ NULL;
		if (self::isAbsolute($name)) {
			$fqn = substr($name, 1);
			
		} else if (self::isQualified($name)) {
			$i = strrpos($name, "\\");
			$leading = substr($name, 0, $i);
			foreach ($this->namespace_use as $u) {
				if ($u->alias === $leading) {
					$u->used++;
					$fqn = $u->target ."\\". substr($name, $i);
					break;
				}
			}
			
		} else if ($is_class) {
			// Bare ID of class.
			foreach ($this->namespace_use as $u) {
				if ($u->alias === $name) {
					$u->used++;
					$fqn = $u->target;
					break;
				}
			}
			
		}
		
		if( $fqn === NULL )
			$fqn = $this->absolute($name);
		
		return new FullyQualifiedName($fqn, $is_const);
	}
	

	/**
	 * Resolves a constant name into a FQN according to the current namespace
	 * and current <code>use</code> statements.
	 * @param string $name Absolute, qualified or bare identifier.
	 * @return FullyQualifiedName FQN of the constant.
	 */
	public function resolveConstant($name) {
		return $this->resolve($name, TRUE, FALSE);
	}
	

	/**
	 * Resolves a function name into a FQN according to the current namespace
	 * and current <code>use</code> statements.
	 * @param string $name Absolute, qualified or bare identifier.
	 * @return FullyQualifiedName FQN of the function.
	 */
	public function resolveFunction($name) {
		return $this->resolve($name, FALSE, FALSE);
	}
	

	/**
	 * Resolves a class name into a FQN according to the current namespace
	 * and current <code>use</code> statements.
	 * @param string $name Absolute, qualified or bare identifier.
	 * @return FullyQualifiedName FQN of the class.
	 */
	public function resolveClass($name) {
		return $this->resolve($name, FALSE, TRUE);
	}

}
