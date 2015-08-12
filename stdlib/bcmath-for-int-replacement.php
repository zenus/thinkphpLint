<?php

/**
 * BCMATH module replacement for arithmetic with integer numbers of arbitrary
 * length. Include this package if the BCMATH module is missing in your system
 * and you need arithmetic between integer numbers only. This package is faster
 * than the companion module {@link ./bcmath-for-decimal-replacement.html}, this
 * latter supporting decimal numbers too.
 * 
 * <p>All the functions of this package accept integer numbers provided in
 * several forms: as string of digits possibly with a leading sign; as int
 * values; as floating point numbers (in this case only the integral part is
 * considered). All these functions throw {@link InvalidArgumentException}
 * for any other type of data and for infinite or not-a-number floating point
 * numbers. Note that a string like <tt>"3.14"</tt> will throw exception
 * because of the decimal point, which make the string an invalid integer.
 * 
 * <p>On the contrary, a floating point number like 3.14 is accepted and
 * considered as 3. If this behavior looks a bit unsafe, it is motivated by the
 * fact that some floating point numbers expected to be integer and that are
 * printed as integer, actually contain a very small fractional part due to
 * rounding problems. In any case, when arbitrary precision calculations are
 * required, floating point numbers should be avoided anyway.
 * 
 * <p>The function bcscale() throws InvalidArgumentException if the argument
 * is anything but zero; the other functions that accept a scale factor do the
 * do the same.
 * 
 * <p>Division by zero and modulus by zero throw InvalidArgumentException.
 * 
 * <p>The functions bcsqrt(), bcpow() and bcpowmod() are not provided
 * here.
 * 
 * @package BCMath-For-Int
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/27 14:45:41 $
 */

/*. require_module 'spl'; .*/

require_once __DIR__ . "/autoload.php";

use it\icosaedro\bignumbers\BigInt;

if (!function_exists("\\bcadd") ) {
	
	function bcmath_for_int_replacement_checkScale(/*. mixed .*/ $scale) {
		if(!( is_null($scale) || (is_int($scale) || is_string($scale)) && (int) $scale == 0 ))
			throw new InvalidArgumentException("unsupported non-zero scale");
	}
	
	/*. boolean .*/ function bcscale(/*. int .*/ $scale) {
		bcmath_for_int_replacement_checkScale($scale);
		return TRUE;
	}

	/*. string .*/ function bcadd(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		bcmath_for_int_replacement_checkScale($scale);
		$t = new BigInt($a);
		$t = $t->add(new BigInt($b));
		return $t->__toString();
	}

	/*. string .*/ function bcsub(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		bcmath_for_int_replacement_checkScale($scale);
		$t = new BigInt($a);
		$t = $t->sub(new BigInt($b));
		return $t->__toString();
	}

	/*. string .*/ function bcmul(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		bcmath_for_int_replacement_checkScale($scale);
		$t = new BigInt($a);
		$t = $t->mul(new BigInt($b));
		return $t->__toString();
	}

	/*. string .*/ function bcdiv(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		bcmath_for_int_replacement_checkScale($scale);
		$t = new BigInt($a);
		$t = $t->div(new BigInt($b));
		return $t->__toString();
	}

	/*. string .*/ function bcmod(/*. mixed .*/ $a, /*. mixed .*/ $b) {
		$t = new BigInt($a);
		$t = $t->rem(new BigInt($b));
		return $t->__toString();
	}

//	/*. string .*/ function bcsqrt(/*. mixed .*/ $a, /*. mixed .*/ $scale = NULL) {
//		throw new UnimplementedException();
//	}
	
//	/*. int .*/ function bcpow(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
//		throw new UnimplementedException();
//	}

//	/*. string .*/ function bcpowmod(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
//		throw new UnimplementedException();
//	}

	/*. int .*/ function bccomp(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		bcmath_for_int_replacement_checkScale($scale);
		$t = new BigInt($a);
		return $t->cmp(new BigInt($b));
	}
	
}
