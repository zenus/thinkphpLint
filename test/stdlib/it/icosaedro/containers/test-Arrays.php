<?php

/*. require_module 'spl'; .*/

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../../../stdlib/all.php";

use RuntimeException;
use it\icosaedro\utils\Floats;


function testArrayOfInt()
{
	echo "\nTesting array of int:\n";
	echo "Building array...\n";
	# Always generate the same sequence for testing:
	srand(1726354);
	$a = /*. (array[int]int) .*/ array();
	for($i = 0; $i < 1000; $i++)
		$a[$i] = rand(0, 999);

	echo "Sorting...\n";
	$start = (float)microtime(TRUE);
	$r = Arrays::sortArrayOfInt($a);
	$delta = (float)microtime(TRUE) - $start;
	echo "finished ($delta s).\n";

	echo "Check ordering...\n";
	$n = count($r);
	for($i = 0; $i < $n-1; $i++)
		if( $r[$i] > $r[$i+1] )
			throw new RuntimeException( "error at index $i");
}


function testArrayOfFloat()
{
	echo "\nTesting array of float:\n";
	echo "Building array...\n";
	# Always generate the same sequence for testing:
	srand(1726354);
	$a = /*. (array[int]float) .*/ array();
	for($i = 0; $i < 1000; $i++)
		$a[$i] = (rand(0, 999) - 500.0)/10.0;

	echo "Sorting...\n";
	$start = (float)microtime(TRUE);
	$r = Arrays::sortArrayOfFloat($a);
	$delta = (float)microtime(TRUE) - $start;
	echo "finished ($delta s).\n";

	echo "Check ordering...\n";
	$n = count($r);
	for($i = 0; $i < $n-1; $i++)
		if( Floats::compare($r[$i], $r[$i+1]) > 0 )
			throw new RuntimeException( "error at index $i");
}


/**
	Simple class of Comparable objects: the data is a bare int.
*/
class IntegerBox implements Sortable {

	public /*. int .*/ $value = 0;

	/*. void .*/ function __construct(/*. int .*/ $value)
	{
		$this->value = $value;
	}


	public /*. string .*/ function __toString()
	{
		return (string) $this->value;
	}


	public /*. int .*/ function intValue()
	{
		return $this->value;
	}

	public /*. int .*/ function compareTo(/*. object .*/ $other)
	{
		if( $this === $other )
			return 0;
		$other2 = cast("it\\icosaedro\\containers\\IntegerBox", $other);
		if( $this->value < $other2->value )
			return -1;
		else if( $this->value > $other2->value )
			return +1;
		else
			return 0;
	}
	
	/*. bool .*/ function equals(/*. object .*/ $other)
	{
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		try {
			$other2 = cast(__CLASS__, $other);
		}
		catch(\CastException $e){
			return FALSE;
		}
		return $this->value == $other2->value;
	}

}


class IntegerBoxComparator implements Sorter {

	/*. int .*/ function compare(/*. object .*/ $a, /*. object .*/ $b)
	{
		$ai = cast("it\\icosaedro\\containers\\IntegerBox", $a)->intValue();
		$bi = cast("it\\icosaedro\\containers\\IntegerBox", $b)->intValue();
		# May overflow, giving wrong result:
		#return $a - $b;
		# Compare int avoiding overflow:
		if( $ai < $bi )
			return -1;
		else if( $ai == $bi )
			return 0;
		else
			return +1;
	}

}


/*. void .*/ function print_arr(/*. array[int]IntegerBox .*/ $a)
{
	for($i = 0; $i < count($a); $i++)
		echo $a[$i], " ";
}


function testArrayOfIntegerBox()
{
	echo "\nTesting array of IntegerBox:\n";
	echo "Building array...\n";
	# Always generate the same sequence for testing:
	srand(1726354);
	$a = /*. (array[int]IntegerBox) .*/ array();
	for($i = 0; $i < 1000; $i++)
		$a[$i] = new IntegerBox(rand(0, 999));

	echo "Sorting...\n";
	$start = (float)microtime(TRUE);
	$r = cast("array[int]it\\icosaedro\\containers\\IntegerBox", Arrays::sort($a));
	$delta = (float)microtime(TRUE) - $start;
	echo "finished ($delta s).\n";

	#print_arr($r);

	echo "Check ordering...\n";
	$n = count($r);
	for($i = 0; $i < $n-1; $i++)
		if( $r[$i]->intValue() > $r[$i+1]->intValue() )
			throw new RuntimeException("error at index $i");
}


function testArrayOfIntegerBoxWithComparator()
{
	echo "\nTesting array of IntegerBox with Comparator:\n";
	echo "Building array...\n";
	# Always generate the same sequence for testing:
	srand(1726354);
	$a = /*. (array[int]IntegerBox) .*/ array();
	for($i = 0; $i < 1000; $i++)
		$a[$i] = new IntegerBox(rand(0, 999));

	echo "Sorting...\n";
	$start = (float)microtime(TRUE);
	$r = cast("array[int]it\\icosaedro\\containers\\IntegerBox", Arrays::sortBySorter($a, new IntegerBoxComparator()));
	$delta = (float)microtime(TRUE) - $start;
	echo "finished ($delta s).\n";

	#print_arr($r);

	echo "Check ordering...\n";
	$n = count($r);
	for($i = 0; $i < $n-1; $i++)
		if( $r[$i]->intValue() > $r[$i+1]->intValue() )
			throw new RuntimeException( "error at index $i");
}


class testArrays extends \it\icosaedro\utils\TestUnit {

	function run()
	{
		testArrayOfInt();
		testArrayOfFloat();
		testArrayOfIntegerBox();
		testArrayOfIntegerBoxWithComparator();
	}
	
}

$tu = new testArrays();
$tu->start();
