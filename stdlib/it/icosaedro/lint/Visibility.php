<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";

/**
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:09:17 $
 */
class Visibility extends Enum {
	
	/**
	 * @var Visibility
	 */
	public static $private_;
	
	/**
	 * @var Visibility
	 */
	public static $protected_;
	
	/**
	 * @var Visibility
	 */
	public static $public_;

	private /*. string .*/ $name;
	
	public /*. void .*/ function __construct(/*. string .*/ $name){
		$this->name = $name;
	}
	
	
	public /*. string .*/ function __toString(){
		return $this->name;
	}
	
}

Visibility::$private_ = new Visibility("private");
Visibility::$protected_ = new Visibility("protected");
Visibility::$public_ = new Visibility("public");
