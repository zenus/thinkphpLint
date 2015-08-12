<?php
/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
.*/

namespace it\icosaedro\utils;

use OutOfRangeException;


/**
 * Utility functions for UTF-8 BMP string encoding. This class only provides
 * very basic functions mostly intended to be used in others, higher level
 * packages.
 *
 * WARNING. These functions do not check for the actual encoding of the
 * passed strings and always assume blindly these strings are properly
 * UTF-8 encoded strings.  If arbitrary data are passed, unexpected results
 * may arise.
 * 
 * ATTENTION. In this document the term <i>byte</i> always refers to a
 * single byte of a generic string; the term <i>character</i> refers to
 * a single Unicode character, that may be encoded as a sequence of 1,
 * 2 or 3 bytes; the term <i>codepoint</i> refers to the numerical code of
 * a single Unicode character in the range [0,65535].
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:20:37 $
 */
class UTF8 {

	/**
	 * Sanitizes the string removing invalid bytes. Invalid bytes, incomplete
	 * UTF-8 sequences, non-minimal sequences and invalid BMP codepoints
	 * are removed.
	 * @param string $s The string to sanitize, possibly NULL.
	 * @return string Properly encoded UTF-8 BMP string. If the subject string
	 * is NULL, NULL is returned as well.
	 */
	static function sanitize($s)
	{
		static $PATTERN = /*. (string) .*/ NULL;
		if( $PATTERN === NULL ){
			$C = "[\x80-\xBF]";
			$PATTERN = "/("
				. "[\x00-\x7F]"
				. "|[\xC2-\xDF]$C"
				. "|\xE0[\xA0-\xBF]$C"
				. "|[\xE1-\xEF]$C$C"
				. ")|(.)/";
		}

		if( $s === NULL )
			return NULL;

		return preg_replace($PATTERN, "\$1", $s);
	}


	/**
		Returns the codepoint as UTF-8 string of bytes.
		@param int $code  Codepoint [0,65535].
		@return string String of bytes that represents the given codepoint.
		@throws OutOfRangeException  If the codepoint is invalid.
	*/
	static function chr($code)
	{
		if( $code < 0 or $code > 65535 )
			throw new OutOfRangeException("$code");

		if( $code < 128 ){
			$s = chr($code);
		} else if( $code < 2048 ){
			$s = chr(0xc0 | ($code >> 6))
				. chr(0x80 | ($code & 0x3f));
		} else {
			$s = chr(0xe0 | ($code >> 12))
				. chr(0x80 | ($code >> 6) & 0x3f)
				. chr(0x80 | ($code & 0x3f));
		}
		return $s;
	}


	/**
		Return true if the passed byte is the continuation byte of a
		UTF-8 sequence.
		@param int $b  Subject byte.
		@return bool  True if the subject byte is the continuation byte
		of a UTF-8 sequence.
	*/
	static function isCont($b)
	{
		return ($b & 0xc0) == 0x80;
	}


	/**
	 * Return the length of the UTF-8 sequence given its starting byte.
	 * Byte code ranges are as follows (by increasing code):
	 * <pre>
	 * [0x00,0x7f]  1 byte sequence (ASCII) returns 1
	 * [0x80,0xbf]  continuation byte -- returns 0
	 * [0xc0,0xc1]  unused byte codes -- returns 0
	 * [0xc2,0xdf]  2 bytes seq. starts -- returns 2
	 * [0xe0,0xef]  3 bytes seq. starts -- returns 3
	 * [0xf0,0xff]  unused byte codes -- returns 0
	 * </pre>
	 * @param int $b  First byte of the sequence in [0,255].
	 * @return int  Length of the sequence in bytes, that is 1, 2 or 3.
	 * Returns 0 if the byte code is invalid or out of the range [0,255].
	 */
	static function sequenceLength($b)
	{
		if( $b < 0 ){
			return 0;
		} else if( $b <= 0x7f ){
			return 1;
		} else if( $b <= 0xc1 ){
			return 0;
		} else if( $b <= 0xdf ){
			return 2;
		} else if( $b <= 0xef ){
			return 3;
		} else {
			return 0;
		}
	}


	/**
	 * Returns the codepoint at a given position in a string.
	 * @param string $s UTF-8 encoded string.
	 * @param int $byte_index Byte index of the sequence.
	 * @return int The code of the codepoint.
	 * @throws OutOfRangeException  If the index is invalid.
	 */
	static function codepointAtByteIndex($s, $byte_index)
	{
		if( $byte_index < 0 or $byte_index >= strlen($s) )
			new OutOfRangeException("$byte_index");
		$b1 = ord($s[$byte_index]);
		if( ($b1 & 0x80) == 0 ){
			return $b1;
		} else if( ($b1 & 0xe0) == 0xc0 ){
			$b2 = ord($s[$byte_index+1]);
			return (($b1 & 0x1f) << 6) + ($b2 & 0x3f);
		} else {
			$b2 = ord($s[$byte_index+1]);
			$b3 = ord($s[$byte_index+2]);
			return (($b1 & 0x0f) << 12) + (($b2 & 0x3f) << 6) + ($b3 & 0x3f);
		}
	}


