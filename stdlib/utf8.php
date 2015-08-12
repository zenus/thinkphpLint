<?php

require_once __DIR__ . "/all.php";

use it\icosaedro\containers\Printable;
use it\icosaedro\containers\UPrintable;
use it\icosaedro\utils\Strings;
use it\icosaedro\utils\UString;
use it\icosaedro\utils\UTF8;

/**
 * Provides the <code>u()</code> function that makes simple to write Unicode
 * aware sources written using the UTF-8 encoding.
 * Unicode strings are implemented through the {@link it\icosaedro\utils\UString} class.
 *
 *
 * <b>Writing and adapting existing source code.</b>
 * Basically, the <code>u()</code> function is intended to wrap literal
 * strings on source files that are UTF-8 encoded:
 *
 * <pre>
 * 	$hello = u("write hello in your own language here");
 * 	echo $hello-&gt;toUTF8();
 * </pre>
 *
 * In this way the <code>$hello</code> variable becomes an object
 * of the {@link it\icosaedro\utils\UString} class and all the methods of that class
 * can be applied to the variable: <code>$hello-&gt;length()</code>,
 * <code>$hello-&gt;equals($s)</code>, <code>$hello-&gt;startsWith($s)</code>,
 * <code>$hello-&gt;append($world)</code> and so on. Literal strings that include
 * variables must be split in several arguments of the <code>u()</code> function,
 * so that for example:
 *
 * <pre>
 * 	/&#42;. int .&#42;/ function count_records(/&#42;. string .&#42;/ $table){...}
 *
 * 	$t = "users";
 * 	$r = count_records("products");
 * 	$summary = "Found $r records in table $t.";
 * </pre>
 *
 * becomes:
 *
 * <pre>
 * 	/&#42;. int .&#42;/ function count_records(/&#42;. UString .&#42;/ $table){...}
 *
 * 	$t = u("users");
 * 	$r = count_records( u("products") );
 * 	$summary = u("Found ", $r, " records in table ", $t, ".");
 * </pre>
 *
 * The recipe to write and adapt an existing PHP source into an Unicode-aware
 * one is to ensure your sources are all either ASCII encoded or UTF-8
 * encoded, then all the existing literal strings must be wrapped by the
 * <code>u()</code> function, and dynamically generated string should be
 * crated with one of the UString factory methods. Any string manipulation
 * must then be performed through the methods provided by the {@link it\icosaedro\utils\UString}
 * class, including string comparison, string replacement, substring
 * extraction, an so on.
 *
 * Things become even simpler if all your functions and classes stick on
 * using UString objects rather than bare strings of bytes: in this way the
 * source become shorter and simpler, and the required encoding conversions
 * are reduced to a minimum without affecting performances.
 *
 * Strings returned from existing libraries can be converted to Unicode
 * calling the appropriate factory method corresponding to the expected
 * encoding of the binary string:
 *
 * <pre>
 * 	# Retrieving data from UTF-8 encoded HTML form:
 * 	$name = UString::fromUTF8( (string) $_POST["name"] );
 * 	$name = $name-&gt;trim()-&gt;toLowerCase();
 * 	if( $name-&gt;length() &lt; 3 )
 * 		die("Your name is too short!");
 * 	else if( $name-&gt;equals( u("guest") ) )
 * 		die("Sorry, this name is reserved for internal use.");
 *	
 *	# Converting text file from ISO-8859-1 to UCS2 little-endian:
 *	$in_file = fopen("InFile.txt", "r");
 *	$out_file = fopen("OutFile.txt", "w");
 *	while( ($line = fgets($in_file)) !== FALSE )
 * 		fwrite( $out_file, UString::fromISO88591($line)-&gt;toUCS2LE() );
 * 	fclose($out_file);
 * 	fclose($in_file);
 * </pre>
 *
 * Once all the strings are UString objects, then all the functions of the
 * PHP library that manipulate strings must be replaced by their UString
 * corresponding methods, always considering that now we are dealing with
 * Unicode characters and not bytes:
 *
 * <pre>
 * 	trim($s)  ==&gt;  $s-&gt;trim()
 * 	strlen($s)  ==&gt;  $s-&gt;length()
 * 	substr($s, 2, 5)  ==&gt;  $s-&gt;substring(2, 2 + 5)
 * 	explode(" ", $s)  ==&gt;  $s-&gt;explode( u(" ") )
 * 	implode(", ", $a)  ==&gt;  implode($a, u(", ") )
 * 	asc(234) ==&gt;  UString::chr(234)
 * 	if( $s1 === $s2 )  ==&gt;  if( $s1-&gt;equals($s2) )
 * 	if( strcmp($s1, $s2) &lt; 0 )  ==&gt;  if( $s1-&gt;compareTo($s2) &lt; 1 )
 * 	$s1 . $s2  ==&gt;  $s1-&gt;append($s2)
 * 	if( $s[0] === "/" )  ==&gt;  if( $s-&gt;startsWith( u("/") ) )
 * 	if( $s[$i] === "," )  ==&gt;  if( $s-&gt;charAt($i)-&gt;equals( u(",") ) )
 * 	strpos($s, $sub)  ==&gt;  $s-&gt;indexOf($sub)
 * 	str_replace($target, $replace, $s) ==&gt;  $s-&gt;replace($target, $replace)
 * 	strtoupper($s)  ==&gt;  $s-&gt;toUpperCase()
 * </pre>
 *
 * See also the {@link ./it/icosaedro/regex/UPattern.html UPattern} class
 * that provides Unicode-aware regular expressions.
 *
 *
 * <b>Shortcomings and limitations.</b> Since all this Unicode infrastructure
 * is implemented through regular PHP functions and classes, no particular
 * assistance is provided by the language, so there are some shortcomings
 * and limitations.
 *
 * Class constants cannot be objects, so constant strings must be declared as
 * bare binary strings and conversions to UString must be made every time
 * the constant is required:
 *
 * <pre>
 * 	class MyClass {
 * 		const X = "something";
 *
 * 		function getX() { return u(X); }
 * 	}
 * </pre>
 *
 * The caching mechanism implemented by the <code>u()</code> function alleviates
 * the performance cost of the conversion, and actually only one UString object
 * is created once for all and returned every time the <code>u(X)</code> is
 * evaluated again.
 *
 * Another limitation is that the default value for a UString parameter
 * of a function or method cannot be a literal UString, so you may either
 * stick on the NULL value, or renounce to declare a default value at all:
 *
 * <pre>
 * 	function myFunc(/&#42;. UString .&#42;/ $separator = NULL){...}
 * </pre>
 *
 * Moreover, the argument of the <code>switch()</code> statement cannot be
 * UString because objects are not allowed, only int and string are. So,
 * you may either convert the argument of the <code>switch()</code>
 * statement to a string:
 *
 * <pre>
 * 	switch( $s-&gt;toUTF8() ){
 * 		case "xxx": ...;
 * 		case "yyy": ...;
 * 		...
 * 	}
 * </pre>
 *
 * or use a chain of <code>if()</code> statements:
 *
 * <pre>
 * 	if( $s-&gt;equals( u("xxx") ) ){
 * 		...
 * 	} else if( $s-&gt;equals( u("yyy") ) ){
 * 		...
 * </pre>
 *
 * @package utf8
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/16 08:20:07 $
 */

