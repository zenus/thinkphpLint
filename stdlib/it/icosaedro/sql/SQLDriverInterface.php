<?php

namespace it\icosaedro\sql;

require_once __DIR__ . "/../../../all.php";

use it\icosaedro\sql\SQLException;
use it\icosaedro\sql\PreparedStatement;

/**
	Minimalistic, generic SQL driver interface. Every implementation should
	specify how strings of characters are encoded (for example, UTF-8) and then
	all the strings that appear in this class and its implementations are
	assumed to work with that same encoding. Example:

	<pre>
	$db = new \it\icosaedro\sql\mysql\Driver( array("localhost", "myname", "mypass", "test") );
	$rs = $db-&gt;query("select current_date");
	$rs-&gt;moveToRow(0);
	echo "Date: ", $rs-&gt;getDateByIndex(0), "\n";
	</pre>

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/23 18:12:05 $
*/
interface SQLDriverInterface {

	/**
		Escapes special characters in literal strings. Does not add
		quotes.
		@param string $str  The string to escape.
		@return string  The quoted string, suitable to be inserted in a
		SQL quoted string. Quotes are not added. The NULL string generates
		the empty string.
	*/
	function escape($str);

	/**
		Submits an SQL update command, typically UPDATE.
		@param string $cmd  The SQL command.
		@return int  Number of rows affected.
		@throws SQLException
	*/
	function update($cmd);

	/**
		Submits an SQL query command, typically SELECT.
		@param string $cmd  The SQL command.
		@return ResultSet  The table that results from the query.
		@throws SQLException
	*/
	function query($cmd);
	
	/**
		Generates a new SQL prepared statement.
		@param string $cmd  The SQL command. See comments about the
		class {@link it\icosaedro\sql\PreparedStatement} for further details.
		@return PreparedStatement  Use this object to set the parameters
		and to perform queries and updates.
	*/
	function prepareStatement($cmd);

	/**
		Closes the data base client connection. Once closed, this object
		cannot be reused.
		@return void
	*/
	function close();
}
