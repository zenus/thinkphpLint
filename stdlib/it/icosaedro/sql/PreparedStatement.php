<?php

namespace it\icosaedro\sql;

/*. require_module 'standard'; .*/

require_once __DIR__ . '/../../../all.php';

use it\icosaedro\containers\Printable;
use it\icosaedro\bignumbers\BigFloat;
use it\icosaedro\bignumbers\BigInt;
use it\icosaedro\utils\Date;
use DateTime;


/**
	SQL prepared statement. A prepared statement is an SQL command with
	variable parts (the "parameters") marked by a placeholder, that is the
	question mark character <code>?</code>.
	These parameters can then be set with proper values given as regular PHP
	language types, and these values are automatically properly encoded and
	possibly quoted as required by the underlying specific implementation of
	the SQL server.
	Once all the parameters are being set, the resulting complete SQL
	statement can be sent to the SQL server for the execution.
	The same prepared statement can then be re-used several times
	changing the values of the parameters. Example:
	<pre>
	$db = new it\icosaedro\sql\mysql\Driver(...);
	$ps = $db-&gt;prepareStatement("SELECT name, code FROM products WHERE price &gt; ? AND available = ?");
	$ps-&gt;setBigFloat(0, new BigFloat("100.00"));
	$ps-&gt;setBoolean(1, TRUE);
	$rs = $ps-&gt;query();
	echo $rs;
	</pre>
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/21 23:06:50 $
*/
class PreparedStatement implements Printable {

	private /*. SQLDriverInterface .*/ $db;

	/* Even entries are literal SQL code; odd entries are the parameters. */
	private /*. array[int]string .*/ $chunks;


	/**
		Constructor called by the SQL driver to create a new SQL prepared
		statement. This constructor should never be called directly from
		the user code. An instance of this class can be used several times
		setting different values for the variable parts.
		@param SQLDriverInterface $db  The SQL driver engine.
		@param string $cmd  Prepared statement, that is an SQL statement
		where the variable parts are marked by a question mark. The question
		mark must not be enclosed between single quotes. For example
		<code>"SELECT * FROM mytable WHERE pk=? AND name=?"</code>.
		@return void
	*/
	function __construct($db, $cmd)
	{
		$this->db = $db;
		$a = explode("?", $cmd);
		$n = count($a);
		for($i = 0; $i < $n; $i++){
			$this->chunks[] = $a[$i];
			if( $i < $n-1 )
				$this->chunks[] = "?";
		}
	}


