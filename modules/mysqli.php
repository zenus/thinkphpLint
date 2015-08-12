<?php
/** MySQL Improved Extension.

See: {@link http://www.php.net/manual/en/ref.mysqli.php}
@package mysqli
*/

/*.  require_module 'standard'; .*/


# FIXME: dummy values
define('MYSQLI_READ_DEFAULT_GROUP', 1);
define('MYSQLI_READ_DEFAULT_FILE', 1);
define('MYSQLI_OPT_CONNECT_TIMEOUT', 1);
define('MYSQLI_OPT_LOCAL_INFILE', 1);
define('MYSQLI_INIT_COMMAND', 1);
define('MYSQLI_CLIENT_SSL', 1);
define('MYSQLI_CLIENT_COMPRESS', 1);
define('MYSQLI_CLIENT_INTERACTIVE', 1);
define('MYSQLI_CLIENT_IGNORE_SPACE', 1);
define('MYSQLI_CLIENT_NO_SCHEMA', 1);
define('MYSQLI_CLIENT_FOUND_ROWS', 1);
define('MYSQLI_STORE_RESULT', 1);
define('MYSQLI_USE_RESULT', 1);
define('MYSQLI_ASSOC', 1);
define('MYSQLI_NUM', 1);
define('MYSQLI_BOTH', 1);
define('MYSQLI_STMT_ATTR_UPDATE_MAX_LENGTH', 1);
define('MYSQLI_STMT_ATTR_CURSOR_TYPE', 1);
define('MYSQLI_CURSOR_TYPE_NO_CURSOR', 1);
define('MYSQLI_CURSOR_TYPE_READ_ONLY', 1);
define('MYSQLI_CURSOR_TYPE_FOR_UPDATE', 1);
define('MYSQLI_CURSOR_TYPE_SCROLLABLE', 1);
define('MYSQLI_NOT_NULL_FLAG', 1);
define('MYSQLI_PRI_KEY_FLAG', 1);
define('MYSQLI_UNIQUE_KEY_FLAG', 1);
define('MYSQLI_MULTIPLE_KEY_FLAG', 1);
define('MYSQLI_BLOB_FLAG', 1);
define('MYSQLI_UNSIGNED_FLAG', 1);
define('MYSQLI_ZEROFILL_FLAG', 1);
define('MYSQLI_AUTO_INCREMENT_FLAG', 1);
define('MYSQLI_TIMESTAMP_FLAG', 1);
define('MYSQLI_SET_FLAG', 1);
define('MYSQLI_NUM_FLAG', 1);
define('MYSQLI_PART_KEY_FLAG', 1);
define('MYSQLI_GROUP_FLAG', 1);
define('MYSQLI_TYPE_DECIMAL', 1);
define('MYSQLI_TYPE_TINY', 1);
define('MYSQLI_TYPE_SHORT', 1);
define('MYSQLI_TYPE_LONG', 1);
define('MYSQLI_TYPE_FLOAT', 1);
define('MYSQLI_TYPE_DOUBLE', 1);
define('MYSQLI_TYPE_NULL', 1);
define('MYSQLI_TYPE_TIMESTAMP', 1);
define('MYSQLI_TYPE_LONGLONG', 1);
define('MYSQLI_TYPE_INT24', 1);
define('MYSQLI_TYPE_DATE', 1);
define('MYSQLI_TYPE_TIME', 1);
define('MYSQLI_TYPE_DATETIME', 1);
define('MYSQLI_TYPE_YEAR', 1);
define('MYSQLI_TYPE_NEWDATE', 1);
define('MYSQLI_TYPE_ENUM', 1);
define('MYSQLI_TYPE_SET', 1);
define('MYSQLI_TYPE_TINY_BLOB', 1);
define('MYSQLI_TYPE_MEDIUM_BLOB', 1);
define('MYSQLI_TYPE_LONG_BLOB', 1);
define('MYSQLI_TYPE_BLOB', 1);
define('MYSQLI_TYPE_VAR_STRING', 1);
define('MYSQLI_TYPE_STRING', 1);
define('MYSQLI_TYPE_CHAR', 1);
define('MYSQLI_TYPE_INTERVAL', 1);
define('MYSQLI_TYPE_GEOMETRY', 1);
define('MYSQLI_RPL_MASTER', 1);
define('MYSQLI_RPL_SLAVE', 1);
define('MYSQLI_RPL_ADMIN', 1);
define('MYSQLI_NO_DATA', 1);
define('MYSQLI_REPORT_INDEX', 1);
define('MYSQLI_REPORT_ERROR', 1);
define('MYSQLI_REPORT_ALL', 1);
define('MYSQLI_REPORT_OFF', 1);