#setlocale ( LC_CTYPE, 'C' );


/**
 * Converts \uHHHH to Unicode characters, UTF-8 encoded.  The codepoint is
 * a hexadecimal number with exactly 4 hexadecimal digits.  Example:
 * <pre>resolveUnicodeHex("\\u0000-\\uffff") ==&gt; "\x00-\xef\xbf\xbf"</pre>
 * Note that <code>\u</code> is not a valid escape sequence according to
 * PHPLint, so the back-slash must be always escaped.  If the syntax is
 * invalid, leaves it verbatim, no error raised.
 * @param string $s Subject string.
 * @return string The subject string with \uHHHH resolved as UTF-8 encoded
 * sequences.  Invalid sequences are removed.  If the subject string is NULL,
 * returns NULL.
 */
function resolveUnicodeHex($s){
	if( $s === NULL )
		return NULL;
	
	# Quick test:
	if( strpos($s, "\\u") === FALSE )
		return $s;
	
	$res = "";
	$i = 0;
	$j = 0;
	do {
		$j = Strings::indexOf($s, "\\u", $j);
		if( $j < 0 ){
			$res .= Strings::substring($s, $i, strlen($s));
			return $res;
		}

		# If the no. of \ that precede \u is odd, we may ignore this \u:
		$k = $j;
		while( $k > 0 and $s[$k-1] === "\\" )
			$k--;
		if( ($j - $k) % 2 == 1 ){
			$j = $j + 2;
			continue;
		}

		if( $j + 6 > strlen($s) ){
			# ERR: Unicode character \\uHHHH requires 4 digits.
			$j = $j + 2;
			continue;
		}

		# Parse 4-digits hex number:
		$c = 0;
		for($k = $j + 2; $k < $j + 6; $k++){
			$d = ord($s[$k]);
			if( ord("0") <= $d and $d <= ord("9") )
				$c = 16*$c + $d - ord("0");
			else if( ord("a") <= $d and $d <= ord("f") )
				$c = 16*$c + $d - ord("a") + 10;
			else if( ord("A") <= $d and $d <= ord("F") )
				$c = 16*$c + $d - ord("A") + 10;
			else {
				# ERR: expected hexadecimal digit in Unicode \\uHHHH but found $d.
				$c = -1;
				break;
			}
		}
		if( $c < 0 ){
			$j = $j + 2;
			continue;
		}

		$res .= Strings::substring($s, $i, $j) . UTF8::chr($c);
		$j = $j + 6;
		$i = $j;

	} while(TRUE);
}


