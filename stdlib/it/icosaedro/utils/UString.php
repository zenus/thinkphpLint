<?php
/*.
	require_module 'standard';
	require_module 'spl';
.*/

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../all.php";

use CastException;
use OutOfRangeException;
use Serializable;
use it\icosaedro\containers\Hash;
use it\icosaedro\containers\Hashable;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\utils\Codepoints;
use it\icosaedro\utils\TestUnit;
use it\icosaedro\utils\UTF8;


/**
	Immutable, Unicode strings with encoding conversion utilities.

	An instance of this class holds an immutable string of Unicode codepoints,
	and provides a set of manipulation functions.

	The client application can build Unicode strings calling the appropriate
	factory method "fromXxx($u)" where Xxx is the encoding of the passed string
	$u. Then, the methods "toXxx()" allows to retrieve the internal string with
	the desired encoding.

	Currently, the internal encoding is UTF-8 to allows for faster conversion
	from and to this encoding, so common in WEB applications. Obviously this
	choice causes some penalty performing heavy low-level string processing,
	but in my experience most WEB applications do not suffer from that, and can
	work effectively anyway.

	Alternative but still compatible implementations might use another
	encoding, for example with wide characters.

	<b>Charset encoding conversions.</b>
	You may use this class to convert strings from one encoding XXX to another
	encoding:
	<pre>
	echo UString::fromXXX("abcdefg")-&gt;toYYY();
	</pre>

	where XXX is the source encoding and YYY is the resulting final encoding.

	<b>BOM in text files.</b>
	Remember that text files may start with a BOM that allows to recognize the
	exact encoding used in that file. The BOM is a sequence of bytes that
	encodes the codepoint 0x0000feff and allows to detect the exact encoding
	of the file, including the byte ordering Big Endian or Little Endian:

	<pre>
	"\xfe\xff": UCS2 BE, use fromUCS2BE() and toUCS2BE()
	"\xff\xfe": UCS2 LE, use fromUCS2LE() and toUCS2LE()
	"\xef\xbb\xbf": UTF-8, use fromUTF8() and toUTF8()
	"\x00\x00\xfe\xff": UTF-32 BE, not supported by this implementation
	"\xff\xfe\x00\x00": UTF-32 LE, not supported by this implementation
	</pre>

	So if you are going to read a text file, you must check for any of these
	sequences of bytes and then use the functions indicated to correcly read
	and write to that file.

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:21:26 $
*/
final class UString implements Printable, Sortable, Hashable, Serializable
{
	/*
		Implementation notes.
		The internal representation of the string is UTF-8 with sequences
		up to 3 bytes long (Unicode BMP):

		1 byte sequence, codepoint range 0-127 (ASCII):
			0xxx xxxx   byte range: 0x00-0x7f

		2 bytes sequence, codepoint range 128-2047 (ISO-8859 up to 255,
		then Unicode):
			110x xxxx   byte range: 0xc2-0xdf
			10xx xxxx   byte range: 0x80-0xbf

		3 bytes sequence, codepoint range 2048-4095 (Unicode):
			1110 0000   byte range: 0xe0
			10xx xxxx   byte range: 0xa0-0xbf
			10xx xxxx   byte range: 0x80-0xbf

		3 bytes sequence, codepoint range 4096-65535 (Unicode):
			1110 xxxx   byte range: 0xe1-0xef
			10xx xxxx   byte range: 0x80-0xbf
			10xx xxxx   byte range: 0x80-0xbf

		Note that bytes in the range 0xc0, 0xc1, 0xf0-0xff never
		appear.
	*/
	
	/**
	 * Value that tells if the string passed to the factory method has already
	 * been already detected to be ASCII, non-ASCII or still unchecked.
	 * @access private
	 */
	const ASCII_UNCHECKED = 0,
		ASCII_OK = 1,
		ASCII_NO = 2;

	/** Immutable string, UTF-8 encoding. */
	private /*. string .*/ $s;
	
	/** If the string is ASCII. */
	private $is_ascii = FALSE;

	/** Length of the string, in codepoint units (-1 = still unknown). */
	private $s_len = -1;

	private /*. int .*/ $hash = 0;

