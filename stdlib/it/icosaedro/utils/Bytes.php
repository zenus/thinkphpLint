<?php
/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
.*/

namespace it\icosaedro\utils;

require_once __DIR__ . '/../../../all.php';

use OutOfRangeException;
use it\icosaedro\containers\Sortable;
use it\icosaedro\containers\Hashable;
use it\icosaedro\containers\Hash;
use it\icosaedro\containers\Printable;

/**
	Immutable array of bytes that wraps a PHP string. The main difference
	between an object of this class and an ordinary PHP string is that this
	object calculates and stores once for all its own hash value. Moreover,
	several methods are simply wrappers over the str*() set of PHP standard
	functions, but with a more consistent, intuitive and safer interface.

	The name of this class is a generic "Bytes" because a more appropriate
	"String" cannot be used (it is a keyword) and, anyway, PHP strings are much
	more bare arrays of bytes without any specific encoding than real strings
	of characters. In this document the terms "string" and "array of bytes"
	or even simply "bytes" are then assumed synonym terms.

	New instances must be created through the factory method:

	<pre>
	use it\icosaedro\utils\Bytes;

	/&#42;. Bytes .&#42;/ function BF(/&#42;. string .&#42;/ $s) {
		return Bytes::factory($s);
	}

	$msg = BF("Bye world!");
	$bye = BF("Bye");
	$hello = BF("Hello");
	if( $msg-&gt;startsWith($bye) )
		$msg = $msg -&gt;remove(0, $bye-&gt;length()) -&gt;insert($hello, 0);
	echo $msg;
	# ==&gt; Hello world!
	</pre>

	The factory method takes care to do some useful optimizations saving memory
	by caching shorter strings.

	Missing features: toUpper(), toLower(), regular expressions.
	Search, replace and comparison case-insensitive.

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:20:37 $
*/
final class Bytes implements Printable, Sortable, Hashable, \Serializable {

	/* The array of bytes, immutable. */
	private /*. string .*/ $s;
	/* Hash of $s, calculated once for all if required. */
	private /*. int .*/ $s_hash = -1;


	private /*. void .*/ function __construct(/*. string .*/ $s)
	{
		if( $s === NULL )
			$s = "";
		$this->s = $s;
	}


	/**
		Builds a new Bytes object out of a string. Shorter strings (currently
		those of length 0 or 1 bytes) are cached internally to save memory
		space. Allows "fluid interface" programming Bytes::factory("abc")-&gt;...
		@param string $s The string to wrap inside an object.
		@return self Object that wraps this string. If the string is NULL, NULL
		is returned.
	*/
	static function factory($s)
	{
		/* Cached empty string. */
		static /*. self .*/ $empty;
		/* Cached single-byte strings; index is the byte. */
		static $bytes = /*. (self[]) .*/ array();

		if( $s === NULL )
			return NULL;

		if( strlen($s) == 0 ){
			if( $empty === NULL )
				$empty = new self("");
			return $empty;

		} else if( strlen($s) == 1 ){
			$i = ord($s);
			if( ! array_key_exists($i, $bytes) )
				$bytes[$i] = new self($s);
			return $bytes[$i];


		} else {
			return new self($s);
		}
	}


	/**
	 * Returns the number of bytes in this array.
	 * @return int Number of bytes in this array.
	 */
	function length()
	{
		return strlen($this->s);
	}


	/**
	 * Returns the byte at a specified index.
	 * @param int $i Index of the byte. The first byte has index 0.
	 * @return int Number in [0,255].
	 * @throws OutOfRangeException The index is either &lt; 0 or
	 * &gt; $this-&gt;length().
	 */
	function getByte($i)
		/*. throws OutOfRangeException .*/
	{
		if( $i < 0 || $i >= strlen($this->s) )
			throw new OutOfRangeException("" . $i);
		return ord($this->s[$i]);
	}


	/**
	 * Returns a PHP-compliant, pure ASCII, literal string in double-quotes.
	 * Useful to display arbitrary strings that may contain control
	 * characters or invalid encoding.
	 * All the ASCII control characters and the <code>$ \ "</code> characters
	 * are converted to the form \xxx, where xxx is the octal code of the
	 * byte, with the exception of the usual control characters as LF, CR etc.
	 * that are rendered as escape sequences "\n", "\r" etc.
	 * Example:
	 * <pre>
	 * echo Bytes::factory("abc\n")-&gt;toLiteral();
	 * # ==&gt; "abc\n"
	 * </pre>
	 * @return string PHP, pure ASCII, literal string.
	 */
	function toLiteral()
	{
		return "\"" . addcslashes($this->s, "\000..\037\\\$\"\177..\377") . "\"";
	}


