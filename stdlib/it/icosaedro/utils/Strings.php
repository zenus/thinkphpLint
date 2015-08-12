<?php
/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
.*/

namespace it\icosaedro\utils;

use OutOfRangeException;

/**
	Utility functions to handle strings intended as arrays of bytes.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:20:37 $
*/
class Strings {
	
	/**
	 * Tells is the string contains only ASCII bytes, range 0-127.
	 * @param string $s
	 * @return boolean True if the given string is NULL, empty or contains only
	 * bytes in the range 0-127.
	 */
	public static function isASCII($s){
		return preg_match("/[\x80-\xff]/", $s) === 0;
	}
	

	/**
	 * Returns a PHP-compliant, pure ASCII, literal string in double-quotes.
	 * Useful to display arbitrary strings that may contain control
	 * characters or invalid encoding.
	 * All the ASCII control characters and the <code>$ \ "</code> characters
	 * are converted to the form \xxx, where xxx is the octal code of the
	 * byte, with the exception of the usual control characters as LF, CR etc.
	 * that are rendered as escape sequences "\n", "\r" etc.
	 * Examples:<p>
	 * <code>toLiteral(NULL) ==&gt; "NULL"</code><br>
	 * <code>toLiteral("abc\n") ==&gt; "\"abc\\n\""</code>
	 * @param string $s
	 * @return string
	 */
	static function toLiteral($s)
	{
		if( $s === NULL )
			return "NULL";
		return "\"" . addcslashes($s, "\000..\037\\\$\"\177..\377") . "\"";
	}


	/**
	 * Compares two strings.
	 * Then $a&lt;$b becomes Strings::compare($a, $b) &lt; 0.
	 * The NULL value is allowed and it is "less" than any other string,
	 * and the empty string is less than any other string with al least
	 * a character: NULL &lt; "" &lt; "abc".
	 * @param string $a
	 * @param string $b
	 * @return int  Less that 0 if $a&lt;$b, greater than zero if $a&gt;$b,
	 * zero if the two strings are equal. Note that NULL is equal to itself,
	 * instead NULL and the empty string differ.
	 */
	static function compare($a, $b)
	{
		if( $a === NULL ){
			if( $b === NULL ){
				return 0;
			} else {
				return -1;
			}
		} else if( $b === NULL ){
			return +1;
		} else {
			return strcmp($a, $b);
		}	
	}


	/**
	 * Return a subrange of a string. The NULL string has no subranges.
	 * The valid range of the indexes is:
	 * <code>0 &le; $start &le; $end &le; strlen($s)</code> Example:
	 * <p><center>{@img ./Strings-substring.gif}</center><p>
	 * <pre>
	 *     substring("ABCDEFG", 0, 4) ==&gt; "ABCD"
	 * </pre>
	 * @param string $s
	 * @param int $start  Index of the first char to consider.
	 * @param int $end  Index of the char <b>past</b> the last char to
	 * consider.
	 * @return string  The substring, whose length is exactly ($end-$start)
	 * bytes. Note that if $start==$end, then the empty string is returned.
	 * @throws \OutOfBoundsException  If $s is NULL or the range is invalid.
	 */
	static function substring($s, $start, $end)
	{
		$len = strlen($s);
		if( $s === NULL or $start < 0 or $start > $end or $end > $len )
			throw new \OutOfBoundsException("invalid range [$start,$end] for "
				. self::toLiteral($s));
		# Workaround for PHP's substr() bug that returns false if len==0
		# (see http://bugs.php.net/bug.php?id=38437 ):
		if( $end - $start == 0 )
			return "";
		return substr($s, $start, $end - $start);
	}


	/**
	 * Returns TRUE if $s starts with $head.
	 * Edge cases: 1) The NULL string starts only with a NULL string.
	 * 2) The empty string starts with either NULL and empty string.
	 * 3) A NULL $head returns TRUE for any $s string.
	 * @param string $s
	 * @param string $head
	 * @return boolean
	 */
	static function startsWith($s, $head)
	{
		if( $s === NULL )
			return $head === NULL;
		if( $head === NULL )
			return TRUE;
		$head_len = strlen($head);
		if( $head_len > strlen($s) )
			return FALSE;
		# substr_compare() fails if $head is empty. Fix:
		if( $head_len == 0 )
			return TRUE;
		return substr_compare($s, $head, 0, $head_len) == 0;
	}


	/**
	 * Returns TRUE if $s ends with $head.
	 * Edge cases: 1) The NULL string ends only with a NULL string.
	 * 2) The empty string ends with either NULL and empty string.
	 * 3) NULL is the tail of any string.
	 * @param string $s
	 * @param string $tail
	 * @return boolean
	 */
	static function endsWith($s, $tail)
	{
		if( $tail === NULL )
			return TRUE;
		$tail_len = strlen($tail);
		if( $tail_len > strlen($s) )
			return FALSE;
		# substr_compare() fails if $tail is empty. Fix:
		if( $tail_len == 0 )
			return TRUE;
		return substr_compare($s, $tail, -$tail_len) == 0;
	}


