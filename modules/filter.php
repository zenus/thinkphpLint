<?php
/** Filter Functions.

	See: {@link http://www.php.net/manual/en/ref.filter.php}

	@deprecated Very poorly written interface with too many 'mixed' values,
	difficult to validate automatically, leaving to the programmer all the
	responsability to handle properly arguments and returned values. The
	returned values are also 'mixed' with a complex semantic based on the
	special values NULL, FALSE/TRUE, etc. It is questionable if this library
	really adds security. [Umberto Salsi]
	@package filter
*/

# These values are all dummy:
define('INPUT_POST', 1);
define('INPUT_GET', 2);
define('INPUT_COOKIE', 3);
define('INPUT_ENV', 4);
define('INPUT_SERVER', 5);
define('INPUT_SESSION', 6);
define('INPUT_REQUEST', 7);
define('FILTER_FLAG_NONE', 8);
define('FILTER_REQUIRE_SCALAR', 9);
define('FILTER_REQUIRE_ARRAY', 10);
define('FILTER_FORCE_ARRAY', 11);
define('FILTER_NULL_ON_FAILURE', 12);
define('FILTER_VALIDATE_INT', 13);
define('FILTER_VALIDATE_BOOLEAN', 14);
define('FILTER_VALIDATE_FLOAT', 15);
define('FILTER_VALIDATE_REGEXP', 16);
define('FILTER_VALIDATE_URL', 17);
define('FILTER_VALIDATE_EMAIL', 18);
define('FILTER_VALIDATE_IP', 19);
define('FILTER_DEFAULT', 20);
define('FILTER_UNSAFE_RAW', 21);
define('FILTER_SANITIZE_STRING', 22);
define('FILTER_SANITIZE_STRIPPED', 23);
define('FILTER_SANITIZE_ENCODED', 24);
define('FILTER_SANITIZE_SPECIAL_CHARS', 25);
define('FILTER_SANITIZE_EMAIL', 26);
define('FILTER_SANITIZE_URL', 27);
define('FILTER_SANITIZE_NUMBER_INT', 28);
define('FILTER_SANITIZE_NUMBER_FLOAT', 29);
define('FILTER_SANITIZE_MAGIC_QUOTES', 30);
define('FILTER_CALLBACK', 31);
define('FILTER_FLAG_ALLOW_OCTAL', 32);
define('FILTER_FLAG_ALLOW_HEX', 33);
define('FILTER_FLAG_STRIP_LOW', 34);
define('FILTER_FLAG_STRIP_HIGH', 35);
define('FILTER_FLAG_ENCODE_LOW', 36);
define('FILTER_FLAG_ENCODE_HIGH', 37);
define('FILTER_FLAG_ENCODE_AMP', 38);
define('FILTER_FLAG_NO_ENCODE_QUOTES', 39);
define('FILTER_FLAG_EMPTY_STRING_NULL', 40);
define('FILTER_FLAG_ALLOW_FRACTION', 41);
define('FILTER_FLAG_ALLOW_THOUSAND', 42);
define('FILTER_FLAG_ALLOW_SCIENTIFIC', 43);
define('FILTER_FLAG_SCHEME_REQUIRED', 44);
define('FILTER_FLAG_HOST_REQUIRED', 45);
define('FILTER_FLAG_PATH_REQUIRED', 46);
define('FILTER_FLAG_QUERY_REQUIRED', 47);
define('FILTER_FLAG_IPV4', 48);
define('FILTER_FLAG_IPV6', 49);
define('FILTER_FLAG_NO_RES_RANGE', 50);
define('FILTER_FLAG_NO_PRIV_RANGE', 51);
define('FILTER_FLAG_STRIP_BACKTICK', 52);

/*. bool .*/ function filter_has_var(/*. int .*/ $type, /*. string .*/ $variable_name){}
/*. int .*/ function filter_id(/*. string .*/ $filtername){}
/*. mixed .*/ function filter_input_array(/*. int .*/ $type /*. , args .*/){}
/*. mixed .*/ function filter_input(/*. int .*/ $type, /*. string .*/ $variable_name /*. , args .*/){}
/*. array[int]string .*/ function filter_list(){}
/*. mixed .*/ function filter_var_array(/*. array[string]mixed .*/ $data /*. , args .*/){}
/*. mixed .*/ function filter_var(/*. mixed .*/ $variable /*. , args .*/){}
