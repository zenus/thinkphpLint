<?php

/*.
	require_module 'standard';
	require_module 'pgsql';
.*/

namespace it\icosaedro\sql\postgresql;

require_once __DIR__ . '/../../../../all.php';

use it\icosaedro\sql\SQLException;
use ErrorException;
use RuntimeException;


/**
	PostgreSQL specific implementation of the result set.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/23 18:12:44 $
*/
class ResultSet extends \it\icosaedro\sql\ResultSet {

	private /*. resource .*/ $res;

	private /*. int .*/ $row_count = 0;

	private /*. int .*/ $curr_row_index = -1;

	private /*. array[int]string .*/ $curr_row;


	/**
		Constructor invoked from the driver. Should never be called
		from user's code.
		@param resource $res PostrgreSQL result set.
		@return void
	*/
	function __construct($res)
	{
		$this->res = $res;
		$this->row_count = pg_num_rows($res);
	}


	/*. int .*/ function getRowCount()
	{
		return $this->row_count;
	}


	/*. void .*/ function moveToRow(/*. int .*/ $row_index)
		/*. throws SQLException .*/
	{
		if( $row_index === $this->curr_row_index )
			return;
		if( $row_index < -1 or $row_index >= $this->row_count )
			throw new SQLException("row index out of the range: $row_index");
		$this->curr_row_index = $row_index;
		if( $row_index < 0 )
			$this->curr_row = NULL;
		else
			$this->curr_row = cast("array[int]string",
				pg_fetch_row($this->res, $row_index));
	}


	/*. int .*/ function getColumnCount()
	{
		return pg_num_fields($this->res);
	}


	/*. string .*/ function getColumnName(/*. int .*/ $column_index)
		/*. throws SQLException .*/
	{
		try {
			return pg_field_name($this->res, $column_index);
		}
		catch(ErrorException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/*. bool .*/ function nextRow()
	{
		if( $this->curr_row_index + 1 >= $this->row_count ){
			return FALSE;
		} else {
			$this->curr_row_index++;
			$this->curr_row = cast("array[int]string",
				pg_fetch_row($this->res, $this->curr_row_index));
			return TRUE;
		}
			
	}


	/*. string .*/ function getStringByIndex(/*. int .*/ $column_index)
		/*. throws SQLException .*/
	{
		if( $this->curr_row === NULL )
			throw new SQLException("row not selected");
		if( $column_index < 0 or $column_index >= pg_num_fields($this->res) )
			throw new SQLException("column index out of the range: $column_index");
		$v = $this->curr_row[$column_index];
		$this->was_null = $v === NULL;
		return $v;
	}


	/*. string .*/ function getStringByName(/*. string .*/ $column_name)
		/*. throws SQLException .*/
	{
		if( $this->curr_row === NULL )
			throw new SQLException("row not selected");
		$column_index = pg_field_num($this->res, $column_name);
		if( $column_index >= 0 )
			$v = $this->curr_row[$column_index];
		else
			throw new SQLException("no this field: $column_name");
		$this->was_null = $v === NULL;
		return $v;
	}


	private /*. string .*/ function decodeBytea(/*. string .*/ $v)
	{
		if( $v === NULL )
			return NULL;
		else if( strlen($v) > 2 and substr($v, 0, 2) === "\\x" ){
			// hex encoding (the default in PG >= 9)
			try {
				return hex2bin( substr($v, 2) );
			}
			catch(ErrorException $e){
				throw new RuntimeException($e->getMessage());
			}
		} else {
			// oct encoding (the only available one in pg <= 8)
			try {
				return pg_unescape_bytea($v);
			}
			catch(ErrorException $e){
				throw new RuntimeException($e->getMessage());
			}
		}
	}
	

	/**
		Retrieves a field of type binary (for example, an image).
		This implementation is specific of PostgreSQL and assumes a field
		of type BYTEA.
		@param int $column_index  Index of the column, starting from 0.
		@return string Value of the field, possibly containing arbitrary
		sequences of bytes. Returns PHP NULL for SQL NULL. 
		@throws SQLException  Failed to retrieve data from SQL server.
		Invalid Base64 encoding. 
	*/
	function getBytesByIndex($column_index)
	{
		$v = $this->getStringByIndex($column_index);
		return $this->decodeBytea($v);
	}


	/**
		Retrieves a field of type binary (for example, an image).
		This implementation is specific of PostgreSQL and assumes a field
		of type BYTEA.
		@param string $column_name  Name of the column.
		@return string Value of the field, possibly containing arbitrary
		sequences of bytes. Returns PHP NULL for SQL NULL. 
		@throws SQLException  Failed to retrieve data from SQL server.
		Invalid Base64 encoding. 
	*/
	function getBytesByName($column_name)
	{
		$v = $this->getStringByName($column_name);
		return $this->decodeBytea($v);
	}


	/*. void .*/ function close()
	{
		if( $this->res !== NULL ){
			pg_free_result($this->res);
			$this->res = NULL;
		}
	}

}
