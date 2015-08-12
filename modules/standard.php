<?php
/**
	Standard library.

	Also referred as the "core" library, it is a collection of the
	most used constants, functions and classes. The standard library
	is available on any PHP installation.

	<p>
	Some of the constant's values are actually dummy values intended
	only to give the right type to the constant, since the actual value
	does not care to PHPLint.

	<p>
	See: {@link http://www.php.net/manual/en/}
	@package standard
*/

define('ABDAY_1', 131072);
define('ABDAY_2', 131073);
define('ABDAY_3', 131074);
define('ABDAY_4', 131075);
define('ABDAY_5', 131076);
define('ABDAY_6', 131077);
define('ABDAY_7', 131078);
define('ABMON_1', 131086);
define('ABMON_10', 131095);
define('ABMON_11', 131096);
define('ABMON_12', 131097);
define('ABMON_2', 131087);
define('ABMON_3', 131088);
define('ABMON_4', 131089);
define('ABMON_5', 131090);
define('ABMON_6', 131091);
define('ABMON_7', 131092);
define('ABMON_8', 131093);
define('ABMON_9', 131094);
define('ALT_DIGITS', 131119);
define('AM_STR', 131110);
define('ASSERT_ACTIVE', 1);
define('ASSERT_BAIL', 3);
define('ASSERT_CALLBACK', 2);
define('ASSERT_QUIET_EVAL', 5);
define('ASSERT_WARNING', 4);
define('CASE_LOWER', 0);
define('CASE_UPPER', 1);
define('CHAR_MAX', 127);
define('CODESET', 14);
define('CONNECTION_ABORTED', 1);
define('CONNECTION_NORMAL', 0);
define('CONNECTION_TIMEOUT', 2);
define('COUNT_NORMAL', 0);
define('COUNT_RECURSIVE', 1);
define('CREDITS_ALL', -1);
define('CREDITS_DOCS', 16);
define('CREDITS_FULLPAGE', 32);
define('CREDITS_GENERAL', 2);
define('CREDITS_GROUP', 1);
define('CREDITS_MODULES', 8);
define('CREDITS_QA', 64);
define('CREDITS_SAPI', 4);
define('CRNCYSTR', 262159);
define('CRYPT_BLOWFISH', 0);
define('CRYPT_EXT_DES', 0);
define('CRYPT_MD5', 1);
define('CRYPT_SALT_LENGTH', 12);
define('CRYPT_STD_DES', 1);
define('CRYPT_SHA256', 5001 /* dummy */);
define('CRYPT_SHA512', 5002 /* dummy */);
define('C_EXPLICIT_ABSTRACT', 32);
define('C_FINAL', 64);
define('C_IMPLICIT_ABSTRACT', 16);
define('DAY_1', 131079);
define('DAY_2', 131080);
define('DAY_3', 131081);
define('DAY_4', 131082);
define('DAY_5', 131083);
define('DAY_6', 131084);
define('DAY_7', 131085);
define('DEFAULT_INCLUDE_PATH', '.:');
define('DIRECTORY_SEPARATOR', '/');
define('DNS_A', 1);
define('DNS_AAAA', 134217728);
define('DNS_ALL', 234936371);
define('DNS_ANY', 268435456);
define('DNS_CNAME', 16);
define('DNS_HINFO', 4096);
define('DNS_MX', 16384);
define('DNS_NAPTR', 67108864);
define('DNS_NS', 2);
define('DNS_PTR', 2048);
define('DNS_SOA', 32);
define('DNS_SRV', 33554432);
define('DNS_TXT', 32768);
define('D_FMT', 131113);
define('D_T_FMT', 131112);
define('ENT_COMPAT', 2);
define('ENT_NOQUOTES', 0);
define('ENT_QUOTES', 3);
define('ERA', 131116);
define('ERA_D_FMT', 131118);
define('ERA_D_T_FMT', 131120);
define('ERA_T_FMT', 131121);
define('EXTR_IF_EXISTS', 6);
define('EXTR_OVERWRITE', 0);
define('EXTR_PREFIX_ALL', 3);
define('EXTR_PREFIX_IF_EXISTS', 5);
define('EXTR_PREFIX_INVALID', 4);
define('EXTR_PREFIX_SAME', 2);
define('EXTR_REFS', 256);
define('EXTR_SKIP', 1);
define('E_ALL', 2047);
define('E_COMPILE_ERROR', 64);
define('E_COMPILE_WARNING', 128);
define('E_CORE_ERROR', 16);
define('E_CORE_WARNING', 32);
define('E_ERROR', 1);
define('E_NOTICE', 8);
define('E_PARSE', 4);
define('E_RECOVERABLE_ERROR', 4096);
define('E_STRICT', 2048);
define('E_USER_ERROR', 256);
define('E_USER_NOTICE', 1024);
define('E_USER_WARNING', 512);
define('E_WARNING', 2);
define('FILE_APPEND', 8);
define('FILE_IGNORE_NEW_LINES', 2);
define('FILE_NO_DEFAULT_CONTEXT', 16);
define('FILE_SKIP_EMPTY_LINES', 4);
define('FILE_USE_INCLUDE_PATH', 1);
define('FNM_CASEFOLD', 16);
define('FNM_NOESCAPE', 2);
define('FNM_PATHNAME', 1);
define('FNM_PERIOD', 4);
define('GLOB_BRACE', 1024);
define('GLOB_MARK', 2);
define('GLOB_NOCHECK', 16);
define('GLOB_NOESCAPE', 64);
define('GLOB_NOSORT', 4);
define('GLOB_ONLYDIR', 8192);
define('HTML_ENTITIES', 1);
define('HTML_SPECIALCHARS', 0);
define('INFO_ALL', -1);
define('INFO_CONFIGURATION', 4);
define('INFO_CREDITS', 2);
define('INFO_ENVIRONMENT', 16);
define('INFO_GENERAL', 1);
define('INFO_LICENSE', 64);
define('INFO_MODULES', 8);
define('INFO_VARIABLES', 32);
define('INI_ALL', 7);
define('INI_PERDIR', 2);
define('INI_SYSTEM', 4);
define('INI_USER', 1);
define('LC_ALL', 6);
define('LC_COLLATE', 3);
define('LC_CTYPE', 0);
define('LC_MESSAGES', 5);
define('LC_MONETARY', 4);
define('LC_NUMERIC', 1);
define('LC_TIME', 2);
define('LOCK_EX', 2);
define('LOCK_NB', 4);
define('LOCK_SH', 1);
define('LOCK_UN', 3);
define('LOG_ALERT', 1);
define('LOG_AUTH', 32);
define('LOG_AUTHPRIV', 80);
define('LOG_CONS', 2);
define('LOG_CRIT', 2);
define('LOG_CRON', 72);
define('LOG_DAEMON', 24);
define('LOG_DEBUG', 7);
define('LOG_EMERG', 0);
define('LOG_ERR', 3);
define('LOG_INFO', 6);
define('LOG_KERN', 0);
define('LOG_LOCAL0', 128);
define('LOG_LOCAL1', 136);
define('LOG_LOCAL2', 144);
define('LOG_LOCAL3', 152);
define('LOG_LOCAL4', 160);
define('LOG_LOCAL5', 168);
define('LOG_LOCAL6', 176);
define('LOG_LOCAL7', 184);
define('LOG_LPR', 48);
define('LOG_MAIL', 16);
define('LOG_NDELAY', 8);
define('LOG_NEWS', 56);
define('LOG_NOTICE', 5);
define('LOG_NOWAIT', 16);
define('LOG_ODELAY', 4);
define('LOG_PERROR', 32);
define('LOG_PID', 1);
define('LOG_SYSLOG', 40);
define('LOG_USER', 8);
define('LOG_UUCP', 64);
define('LOG_WARNING', 4);
define('MON_1', 131098);
define('MON_10', 131107);
define('MON_11', 131108);
define('MON_12', 131109);
define('MON_2', 131099);
define('MON_3', 131100);
define('MON_4', 131101);
define('MON_5', 131102);
define('MON_6', 131103);
define('MON_7', 131104);
define('MON_8', 131105);
define('MON_9', 131106);
define('M_1_PI', 0.31830988618379);
define('M_2_PI', 0.63661977236758);
define('M_2_SQRTPI', 1.1283791670955);
define('M_ABSTRACT', 2);
define('M_E', 2.718281828459);
define('M_FINAL', 4);
define('M_LN10', 2.302585092994);
define('M_LN2', 0.69314718055995);
define('M_LOG10E', 0.43429448190325);
define('M_LOG2E', 1.442695040889);
define('M_PI', 3.1415926535898);
define('M_PI_2', 1.5707963267949);
define('M_PI_4', 0.78539816339745);
define('M_PRIVATE', 1024);
define('M_PROTECTED', 512);
define('M_PUBLIC', 256);
define('M_SQRT1_2', 0.70710678118655);
define('M_SQRT2', 1.4142135623731);
define('M_STATIC', 1);
define('NOEXPR', 327681);
define('PATHINFO_BASENAME', 2);
define('PATHINFO_DIRNAME', 1);
define('PATHINFO_EXTENSION', 4);
define('PATH_SEPARATOR', ':');
define('PEAR_EXTENSION_DIR', '/usr/local/php-5.0.1/lib/php/extensions/no-debug-non-zts-20040412');
define('PEAR_INSTALL_DIR', '');
define('PHP_BINDIR', '/usr/local/php-5.0.1/bin');
define('PHP_CONFIG_FILE_PATH', '/usr/local/php-5.0.1/lib');
define('PHP_CONFIG_FILE_SCAN_DIR', '');
define('PHP_DATADIR', '/usr/local/php-5.0.1/share/php');
define('PHP_EOL', "\n");
define('PHP_EXTENSION_DIR', '/usr/local/php-5.0.1/lib/php/extensions/no-debug-non-zts-20040412');
define('PHP_LIBDIR', '/usr/local/php-5.0.1/lib/php');
define('PHP_LOCALSTATEDIR', '/usr/local/php-5.0.1/var');
define('PHP_OS', 'Linux');
define('PHP_OUTPUT_HANDLER_CONT', 2);
define('PHP_OUTPUT_HANDLER_END', 4);
define('PHP_OUTPUT_HANDLER_START', 1);
define('PHP_PREFIX', '/usr/local/php-5.0.1');
define('PHP_ROUND_HALF_UP', 1);
define('PHP_ROUND_HALF_DOWN', 2);
define('PHP_ROUND_HALF_EVEN', 3);
define('PHP_ROUND_HALF_ODD', 4);
define('PHP_SAPI', 'cgi');
define('PHP_SHLIB_SUFFIX', 'so');
define('PHP_SYSCONFDIR', '/usr/local/php-5.0.1/etc');
define('PHP_VERSION', '5.0.1');
define('PHP_INT_SIZE', 4);
define('PHP_INT_MAX', 2147483647);
define('PM_STR', 131111);
define('PSFS_ERR_FATAL', 0);
define('PSFS_FEED_ME', 1);
define('PSFS_FLAG_FLUSH_CLOSE', 2);
define('PSFS_FLAG_FLUSH_INC', 1);
define('PSFS_FLAG_NORMAL', 0);
define('PSFS_PASS_ON', 2);
define('P_PRIVATE', 1024);
define('P_PROTECTED', 512);
define('P_PUBLIC', 256);
define('P_STATIC', 1);
define('RADIXCHAR', 65536);
define('SEEK_CUR', 1);
define('SEEK_END', 2);
define('SEEK_SET', 0);
define('SORT_ASC', 4);
define('SORT_DESC', 3);
define('SORT_NUMERIC', 1);
define('SORT_REGULAR', 0);
define('SORT_STRING', 2);
define('STR_PAD_BOTH', 2);
define('STR_PAD_LEFT', 0);
define('STR_PAD_RIGHT', 1);
define('SUNFUNCS_RET_DOUBLE', 2);
define('SUNFUNCS_RET_STRING', 1);
define('SUNFUNCS_RET_TIMESTAMP', 0);
define('THOUSEP', 65537);
define('T_FMT', 131114);
define('T_FMT_AMPM', 131115);
define('UPLOAD_ERR_OK', 0);
define('UPLOAD_ERR_INI_SIZE', 1);
define('UPLOAD_ERR_FORM_SIZE', 2);
define('UPLOAD_ERR_PARTIAL', 3);
define('UPLOAD_ERR_NO_FILE', 4);
define('UPLOAD_ERR_NO_TMP_DIR', 6);
define('UPLOAD_ERR_CANT_WRITE', 7);
define('UPLOAD_ERR_EXTENSION', 8);
define('YESEXPR', 327680);
define('ZEND_THREAD_SAFE', false);