	/* Cached empty string. */
	private static /*. self .*/ $empty;

	/* Cached single-codepoint strings; index is the codepoint. */
	private static $codepoints_cache = /*. (self[]) .*/ array();


	/**
		Set the internal string. The initial length is left to -1.
		Fast, but for internal use only.
		No checking performed, so the caller must be aware of the
		internal encoding used by this implementation of the class,
		that is UTF-8: that is why the constructor is made
		private and cannot be used in application code.
		@param string $s  The string, UTF-8 encoding, not NULL.
		@param boolean $is_ascii If the string is ASCII.
		@return void
	*/
	private function __construct($s, $is_ascii)
	{
		$this->s = $s;
		$this->s_len = -1;
		$this->is_ascii = $is_ascii;
	}


	/**
		Returns an UString out of a UTF-8 string. Shorter strings (currently
		those of length 0 or 1 codepoints) are cached internally to save memory
		space.
		@param string $s Assumed as UTF-8, possibly NULL.
		@param int $ascii_test One of ASCII_* constants telling if the string
		is ASCII, non-ASCII or still untested.
		@return self Object that wraps this string. If the argument is NULL,
		NULL is returned.
	*/
	private static function factory($s, $ascii_test = self::ASCII_UNCHECKED)
	{
		if( $s === NULL )
			return NULL;

		if( strlen($s) == 0 ){
			if( self::$empty === NULL )
				self::$empty = new self("", TRUE);
			return self::$empty;

		} else if( strlen($s) <= 3 and strlen($s) == UTF8::sequenceLength(ord($s)) ){
			# Single codepoint in string.
			$i = UTF8::codepointAtByteIndex($s, 0);
			if( array_key_exists($i, self::$codepoints_cache) ){
				return self::$codepoints_cache[$i];
			} else {
				$c = new self($s, strlen($s) <= 1);
				$c->s_len = 1;
				self::$codepoints_cache[$i] = $c;
				return $c;
			}

		} else {
			if( $ascii_test == self::ASCII_UNCHECKED )
				$is_ascii = Strings::isASCII($s);
			else
				$is_ascii = $ascii_test == self::ASCII_OK;
			return new self($s, $is_ascii);
		}
	}


	/**
		Maps a codepoint to its internal string representation.
		@param int $code  Codepoint [0,65535].
		@return string String of bytes that represents the internal
		encoding of the codepoint, in this implementation is UTF-8.
		@throws OutOfRangeException  If the codepoint is invalid.
	*/
	private static function codepointToString($code)
	{
		if( array_key_exists($code, self::$codepoints_cache) ){
			return self::$codepoints_cache[$code]->s;
		}

		return UTF8::chr($code);
	}


	/**
		Return a single codepoint, given its code.
		@param int $code  Codepoint in the range [0,65535]. Note that also
		undefined and forbidden codepoints can be generated.
		@return self  A string containing the codepoint.
		@throws OutOfRangeException  If the codepoint is invalid.
	*/
	static function chr($code)
	{
		if( $code < 0 or $code > 65535 )
			throw new OutOfRangeException("$code");

		if( array_key_exists($code, self::$codepoints_cache) )
			return self::$codepoints_cache[$code];

		$s = self::codepointToString($code);
		$c = new self($s, $code < 128);
		$c->s_len = 1;
		self::$codepoints_cache[$code] = $c;
		return $c;
	}


	/**
		Return the length of the string.
		@return int  Length of the string as number of codepoints.
	*/
	function length()
	{
		if( $this->s_len < 0 ){
			if( $this->is_ascii )
				$this->s_len = strlen($this->s);
			else
				$this->s_len = UTF8::length($this->s);
		}
		return $this->s_len;
	}


	// byteIndex() uses these values for faster sequential access.
	private $cached_i = -1;
	private $cached_index = -1;


