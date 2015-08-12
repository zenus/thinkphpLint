<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../autoload.php";

use \CastException;

/**
	Generic interface that defines a sorter object among the objects
	of a given set. Sorting algorithms may then use such an object
	to establish an ordering between generic objects. For objects that
	already implements the Sortable interface, a sorter object allows
	to implement even more specialized sorting criteria beside that
	these objects already implement. See for example the
	Arrays::sortBySorter() method.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/02/09 10:11:26 $
*/
interface Sorter {

	/**
		Compare two objects.
		@param object $a The first object.
		@param object $b The second object.
		@return int Negative, zero or positive if $a is less, equal or
		greater than $b respectively.
		@throws CastException If $a or $b does not belong to the expected
		class or extended class.
	*/
	function compare($a, $b);

	/* *
		Tells if the $this object equals the $other.
		The simplest implementation might be:
	<pre>
	function equals($other)
	{
		try {
			$other2 = cast(__CLASS__, $other);
			return $this->compare($this, $other2) == 0;
		}
		catch(CastException $e){
			return FALSE;
		}
	}
	</pre>
		Note that the contract of the interface does not allow to throw
		exceptions, so we return FALSE if, for any reason, the two objects
		cannot be compared.
		@param object $other The other object.
		@return bool True if the two objects are equal.
	function equals($other);
	*/

}
