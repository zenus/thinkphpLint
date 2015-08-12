<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\lint\Where;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\UnknownType;
use it\icosaedro\lint\docblock\DocBlock;

/**
 * Variable: global, local to function or method, or formal argument.
 * Class properties and instance properties belong to another class.
 * A variable may not exist at all, existing but being not assigned, and exist
 * and being assigned. The following example illustrates these 3 cases:
 * <blockquote><pre>
 * # Here, $v still does not exist.
 * if( ... ){
 *	$v = 123; # Here, $v exists and it is assigned
 * }
 * echo $v;  # Here, $v exists but it is not assigned (error)
 * </pre></blockquote>
 * Variables come to life and exist when an assignment is made, or the variable
 * is the formal argument of function, or when the variable returns as actual
 * argument of function that returns by reference.
 * The type of the expression assigned or the type of the formal argument is
 * the type of the variable, and this type never change for all the lifetime of
 * the variable. Instead, its assignment status may change over time from
 * statement to statement depending on the execution path (see the the Flow
 * class for more about static flow analysis).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/04 12:01:38 $
 */
class Variable implements Printable, Sortable {
	
	const NAME = __CLASS__;
	
	/**
	 * Name of the variable, without leading dollar sign.
	 * @var string 
	 */
	public $name;
	
	/**
	 * Is it private to the package?
	 * @var boolean 
	 */
	public $is_private = FALSE;
	
	/**
	 * First declared here.
	 * @var Where 
	 */
	public $decl_in;
	
	/**
	 * Visibility scope.
	 * -1 = superglobal, 0 = global, 1 or more = local.
	 * @var int 
	 */
	public $scope = 0;
	
	/**
	 * Is it declared in 'global' statement? If yes, this local var refers to
	 * a global var with the same name; if used (RHS) or assigned (LHS) we must
	 * account both vars.
	 * @var boolean
	 */
	public $is_global = FALSE;
	
	/**
	 * Definitely assigned in current execution path. If set, the variable is
	 * "assigned". If not set, the variable cannot be used as RHS, that is,
	 * cannot be dereferenced by "[" nor "-&gt;", nor it can be incremented,
	 * but it still exist and it has a defined type. This flag can be set and
	 * reset according to the current state of the flow analysis.
	 * @var boolean
	 */
	public $assigned = FALSE;
	
	/**
	 * Accounts if this variable has been used as LHS (left-hand side) at least
	 * one time. Local variables bound to a global one with "global $v;" that
	 * are not used and never assigned, are reported as "unused". So, basically,
	 * this flag gets set along with the $assigned flag but gets never reset.
	 * @var boolean 
	 */
	public $assigned_once = FALSE;
	
	/**
	 * No. of usages as RHS (right-hand side).
	 * @var int
	 */
	public $used = 0;
	
	/**
	 * Type of this variable. Variables whose type cannot be determined, are
	 * UnknownType: an error was already signaled the first time the unknown
	 * value was assigned, so no more errors should be signaled later for this
	 * variable.
	 * @var Type 
	 */
	public $type;

	/**
	 * DocBlock associated, or NULL if not available.
	 * @var DocBlock 
	 */
	public $docblock;

	
	/**
	 * Builds a new, unassigned variable.
	 * @param string $name
	 * @param boolean $is_private
	 * @param Where $decl_in
	 * @param int $scope
	 * @return void
	 */
	public function __construct($name, $is_private, $decl_in, $scope) {
		$this->name = $name;
		$this->is_private = $is_private;
		$this->decl_in = $decl_in;
		$this->scope = $scope;
		$this->is_global = FALSE;
		$this->assigned = FALSE;
		$this->assigned_once = FALSE;
		$this->used = 0;
		$this->type = UnknownType::getInstance();
	}
	
	
	/**
	 * Returns the name of the variable "$name".
	 * @return string 
	 */
	public function __toString(){
		return "$" . $this->name;
	}
	
	
	/**
	 *
	 * @param object $o 
	 * @return int
	 */
	public function compareTo($o) {
		$v = cast(__CLASS__, $o);
		return strcmp($this->name, $v->name);
	}
	
	
	/**
	 * @param object $o
	 */
	public function equals($o){
		if( $this === $o )
			return TRUE;
		$v = cast(__CLASS__, $o);
		return $this->name === $v->name
		// if compareTo($o) == 0, then must also be equal:
		&& $this->decl_in->getFile()->equals($v->decl_in->getFile());
	}
	
}