/*. if_php_ver_5 .*/
define('PHP_DEBUG', 1);
define('PHP_EXTRA_VERSION', "?");
define('PHP_MAJOR_VERSION', 1);
define('PHP_MAXPATHLEN', 1);
define('PHP_MINOR_VERSION', 1);
define('PHP_RELEASE_VERSION', 1);
define('PHP_VERSION_ID', 1);
define('PHP_ZTS', 1);
define('__COMPILER_HALT_OFFSET__', 1);
define('E_DEPRECATED', 8192);
define('E_USER_DEPRECATED', 16384);

define("PHP_WINDOWS_VERSION_MAJOR", 1);
define("PHP_WINDOWS_VERSION_MINOR", 1);
define("PHP_WINDOWS_VERSION_BUILD", 1);
define("PHP_WINDOWS_VERSION_PLATFORM", 1);
define("PHP_WINDOWS_VERSION_SP_MAJOR", 1);
define("PHP_WINDOWS_VERSION_SP_MINOR", 1);
define("PHP_WINDOWS_VERSION_SUITEMASK", 1);
define("PHP_WINDOWS_VERSION_PRODUCTTYPE", 1);
define("PHP_WINDOWS_NT_DOMAIN_CONTROLLER", 1);
define("PHP_WINDOWS_NT_SERVER", 1);
define("PHP_WINDOWS_NT_WORKSTATION", 1);
define("INI_SCANNER_NORMAL", 1);
define("INI_SCANNER_RAW", 1);
/*. end_if_php_ver .*/

/* FIXME: $argc,$argv are actually defined only in the CLI and CGI versions
and are set only if "register_argc_argv = on" in php.ini. */
$argc = 0;
$argv = /*. (array[int]string) .*/ array();

/*. mixed .*/ function constant(/*. string .*/ $name){}
/*. string.*/ function bin2hex(/*. string .*/ $str){}
/*. string.*/ function hex2bin(/*. string .*/ $str)/*. triggers E_WARNING .*/{}
/*. int   .*/ function sleep(/*. int .*/ $secs){}
/*. void  .*/ function usleep(/*. int .*/ $microsecs){}
/*. mixed .*/ function time_nanosleep(/*.int.*/ $secs, /*.int.*/ $nanosecs){}
/*. int   .*/ function time(){}
/*. int   .*/ function mktime(/*. args .*/){}
/*. int   .*/ function gmmktime(/*. args .*/){}
/*. string.*/ function strftime(/*.string .*/ $fmt /*., args .*/){}
/*. string.*/ function gmstrftime(/*. string .*/ $fmt /*., args .*/){}
/*. int   .*/ function strtotime(/*. string .*/ $time /*., args .*/){}
/*. string.*/ function date(/*. string .*/ $fmt /*., args .*/){}
/*. int   .*/ function idate(/*. string .*/ $fmt /*., args .*/){}
/*. string.*/ function gmdate(/*. string .*/ $fmt /*., args .*/){}
/*. array[string]mixed .*/ function getdate(/*. args .*/){}
/*. array[]int .*/ function localtime($timestamp=0, $is_associative=FALSE)
/*. triggers E_NOTICE, E_WARNING .*/{}
/*. bool  .*/ function checkdate(/*.int.*/ $month, /*.int.*/ $day, /*.int.*/ $year){}
/*. void  .*/ function flush(){}
/*. string.*/ function wordwrap(/*.string.*/ $s, $width = 75, $break_ = "\n", $cut = FALSE){}
/*. string.*/ function htmlspecialchars(/*. string .*/ $s, $quote_style = ENT_COMPAT, $charset = "ISO-8859-1", $double_encode = TRUE){}
/*. string.*/ function htmlspecialchars_decode(/*. string .*/ $s, $quote_style = ENT_COMPAT){}
/*. string.*/ function htmlentities(/*. string .*/ $s, $quote_style = ENT_COMPAT, $charset = "ISO-8859-1", $double_encode = TRUE){}
/*. string.*/ function html_entity_decode(/*. string .*/ $s, $quote_style = ENT_COMPAT, $charset = "ISO-8859-1"){}
/*. array[string]string .*/ function get_html_translation_table($table = HTML_SPECIALCHARS, $quote_style = ENT_COMPAT){}
/*. int .*/ function realpath_cache_size(){}
/*. array .*/ function realpath_cache_get(){}


/*. int   .*/ function crc32(/*. string .*/ $str){}



