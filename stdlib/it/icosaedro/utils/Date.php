<?php

/*.
	require_module 'spl';
	require_module 'pcre';
.*/

namespace it\icosaedro\utils;

require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;
use RuntimeException;
use ErrorException;
use Serializable;
use CastException;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Hashable;
use it\icosaedro\containers\Hash;
use it\icosaedro\containers\Sortable;

/**
	Holds a Gregorian date. The value is immutable.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:20:37 $
*/
class Date implements Printable, Hashable, Sortable, Serializable
{
	private /*. int .*/ $y = 0, $m = 0, $d = 0;
	private /*. string .*/ $as_string;
	private /*. int .*/ $hash = 0;


	/**
		Tells if the year is a leap year, that is, February as 29 days.
		@param int $y Year under test.
		@return bool True if the year is a leap year.
	*/
	static function isLeapYear($y)
	{
		return $y % 400 == 0
			|| $y % 100 != 0 and $y % 4 == 0;
	}


	/**
		Builds a new gregorian date.
		@param int $y  Year in the range [0,9999].
		@param int $m  Month in the range [1,12].
		@param int $d  Day in the range [1,31].
		@return void
		@throws InvalidArgumentException  If the date is invalid.
	*/
	function __construct($y, $m, $d)
	{
		if(
			! (
				0 <= $y and $y <= 9999
				and 1 <= $m and $m <= 12
				and 1 <= $d and $d <= 31
			)
			or ( ($m == 4 or $m == 6 or $m == 9) and $d > 30 )
			or ( $m == 2 and ($d > 29 or ! self::isLeapYear($y) and $d > 28) )
		)
			throw new InvalidArgumentException("invalid date: $y-$m-$d");

		$this->y = $y;
		$this->m = $m;
		$this->d = $d;
	}


	/**
		Parse a date.
		@param string $v Date in the format Y-M-D. The ranges of the values
		are the same of the constructor.
		@return self
		@throws InvalidArgumentException Invalid date.
	*/
	static function parse($v)
	{
		if( 1 !== preg_match("#^[0-9]{1,4}-[0-9]{1,2}-[0-9]{1,2}\$#", $v) )
			throw new InvalidArgumentException($v);
		$a = explode("-", $v);
		return new self((int) $a[0], (int) $a[1], (int) $a[2]);
	}


	/**
		Returns the year.
		@return int Year.
	*/
	function getYear()
	{
		return $this->y;
	}


	/**
		Returns the month.
		@return int Month in [1,12].
	*/
	function getMonth()
	{
		return $this->m;
	}


	/**
		Returns the day.
		@return int Day in [1,31].
	*/
	function getDay()
	{
		return $this->d;
	}


	/**
		Returns the date as a string.
		@return string The date in the form YYYY-MM-DD.
	*/
	function __toString()
	{
		if( $this->as_string === NULL )
			$this->as_string = sprintf("%04d-%02d-%02d",
				$this->y, $this->m, $this->d);
		return $this->as_string;
	}

	
	/*. int .*/ function getHash()
	{
		if( $this->hash == 0 )
			$this->hash = 416 * $this->y + 32 * $this->m + $this->d;
		return $this->hash;
	}


	/**
		Compares this date against another date.
		@param object $other The other date.
		@return int Negative if $this &lt; $other, positive if $this &gt; $other,
		zero if the same date.
		@throws CastException If $other is NULL or is not exactly instance of
		Date.
	*/
	function compareTo($other)
	{
		if( $other === NULL )
			throw new \CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new CastException("expected " . __CLASS__ . " but got "
			. get_class($other));
		$other2 = cast(__CLASS__, $other);
		$a = 416 * $this->y + 32 * $this->m + $this->d;
		$b = 416 * $other2->y + 32 * $other2->m + $other2->d;
		return $a - $b;
	}


	/*
		Returns the current timezone offset.
		@return int Local timezone offset (s).
	static function getTimezoneOffset()
	{
		$d = new \DateTime();
		return $d->getTimezone()->getOffset($d);
	}
	*/


	/**
		Factory method that returns the today date according to the default
		configuration of the server. You may want to set your actual timezone
		before calling this method or any other PHP function involving date and
		time, for example
		<pre> date_default_timezone_set('Europe/Berlin'); </pre>
		See the complete list of the supported timezones at
		{@link http://www.php.net/manual/en/timezones.php}.
		@return self Today date.
	*/
	static function today()
	{
		return self::parse( date("Y-m-d") );
	}


	/**
		Tells if the another date equals this one.
		@param object $other Another date.
		@return bool True if the other date is not NULL, belongs to this same
		exact class (not extended) and contains the same date.
	*/
	function equals($other)
	{
		if( $other === NULL )
			return FALSE;
		# Can't throw exceptions by contract:
		try {
			return $this->compareTo($other) == 0;
		}
		catch(CastException $e){}
		return FALSE;
	}


	/*. string .*/ function serialize()
	{
		return $this->__toString();
	}


	/*. void .*/ function unserialize(/*. string .*/ $serialized)
		/*. throws RuntimeException .*/
	{
		try {
			$d = self::parse($serialized);
		}
		catch(InvalidArgumentException $e){
			throw new RuntimeException($e->getMessage());
		}
		$this->y = $d->y;
		$this->m = $d->m;
		$this->d = $d->d;
	}

}
