<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\lint\Enum;

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/01/12 03:27:54 $
 */
class PhpVersion extends Enum {
	
	public static /*. PhpVersion .*/ $php4, $php5;
	
	
	private /*. string .*/ $name;
	
	public /*. void .*/ function __construct(/*. string .*/ $name){
		$this->name = $name;
	}
	
	
	public /*. string .*/ function __toString(){
		return $this->name;
	}
}

PhpVersion::$php4 = new PhpVersion("PHP4");
PhpVersion::$php5 = new PhpVersion("PHP5");