	/**
	 * Returns the starting position of the first occurrence of the target
	 * string in the given subject string.
	 * @param string $s The subject string.
	 * @param string $target Target substring to search. The empty string can
	 * always be found at the very beginning of the search, so $from is
	 * returned.
	 * @param int $from Search target in the range [$from,strlen($s)]
	 * of the subject string.
	 * @return int Index of the beginning first matching target, or -1 if
	 * not found.
	 * @throws OutOfRangeException If $from outside [0,strlen($s)].
	 */
	static function indexOf($s, $target, $from = 0)
	{
		if( $from < 0 or $from > strlen($s) )
			throw new OutOfRangeException("$from");
		if( $s === NULL ){
			if( $target === NULL )
				return 0;
			else
				return -1;
		}
		if( strlen($target) == 0 )
			return $from;
		$i = strpos($s, $target, $from);
		if( $i === FALSE )
			return -1;
		else
			return $i;
	}


	/**
	 * Returns the starting position of the last occurrence of the target
	 * string in the given subject string.
	 * @param string $s Subject string.
	 * @param string $target Target substring to search. The NULL and the
	 * empty string can always be found at the beginning of the search,
	 * so the length of $s string is returned.
	 * @param int $from Search target in the range [0,$from] of $s.
	 * @return int Index of the beginning of the first matching target,
	 * or -1 if not found.
	 * @throws OutOfRangeException If $from outside [0,strlen($s)].
	 */
	static function lastIndexOf($s, $target, $from)
	{
		if( $from < 0 or $from > strlen($s) )
			throw new OutOfRangeException("$from");
		if( $s === NULL ){
			if( $target === NULL )
				return 0;
			else
				return -1;
		}
		if( strlen($target) == 0 )
			return $from;
		if( $from == 0 )
			return -1;
		$i = strrpos($s, $target, $from - strlen($s) - 1);
		if( $i === FALSE )
			return -1;
		else
			return $i;
	}


	/**
	 * Replaces any occurrence of the target string with the replacement
	 * string.  Search and replacement is performed scanning the subject string
	 * from left to right.
	 * @param string $s The subject string.
	 * @param string $target Any occurrence of this string is replaced.
	 * @param string $replacement Replacement string.
	 * @return string This string but with any occurrence of the target
	 * string replaced.
	 * @throws \InvalidArgumentException If the target is the empty string.
	 */
	static function replace($s, $target, $replacement)
	{
		# If target empty, str_replace() simply returns the subject string.
		if( strlen($target) == 0 )
			throw new \InvalidArgumentException("empty target");
		return (string) str_replace($target, $replacement, $s);
	}


	/**
	 * Returns the UTF-8 character of the given codepoint.
	 * Only the Unicode BMP subset is supported, see
	 * {@link http://www.unicode.org www.unicode.org} and RFC 3629 for
	 * more details.
	 * @param int $codepoint
	 * The allowed range of Unicode codes is 0x0000-0xD7FF, 0xE000-0xFFFF,
	 * so a sequence up to 3 bytes is generated.
	 * @param string $invalid
	 * Value returned if an invalid codepoint is given.
	 * @return string
	 * The UTF-8 sequences of bytes that represents the codepoint.
	 */
	static function codepointToUTF8($codepoint, $invalid=NULL)
	{
		if( $codepoint < 0 ){
			# negative
		} else if( $codepoint < 0x80 ){
			return chr($codepoint);
		} else if( $codepoint < 0x800 ){
			return chr(0xC0 + ($codepoint >> 6))
			. chr(0x80 | $codepoint & 0x3F);
		} else if( $codepoint < 0x10000 ){
			if( $codepoint < 0xD800 or $codepoint > 0xDFFF ){
				return chr(0xE0 + ($codepoint >> 12))
				. chr(0x80 | ($codepoint >> 6) & 0x3F)
				. chr(0x80 | $codepoint & 0x3F);
			}
		}

		return $invalid;
	}


	/**
	 * Returns a proper UTF-8 BMP string.
	 * Invalid UTF-8 BMP sequences and bytes are dropped, so the resulting
	 * string contains only characters from the BMP encoded as per the RFC
	 * 3629 par. 4.
	 * Only the Unicode BMP subset is supported, see
	 * {@link http://www.unicode.org www.unicode.org} and RFC 3629 for
	 * more details.
	 * @param string $s
	 * An arbitrary string, possibly with invalid UTF-8 sequences and
	 * invalid BMP codepoints. If NULL is passed, then NULL is returned.
	 * @return string
	 * Cleaned string in strict UTF8/BMP encoding, with invalid sequences and
	 * invalid BMP codepoints removed.
	 */
	static function UTF8Filter($s)
	{
		if( $s === NULL )
			return NULL;

		$T = "[\x80-\xBF]";

		return preg_replace("/("

			# Unicode range 0x0000-0x007F (ASCII charset):
			."[\\x00-\x7F]"
			
			# Unicode range 0x0080-0x07FF:
			."|[\xC2-\xDF]$T"

			# Unicode range 0x0800-0xD7FF, 0xE000-0xFFFF:
			."|\xE0[\xA0-\xBF]$T|[\xE1-\xEC]$T$T|\xED[\x80-\x9F]$T|[\xEE-\xEF]$T$T"

			# Invalid/unsupported multi-byte sequence:
			.")|(.)/",
			
			"\$1", $s);
	}


}

