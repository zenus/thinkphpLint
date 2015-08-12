<?php
/** iconv Functions.

See: {@link http://www.php.net/manual/en/ref.iconv.php}
@package iconv
*/

/*. require_module 'standard'; .*/

# FIXME: all dummy values
define('ICONV_IMPL', '?');
define('ICONV_VERSION', '?');
define('ICONV_MIME_DECODE_STRICT', 1);
define('ICONV_MIME_DECODE_CONTINUE_ON_ERROR', 1);

/*. int .*/ function iconv_strlen(/*. string .*/ $str /*., args .*/){}
/*. string .*/ function iconv_substr(/*. string .*/ $str, /*. int .*/ $offset /*., args .*/){}
/*. int .*/ function iconv_strpos(/*. string .*/ $haystack, /*. string .*/ $needle, /*. int .*/ $offset /*., args .*/){}
/*. int .*/ function iconv_strrpos(/*. string .*/ $haystack, /*. string .*/ $needle /*., args .*/){}
/*. string .*/ function iconv_mime_encode(/*. string .*/ $field_name, /*. string .*/ $field_value /*., args .*/){}
/*. string .*/ function iconv_mime_decode(/*. string .*/ $encoded_string /*., args .*/){}
/*. array .*/ function iconv_mime_decode_headers(/*. string .*/ $headers /*., args .*/){}
/*. string .*/ function iconv(/*. string .*/ $in_charset, /*. string .*/ $out_charset, /*. string .*/ $str)/*. triggers E_NOTICE .*/{}
/*. string .*/ function ob_iconv_handler(/*. string .*/ $contents, /*. int .*/ $status){}
/*. bool .*/ function iconv_set_encoding(/*. string .*/ $type, /*. string .*/ $charset){}
/*. mixed .*/ function iconv_get_encoding( /*. args .*/){}