/**
 * Returns the the value as Unicode string. Basically this function provides
 * a caching mechanism for all the literal strings UTF-8 encoded that are
 * present in a source program:
 *
 * <pre>
 * 	$s = u("some string, UTF-8 encoded");
 * </pre>
 *
 * This statement creates an UString object once for all, then the same statement can
 * be executed several times with the $s variable assigned with the same object.
 *
 * On regular strings, the <code>\uHHHH</code> special escape sequence allows to
 * enter any codepoint given its hexadecimal code. The hexadecimal code must be
 * exactly 4 hexadecimal digits long. Since this special escape sequence is specific
 * of this function, and is not recognized by PHP, the leading back-slash must be
 * doubled like in this example (<code>\u20ac</code> is the EUR symbol):
 *
 * <pre>
 * 	$total = u("Total charge: \\u20ac + V.A.T. is ");
 * </pre>
 *
 * If two or more arguments are given, each argument is cached separately and the
 * concatenation of all the strings is returned.
 *
 * Besides the literal strings, other types of values are accepted as well:
 *
 * <ul>
 * <li><b>null</b> yielding the "NULL" string</li>
 * <li><b>boolean</b> yielding either "FALSE" or "TRUE"</li>
 * <li><b>int</b> yielding the usual 10-base representation possibly with a
 * leading minus sign</li>
 * <li><b>float</b> yielding the usual 10-base representation
 * possibly with scientific notation</li>
 * <li><b>string</b> ASCII encoded or UTF-8 encoded</li>
 * <li><b>{@link it\icosaedro\utils\UString}</b> yields itself</li>
 * <li><b>object</b> implementing the
 * {@link it\icosaedro\containers\UPrintable} or the
 * {@link it\icosaedro\containers\Printable}
 * interfaces (tried in this order) or the class name is taken instead,
 * assumed to be UTF-8 encoded</li>
 * </ul>
 *
 * For any other type of value the string
 * returned by {@link gettype()} is returned instead.
 * 
 * For better performances, all the strings and all the integer numbers
 * in the range [-9,9] are cached. This implies that this function is not
 * simply a abbreviation for the creation of a UString object, but instead
 * this function is mainly intended to wrap any literal string of a PHP
 * source program. Since literal strings of a source program are in limited,
 * finite number, the caching mechanism avoids an object creation every time
 * the execution involves the same literal string.  For example, this
 * chunk of code:
 *
 * <pre>
 * 	for($i = 0; $i &lt; 10; $i++)
 * 		echo u("the index is: ", $i, "\n")-&gt;toUTF8();
 * </pre>
 *
 * generates at runtime only 12 UString objects: one once for all for the
 * <code>"the index is: "</code> literal string, another once for all for
 * the literal string <code>"\n"</code>, and 10 UString objects for the
 * numbers from 0 to 9.
 *
 * Vice-versa, this function MUST NOT be used to convert dynamically generated
 * strings, for example those that come from the <code>$_POST</code> array or
 * from any other PHP standard library; use the proper factory method
 * provided by the {@link it\icosaedro\utils\UString} class instead.
 *
 * @param mixed $m Any value. Strings are assumed to be either ASCII encoded
 * or UTF-8 encoded; invalid characters and invalid UTF-8 sequences are
 * silently replaced by <code>'?'</code>.
 * @return UString Textual representation of the value.
 */
