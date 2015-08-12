<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Comparable;

/**
 * Abstract base class of any type defined under PHPLint.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:52:46 $
 */
abstract class Type implements Printable, Comparable {
	
	
	/**
	 * Returns true if a value of this type can be assigned to a variable of
	 * type specified.
	 * @param Type $lhs Type of the assigned variable.
	 * @return boolean True if a value of this type can be assigned to a
	 * variable of type specified.
	 */
	public abstract function assignableTo($lhs);
	
	
}