/*. if_php_ver_4 .*/

class mysqli_field {
	var /*. string .*/ $name;
	var /*. string .*/ $orgname;
	var /*. string .*/ $table;
	var /*. string .*/ $orgtable;
	var /*. string .*/ $def;
	var /*. int .*/    $max_length = 0;  # dummy initial value
	var /*. int .*/    $length = 0;  # dummy initial value
	var /*. int .*/    $charsetnr = 0;  # dummy initial value
	var /*. int .*/    $flags = 0;  # dummy initial value
	var /*. string .*/ $type;
	var /*. int .*/    $decimals = 0;  # dummy initial value
}


class mysqli_result {

	var /*. int .*/    $current_field = 0;  # dummy initial value
	var /*. int .*/    $field_count = 0;  # dummy initial value
	var /*. array[int]int .*/ $lengths;
	var /*. int .*/    $num_rows = 0;  # dummy initial value
	var /*. int .*/ $type = 0;  # dummy initial value

	/*. void .*/ function close(){}
	/*. bool .*/ function data_seek(/*. int .*/ $offset){}
	/*. array[string]string .*/ function fetch_array(/*. args .*/){}
	/*. array[]string .*/ function fetch_assoc(){}
	/*. mysqli_field .*/ function fetch_field(){}
	/*. array[int]mysqli_field .*/ function fetch_fields(){}
	/*. mysqli_field .*/ function fetch_field_direct(/*. int .*/ $offset){}
	/*. object .*/ function fetch_object(){}
	/*. array[int]string .*/ function fetch_row(){}
	/*. int .*/ function field_seek(/*. int .*/ $fieldnr){}
	/*. void .*/ function free_result(){}
}


class mysqli_stmt {

	var /*. int .*/     $affected_rows = 0;  # dummy initial value
	var /*. int .*/     $errno = 0;  # dummy initial value
	var /*. string .*/  $error;
	var /*. int .*/     $field_count = 0;  # dummy initial value
	var /*. int .*/     $insert_id = 0;  # dummy initial value
	var /*. int .*/     $num_rows = 0;  # dummy initial value
	var /*. int .*/     $param_count = 0;  # dummy initial value
	var /*. string .*/  $sqlstate;

	/*. bool .*/ function bind_param(/*. string .*/ $types, /*. mixed .*/ $variable /*., args .*/){}
	/*. bool .*/ function bind_result(/*. mixed .*/ $var_ /*., args .*/){}
	/*. bool .*/ function close(){}
	/*. void .*/ function data_seek(/*. int .*/ $offset){}
	/*. bool .*/ function execute(){}
	/*. bool .*/ function fetch(){}
	/*. void .*/ function free_result(){}
	/*. bool .*/ function prepare(/*. string .*/ $query){}
	/*. bool .*/ function reset(){}
	/*. mysqli_result .*/ function result_metadata(){}
	/*. bool .*/ function send_long_data(/*. int .*/ $param_nr, /*. string .*/ $data){}
	/*. bool .*/ function store_result(){}
}



class mysqli {

