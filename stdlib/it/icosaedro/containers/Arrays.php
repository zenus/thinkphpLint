<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../autoload.php";

use CastException;
use it\icosaedro\utils\Floats;


/**
	Utility functions to manipulate arrays. Arrays must have int indices
	T[int], with T that may be int, float, string or object of any class.
	If T is an object that implements {@link it\icosaedro\containers\Sortable}, a specific sorting
	function is provided, otherwise a custom {@link it\icosaedro\containers\Sorter} object may be
	provided. Example where sortable objects get sorted:

	<pre>
	require_once __DIR__ . "/all.php";
	use it\icosaedro\containers\Arrays;
	use it\icosaedro\containers\Sortable;
	class MyClass implements Sortable { ... }
	$a = array(new MyClass(...), new MyClass(...), ...);
	$sorted = cast("MyClass[int]", Arrays::sort($a));
	</pre>

	Note that the magic cast() function is required to retrieve the expected
	type of the resulting array. Specialized sorter objects can be used as well:

	<pre>
	use it\icosaedro\containers\Sorter;
	class MyClassSomefieldSorter implements Sorter { ... }
	$sorted = cast("MyClass[int]", Arrays::sortBySorter(
		$a, new MyClassSomefieldSorter() );
	</pre>

	Specialized methods are also provided to sort arrays of the basic PHP
	types. Their names follow the schema sortArrayOfT(), where T is the
	specific type of the elements of the T[int]. For strings, there is
	also sortArrayOfStringBySorter() where a custom sorter object may be
	provided. For objects, the sort() method assumes the objects implement the
	{@link it\icosaedro\containers\Sortable} interface, while the sortBySorter() allows to
	specify a custom sorter object. Example where an array of strings get sorted:

	<pre>
	$a = array("one", "two", "three");
	$sorted = Arrays::sortArrayOfString($a);
	</pre>
	
	Sorting methods use the Quicksort algorithm, that requires time
	t=k*n*log(n) where n is the number of elements in the array and k depends
	on the speed of the processor. On a Pentium 4 at 1,6 GHz the generic object
	sorting method performs k=5e-5 seconds, that is 0.34 seconds for n=1000
	and about 11 minutes for n=1e6.

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:04:04 $
*/
class Arrays {