/*. bool  .*/ function phpinfo($what = INFO_ALL){}
/*. string.*/ function phpversion(/*. args .*/){}
/*. bool  .*/ function phpcredits($flag = CREDITS_ALL){}
/*. string.*/ function php_logo_guid(){}
/*. string.*/ function zend_logo_guid(){}
/*. string.*/ function php_sapi_name(){}
/*. string.*/ function php_uname($mode = "a"){}
/*. string.*/ function php_ini_loaded_file(){}
/*. string.*/ function php_ini_scanned_files(){}
/*. int   .*/ function strnatcmp(/*.string.*/ $s1, /*.string.*/ $s2){}
/*. int   .*/ function strnatcasecmp(/*.string.*/ $s1, /*.string.*/ $s2){}
/*. int   .*/ function strcasecmp(/*.string.*/ $s1, /*.string.*/ $s2){}
/*. int   .*/ function substr_count(/*.string.*/ $haystack, /*.string.*/ $needle, $offset = 0, $length = -1){}
/*. int   .*/ function strspn(/*. string .*/ $str1, /*. string .*/ $str2 /*., args .*/){}
/*. int   .*/ function strcspn(/*. string .*/ $str1, /*. string .*/ $str2 /*., args .*/){}
/*. string.*/ function strtok(/*. string .*/ $str, /*. string .*/ $token){}
/*. string.*/ function strtoupper(/*. string .*/ $s){}
/*. string.*/ function strtolower(/*. string .*/ $s){}
/*. int .*/ function strpos(/*.string.*/ $haystack, /*.mixed.*/ $needle /*., args .*/){}
/*. int .*/ function stripos(/*. string .*/ $haystack, /*. string .*/ $needle /*., args .*/){}
/*. int .*/ function strrpos(/*. string .*/ $haystack, /*. string .*/ $needle /*., args .*/){}
/*. int .*/ function strripos(/*. string .*/ $haystack, /*. string .*/ $needle /*., args .*/){}
/*. string.*/ function strrev(/*. string .*/ $s){}
/*. string .*/ function hebrev(/*. string .*/ $str, $max_chars_per_line = 0){}
/*. string .*/ function hebrevc(/*. string .*/ $str, $max_chars_per_line = 0){}
/*. string.*/ function nl2br(/*. string .*/ $s, $is_xhtml = TRUE){}
/*. string.*/ function basename(/*. string .*/ $path /*., args .*/){}
/*. string.*/ function dirname(/*. string .*/ $path){}
/*. array[string]string .*/ function pathinfo(/*. string .*/ $path /*., args .*/){}
/*. string.*/ function stripslashes(/*.string.*/ $s){}
/*. string.*/ function stripcslashes(/*. string .*/ $str){}
/*. string.*/ function strstr(/*.string.*/ $haystack, /*.mixed.*/ $needle /*., args .*/){}
/*. string.*/ function strchr(/*.string.*/ $haystack, /*.string.*/ $needle){}
/*. string.*/ function stristr(/*.string.*/ $haystack, /*.mixed.*/ $needle /*., args .*/){}
/*. string.*/ function strrchr(/*.string.*/ $haystack, /*.mixed.*/ $needle){}
/*. string.*/ function str_shuffle(/*. string .*/ $str){}
/*. mixed .*/ function str_word_count(/*. string .*/ $str /*., args .*/){}
/*. array[int]string .*/ function str_split(/*.string.*/ $s, $split_length = 1){}
/*. string.*/ function strpbrk(/*. string .*/ $haystack, /*.string.*/ $charlist){}
/*. int   .*/ function substr_compare(/*. string .*/ $main_str, /*. string .*/ $str, /*. int .*/ $offset /*., args .*/){}
/*. int   .*/ function strcoll(/*. string .*/ $str1, /*. string .*/ $str2){}
/** @deprecated This function is not available on Windows. */
/*. string.*/ function money_format(/*. string .*/ $format, /*. float .*/ $number_){}
/*. string.*/ function substr(/*.string.*/ $s, /*.int.*/ $start /*., args .*/){}
/*. mixed .*/ function substr_replace(/*. mixed .*/ $s, /*. string .*/ $replacement, /*. int .*/ $start /*., args .*/){}
/*. string.*/ function quotemeta(/*. string .*/ $str){}
/*. string.*/ function ucfirst(/*.string.*/ $str){}
/*. string.*/ function ucwords(/*.string.*/ $str){}
/*. string.*/ function strtr(/*.string.*/ $s, /*.mixed.*/ $x /*., args .*/){}
/*. string.*/ function addslashes(/*.string.*/ $str){}
/*. string.*/ function addcslashes(/*.string.*/ $str, /*.string.*/ $charlist){}
/*. string.*/ function rtrim(/*.string.*/ $s, $charlist = " \t\n\r\0\x0b"){}
/*. string.*/ function chop(/*.string.*/ $s, $charlist = " \t\n\r\0\x0b"){}
/*. mixed .*/ function str_replace(/*.mixed.*/ $search, /*.mixed.*/ $replace, /*.mixed.*/ $subject /*., args .*/){}
/*. mixed .*/ function str_ireplace(/*.mixed.*/ $search, /*.mixed.*/ $replace, /*.mixed.*/ $subject /*., args .*/){}
/*. string.*/ function str_repeat(/*.string.*/ $s, /*.int.*/ $n){}
/*. mixed .*/ function count_chars(/*. string .*/ $str, $mode = 0){}
/*. string.*/ function chunk_split(/*.string.*/ $body, $chunklen = 76, $end = "\r\n"){}
/*. string.*/ function trim(/*.string.*/ $str, $charlist = " \t\n\r\0\x0b"){}
/*. string.*/ function ltrim(/*. string .*/ $str, $charlist = " \t\n\r\0\x0b"){}
/*. string.*/ function strip_tags(/*. string .*/ $str /*., args .*/){}
/*. int   .*/ function similar_text(/*. string .*/ $first, /*. string .*/ $second /*., args .*/){}
/*. array[int]string .*/ function explode(/*.string.*/ $sep, /*.string.*/ $s /*., args .*/){}

/** ATTENTION!
	The actual PHP function also accepts one single array assuming glue to be the
	empty string. Rewrite as implode("", array) to comply with this declaration.
*/ 
/*. string.*/ function implode(/*.string.*/ $glue, /*. array[]string .*/ $pieces){}

