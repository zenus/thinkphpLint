<?php  # Test: arguments passed by reference.

function f1(
	/*. int        .*/ & $a,
	/*. return int .*/ & $b,
	/*. int        .*/ & $c = 1,
	/*. return int .*/ & $d = 1
)
{
	$b = 1;
	$d = 1;
}

function test1()
{
	# Actual args all assigned, use default:
	$x = 1;
	$y = 1;
	f1($x, $y);
	echo $x, $y;
}


function test2()
{
	# Actual args all assigned:
	$x = 1;
	$y = 1;
	$z = 1;
	$t = 1;
	f1($x, $y, $z, $t);
	echo $x, $y, $z, $t;
}


function test3()
{
	# Actual args not assigned, use default:
	f1($x, $y);
	echo $x, $y;
}


function test4()
{
	# Actual args not assigned:
	f1($x, $y, $z, $t);
	echo $x, $y, $z, $t;
}


function f2(
	/*. int        .*/ & $a,
	/*. return int .*/ & $b,
	/*. int        .*/ & $c = 1,
	/*. return int .*/ & $d = 1
)
{
	# No arg used.
}


function f3(
	/*. int        .*/ & $a,
	/*. return int .*/ & $b,
	/*. int        .*/ & $c = 1,
	/*. return int .*/ & $d = 1
)
{
	# All args as RHS:
	echo $a, $b, $c, $d;
}


function f4(
	/*. int        .*/ & $a,
	/*. return int .*/ & $b,
	/*. int        .*/ & $c = 1,
	/*. return int .*/ & $d = 1
)
{
	# All args as LHS:
	$a = 1;
	$b = 1;
	$c = 1;
	$d = 1;
}


function f5(
	/*. int        .*/ & $a,
	/*. return int .*/ & $b)
{
	if($a > 1)
		$b = 1;
	else
		return;
}


/*. forward void function f6(
	int        & $a,
	return int & $b,
	int        & $c =,
	return int & $d =); .*/

/*. void .*/ function f6(
	/*. int        .*/ & $a,
	/*. return int .*/ & $b,
	/*. int        .*/ & $c = 1,
	/*. return int .*/ & $d = 1
)
{
	$b = 1;
	$d = 1;
}


# Invalid implementation of proto:
/*. forward void function f7(
	int        & $a,
	return int & $b,
	int        & $c =,
	return int & $d =); .*/

/*. void .*/ function f7(
	/*. int        .*/ & $a,
	/*. return int .*/ & $b,
	/*. int        .*/   $c = 1,
	/*. return int .*/ & $d = 1
)
{
	$b = 1;
	$d = 1;
}


# The same tests as before, but inside a class:
class aClass {

	function f1(
		/*. int        .*/ & $a,
		/*. return int .*/ & $b,
		/*. int        .*/ & $c = 1,
		/*. return int .*/ & $d = 1
	)
	{
		$b = 1;
		$d = 1;
	}

	function test1()
	{
		# Actual args all assigned, use default:
		$x = 1;
		$y = 1;
		$this->f1($x, $y);
		echo $x, $y;
	}


	function test2()
	{
		# Actual args all assigned:
		$x = 1;
		$y = 1;
		$z = 1;
		$t = 1;
		$this->f1($x, $y, $z, $t);
		echo $x, $y, $z, $t;
	}


	function test3()
	{
		# Actual args not assigned, use default:
		$this->f1($x, $y);
		echo $x, $y;
	}


	function test4()
	{
		# Actual args not assigned:
		$this->f1($x, $y, $z, $t);
		echo $x, $y, $z, $t;
	}


	function f2(
		/*. int        .*/ & $a,
		/*. return int .*/ & $b,
		/*. int        .*/ & $c = 1,
		/*. return int .*/ & $d = 1
	)
	{
		# No arg used.
	}


	function f3(
		/*. int        .*/ & $a,
		/*. return int .*/ & $b,
		/*. int        .*/ & $c = 1,
		/*. return int .*/ & $d = 1
	)
	{
		# All args as RHS:
		echo $a, $b, $c, $d;
	}


	function f4(
		/*. int        .*/ & $a,
		/*. return int .*/ & $b,
		/*. int        .*/ & $c = 1,
		/*. return int .*/ & $d = 1
	)
	{
		# All args as LHS:
		$a = 1;
		$b = 1;
		$c = 1;
		$d = 1;
	}


	function f5(
		/*. int        .*/ & $a,
		/*. return int .*/ & $b)
	{
		if($a > 1)
			$b = 1;
		else
			return;
	}


	/*. forward void function f6(
		int        & $a,
		return int & $b,
		int        & $c =,
		return int & $d =); .*/

	/*. void .*/ function f6(
		/*. int        .*/ & $a,
		/*. return int .*/ & $b,
		/*. int        .*/ & $c = 1,
		/*. return int .*/ & $d = 1
	)
	{
		$b = 1;
		$d = 1;
	}


	# Invalid implementation of proto:
	/*. forward void function f7(
		int        & $a,
		return int & $b,
		int        & $c =,
		return int & $d =); .*/

	/*. void .*/ function f7(
		/*. int        .*/ & $a,
		/*. return int .*/ & $b,
		/*. int        .*/   $c = 1,
		/*. return int .*/ & $d = 1
	)
	{
		$b = 1;
		$d = 1;
	}

}


interface Interface1 {
	/*. void .*/ function m1(/*. int .*/ &$a, /*. return int .*/ &$b);
	/*. void .*/ function m2(/*. int .*/ &$a, /*. return int .*/ &$b);
}

class Concrete1 implements Interface1 {

	/*. void .*/ function m1(/*. int .*/ &$a, /*. return int .*/ &$b)
	{
		if( $a < 0 )
			$b = 0;
		else
			$b = $a;
	}

	/*. void .*/ function m2(/*. int .*/ &$a, /*. int .*/ &$b)
	{
	}

}


abstract class AbsClass1 {
	abstract /*. void .*/ function m1(/*. int .*/ &$a, /*. return int .*/ &$b);
	abstract /*. void .*/ function m2(/*. int .*/ &$a, /*. return int .*/ &$b);
}

class Concrete2 extends AbsClass1 {

	/*. void .*/ function m1(/*. int .*/ &$a, /*. return int .*/ &$b)
	{
		if( $a < 0 )
			$b = 0;
		else
			$b = $a;
	}

	/*. void .*/ function m2(/*. int .*/ &$a, /*. int .*/ &$b)
	{
	}

}

