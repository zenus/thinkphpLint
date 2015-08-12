<?php

namespace it\icosaedro\bignumbers;

require_once __DIR__ . "/../../../all.php";

use it\icosaedro\bignumbers\BigInt;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\containers\Hashable;
use it\icosaedro\containers\Hash;

/**
 *  Implements floating point numbers of arbitrary length and precision.
 *  
 *  BigFloat are floating point numbers with sign and arbitrary length and
 *  precision suitable for monetary calculations and other numerical non-intensive
 *  tasks. Big floating numbers can be entered using a syntax similar to that
 *  of regular floating point numbers of the PHP language, included sign,
 *  fractional part and scale coefficient. For example:
 *  
 *  <code>+12.3456789012e+123</code>
 *  
 *  All the operations are performed with absolute precision using a decimal
 *  internal representation. A number can take as many digits as needed, either
 *  in the integer part or in the decimal part. Only the division requires to
 *  set a limit to the precision.
 *  
 *  Example:
 *  
 *  <pre>
 *      use it\icosaedro\bignumbers\BigFloat;
 *      $price = new BigFloat("56.78");
 *      $VAT = $price
 *          -&gt;mul( new BigFloat("0.20") )  # apply VAT 20%
 *          -&gt;round(-2);  # round to 2 decimal digits
 *      $total = $price-&gt;add($VAT);
 *      echo "Price: ", $price-&gt;format(2), "\n";
 *      echo "VAT  : ", $VAT-&gt;format(2), "\n";
 *      echo "Total: ", $total-&gt;format(2), "\n";
 *  </pre>
 *  
 *  The code above displays:
 *  
 *  <pre>
 *      Price: 56.78
 *      VAT  : 11.36
 *      Total: 68.14
 *  </pre>
 *  
 *  Currently the exponent part of the number is handled as <code>int</code>
 *  number and possible overflows are not detected.  The method
 *  <code>isValid()</code> devoted to validate input submitted by the user
 *  sets a quite
 *  arbitrary limit of +/-9999 to the exponent, and this should be enough to
 *  protect the application.  However, it is unlikely that so big numbers can
 *  ever be required by real-world applications.
 *  
 *  A note about the interface provided by this class.  Every object of the class
 *  holds a big floating point number, so most of the methods have this value
 *  as implicit argument, here represented with the word "<code>$this</code>".
 *  Methods that require two or more numbers take <code>$this</code> as the
 *  first argument.
 *  Once created, an object is never changed, i.e. it is immutable.
 *  
 *  <b>See also:</b>
 *  {@link http://www.php.net/manual/en/ref.bc.php
 *  www.php.net/manual/en/ref.bc.php} (BCMath extension)
 *  
 *  @author Umberto Salsi <salsi@icosaedro.it>
 *  @copyright 2007 by icosaedro.it di Umberto Salsi
 *  @version $Date: 2014/02/24 19:52:43 $
 *  @license http://www.icosaedro.it/license/bsd-style.html BSD-style
 */
class BigFloat implements Printable, Sortable, Hashable
{
	/* Trick to preserve basic version info also if source gets stripped: */
	const
		VERSION = '$Date: 2014/02/24 19:52:43 $';

	private /*. BigInt .*/ $m;

	/* scale factor, exponent of 10 */
	private /*. int .*/ $e = 0;

	private /*. int .*/ $hash = 0;

	private static /*. BigInt .*/ $int_zero;


	/**
	 *  Returns TRUE if the string represents a valid BigFloat
	 *
	 *  Valid BigFloat numbers looks like regular PHP floating point
	 *  numbers with the only difference that, if a decimal point is
	 *  present, at least a digit must be present before and after
	 *  that decimal point.  To be more precise, a BigFloat may
	 *  have a sign +/- followed by one or more decimal digits with
	 *  possibly a decimal point followed by one or more digits and
	 *  an exponent. The exponent can range from -9999 up to +9999.
	 *  Spaces and any other character are not allowed.
	 *  Examples:
	 *
	 *  <code> 0 -1.5 +0.012345 1e6 12.34E-128  </code>
	 *
	 *  Invalid examples:
	 *
	 *  <code> .1 100. 1^6 1,234 </code>
	 *
	 *  @param string $f  The string to be evaluated as BigFloat.
	 *  @return boolean  TRUE if the string represents a valid BigFloat.
	 */
	static function isValid($f)
	{
		return is_string($f)
		and preg_match("/^[-+]?[0-9]+(\\.[0-9]+)?([eE][-+]?[0-9]{1,4})?\$/", $f) == 1;
	}