	var /*. int .*/    $affected_rows = 0;  # dummy initial value
	var /*. string .*/ $client_info;
	var /*. int .*/    $client_version = 0;  # dummy initial value
	var /*. int .*/    $errno = 0;  # dummy initial value
	var /*. string .*/ $error;
	var /*. int .*/    $field_count = 0;  # dummy initial value
	var /*. string .*/ $host_info;
	var /*. string .*/ $info;
	var /*. int .*/    $insert_id = 0;  # dummy initial value
	var /*. string .*/ $protocol_version;
	var /*. string .*/ $server_info;
	var /*. int .*/    $server_version = 0;  # dummy initial value
	var /*. string .*/ $sqlstate;
	var /*. int .*/    $thread_id = 0;  # dummy initial value
	var /*. int .*/    $warning_count = 0;  # dummy initial value

	/*. void .*/ function mysqli( /*. args .*/){}
	/*. bool .*/ function autocommit(/*. bool .*/ $mode){}
	/*. bool .*/ function change_user(/*. string .*/ $user, /*. string .*/ $password, /*. string .*/ $database){}
	/*. string .*/ function character_set_name(){}
	/*. bool .*/ function close(){}
	/*. bool .*/ function commit(){}
	/*. void .*/ function debug(/*. string .*/ $debug){}
	/*. bool .*/ function dump_debug_info(){}
	/*. string .*/ function get_client_info(){}
	/*. string .*/ function get_host_info(){}
	/*. string .*/ function get_server_info(){}
	/*. int .*/ function get_server_version(){}
	/*. mysqli .*/ function init(){}
	/*. string .*/ function info(){}
	/*. bool .*/ function kill(/*. int .*/ $processid){}
	/*. bool .*/ function multi_query(/*. string .*/ $query){}
	/*. bool .*/ function more_results(){}
	/*. bool .*/ function next_result(/*. mysqli .*/ $link){}
	/*. bool .*/ function options(/*. int .*/ $flags, /*. mixed .*/ $values){}
	/*. bool .*/ function ping(){}
	/*. mysqli_stmt .*/ function prepare(/*. string .*/ $query){}
	/*. mixed .*/ function query(/*. string .*/ $query /*., args .*/){}
	/*. bool .*/ function real_connect(/*. args .*/){}
	/*. string .*/ function real_escape_string(/*. string .*/ $escapestr){}
	/*. bool .*/ function rollback(){}
	/*. bool .*/ function select_db(/*. string .*/ $dbname){}
	/*. bool .*/ function set_charset(/*. string .*/ $charset){}
	/*. bool .*/ function ssl_set(/*. string .*/ $key ,/*. string .*/ $cert ,/*. string .*/ $ca ,/*. string .*/ $capath ,/*. string .*/ $cipher){}
	/*. string .*/ function stat(){}
	/*. mysqli_stmt .*/ function stmt_init(){}
	/*. mysqli_result .*/ function store_result(){}
	/*. bool .*/ function thread_safe(){}
	/*. mysqli_result .*/ function use_result(){}
}


/*. else .*/

class mysqli_field {
	public /*. string .*/ $name;
	public /*. string .*/ $orgname;
	public /*. string .*/ $table;
	public /*. string .*/ $orgtable;
	public /*. string .*/ $def;
	public /*. int .*/    $max_length = 0;  # dummy initial value
	public /*. int .*/    $length = 0;  # dummy initial value
	public /*. int .*/    $charsetnr = 0;  # dummy initial value
	public /*. int .*/    $flags = 0;  # dummy initial value
	public /*. string .*/ $type;
	public /*. int .*/    $decimals = 0;  # dummy initial value
}


/*. require_module 'spl'; .*/


class mysqli_result implements Iterator {

	public /*. int .*/    $current_field = 0;  # dummy initial value
	public /*. int .*/    $field_count = 0;  # dummy initial value
	public /*. array[int]int .*/ $lengths;
	public /*. int .*/    $num_rows = 0;  # dummy initial value
	public /*. int .*/ $type = 0;  # dummy initial value

