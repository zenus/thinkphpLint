<?php

namespace it\icosaedro\sql;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

use it\icosaedro\utils\TestUnit as TU;
use it\icosaedro\utils\Date;
use it\icosaedro\sql\SQLDriverInterface;
use it\icosaedro\sql\ResultSet;
use it\icosaedro\sql\PreparedStatement;
use it\icosaedro\sql\SQLException;


/*. void .*/ function mysql()
	/*. throws SQLException .*/
{
	echo "\n\nUsing MySQL:\n";
	$db = new \it\icosaedro\sql\mysql\Driver( array("localhost", "xyz", "", "test") );
	#$db->insert("set autocommit = 1");
	#$db->query("select * from prodotti");
	#$db->query("select current_date");

	$rs = $db->query("select current_date");
	$rs->moveToRow(0);
	TU::test($rs->getDateByIndex(0), Date::today());

	echo "MySQL: prepared statement:\n";
	$ps = $db->prepareStatement("select * from prodotti where nome = ? or nome = ? or pk = ?");
	$ps->setString(0, "Matita");
	$ps->setString(1, "admin");
	$ps->setInt(2, 7);
	echo "   statement: ", $ps->getSQLStatement(), "\n";
	echo $ps->query();

	$ps = $db->prepareStatement("select ?");
	$bin_str = "\x00\x01\x02\x03\xc1\x80";
	$ps->setBytes(0, $bin_str);
	echo "PS: ", $ps, ":\n";
	$rs = $ps->query();
	$rs->moveToRow(0);
	TU::test($rs->getBytesByIndex(0), $bin_str);

	$db->close();
}


/*. void .*/ function postgresql()
	/*. throws SQLException .*/
{
	echo "Using PostgreSQL:\n";
	$db = new \it\icosaedro\sql\postgresql\Driver("dbname=icodb");
	#$db->insert("set DateStyle = iso");
	#$db->query("select 123 as aaa, 124 as bbb, 125");
	#$db->query("select * from users");

	$rs = $db->query("select current_date");
	$rs->moveToRow(0);
	TU::test($rs->getDateByIndex(0), Date::today());

	echo "PG: prepared statement:\n";
	$ps = $db->prepareStatement("select * from users where name = ? or name = ? or pk = ?");
	$ps->setString(0, "guest");
	$ps->setString(1, "admin");
	$ps->setInt(2, 7);
	echo "   statement: ", $ps, "\n";
	echo $ps->query();

	#$db->update("SET bytea_output = 'hex'");
	$bin_str = "\x00\x01\x02\x03\xc1\x80";
	#$sql = "select decode('" . base64_encode($bin_str) . "', 'base64')::bytea";
	#echo "$sql :\n";
	$ps = $db->prepareStatement("select ?");
	$ps->setBytes(0, $bin_str);
	echo "PS: ", $ps, ":\n";
	$rs = $ps->query();
	$rs->moveToRow(0);
	TU::test($rs->getBytesByIndex(0), $bin_str);

	$db->close();
}


class testSQLDriver extends TU {
	function run() /*. throws \Exception .*/ {
		postgresql();
		mysql();
	}
}
$tu = new testSQLDriver();
$tu->start();