	static private /*. void .*/ function normalize(self $x)
	/* WARNING: the argument passed may be changed by this method */
	{
		if( $x->m->sign() == 0 ){
			$x->e = 0;
			return;
		}

		# Remove useless trailing zeroes:
		# 12.3400 --> 12.34
		$s = $x->m->__toString();
		$j = 1;
		if( $s[0] === "-" )
			$j = 2;
		$i = strlen($s);
		while( $i >= $j  and  $s[$i-1] === "0" )
			$i--;
		if( $i < strlen($s) ){
			$d = strlen($s) - $i;
			$x->e += $d;
			$x->m = $x->m->shift(-$d);
		}
	}


	/*. forward void function __construct(mixed $x)
	    throws \InvalidArgumentException; .*/

	/**
	 *  Returns the number represented as string
	 *
	 *  @return string  An optional "-" is followed by one or more digits
	 *          and a possible fractional part.
	 */
	function __toString()
	{
		# FIXME: remove this line:
		#return $this->m->__toString() . "e" . $this->e;
		$s = $this->m->abs()->__toString();
		$e = $this->e;
		if( $e > 0 )
			$s .= str_repeat("0", $e);
		else if( $e < 0 ){
			$l = strlen($s);
			if( $l <= -$e )
				$s = "0." . str_repeat("0", -$e - $l) . $s;
			else
				$s = substr($s, 0, $l + $e) . "." . substr($s, $l + $e);
		}
		if( $this->m->sign() < 0 )
			$s = "-" . $s;
		return $s;
	}


	/**
	 *  Pretty formatting
	 *
	 *  WARNING! The number is truncated as needed, but not rounded.
	 *  If a rounding is required, apply {@link ::round()} before
	 *  formatting.
	 *
	 *  @param int $decimals  Number of digits in the fractional part.
	 *         The BigFloat is truncated or some zero is added if
	 *         required. If negative, the fractional part is omitted.
	 *  @param string $dec_point  Separator string between integral part
	 *         and fractional part.
	 *  @param string $thousands_sep  Separator string between thousands.
	 *  @return string  The BigFloat formatted.
	 */
	function format(
		$decimals,
		$dec_point = ".",
		$thousands_sep = ",")
	{
		$a = explode(".", $this->__toString());

		# Format the integer part:
		$m = $a[0];
		$i = new BigInt($m);
		$s = $i->format(0, "", $thousands_sep);
		if( $decimals <= 0 )
			return $s;

		# Format the fractional part:
		if( isset($a[1]) )
			$f = $a[1];
		else
			$f = "";
		$fl = strlen($f);
		if( $fl > $decimals )
			$f = substr($f, 0, $decimals);
		else if( $fl < $decimals )
			$f = $f . str_repeat("0", $decimals - $fl);

		return $s . $dec_point . $f;
	}


	/**
	 *  Returns the sign of the number.
	 *
	 *  @return int  +1 if the number is positive, -1 if negative, 0 if zero.
	 */
	function sign()
	{
		return $this->m->sign();
	}


	/**
	 *  Returns the scale factor, that is the power of the first digit
	 *
	 *  For example, the scale of 1230 is 4, the scale of 0.00123 is -3.
	 */
	/*. int .*/ function scale()
	{
		return $this->m->scale() + $this->e;
	}


	/**
	 *  Returns the number without the sign.
	 *
	 *  @return self  The number without the sign.
	 */
	function abs()
	{
		if( $this->m->sign() >= 0 )
			return $this;

		$n = clone $this;
		$n->m = $n->m->minus();
		return $n;
	}


	/**
	 *  Returns the number with the sign reversed.
	 *
	 *  @return self  The number with the sign reversed.
	 */
	function minus()
	{
		if( $this->m->sign() == 0 )
			return $this;

		$n = clone $this;
		$n->m = $n->m->minus();
		return $n;
	}


	/**
	 *  Compare $this with $b.
	 *  
	 *  @param self $b  The number to be compared.
	 *  @return int  Negative if $this is less than $b, positive if
	 *          $this is greater than $b, zero if they are equal.
	 */
	function cmp($b)
	{
		$a_sgn = $this->m->sign();
		$b_sgn = $b->m->sign();

		if( $a_sgn != $b_sgn )
			return $a_sgn - $b_sgn;

		if( $a_sgn == 0 )
			return 0;

		$d = $this->scale() - $b->scale();
		if( $d != 0 )
			return $a_sgn * $d;

		$x = $this->m;
		$y = $b->m;
		$d = $x->scale() - $y->scale();
		if( $d < 0 )
			$x = $x->shift(-$d);
		else if( $d > 0 )
			$y = $y->shift($d);
		return $x->cmp($y);
	}


