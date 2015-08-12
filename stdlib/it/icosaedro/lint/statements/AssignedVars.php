<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\containers\BitSet;

/**
 * Set of assigned variables. An instance of this class holds a snapshot of
 * the current variables that are assigned, where the offset of the bit is
 * the index to the global array <code>vars[]</code> and the bit saves the
 * assignement status of the variable.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:34 $
 */
class AssignedVars extends BitSet {
	
	/**
	 * Global context with the list of currently existing variables.
	 * @var Globals
	 */
	private $globals;
	
	/**
	 * Takes a snapshot of the set of currently assigned variables. Each bit
	 * offset corresponds to an entry of the <code>vars[]</code> array in the
	 * global context containing an assigned variable.
	 * @param Globals $globals 
	 * @return void
	 */
	public function __construct($globals)
	{
		parent::__construct();
		$this->globals = $globals;
		$vars = $globals->vars;
		for($i = $globals->vars_n - 1; $i >= 0; $i--)
			if( $vars[$i] !== NULL && $vars[$i]->assigned )
				$this->set($i);
	}
	
	
	/**
	 * Restores the assignment status of the variables according to this set.
	 * @return void
	 */
	public function restore()
	{
		$vars = & $this->globals->vars;
		$vars_n = $this->globals->vars_n;
		$max = $this->magnitude();
		for($i = 0; $i < $max; $i++)
			if( $vars[$i] !== NULL )
				$vars[$i]->assigned = $this->get($i);
		for($i = $max; $i < $vars_n; $i++)
			if( $vars[$i] !== NULL )
				$vars[$i]->assigned = FALSE;
	}
	
}
