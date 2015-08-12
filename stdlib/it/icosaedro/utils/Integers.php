<?php

namespace it\icosaedro\utils;

/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
.*/

/**
	Utility functions to handle int numbers.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/05/06 16:08:01 $
*/
class Integers {

	/**
	 * Parses the given string as an int number in 10 base with sign.
	 * @param string $s  The string that represents the int number. Spaces are
	 * not allowed: only an optional sign mark +/- followed by one or more
	 * digits is allowed.
	 * @return int
	 * @throws \InvalidArgumentException  If the syntax is not valid or the number is
	 * to large to fit int.
	 */
	static function parseInt($s)
	{
		if( preg_match("/^[-+]?[0-9]+$/", $s) !== 1 )
			throw new \InvalidArgumentException("invalid int syntax: $s");
		
		# Remove sign:
		$sign = FALSE;
		if( $s[0] === "+" )
			$s = substr($s, 1);
		else if( $s[0] === "-" ){
			$s = substr($s, 1);
			$sign = TRUE;
		}

		# Remove leading zeroes:
		$s = trim("$s*", "0");
		if( strlen($s) == 1 )
			return 0;
		$s = substr($s, 0, strlen($s)-1);

		# Restore sign:
		if( $sign )
			$s = "-$s";

		$i = (int) $s;
		if( (string) $i !== $s )
			throw new \InvalidArgumentException("int out of range: $s");
		return $i;
	}


	/**
	 * Returns the number of bits set to 1.
	 * @param int $i Any int number.
	 * @return int Number of bits set to 1.
	 */
	static function bitCount($i)
	{
		static $lut = array(0, 1, 1, 2, 1, 2, 2, 3, 1, 2, 2, 3, 2, 3, 3, 4);
		$n = 0;
		if( $i < 0 ){
			# Avoid int overflow, that results in a float under PHP:
			$i &= PHP_INT_MAX;
			$n = 1;
		}
		while( $i != 0 ){
			$n += $lut[ $i & 15 ];
			$i >>= 4;
		}
		return $n;
	}


	/**
	 * Returns the magnitude of the int as unsigned number.  The magnitude is
	 * the number of bits remaining in the machine word once all the leading
	 * zeroes have been stripped off.  Then, the magnitude of 1 is 1; the
	 * magnitude of 0 is 0; the magnitude of -1 is 8 * {@link PHP_INT_SIZE}.
	 * @param int $i Any int number.
	 * @return int Magnitude of the int as unsigned number.
	 */
	static function magnitude($i)
	{
		$r = 0;
		if( $i < 0 )
			return 8 * PHP_INT_SIZE;
		while( $i >= 16 ){
			$i >>= 4;
			$r += 4;
		}
		while( $i > 0 ){
			$i >>= 1;
			$r++;
		}
		return $r;
	}


	/**
	 * Returns the minimum between two int numbers.
	 * @param int $a
	 * @param int $b
	 * @return int Minimum between the two numbers.
	 */
	static function min($a, $b)
	{
		if( $a <= $b )
			return $a;
		else
			return $b;
	}


	/**
	 * Returns the maximum between two int numbers.
	 * @param int $a
	 * @param int $b
	 * @return int Maximum between the two numbers.
	 */
	static function max($a, $b)
	{
		if( $a >= $b )
			return $a;
		else
			return $b;
	}


}
