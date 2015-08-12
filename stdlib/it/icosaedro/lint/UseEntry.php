<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\lint\Where;
use it\icosaedro\containers\Printable;

/**
 * Holds an `use' statement entry `use TARGET as ALIAS;'.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/08 14:58:04 $
 */
class UseEntry implements Printable {

	/**
	 * Target namespace without leading \, as in `use TARGET as ALIAS'.
	 * @var string
	 */
	public $target;

	/**
	 * Alias argument of the "use" statement. If missing from the "use"
	 * statement, it is the last identifier of the target..
	 * @var string
	 */
	public $alias;

	/**
	 * How many times this entry has been used.
	 * @var int
	 */
	public $used = 0;

	/**
	 * Location of this `use' statement.
	 * @var Where 
	 */
	public $decl_in;
	
	
	/**
	 * Creates a new "use TARGET as ALIAS" entry.
	 * @param string $target Target name.
	 * @param string $alias Optional target name. If NULL, the last identifier
	 * of the target is assumed instead.
	 * @param Where $decl_in Location of the "use" statement.
	 * @return void
	 */
	public function __construct($target, $alias, $decl_in){
		$this->target = $target;
		if( $alias === NULL ){
			$trail_id_idx = strrpos($target, "\\");
			if( $trail_id_idx === FALSE )
				$alias = $target;
			else
				$alias = substr($target, $trail_id_idx + 1);
		}
		$this->alias = $alias;
		$this->used = 0;
		$this->decl_in = $decl_in;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function __toString(){
		// FIXME: TARGET in global NS must be rendered as abs? check
		return "use ". $this->target ." as ". $this->alias .";";
	}

}