	private static /*. void .*/ function sort_recurse(
		/*. Sortable[int] .*/ & $a,
		/*. int .*/ $l,
		/*. int .*/ $r)
		/*. throws CastException .*/
	{
		/*
		if( $r - $l == 1 ){
			if( $a[$l]->compareTo($a[$r]) > 0 ){
				$w = $a[$l];
				$a[$l] = $a[$r];
				$a[$r] = $w;
			}
			return;
		}
		*/
		$i = $l;
		$j = $r;
		$m = $l + (int) (($r - $l)/2);
		$p = $a[$m];
		do {
			while( $a[$i]->compareTo($p) < 0 )  $i++;
			while( $p->compareTo($a[$j]) < 0 )  $j--;
			if( $i <= $j ){
				$w = $a[$i];
				$a[$i] = $a[$j];
				$a[$j] = $w;
				$i++;
				$j--;
			} else {
				break;
			}
		} while( $i < $j );
		if( $l < $j)
			self::sort_recurse($a, $l, $j);
		if( $i < $r )
			self::sort_recurse($a, $i, $r);
	}

	
	/**
		Sorts an array of Sortable objects.
		@param Sortable[int] $a Array to be sorted. The array can be
		empty, but not NULL.
		@return Sortable[int] Sorted array. The indices, all of type
		int, start from 0. The passed array is not modified.
		@throws CastException If objects cannot be compared because belong
		to incompatible classes.
	*/
	static function sort($a)
	{
		$n = count($a);
		$b = /*. (Sortable[int]) .*/ array();
		foreach($a as $e)
			$b[] = $e;
		if( $n < 2 )
			return $b;
		self::sort_recurse($b, 0, $n-1);
		return $b;
	}

	
	private static /*. void .*/ function sort_recurse_by_sorter(
		/*. object[int] .*/ & $a,
		/*. int .*/ $l,
		/*. int .*/ $r,
		/*. Sorter .*/ $c)
		/*. throws CastException .*/
	{
		/*
		if( $r - $l == 1 ){
			if( $c->compare($a[$l], $a[$r]) > 0 ){
				$w = $a[$l];
				$a[$l] = $a[$r];
				$a[$r] = $w;
			}
			return;
		}
		*/
		$i = $l;
		$j = $r;
		$m = $l + (int) (($r - $l)/2);
		$p = $a[$m];
		do {
			while( $c->compare($a[$i], $p) < 0 )  $i++;
			while( $c->compare($p, $a[$j]) < 0 )  $j--;
			if( $i <= $j ){
				$w = $a[$i];
				$a[$i] = $a[$j];
				$a[$j] = $w;
				$i++;
				$j--;
			} else {
				break;
			}
		} while( $i < $j );
		if( $l < $j)
			self::sort_recurse_by_sorter($a, $l, $j, $c);
		if( $i < $r )
			self::sort_recurse_by_sorter($a, $i, $r, $c);
	}

	
	/**
		Sorts array of objects with custom sorter object.
		@param object[int] $a Array to be sorted.
		@param Sorter $c The sorter defining the order of the elements.
		@return object[int] Sorted array. The indices, all of type
		int, start from 0. The passed array is not modified.
		@throws CastException If the elements of the array does not belong
		to the class expected by the sorter.
	*/
	static function sortBySorter($a, $c)
	{
		$n = count($a);
		$b = /*. (object[int]) .*/ array();
		foreach($a as $e)
			$b[] = $e;
		if( $n < 2 )
			return $b;
		self::sort_recurse_by_sorter($b, 0, $n-1, $c);
		return $b;
	}


	private static /*. void .*/ function sort_recurse_array_of_int(
		/*. int[int] .*/ & $a,
		/*. int .*/ $l,
		/*. int .*/ $r)
	{
		$i = $l;
		$j = $r;
		$m = $l + (int) (($r - $l)/2);
		$p = $a[$m];
		do {
			while( $a[$i] < $p )  $i++;
			while( $p < $a[$j] )  $j--;
			if( $i <= $j ){
				$w = $a[$i];
				$a[$i] = $a[$j];
				$a[$j] = $w;
				$i++;
				$j--;
			} else {
				break;
			}
		} while( $i < $j );
		if( $l < $j)
			self::sort_recurse_array_of_int($a, $l, $j);
		if( $i < $r )
			self::sort_recurse_array_of_int($a, $i, $r);
	}

	
	/**
		Sort array of int numbers in ascending order.
		@param int[int] $a Array to be sorted.
		@return int[int] Sorted array. The indices, all of type
		int, start from 0. The passed array is not modified.
	*/
	static function sortArrayOfInt($a)
	{
		$n = count($a);
		$b = /*. (int[int]) .*/ array();
		foreach($a as $e)
			$b[] = $e;
		if( $n < 2 )
			return $b;
		self::sort_recurse_array_of_int($b, 0, $n-1);
		return $b;
	}
	

	private static /*. void .*/ function sort_recurse_array_of_float(
		/*. float[int] .*/ & $a,
		/*. int .*/ $l,
		/*. int .*/ $r)
	{
		$i = $l;
		$j = $r;
		$m = $l + (int) (($r - $l)/2);
		$p = $a[$m];
		do {
			while( Floats::compare($a[$i], $p) < 0 )  $i++;
			while( Floats::compare($p, $a[$j]) < 0 )  $j--;
			if( $i <= $j ){
				$w = $a[$i];
				$a[$i] = $a[$j];
				$a[$j] = $w;
				$i++;
				$j--;
			} else {
				break;
			}
		} while( $i < $j );
		if( $l < $j)
			self::sort_recurse_array_of_float($a, $l, $j);
		if( $i < $r )
			self::sort_recurse_array_of_float($a, $i, $r);
	}

	
	/**
		Sort array of float numbers in ascending order.
		Applies the total ordering imposed by {@link it\icosaedro\utils\Floats::compare()}.
		@param float[int] $a Array to be sorted.
		@return float[int] Sorted array. The indices, all of type
		int, start from 0. The passed array is not modified.
	*/
	static function sortArrayOfFloat($a)
	{
		$n = count($a);
		$b = /*. (float[int]) .*/ array();
		foreach($a as $e)
			$b[] = $e;
		if( $n < 2 )
			return $b;
		self::sort_recurse_array_of_float($b, 0, $n-1);
		return $b;
	}


