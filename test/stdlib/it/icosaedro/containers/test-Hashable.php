<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../../../../../stdlib/autoload.php";

use it\icosaedro\utils\TestUnit as TU;

class Guy implements Hashable {

	private /*. string .*/ $name;
	private /*. int .*/ $age = 0;
	private /*. int .*/ $hash = 0;

	/*. void .*/ function __construct(/*. string .*/ $name, /*. int .*/ $age)
	{
		$this->name = $name;
		$this->age = $age;
	}


	/*. int .*/ function getHash()
	{
		if( $this->hash == 0 )
			$this->hash = Hash::hashOfString($this->name)
				^ Hash::hashOfInt($this->age);
		return $this->hash;
	}


	/*. bool .*/ function equals(/*. object .*/ $other)
	{
		$other2 = cast(__CLASS__, $other);
		return $this->name === $other2->name
			&& $this->age === $other2->age;
	}
}


class testHashable extends TU {
	function run()
	{
		echo Hash::hashOf(TRUE), "\n";
		echo Hash::hashOf(12345), "\n";
		echo Hash::hashOf("aaaa"), "\n";

		$a = new Guy("pippo", 46);
		$b = new Guy("pluto", 15);
		$c = new Guy("topolino", 15);
		$d = new Guy("paperino", 15);
		echo Hash::hashOf($a), "\n";
		echo Hash::hashOf($b), "\n";
		echo Hash::hashOf($c), "\n";
		echo Hash::hashOf($d), "\n";
	}
}

$tu = new testHashable();
$tu->start();