	/*. void .*/ function close(){}
	/*. bool .*/ function data_seek(/*. int .*/ $offset){}
	/*. array[string]string .*/ function fetch_array(/*. args .*/){}
	/*. array[]string .*/ function fetch_assoc(){}
	/*. mysqli_field .*/ function fetch_field(){}
	/*. array[int]mysqli_field .*/ function fetch_fields(){}
	/*. mysqli_field .*/ function fetch_field_direct(/*. int .*/ $offset){}
	/*. object .*/ function fetch_object(){}
	/*. array[int]string .*/ function fetch_row(){}
	/*. int .*/ function field_seek(/*. int .*/ $fieldnr){}
	/*. void .*/ function free_result(){}
	/*. array .*/ function fetch_all(/*. args .*/){}

	# Implements Iterator:
	/*. void .*/ function rewind(){}
	/*. bool .*/ function valid(){}
	/*. mixed .*/ function key(){}
	/*. mysqli_result .*/ function current(){}
	/*. void .*/ function next(){}
}


class mysqli_stmt {

	public /*. int .*/     $affected_rows = 0;  # dummy initial value
	public /*. int .*/     $errno = 0;  # dummy initial value
	public /*. mixed[string] .*/ $error_list;
	public /*. string .*/  $error;
	public /*. int .*/     $field_count = 0;  # dummy initial value
	public /*. int .*/     $insert_id = 0;  # dummy initial value
	public /*. int .*/     $num_rows = 0;  # dummy initial value
	public /*. int .*/     $param_count = 0;  # dummy initial value
	public /*. string .*/  $sqlstate;

	/*. int .*/ function attr_get(/*. int .*/ $attr){}
	/*. bool .*/ function attr_set(/*. int .*/ $attr, /*. int .*/ $mode){}
	/*. bool .*/ function bind_param(/*. string .*/ $types, /*. mixed .*/ $variable /*., args .*/){}
	/*. bool .*/ function bind_result(/*. mixed .*/ $var_ /*., args .*/){}
	/*. bool .*/ function close(){}
	/*. void .*/ function data_seek(/*. int .*/ $offset){}
	/*. bool .*/ function execute(){}
	/*. bool .*/ function fetch(){}
	/*. void .*/ function free_result(){}
	/*. mysqli_result .*/ function get_result(){}
	/*. bool .*/ function prepare(/*. string .*/ $query){}
	/*. bool .*/ function reset(){}
	/*. mysqli_result .*/ function result_metadata(){}
	/*. bool .*/ function send_long_data(/*. int .*/ $param_nr, /*. string .*/ $data){}
	/*. bool .*/ function store_result(){}
}


class mysqli_warning {
	public /*. string .*/ $message;
	public /*. string .*/ $sqlstate;
	public $errno = 0;
	public /*. void .*/ function next (){} /* what's this? */
}



class mysqli {

	public /*. int .*/    $affected_rows = 0;  # dummy initial value
	public /*. string .*/ $client_info;
	public /*. int .*/    $client_version = 0;  # dummy initial value
	public /*. string .*/ $connect_errno;
	public /*. string .*/ $connect_error;
	public /*. int .*/    $errno = 0;  # dummy initial value
	public /*. mixed[string] .*/ $error_list;
	public /*. string .*/ $error;
	public /*. int .*/    $field_count = 0;  # dummy initial value
	public /*. string .*/ $host_info;
	public /*. string .*/ $info;
	public /*. int .*/    $insert_id = 0;  # dummy initial value
	public /*. string .*/ $protocol_version;
	public /*. string .*/ $server_info;
	public /*. int .*/    $server_version = 0;  # dummy initial value
	public /*. string .*/ $sqlstate;
	public /*. int .*/    $thread_id = 0;  # dummy initial value
	public /*. int .*/    $warning_count = 0;  # dummy initial value

