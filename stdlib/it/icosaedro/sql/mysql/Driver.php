<?php

namespace it\icosaedro\sql\mysql;

require_once __DIR__ . "/../../../../all.php";

/*. require_module 'mysqli'; .*/

use it\icosaedro\sql\SQLDriverInterface;
use it\icosaedro\sql\SQLException;
use it\icosaedro\sql\mysql\ResultSet;
use it\icosaedro\sql\mysql\PreparedStatement;
use ErrorException;
use mysqli;


/**
	MySQL specific implementation of the {@link it\icosaedro\sql\SQLDriverInterface} Interface.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/23 18:12:20 $
*/
class Driver implements SQLDriverInterface {

	private /*. mysqli .*/ $conn;


	/**
		Establishes a new client connection with a remote MySQL 4/5 data
		base server.
		@param array[int]mixed $parameters Lists the connection parameters to
		be sent as arguments to the {@link mysqli::__construct()} method.
		@param string $encoding  Character encoding for every string exchanged
		by this client connection and the remote server. The default is
		"UTF-8". All the functions of this library that accept or retrieve
		strings assume this encoding, unless otherwise specified. Example:
		<pre>
		use it\icosaedro\sql\mysql\Driver;
		$db = new Driver( array("localhost", "theuser", "pass", "dbname") );
		</pre>
		@return void
		@throws SQLException
	*/
	function __construct($parameters, $encoding = "UTF8")
	{
		try {
			$conn = call_user_func_array("mysqli_connect", $parameters);
		}
		catch(ErrorException $e){
			throw new SQLException($e->getMessage() . ": " . mysqli_connect_error());
		}
		$this->conn = cast("mysqli", $conn);
		if( ! mysqli_set_charset($this->conn, $encoding) )
			throw new SQLException("mysqli_set_charset($encoding): unknown charset");
	}


	/*. string .*/ function escape(/*. string .*/ $str)
	{
		if( $str === NULL )
			$str = "";
		return mysqli_real_escape_string($this->conn, $str);
	}


	/*. int .*/ function update(/*. string .*/ $cmd)
		/*. throws SQLException .*/
	{
		$res = mysqli_query($this->conn, $cmd);
		if( $res === FALSE )
			throw new SQLException(mysqli_error($this->conn));
		return mysqli_affected_rows($this->conn);
	}


	/*. \it\icosaedro\sql\ResultSet .*/ function query(/*. string .*/ $cmd)
		/*. throws SQLException .*/
	{
		$res = mysqli_query($this->conn, $cmd);
		if( $res === FALSE )
			throw new SQLException(mysqli_error($this->conn));
		return new ResultSet(cast("mysqli_result", $res));
	}

	
	/*. \it\icosaedro\sql\PreparedStatement .*/ function prepareStatement(/*. string .*/ $cmd)
	{
		return new PreparedStatement($this, $cmd);
	}


	/*. void .*/ function close()
	{
		if( $this->conn !== NULL ){
			mysqli_close($this->conn);
			$this->conn = NULL;
		}
	}
}
