<?php
/** Zlib Compression Functions.

See: {@link http://www.php.net/manual/en/function.zlib-encode.php}
@package zlib
*/

/*. require_module 'standard'; .*/

define('FORCE_GZIP', 31);
define('FORCE_DEFLATE', 15);
define('ZLIB_ENCODING_RAW', -15);
define('ZLIB_ENCODING_GZIP', 31);
define('ZLIB_ENCODING_DEFLATE', 15);


/*. bool  .*/ function gzclose(/*.resource.*/ $zp)
	/*. triggers E_WARNING .*/ {}
	
/*. string.*/ function gzcompress(/*. string .*/ $data, $level = -1, $encoding = ZLIB_ENCODING_DEFLATE)
	/*. triggers E_WARNING .*/ {}
	
/*. string.*/ function gzdeflate(/*. string .*/ $data, $level = -1, $encoding = ZLIB_ENCODING_RAW)
	/*. triggers E_WARNING .*/ {}
	
/*. string .*/ function gzdecode(/*. string .*/ $data, $length=-1)
	/*. triggers E_WARNING .*/ {}
	
/*. string.*/ function gzencode(/*. string .*/ $data, $level = -1, $encoding_mode = FORCE_GZIP)
	/*. triggers E_WARNING .*/ {}
	
/*. bool  .*/ function gzeof(/*. resource .*/ $f)
	/*. triggers E_WARNING .*/ {}

/*. array[int]string .*/ function gzfile(/*. string .*/ $filename, $use_include_path = 0)
	/*. triggers E_WARNING .*/ {}
	
/*. mixed.*/ function gzgetc(/*. resource .*/ $h)
	/*. triggers E_WARNING .*/ {}

/*. string.*/ function gzgets(/*. resource .*/ $f, /*. int .*/ $length)
	/*. triggers E_WARNING .*/ {}

/*. string .*/ function gzgetss(/*. resource .*/ $zp , /*. int .*/ $length, /*. string .*/ $allowable_tags = NULL)
	/*. triggers E_WARNING .*/ {}

/*. string.*/ function gzinflate(/*. string .*/ $data, $length = 0)
	/*. triggers E_WARNING .*/ {}

/*. resource .*/ function gzopen(/*. string .*/ $filename, /*. string .*/ $mode, $use_include_path = 0)
	/*. triggers E_WARNING .*/ {}

/*. int .*/ function gzpassthru(/*. resource .*/ $zp)
	/*. triggers E_WARNING .*/ {}

/*. int .*/ function gzputs(/*. resource .*/ $zp, /*. string .*/ $string_, $length=-1)
	/*. triggers E_WARNING .*/ {}
	
/*. string .*/ function gzread(/*. resource .*/ $zp, /*. int .*/ $length)
	/*. triggers E_WARNING .*/ {}

/*. bool .*/ function gzrewind(/*. resource .*/ $zp )
	/*. triggers E_WARNING .*/ {}

/*. int .*/ function gzseek(/*. resource .*/ $zp, /*. int .*/ $offset, $whence = SEEK_SET)
	/*. triggers E_WARNING .*/ {}

/*. bool .*/ function gztell(/*. resource .*/ $zp)
	/*. triggers E_WARNING .*/ {}

/*. string.*/ function gzuncompress(/*. string .*/ $data, $length = 0)
	/*. triggers E_WARNING .*/ {}

/*. int .*/ function gzwrite(/*. resource .*/ $zp, /*. string .*/ $string_, $length=-1)
	/*. triggers E_WARNING .*/ {}

/*. int   .*/ function readgzfile(/*. string .*/ $filename, $use_include_path = 0)
	/*. triggers E_WARNING .*/ {}
	
/*. string .*/ function zlib_decode(/*. string .*/ $data, $max_decoded_len=-1)
	/*. triggers E_WARNING .*/ {}

/*. string .*/ function zlib_encode(/*. string .*/ $data, /*. string .*/ $encoding, $level = -1)
	/*. triggers E_WARNING .*/ {}
	
/*. mixed.*/ function zlib_get_coding_type(){}
