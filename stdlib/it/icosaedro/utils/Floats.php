<?php

namespace it\icosaedro\utils;

/*.
	require_module 'standard';
	require_module 'pcre';
	require_module 'spl';
.*/

require_once __DIR__ . "/Integers.php";

use InvalidArgumentException;


/**
	Utility functions to handle floating point numbers.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:20:37 $
*/
class Floats {

	/*. private .*/ const LT = -1, EQ = 0, GT = 1;


	/**
		Compares two floating point numbers. Imposes a total ordering that
		overrides the comparison made by the IEEE 754 and by PHP, assuming NAN
		greater than any other value (including +INF); NAN, +INF and -INF are
		each one equal to itself. Then we have:
		<pre>-INF &lt; -0.0 &lt; 0.0 &lt; +INF &lt; NAN</pre>
		@param float $a
		@param float $b
		@return int Negative value, zero or positive value if $a is less, equal
		or greater then $b respectively, according to the comparison rules
		stated above.
	*/
	static function compare($a, $b)
	{
		/*
			At least under PHP 5.3, these expressions are all TRUE:

				INF === INF
				INF < INF (!)
				NAN < NAN (!)

			while these are FALSE:

				INF == INF (!)
				NAN == NAN (!)
				NAN === NAN

			so we walk over eggs...
		*/
		if( is_nan($a) ){
			if( is_nan($b) ){
				return self::EQ;
			} else {
				return self::GT;
			}
		} else if( is_nan($b) ){
			return self::LT;
		} else if( $a === +INF ){
			if( $b === +INF ){
				return self::EQ;
			} else {
				return self::GT;
			}
		} else if( $a === -INF ){
			if( $b === -INF ){
				return self::EQ;
			} else {
				return self::LT;
			}
		} else {

			/* 
			 * Problem of the zero negative (see https://bugs.php.net/bug.php?id=52355):
			 * $zn = -1.0 * 0.0;
			 * yields -0.0 and gets printed as "-0" if converted to string,
			 * but $zn === 0.0 is true and $zn < 0.0 is false under PHP.
			 * Workaround (note that "$zn" === "-0"): first check if both
			 * $a and $b are zero, and only then performs the quite expensive
			 * conversions to string:
			 */
			if( $a === 0.0 and $b === 0.0 ){
				if( "$a" === "-0" ){
					if( "$b" === "-0" )
						return self::EQ;
					else
						return self::LT;
				} else {
					if( "$b" === "-0" )
						return self::GT;
					else
						return self::EQ;
				}
			}

			// Normal comparison between finite, non zero, numbers:
			if( $a < $b )
				return self::LT;
			else if( $a > $b )
				return self::GT;
			else
				return self::EQ;
		}
	}


	/**
	 * Returns the floating point number in its hexadecimal representation.
	 * The string may start with a minus sign, followed by an integral part
	 * (in hexadecimal base with leading <code>"0x"</code>), possibly followed
	 * by a fractional part with at least a digit, then possibly followed
	 * by a "p" and the power of 2 (in decimal base). So for example:
	 * <pre>
	 * -0x3.ap4
	 * </pre>
	 * is the number -(3+10/16)*2^4 = 58 with -0x3.a being the mantissa and
	 * 4 being the exponent of 2.
	 * Note that the exponent part is the power of 2, NOT the power of 16.
	 * Note that any possible value of a floating point number can be expressed
	 * exactly with this hexadecimal representation.
	 * @param float $f  The floating point number to convert in hexadecimal
	 * representation, possibly NAN, INF or -INF.
	 * @return string The floating point number in hexadecimal representation.
	 */
	static function toHex($f)
	{
		if( is_nan($f) )
			return "NAN";
		if( $f === INF )
			return "INF";
		if( $f === -INF )
			return "-INF";
		if( $f === 0.0 ){
			if( "$f" === "-0" )
				return "-0x0.0";
			else
				return "0x0.0";
		}
			
		
		$s = "";
		if( $f < 0.0 ){
			$s = "-";
			$f = -$f;
		}

		$s .= "0x";

		# Normalize $f to [1/16,16.0[ (first digit must be in [0x0,0xf]):
		$p = 0;
		while( $f >= 16.0 ){
			$f *= 0.0625; // right shift by 4 bits
			$p += 4;
		}
		while( $f < 0.0625 ){
			$f *= 16.0; // left shift by 4 bits
			$p -= 4;
		}

		$d = (int) $f;
		$s .= dechex($d) . ".";
		$f = 16.0 * ($f - $d);
		do {
			$d = (int) $f;
			$s .= dechex($d);
			$f = 16.0 * ($f - $d);
		} while( $f > 0.0 );
		if( $p != 0 )
			$s .= "p$p";
		return $s;
	}