	/**
	 * Returns the bytes as a PHP string.
	 * @return string The bytes. Since this is an arbitrary sequence
	 * of bytes without any particular encoding, consider to call {@link
	 * self::toLiteral()} instead in order to get a readable result whatever
	 * the binary content might be.
	 */
	function __toString()
	{
		return $this->s;
	}
		

	/**
	 * Compares two strings based on their binary content.
	 * Non locale-aware: bytes are compared left to right according
	 * to their value in [0..255].
	 * @param object $other The other array of bytes to compare with.
	 * @return int  Less that 0 if $a &lt; $b, greater than zero if $a &gt; $b,
	 * zero if the two strings are equal.
	 * @throws \CastException The other object is NULL or is not exactly
	 * instance of this class.
	 */
	function compareTo($other)
	{
		if( $other === NULL )
			throw new \CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new \CastException(get_class($other));
		$other2 = cast(__CLASS__, $other);
		# strcmp() is implemented with C memcmp() since
		# PHP 5.0 (2004), so it is not locale aware: good.
		return strcmp($this->s, $other2->s);
	}


	/**
	 * Appends bytes to the end of these bytes.
	 * @param self $other Another array of bytes.
	 * @return self The array of bytes resulting appending the other to this.
	 */
	function append($other)
	{
		return self::factory($this->s . $other->s);
	}


	/**
	 * Return a subrange of this string. The NULL string has no subranges.
	 * The valid range of the indexes is:
	 * <code>0 &le; $a &le; $b &le; strlen($s)</code> Example:
	 * <p><center>{@img ./Bytes-substring.gif}</center><p>
	 * <pre>
	 *     $s = new Bytes("ABCDEFG");
	 *     $s-&gt;substring(0, 4) ==&gt; "ABCD"
	 *     $s-&gt;substring(2, 2) ==&gt; ""
	 * </pre>
	 * @param int $a Beginning of the range.
	 * @param int $b End of the range.
	 * @return self  The substring, whose length is exactly ($b-$a)
	 * bytes. Note that if $a==$b, then the empty string is returned.
	 * @throws OutOfRangeException  If the range is invalid.
	 */
	function substring($a, $b)
	{
		return new self( Strings::substring($this->s, $a, $b) );
	}


	/**
	 * Removes a range of bytes from this string. For example, if
	 * this string is "ABCD", removing the range (1,3) yields "AD";
	 * instead, remove($i,$i+1) removes one byte at index $i.
	 * @param int $a Beginning of the range.
	 * @param int $b End of the range.
	 * @return self This string with the substring removed. The resulting
	 * string is ($b-$a) bytes shorter than this. Note that the string
	 * removed is just $this-&gt;substring($a,$b).
	 * @throws OutOfRangeException Invalid range: must be 0 &le; $a &le; $b &le;
	 * $this-&gt;length().
	 */
	function remove($a, $b)
	{
		return $this->substring(0, $a)
		->append( $this->substring($b, $this->length()) );
	}


	/**
	 * Inserts a string in this string at a given position.
	 * @param self $s String to insert.
	 * @param int $at Index position.
	 * @return self This string with the given string inserted at the
	 * given position. The resulting string is exactly $this-&gt;length()
	 * + $s-&gt;length() bytes long.
	 * @throws OutOfRangeException Invalid range: must be 0 &le; $at &le;
	 * $this-&gt;length().
	 */
	function insert($s, $at)
	{
		return $this->substring(0, $at)
		->append($s)
		->append( $this->substring($at, $this->length()) );
	}


	/**
	 * Returns TRUE if $this starts with $head.
	 * @param self $head The expected beginning of this.
	 * @return boolean True if $this starts with $head. The empty string is
	 * the beginning of any string. Any string starts with itself.
	 */
	function startsWith($head)
	{
		return Strings::startsWith($this->s, $head->s);
	}


	/**
	 * Returns TRUE if $this ends with $tail.
	 * @param self $tail The expected tail of this.
	 * @return boolean True if $this ends with $tail. The empty string is
	 * the end of any string. Any string ends with itself.
	 */
	function endsWith($tail)
	{
		return Strings::endsWith($this->s, $tail->s);
	}