	/**
		Return the byte index given the UTF-8 sequence index.
		@param int $codepoint_index  Index of the UTF-8 sequence, ranging from
		0 (the first sequence) up to the length in codepoints of the
		string. Note that this last sequence does not exist because
		its byte index is just one byte above the last sequence so the
		index returned points to the byte just next to the end of the
		string.
		@return int  Byte index of the UTF-8 sequence.
		@throws OutOfRangeException  If the parameter is out of the
		range from 0 up to the length in codepoints of the string.
	*/
	private function byteIndex($codepoint_index)
	{
		$s_len = $this->length();
		if( $codepoint_index < 0 || $codepoint_index > $s_len)
			throw new OutOfRangeException("$codepoint_index");
		
		if( $this->is_ascii )
			return $codepoint_index;

		if( $this->cached_i < 0 or $this->cached_i > $codepoint_index ){
			$j = 0;
			$byte_index = 0;
		} else {
			$j = $this->cached_i;
			$byte_index = $this->cached_index;
		}
		while($j < $codepoint_index){
			// Skip sequence starting at index $byte_index:
			$b = ord($this->s[$byte_index]);
			if( ($b & 0x80) == 0 ){
				$byte_index += 1;
			} else if( ($b & 0xe0) == 0xc0 ){
				$byte_index += 2;
			} else {
				$byte_index += 3;
			}
			$j++;
		}
		$this->cached_i = $codepoint_index;
		$this->cached_index = $byte_index;
		return $byte_index;
	}


	/**
		Returns the code of the codepoint at the given index.
		@param int $i  Index of the codepoint, in the range from 0
		up to the length of the string minus one. Note that for an
		empty string there is no valid range.
		@return int  Value of the codepoint in the range [0,65535].
		@throws OutOfRangeException  If the index is invalid.
	*/
	function codepointAt($i)
	{
		$s_len = $this->length();
		if( $i < 0 || $i > $s_len)
			throw new OutOfRangeException("$i");
		if( $this->is_ascii )
			return ord($this->s[$i]);
		else
			return UTF8::codepointAtByteIndex($this->s, $this->byteIndex($i));
	}


	/**
	 * Returns the character at the given index.
	 * @param int $i Index of the character in the range from 0 up to
	 * $s-&gt;length()-1.
	 * @return UString The character at the given index.
	 * @throws OutOfRangeException  If the index is invalid.
	 */
	function charAt($i)
	{
		$byte_index = $this->byteIndex($i);
		
		if( $this->is_ascii )
			return self::factory($this->s[$byte_index], self::ASCII_OK);
		
		$seq_len = UTF8::sequenceLength( ord($this->s[$byte_index]) );
		return self::factory(
			Strings::substring($this->s, $byte_index, $byte_index + $seq_len),
			$seq_len == 1? self::ASCII_OK : self::ASCII_NO );
	}


	/**
		Appends another string to this.
		@param self $other  String to append.
		@return self  This string with the other appended.
	*/
	function append($other)
	{
		$res = self::factory( $this->s . $other->s,
			$this->is_ascii && $other->is_ascii? self::ASCII_OK : self::ASCII_NO);
		$res->s_len = $this->length() + $other->length();
		return $res;
	}


	/**
		Returns a substring. You must indicate the range [$a,$b] of
		delimiting tick marks between codepoints, so that ($b-$a) is
		the resulting length of the substring.
		<center>{@img ./UString-substring.gif}</center>
		<pre>
		$s = UString-&gt;fromASCII("ABCDEFG");
		echo $s-&gt;substring(0,4);
		# ==&gt; "ABCD"
		echo $s-&gt;substring(3,3);
		# ==&gt; ""
		</pre>
		Note that the empty range generates the empty string and that
		it must be 0 &le; $a &le; $b &le; length().
		@param int $a  Index of the first codepoint.
		@param int $b  Index of the last codepoint (excluded).
		@return self  The substring, exactly ($b-$a) codepoints long.
		@throws OutOfRangeException Invalid range: must be 0 &le; $a &le; $b &le;
		length of the string.
	*/
	function substring($a, $b)
	{
		if( $a > $b )
			throw new OutOfRangeException("[$a,$b]");
		$index_a = $this->byteIndex($a);
		$index_b = $this->byteIndex($b);
		return self::factory( Strings::substring($this->s, $index_a, $index_b),
			$this->is_ascii? self::ASCII_OK : self::ASCII_UNCHECKED);
	}


