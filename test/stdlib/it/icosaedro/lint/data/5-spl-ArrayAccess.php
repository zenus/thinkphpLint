#!/bin/php530 -c.
<?php

/*.
	require_module 'spl';
.*/

class TestArrayAccess
implements ArrayAccess
{
	private $a = array("zero", "one", "two");


	/*. bool  .*/ function offsetExists(/*. mixed .*/ $offset)
	{
		if( ! is_int($offset) )
			return FALSE;
		$k = (int) $offset;
		return $k >= 0 && $k < count($this->a);
	}


	/*. string .*/ function offsetGet(/*. mixed .*/ $offset)
	{
		if( ! is_int($offset) )
			throw new InvalidArgumentException("offset must be int");
		$k = (int) $offset;
		if( $k < 0 || $k >= count($this->a) )
			throw new OutOfRangeException("offset = $k");
		return $this->a[$k];
	}

	
	/*. void  .*/ function offsetSet(/*. mixed .*/ $offset,
		/*. string .*/ $value)
	{
		die("unimplemented");
	}


	/*. void  .*/ function offsetUnset(/*. mixed .*/ $offset)
	{
		die("unimplemented");
	}

}


$x = new TestArrayAccess();
for( $i = 0; $x->offsetExists($i); $i++ )
	echo $x[$i], "\n";



$test = new SplFixedArray(4);
$test[4] = 'test';
echo (string) $test[1];
