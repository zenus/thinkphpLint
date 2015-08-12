<?php
/** Regular Expression Functions (Perl-Compatible).

This module is part of the PHP core since PHP 5.3.0.
See: {@link http://www.php.net/manual/en/ref.pcre.php}
@package pcre
*/


define('PREG_PATTERN_ORDER', 1);
define('PREG_SET_ORDER', 2);
define('PREG_OFFSET_CAPTURE', 256);
define('PREG_SPLIT_NO_EMPTY', 1);
define('PREG_SPLIT_DELIM_CAPTURE', 2);
define('PREG_SPLIT_OFFSET_CAPTURE', 4);
define('PREG_GREP_INVERT', 1);
define('PREG_NO_ERROR', 1);
define('PREG_INTERNAL_ERROR', 1);
define('PREG_BACKTRACK_LIMIT_ERROR', 1);
define('PREG_RECURSION_LIMIT_ERROR', 1);
define('PREG_BAD_UTF8_ERROR', 1);
define('PREG_BAD_UTF8_OFFSET_ERROR', 1);
define('PCRE_VERSION', 1);

/*. if_php_ver_4 .*/

/*. int   .*/ function preg_match(
	/*. string .*/ $pattern,
	/*. string .*/ $subject
	/*., args .*/
){}

/**
	BUG: if the PREG_OFFSET_CAPTURE flag is used then the resulting
	structure of the $matches array is array[int][int][int]mixed where the
	elements mixed are strings and integers. PHPLint cannot support such a
	variability of types, so this prototype only accounts for the most
	common case in which the PREG_OFFSET_CAPTURE isn't used.
	<p>
	Remember that the possible flags combinations are:
	<p>
	PREG_PATTERN_ORDER (the default if no flags at all)<br>
	PREG_PATTERN_ORDER|PREG_OFFSET_CAPTURE<br>
	PREG_SET_ORDER<br>
	PREG_SET_ORDER|PREG_OFFSET_CAPTURE
*/
/*. int   .*/ function preg_match_all(
	/*. string .*/ $pattern,
	/*. string .*/ $subject /*. , args .*/){}

/*. else .*/

/*. int   .*/ function preg_match(
	/*. string .*/ $pattern,
	/*. string .*/ $subject,
	/*. return array[int]string .*/ & $matches = NULL,
	$offset = 0
){}

/**
	BUG: if the PREG_OFFSET_CAPTURE flag is used then the resulting
	structure of the $matches array is array[int][int][int]mixed where the
	elements mixed are strings and integers. PHPLint cannot support such a
	variability of types, so this prototype only accounts for the most
	common case in which the PREG_OFFSET_CAPTURE isn't used.
	<p>
	Remember that the possible flags combinations are:
	<p>
	PREG_PATTERN_ORDER (the default if no flags at all)<br>
	PREG_PATTERN_ORDER|PREG_OFFSET_CAPTURE<br>
	PREG_SET_ORDER<br>
	PREG_SET_ORDER|PREG_OFFSET_CAPTURE
*/
/*. int   .*/ function preg_match_all(
	/*. string .*/ $pattern,
	/*. string .*/ $subject,
	/*. return array[int][int]string .*/ & $matches = NULL,
	$flags = PREG_PATTERN_ORDER,
	$offset = 0){}

/*. end_if_php_ver .*/

/*. string.*/ function preg_replace(/*. mixed .*/ $regex, /*. mixed .*/ $replace, /*. mixed .*/ $subject /*., args .*/){}
/*. string.*/ function preg_replace_callback(/*. mixed .*/ $regex, /*. mixed .*/ $string_, /*. mixed .*/ $subject /*., args .*/){}
/*. array .*/ function preg_split(/*. string .*/ $pattern, /*. string .*/ $subject /*., args .*/){}
/*. string.*/ function preg_quote(/*. string .*/ $str, /*. string .*/ $delim_char){}
/*. array .*/ function preg_grep(/*. string .*/ $regex, /*. array .*/ $input){}
/*. int .*/ function preg_last_error(){}