	/**
	 * Compares this number with another.
	 * @param object $other Another BigFloat number to compare.
	 * @return int Negative if $this &lt; $other, positive if $this &gt; $other,
	 * zero if they are equal.
	 * @throws \CastException If the object passed is NULL or does not
	 * belong exactly to this class (not extended).
	 */
	function compareTo($other)
	{
		if( $other === NULL )
			throw new \CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new \CastException("expected " . __CLASS__ . " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		if( $this === $other2 )
			return 0;
		return $this->cmp($other2);
	}


	/**
	 *  Addition.
	 *
	 *  @param self $b  The second term to add.
	 *  @return self  The sum $this+$b.
	 */
	function add($b)
	{
		$am = $this->m;
		$bm = $b->m;
		$d = $this->e - $b->e;
		if( $d > 0 ){
			$am = $am->shift($d);
			$e = $b->e;
		} else if( $d < 0 ){
			$bm = $bm->shift(-$d);
			$e = $this->e;
		} else {
			$e = $this->e;
		}
		$c = new self(0);
		$c->m = $am->add($bm);
		$c->e = $e;
		self::normalize($c);
		return $c;
	}


	/**
	 *  Subtraction.
	 *
	 *  @param self $b  The term to subtract.
	 *  @return  self  The difference $this-$b.
	 */
	function sub($b)
	{
		$am = $this->m;
		$bm = $b->m;
		$d = $this->e - $b->e;
		if( $d > 0 ){
			$am = $am->shift($d);
			$e = $b->e;
		} else if( $d < 0 ){
			$bm = $bm->shift(-$d);
			$e = $this->e;
		} else {
			$e = $this->e;
		}
		$c = new self(0);
		$c->m = $am->sub($bm);
		$c->e = $e;
		self::normalize($c);
		return $c;
	}


	/**
	 *  Multiplication.
	 *
	 *  @param self $b  The second factor.
	 *  @return self  The product $this*$b.
	 */
	function mul($b)
	{
		$p = new self( 0 );
		$p->e = $this->e + $b->e;
		$p->m = $this->m->mul( $b->m );
		self::normalize($p);
		return $p;
	}


	/**
	 *  Returns $this/$b.
	 *
	 *  Calculate the quotient $q=$this/$b precise up to the digit of
	 *  the power 1e$precision.  For example, to obtain a result
	 *  with 5 decimal digits you must set $precision to -5. Note
	 *  that the result is truncated. If a rounding is required,
	 *  the division must be performed with higher precision -6
	 *  and the result can then be rounded:
	 *
	 *  <code>$q = $n -&gt;div($d, -6) -&gt;round(-5);</code>
	 *
	 *  @param self $b  The divisor.
	 *  @param int $precision  Power of ten of the last digit to calculate.
	 *         For example, to calculate up to the second fractional digit,
	 *         set $precision to -2. Setting $precision=0 would return the
	 *         integral part of the division. Positive values would stop
	 *         the calculation to the given power of ten. Examples:
	 * <pre>
	 * echo $a-&gt;div( new BigFloat(3), -2), "\n";  # 1.66
	 * echo $a-&gt;div( new BigFloat(3), 0), "\n";  # 1
	 * echo $a-&gt;div( new BigFloat(3), 1), "\n";  # 0
	 * </pre>
	 *
	 *  @return self  The quotient calculated up to the given precision.
	 *  @throws \InvalidArgumentException if the divisor $b is zero.
	 */
	function div($b, $precision)
	{
		if( $b->sign() == 0 )
			throw new \InvalidArgumentException("division by zero");

		$q = new self(0);
		$q->e = $precision;
		$q->m = $this->m
			->shift($this->e - $b->e - $precision)
			->div($b->m);

		self::normalize($q);
		return $q;
	}


	/**
	 *  Returns $this/$b and the remainder
 	 *
	 *  Returns the quotient $q=$this/$b precise up to the power
	 *  1e$precision, just like {@link ::div()} does, but it returns
	 *  also the remainder $rem = $this - $q*$b.
	 *  For example, having to divide 100 EUR into 3 parts with
	 *  precision of 1 cent ($precision=-2) we get the quotient
	 *  100/3=33.33 with remainder 0.01.
	 *
	 *  @param self $b  The divisor.
	 *  @param int $precision  Power of ten of the last digit to calculate.
	 *  @param self & $rem  Remainder of the division.
	 *  @return self  The quotient calculated up to the given precision.
	 *  @throws \InvalidArgumentException if the divisor $b is zero.
	 */
	function div_rem(
		$b,
		$precision,
		/*. return .*/ & $rem)
	{
		if( $b->sign() == 0 )
			throw new \InvalidArgumentException("division by zero");

		$q = $this->div($b, $precision);
		$rem = $this->sub( $q->mul($b) );
		return $q;
	}


	/**
	 *  Returns the number truncated to a given power of ten
	 *
	 *  The digits of power 1e($precision-1), 1e($precision-2),
	 *  ... are simply discarded. <code>trunc(0)</code> returns
	 *  the integer part of the number. Examples:
	 *  <pre>
	 *      $n = new BigFloat("12.345");
	 *      echo $n-&gt;trunc(-2);  # 12.34
	 *      echo $n-&gt;trunc( 1);  # 10
	 *      echo $n-&gt;trunc( 2);  # 0
	 *      echo $n-&gt;trunc(-9);  # 12.345
	 *  </pre>
	 *  Note that if the $precision is greater than the scale of the
	 *  number, zero is returned.
	 *
	 *  @param int $precision  Power of ten of the last digit to retain.
	 *  @return self  The truncated number.
	 */
	function trunc($precision)
	{
		if( $precision <= $this->e )
			return $this;

		if( $precision > $this->scale() )
			return new self("0");

		$m = $this->m->__toString();
		$m = substr($m, 0, strlen($m) - $precision + $this->e);
		return new self($m . "e" . (string) $precision);
	}


	/**
	 *  Returns the truncated number and the remainder
	 *
	 *  The same as {@link ::trunc()} but it returns also the truncated
	 *  remainder $rem. Note that the truncated number added to the
	 *  remainder give back the original number.
	 *
	 *  @param int $precision  Power of ten of the last digit to retain.
	 *  @param self & $rem  The truncated part.
	 *  @return self  The truncated number.
	 */
	function trunc_rem($precision, /*. return .*/ & $rem)
	{
		$i = $this->trunc($precision);
		$rem = $this->sub($i);
		return $i;
	}


	/**
	 *  Rounds the number to a given power of ten
	 *
	 *  Returns $this truncated just as explained for {@link ::trunc()}.
	 *  If the first digit of the remainder is 5 or greater, the
	 *  truncated number is also rounded.  For example, round(0)
	 *  returns the nearest integer.  Examples:
	 *  <pre>
	 *      $n = new BigFloat("1.4");
	 *      echo $n-&gt;round(0);  # displays 1
	 *      $n = new BigFloat("1.5");
	 *      echo $n-&gt;round(0);  # displays 2
	 *      $n = new BigFloat("-1.4");
	 *      echo $n-&gt;round(0);  # displays -1
	 *      $n = new BigFloat("-1.5");
	 *      echo $n-&gt;round(0);  # displays -2
	 *  </pre>
	 *
	 *  <b>See also:</b>
	 *  {@link http://en.wikipedia.org/wiki/Rounding}
	 *  for a discussion of various rounding methods.
	 *
	 *  @param int $precision  Power of ten of the last digit to retain.
	 *  @return self  The rounded number.
	 */
	function round($precision)
	{
		$i = $this->trunc_rem($precision, $r);
		$thr = new self("0.5e$precision");
		if( $r->abs()->cmp($thr) < 0 )
			return $i;
		if( $i->sign() >= 0 )
			return $i->add( new self("1e$precision") );
		else
			return $i->sub( new self("1e$precision") );
	}


	/**
	 *  Return the smallest integral value not less than $this.
	 *
	 *  @return self  The ceil of $this.
	 */
	function ceil()
	{
		if( $this->e >= 0 )
			return $this;
		else if( $this->m->sign() >= 0 )
			return new self($this->m->shift($this->e)->add(new BigInt(1)));
		else
			return new self($this->m->shift($this->e));
	}


	/**
	 *  Return the largest integral value not greater than $this.
	 *
	 *  @return self  The floor of $this.
	 */
	function floor()
	{
		if( $this->e >= 0 )
			return $this;
		else if( $this->m->sign() >= 0 )
			return new self($this->m->shift($this->e));
		else
			return new self($this->m->shift($this->e)->sub(new BigInt(1)));
	}


	/**
	 *  Return the integral part of $this as an int number
	 *
	 *  @return int  If $this is positive the floor() is returned,
	 *          otherwise the ceil() is returned, so for example 1.2
	 *          gives 1 while -1.2 gives -1.
	 *  @throws \OutOfRangeException  if the resulting number is too big to
	 *          fit int.
	 */
	function toInt()
	{
		return $this->m->shift($this->e)->toInt();
	}


	/**
	 *  Return the best approximating floating point representation.
	 *
	 *  WARNING: 1) very large positive numbers may give INF, while
	 *  negative ones may give -INF; 2) some BigFloat decimal numbers
	 *  cannot be represented exactly under the <b>float</b> binary
	 *  representation and must be rounded; 3) precision may be lost as
	 *  <b>float</b> numbers can hold typically only about 15 decimal
	 *  digits, and with very large number the precision decreases even
	 *  further. Because of that, conversion from BigFloat to
	 *  <b>float</b> requires maximum care and should be avoided.
	 *
	 *  @return float  The best approximating floating point representation.
	 */
	function toFloat()
	{
		return (float) $this->__toString();
	}


	/**
	 *  Return the integral part of $this as a BigInt number
	 *
	 *  @return BigInt  If $this is positive the floor() is returned,
	 *          otherwise the ceil() is returned, so for example 1.2
	 *          gives 1 while -1.2 gives -1.
	 */
	function toBigInt()
	{
		return $this->m->shift($this->e);
	}


	/**
	 *  Returns the square root of $this
	 *
	 *  The result il calculated up to the digit of power 1e$precision.
	 *  No rounding is performed. If a rounding is required, the sqrt()
	 *  can be calculated with higher precision ($precision+1) then
	 *  the result can be rounded at $precision. Example:
	 *
	 *  <pre>
	 *      $x = new BigFloat("2");
	 *      echo $x-&gt;sqrt(-5);  # displays 1.41421
	 *  </pre>
	 *
	 *  @param int $precision  The power of ten of the last digit to calculate.
	 *  @return self  The square root.
	 *  @throws \InvalidArgumentException if $this is negative.
	 */
	function sqrt($precision)
	{
		if( $this->sign() < 0 )
			throw new \InvalidArgumentException("negative argument $this");

		$onehalf = new self("0.5");
		$epsilon = new self("1e$precision");
		$y1 = $this->mul($onehalf)->add($onehalf);
		while( TRUE ){
			$y2 = $onehalf
				->mul( $y1->add( $this->div($y1, $precision) ) );
			if( $y1->sub($y2)->cmp($epsilon) < 0 )
				break;
			$y1 = $y2;
		}
		$y2 = $y2->trunc($precision);
		$y3 = $y2->add($epsilon);
		if( $y3->mul($y3)->cmp($this) <= 0 )
			return $y3;
		return $y2;
	}


	/**
	 *  Builds a new BigFloat number
	 *
	 *  <b>int</b> numbers and {@link it\icosaedro\bignumbers\BigInt} numbers can always be
	 *  converted exactly into BigFloat numbers.
	 *
	 *  If $x is a <b>string</b>, always use {@link ::isValid()} before
	 *  passing arbitrary strings, i.e. user submitted input. Spaces
	 *  are not allowed, so use {@link trim()}.
	 *
	 *  If $x is a <b>float</b> it is converted exactly into its
	 *  corresponding decimal representation.
	 *  BigFloat guarantees all the bits of a floating point number be
	 *  preserved, but this does not prevent "unexpected" values from
	 *  appearing.
	 *  INF and NAN yield exception as they cannot be represented
	 *  internally.
	 *
	 *  WARNING: avoid to use floating-point numbers at all as they may
	 *  give unexpected results due to the rounding that occurs in the
	 *  conversion process from decimal to binary form operated at the
	 *  parsing stage by the PHP interpreter. For example
	 *  printf("%.0F", 1e23) prints "99999999999999991611392" rather
	 *  than the expected "1" followed by 23 zeroes just because 1e23
	 *  requires 54 bits, one more than those available in a
	 *  double-precision IEEE 754 register; so $f must store a truncated
	 *  value. Also numbers as simple as 0.1 cannot be stored in a
	 *  float without loss of precision. To avoid these problems avoid
	 *  passing float numbers to the constructor <code>new
	 *  BigFloat(0.1)</code> but instead use the string notation
	 *  <code>new BigFloat("0.1")</code> as this latter preserves the
	 *  precision.
	 *
	 *  @param mixed $x  The value to be converted to BigFloat. It may be:
	 *         int, float, string or {@link it\icosaedro\bignumbers\BigInt}.
	 *  @return void
	 *  @throws \InvalidArgumentException if the argument passed is a
	 *          string that does not represent a valid floating point
	 *          number, or it is a non-finite floating point number,
	 *          or it is any another unexpected type of data.
	 */
	function __construct($x)
	{
		if( is_int($x) ){

			$i = (int) $x;

			if( $i == 0 ){
				$this->e = 0;
				if( self::$int_zero == NULL )
					self::$int_zero = new BigInt(0);
				$this->m = self::$int_zero;
			} else {
				$this->e = 0;
				$this->m = new BigInt($i);
				self::normalize($this);
			}

		} else if( $x instanceof BigInt ){

			$this->e = 0;
			$this->m = cast("it\\icosaedro\\bignumbers\\BigInt", $x);
			self::normalize($this);

		} else if( is_string($x) ){

			$s = (string) $x;

			if( ! self::isValid($s) )
				throw new \InvalidArgumentException("invalid argument `$s'");

			$s_l = strlen($s);

			# Parse exponent:
			$e_pos = strpos($s, "e");
			if( $e_pos === FALSE ){
				$e_pos = strpos($s, "E");
				if( $e_pos === FALSE )
					$e_pos = $s_l;
			}
			if( $e_pos < $s_l )
				$e = (int) substr($s, $e_pos+1, $s_l - $e_pos - 1);
			else
				$e = 0;

			# Parse mantissa:
			$dot_pos = strpos($s, ".");
			if( $dot_pos === FALSE ){
				if( $e_pos == $s_l )
					$m = $s;
				else
					$m = substr($s, 0, $e_pos);
			} else {
				$m = substr($s, 0, $dot_pos)
					. substr($s, $dot_pos+1, $e_pos - $dot_pos - 1);
				$e -= $e_pos - $dot_pos - 1;
			}

			$this->e = $e;
			$this->m = new BigInt($m);
			self::normalize($this);

		} else if( is_float($x) ){

			$f = (float) $x;

			if( ! is_finite($f) )
				throw new \InvalidArgumentException("argument infinite or not-a-number: $f");

			if( $f == 0.0 ){

				$this->e = 0;
				if( self::$int_zero == NULL )
					self::$int_zero = new BigInt(0);
				$this->m = self::$int_zero;

			} else if( $f == 1.0 ){

				$this->e = 0;
				$this->m = new BigInt(1);

			} else if( $f == floor($f) ){

				/* Only integral part. */

				$this->e = 0;
				$this->m = new BigInt($f);
				self::normalize($this);

			} else {

				/* $f has both an integral and a fractional part. */

				if( $f >= 0.0 )
					$sgn = +1;
				else {
					$f = - $f;
					$sgn = -1;
				}

				/* Extract integral part into partial result $r: */
				$r = new BigFloat(floor($f));

				/* Extract fractional part, bit by bit: */
				$rem = $f - floor($f);
				$half = new BigFloat("0.5");
				$p = $half;
				while( $rem != 0.0 ){
					$rem *= 2.0;
					if( $rem >= 1.0 ){
						$r = $r->add($p);
						$rem -= 1.0;
					}
					$p = $p->mul($half);
				}

				$this->e = $r->e;
				if( $sgn >= 0 )
					$this->m = $r->m;
				else
					$this->m = $r->m->minus();

				self::normalize($this);

			}

		} else {

			throw new \InvalidArgumentException("argument of invalid type: "
				. gettype($x));

		}

	}


	/*. int .*/ function getHash()
	{
		if( $this->hash == 0 )
			$this->hash = Hash::hashOfInt($this->e)
				^ $this->m->getHash();
		return $this->hash;
	}


	/**
	 * Compares this with another number for equality.
	 * @param object $other The other number.
	 * @return bool True if the other number is not NULL, belongs exactly to
	 * this same class (not extended) and carries the same value.
	 */
	function equals($other)
	{
		if( $other === $this )
			return TRUE;
		if( $other === NULL or get_class($other) !== __CLASS__ )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->cmp($other2) == 0;
	}

}
