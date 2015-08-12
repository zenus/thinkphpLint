<?php

/*.  require_module 'pgsql'; .*/

namespace it\icosaedro\sql\postgresql;

require_once __DIR__ . '/../../../../all.php';

use it\icosaedro\sql\SQLException;
use ErrorException;


/**
	PostgreSQL specific implementation of the
	{@link \it\icosaedro\sql\SQLDriverInterface} interface.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/02/01 08:12:30 $
*/
class Driver implements \it\icosaedro\sql\SQLDriverInterface {

	private /*. resource .*/ $conn;


	/**
		Establishes a new client connection with a remote PostgreSQL 8/9 data
		base server.
		Also sets the date format to ISO.
		@param string $parameters Lists the connection parameters, for example:
		<code>"host=localhost port=5432 dbname=mary user=lamb
		password=foo"</code>.
		@param string $encoding  Character encoding for every string exchanged
		by this client connection and the remote server. The default is
		"UTF-8". All the functions of this library that accept or retrieve
		strings assume this encoding, unless otherwise specified.
		@return void
		@throws SQLException
	*/
	function __construct($parameters, $encoding = "UTF-8")
	{
		try {
			$this->conn = pg_connect($parameters);
		}
		catch(ErrorException $e){
			throw new SQLException($php_errormsg);
		}

		if( pg_set_client_encoding($this->conn, $encoding) != 0 )
			throw new SQLException(pg_last_error($this->conn));

		try {
			pg_query($this->conn, "set datestyle to iso");
		}
		catch(ErrorException $e){
			throw new SQLException(pg_last_error($this->conn));
		}
	}


	/*. string .*/ function escape(/*. string .*/ $str)
	{
		if( $str === NULL )
			$str = "";
		return pg_escape_string($this->conn, $str);
	}


	/*. int .*/ function update(/*. string .*/ $cmd)
		/*. throws SQLException .*/
	{
		try {
			$res = pg_query($this->conn, $cmd);
		}
		catch(ErrorException $e){
			throw new SQLException(pg_last_error($this->conn));
		}
		return pg_affected_rows($res);
	}


	/*. \it\icosaedro\sql\ResultSet .*/ function query(/*. string .*/ $cmd)
		/*. throws SQLException .*/
	{
		try {
			$res = pg_query($this->conn, $cmd);
		}
		catch(ErrorException $e){
			throw new SQLException($e->getMessage());
		}
		return new ResultSet($res);
	}

	
	/*. \it\icosaedro\sql\PreparedStatement .*/ function prepareStatement(/*. string .*/ $cmd)
	{
		return new PreparedStatement($this, $cmd);
	}


	/*. void .*/ function close()
	{
		if( $this->conn !== NULL ){
			pg_close($this->conn);
			$this->conn = NULL;
		}
	}

}