/*. string.*/ function join(/*.string.*/ $glue, /*. array[]string .*/ $pieces){}
/*. string.*/ function setlocale(/*. int .*/ $category, /*. mixed .*/ $locale /*., args .*/){}
/*. array[string]mixed .*/ function localeconv(){}
/*. string.*/ function nl_langinfo(/*. int .*/ $item){}
/*. string.*/ function soundex(/*. string .*/ $str){}
/*. int   .*/ function levenshtein(/*. string .*/ $str1, /*. string .*/ $str2 /*., args .*/){}
/*. string.*/ function chr(/*.int.*/ $c){}
/*. int   .*/ function ord(/*.string.*/ $s){}
/*. void  .*/ function parse_str(/*. string .*/ $str /*., args .*/){}
/*. string.*/ function str_pad(/*. string .*/ $input, /*. int .*/ $pad_length, $pad_string = " ", $pad_type = STR_PAD_RIGHT){}
/*. string.*/ function sprintf(/*.string.*/ $fmt /*., args .*/){}
/*. string.*/ function printf(/*.string.*/ $fmt /*., args .*/){}
/*. int   .*/ function vprintf(/*. string .*/ $format, /*. array .*/ $args_){}
/*. string.*/ function vsprintf(/*. string .*/ $format, /*. array .*/ $args_){}
/*. int   .*/ function fprintf(/*.resource.*/ $f, /*.string.*/ $fmt /*., args .*/){}
/*. int   .*/ function vfprintf(/*. resource .*/ $handle, /*. string .*/ $format, /*. array .*/ $args_){}
/*. mixed .*/ function sscanf(/*.string.*/ $str, /*.string.*/ $fmt /*., args .*/){}
/*. mixed .*/ function fscanf(/*. resource .*/ $handle, /*. string .*/ $format /*., args .*/){}
/*. array[string]string .*/ function parse_url(/*. string .*/ $url, $component = -1)/*. triggers E_WARNING .*/{}
/*. string.*/ function urlencode(/*. string .*/ $s){}
/*. string.*/ function urldecode(/*. string .*/ $s){}
/*. string.*/ function rawurlencode(/*. string .*/ $str){}
/*. string.*/ function rawurldecode(/*. string .*/ $str){}
/*. string.*/ function http_build_query(/*. array .*/ $formdata /*., args .*/){}
/*. string.*/ function readlink(/*. string .*/ $path){}
/*. int   .*/ function linkinfo(/*. string .*/ $path){}
/*. bool  .*/ function symlink(/*. string .*/ $target, /*. string .*/ $link){}
/*. bool  .*/ function link(/*. string .*/ $target, /*. string .*/ $link){}
/*. bool  .*/ function unlink(/*. string .*/ $filename /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. string.*/ function exec(/*. string .*/ $command /*., args .*/){}
/*. string.*/ function system(/*. string .*/ $command /*., args .*/){}
/*. string.*/ function escapeshellcmd(/*. string .*/ $command){}
/*. string.*/ function escapeshellarg(/*. string .*/ $arg){}
/*. void  .*/ function passthru(/*. string .*/ $command /*., args .*/){}
/*. string.*/ function shell_exec(/*. string .*/ $cmd){}
/*. resource .*/ function proc_open(/*. string .*/ $cmd, /*. array[int][int]string .*/ $descriptorspec, /*. return array[]resource .*/ &$pipes /*., args .*/){}
/*. int   .*/ function proc_close(/*. resource .*/ $process){}
/*. int   .*/ function proc_terminate(/*. resource .*/ $process /*., args .*/){}
/*. array[string]mixed .*/ function proc_get_status(/*. resource .*/ $process){}
/*. bool  .*/ function proc_nice(/*. int .*/ $increment){}
/*. int   .*/ function rand(/*. args .*/){}
/*. void  .*/ function srand(/*. args .*/){}
/*. int   .*/ function getrandmax(){}
/*. int   .*/ function mt_rand(/*. args .*/){}
/*. void  .*/ function mt_srand(/*. args .*/){}
/*. int   .*/ function mt_getrandmax(){}
/*. int   .*/ function getservbyname(/*. int .*/ $service, /*. string .*/ $protocol){}
/*. string.*/ function getservbyport(/*. int .*/ $port, /*. string .*/ $protocol){}
/*. int   .*/ function getprotobyname(/*. string .*/ $name){}
/*. string.*/ function getprotobynumber(/*. int .*/ $number_){}
/*. int   .*/ function getmyuid(){}
/*. int   .*/ function getmygid(){}
/*. int   .*/ function getmypid(){}
/*. int   .*/ function getmyinode(){}
/*. int   .*/ function getlastmod(){}
/*. string.*/ function base64_decode(/*. string .*/ $s){}
/*. string.*/ function base64_encode(/*. string .*/ $s){}
/*. string.*/ function convert_uuencode(/*. string .*/ $data){}
/*. string.*/ function convert_uudecode(/*. string .*/ $data){}

/**
	Returns the absolute value of the argument.
	<b>Warning.</b>
	This function can be applied to int values as well when an int result is
	expected, but this is not the case when the int value is the maximum
	negative int, in fact the result is float:
	<pre>abs(-PHP_INT_MAX-1) ==&gt; float(2147483648)</pre>
	and applying the typecast required by PHPLint we end with an unexpected
	negative number:
	<pre>(int) abs(-PHP_INT_MAX-1) ==&gt; int(-2147483648)</pre>
	rather than the expected 0 as a result of the 2-complement. If you really
	need the common behaviour of a true abs() function applied to int, consider
	this expression instead that calculates the 2-complement:
	<pre>$i &gt;= 0? $i : (($i ^ PHP_INT_MAX) + 1) &amp; PHP_INT_MAX</pre>
	or even:
	<pre>$i == -PHP_INT_MAX - 1? 0 : (int) abs($i)</pre>
	If, instead, you are only interested to drop the sign, simply do this:
	<pre>$i ^ PHP_INT_MAX</pre>
	@param float $n
	@return float  The absolute value of the argument.
*/
function abs($n){}

/*. float .*/ function acos(/*. float .*/ $x){}
/*. float .*/ function acosh(/*. float .*/ $x){}
/*. float .*/ function asin(/*. float .*/ $x){}
/*. float .*/ function asinh(/*. float .*/ $x){}
/*. float .*/ function atan(/*. float .*/ $x){}
/*. float .*/ function atan2(/*. float .*/ $y, /*. float .*/ $x){}
/*. float .*/ function atanh(/*. float .*/ $x){}
/*. float .*/ function ceil(/*. float .*/ $x){}
/*. float .*/ function cos(/*. float .*/ $x){}
/*. float .*/ function cosh(/*. float .*/ $x){}
/*. float .*/ function expm1(/*. float .*/ $x){}
/*. float .*/ function floor(/*. float .*/ $x){}
/*. float .*/ function log1p(/*. float .*/ $x){}
/*. float .*/ function round(/*. float .*/ $x, $precision = 0, $mode = PHP_ROUND_HALF_UP){}
/*. float .*/ function sin(/*. float .*/ $x){}
/*. float .*/ function sinh(/*. float .*/ $x){}
/*. float .*/ function tan(/*. float .*/ $x){}
/*. float .*/ function tanh(/*. float .*/ $x){}
/*. float .*/ function pi(){}
/*. bool  .*/ function is_finite(/*. float .*/ $val){}
/*. bool  .*/ function is_nan(/*. float .*/ $val){}
/*. bool  .*/ function is_infinite(/*. float .*/ $val){}
/*. float .*/ function pow(/*. float .*/ $base, /*. float .*/ $esp){}
/*. float .*/ function exp(/*. float .*/ $x){}
/*. float .*/ function log(/*. float .*/ $x){}
/*. float .*/ function log10(/*. float .*/ $x){}
/*. float .*/ function sqrt(/*. float .*/ $x){}
/*. float .*/ function hypot(/*. float .*/ $x, /*. float .*/ $y){}
/*. float .*/ function deg2rad(/*. float .*/ $number_){}
/*. float .*/ function rad2deg(/*. float .*/ $number_){}
/*. float .*/ function bindec(/*. string .*/ $binary){}
/*. float .*/ function hexdec(/*. string .*/ $hex){}
/*. float .*/ function octdec(/*. string .*/ $oct){}
/*. string.*/ function decbin(/*. int .*/ $number_){}
/*. string.*/ function decoct(/*. int .*/ $number_){}
/*. string.*/ function dechex(/*. int .*/ $number_){}
/*. string.*/ function base_convert(/*. string .*/ $number_, /*. int .*/ $frombase, /*. int .*/ $tobase){}

/**
	WARNING. This function cannot be called with 3 arguments. Instead,
	you MUST either provide one, two or four arguments.
*/
/*. string.*/ function number_format(/*.float.*/ $n, $decimals = 0, $dec_point = ".", $thousands_sep = ","){}

/*. float .*/ function fmod(/*. float .*/ $x, /*. float .*/ $y){}
/*. int   .*/ function ip2long(/*. string .*/ $ip_address){}
/*. string.*/ function long2ip(/*. int .*/ $proper_address){}
/*. string.*/ function getenv(/*. string .*/ $varname){}
/*. bool  .*/ function putenv(/*. string .*/ $setting){}
/*. array[string][int]string .*/ function getopt(/*. string .*/ $options /*., args .*/){}
/*. mixed.*/ function microtime($get_as_float = FALSE){}
/*. array[string]int .*/ function gettimeofday(){}
/*. array[string]int .*/ function getrusage($who = 0){}
/*. string.*/ function uniqid($prefix = "", $more_entropy = FALSE){}
/*. string.*/ function quoted_printable_decode(/*. string .*/ $str){}
/*. string.*/ function quoted_printable_encode(/*. string .*/ $str){}
/*. string.*/ function convert_cyr_string(/*. string .*/ $str, /*. string .*/ $from, /*. string .*/ $to){}
/*. string.*/ function get_current_user(){}
/*. void  .*/ function set_time_limit(/*. int .*/ $seconds){}
/*. string.*/ function get_cfg_var(/*. string .*/ $varname){}
/** @deprecated */
/*. bool  .*/ function set_magic_quotes_runtime(/*. int .*/ $new_setting){}
/*. int   .*/ function get_magic_quotes_gpc(){}
/*. int   .*/ function get_magic_quotes_runtime(){}

/**
	Official manual states that the second arguments is de-facto
	mandatory, altough being formally optional. Then the declaration here
	given is the most conservative one to prevent errors.
	@deprecated This function has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0.
*/
/*. bool  .*/ function import_request_variables(/*. string .*/ $types, /*. string .*/ $prefix)
{}

/*. bool  .*/ function error_log(/*. string .*/ $message /*., args .*/){}
/*. mixed .*/ function call_user_func(/*. mixed .*/ $func /*., args .*/)/*. triggers E_WARNING .*/{}
/*. mixed .*/ function call_user_func_array(/*. mixed .*/ $func, /*. array[int]mixed .*/ $param_arr)/*. triggers E_WARNING .*/{}

/** @deprecated Use {@link call_user_func_array()} instead. */
/*. mixed .*/ function call_user_method(/*. string .*/ $method_name, /*. object .*/ &$obj /*., args .*/){}

/** @deprecated Use {@link call_user_func_array()} instead. */
/*. mixed .*/ function call_user_method_array(/*. string .*/ $method_name, /*. object .*/ &$obj, /*. array[int]mixed .*/ $paramarr){}

/*. string.*/ function serialize(/*. mixed .*/ $value){}

/**
	Under PHP 5, please apply <tt>cast()</tt> to the result.
	For example, if the intended result must be an object:
	<p>
	<tt>$obj = cast("MyClass", unserialize($serialized_data));</tt>
	<p>
	or if the expected result must be an array:
	<p>
	<tt>$arr = cast("array[int]MyClass", unserialize($serialized_data)(;</tt>

	<p>
	<b>Warning.</b> Raises E_NOTICE if the data are corrupted.
*/
/*. mixed .*/ function unserialize(/*. string .*/ $s)
/*. triggers E_NOTICE .*/{}

/*. void  .*/ function var_dump(/*. mixed .*/ $x /*., args .*/){}
/*. string.*/ function var_export(/*. mixed .*/ $x /*., args .*/){}
/*. void  .*/ function debug_zval_dump(/*. mixed .*/ $variable){}
/*. mixed .*/ function print_r(/*. mixed .*/ $expr, $return_ = FALSE){}
/*. void  .*/ function register_shutdown_function(/*. mixed .*/ $func /*., args .*/){}

/** @deprecated See the manual for details. */
/*. bool  .*/ function register_tick_function(/*. mixed .*/ $func /*., args .*/){}

/*. void  .*/ function unregister_tick_function(/*. string .*/ $func){}
/*. mixed .*/ function highlight_file(/*. string .*/ $filename /*., args .*/){}
/*. mixed .*/ function show_source(/*. string .*/ $filename /*., args .*/){}
/*. mixed .*/ function highlight_string(/*. string .*/ $str /*., args .*/){}
/*. string.*/ function php_strip_whitespace(/*. string .*/ $filename){}

/**
	@deprecated For technical reasons, this function is deprecated and
	removed from PHP.  Instead, use <code>php -l somefile.php</code> from
	the commandline. Or, even better, use PHPLint.
*/
/*. bool  .*/ function php_check_syntax(/*. string .*/ $filename /*., args .*/){}

/**
	Gets the value of a configuration option.
    Returns the value of the configuration option on success. Failure,
    such as querying for a non-existent value, will return an empty string.
    Boolean values are returned as "0" for FALSE, "1" for "TRUE".  Some
    parameters have the default value NULL, and this value gets returned
    if the paramenter isn't defined in the php.ini.  Some values uses a
    special format, for example "upload_max_filesize = 10M": take a look
    to the official WEB site for an example on how to parse these values.
    A typical example:
	<pre>
	if ( ini_get("magic_quotes_gpc") === "1" )
		$s = stripslashes($s);
	</pre>
*/ 
/*. string.*/ function ini_get(/*. string .*/ $varname){}

/*. array[string][string]mixed .*/ function ini_get_all(/*. args .*/){}
/*. string.*/ function ini_set(/*. string .*/ $varname, /*. string .*/ $newvalue){}
/*. string.*/ function ini_alter(/*. string .*/ $varname, /*. string .*/ $newvalue){}
/*. void  .*/ function ini_restore(/*. string .*/ $varname){}
/*. string.*/ function get_include_path(){}
/*. string.*/ function set_include_path(/*. string .*/ $new_inc_path){}
/*. void  .*/ function restore_include_path(){}
/*. bool  .*/ function setcookie(/*. string .*/ $n /*., args .*/){}
/*. bool  .*/ function setrawcookie(/*. string .*/ $n /*., args .*/){}
/*. void  .*/ function header(/*. string .*/ $s /*., args .*/){}
/*. void  .*/ function header_remove(/*. string .*/ $name = NULL){}
/*. bool  .*/ function headers_set(/*. args .*/){}
/*. array[int]string .*/ function headers_list(){}
/*. int   .*/ function connection_aborted(){}
/*. int   .*/ function connection_status(){}
/*. int   .*/ function ignore_user_abort(/*. args .*/){}
/*. array[string][string]mixed .*/ function parse_ini_file(/*. string .*/ $filename /*., args .*/){}
/*. bool  .*/ function is_uploaded_file(/*. string .*/ $fn){}
/*. bool  .*/ function move_uploaded_file(/*. string .*/ $fn, /*. string .*/ $dst){}
/*. string.*/ function gethostbyaddr(/*.string.*/ $ip){}
/*. string.*/ function gethostbyname(/*.string.*/ $hn){}
/*. string.*/ function gethostname(){}
/*. array[int]string .*/ function gethostbynamel(/*. string .*/ $hostname){}
/*. bool  .*/ function dns_check_record(/*. string .*/ $host /*., args .*/){}
/*. bool  .*/ function checkdnsrr(/*. string .*/ $host /*., args .*/){}
/*. bool  .*/ function dns_get_mx(/*. string .*/ $hostname, /*. return array[int]string .*/ &$mxhosts /*., args .*/){}
/*. bool  .*/ function getmxrr(/*. string .*/ $hostname, /*. return array[int]string .*/ &$a /*., args .*/){}
/*. array[int][string]mixed .*/ function dns_get_record(/*. string .*/ $hostname /*., args .*/){}
/*. int   .*/ function intval(/*. mixed .*/ $v /*., args .*/){}
/*. float .*/ function floatval(/*. mixed .*/ $v){}
/*. float .*/ function doubleval(/*. mixed .*/ $v){}
/*. string.*/ function strval(/*. mixed .*/ $v){}
/*. string.*/ function gettype(/*. mixed .*/ $variable){}

/** @deprecated The variable's type is always statically determined under PHPLint and cannot be changed at runtime to arbitrary types. */
/*. bool  .*/ function settype(/*. mixed .*/ &$v, /*. string .*/ $type){}

/*. bool  .*/ function is_null(/*. mixed .*/ $v){}
/*. bool  .*/ function is_resource(/*.mixed.*/ $v){}
/*. bool  .*/ function is_bool(/*.mixed.*/ $v){}
/*. bool  .*/ function is_long(/*.mixed.*/ $v){}
/*. bool  .*/ function is_float(/*.mixed.*/ $v){}
/*. bool  .*/ function is_int(/*.mixed.*/ $v){}
/*. bool  .*/ function is_integer(/*.mixed.*/ $v){}
/*. bool  .*/ function is_double(/*.mixed.*/ $v){}
/*. bool  .*/ function is_real(/*.mixed.*/ $v){}
/*. bool  .*/ function is_numeric(/*.mixed.*/ $v){}
/*. bool  .*/ function is_string(/*.mixed.*/ $v){}
/*. bool  .*/ function is_array(/*.mixed.*/ $v){}
/*. bool  .*/ function is_object(/*.mixed.*/ $v){}
/*. bool  .*/ function is_scalar(/*.mixed.*/ $v){}
/*. bool  .*/ function is_callable(/*.mixed.*/ $f /*., args .*/){}

/**
	@deprecated As of PHP 5, the dl() function is deprecated in every SAPI
	except CLI. Use Extension Loading Directives method instead.
*/ 
/*. bool  .*/ function dl(/*. string .*/ $lib){}

/*. int   .*/ function pclose(/*. resource .*/ $handle){}
/*. resource .*/ function popen(/*. string .*/ $cmd, /*. string .*/ $mode)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function readfile(/*. string .*/ $fn /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function rewind(/*. resource .*/ $handle){}
/*. bool  .*/ function rmdir(/*. string .*/ $dirname /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function umask(/*. args .*/){}
/*. bool  .*/ function fclose(/*.resource.*/ $f)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function feof(/*. resource .*/ $f){}
/*. string.*/ function fgetc(/*. resource .*/ $h)
/*. triggers E_WARNING .*/{}
/*. string.*/ function fgets(/*. resource .*/ $f /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. string.*/ function fgetss(/*. resource .*/ $f /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. string.*/ function fread(/*.resource.*/ $f, /*.int.*/ $length)
/*. triggers E_WARNING .*/{}
/*.resource.*/function fopen(/*.string.*/ $filename, /*.string.*/ $mode /*. , args.*/)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function fpassthru(/*. resource .*/ $handle)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function ftruncate(/*. resource .*/ $handle, /*. int .*/ $size)
/*. triggers E_WARNING .*/{}
/*. array[string]int .*/ function fstat(/*. resource .*/ $handle)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function fseek(/*. resource .*/ $handle, /*. int .*/ $offset /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function ftell(/*. resource .*/ $handle)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function fflush(/*. resource .*/ $handle)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function fwrite(/*. resource .*/ $handle, /*. string .*/ $s /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function fputs(/*. resource .*/ $handle, /*. string .*/ $s /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function mkdir(/*. string .*/ $pathname /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function rename(/*. string .*/ $oldname, /*. string .*/ $newname /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function copy(/*. string .*/ $source, /*. string .*/ $dest /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. string.*/ function tempnam(/*. string .*/ $dir, /*. string .*/ $prefix){}
/*. resource .*/ function tmpfile(){}
/*. array[int]string .*/ function file(/*. string .*/ $filename /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. string.*/ function file_get_contents(/*.string.*/ $fn /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function file_put_contents(/*.string.*/ $fn, /*.string.*/ $data /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. array[int]string .*/ function fgetcsv(/*. resource .*/ $handle /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function flock(/*. resource .*/ $handle, /*. int .*/ $op /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. array[string]string .*/ function get_meta_tags(/*. string .*/ $fn /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function set_file_buffer(/*. resource .*/ $stream, /*. int .*/ $buffer){}
/*. string.*/ function realpath(/*. string .*/ $path){}
/*. bool  .*/ function fnmatch(/*. string .*/ $pattern, /*. string .*/ $str /*., args .*/){}
# FIXME:
/*. if_php_ver_4 .*/
	/*. resource .*/ function fsockopen(/*.string.*/ $target /*., args .*/)
		/*. triggers E_WARNING .*/{}
/*. else .*/
	/*. resource .*/ function fsockopen(/*.string.*/ $target, $port=0, /*. return .*/ &$errno=0, /*. return .*/ &$errstr="", $timeout=0.0)
		/*. triggers E_WARNING .*/{}
/*. end_if_php_ver .*/
/*. resource .*/ function pfsockopen(/*. string .*/ $hostname /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. string.*/ function pack(/*. string .*/ $format /*., args .*/){}
/*. mixed[] .*/ function unpack(/*. string .*/ $format, /*. string .*/ $data){}
/*. mixed .*/ function get_browser( /*. args .*/){}
/*. string.*/ function crypt(/*. string .*/ $str, $salt = ""){}
/*. resource .*/ function opendir(/*. string .*/ $path /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. void  .*/ function closedir(/*. resource .*/ $dirhandle){}
/*. bool  .*/ function chdir(/*. string .*/ $dir)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function chroot(/*. string .*/ $dir)
/*. triggers E_WARNING .*/{}
/*. string.*/ function getcwd()/*. triggers E_WARNING .*/{}
/*. void  .*/ function rewinddir(/*. resource .*/ $dir_handle)
/*. triggers E_WARNING .*/{}
/*. string.*/ function readdir(/*. resource .*/ $dir_handle)
/*. triggers E_WARNING .*/{}

/*. if_php_ver_4 .*/

class Directory {
	var /*. string .*/ $path;
	var /*. resource .*/ $hanlde;
	/*. string .*/ function read(){}
	/*. void .*/ function rewind(){}
	/*. void .*/ function close(){}
}

/*. else .*/

class Directory {
	public /*. string .*/ $path;
	public /*. resource .*/ $handle;
	public /*. string .*/ function read(){}
	public /*. void .*/ function rewind(){}
	public /*. void .*/ function close(){}
}

/*. end_if_php_ver .*/

/*. Directory .*/ function dir(/*. string .*/ $directory /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. array[int]string .*/ function scandir(/*. string .*/ $dir /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. array[int]string .*/ function glob(/*. string .*/ $pattern /*., args .*/){}
/*. int   .*/ function fileatime(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function filectime(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function filegroup(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function fileinode(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function filemtime(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function fileowner(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function fileperms(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function filesize(/*. string .*/ $filename)
/*. triggers E_WARNING .*/{}
/*. string.*/ function filetype(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function file_exists(/*.string.*/ $fn){}
/*. bool  .*/ function is_writable(/*. string .*/ $fn){}
/*. bool  .*/ function is_writeable(/*. string .*/ $fn){}
/*. bool  .*/ function is_readable(/*. string .*/ $fn){}
/*. bool  .*/ function is_executable(/*. string .*/ $fn){}
/*. bool  .*/ function is_file(/*. string .*/ $fn){}
/*. bool  .*/ function is_dir(/*. string .*/ $fn){}
/*. bool  .*/ function is_link(/*. string .*/ $fn){}
/*. array[]int .*/ function stat(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. array[]int .*/ function lstat(/*. string .*/ $fn)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function chown(/*. string .*/ $fn, /*. mixed .*/ $user)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function chgrp(/*. string .*/ $fn, /*. mixed .*/ $group)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function chmod(/*. string .*/ $fn, /*. int .*/ $mode)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function touch(/*. string .*/ $fn /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. void  .*/ function clearstatcache(/*. args .*/){}
/*. float .*/ function disk_total_space(/*. string .*/ $dir)
/*. triggers E_WARNING .*/{}
/*. float .*/ function disk_free_space(/*. string .*/ $dir)
/*. triggers E_WARNING .*/{}
/*. float .*/ function diskfreespace(/*. string .*/ $dir)
/*. triggers E_WARNING .*/{}
/*. bool  .*/ function mail(/*. string .*/ $to, /*. string .*/ $subj, /*. string .*/ $msg /*., args .*/){}
/*. int   .*/ function ezmlm_hash(/*. string .*/ $addr){}
/*. bool  .*/ function openlog(/*. string .*/ $ident, /*. int .*/ $option, /*. int .*/ $facility){}
/*. bool  .*/ function syslog(/*.int.*/ $priority, /*.string.*/ $msg){}
/*. bool  .*/ function closelog(){}
/*. if_php_ver_4 .*/

	/*. void  .*/ function define_syslog_variables(){}
	
/*. else .*/

	/** @deprecated Since PHP 5.3.0. */
	/*. void  .*/ function define_syslog_variables()
	/*. triggers E_DEPRECATED .*/{}
	
/*. end_if_php_ver .*/
/*. float .*/ function lcg_value(){}
/*. string.*/ function metaphone(/*. string .*/ $str /*., args .*/){}
/*. bool  .*/ function ob_start(/*. args .*/){}
/*. void  .*/ function ob_flush(){}
/*. void  .*/ function ob_clean(){}
/*. bool  .*/ function ob_end_flush(){}
/*. bool  .*/ function ob_end_clean(){}
/*. bool  .*/ function ob_get_flush(){}
/*. string.*/ function ob_get_clean(){}
/*. int   .*/ function ob_get_length(){}
/*. int   .*/ function ob_get_level(){}
/*. array .*/ function ob_get_status( /*. args .*/){}
/*. string.*/ function ob_get_contents(){}
/*. void  .*/ function ob_implicit_flush( /*. args .*/){}
/*. array .*/ function ob_list_handlers(){}
/*. string.*/ function ob_gzhandler(/*. string .*/ $str, /*. int .*/ $mode){}
/*. bool  .*/ function ksort(/*. array .*/ $array_arg, $sort_flags = SORT_REGULAR){}
/*. bool  .*/ function krsort(/*. array .*/ $array_arg, $sort_flags = SORT_REGULAR){}
/*. void  .*/ function natsort(/*. array .*/ $array_arg){}
/*. void  .*/ function natcasesort(/*. array .*/ $array_arg){}
/*. bool  .*/ function asort(/*. array .*/ $a, $sort_flags = SORT_REGULAR){}
/*. bool  .*/ function arsort(/*. array .*/ $a, $sort_flags = SORT_REGULAR){}
/**
	Warning: indexes must really be int.
	That is because in any case this function changes the type of the array
	into array[int].
*/
/*. bool  .*/ function sort(/*. array[int] .*/ $a, $sort_flags = SORT_REGULAR){}

/**
	Warning: indexes must really be int.
	That is because in any case this function changes the type of the array
	into array[int].
*/
/*. bool  .*/ function rsort(/*. array[int] .*/ $a,  $sort_flags = SORT_REGULAR){}

/**
	Warning: indexes must really be int.
	That is because in any case this function changes the type of the array
	into array[int].
*/
/*. bool  .*/ function usort(/*. array[int] .*/ $a, /*. mixed .*/ $cmp_func)
{}
/*. bool  .*/ function uasort(/*. array .*/ $a, /*. mixed .*/ $cmp_func){}
/*. bool  .*/ function uksort(/*. array .*/ $a, /*. mixed .*/ $cmp_func){}
/*. bool  .*/ function shuffle(/*. array .*/ $array_arg){}
/*. bool  .*/ function array_walk(/*. array .*/ $input, /*. mixed .*/ $funcname /*., args .*/){}
/*. bool  .*/ function array_walk_recursive(/*. array .*/ $input, /*. mixed .*/ $funcname /*., args .*/){}
/*. int   .*/ function count(/*. mixed .*/ $a /*., args .*/){}
/*. int   .*/ function sizeof(/*. array .*/ $a /*., args .*/){}
/*. mixed .*/ function end(/*. array .*/ $a){}
/*. mixed .*/ function prev(/*. array .*/ $a){}
/*. mixed .*/ function next(/*. array .*/ $a){}
/*. mixed .*/ function reset(/*. array .*/ $a){}
/*. mixed .*/ function current(/*. array .*/ $a){}
/*. mixed .*/ function key(/*. array .*/ $array_arg){}
/*. float .*/ function min(/*.float.*/ $a, /*.float.*/ $b /*., args .*/){}

/**
	Actually the max() function has a much more complex semantic that cannot
	be effectively expressed using the restrictive syntax of PHPLint.
	The arguments can be of the type int or float, and the result can
	be either int or float; a single array of numbers is also allowed.
	The syntax described here should be the most useful one, with two or
	more int or float numbers and returning a float. If the required result
	must be int, use a cast: <tt>$m = (int) max($count1, $count2);</tt>.
*/
/*. float .*/ function max(/*.float.*/ $a, /*.float.*/ $b /*., args .*/){}

/*. bool  .*/ function in_array(/*. mixed .*/ $needle, /*. array .*/ $haystack /*., args .*/){}
/*. mixed .*/ function array_search(/*. mixed .*/ $needle, /*. array .*/ $hatstack /*., args .*/){}

/** @deprecated It may overwrite existing variables with unpredictable types of data. */
/*. int   .*/ function extract(/*. array[string]mixed .*/ $var_array /*., args .*/){}

/*. array[string]mixed .*/ function compact(/*. mixed .*/ $var_names /*., args .*/){}
/*. array .*/ function range(/*. mixed .*/ $low, /*. mixed .*/ $high /*., args .*/){}
/*. array  .*/ function array_change_key_case(/*. array .*/ $a /*., args .*/){}
/*. array[int][]mixed .*/ function array_chunk(/*. array .*/ $a, /*. int .*/ $size, $preserve_keys = FALSE)
	/*. triggers E_WARNING .*/{}
/*. array .*/ function array_combine(/*. array .*/ $keys, /*. array .*/ $values)
	/*. triggers E_WARNING .*/{}
/*. array .*/ function array_count_values(/*. array .*/ $input)
	/*. triggers E_WARNING .*/{}
/*. array .*/ function array_diff(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_diff_key(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_diff_assoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_diff_uassoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_fill(/*. int .*/ $start_key, /*. int .*/ $num, /*. mixed .*/ $val)
	/*. triggers E_WARNING .*/{}
/*. array .*/ function array_filter(/*. array .*/ $input /*., args .*/){}
/*. array .*/ function array_flip(/*. array .*/ $input){}
/*. array .*/ function array_intersect(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_intersect_assoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_intersect_uassoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. bool  .*/ function array_key_exists(/*. mixed .*/ $key, /*. array .*/ $search){}
/*. array[int]mixed .*/ function array_keys(/*. array .*/ $input /*., args .*/){}
/*. array .*/ function array_map(/*. mixed .*/ $callback, /*. array .*/ $a1 /*., args .*/){}
/*. array .*/ function array_merge(/*. array .*/ $a1, /*. array .*/ $a2 /*., args .*/){}
/*. array .*/ function array_merge_recursive(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. bool  .*/ function array_multisort(/*. array .*/ $ar1 /*., args .*/){}
/*. array .*/ function array_pad(/*. array .*/ $input, /*. int .*/ $pad_size, /*. mixed .*/ $pad_value){}
/*. mixed .*/ function array_pop(/*. array .*/ $stack){}
/*. int   .*/ function array_push(/*. array .*/ $stack, /*. mixed .*/ $var_ /*., args .*/){}
/*. mixed .*/ function array_rand(/*. array .*/ $input /*., args .*/){}
/*. mixed .*/ function array_reduce(/*. array .*/ $input, /*. mixed .*/ $string_ /*., args .*/){}
/*. array .*/ function array_reverse(/*. array .*/ $input /*., args .*/){}
/*. mixed .*/ function array_shift(/*. array .*/ $a){}
/*. array .*/ function array_slice(/*. array .*/ $input, /*. int .*/ $offset /*., args .*/){}
/*. array .*/ function array_splice(/*. array .*/ $input, /*. int .*/ $offset /*., args .*/){}
/*. float .*/ function array_sum(/*. array .*/ $input){}
/*. array .*/ function array_udiff(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_udiff_assoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_udiff_uassoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_uintersect(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_uintersect_assoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_uintersect_uassoc(/*. array .*/ $arr1, /*. array .*/ $arr2 /*., args .*/){}
/*. array .*/ function array_unique(/*. array .*/ $input){}
/*. int   .*/ function array_unshift(/*. array .*/ $stack, /*. mixed .*/ $var_ /*., args .*/){}
/*. array .*/ function array_values(/*. array .*/ $input){}




/*. int   .*/ function assert(/*. mixed .*/ $assertion, /*. string .*/ $description = NULL){}
/*. mixed .*/ function assert_options(/*. int .*/ $what, /*. mixed .*/ $value = NULL){}
/*. int   .*/ function version_compare(/*. string .*/ $ver1, /*. string .*/ $ver2 /*., args .*/){}
/*. int   .*/ function ftok(/*. string .*/ $pathname, /*. string .*/ $proj){}
/*. string.*/ function str_rot13(/*. string .*/ $str){}
/*. bool  .*/ function output_add_rewrite_var(/*. string .*/ $name, /*. string .*/ $value){}
/*. bool  .*/ function output_reset_rewrite_vars(){}
/*. mixed .*/ function date_sunrise(/*. mixed .*/ $time /*., args .*/){}
/*. mixed .*/ function date_sunset(/*. mixed .*/ $time /*., args .*/){}
### This function is built-in in PHPLint:
###/*. bool  .*/ function trigger_error(/*.string.*/ $msg /*., args .*/){}
/*. int   .*/ function strlen(/*.string.*/ $s){}
/*. int   .*/ function strcmp(/*.string.*/ $a, /*.string.*/ $b){}
/*. bool  .*/ function ctype_cntrl(/*. string .*/ $text){}
/*. int   .*/ function func_num_args(){}
/*. mixed .*/ function func_get_arg(/*. int .*/ $n){}
/*. array[int]mixed .*/ function func_get_args(){}
/*. array .*/ function each(/*. array .*/ $a){}
/*. bool  .*/ function empty(/*. mixed .*/ $variable){}
/*. void  .*/ function unset(/*. mixed .*/ $var_ /*., args .*/){}

/** @deprecated The contents of the eval() cannot be parsed by PHPLint. */
/*. mixed .*/ function eval(/*. string .*/ $cmd){}

/*. bool  .*/ function defined(/*. string.*/ $c){}
/*. if_php_ver_4 .*/
	/*. bool  .*/ function headers_sent(/*. args .*/){}
/*. else .*/
	/*. bool  .*/ function headers_sent(/*. return string .*/ &$file='', /*. return int .*/ &$line=0){}
/*. end_if_php_ver .*/
/*. bool  .*/ function header_register_callback(/*. mixed .*/ $callback){}
/*. int   .*/ function http_response_code($response_code = 0){}
/*. mixed .*/ function set_error_handler(/*.mixed.*/ $cb /*., args.*/){}
/*. int   .*/ function error_reporting(/*. args .*/){}
/*. bool  .*/ function function_exists(/*. string .*/ $func_name){}
/*. bool  .*/ function method_exists(/*. mixed .*/ $obj, /*. string .*/ $method){}
/*. string.*/ function get_class(/*. object .*/ $obj=NULL){}
/*. string.*/ function zend_version(){}
/*. bool  .*/ function is_subclass_of(/*. mixed .*/ $obj, /*.string.*/ $class_name){}


class stdClass{}

/**
 * Trying to retrieve an object from $_SESSION[] whose class is undefined
 * we end with an instance of this dummy class. Original properties are
 * restored, but the original class name and the methods do not, so the
 * class is unusable. Trying to call any method on this class causes these
 * messages:
 *
 * PHP Notice:  main(): The script tried to execute a method or access a
 * property of an incomplete object.  Please ensure that the class definition
 * "MyClass" of the object you are trying to operate on was loaded _before_
 * unserialize() gets called or provide a __autoload() function to load the
 * class definition  in /home/www.icosaedro.it/public_html/mypage.html on
 * line 34
 * 
 * PHP Fatal error:  main(): The script tried to execute a method or access
 * a property of an incomplete object. [again, same hint]
 */
class __PHP_Incomplete_Class_Name {}

/*. bool  .*/ function my_drawtext(/*. resource .*/ $image, /*. string .*/ $text, /*. resource .*/ $font, /*. int .*/ $x, /*. int .*/ $y /*., args .*/){}
/*. int   .*/ function strncmp(/*. string .*/ $str1, /*. string .*/ $str2, /*. int .*/ $len){}
/*. int   .*/ function strncasecmp(/*. string .*/ $str1, /*. string .*/ $str2, /*. int .*/ $len){}
/*. string.*/ function get_parent_class(/*. mixed .*/ $object_=NULL){}
/*. bool  .*/ function is_a(/*. object .*/ $object_, /*. string .*/ $class_name){}
/*. array[string]mixed .*/ function get_class_vars(/*. string .*/ $class_name){}
/*. array[string]mixed .*/ function get_object_vars(/*. object .*/ $obj){}
/*. array[int]string .*/ function get_class_methods(/*. mixed .*/ $class_){}
/*. bool  .*/ function class_exists(/*. string .*/ $classname, /*.bool.*/ $autoload=TRUE){}
/*. bool  .*/ function interface_exists(/*. string .*/ $classname, /*.bool.*/ $autoload= TRUE){}
/*. void  .*/ function leak(/*. int .*/ $num_bytes=3){}
/*. array[int]string .*/ function get_included_files(){}
/*. void  .*/ function restore_error_handler(){}
/*. array[int]string .*/ function get_declared_classes(){}
/*. array[int]string .*/ function get_declared_interfaces(){}
/*. array[string][int]string .*/ function get_defined_functions(){}
/*. array[string]mixed .*/ function get_defined_vars(){}
/*. string.*/ function create_function(/*. string .*/ $args_, /*. string .*/ $code){}
/*. string.*/ function get_resource_type(/*. resource .*/ $res){}
/*. array[int]string .*/ function get_loaded_extensions(){}
/*. array[string]mixed .*/ function get_defined_constants(){}
define("DEBUG_BACKTRACE_PROVIDE_OBJECT", 1);
define("DEBUG_BACKTRACE_IGNORE_ARGS", 2);
/*. void  .*/ function debug_print_backtrace($options = 0, $limit = 0){}
/*. array[int][string]mixed .*/ function debug_backtrace(
	$options = DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit = 0){}
/*. bool  .*/ function extension_loaded(/*. string .*/ $extension_name){}
/*. array[int]string .*/ function get_extension_funcs(/*. string .*/ $extension_name){}
/*. string.*/ function confirm_extname_compiled(/*. string .*/ $arg){}
/*. int   .*/ function memory_get_usage(){}
/*. string.*/ function uuencode(/*. string .*/ $data){}
/*. string.*/ function uudecode(/*. string .*/ $data){}
/*. string.*/ function php_real_logo_guid(){}
/*. string.*/ function php_egg_logo_guid(){}
/*. string.*/ function image_type_to_extension(/*. int .*/ $imagetype /*., args .*/){}
/*. string.*/ function image_type_to_mime_type(/*. int .*/ $imgtype){}
/*. string[] .*/ function get_headers(/*. string .*/ $url, $format = 0)/*. triggers E_WARNING .*/{}
/*. array[string]mixed .*/ function error_get_last(){}



/*. if_php_ver_5 .*/








/*. bool  .*/ function time_sleep_until(/*. float .*/ $timestamp)
/*. triggers E_WARNING .*/{}

/*. array .*/ function array_fill_keys(/*. array .*/ $keys, /*. mixed .*/ $value){}
/*. array .*/ function array_intersect_key(/*. array .*/ $array1, /*. array .*/ $array2 /*., args .*/){}
/*. array .*/ function array_intersect_ukey(/*. array .*/ $array1, /*. array .*/ $array2 /*., args .*/){}
/*. array .*/ function array_diff_ukey(/*. array .*/ $array1, /*. array .*/ $array2 /*., args .*/){}
/*. float .*/ function array_product(/*. array .*/ $array_){}
/*. int .*/ function memory_get_peak_usage(/*. args .*/){}

/*. string.*/ function set_exception_handler(/*. mixed .*/ $exception_handler){}
/*. void  .*/ function restore_exception_handler(){}
/*. string .*/ function sys_get_temp_dir()/*. triggers E_WARNING .*/{}
/*. bool .*/ function property_exists(/*. mixed .*/ $class_, /*. string .*/ $property){}

class Exception
{
	/** Exception message */
	protected $message = "";
	
	/** User defined exception code */
	protected $code = 0;
	
	/** Source filename of exception */
	protected /*.string.*/ $file;
	
	/** Source line of exception */
	protected /*.int   .*/ $line = 0;
	
	/** Backtrace */
	private /*. mixed .*/ $trace;
	
	/** Previous exception if nested exception */
	private /*. Exception .*/ $previous;

	/*.void.*/ function __construct($message = "", $code = 0,
		Exception $previous = NULL){}

	/** Message of exception */
	final /*.string.*/ function getMessage(){}

	/** Return previous exception, or NULL if there is not */ 
	final /*. Exception .*/ function getPrevious(){}

	/** Code of exception */
	final /*. int .*/ function getCode(){}
	
	/** Source filename */ 
	final /*.string.*/ function getFile(){}
	
	/** Source line */
	final /*. int .*/ function getLine(){}
	
	/** An array of the backtrace() */ 
	final /*.array[int][string]mixed .*/ function getTrace(){}
	
	/** Formatted string of trace */
	final /*.string.*/ function getTraceAsString(){}

	/** Formatted string for display */ 
	/*.string.*/ function __toString(){}

	/**
		Always gives a fatal error: exceptions are not clonable.
	    FIXME: this method is `private' in official manual: why?
	*/ 
	final public /*. void .*/ function __clone(){}
}

/**
	Generic exception into which errors can be mapped.
	PHPLint uses just it extensively, see the cast() magic function int
	the stdlib/errors.php package.
*/
class ErrorException extends Exception
{
	protected /*. int .*/ $severity = 0;

	/** Encapsulate errors in exceptions

		$message is the human-readable description of the
		exception, while $code lets the program to detect the
		specific condition occurred. The $severity is the usual
		log level (see the E_* constants).  $filename and $lineno
		lets to indicate an alternate position; customized
		error handling functions can indicate the real source
		of the error rather that the point where the exception
		was thrown.
	*/
	/*. void .*/ function __construct(
		$message = "",
		$code = 0,
		$severity = E_ERROR,
		$filename = /*. (string) .*/ NULL,
		$lineno = 0,
		$previous = /*. (Exception) .*/ NULL
	)
	{ parent::__construct(); }

	/*. int .*/ function getSeverity(){}
}

/*. end_if_php_ver .*/



/*. if_php_ver_5 .*/

	define('DATE_ATOM', '?');
	define('DATE_COOKIE', '?');
	define('DATE_ISO8601', '?');
	define('DATE_RFC822', '?');
	define('DATE_RFC850', '?');
	define('DATE_RFC1036', '?');
	define('DATE_RFC1123', '?');
	define('DATE_RFC2822', '?');
	define('DATE_RFC3339', '?');
	define('DATE_RSS', '?');
	define('DATE_W3C', '?');

	/*. forward class DateTime{} .*/

	/** See: {@link http://www.php.net/manual/en/ref.datetime.php} */
	class DateTimeZone
	{
		const
			AFRICA = 1,
			AMERICA = 2,
			ANTARCTICA = 4,
			ARCTIC = 8,
			ASIA = 16,
			ATLANTIC = 32,
			AUSTRALIA = 64,
			EUROPE = 128,
			INDIAN = 256,
			PACIFIC = 512,
			UTC = 1024,
			ALL = 2047,
			ALL_WITH_BC = 4095,
			PER_COUNTRY = 4096;

		static /*. array .*/ function listAbbreviations(){}
		static /*. array[int]string .*/ function listIdentifiers(
			/*. int .*/ $what = DateTimeZone::ALL,
			/*. string .*/ $country = NULL){}
		static /*. string .*/ function getName(){}
		static /*. int .*/ function getOffset(/*. DateTime .*/ $datetime){}
		/*. void .*/ function __construct(/*. string .*/ $timezone){}
		/*. array[string][string]mixed .*/ function getTransitions(){}
	}

	class DateInterval
	{
		/*. void .*/ function __construct(/*. string .*/ $interval_spec){}
		static /*. DateInterval .*/ function createFromDateString(/*. string .*/ $time){}
		/*. string .*/ function format(/*. string .*/ $format){}
	}

	/** See: {@link http://www.php.net/manual/en/ref.datetime.php} */
	class DateTime
	{
		const
			ATOM  = "Y-m-d\\TH:i:sP",
			COOKIE = "l, d-M-y H:i:s T",
			ISO8601 = "Y-m-d\\TH:i:sO",
			RFC822 = "D, d M y H:i:s O",
			RFC850 = "l, d-M-y H:i:s T",
			RFC1036 = "D, d M y H:i:s O",
			RFC1123 = "D, d M Y H:i:s O",
			RFC2822 = "D, d M Y H:i:s O",
			RFC3339 = "Y-m-d\\TH:i:sP",
			RSS = "D, d M Y H:i:s O",
			W3C = "Y-m-d\\TH:i:sP";

		/*. void .*/ function __construct(
			/*. string .*/ $time = "now",
			/*. DateTimeZone .*/ $timezone = NULL)
			/*. throws Exception .*/ {}
		/*. void .*/ function setDate(/*. int .*/ $year, /*. int .*/ $month, /*. int .*/ $day){}
		/*. string .*/ function format(/*. string .*/ $format){}
		/*. void .*/ function setISODate(/*. int .*/ $year, /*. int .*/ $week /*., args .*/){}
		/*. void .*/ function modify(/*. string .*/ $modify){}
		/*. int .*/ function getOffset(){}
		/*. void .*/ function setTime(/*. int .*/ $hour, /*. int .*/ $minute /*., args .*/){}
		/*. DateTimeZone .*/ function getTimezone(){}
		/*. void .*/ function setTimezone(/*. DateTimeZone .*/ $tz){}
		/*. DateTime .*/ function add(/*. DateInterval .*/ $interval){}
		/*. DateTime .*/ function sub(/*. DateInterval .*/ $interval){}
		/*. DateInterval .*/ function diff(/*. DateTime .*/ $datetime /*., args .*/){}
		static /*. DateTime .*/ function createFromFormat(/*. string .*/ $format, /*. string .*/ $time /*., args .*/){}
		static /*. array[string]mixed .*/ function getLastErrors(){}
		/*. int .*/ function getTimestamp(){}
		/*. DateTime .*/ function setTimestamp(/*. int .*/ $unixtimestamp){}
	}

	class DatePeriod
	{
		const EXCLUDE_START_DATE = 1;

		/*. void .*/ function __construct(/*. DateTime .*/ $start, /*. DateInterval .*/ $interval, /*. int .*/ $recurrences /*., args .*/){}
	}

	/*. DateTime .*/ function date_create( /*. args .*/){}
	/*. void .*/ function date_date_set(/*. DateTime .*/ $obj, /*. int .*/ $year, /*. int .*/ $month, /*. int .*/ $day){}
	/*. string .*/ function date_default_timezone_get(){}
	/*. bool .*/ function date_default_timezone_set(/*. string .*/ $timezone_identifier){}
	/*. string .*/ function date_format(DateTime $obj, /*. string .*/ $format){}
	/*. void .*/ function date_isodate_set(/*. DateTime .*/ $obj, /*. int .*/ $year, /*. int .*/ $week /*., args .*/){}
	/*. void .*/ function date_modify(DateTime $obj, /*. string .*/ $modify){}
	/*. int .*/ function date_offset_get(/*. DateTime .*/ $obj){}
	/*. array[string]mixed .*/ function date_parse(/*. string .*/ $date){}
	/*. array[string]string .*/ function date_sun_info(/*. int .*/ $time, /*. float .*/ $latitude, /*. float .*/ $longitude){}
	/*. void .*/ function date_time_set(/*. DateTime .*/ $obj, /*. int .*/ $hour, /*. int .*/ $minute /*., args .*/){}

	/*. DateTimeZone .*/ function date_timezone_get(/*. DateTime .*/ $obj){}
	/*. void .*/ function date_timezone_set(/*. DateTime .*/ $obj, /*. DateTimeZone .*/ $tz){}
 	/*. array .*/ function timezone_abbreviations_list(){}
	/*. array[int]string .*/ function timezone_identifiers_list(){}
	/*. string .*/ function timezone_name_from_abbr(/*. string .*/ $abbr /*., args .*/){}
	/*. string .*/ function timezone_name_get(/*. DateTimeZone .*/ $obj){}
	/*. int .*/ function timezone_offset_get(/*. DateTimeZone .*/ $tz, /*. DateTime .*/ $date){}
	/*. DateTimeZone .*/ function timezone_open(/*. string .*/ $timezone){}
	/*. array[string][string]mixed .*/ function timezone_transitions_get(/*. DateTimeZone .*/ $tz){}

	/*. void .*/ function gc_collect_cycles(){}
	/*. bool .*/ function gc_enabled(){}
	/*. void .*/ function gc_enable(){}
	/*. void .*/ function gc_disable(){}
	/*. bool .*/ function class_alias(/*. args .*/){}
	/*. string .*/ function get_called_class(){}
	#/*. void .*/ function forward_static_call(){}
	#/*. void .*/ function forward_static_call_array(){}
	/*. array[int]string .*/ function str_getcsv(/*. string .*/ $input /*., args .*/){}
	/*. string .*/ function lcfirst(/*. string .*/ $str){}
	/*. array .*/ function array_replace(/*. array .*/ $arr, /*. array .*/ $arr1 /*., args .*/){}
	/*. array .*/ function array_replace_recursive(/*. array .*/ $arr, /*. array .*/ $arr1 /*., args .*/){}
	/*. DateTime .*/ function date_add(/*. DateTime .*/ $dt, /*. DateInterval .*/ $interval){}
	/*. DateTime .*/ function date_sub(/*. DateTime .*/ $dt, /*. DateInterval .*/ $interval){}
	/*. array[string]mixed .*/ function date_parse_from_format(/*. string .*/ $format, /*. string .*/ $date){}
	/*. string .*/ function timezone_version_get(){}
	/*. array .*/ function parse_ini_string(/*. string .*/ $ini, /*. bool .*/ $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL){}

	# Incomplete doc in manual:
	#/*. void .*/ function date_diff(){}
	#/*. void .*/ function date_create_from_format(){}
	#/*. void .*/ function date_get_last_errors(){}

/*. end_if_php_ver .*/
