<?php

namespace it\icosaedro\sql\mysql;

require_once __DIR__ . '/../../../../all.php';

/*. require_module 'mysqli'; .*/

use it\icosaedro\sql\SQLException;
use mysqli_result;
use ErrorException;

/**
	MySQL specific implementation of the result set.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/01/28 20:27:32 $
*/
class ResultSet extends \it\icosaedro\sql\ResultSet {

	private /*. mysqli_result .*/ $res;

	private /*. int .*/ $row_count = 0;

	private /*. int .*/ $curr_row_index = -1;

	private /*. array[int]string .*/ $curr_row;

	/** Maps column names to column indeces. */
	private /*. array[string]int .*/ $fields;


	/*. void .*/ function __construct(/*. mysqli_result .*/ $res)
	{
		$this->res = $res;
		$this->row_count = mysqli_num_rows($res);
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
		else {
			if( ! mysqli_data_seek($this->res, $row_index) )
				throw new SQLException("no this row index: $row_index");
			$this->curr_row = cast("array[int]string",
				mysqli_fetch_row($this->res));
		}
	}


	/*. int .*/ function getColumnCount()
	{
		return mysqli_num_fields($this->res);
	}


	/*. string .*/ function getColumnName(/*. int .*/ $column_index)
		/*. throws SQLException .*/
	{
		try {
			return mysqli_fetch_field_direct($this->res, $column_index)->name;
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
			if( ! mysqli_data_seek($this->res, $this->curr_row_index) )
				return FALSE;
			$this->curr_row = cast("array[int]string",
				mysqli_fetch_row($this->res));
			return TRUE;
		}
			
	}


	/*. string .*/ function getStringByIndex(/*. int .*/ $column_index)
		/*. throws SQLException .*/
	{
		if( $this->curr_row === NULL )
			throw new SQLException("row not selected");
		if( $column_index < 0 or $column_index >= mysqli_num_fields($this->res) )
			throw new SQLException("column index out of the range: $column_index");
		$v = $this->curr_row[$column_index];
		$this->was_null = $v === NULL;
		return $v;
	}


	private /*. int .*/ function getColumnIndex(/*. string .*/ $column_name)
		/*. throws SQLException .*/
	{
		if( $this->fields === NULL ){
			$this->fields = /*. (array[string]int) .*/ array();
			$n = $this->getColumnCount();
			for($i = 0; $i < $n; $i++){
				try {
					$meta = mysqli_fetch_field_direct($this->res, $i);
				}
				catch(ErrorException $e){
					throw new SQLException("cannot retrieve name of column no. $i: " . $e->getMessage());
				}
				$this->fields[$meta->name] = $i;
			}
		}
		if( isset($this->fields[$column_name]) )
			return $this->fields[$column_name];
		else
			return -1;
	}


	/*. string .*/ function getStringByName(/*. string .*/ $column_name)
		/*. throws SQLException .*/
	{
		if( $this->curr_row === NULL )
			throw new SQLException("row not selected");
		$column_index = $this->getColumnIndex($column_name);
		if( $column_index >= 0 )
			$v = $this->curr_row[$column_index];
		else
			throw new SQLException("no this field: $column_name");
		$this->was_null = $v === NULL;
		return $v;
	}
	

	/*. string .*/ function getBytesByIndex(/*. int .*/ $column_index)
		/*. throws SQLException .*/
	{
		$v = $this->getStringByIndex($column_index);
		if( $v === NULL )
			return NULL;
		else
			return stripslashes($v);
	}


	/*. string .*/ function getBytesByName(/*. string .*/ $column_name)
		/*. throws SQLException .*/
	{
		$v = $this->getStringByName($column_name);
		if( $v === NULL )
			return NULL;
		else
			return stripslashes($v);
	}


	/*. void .*/ function close()
	{
		if( $this->res !== NULL ){
			mysqli_free_result($this->res);
			$this->res = NULL;
		}
	}

}