	/*. void .*/ function __construct(
		$host = "ini_get('mysqli.default_host')",
		$username = "ini_get('mysqli.default_user')",
		$passwd = "ini_get('mysqli.default_pw')",
		$dbname = "",
		$port = 0 /* ini_get("mysqli.default_port") */,
		$socket = "ini_get('mysqli.default_socket')"){}
	/*. bool .*/ function autocommit(/*. bool .*/ $mode){}
	/*. bool .*/ function change_user(/*. string .*/ $user, /*. string .*/ $password, /*. string .*/ $database){}
	/*. string .*/ function character_set_name(){}
	/*. bool .*/ function close(){}
	/*. bool .*/ function commit(){}
	/*. void .*/ function debug(/*. string .*/ $debug){}
	/*. bool .*/ function dump_debug_info(){}
	/*. object .*/ function get_charset(){}
	/*. string .*/ function get_client_info(){}
	/*. mysqli_warning .*/ function get_warnings(){}
	/*. string .*/ function get_host_info(){}
	/*. string .*/ function get_server_info(){}
	/*. int .*/ function get_server_version(){}
	/*. mysqli .*/ function init(){}
	/*. string .*/ function info(){}
	/*. bool .*/ function kill(/*. int .*/ $processid){}
	/*. bool .*/ function multi_query(/*. string .*/ $query){}
	/*. bool .*/ function more_results(){}
	/*. bool .*/ function next_result(/*. mysqli .*/ $link){}
	/*. bool .*/ function options(/*. int .*/ $flags, /*. mixed .*/ $values){}
	/*. bool .*/ function ping(){}
	/*. mysqli_stmt .*/ function prepare(/*. string .*/ $query){}
	/*. mixed .*/ function query(/*. string .*/ $query /*., args .*/){}
	/*. bool .*/ function real_connect(/*. args .*/){}
	/*. string .*/ function escape_string(/*. string .*/ $escapestr){}
	/*. bool .*/ function real_query(/*. string .*/ $query){}
	/*. mysqli_result .*/ function reap_async_query(){}
	/*. bool .*/ function refresh(/*. int .*/ $options){}
	/*. bool .*/ function rollback(){}
	/*. int .*/ function rpl_query_type(/*. string .*/ $query){}
	/*. bool .*/ function select_db(/*. string .*/ $dbname){}
	/*. bool .*/ function send_query(/*. string .*/ $query){}
	/*. bool .*/ function set_charset(/*. string .*/ $charset){}
	/*. bool .*/ function ssl_set(/*. string .*/ $key ,/*. string .*/ $cert ,/*. string .*/ $ca ,/*. string .*/ $capath ,/*. string .*/ $cipher){}
	/*. string .*/ function stat(){}
	/*. mysqli_stmt .*/ function stmt_init(){}
	/*. mysqli_result .*/ function store_result(){}
	/*. bool .*/ function thread_safe(){}
	/*. mysqli_result .*/ function use_result(){}
	/*. array[string]int .*/ function get_connection_stats(){}
}

/*. end_if_php_ver .*/


