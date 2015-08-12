<?php

namespace it\icosaedro\sql;

/*. require_module 'spl'; .*/

require_once __DIR__ . '/../../../all.php';

use it\icosaedro\containers\Printable;
use it\icosaedro\bignumbers\BigInt;
use it\icosaedro\bignumbers\BigFloat;
use it\icosaedro\utils\Integers;
use it\icosaedro\utils\Date;


/**
	Table resulting from a SQL data base query. An object of this class allows
	to retrieve the result of a single SQL query, consisting in a table of
	values. The table is organized as a matrix of rows and columns. Every
	column may have a name, and there may be 0 or more rows of data.

	The encoding of the strings of characters submitted to, or retrieved from
	these functions are intended to be encoded as specified in the client
	connection that generated this result set. So, for example, if the client
	connection with the remote data base server uses UTF-8, then all the
	strings must use this same encoding. See the details about each specific
	implementation for more details.

	For simplicity, most of the methods return {@link it\icosaedro\sql\SQLException} for both
	actual errors related to the client/server communication and for invalid
	parameters submitted as well.

	"Getters" for fields values are available in two forms: getTypeByIndex()
	takes the index of the column, while getTypeByName() takes the name of the
	column.

	Example:
	<pre>
	$db = new it\icosaedro\sql\mysql\Driver(array(...));
	# Retrieves the result set from a query:
	$rs = $db-&gt;query("SELECT * FROM users");
	# Displays records, field names and values:
	echo $rs;
	</pre>

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:19:05 $
*/
abstract class ResultSet implements Printable {

	/** If the last field retrieved was NULL. Classes that implement this
	interface must set this flag for every field retrieved. */
	protected $was_null = FALSE;

	/**
		Returns the number of rows of data in the table.
		@return int Number of rows of data in the result.
	*/
	abstract function getRowCount();

	/**
		Moves the internal pointer to the specified row. Most of the functions
		of this class uses the selected row as the current row you are
		interested to. The initial position of the internal pointer is -1, that
		is no row selected and not value can be retrieved until a valid row is
		selected.
		@param int $row_index Index of the row to select, in the range from -1
		(no row selected) up to {@link self::getRowCount()}-1. The first
		row is the number 0.
		@return void
		@throws SQLException Invalid index. Failed to retrieve row from the
		remote data base server.
	*/
	abstract function moveToRow($row_index);

	/**
		Returns the number of columns. The value is defined also if the
		resulting number of rows is zero.
		@return int  Number of columns.
	*/
	abstract function getColumnCount();

	/**
		Returns the name of the specified column.
		@param int $column_index Index of the column, starting from zero.
		@return string Name of the column, typically the name of a field.
		Columns evaluated at runtime (for example "<code>SELECT 123</code>")
		may not have a defined name, so either a dummy name or an SQLException
		may be thrown.
		@throws SQLException Invalid column index. Failed to retrieve
		the column name from the remote server.
	*/
	abstract function getColumnName($column_index);
	
	/**
		Move internal pointer to the next row of data. Initially the internal
		pointer is set to -1, which means no row selected, so the first call to
		this function moves the pointer to the row number 0.
		@return bool If the internal pointer was successfully moved to the
		next row; false if already at the last available row.
	*/
	abstract function nextRow();

	/**
		Returns a field value from the current row as a string.
		@param int $column_index Index of the column, starting from 0.
		@return string Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException
	*/
	abstract function getStringByIndex($column_index);

	/**
		Returns a field value from the current row as a string.
		@param string $column_name Name of the column.
		@return string Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException
	*/
	abstract function getStringByName($column_name);
	

	private static /*. bool .*/ function stringToBool(/*. string .*/ $v)
		/*. throws SQLException .*/
	{
		if( $v === NULL )
			return FALSE;
		else if( $v === "0" or $v === "false" or $v === "f" or $v === "FALSE" or $v === "F" )
			return FALSE;
		else if( $v === "1" or $v === "true" or $v === "T" or $v === "TRUE" or $v === "T" )
			return TRUE;
		else
			throw new SQLException("invalid boolean value: $v");
	}


	/**
		Returns a field value from the current row as a boolean.
		@param int $column_index Index of the column, starting from 0.
		@return bool Value of the field. Returns PHP FALSE for SQL NULL:
		you may use the wasNull() method to check for this latter case.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the boolean value.
	*/
	function getBooleanByIndex($column_index)
	{
		$v = $this->getStringByIndex($column_index);
		return self::stringToBool($v);
	}


	/**
		Returns a field value from the current row as a boolean.
		@param string $column_name Name of the column.
		@return bool Value of the field. Returns PHP FALSE for SQL NULL:
		you may use the wasNull() method to check for this latter case.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the boolean value.
	*/
	function getBooleanByName($column_name)
	{
		$v = $this->getStringByName($column_name);
		return self::stringToBool($v);
	}
	

