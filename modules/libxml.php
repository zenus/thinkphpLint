<?php
/** libxml Functions.

See: {@link http://www.php.net/manual/en/ref.libxml.php}
@package libxml
*/

/*. if_php_ver_4 .*/
	This_module_is_not_available_under_PHP_4.
/*. end_if_php_ver .*/

define("LIBXML_COMPACT", 65536);
define("LIBXML_DOTTED_VERSION", "?");
define("LIBXML_DTDATTR", 8);
define("LIBXML_DTDLOAD", 4);
define("LIBXML_DTDVALID", 16);
define("LIBXML_ERR_ERROR", 2);
define("LIBXML_ERR_FATAL", 3);
define("LIBXML_ERR_NONE", 0);
define("LIBXML_ERR_WARNING", 1);
define("LIBXML_LOADED_VERSION", 1);
define("LIBXML_NOBLANKS", 256);
define("LIBXML_NOCDATA", 16384);
define("LIBXML_NOEMPTYTAG", 4);
define("LIBXML_NOENT", 2);
define("LIBXML_NOERROR", 32);
define("LIBXML_NONET", 2048);
define("LIBXML_NOWARNING", 64);
define("LIBXML_NOXMLDECL", 2);
define("LIBXML_NSCLEAN", 8192);
define("LIBXML_VERSION", 20632);
define("LIBXML_XINCLUDE", 1024);


class LibXMLError
{
	public /*. int .*/ $code = 0;  # dummy initial value
	public /*. int .*/ $column = 0;  # dummy initial value
	public /*. string .*/ $file;
	public /*. int .*/ $level = 0;  # dummy initial value
	public /*. int .*/ $line = 0;  # dummy initial value
	public /*. string .*/ $message;
}

/*. void .*/ function libxml_set_streams_context(/*. resource .*/ $streams_context){}
/*. void .*/ function libxml_use_internal_errors(/*. args .*/){}
/*. LibXMLError .*/ function libxml_get_last_error(){}
/*. array[int]LibXMLError .*/ function libxml_get_errors(){}
/*. void .*/ function libxml_clear_errors() {}
/*. mixed .*/ function libxml_disable_entity_loader(/*. bool .*/ $disable = TRUE){}
