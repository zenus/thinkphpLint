<?php

/**
 * BCMATH module replacement for arithmetic with decimal numbers of arbitrary
 * length and precision. Include this package if the BCMATH module is missing in
 * your system and you need arithmetic between decimal floating point numbers of
 * arbitrary length and precision. If you known that the involved numbers are
 * all and only integer, better to use the faster implementation provided by the
 * {@link ./bcmath-for-int-replacement.html} package.
 * 
 * <p>All the functions of this package accept floating point numbers provided
 * in several forms: as string of digits possibly with a leading sign and
 * exponent; as int values; as floating point numbers. All these functions
 * throw {@link InvalidArgumentException} for any other type of data and for
 * infinite or not-a-number floating point numbers.
 * 
 * <p>Division by zero and square root of a negative number throw
 * InvalidArgumentException.
 * 
 * <p>The functions bcmod(), bcpow() and bcpowmod() are not provided here.
 * 
 * @package BCMath-For-Decimal
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/27 14:45:50 $
 */

require_once __DIR__ . "/autoload.php";

use it\icosaedro\bignumbers\BigFloat;

if (!function_exists("\\bcadd") ) {
	
	/*. private mixed .*/ $bcmath_int_replacement_scale = 0;
	
	/*. private string .*/ function bcmath_int_replacement_doScale(BigFloat $a, /*. mixed .*/ $scale) {
		if( $scale === NULL )
			$scale = $GLOBALS["bcmath_int_replacement_scale"];
		return $a->format((int) $scale);
	}
	
	/*. boolean .*/ function bcscale(/*. int .*/ $scale) {
		$GLOBALS["bcmath_int_replacement_scale"] = $scale;
		return true;
	}

	/*. string .*/ function bcadd(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		$t = new BigFloat($a);
		$t = $t->add(new BigFloat($b));
		return bcmath_int_replacement_doScale($t, $scale);
	}

	/*. string .*/ function bcsub(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		$t = new BigFloat($a);
		$t = $t->sub(new BigFloat($b));
		return bcmath_int_replacement_doScale($t, $scale);
	}

	/*. string .*/ function bcmul(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		$t = new BigFloat($a);
		$t = $t->mul(new BigFloat($b));
		return bcmath_int_replacement_doScale($t, $scale);
	}

	/*. string .*/ function bcdiv(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		$t = new BigFloat($a);
		if( $scale === NULL )
			$scale = $GLOBALS["bcmath_int_replacement_scale"];
		if( $scale !== NULL )
			$precision = (int) $scale;
		else
			$precision = -2;
		$t = $t->div(new BigFloat($b), $precision > 0? -$precision : 0);
		return $t->__toString();
	}

//	/*. string .*/ function bcmod(/*. mixed .*/ $a, /*. mixed .*/ $b) {
//		throw new UnimplementedException();
//	}

	/*. string .*/ function bcsqrt(/*. mixed .*/ $a, /*. mixed .*/ $scale = NULL) {
		$t = new BigFloat($a);
		if( $scale === NULL )
			$scale = $GLOBALS["bcmath_int_replacement_scale"];
		if( $scale !== NULL )
			$precision = (int) $scale;
		else
			$precision = -2;
		$t = $t->sqrt($precision > 0? -$precision : 0);
		return $t->__toString();
	}
	
//	/*. int .*/ function bcpow(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
//		throw new UnimplementedException();
//	}
//
//	/*. string .*/ function bcpowmod(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
//		throw new UnimplementedException();
//	}

	/*. int .*/ function bccomp(/*. mixed .*/ $a, /*. mixed .*/ $b, /*. mixed .*/ $scale = NULL) {
		$t = new BigFloat($a);
		$a = bcmath_int_replacement_doScale($t, $scale);
		
		$t = new BigFloat($b);
		$b = bcmath_int_replacement_doScale($t, $scale);
		
		$t = new BigFloat($a);
		return $t->cmp(new BigFloat($b));
	}
}
