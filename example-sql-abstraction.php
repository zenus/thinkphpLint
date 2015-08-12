<?php

require_once __DIR__ . '/stdlib/autoload.php';

/**
	Example that shows how to use the it\icosaedro\sql package,
	an abstraction layer to access an SQL data base.
	MySQL 5 and PostgreSQL 8/9 implementations are supported;
	for other DB you may want to implement the same interfaces.
	Basically, all you need to do is to create an instance of the
	Driver object from the specific implementation
	(either {@link it\icosaedro\sql\mysql\Driver} or
	{@link it\icosaedro\sql\postgresql\Driver}). This driver object
	implements the generic interface
	SQLDriverInterface that provides abstract methods to
	perform data base queries and other operations, including
	prepared statements. For more details, please see
	{@link it\icosaedro\sql\SQLDriverInterface} and
	{@link it\icosaedro\sql\PreparedStatement}.
	
	@package example-sql-abstraction.php
*/

use it\icosaedro\sql\SQLException;

const DB_DRIVER = "mysql";

function displayQuery(/*. string .*/ $query)
	/*. throws SQLException .*/
{
	$db = /*. (it\icosaedro\sql\SQLDriverInterface) .*/ NULL;
	if( DB_DRIVER === "mysql" )
		$db = new it\icosaedro\sql\mysql\Driver( array("localhost", "MyName", "MyPass", "mydb") );
	else if( DB_DRIVER === "postgresql" )
		$db = new it\icosaedro\sql\postgresql\Driver("dbname=mydb");
	else
		throw new RuntimeException("unknown DB driver: " . DB_DRIVER);
	
	echo "Performing query: $query\n";
	$res = $db->query($query);
	$cols = $res->getColumnCount();
	$rows = $res->getRowCount();
	for($row = 0; $row < $rows; $row++){
		echo "Row no. $row:\n";
		$res->moveToRow($row);
		for($col = 0; $col < $cols; $col++){
			echo "   ", $res->getColumnName($col), ": ",
				$res->getStringByIndex($col), "\n";
		}
	}
}

try {
	displayQuery("SELECT name, id FROM users");
}
catch(SQLException $e){
	echo $e;
}