/*. mysqli .*/ function mysqli_connect( /*. args .*/){}
/*. mysqli .*/ function mysqli_embedded_connect(){}
/*. int .*/ function mysqli_connect_errno(){}
/*. string .*/ function mysqli_connect_error(){}
/*. array[]string .*/ function mysqli_fetch_array(/*. mysqli_result .*/ $result /*., args .*/){}
/*. array[string]string .*/ function mysqli_fetch_assoc(/*. mysqli_result .*/ $result){}
/*. object .*/ function mysqli_fetch_object(/*. mysqli_result .*/ $result){}
/*. bool .*/ function mysqli_multi_query(/*. mysqli .*/ $link, /*. string .*/ $query){}
/*. mixed .*/ function mysqli_query(/*. mysqli .*/ $link, /*. string .*/ $query /*., args .*/){}
/*. int .*/ function mysqli_affected_rows(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_autocommit(/*. mysqli .*/ $link, /*. bool .*/ $mode){}
/*. int .*/ function mysqli_stmt_attr_get(/*. mysqli_stmt .*/ $stmt, /*. int .*/ $attr){}
/*. bool .*/ function mysqli_stmt_attr_set(/*. mysqli_stmt .*/ $stmt, /*. int .*/ $attr, /*. int .*/ $mode){}
/*. mysqli_result .*/ function mysqli_stmt_get_result(/*. mysqli_stmt .*/ $stmt){}
/*. bool .*/ function mysqli_stmt_bind_param(/*. mysqli_stmt .*/ $stmt, /*. string .*/ $types, /*. mixed .*/ $variable /*., args .*/){}
/*. bool .*/ function mysqli_stmt_bind_result(/*. mysqli_stmt .*/ $stmt, /*. mixed .*/ $var_ /*., args .*/){}
/*. bool .*/ function mysqli_change_user(/*. mysqli .*/ $link, /*. string .*/ $user, /*. string .*/ $password, /*. string .*/ $database){}
/*. string .*/ function mysqli_character_set_name(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_close(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_commit(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_data_seek(/*. mysqli_result .*/ $result, /*. int .*/ $offset){}
/*. void .*/ function mysqli_debug(/*. string .*/ $debug){}
/*. bool .*/ function mysqli_dump_debug_info(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_errno(/*. mysqli .*/ $link){}
/*. string .*/ function mysqli_error(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_stmt_execute(/*. mysqli_stmt .*/ $stmt){}
/*. mixed .*/ function mysqli_stmt_fetch(/*. mysqli_stmt .*/ $stmt){}
/*. mysqli_field .*/ function mysqli_fetch_field(/*. mysqli_result .*/ $result){}
/*. array[int]mysqli_field .*/ function mysqli_fetch_fields(/*. mysqli_result .*/ $result){}
/*. mysqli_field .*/ function mysqli_fetch_field_direct(/*. mysqli_result .*/ $result, /*. int .*/ $offset)/*. triggers E_WARNING .*/{}
/*. array[int]int .*/ function mysqli_fetch_lengths(/*. mysqli_result .*/ $result){}
/*. array .*/ function mysqli_fetch_row(/*. mysqli_result .*/ $result){}
/*. int .*/ function mysqli_field_count(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_field_seek(/*. mysqli_result .*/ $result, /*. int .*/ $fieldnr){}
/*. int .*/ function mysqli_field_tell(/*. mysqli_result .*/ $result){}
/*. void .*/ function mysqli_free_result(/*. mysqli_result .*/ $result){}
/*. string .*/ function mysqli_get_client_info(){}
/*. int .*/ function mysqli_get_client_version(){}
/*. string .*/ function mysqli_get_host_info(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_get_proto_info(/*. mysqli .*/ $link){}
/*. string .*/ function mysqli_get_server_info(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_get_server_version(/*. mysqli .*/ $link){}
/*. string .*/ function mysqli_info(/*. mysqli .*/ $link){}
/*. resource .*/ function mysqli_init(){}
/*. int .*/ function mysqli_insert_id(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_kill(/*. mysqli .*/ $link, /*. int .*/ $processid){}
/*. bool .*/ function mysqli_set_local_infile_handler(/*. mysqli .*/ $link, /*. mixed .*/ $read_func){}
/*. bool .*/ function mysqli_more_results(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_next_result(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_num_fields(/*. mysqli_result .*/ $result){}
/*. int .*/ function mysqli_num_rows(/*. mysqli_result .*/ $result){}
/*. bool .*/ function mysqli_options(/*. mysqli .*/ $link, /*. int .*/ $flags, /*. mixed .*/ $values){}
/*. bool .*/ function mysqli_ping(/*. mysqli .*/ $link){}
/*. mysqli_stmt .*/ function mysqli_prepare(/*. mysqli .*/ $link, /*. string .*/ $query){}
/*. bool .*/ function mysqli_real_connect(/*. mysqli .*/ $link /*., args .*/){}
/*. bool .*/ function mysqli_real_query(/*. mysqli .*/ $link, /*. string .*/ $query){}
/*. string .*/ function mysqli_real_escape_string(/*. mysqli .*/ $link, /*. string .*/ $escapestr){}
/*. bool .*/ function mysqli_rollback(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_server_init(){}
/*. void .*/ function mysqli_server_end(){}
/*. int .*/ function mysqli_stmt_affected_rows(/*. mysqli_stmt .*/ $stmt){}
/*. bool .*/ function mysqli_stmt_close(/*. mysqli_stmt .*/ $stmt){}
/*. void .*/ function mysqli_stmt_data_seek(/*. mysqli_stmt .*/ $stmt, /*. int .*/ $offset){}
/*. int .*/ function mysqli_stmt_field_count(/*. mysqli_stmt .*/ $stmt){}
/*. void .*/ function mysqli_stmt_free_result(/*. mysqli_stmt .*/ $stmt){}
/*. int .*/ function mysqli_stmt_insert_id(/*. mysqli_stmt .*/ $stmt){}
/*. int .*/ function mysqli_stmt_param_count(/*. mysqli_stmt .*/ $stmt){}
/*. bool .*/ function mysqli_stmt_reset(/*. mysqli_stmt .*/ $stmt){}
/*. int .*/ function mysqli_stmt_num_rows(/*. mysqli_stmt .*/ $stmt){}
/*. bool .*/ function mysqli_select_db(/*. mysqli .*/ $link, /*. string .*/ $dbname){}
/*. string .*/ function mysqli_sqlstate(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_ssl_set(/*. mysqli .*/ $link ,/*. string .*/ $key ,/*. string .*/ $cert ,/*. string .*/ $ca ,/*. string .*/ $capath ,/*. string .*/ $cipher){}
/*. string .*/ function mysqli_stat(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_stmt_errno(/*. mysqli_stmt .*/ $stmt){}
/*. string .*/ function mysqli_stmt_error(/*. mysqli_stmt .*/ $stmt){}
/*. mysqli_stmt .*/ function mysqli_stmt_init(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_stmt_prepare(/*. mysqli_stmt .*/ $stmt, /*. string .*/ $query){}
/*. mysqli_stmt .*/ function mysqli_stmt_result_metadata(/*. mysqli_stmt .*/ $stmt){}
/*. bool .*/ function mysqli_stmt_store_result(/*. mysqli_stmt .*/ $stmt){}
/*. string .*/ function mysqli_stmt_sqlstate(/*. mysqli_stmt .*/ $stmt){}
/*. mysqli_result .*/ function mysqli_store_result(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_thread_id(/*. mysqli .*/ $link){}
/*. bool .*/ function mysqli_thread_safe(){}
/*. mysqli_result .*/ function mysqli_use_result(/*. mysqli .*/ $link){}
/*. int .*/ function mysqli_warning_count(/*. mysqli .*/ $link){}
/** @deprecated Removed. */ 
/*. void .*/ function mysqli_disable_reads_from_master(/*. mysqli .*/ $link){}
/** @deprecated Removed. */ 
/*. void .*/ function mysqli_disable_rpl_parse(/*. mysqli .*/ $link){}
/** @deprecated Removed. */ 
/*. void .*/ function mysqli_enable_reads_from_master(/*. mysqli .*/ $link){}
/** @deprecated Removed. */ 
/*. void .*/ function mysqli_enable_rpl_parse(/*. mysqli .*/ $link){}
/** @deprecated Removed. */ 
/*. bool .*/ function mysqli_master_query(/*. mysqli .*/ $link, /*. string .*/ $query){}
/** @deprecated Removed. */ 
/*. int .*/ function mysqli_rpl_parse_enabled(/*. mysqli .*/ $link){}
/** @deprecated Removed. */ 
/*. bool .*/ function mysqli_rpl_probe(/*. mysqli .*/ $link){}
/** @deprecated Removed. */ 
/*. int .*/ function mysqli_rpl_query_type(/*. string .*/ $query){}
/** @deprecated Removed. */ 
/*. bool .*/ function mysqli_send_query(/*. mysqli .*/ $link, /*. string .*/ $query){}
/** @deprecated Removed. */ 
/*. bool .*/ function mysqli_slave_query(/*. mysqli .*/ $link, /*. string .*/ $query){}
/*. bool .*/ function mysqli_set_charset (/*. mysqli .*/ $link, /*. string .*/ $charset){}
/*. array .*/ function mysqli_fetch_all(/*. mysqli_result .*/ $result /*., args .*/){}
/*. array[string]int .*/ function mysqli_get_connection_stats(/*. mysqli .*/ $link){}