	/**
	 * Return the byte index given the UTF-8 sequence index.
	 * @param string $s UTF-8 encoded string.
	 * @param int $codepoint_index  Index of the UTF-8 sequence, ranging from
	 * 0 (the first sequence) up to the length in characters of the
	 * string. Note that this last sequence does not exist because
	 * its byte index is just one byte above the last sequence so the
	 * index returned points to the byte just next to the end of the
	 * string.
	 * @return int  Byte index of the UTF-8 sequence.
	 * @throws OutOfRangeException  If the parameter is out of the
	 * range from 0 up to the length in characters of the string.
	 */
	static function byteIndex($s, $codepoint_index)
	{
		if( $codepoint_index < 0 )
			throw new OutOfRangeException("$codepoint_index");
		$s_len = strlen($s);
		$byte_index = 0;
		while( $codepoint_index > 0 ){
			if( $byte_index >= $s_len )
				throw new OutOfRangeException("$codepoint_index");
			$b = ord($s[$byte_index]);
			$seq_len = self::sequenceLength($b);
			if( $seq_len <= 0 )
				# Just skip invalid byte.
				$seq_len = 1;
			$byte_index += $seq_len;
			$codepoint_index--;
		}
		return $byte_index;
	}


	/**
	 * Returns the codepoint index given its byte index.
	 * @param string $s UTF-8 encoded string.
	 * @param int $byte_index Byte index of the codepoint, in
	 * [0,strlen($this-&gt;s)].  Note that if $byte_index is exactly equal
	 * to strlen($this-&gt;s), then the result is the length of the string
	 * in codepoints.
	 * @return int Byte index of this codepoint, that is the number of UTF-8
	 * sequences from the beginning of the string up there.
	 * @throws OutOfRangeException  If $byte_index is out of the range
	 * [0,strlen($this-&gt;s)].
	 */
	static function codepointIndex($s, $byte_index)
	{
		# FIXME: terribly slow.
		# Counts how many non-continuation bytes are
		# in the range [0,$byte_index]:
		if( $byte_index < 0 or $byte_index > strlen($s) )
			throw new OutOfRangeException("$byte_index");
		$codepoint_index = 0;
		$byte_index--;
		while( $byte_index >= 0 ){
			if( ! UTF8::isCont( ord($s[$byte_index]) ) )
				$codepoint_index++;
			$byte_index--;
		}
		return $codepoint_index;
	}


	/**
	 * Returns the code of the codepoint at the given index.
	 * @param string $s UTF-8 encoded string.
	 * @param int $codepoint_index  Index of the codepoint, in the range from 0
	 * up to the length of the string minus one. Note that for an
	 * empty string there is no valid range.
	 * @return int  Code of the codepoint.
	 * @throws OutOfRangeException  If the index is invalid.
	 */
	function codepointAt($s, $codepoint_index)
	{
		return self::codepointAtByteIndex($s, self::byteIndex($s, $codepoint_index));
	}


	/**
	 * Return the length of the string as number of characters.
	 * @param string $s UTF-8 encoded string.
	 * @return int  Length of the string as number of characters.
	 */
	static function length($s)
	{
		// Count non-continuation bytes:
		$len = 0;
		for($i = strlen($s) - 1; $i >= 0; $i--)
			if( ! UTF8::isCont(ord($s[$i]) ) )
				$len++;
		return $len;
	}


	/**
	 * Returns the character at the given index.
	 * @param string $s UTF-8 encoded string.
	 * @param int $i Index of the character in the range from 0 up to
	 * UTF8::length($s)-1.
	 * @return string The character as a UTF-8 string. The returned string
	 * may contain from 1 up to 3 bytes.
	 * @throws OutOfRangeException  If the index is invalid.
	 */
	function charAt($s, $i)
	{
		try {
			$byte_index = self::byteIndex($s, $i);
		}
		catch(OutOfRangeException $e){
			throw new OutOfRangeException("$i");
		}
		if( $byte_index >= strlen($s) )
			throw new OutOfRangeException("$byte_index");
		$seq_len = self::sequenceLength( ord($s[$byte_index]) );
		if( $seq_len <= 0 or $byte_index + $seq_len > strlen($s) )
			return "?";
		return substr($s, $byte_index, $seq_len);
	}

}
