<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../all.php";

/*. require_module 'pcre'; .*/
use it\icosaedro\utils\Strings;
use it\icosaedro\utils\Integers;
use InvalidArgumentException;
use OutOfRangeException;


/**
 * Set of non-negative integer numbers. The size of the set grows dynamically
 * as more elements are entered. Bits have a position given by an index
 * that starts from 0 (the first bit). The set is open to the right, and
 * bits above the last bit set or cleared are assumed zero.  Example:
 *
 * <pre>
 *     /&#42;*
 *      * Eratosthenes sieve algorithm.
 *      * @param int $n Search prime numbers up to this limit.
 *      * @return BitSet Found prime numbers.
 *      &#42;/
 *     function primes($n)
 *     {
 *         $b = new BitSet();
 *         for($i = 2; $i &lt;= $n; $i++)
 *             $b-&gt;set($i);
 *         $i = 2;
 *         while( $i * $i &lt;= $n ){
 *             if( $b-&gt;get($i) ){
 *                 $k = 2 * $i;
 *                 while( $k &lt;= $n ){
 *                     $b-&gt;clear($k);
 *                     $k += $i;
 *                 }
 *             }
 *             $i++;
 *         }
 *         return $b;
 *     }
 *
 *     echo primes(100);
 *     ==&gt; {2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53,
 *     59, 61, 67, 71, 73, 79, 83, 89, 97}
 * </pre>
 * @version $Date: 2015/02/06 10:04:04 $
 * @author Umberto Salsi <salsi@icosaedro.it>
 */
class BitSet implements Hashable, Printable
{
	/* Allocated bits, 32 bits per int value. */
	private /*. int[int] .*/ $bits;

	/* Number of "allocated" bits. */
	private $size = 0;

	private $cached_hash = 0;
	private $cached_magnitude = 0;
	private $cached_cardinality = 0;


	/**
	 * Initializes a new empty set of bits.
	 * @return void
	 */
	function __construct()
	{
		$this->bits = /*. (int[int]) .*/ array();
	}


	/**
	 * @param int $n Number of words of 32 bits required.
	 * @return void
	 */
	private function ensureCapacity($n)
	{
		if( $n > count($this->bits) )
			for($i = count($this->bits); $i < $n; $i++)
				$this->bits[] = 0;
	}


	/**
	 * @return void
	 */
	private function resetCache()
	{
		$this->cached_hash = 0;
		$this->cached_magnitude = 0;
		$this->cached_cardinality = 0;
	}

	
	/**
	 * Sets a bit in the set to 1.
	 * @param int $bit Index of the bit. If the current size of the set
	 * is too small to hold a bit at this offset, the size of the set is
	 * increased so that the final size of the set will be exactly ($bit+1);
	 * the new bits added are set to zero.
	 * @return void
	 * @throws OutOfRangeException The argument is negative.
	 */
	function set($bit)
	{
		if( $bit < 0 )
			throw new OutOfRangeException("$bit");
		$i = $bit >> 5;
		if( $bit >= $this->size ){
			$this->ensureCapacity($i + 1);
			$this->size = $bit + 1;
		}
		$this->bits[$i] |= (1 << ($bit & 0x1f));
		$this->resetCache();
	}


	/**
	 * Resets a bit in the set.
	 * @param int $bit Index of the bit. If the current size of the set
	 * is too small to hold a bit at this offset, the size of the set is
	 * increased so that the final size of the set will be exactly ($bit+1);
	 * the new bits added are set to zero.
	 * @return void
	 * @throws OutOfRangeException The argument is negative.
	 */
	function clear($bit)
	{
		if( $bit < 0 )
			throw new OutOfRangeException("$bit");
		$i = $bit >> 5;
		if( $bit >= $this->size ){
			$this->ensureCapacity($i + 1);
			$this->size = $bit + 1;
		}
		$this->bits[$i] &= ~ (1 << ($bit & 0x1f));
		$this->resetCache();
	}


	/**
	 * Returns the status of a bit in the set. The set has only a limited
	 * size that depends on the highest index of the bit set or cleared;
	 * if the status of a bit above the size of the set is requested,
	 * FALSE is returned.
	 * @param int $bit Index of the bit.
	 * @return bool Status of the bit, that is FALSE for 0 or TRUE for 1.
	 * @throws OutOfRangeException The argument is negative.
	 */
	function get($bit)
	{
		if( $bit < 0 )
			throw new OutOfRangeException("$bit");
		if( $bit >= $this->size )
			return FALSE;
		return ($this->bits[$bit >> 5] & (1 << ($bit & 0x1f))) != 0;
	}


	/**
	 * Index of the highest bit set to true + 1. If the set
	 * is empty or there are not bits set to 1, zero is returned.
	 * @return int Magnitude of the set.
	 */
	function magnitude()
	{
		if( $this->cached_magnitude != 0 )
			return $this->cached_magnitude;

		$m = 0;
		for( $i = count($this->bits) - 1; $i >= 0; $i-- ){
			$s = $this->bits[$i];
			if( $s != 0 ){
				$m = 32 * $i + Integers::magnitude($s);
				break;
			}
		}
		$this->cached_magnitude = $m;
		return $m;
	}


	/**
	 * Number of bits set to true in this set.
	 * @return int Number of bits set to true in this set.
	 */
	function cardinality()
	{
		if( $this->cached_cardinality != 0 )
			return $this->cached_cardinality;

		$c = 0;
		for( $i = count($this->bits) - 1; $i >= 0; $i-- )
			$c += Integers::bitCount( $this->bits[$i] );
		$this->cached_cardinality = $c;
		return $c;
	}