	/**
	 * Returns the starting position of the first occurrence of the target
	 * string in this string.
	 * @param self $target Target substring to search. The empty string can
	 * always be found at the very beginning of the search, so $from is
	 * returned.
	 * @param int $from Search target in the range [$from,$this-&gt;length()]
	 * of this string.
	 * @return int Index of the beginning first matching target, or -1 if
	 * not found.
	 * @throws OutOfRangeException If $from outside [0,$this-&gt;length()].
	 */
	function indexOf($target, $from = 0)
	{
		return Strings::indexOf($this->s, $target->s, $from);
	}


	/**
	 * Returns the starting position of the last occurrence of the target
	 * string in this string.
	 * @param self $target Target substring to search. The empty string
	 * can always be found at the beginning of the search, so the length
	 * of $this string is returned.
	 * @param int $from Search target in the range [0,$from] of this string.
	 * @return int Index of the beginning of the first matching target,
	 * or -1 if not found.
	 * @throws OutOfRangeException If $from outside [0,$this-&gt;length()].
	 */
	function lastIndexOf($target, $from)
	{
		return Strings::lastIndexOf($this->s, $target->s, $from);
	}


	/**
	 * Returns a copy of this string with leading and trailing bytes
	 * specified removed.
	 * @param string $bytelist List of the bytes to remove. The special
	 * sequence "A..B" specifies the range from "A" to "B".  The default
	 * value lists the space characters as they may be appear to be encoded
	 * in the ASCII charset and in some other extended ASCII charsets,
	 * including ISO-8859-* and UTF-8.
	 * @return self This string but with all the leading and trailing
	 * bytes specified removed.
	 */
	function trim($bytelist = " \t\n\r\0\x0B")
	{
		return self::factory( trim($this->s, $bytelist) );
	}


	/**
	 * Replaces any occurrence of the target string with the replacement
	 * string.  Search and replacement is performed scanning this string
	 * from left to right.
	 * @param self $target Any occurrence of this string is replaced.
	 * @param self $replacement Replacement string.
	 * @return self This string but with any occurrence of the target
	 * string replaced.
	 * @throws \InvalidArgumentException If the target is the empty string.
	 */
	function replace($target, $replacement)
	{
		# If target empty, str_replace() simply returns the subject string.
		if( strlen($target->s) == 0 )
			throw new \InvalidArgumentException("empty target");
		return self::factory(
			(string) str_replace($target->s, $replacement->s, $this->s) );
	}


	/**
	 * Explode this string in pieces.
	 * This string is scanned from left to right.
	 * @param self $separator Any non-empty string that separates pieces.
	 * @return self[int] Pieces of this string that were separated by the
	 * given separator.
	 * @throws \InvalidArgumentException Separator is empty.
	 */
	function explode($separator)
	{
		if( $separator->length() == 0 )
			throw new \InvalidArgumentException("empty separator");
		$a = explode($separator->s, $this->s);
		$res = /*. (self[int]) .*/ array();
		foreach($a as $p)
			$res[] = self::factory($p);
		return $res;
	}


	/**
	 * Implode the array of strings.
	 * @param self[int] $pieces Strings to be joined.
	 * @param self $separator
	 * @return self
	 */
	static function implode($pieces, $separator)
	{
		$res = "";
		$n = 0;
		foreach($pieces as $p){
			if( $n == 0 ){
				$res = $p->s;
			} else {
				$res .= $separator->s;
				$res .= $p->s;
			}
			$n++;
		}
		return self::factory($res);
	}


	/*. int .*/ function getHash()
	{
		if( $this->s_hash == -1 )
			$this->s_hash = Hash::hashOfString($this->s);
		return $this->s_hash;
	}


	/**
	 * Compares these bytes with others bytes for equality.
	 * @param object $other Bytes to compare with.
	 * @return bool True if the other is not NULL, belongs to the
	 * exact same class of this (not extended), and it contains
	 * the same array of bytes.
	 */
	function equals($other)
	{
		if( $other === NULL or get_class($other) !== __CLASS__ )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->s === $other2->s;
	}


	/*. string .*/ function serialize()
	{
		return $this->s;
	}


	/*. void .*/ function unserialize(/*. string .*/ $serialized)
	{
		# FIXME: \unserialize() bypasses factory method by creating a brand new object.
		
		# [Paranoid] User code cannot call this method, or it would
		# subvert the state:
		#if( $this->s !== NULL )
		#	throw new \RuntimeException("already initialized");
			
		$this->s = $serialized;
	}

}

