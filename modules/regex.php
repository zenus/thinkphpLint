<?php
/** Regular Expression Functions (POSIX Extended).


See: {@link http://www.php.net/manual/en/ref.regex.php}

@deprecated
	Not binary safe: the NUL byte "\000" marks the end of the string, so
	that bytes following the NUL are ignored, with possible security issues.
	As of PHP 5.3.0 calling any function of this module will issue an
	E_DEPRECATED error.

@package regex
*/


/*. if_php_ver_4 .*/
	/*. int   .*/ function ereg(/*. string .*/ $p, /*. string .*/ $s /*., args .*/){}
/*. else .*/
	/*. int   .*/ function ereg(/*. string .*/ $p, /*. string .*/ $s , /*. return array[int]string .*/ & $a = NULL){}
/*. end_if_php_ver .*/
/*. string.*/ function ereg_replace(/*. string .*/ $pattern, /*. string .*/ $replacement, /*. string .*/ $str){}
/*. string.*/ function eregi_replace(/*. string .*/ $pattern, /*. string .*/ $replacement, /*. string .*/ $str){}
/*. int   .*/ function eregi(/*. string .*/ $pattern, /*. string .*/ $str /*., args .*/){}
/*. array[int]string .*/ function split(/*. string .*/ $pattern, /*. string .*/ $str /*., args .*/){}
/*. array[int]string .*/ function spliti(/*. string .*/ $pattern, /*. string .*/ $str /*., args .*/){}
/*. string.*/ function sql_regcase(/*. string .*/ $s){}
