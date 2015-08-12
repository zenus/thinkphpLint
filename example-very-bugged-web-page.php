<HTML>
<BODY>
<H1>BUGGEDD WeB pAGE</H1>
<?php
	/*. require_module 'standard'; .*/

	define('MY_LIBS', '/here/they/are/');
	include(MY_LIBS . "commons");
	require("more-stuff");

	define(Pi, 3.14159);           # it should be define('Pi', ...
	$debugging = TRUE;

	function PrintNumber($i, $k)
	{
		global $debugging;         # global var. not used...
		echo("$k");                # will print nothing
		                           # argument $i not used
	}

	for($i=1; $i<=10; $i++){
		printNumber($i, 2, 3);     # misspelled; too many arguments
	}

	if( $debuging ){               # give me a 'g'...
		echo "Today is: ", date("Y-m-d");
		                           # ...or the date will never
		                           # be printed!
	}
	$first_name = "John';          # unclosed double quote
	$last_name = 'Smith";          # the variable isn't assigned...

	echo $first_name, " ", $last_name;
	                               # $last_name isn't defined

	$radius = 10.0;
	$circum = 2.0 * PI * $radius;  # misspelled constant name
	echo "Circumference=$circum";  # will print 0 for any $radius...

	if( ! file_exists("important-data.dat") ){
		mai1("webmaster", "ALERT, important data missing!", "");
		                           # this mail will never be sent
		                           # because "mail" is misspelled
	}

	# ...and still "php -l" gives "no errors" :-)

?>
</BODY></HTML>
