<?php

namespace it\icosaedro\containers;

require_once __DIR__ . '/../../../autoload.php';

/**
	Objects that may be compared for equality and that may be hashed
	should implement this interface. Hashing allows to implement fast
	algorithms of data search, like the HashSet and the HashMap.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/03/11 10:07:26 $
*/
interface Hashable extends Comparable {

	/**
		Returns the hash of the object.
		The hash value must be consistent with the equality test, that is if
		two distinct objects carry the same value, then the resulting hash
		must be the same: if <code>$a-&gt;equals($b)</code> then
		<code>$a-&gt;getHash() === $b-&gt;getHash()</code> must be true as well.
		Moreover, the objects, once defined, must be immutable, so that
		the hash of a given object never changes.
		@return int The hash.
	*/
	function getHash();

}
