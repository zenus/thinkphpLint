<?php
/** JSON Functions.

This extension implements the JavaScript Object Notation (JSON)
data-interchange format. The decoding is handled by a parser based on the
JSON_checker by Douglas Crockford.
<p>

See: {@link http://www.php.net/manual/en/ref.json.php}
@package json
*/

define("JSON_ERROR_NONE", 0);
define("JSON_ERROR_DEPTH", 1);
define("JSON_ERROR_STATE_MISMATCH", 2);
define("JSON_ERROR_CTRL_CHAR", 3);
define("JSON_ERROR_SYNTAX", 4);
define("JSON_ERROR_UTF8", 5);
define("JSON_ERROR_RECURSION", 6);
define("JSON_ERROR_INF_OR_NAN", 7);
define("JSON_ERROR_UNSUPPORTED_TYPE", 8);
define("JSON_FORCE_OBJECT", 16);
define("JSON_HEX_TAG", 1);
define("JSON_HEX_AMP", 2);
define("JSON_HEX_APOS", 4);
define("JSON_HEX_QUOT", 8);
define("JSON_NUMERIC_CHECK", 32);
define("JSON_UNESCAPED_SLASHES", 64);
define("JSON_PRETTY_PRINT", 128);
define("JSON_UNESCAPED_UNICODE", 256);
define("JSON_PARTIAL_OUTPUT_ON_ERROR", 512);
define("JSON_OBJECT_AS_ARRAY", 1);
define("JSON_BIGINT_AS_STRING", 2);

/*. mixed .*/ function json_decode(/*. string .*/ $json, $assoc = false, $depth = 512, $options = 0){}
/*. string .*/ function json_encode(/*. mixed .*/ $value, $options = 0, $depth = 512){}
/*. int .*/ function json_last_error(){}
/*. string .*/ function json_last_error_msg(){}

interface JsonSerializable {
	public /*. mixed .*/ function jsonSerialize();
}