	private static /*. void .*/ function sort_recurse_array_of_string(
		/*. string[int] .*/ & $a,
		/*. int .*/ $l,
		/*. int .*/ $r)
	{
		$i = $l;
		$j = $r;
		$m = $l + (int) (($r - $l)/2);
		$p = $a[$m];
		do {
			while( strcmp($a[$i], $p) < 0 )  $i++;
			while( strcmp($p, $a[$j]) < 0 )  $j--;
			if( $i <= $j ){
				$w = $a[$i];
				$a[$i] = $a[$j];
				$a[$j] = $w;
				$i++;
				$j--;
			} else {
				break;
			}
		} while( $i < $j );
		if( $l < $j)
			self::sort_recurse_array_of_string($a, $l, $j);
		if( $i < $r )
			self::sort_recurse_array_of_string($a, $i, $r);
	}

	
	/**
		Sort array of strings in ascending order.
		Strings are compared with the standard function {@link strcmp()}.
		@param string[int] $a Array to be sorted.
		@return string[int] Sorted array. The indices, all of type
		int, start from 0. The passed array is not modified.
	*/
	static function sortArrayOfString($a)
	{
		$n = count($a);
		$b = /*. (string[int]) .*/ array();
		foreach($a as $e)
			$b[] = $e;
		if( $n < 2 )
			return $b;
		self::sort_recurse_array_of_string($b, 0, $n-1);
		return $b;
	}


	private static /*. void .*/ function sort_recurse_array_of_string_by_sorter(
		/*. string[int] .*/ & $a,
		/*. int .*/ $l,
		/*. int .*/ $r,
		/*. StringSorter .*/ $c)
	{
		$i = $l;
		$j = $r;
		$m = $l + (int) (($r - $l)/2);
		$p = $a[$m];
		do {
			while( $c->compare($a[$i], $p) < 0 )  $i++;
			while( $c->compare($p, $a[$j]) < 0 )  $j--;
			if( $i <= $j ){
				$w = $a[$i];
				$a[$i] = $a[$j];
				$a[$j] = $w;
				$i++;
				$j--;
			} else {
				break;
			}
		} while( $i < $j );
		if( $l < $j)
			self::sort_recurse_array_of_string_by_sorter($a, $l, $j, $c);
		if( $i < $r )
			self::sort_recurse_array_of_string_by_sorter($a, $i, $r, $c);
	}

	
	/**
		Sort array of strings according to the specific sorter.
		@param string[int] $a Array to be sorted.
		@param StringSorter $c Custom string sorter.
		@return string[int] Sorted array. The indices, all of type
		int, start from 0. The passed array is not modified.
	*/
	static function sortArrayOfStringBySorter($a, $c)
	{
		$n = count($a);
		$b = /*. (string[int]) .*/ array();
		foreach($a as $e)
			$b[] = $e;
		if( $n < 2 )
			return $b;
		self::sort_recurse_array_of_string_by_sorter($b, 0, $n-1, $c);
		return $b;
	}


	/**
	 * Joins an array of printable objects in a single string.
	 * @param Printable[int] $objects Array of printable objects.
	 * @param string $separator Separator string.
	 * @return string Result of the joining.
	 */
	static function implode($objects, $separator)
	{
		$res = "";
		$n = 0;
		foreach($objects as $o){
			if( $n > 0 )
				$res .= $separator;
			$res .= $o;
			$n++;
		}
		return $res;
	}

}
