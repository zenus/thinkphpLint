<?php

/*.
	require_module 'standard';
	require_module 'spl';
.*/

class MyIterator implements Iterator
{
    private /*. array[int]string .*/ $a;

    public /*. void .*/ function __construct(/*. array[int]string .*/ $a)
    {
		$this->a = $a;
    }

    public /*. void .*/ function rewind() {
        echo "rewinding\n";
        reset($this->a);
    }

    public /*. string .*/ function current() {
        $e = current($this->a);
        echo "current: ";
		var_dump($e);
		if($e === FALSE)
			return NULL;
		else
			return (string) $e;
    }

    public /*. mixed .*/ function key() {
        $k = key($this->a);
        echo "key: ";
		var_dump($k);
        return (int) $k;
    }

    public /*. void .*/ function next() {
        $e = next($this->a);
        echo "next: ";
		var_dump($e);
    }

    public /*. bool .*/ function valid() {
        $v = $this->current() !== NULL;
        echo "valid: ";
		var_dump($v);
        return $v;
    }
}

$values = array("zero", "one", "two");


echo "Testing MyIterator:\n";
$it = new MyIterator($values);
foreach ($it as $k => $v) {
    print "found key=$k, value=" . $v ."\n";
	$i = 10 + $k;
}


class MyIteratorAggregate
implements IteratorAggregate
{
	
    private /*. array[int]string .*/ $a;

    public /*. void .*/ function __construct(/*. array[int]string .*/ $a)
    {
		$this->a = $a;
    }


	/*. MyIterator .*/ function getIterator()
	{
		return new MyIterator($this->a);
	}

}


echo "Testing MyIteratorAggregate:\n";
$ita = new MyIteratorAggregate($values);
foreach ($ita as $k2 => $v2) {
    print "found key=$k2, value=" . $v2 ."\n";
	$i = 10 + $k2;
}