	/**
		Returns a field value from the current row as a int.
		@param int $column_index Index of the column, starting from 0.
		@return int Value of the field. Returns PHP 0 for SQL NULL:
		you may use the wasNull() method to check for this latter case.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the integer number, or value out of the allowed
		range for int numbers.
	*/
	function getIntByIndex($column_index)
	{
		$v = $this->getStringByIndex($column_index);
		if( $v === NULL )
			return 0;
		try {
			return Integers::parseInt($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a int.
		@param string $column_name Name of the column.
		@return int Value of the field. Returns PHP 0 for SQL NULL:
		you may use the wasNull() method to check for this latter case.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the integer number, or value out of the allowed
		range for int numbers.
	*/
	function getIntByName($column_name)
	{
		$v = $this->getStringByName($column_name);
		if( $v === NULL )
			return 0;
		try {
			return Integers::parseInt($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a BigInt.
		@param int $column_index Index of the column, starting from 0.
		@return BigInt Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the big integer number.
	*/
	function getBigIntByIndex($column_index)
	{
		$v = $this->getStringByIndex($column_index);
		if( $v === NULL )
			return NULL;
		try {
			return new BigInt($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a BigInt.
		@param string $column_name Name of the column.
		@return BigInt Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the big integer number.
	*/
	function getBigIntByName($column_name)
	{
		$v = $this->getStringByName($column_name);
		if( $v === NULL )
			return NULL;
		try {
			return new BigInt($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a BigFloat.
		@param int $column_index Index of the column, starting from 0.
		@return BigFloat Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the big float number.
	*/
	function getBigFloatByIndex($column_index)
	{
		$v = $this->getStringByIndex($column_index);
		if( $v === NULL )
			return NULL;
		try {
			return new BigFloat($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a BigFloat.
		@param string $column_name Name of the column.
		@return BigFloat Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the big float number.
	*/
	function getBigFloatByName($column_name)
	{
		$v = $this->getStringByName($column_name);
		if( $v === NULL )
			return NULL;
		try {
			return new BigFloat($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a gregorian date.
		@param int $column_index Index of the column, starting from 0.
		@return Date Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the date.
	*/
	function getDateByIndex($column_index)
	{
		$v = $this->getStringByIndex($column_index);
		if( $v === NULL )
			return NULL;
		try {
			return Date::parse($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a gregorian date.
		@param string $column_name Name of the column.
		@return Date Value of the field. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid format of the date.
	*/
	function getDateByName($column_name)
	{
		$v = $this->getStringByName($column_name);
		if( $v === NULL )
			return NULL;
		try {
			return Date::parse($v);
		}
		catch(\InvalidArgumentException $e){
			throw new SQLException($e->getMessage());
		}
	}


	/**
		Returns a field value from the current row as a block of bytes.
		Assumes the data coming from the data base be Base64 encoded.
		@param int $column_index Index of the column, starting from 0.
		@return string Value of the field, possibly containing arbitrary
		sequences of bytes. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid Base64 encoding.
	*/
	function getBytesByIndex($column_index)
	{
		$v = $this->getStringByIndex($column_index);
		if( $v === NULL )
			return NULL;
		$decoded = base64_decode($v);
		if( $decoded === FALSE )
			throw new SQLException("invalid Base64 encoding: $v");
		return $decoded;
	}


	/**
		Returns a field value from the current row as a block of bytes.
		Assumes the data coming from the data base be Base64 encoded.
		@param string $column_name Name of the column.
		@return string Value of the field, possibly containing arbitrary
		sequences of bytes. Returns PHP NULL for SQL NULL.
		@throws SQLException Failed to retrieve data from SQL server.
		Invalid Base64 encoding.
	*/
	function getBytesByName($column_name)
	{
		$v = $this->getStringByName($column_name);
		if( $v === NULL )
			return NULL;
		else
			return base64_decode($v);
	}


	/**
		Tells if the last field retrieved was SQL NULL.
		@return bool True if the last field retrieved from the result set
		was SQL NULL.
	*/
	function wasNull()
	{
		return $this->was_null;
	}


	/**
		Releases the result set. Once released, this result set cannot
		be used again. Doesn nothing if already closed.
		@return void
	*/
	abstract function close();


	/**
		Returns the result set as a human-readable text.
		@return string The result set as a text, including records, field names
		and values as string encoded as set in the driver.
	*/
	function __toString()
	{
		$res = "";
		try {
			$n_rows = $this->getRowCount();
			$n_cols = $this->getColumnCount();

			for($r = 0; $r < $n_rows; $r++){
				if( $r > 0 )
					$res .= "\n";
				$this->moveToRow($r);
				$res .= "Record no. $r)\n";
				for($c = 0; $c < $n_cols; $c++){

					$res .= "    " . $this->getColumnName($c) . ": ";

					$v = $this->getStringByIndex($c);
					if( $v === NULL ){
						$res .= "NULL\n";
					} else {
						$res .= "$v\n";
					}
				}
			}
		}
		catch(SQLException $e){
			$res .= "\n$e";
		}
		return $res;
	}

}

