<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../autoload.php";

/**
	Objects that provide this interface can be compared for equality.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/03/03 16:21:50 $
*/
interface Comparable {

	/**
		Tells if this object equals the other. Comparison must be made between
		actual contents, that is, the values; different instances can carry the
		same value.

		The equality operator must be reflexive: if
		<code>$a-&gt;equals($b)</code> then also <code>$b-&gt;equals($a)</code>. This
		latter requirement cannot be guaranteed if one of the two objects
		belongs to an extended class that overrides the equals() method, so you
		should either declare this method as final or check carefully the type
		of the other object against the type of this. See also the get_glass()
		function that returns the actual name of the class to which an object
		belongs.

		Note that the contract of this method does not allow to throw
		exceptions, so you must take care to catch any exception (checked and
		unchecked) and return FALSE if the comparison cannot be made for any
		reason.
		
		<p>
		A scheleton of the typical implementation follows:
	<pre>
	/&#42;*
	 &#42; @param object $other
	 &#42; @return bool
	 &#42;/
	function equals(object $other)
	{
		if( $other === NULL )
			return FALSE;
		
		# Fast, easy test:
		if( $this === $other )
			return TRUE;

		# If they belong to different classes, cannot be
		# equal, also if the 2 classes are relatives:
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		
		$other2 = cast(__CLASS__, $other);
		
		# Here, comparison specific of the class, field by field.
		# See also the Equality::areEqual($a,$b) method. Example:

		return Equality::areEqual($this-&gt;field1, $other2-&gt;field1)
		and Equality::areEqual($this-&gt;field2, $other2-&gt;field2)
		and Equality::areEqual($this-&gt;field3, $other2-&gt;field3);
	}
	</pre>
		@param object $other The object to compare with this. Implementation
		must check the actual identity of the passed object and check if a
		comparison is actually possible.
		@return bool True if the data contained in this object equals the other
		object. False if the two objects contains different values or are
		different, not comparable classes.
	*/
	function equals($other);

}
