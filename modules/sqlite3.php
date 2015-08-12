<?php
/** SQLite3 Functions.

See: {@link http://www.php.net/manual/en/book.sqlite3.php}
@package sqlite3
*/

define('SQLITE3_ASSOC', 1);
define('SQLITE3_NUM', 2);
define('SQLITE3_BOTH', 3);
define('SQLITE3_INTEGER', 1);
define('SQLITE3_FLOAT', 2);
define('SQLITE3_TEXT', 3);
define('SQLITE3_BLOB', 4);
define('SQLITE3_NULL', 5);
define('SQLITE3_OPEN_READONLY', 1);
define('SQLITE3_OPEN_READWRITE', 2);
define('SQLITE3_OPEN_CREATE', 4);

class SQLite3Result
{
    /*. string .*/ function columnName(/*. integer .*/ $column_number){}
    /*. integer .*/ function columnType(/*. integer .*/ $column_number){}
    /*. array .*/ function fetchArray(/*. integer .*/ $mode = 3){}
    /*. boolean .*/ function finalize(){}
    /*. integer .*/ function numColumns(){}
    /*. boolean .*/ function reset(){}
}

class SQLite3Stmt
{
    /*. boolean .*/ function bindParam(/*. string .*/ $sql_param, /*. mixed .*/ $param, /*. integer .*/ $type = 0){}
    /*. boolean .*/ function bindValue(/*. string .*/ $sql_param, /*. mixed .*/ $value, /*. integer .*/ $type = 0){}
    /*. boolean .*/ function clear(){}
    /*. boolean .*/ function close(){}
    /*. SQLite3Result .*/ function execute(){}
    /*. integer .*/ function paramCount(){}
    /*. boolean .*/ function reset(){}
}

class SQLite3
{
    /*. boolean .*/ function busyTimeout(/*. integer .*/ $msecs){}
    /*. integer .*/ function changes(){}
    /*. boolean .*/ function close(){}
    /*. void .*/ function __construct(/*. string .*/ $filename, /*. integer .*/ $flags = 6, /*. string .*/ $encryption_key = ''){}
    /*. boolean .*/ function createAggregate(/*. string .*/ $name, /*. mixed .*/ $step_callback, /*. mixed .*/ $final_callback, /*. integer .*/ $argument_count = -1){}
    /*. boolean .*/ function createFunction(/*. string .*/ $name, /*. mixed .*/ $callback, /*. integer .*/ $argument_count = -1){}
    /*. string .*/ function escapeString(/*. string .*/ $value){}
    /*. boolean .*/ function exec(/*. string .*/ $query){}
    /*. integer .*/ function lastErrorCode(){}
    /*. string .*/ function lastErrorMsg(){}
    /*. integer .*/ function lastInsertRowID(){}
    /*. boolean .*/ function loadExtension(/*. string .*/ $shared_library){}
    /*. boolean .*/ function open(/*. string .*/ $filename, /*. integer .*/ $flags = 6, /*. string .*/ $encryption_key = ''){}
    /*. SQLite3Stmt .*/ function prepare(/*. string .*/ $query){}
    /*. SQLite3Result .*/ function query(/*. string .*/ $query){}
    /*. mixed .*/ function querySingle(/*. string .*/ $query, /*. boolean .*/ $entire_row = false){}
    /*. array .*/ function version(){}
}