	/**
	 * Checks for an hexadecimal digit.
	 * @param string $c A character.
	 * @return bool True if the character $c is a valid hexadecimal digit.
	 */
	private static function isHexDigit($c)
	{
		# Avoid dependency from the ctype module:
		#return ctype_xdigit($c);
		return strcmp("0", $c) <= 0 and strcmp($c, "9") <= 0
		or strcmp("a", $c) <= 0 and strcmp($c, "f") <= 0
		or strcmp("A", $c) <= 0 and strcmp($c, "F") <= 0;
	}


	/**
	 * Parses a floating point number from its hexadecimal representation.
	 * The mantissa is expressend with hexadecimal digits with a leading "0x"
	 * and possibly a fractional part. An exponent of 2 part may also be present.
	 * Valid hexadecimal representations can be expressed in EBNF form:
	 * <blockquote>
	 * <pre>
	 * hex_float = "NAN" | "INF" | "-INF"
	 *             | [sign] "0x" inthex [ "." inthex ] ["p" [sign] intdec];
	 * sign = "+" | "-";
	 * inthex = hexdigit {hexdigit};
	 * intdec = "0".."9" {"0".."9"};
	 * hexdigit = "0".."9" | "a".."f" | "A".."F";
	 * </pre>
	 * </blockquote>
	 * Examples:
	 * <blockquote>
	 * <pre>
	 * 0x0   -0x1.23ab   0xabc.dep-5
	 * </pre>
	 * </blockquote>
	 * Parsing is made case-insensitive, so "p" and "P" or "INF" and "inf" are
	 * the same.
	 * Note that the integral part always contains at least one digit.
	 * Note that the fractional part, if present, must contain at least one digit.
	 * Note that the power part gives the power of 2, NOT the power of 16.
	 * @param string $s The floating point number is hexadecimal representation.
	 * @return float
	 * @throws InvalidArgumentException The string does not contain a valid
	 * hexadecimal representation of a floating point number.
	 */
	static function fromHex($s)
	{
		$s = strtolower($s);
		if( $s === "nan" )  return NAN;
		if( $s === "inf" )  return INF;
		if( $s === "-inf" ) return -INF;
		if( 1 !== preg_match("/^[-+]?0x[0-9a-fA-F]+(\\.[0-9a-fA-F]+)?(p[-+]?[0-9]+)?\$/", $s) )
			throw new InvalidArgumentException("not an hex float: $s");
		
		// Parse sign:
		$sign = false;
		$i = 0;
		if( $s[0] === "+" ){
			$i = 1;
		} else if( $s[0] === "-" ){
			$sign = true;
			$i = 1;
		}

		// Skip "0x":
		$i += 2;

		// Parse integral part:
		$f = 0.0;
		do {
			$f = 16.0 * $f + hexdec($s[$i]);
			$i++;
		} while( $i < strlen($s) and self::isHexDigit($s[$i]) );

		// Parse fractional part:
		if( $i < strlen($s) and $s[$i] === "." ){
			$g = 1.0;
			$i++;  // skip "."
			do {
				$g *= 0.0625; // 1/16
				$f += $g * hexdec($s[$i]);
				$i++;
			} while( $i < strlen($s) and self::isHexDigit($s[$i]) );
		}

		// Parse exponent part:
		if( $i < strlen($s) ){
			// Normalize mantissa to avoid overflow:
			$p = 0;
			if( $f > 0.0 ){
				while( $f > 1.0 ){ $f *= 0.5; $p++; }
				while( $f < 0.5 ){ $f *= 2.0; $p--; }
			}

			// Now $s[$i] cannot be other than "p".
			$p += Integers::parseInt( substr($s, $i+1) );
			$f *= pow(2.0, $p);
		}
		if( $sign )
			$f = -1.0 * $f;
		return $f;
	}


