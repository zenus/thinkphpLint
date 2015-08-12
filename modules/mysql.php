<?php
/** MySQL Functions.


See: {@link http://www.php.net/manual/en/ref.mysql.php}
@package mysql
*/

# Required for E_WARNING:
/*. require_module 'standard'; .*/


# FIXME: dummy values
define('MYSQL_ASSOC', 1);
define('MYSQL_NUM', 1);
define('MYSQL_BOTH', 1);
define('MYSQL_CLIENT_COMPRESS', 1);
define('MYSQL_CLIENT_SSL', 1);
define('MYSQL_CLIENT_INTERACTIVE', 1);
define('MYSQL_CLIENT_IGNORE_SPACE', 1);

/*. int    .*/ function mysql_affected_rows( /*. args .*/)
/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_client_encoding(/*. args .*/)
/*. triggers E_WARNING .*/{}
/*. bool   .*/ function mysql_close(/*. args .*/)
/*. triggers E_WARNING .*/{}
/*. resource .*/ function mysql_connect(/*. args .*/)
/*. triggers E_WARNING .*/{}

/** @deprecated
 * It is preferable to use {@link mysql_query()} to issue a
 * sql CREATE DATABASE statement instead.
 */
/*. bool   .*/ function mysql_create_db(/*. string .*/ $database_name /*., args .*/){}

/*. bool   .*/ function mysql_data_seek(/*. resource .*/ $result, /*. int .*/ $row_number)
/*. triggers E_WARNING .*/{}

/*. if_php_ver_4 .*/
	/*. resource .*/ function mysql_db_query(/*.string.*/ $db, /*.string.*/ $query /*., args .*/){}
/*. end_if_php_ver .*/

/*. if_php_ver_5 .*/
	/** @deprecated Use {@link mysql_select_db()} and {@link mysql_query()}
		instead.
		Be aware that this function does NOT switch back to the database you
		were connected before. In other words, you can't use this function
		to temporarily run a sql query on another database, you would have
		to manually switch back. Users are strongly encouraged to use the
		database.table syntax in their sql queries or {@link mysql_select_db()}
		instead of this function.
	*/
	/*. resource .*/ function mysql_db_query(/*.string.*/ $db, /*.string.*/ $query /*., args .*/)/*. triggers E_DEPRECATED .*/{}
/*. end_if_php_ver .*/


/** @deprecated It is preferable to use {@link mysql_query()} to issue a
 * sql DROP DATABASE statement instead.
 */
/*. bool   .*/ function mysql_drop_db(/*. string .*/ $database_name /*., args .*/)
/*. triggers E_WARNING .*/{}

/*. int    .*/ function mysql_errno(/*. args .*/){}
/*. string .*/ function mysql_error(/*. args .*/){}

/*. if_php_ver_4 .*/
/*. string .*/ function mysql_escape_string(/*.string.*/ $s){}
/*. end_if_php_ver .*/

/*. if_php_ver_5 .*/
/** @deprecated Use {@link mysql_real_escape_string()}. */
/*. string .*/ function mysql_escape_string(/*.string.*/ $s)
/*. triggers E_DEPRECATED .*/{}
/*. end_if_php_ver .*/

/*. array[]string .*/ function mysql_fetch_array(
	/*. resource .*/ $res,
	$result_type = MYSQL_BOTH
){}

/*. array[string]string .*/ function mysql_fetch_assoc(/*. resource .*/ $res){}

/*. if_php_ver_4 .*/

	/** Info about a field.
		Actually this class is anonymous, but PHPLint requires a name.
	*/
	class mysqlFieldDataDummyClass
	{
		var /*. string .*/ $name;
		var /*. string .*/ $table;
		var /*. string .*/ $def;
		var /*. int .*/ $max_length = 0; # initial dummy value
		var /*. int .*/ $not_null = 0; # initial dummy value
		var /*. int .*/ $primary_key = 0; # initial dummy value
		var /*. int .*/ $multiple_key = 0; # initial dummy value
		var /*. int .*/ $unique_key = 0; # initial dummy value
		var /*. int .*/ $numeric = 0; # initial dummy value
		var /*. int .*/ $blob = 0; # initial dummy value
		var /*. string .*/ $type;
		var /*. int .*/ $unsigned = 0; # initial dummy value
		var /*. int .*/ $zerofill = 0; # initial dummy value
	}