	/**
	 * Removes a range of codepoints from this string. For example, if
	 * this string is "ABCD", removing the range (1,3) yields "AD".
	 * @param int $a Beginning of the range.
	 * @param int $b End of the range.
	 * @return self This string with the substring removed. The resulting
	 * string is ($b-$a) codepoints shorter than this. Note that the string
	 * removed is just $this-&gt;substring($a,$b).
	 * @throws OutOfRangeException Invalid range: must be 0 &le; $a &le; $b &le;
	 * length of the string.
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
	 * given position.
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
		Check if this string begins with the given other string.
		@param self $head  The beginning.
		@return bool  True the this string begins with $head.
		The empty string is the beginning of any string.
		Every string starts with itself.
	*/
	function startsWith($head)
	{
		return Strings::startsWith($this->s, $head->s);
	}


	/**
		Check if this string ends with the given other string.
		@param self $tail  The ending.
		@return bool  True the this string ends with $tail.
		The empty string is the end of any string.
		Every string ends with itself.
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
	 * @throws \OutOfRangeException If $from outside [0,$this-&gt;length()].
	 */
	function indexOf($target, $from = 0)
	{
		if( $from < 0 or $from > $this->length() )
			throw new \OutOfRangeException("$from");
		if( $target->length() == 0 )
			return $from;
		$i = strpos($this->s, $target->s, $this->byteIndex($from));
		if( $i === FALSE )
			return -1;
		if( $this->is_ascii )
			return $i;
		else
			return UTF8::codepointIndex($this->s, $i);
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
	 * @throws \OutOfRangeException If $from outside [0,$this-&gt;length()].
	 */
	function lastIndexOf($target, $from)
	{
		if( $from < 0 or $from > $this->length() )
			throw new \OutOfRangeException("$from");
		if( $target->length() == 0 )
			return $from;
		if( $from == 0 )
			return -1;
		$i = strrpos($this->s, $target->s,
			$this->byteIndex($from) - strlen($this->s) - 1);
		if( $i === FALSE )
			return -1;
		if( $this->is_ascii )
			return $i;
		else
			return UTF8::codepointIndex($this->s, $i);
	}


	/**
	 * Generates a compiled version of a set of codepoints. Compiled strings
	 * are cached for later reuse.
	 * @param self $codes Codepoints to be included in the set.  Ranges of
	 * codepoints can be indicated as "A..B".
	 * @return int[int] Compiled version of the set. The array contains an
	 * even number of paired entries; in each pair, the first number is the
	 * first codepoint of a range, the second number is the last element in
	 * the range. If, for example, the codes are "0..9,." then the result
	 * is array(48, 57, 44, 44, 46, 46).
	 */
	function compileCodepointSet($codes)
	{
		static $cache = /*. (int[string][int]) .*/ array();
		$key = "*" . $codes->s;
		if( array_key_exists($key, $cache) )
			return $cache[$key];

		$set = /*. (int[int]) .*/ array();
		$len = $codes->length();
		$i = 0;
		while( $i < $len ){
			$c = $codes->codepointAt($i);
			$set[] = $c;
			if( $i + 4 <= $len and $codes->codepointAt($i+1) == ord(".")
			and $codes->codepointAt($i+2) == ord(".") ){
				$set[] = $codes->codepointAt($i+3);
				$i += 4;
			} else {
				$set[] = $c;
				$i++;
			}
		}
		$cache[$key] = $set;
		return $set;
	}


	/**
	 * Returns true if the codepoint belongs to the set.
	 * @param int $c Codepoint code.
	 * @param int[int] $set Set of codepoints generated by {@link
	 * self::compileCodepointSet()}.
	 * @return bool True if the codepoint code belongs to the set.
	 */
	function codepointInSet($c, $set)
	{
		for( $i = count($set) - 2; $i >= 0; $i -= 2 ){
			if( $c >= $set[$i] and $c <= $set[$i+1] )
				return TRUE;
		}
		return FALSE;
	}


	/**
	 * Returns a copy of this string with leading and trailing codepoints
	 * specified removed.
	 * @param self $blacklist List of the codepoints to remove. The special
	 * sequence "A..B" specifies the range from "A" to "B".  If NULL or
	 * not specified, the default value includes: whitespace, HT, NL, CR,
	 * NUL, VT.
	 * @return self This string but with all the leading and trailing
	 * codepoints specified removed.
	 */
	function trim($blacklist = NULL)
	{
		if( $blacklist === NULL ){
			$is_ascii = $this->is_ascii? self::ASCII_OK : self::ASCII_NO;
			return self::factory( trim($this->s), $is_ascii );
		}

		if( $blacklist->length() == strlen($blacklist->s) )
			# Only ASCII chars in black list.
			return self::factory( trim($this->s, $blacklist->s) );

		# General algo:
		$set = self::compileCodepointSet($blacklist);
		$len = $this->length();
		$i = 0;
		while( $i < $len and self::codepointInSet($this->codepointAt($i), $set) )
			$i++;
		$j = $len;
		while( $j >= $i + 1 and self::codepointInSet($this->codepointAt($j-1), $set) )
			$j--;
		if( $j - $i == $len )
			return $this;
		else
			return $this->substring($i, $j);
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
	 * Compares this string with the other ignoring case.
	 * @param self $other The other string to compare with.
	 * @return bool True if the two strings are equal ignoring
	 * case differences.
	 */
	function equalsIgnoreCase($other)
	{
		$len = $this->length();
		if( $len != $other->length() )
			return FALSE;
		if( $this->s === $other->s )
			return TRUE;
		for( $i = $len - 1; $i >= 0; $i-- ){
			$a = $this->codepointAt($i);
			$b = $other->codepointAt($i);
			if( Codepoints::toFoldCase($a) != Codepoints::toFoldCase($b) )
				return FALSE;
		}
		return TRUE;
	}


	/**
	 * Returns this string in upper-case letters.
	 * @return self This string in upper-case letters.
	 */
	function toUpperCase()
	{
		$u = "";
		$l = $this->length();
		for($i = 0; $i < $l; $i++)
			$u .= self::codepointToString( Codepoints::toUpperCase( $this->codepointAt($i) ) );
		return self::factory($u);
	}


	/**
	 * Returns this string in lower-case letters.
	 * @return self This string in lower-case letters.
	 */
	function toLowerCase()
	{
		$u = "";
		$l = $this->length();
		for($i = 0; $i < $l; $i++)
			$u .= self::codepointToString( Codepoints::toLowerCase( $this->codepointAt($i) ) );
		return self::factory($u);
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
		if( strlen($separator->s) == 0 )
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


	/**
		Compare strings. Implements the {@link it\icosaedro\containers\Sortable} interface.
		Strings are compared left to right based on their codepoints.
		@param object $other  The second string.
		@return int  An integer number whose sign depends on the
		alphabetical order of this string compared with the other.
		The comparison is made over the codepoint values.
		@throws CastException If the object passed is not {@link self}.
	*/
	function compareTo($other)
	{
		if( $other === NULL )
			throw new \CastException("NULL");
		$other2 = cast(__CLASS__, $other);
		if( $this === $other2 )
			return 0;
		# strcmp() is implemented with C memcmp() since
		# PHP 5.0 (2004), so it is not locale aware: good.
		return strcmp($this->s, $other2->s);
	}


	/**
		Compare strings, case-insensitive.
		Strings are compared left to right based on their folded codepoints.
		@param UString $other  The second string.
		@return int  An integer number whose sign depends on the
		alphabetical order of this string compared with the other.
		The comparison is made over the folded codepoint values.
	*/
	function compareIgnoreCaseTo($other)
	{
		if( $this === $other )
			return 0;
		$this_len = $this->length();
		$other_len = $other->length();
		$n = $this_len < $other_len? $this_len : $other_len;
		for($i = 0; $i < $n; $i++){
			$a = Codepoints::toFoldCase( $this->codepointAt($i) );
			$b = Codepoints::toFoldCase( $other->codepointAt($i) );
			if( $a != $b )
				return $a - $b;
		}
		return $this_len - $other_len;
	}


	/**
	 * Return an hash value of this string.
	 * @return int Hash value of this string. 
	 */
	function getHash()
	{
		if( $this->hash == 0 )
			$this->hash = Hash::hashOfString($this->s);
		return $this->hash;
	}


	/**
	 * Return a case-insensitive hash value of this string. The value
	 * <b>is not cached</b> and is computed every time this method is called.
	 * This method is here just as an help to build higher level classes that
	 * handle case-insensitive strings.
	 * @return int Case-insensitive hash value of this string. 
	 */
	function getHashIgnoreCase()
	{
		$hash = 17;
		for($i = $this->length() - 1; $i >= 0; $i--){
			$hash = (31*$hash) ^ Codepoints::toFoldCase( $this->codepointAt($i) );
		}
		return $hash;
	}

	/**
		Return true if the two strings are equal.
		@param object $other  The other string.
		@return bool True if the other string is not NULL, belongs to this same
		class (not extended) and contains the same string of codepoints.
	*/
	function equals($other)
	{
		if( $other === NULL or get_class($other) !== __CLASS__ )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->s === $other2->s;
	}


	/**
	 * Factory method that takes an UTF-8 string as input.
	 * @param string $u Array of bytes that represents an UTF-8 well formed
	 * string, possibly NULL.
	 * @return self The resulting Unicode string. Invalid sequences, trunked
	 * sequences and non-minimal sequences are silently replaced with a
	 * question mark "?". If the argument is NULL, NULL is returned.
	 */
	static function fromUTF8($u)
	{
		if( $u === NULL )
			return NULL;
		
		if( Strings::isASCII($u) )
			return self::factory($u, self::ASCII_OK);

		/*
			Checks $u for invalid UTF-8 sequences.
		*/

		$s = "";  // resulting UTF-8 string
		$s_len = 0;  // compute here the length of the resulting string
		$u_len = strlen($u);
		$j = 0; // last UTF-8 sequence appended to result $s
		$i = 0; // index to next UTF-8 sequence to examine

		# Avoid to copy one byte at a time. Instead, the internal loop
		# scans the input string up to the end or up to the first invalid
		# sequence. The external loop is only apparent, as it performs a
		# cycle only after every invalid sequence found.
		# On valid UTF-8 encoded strings, then, we exit from the outermost
		# cycle with $j==0 and $i==strlen($u) and then we did not have
		# the need to copy any char at all, as we only store $u as is.
		while($i < $u_len){

			// Move $i up to the end of $u or invalid sequence:
			while($i < $u_len){

				$b1 = ord($u[$i]);
				$seq_len = UTF8::sequenceLength($b1);

				if( $seq_len == 1 ){
					$s_len++;
					$i++;

				} else if( $seq_len == 2 ){
					if( $i + 2 > $u_len ){
						// Trunked sequence.
						break;
					} else {
						$b2 = ord($u[$i + 1]);
						if( UTF8::isCont($b2) ){
							$s_len++;
							$i += 2;
						} else {
							// Invalid continuation byte.
							break;
						}
					}

				} else if( $seq_len == 3 ){
					if( $i + 3 > $u_len ){
						// Trunked sequence.
						break;
					} else {
						$b2 = ord($u[$i + 1]);
						$b3 = ord($u[$i + 2]);
						if( UTF8::isCont($b2) && UTF8::isCont($b3)
							# If b1=0xe0, then the first cont. byte must be in [0xa0,0xbf]:
							&& ($b1 != 0xe0 or $b2 >= 0xa0)
						){
							$s_len++;
							$i += 3;
						} else {
							// Invalid continuation bytes.
							break;
						}
					}

				} else {
					// Invalid start of sequence.
					break;
				}
			}

			if( $i == $u_len ){
				// Scanning finished.
				if( $j == 0 ){
					// Most common case: we reach the end without errors.
					$s = $u;
				} else if( $i > $j ){
					// Append last valid chunk:
					$s .= Strings::substring($u, $j, $i);
				}
				break;

			} else {
				// Invalid seq. detected at $u[$i].
				// Append valid chunk [$j,$i], skip 1 byte and continue.
				if( $i > $j )
					$s .= Strings::substring($u, $j, $i);
				$s .= "?";
				$s_len++;
				$i++;
				$j = $i;
			}
		}
		$us = self::factory($s);
		# If $us from cache, len may or may not be already set, anyway:
		$us->s_len = $s_len;
		return $us;
	}


	/**
	 * Returns this string in UTF-8 encoding.
	 * @return string This string in UTF-8 encoding.
	 */
	function toUTF8()
	{
		return $this->s;
	}


	/**
		Factory method that takes an ASCII string as input.
		@param string $u  Array of bytes that represents an ASCII string,
		possibly NULL.
		@return self The resulting Unicode string. Invalid non-ASCII bytes are
		silently replaced with a question mark "?". If the argument is NULL,
		NULL is returned.
	*/
	static function fromASCII($u)
	{
		if( $u === NULL )
			return NULL;
		
		if( Strings::isASCII($u) )
			return self::factory($u, self::ASCII_OK);
		
		$s = "";
		$j = 0;
		$u_len = strlen($u);
		for($i = 0; $i < $u_len; $i++){
			if( ord($u[$i]) >= 128 ){
				$s .= Strings::substring($u, $j, $i) . "?";
				$j = $i + 1;
			}
		}
		if( $j == 0 )
			$s = $u;
		else
			$s .= Strings::substring($u, $j, $u_len);
		$us = self::factory($s);
		# If $us from cache, len may or may not be already set, anyway:
		$us->s_len = $u_len;
		return $us;
	}


	/**
		Return the string as ASCII.
		@return string  Array of bytes, ASCII encoding. Non-ASCII
		codes are rendered as question mark "?".
	*/
	function toASCII()
	{
		if( $this->is_ascii )
			return $this->s;
		
		$s = $this->s;
		$s_len = strlen($s);
		$res = "";
		$i = 0;
		while($i < $s_len){
			$b = ord($s[$i]);
			$seq_len = UTF8::sequenceLength($b);
			if( $seq_len == 1 ){
				$res .= chr($b);
				$i += 1;
			} else if( $seq_len == 2 ){
				$res .= "?";
				$i += 2;
			} else {
				$res .= "?";
				$i += 3;
			}
		}
		return $res;
	}


	/**
		Factory method that takes an ISO-8859-1 string as input.
		@param string $u  Array of bytes that represents an ISO-8859-1
		string, possibly NULL.
		@return self The resulting Unicode string. If the argument is NULL,
		NULL is returned.
	*/
	static function fromISO88591($u)
	{
		if( $u === NULL )
			return NULL;
		
		if( Strings::isASCII($u) )
			return self::factory($u, self::ASCII_OK);
		
		$u_len = strlen($u);
		$s = "";
		$s_len = 0;
		for($i = 0; $i < $u_len; $i++){
			$codepoint = ord($u[$i]);
			if( $codepoint <= 0x7f ){
				# ASCII.
				$s .= chr($codepoint);
				$s_len++;
			} else if( $codepoint <= 0x9f ){
				# Codes 0x80-0x9f are undefined.
				$s .= "?";
			} else {
				$s .= chr(0xc0 + ($codepoint >> 6))
					. chr(0x80 + ($codepoint & 0x3f));
				$s_len++;
			}
		}
		$us = self::factory($s);
		# If $us from cache, len may or may not be already set, anyway:
		$us->s_len = $s_len;
		return $us;

		/*
		# Faster, but requires the xml PHP extension for utf8_encoding().
		# Codes 0x80-0x9f are undefined, but utf8_encoding() passes them
		# anyway.
		$us = self::factory( utf8_encode($s) );
		$us->s_len = strlen($u);
		return $us;
		*/
	}


	/**
		Return the string as ISO-8859-1.
		@return string  Array of bytes, ISO-8859-1 encoding. Non-ISO-8859-1
		codes are rendered as question mark "?".
	*/
	function toISO88591()
	{
		if( $this->is_ascii )
			return $this->s;
		
		$s = $this->s;
		$s_len = strlen($s);
		$res = "";
		$i = 0;
		while($i < $s_len){
			$b = ord($s[$i]);
			$seq_len = UTF8::sequenceLength($b);
			if( $seq_len == 1 ){
				$res .= chr($b);
				$i += 1;
			} else if( $seq_len == 2 ){
				$codepoint = (($b & 0x1f) << 6) + (ord($s[$i+1]) & 0x3f);
				if( $codepoint <= 255 )
					$res .= chr($codepoint);
				else
					$res .= "?";
				$i += 2;
			} else {
				$res .= "?";
				$i += 3;
			}
		}
		return $res;
	}


	/**
		Factory method that takes an UCS2 little endian string as input.
		@param string $u Array of bytes that represents an UCS2 little endian
		string, possibly NULL. The length of the array of bytes should be even,
		otherwise the last odd byte is ignored and a question mark "?" is
		appended to the resulting string.
		@return self The resulting Unicode string. If the argument is NULL,
		NULL is returned.
	*/
	static function fromUCS2LE($u)
	{
		if( $u === NULL )
			return NULL;
		$s = "";
		$s_len = (int) (strlen($u) / 2);
		for($i = 0; $i < 2*$s_len; $i += 2){
			$codepoint = ord($u[$i]) + (ord($u[$i+1]) << 8);
			$s .= self::codepointToString($codepoint);
		}
		if( (strlen($u) & 1) != 0 ){
			$s .= "?";
			$s_len++;
		}
		$us = self::factory($s);
		$us->s_len = $s_len;
		return $us;
	}


	/**
		Returns the string as UCS2 LE.
		@return string  Array of bytes, UCS2 LE encoding.
	*/
	function toUCS2LE()
	{
		$res = "";
		$s_len = $this->length();
		for($i = 0; $i < $s_len; $i++){
			$codepoint = $this->codepointAt($i);
			$res .= chr($codepoint & 255) . chr($codepoint >> 8);
		}
		return $res;
	}


	/**
		Factory method that takes an UCS2 big endian string as input.
		@param string $u Array of bytes that represents an UCS2 big endian
		string, possibly NULL. The length of the array of bytes should be even,
		otherwise the last odd byte is ignored and a question mark "?" is
		appended to the resulting string.
		@return self The resulting Unicode string. If the argument is NULL,
		NULL is returned.
	*/
	static function fromUCS2BE($u)
	{
		if( $u === NULL )
			return NULL;
		$s = "";
		$s_len = (int) (strlen($u) / 2);
		for($i = 0; $i < 2*$s_len; $i += 2){
			$codepoint = ord($u[$i+1]) + (ord($u[$i]) << 8);
			$s .= self::codepointToString($codepoint);
		}
		if( (strlen($u) & 1) != 0 ){
			$s .= "?";
			$s_len++;
		}
		$us = self::factory($s);
		$us->s_len = $s_len;
		return $us;
	}


	/**
		Returns the string as UCS2 BE.
		@return string  Array of bytes, UCS2 BE encoding.
	*/
	function toUCS2BE()
	{
		$res = "";
		$s_len = $this->length();
		for($i = 0; $i < $s_len; $i++){
			$codepoint = $this->codepointAt($i);
			$res .= chr($codepoint >> 8) . chr($codepoint & 255);
		}
		return $res;
	}


	/**
	 * Returns the internal representation of the string.
	 * @return string Array of bytes encoded as a PHP ASCII string with
	 * double quotes.
	 */
	function __toString()
	{
		return TestUnit::dump($this->s);
	}


	/**
	 * Returns this string as a PHP-compliant literal string in double-quotes.
	 * Useful to display arbitrary strings that may contain control
	 * characters.
	 * All the ASCII control characters 0-31,127 and the <code>$ \ "</code> characters
	 * are converted to the form \xxx, where xxx is the octal code of the
	 * byte, with the exception of the usual control characters as LF, CR etc.
	 * that are rendered as escape sequences "\n", "\r" etc.
	 * Example:<p>
	 * <code>UString::fromUTF8("abce\n")-&gt;toLiteral() ==&gt; "\"abce\\n\""</code>
	 * @return UString
	 */
	function toLiteral()
	{
		return self::fromUTF8("\"" . addcslashes($this->s, "\000..\037\\\$\"\177") . "\"");
	}


	/*. string .*/ function serialize()
	{
		return $this->s;
	}


	/*. void .*/ function unserialize(/*. string .*/ $serialized)
	{
		$u = self::fromUTF8($serialized);
		$this->s = $u->s;
		$this->s_len = $u->s_len;
	}


}
