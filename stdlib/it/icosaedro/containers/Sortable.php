<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../autoload.php";

use CastException;

/**
	Objects that provide this interface can be sorted.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:04:04 $
*/
interface Sortable extends Comparable {

	/**
		Compare this object with another object. The two objects (this and
		the other) MUST be comparable because the client algorithm expects
		they are sortable, so an exception MUST be thrown if the two object
		cannot be compared because they belong to different classes.
		Note that a test like "$other instanceof __CLASS__" is not
		sufficient because $other might be an extended class; extended
		classes should override the Sortable interface, and all the objects
		must belong to this new class. To summarize, the recommended
		implementation follows:
	<pre>
	function compareTo($other)
	{
		if( $other === NULL )
			throw new CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new CastException("expected " . __CLASS__
			. " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		...here, comparison specific of this class...
		return result_of_the_comparison;
	}
	</pre>
		@param object $other The other object.
		@return int Negative, zero or positive if $this is less, equal or
		greater than $other respectively.
		@throws CastException If the other object belongs to a different
		class and cannot be compared with this.
	*/
	function compareTo($other);

}
