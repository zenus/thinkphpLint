<?php
/**
File Information.

See: {@link http://www.php.net/manual/en/book.fileinfo.php}
@package fileinfo
*/

/*. require_module 'standard'; .*/

# Dummy values:
define("FILEINFO_NONE", 1);
define("FILEINFO_SYMLINK", 1);
define("FILEINFO_MIME_TYPE", 1);
define("FILEINFO_MIME_ENCODING", 1);
define("FILEINFO_MIME", 1);
define("FILEINFO_COMPRESS", 1);
define("FILEINFO_DEVICES", 1);
define("FILEINFO_CONTINUE", 1);
define("FILEINFO_PRESERVE_ATIME", 1);
define("FILEINFO_RAW", 1);


class finfo {
	/*. void .*/ function __construct($options = FILEINFO_NONE, /*. string .*/ $magic_file = NULL)
	/*. triggers E_WARNING .*/{}
	/*. string .*/ function buffer(/*. string .*/ $string_ = NULL, $options = FILEINFO_NONE, /*. resource .*/ $context = NULL)
	/*. triggers E_WARNING .*/{}
	/*. string .*/ function file(/*. string .*/ $file_name = NULL, $options = FILEINFO_NONE, /*. resource .*/ $context = NULL)/*. triggers E_WARNING .*/{}
	/*. bool .*/ function set_flags(/*. int .*/ $options)/*. triggers E_WARNING .*/{}
}

/*. string .*/ function finfo_buffer(/*. resource .*/ $finfo, /*. string .*/ $string_ = NULL, $options = FILEINFO_NONE, /*. resource .*/ $context = NULL)/*. triggers E_WARNING .*/{}
/*. bool .*/ function finfo_close (/*. resource .*/ $finfo)/*. triggers E_WARNING .*/{}
/*. string .*/ function finfo_file (/*. resource .*/ $finfo , /*. string .*/ $file_name = NULL, $options = FILEINFO_NONE, /*. resource .*/ $context = NULL)/*. triggers E_WARNING .*/{}
/*. resource .*/ function finfo_open($options = FILEINFO_NONE, /*. string .*/ $magic_file = NULL)/*. triggers E_WARNING .*/{}
/*. bool .*/ function finfo_set_flags(/*. resource .*/ $finfo, /*. int .*/ $options)/*. triggers E_WARNING .*/{}

/**
 * @deprecated The PECL extension Fileinfo provides the same functionality
 * (and more) in a much cleaner way.
 */
/*. string.*/ function mime_content_type(/*. args .*/)
/*. triggers E_WARNING .*/{}
