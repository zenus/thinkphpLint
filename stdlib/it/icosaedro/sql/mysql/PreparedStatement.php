<?php

namespace it\icosaedro\sql\mysql;

require_once __DIR__ . '/../../../../all.php';

use it\icosaedro\sql\SQLException;


/**
	MySQL specific implementation of the prepared statement.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/01/22 19:34:11 $
*/
class PreparedStatement extends \it\icosaedro\sql\PreparedStatement {

	/*. void .*/ function __construct(/*. Driver .*/ $db, /*. string .*/ $cmd)
	{
		# This method merely enforces type checking over the $db param,
		# so that it is a MySQL driver.
		parent::__construct($db, $cmd);
	}


	/**
		Sets a parameter of type binary (for example, an image).
		This implementation is specific of MySQL and assumes a field
		of type VARBINARY. Please note that the BINARY type should not
		be used because the fields of this type are automatically
		right-padded with spaces or NUL bytes depending on the version of
		MySQL, which normally is not what you want.
		@param int $index  Index of the parameter to set, the first question
		mark is the number 0.
		@param string $value  Value of the parameter as an array of bytes.
		If NULL, then the SQL NULL value is set.
		@return void
		@throws SQLException  If the index is invalid.
	*/
	function setBytes($index, $value)
	{
		if( $value === NULL )
			$v = "NULL";
		else
			$v = "'" . addslashes($value) . "'";
		$this->setParameter($index, $v);
	}


}