function u($m /*. , args .*/)
{
	static $cache = /*. (UString[string]) .*/ array();

	if( $m === NULL )
		$m = "NULL";
	
	# Evaluates the result $u:
	if( is_string($m) ){
		$s = (string) $m;
		if( array_key_exists($s, $cache) ){
			$u = $cache[$s];
		} else {
			$u = UString::fromUTF8( resolveUnicodeHex($s) );
			$cache[$s] = $u;
		}
	
	} else if( is_bool($m) ){
		$b = (bool) $m;
		$u = $b? u("TRUE") : u("FALSE");

	} else if( is_int($m) ){
		$i = (int) $m;
		if( -9 <= $i and $i <= 9 ){
			$u = u("$i");
		} else {
			$u = UString::fromASCII("$i");
		}
	
	} else if( is_float($m) ){
		$f = (float) $m;
		$u = UString::fromASCII("$f");
	
	} else if( $m instanceof UString ){
		$u = cast("it\\icosaedro\\utils\\UString", $m);
	
	} else if( is_object($m) ){
		if( $m instanceof UPrintable ){
			$up = cast("it\\icosaedro\\containers\\UPrintable", $m);
			$u = $up->toUString();

		} if( $m instanceof Printable ){
			$p = cast("it\\icosaedro\\containers\\Printable", $m);
			$u = UString::fromASCII( $p->__toString() );

		} else {
			$o = cast("object", $m);
			$u = u(get_class($o));

		}
	
	} else {
		$u = u( gettype($m) );
	
	}

	# Concatenates optional arguments:
	for($i = 1; $i < func_num_args(); $i++)
		$u = $u->append( u( func_get_arg($i) ) );

	return $u;
}


/**
 * Displays the arguments to standard output with UTF-8 encoding.
 * For example, the statement
 *
 * <pre>
 * 	uecho($a, $b, $c);
 * </pre>
 *
 * is logically equivalent to the longer form
 *
 * <pre>
 * 	echo u($a, $b, $c)-&gt;toUTF8();
 * </pre>
 *
 * but it is also more efficient because every single argument is sent to
 * standard output immediately without the need to really concatenate them.
 * All the arguments have the same meaning of those of the u() function.
 * @param mixed $m Value to display.
 * @return void
 */
function uecho($m /*. , args .*/)
{
	echo u($m)->toUTF8();

	for($i = 1; $i < func_num_args(); $i++)
		echo u( func_get_arg($i) )->toUTF8();
}