	/**
		Sets the value of a parameter. This method should never be called
		directly from the user code. The parameter is set verbatim - no
		escaping is performed and no quotes are inserted.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param string $value  Value as a string that repleces the placeholder.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	protected function setParameter($index, $value)
	{
		$pos = 2*$index + 1;
		if( $pos < 0 or $pos >= count($this->chunks) )
			throw new SQLException("invalid index: $index");
		$this->chunks[$pos] = $value;
	}


	/**
		Sets the parameter as SQL literal NULL value.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setNull($index)
	{
		$this->setParameter($index, "NULL");
	}


	/**
		Sets a parameter of type string.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param string $value  Value of the parameter, encoded accordingly to
		the instance of the client data base established. If NULL, then the
		SQL NULL value is set.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setString($index, $value)
	{
		if( $value === NULL )
			$v = "NULL";
		else
			$v = "'" . $this->db->escape($value) . "'";
		$this->setParameter($index, $v);
	}


	/**
		Sets a parameter of type boolean.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param bool $value  Value of the parameter. If the parameter has to
		be set to the SQL NULL value, use {@link self::setNull()} instead.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setBoolean($index, $value)
	{
		$this->setParameter($index, $value? "'1'" : "'0'");
	}


	/**
		Sets a parameter of type int.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param int $value  Value of the parameter. If the parameter has to
		be set to the SQL NULL value, use {@link self::setNull()} instead.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setInt($index, $value)
	{
		$this->setParameter($index, (string) $value);
	}


	/**
		Sets a parameter of type BigInt.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param BigInt $value  Value of the parameter. If NULL, then the SQL
		NULL value is set.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setBigInt($index, $value)
	{
		if( $value === NULL )
			$v = "NULL";
		else
			$v = $value->__toString();
		$this->setParameter($index, $v);
	}


	/**
		Sets a parameter of type BigFloat.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param BigFloat $value  Value of the parameter. If NULL, then the SQL
		NULL value is set.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setBigFloat($index, $value)
	{
		if( $value === NULL )
			$v = "NULL";
		else
			$v = $value->__toString();
		$this->setParameter($index, $v);
	}


	/**
		Sets a parameter of type gregorian date.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param Date $value  Value of the parameter. If NULL, then the SQL
		NULL value is set.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setDate($index, $value)
	{
		if( $value === NULL )
			$v = "NULL";
		else
			$v = "'" . $value->__toString() . "'";
		$this->setParameter($index, $v);
	}


	/**
		Sets a parameter of type date with time.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param DateTime $value  Value of the parameter. If NULL, then the SQL
		NULL value is set.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setDateTime($index, $value)
	{
		if( $value === NULL )
			$v = "NULL";
		else
			$v = "'" . $value->format("Y-m-d H:M:S") . "'";
		$this->setParameter($index, $v);
	}


	/**
		Sets a parameter of type binary (for example, an image).
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param string $value  Value of the parameter as an array of bytes.
		If NULL, then the SQL NULL value is set.
		In this generic implementation the value is sent to the data base as
		a Base64-encoded ASCII string, that is the safer and most efficient
		known encoding using only ASCII characters. Implementations for
		specific data base engines that provide a binary field type should
		override this method with something more efficient.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setBytes($index, $value)
	{
		if( $value === NULL )
			$v = "NULL";
		else
			$v = "'" . base64_encode($value) . "'";
		$this->setParameter($index, $v);
	}


	/**
		Returns the prepared statement in its current state.
		@return string  The prepared statement as a SQL string with parameters
		replaced by their values as set so far. Parameters still not set are
		rendered as empty strings; the original question mark placeholder
		does not appear anymore.
	*/
	function getSQLStatement()
	{
		return implode("", $this->chunks);
	}


	/**
		Sends the prepared statement to the data base for execution as
		an update command, typically UPDATE or INSERT. The variable
		parameters, marked by the question mark placeholder. Variable
		parameters still not set are left as empty strings, possibly
		resulting in an invalid SQL command.
		@return int  Number of rows affected by the change.
		@throws SQLException  If the execution of the prepared statement
		failed, possibly because some variable parameters were not set,
		or the syntax of the command was not valid, or the specified
		tables and fields do not exist.
	*/
	function update()
	{
		return $this->db->update( $this->getSQLStatement() );
	}


	/**
		Sends the prepared statement to the data base for execution as
		an enquiry command, typically a SELECT. The variable
		parameters, marked by the question mark placeholder. Variable
		parameters still not set are left as empty strings, possibly
		resulting in an invalid SQL command.
		@return ResultSet  Resulting table.
		@throws SQLException  If the execution of the prepared statement
		failed, possibly because some variable parameters were not set,
		or the syntax of the command was not valid, or the specified
		tables and fields do not exist.
	*/
	function query()
	{
		return $this->db->query( $this->getSQLStatement() );
	}


	/**
		Clears all the variable parameters of the prepared statement
		to their initial default value, that is undefined. If this prepared
		statement gets reused there is not really need to call this method,
		but it may help to detect missing parameter assignments because an
		incomplete (and then invalid) SQL command would be generated.
		@return void
	*/
	function clearParameters()
	{
		$n = count($this->chunks);
		for($i = 1; $i < $n; $i += 2)
			$this->chunks[$i] = "?";
	}


	/**
		Returns the prepared statement in its current state.
		It is simply an alias of the getSQLStatement() method.
		@return string  The prepared statement as a SQL string with parameters
		replaced by their values as set so far. Parameters still not set are
		rendered as empty strings; the original question mark placeholder
		does not appear anymore.
	*/
	function __toString()
	{
		return $this->getSQLStatement();
	}

}
