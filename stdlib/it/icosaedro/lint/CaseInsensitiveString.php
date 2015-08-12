<?php

namespace it\icosaedro\lint;

require_once __DIR__ . "/../../../all.php";
use it\icosaedro\containers\Hashable;
use it\icosaedro\containers\Hash;
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\containers\Printable;
use InvalidArgumentException;
use CastException;

/**
 * Case-insensitive string according to the current system locale setting.
 * An instance of this class holds a string supposedly encoded as per the
 * current locale, so that comparisons can be made with the lower-case
 * version of the string made with the standard function {@link strtolower()}.
 * Both the original form and the lower-cased form of the string are stored
 * in the object. Instances of this class are immutable.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/17 11:51:10 $
 */
class CaseInsensitiveString implements Printable, Hashable, Sortable {
	
	const NAME = __CLASS__;
	
	/**
	 * Original string.
	 * @var string
	 */
	private $s;
	
	private $hash = 0;
	
	/**
	 * Lower-case version of the string.
	 * @var string 
	 */
	private $s_low;

	/**
	 * Creates a new case-insensitive string.
	 * @param string $s Not-NULL string to store.
	 * @return void
	 * @throws InvalidArgumentException The string is NULL.
	 */
	public function __construct($s)
	{
		if( $s === NULL )
			throw new InvalidArgumentException("NULL");
		$this->s = $s;
		$this->s_low = strtolower($s);
	}
	
	
	/**
	 * Returns the original string.
	 * @return string The original string. 
	 */
	public function __toString()
	{
		return $this->s;
	}
	
	
	/**
	 *
	 * @return string 
	 */
	public function getUppercase()
	{
		return strtoupper($this->s);
	}
	
	
	/**
	 *
	 * @return string 
	 */
	public function getLowercase()
	{
		return $this->s_low;
	}
	
	
	/**
	 * Hash code, case-independent.
	 * @return int 
	 */
	public function getHash()
	{
		if( $this->hash == 0 )
			$this->hash = Hash::hashOfString($this->s_low);
		return $this->hash;
	}
	
	
	/**
	 * Compares for equality with another case-insensitive string,
	 * disregarding any difference between upper-case and lower-case
	 * letters.
	 * @param object $other
	 * @return boolean True if the other object is an instance of this class
	 * and holds the same string compared in case insensitive way.
	 */
	public function equals($other)
	{
		if($other === $this)
			return TRUE;
		if( ! ($other instanceof self) )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->s_low === $other2->s_low;
	}
	
	
	/**
	 * Case-insensitive comparison.
	 * @param object $other Another CaseInsensitiveString object.
	 * @return int Negative, zero or positive if $this is less, equal or
	 * greater than $other respectively compared in case-insensitive way.
	 * @throws CastException The other object belongs to a different
	 * class and cannot be compared with this.
	 */
	public function compareTo($other)
	{
		if( $other === NULL )
			throw new CastException("NULL");
		if( ! ($other instanceof self) )
			throw new CastException("expected " . __CLASS__
			. " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		return strcmp($this->s_low, $other2->s_low);
	}

}