	/**
	 * Size of the bit set used.
	 * @return int Size of the bit set. This is the highest bit index set
	 * or cleared + 1.
	 */
	function size()
	{
		return $this->size;
	}


	/**
	 * This becomes the logical AND between $this and $other.
	 * The size of this set does not change.
	 * @param self $other The other set.
	 * @return void
	 */
	function intersection($other)
	{
		$n = Integers::min( count($this->bits), count($other->bits) );
		for($i = $n - 1; $i >= 0; $i--)
			$this->bits[$i] &= $other->bits[$i];
		for($i = count($this->bits) - 1; $i >= $n; $i--)
			$this->bits[$i] = 0;
		$this->resetCache();
	}


	/**
	 * This becomes the logical OR between $this and $other.
	 * The size of this set becomes the maximum of the sizes of the
	 * two sets.
	 * @param self $other The other set.
	 * @return void
	 */
	function union($other)
	{
		$n = Integers::min( count($this->bits), count($other->bits) );
		for($i = $n - 1; $i >= 0; $i--)
			$this->bits[$i] |= $other->bits[$i];
		if( $other->size() > $this->size() ){
			for($i = count($other->bits) - 1; $i >= $n; $i--)
				$this->bits[$i] = $other->bits[$i];
			$this->size = $other->size;
		}
		$this->resetCache();
	}


	/**
	 * This becomes the logical XOR between $this and $other.
	 * The operation performed is also known as "Symmetric difference".
	 * The size of this set becomes the maximum of the sizes of the
	 * two sets.
	 * @param self $other The other set.
	 * @return void
	 */
	function reverse($other)
	{
		$n = Integers::min( count($this->bits), count($other->bits) );
		for($i = $n - 1; $i >= 0; $i--)
			$this->bits[$i] ^= $other->bits[$i];
		if( $other->size() > $this->size() ){
			for($i = count($other->bits) - 1; $i >= $n; $i--)
				$this->bits[$i] = $other->bits[$i];
			$this->size = $other->size;
		}
		$this->resetCache();
	}


	/**
	 * This becomes the logical AND NOT between $this and $other.
	 * @param self $other
	 * @return void
	 */
	function difference($other)
	{
		$n = Integers::min( count($this->bits), count($other->bits) );
		for($i = $n - 1; $i >= 0; $i--)
			$this->bits[$i] &= ~ $other->bits[$i];
		$this->resetCache();
	}


	/**
	 * Returns the hash code for this set.
	 * @return int Hash code.
	 */
	function getHash()
	{
		if( $this->cached_hash != 0 )
			return $this->cached_hash;

		$h = 1777;
		for($i = count($this->bits) - 1; $i >= 0; $i--)
			$h ^= $this->bits[$i];
		$this->cached_hash = $h;
		return $h;
	}


	/**
	 * Compares this bit set with another bit set for equality.  You may
	 * think at the result of the comparison as if the string representations
	 * of the two set as the {@link ::__toString()} method yields were
	 * compared.
	 * @param object $other The other bit set.
	 * @return bool True if the other object is a BitSet and contains the
	 * same 1 bits in the same positions. The sizes of the two sets are
	 * not compared.
	 */
	function equals($other)
	{
		if( $other === NULL )
			return FALSE;

		if( $this === $other )
			return TRUE;

		if( get_class($other) !== __CLASS__ )
			return FALSE;
		
		$other2 = cast(__CLASS__, $other);

		$this_m = $this->magnitude();
		$other2_m = $other2->magnitude();
		if( $this_m != $other2_m )
			return FALSE;

		if( $this_m == 0 )
			# Both sets are empty.
			return TRUE;

		$i = $this_m >> 5;
		do {
			if( $this->bits[$i] != $other2->bits[$i] )
				return FALSE;
			$i--;
		} while( $i >= 0 );
		return TRUE;
	}


	/**
	 * Returns the string representation of the bit set.  See also the
	 * {@link ::parse()} method that perform the inverse conversion.
	 * @return string List of integer numbers that give the indices of the
	 * bits that are set; indices are separated by commas and the list is
	 * enclosed between braces.  Example: <code>{0, 7, 34}</code>.
	 */
	public function __toString()
	{
		$res = "{";
		$n = 0;
		for($bit = 0; $bit < $this->size; $bit++){
			if( $this->get($bit) ){
				if( $n > 0 ) $res .= ", ";
				$res .= "$bit";
				$n++;
			}
		}
		return $res . "}";
	}


	/**
	 * Parses a bit set as generated by {@link ::__toString()}.
	 * @param string $s Bit set as generated by {@link ::__toString()},
	 * for example "{1, 2, 7}". Spaces and horizontal tabulators are ignored.
	 * @return BitSet The parsed bit set.
	 * @throws InvalidArgumentException Invalid syntax. Integer value too
	 * big to be represented as int under this platform.
	 */
	public static function parse($s)
	{
		$sp = "[ \t]*";
		$nn = "[0-9]+";
		if( preg_match("/^$sp\\{"."$sp($nn$sp(,$sp$nn$sp)*)\\}$sp\$/", $s) !== 1 )
			throw new InvalidArgumentException($s);
		$s = Strings::replace($s, " ", "");
		$s = Strings::replace($s, "\t", "");
		$s = Strings::substring($s, 1, strlen($s) - 1);
		$a = explode(",", $s);
		$bits = new BitSet();
		for( $i = count($a) - 1; $i >= 0; $i-- )
			$bits->set( Integers::parseInt($a[$i]) );
		return $bits;
	}

}
