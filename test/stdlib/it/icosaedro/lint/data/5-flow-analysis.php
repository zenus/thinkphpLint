<?php
# Static flow analysis test:
# 1. Unreachable code detection.
# 2. Missing return.

/*. require_module 'standard'; .*/


# Generic boolean expr of indeterminate value (in fact, PHPLint cannot guess
# that this variable is actually assigned once):
$flag = false;

class AException extends Exception{}
class BException extends AException{}
class CException extends BException{}



/*. int .*/ function f()
{
	if( false )
		return 1;
	# BUG
}


/*. string .*/ function g(/*. int .*/ $i)
{
	switch($i){
	case 0: return "zero";
	case 1:
	case 2: return "one or two";
	case 3: echo "three"; break;  # <-- BUG
	default: die("unexpected value $i");
	}
}


if( true )
	exit;
else
	throw new Exception();

while(true){
	if( true )
		exit;
	else
		throw new Exception();
	echo 123;
}

do {
	if( true )
		exit;
	else
		break;
} while(true);

while(true){
}

while(false){
}

while(TRUE){
}


while(true){
	echo 1;
}

while(false){
	echo 1;
}

while(TRUE){
	break;
}

do {
	echo 1;
} while(false);

define("DEBUG", true);

do {
	echo 1;
} while(DEBUG);

do {
	if(true) break;
	echo 1;
} while(true);

for(;;){
	continue;
}


for(;true;){
	continue;
}



/*. void .*/ function test_vars_definitely_assigned()
{
	global $flag;

	if(false){
		$a = 1;
	}
	echo $a;

	if(false){
		$v = 1;
		$u = 2;
	} else {
		#echo $v, $u;
		$v = 3;
		$w = 4;
	}
	echo $v, $u, $w;

	if(false){
		$x = 1;
		$y = 2;
	} elseif(false){
		$x = 3;
	}
	echo $x, $y;
	$y = "";

# Test 4: variables preserve their type in any execution path:
	if(true)
		$t4_x = 1;
	else
		$t4_x = "";

# Test 5: nested if():
	if(false){
		$t5_a = 1;
		$t5_b = 2;
	} else {
		$t5_a = 3;
		if( $t5_a > 9 )
			$t5_b = 4;
	}
	echo $t5_a, $t5_b;

# Test 6: excluded branches in if():
	if(false){
		$t6_a = 1;
		return;
	}
	echo $t6_a;

	if(false){
		$t6_b = 1;
		return;
	} else {
		$t6_c = 1;
	}
	echo $t6_b, $t6_c;

	if(false){
		$t6_d = 1;
	} else {
		$t6_e = 1;
		return;
	}
	echo $t6_d, $t6_e;

	if(false){
		$t6_f = 1;
		return;
	} else {
		$t6_g = 1;
		return;
	}
	echo $t6_f, $t6_g;

# Test 7: while():
	while($flag){
		$t7_a = 1;
		if(true)
			$t7_b = 1;
	}
	echo $t7_a, $t7_b;

	while(false){
		$t7_c = 1;
		if(true)
			$t7_d = 1;
	}
	echo $t7_c, $t7_d;

	while(true){
		$t7_e = 1;
		if(true)
			$t7_f = 1;
	}
	echo $t7_e, $t7_f;

# Test 8: switch():
	switch(1){
		case 1: $t8_a = 1; break;
		default: $t8_a = 2; break;
	}
	echo $t8_a;

	switch(1){
		case 1: $t8_b = 1; break;
		default: $t8_c = 2; break;
	}
	echo $t8_b, $t8_c;

	switch(1){
		case 1:
			$t8_d = 1;
			if($flag)
				$t8_e = 1;
			break;
		default: $t8_d = 2; break;
	}
	echo $t8_d, $t8_e;

	switch(1){
		case 1: $t8_f = 1; break;
		default: return;
	}
	echo $t8_f;

	switch(1){
	case 1:  return;
	default:
		if($flag)
			$t8_g = 1;
		else
			$t8_g = 1;
	}
	echo $t8_g;

# Test 9: try{}catch(){}
	
	try {
		$t9_a = 1;
		if($t9_a == 1)
			throw new AException();
		else if($t9_a == 2)
			throw new BException();
	}
	catch(CException $e){
		die("exception!");
	}
	catch(BException $e){
		$t9_a = 1;
	}
	catch(AException $e){
		$t9_a = 1;
		$t9_b = 1;
	}
	echo $t9_a, $t9_b;

	# Test fixed bug:
	$undefined = $undefined;
	echo $undefined;
}


/*. void .*/ function advanced_data_flow_analysis()
{
	$flag = true;


	# Multiple `break' in sequence:
	do {
		$a = 1;
		if( $flag )  break;
		$b = 1;
		if( $flag )  break;
	} while( $flag );
	echo $a, $b;
	# FIXME: $b should result not assigned


	# Multiple `break' in sequence:
	switch(1){

	case 1:
		$c = 1;
		if( $flag )  break;
		$d = 1;
		break;

	default:
		return;
	}
	echo $c, $d;
	# FIXME: $d should result not assigned
}

echo "still alive\n";