/*. else .*/


	/** Info about a field.
		Actually this class is anonymous, but PHPLint requires a name.
	*/
	class mysqlFieldDataDummyClass
	{
		public /*. string .*/ $name;
		public /*. string .*/ $table;
		public /*. string .*/ $def;
		public /*. int .*/ $max_length = 0; # initial dummy value
		public /*. int .*/ $not_null = 0; # initial dummy value
		public /*. int .*/ $primary_key = 0; # initial dummy value
		public /*. int .*/ $multiple_key = 0; # initial dummy value
		public /*. int .*/ $unique_key = 0; # initial dummy value
		public /*. int .*/ $numeric = 0; # initial dummy value
		public /*. int .*/ $blob = 0; # initial dummy value
		public /*. string .*/ $type;
		public /*. int .*/ $unsigned = 0; # initial dummy value
		public /*. int .*/ $zerofill = 0; # initial dummy value
	}

/*. end_if_php_ver .*/

/*. mysqlFieldDataDummyClass .*/ function mysql_fetch_field(/*. resource .*/ $res /*., args .*/){}

/*. array  .*/ function mysql_fetch_lengths(/*. resource .*/ $result){}
/*. mixed  .*/ function mysql_fetch_object(/*. resource .*/ $res /*. , args .*/){}
/*. array  .*/ function mysql_fetch_row(/*. resource .*/ $result){}
/*. string .*/ function mysql_field_flags(/*. resource .*/ $result, /*. int .*/ $field_offset)/*. triggers E_WARNING .*/{}
/*. int    .*/ function mysql_field_len(/*. resource .*/ $result, /*. int .*/ $field_offset)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_field_name(/*. resource .*/ $result, /*. int .*/ $field_index)/*. triggers E_WARNING .*/{}
/*. bool   .*/ function mysql_field_seek(/*. resource .*/ $result, /*. int .*/ $field_offset)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_field_table(/*. resource .*/ $result, /*. int .*/ $field_offset)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_field_type(/*. resource .*/ $result, /*. int .*/ $field_offset)/*. triggers E_WARNING .*/{}
/*. bool   .*/ function mysql_free_result(/*. resource .*/ $result)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_get_client_info(){}
/*. string .*/ function mysql_get_host_info( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. int    .*/ function mysql_get_proto_info( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_get_server_info( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_info( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. int    .*/ function mysql_insert_id( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. resource .*/ function mysql_list_dbs( /*. args .*/)/*. triggers E_WARNING .*/{}

/** @deprecated It is preferable to use {@link mysql_query()} to issue a
	SQL SHOW COLUMNS FROM table [LIKE 'name'] statement instead.
*/
/*. resource .*/ function mysql_list_fields(/*. string .*/ $database_name, /*. string .*/ $table_name /*., args .*/)
/*. triggers E_WARNING .*/{}

/*. resource .*/ function mysql_list_processes( /*. args .*/)/*. triggers E_WARNING .*/{}

/** @deprecated It is preferable to use {@link mysql_query()} to issue a
	SQL SHOW TABLES [FROM db_name] [LIKE 'pattern'] statement instead.
*/
/*. resource .*/ function mysql_list_tables(/*. string .*/ $database_name /*., args .*/)
/*. triggers E_WARNING .*/{}

/*. int    .*/ function mysql_num_fields(/*. resource .*/ $result){}
/*. int    .*/ function mysql_num_rows(/*. resource .*/ $res){}
/*. resource .*/ function mysql_pconnect( /*. args .*/){}
/*. bool   .*/ function mysql_ping( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. resource .*/ function mysql_query(/*.string.*/ $query /*., args .*/)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_real_escape_string(/*. string .*/ $to_be_escaped /*., args .*/){}
/*. mixed  .*/ function mysql_result(/*. resource .*/ $result, /*. int .*/ $row /*., args .*/){}
/*. bool   .*/ function mysql_select_db(/*.string.*/ $db /*., args .*/)/*. triggers E_WARNING .*/{}
/*. string .*/ function mysql_stat( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. int    .*/ function mysql_thread_id( /*. args .*/)/*. triggers E_WARNING .*/{}
/*. resource .*/ function mysql_unbuffered_query(/*. string .*/ $query /*., args .*/)/*. triggers E_WARNING .*/{}

/** @deprecated This function does no longer exists in PHP. */
/*. int    .*/ function mysql_change_user(/*. string .*/ $user, /*. string .*/ $password /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. bool   .*/ function mysql_set_charset(/*. string .*/ $charset /*. , args .*/){}