	/**
	 * Return the literal, exact, decimal representation of the binary
	 * internal representation of the floating point number.
	 * "Exact" means that the decimal result can be converted back into
	 * the same exact value again without loss of precision, that is
	 * (float)Floats::toLiteral($f) === $f for any float $f.
	 * In fact the
	 * conversion from the internal binary representation to the decimal
	 * representation can always be performed exactly, although the result
	 * may require a very big number of digits. For example
	 * toLiteral(0.1) gives
	 * "0.1000000000000000055511151231257827021181583404541015625"
	 * which is the actual best internal approximation of 0.1 using
	 * the IEEE 754 double-precision format common to all the "PC".
	 * The vice-versa is not true: even the simplest decimal number may
	 * require an infinite number of binary digits and then requires
	 * trunkink or rounding to fit the size of a IEEE 754 register.
	 * @param float $f
	 * @return string
	 */
	public static function toLiteral($f)
	{
		if( is_nan($f) )
			return "NAN";

		if( is_infinite($f) ){
			if( $f > 0.0 )
				return "INF";
			else
				return "-INF";
		}

		/*
			How many digits required? With IEE 754 double-precision, the worst
			case is $f less than 1.0 (in absolute value) with exponent 2^-1022
			and denormalized number, then the 53-th bit of the mantissa
			occupies the position 2^-1074, this also means 1074 decimals. To
			all that if negative we must add the sign in front, that brings the
			total maximum length to 1075. This is very rough estimation as
			actually there need far less digits than that because the
			scientific notation is introduced, so removing all those leading
			zeroes. This brings the actual no. of required digits to about only
			757 (tested "experimentally :-).
		*/
		$old_precision = ini_set("serialize_precision", "1079");
		$s = serialize($f);
		ini_set("serialize_precision", $old_precision);
		$s = substr($s, 2, strlen($s) - 3);

		if( strstr($s, ".") === FALSE and strstr($s, "e") === FALSE )
			# "float" must have at least either a "." or a "e", otherwise
			# 1.0 becomes "1" which looks like a int rather than "float".
			return "$s.0";
		else
			return $s;
	}


	/**
	 * Parse the given string as floating point number.
	 * @param string $s  String that represents a floating point number.
	 * Spaces are not allowed, so apply trim() if required.
	 * The given string is evaluated case-insensitive.
	 * The special values "NAN", "INF" and "-INF" are parsed correctly.
	 * At least one digit before and after the decimal point, if present,
	 * is required; so ".5" and "1." are not valid syntaxes.
	 * Huge numbers may give INF or -INF.
	 * Bare integer numbers are also allowed, then "123" becomes 123.0.
	 * @return float
	 * @throws \InvalidArgumentException  If the syntax of the number is invalid.
	 */
	static function parseFloat($s)
	{
		$s = strtoupper($s);
		if( $s === "NAN" ) return NAN;
		if( $s === "INF" ) return INF;
		if( $s === "-INF" ) return -INF;
		if( preg_match("/^[-+]?[0-9]+(\\.[0-9]+)?(E[-+]?[0-9]+)?\$/", $s) !== 1 )
			throw new \InvalidArgumentException("invalid float: $s");
		return (float) $s;
	}

}
