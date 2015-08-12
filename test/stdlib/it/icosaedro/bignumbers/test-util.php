<?php

/*. require_module 'spl'; .*/

error_reporting(E_ALL|E_STRICT);

$err = 0;


/*. void .*/ function test(
	/*. string .*/ $test_name,
	/*. string .*/ $actual_result,
	/*. string .*/ $expected_result)
{
	if( ! is_string($actual_result) or ! is_string($expected_result) )
		throw new RuntimeException("argument is not a string");
	if( empty($test_name) )
		$test_name = "[$expected_result]";
	$r = "Test $test_name: ";
	echo $r, str_repeat(" ", 60-strlen($r));
	if( $actual_result === $expected_result ){
		echo "ok\n";
	} else {
		echo "FAILED\n\texpected `$expected_result' got `$actual_result'\n";
		$GLOBALS['err']++;
	}
}

?>
