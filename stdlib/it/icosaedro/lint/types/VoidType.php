<?php

namespace it\icosaedro\lint\types;
require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the "nothing" type.
 * Functions and methods that do not return anything are set to return this.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:58 $
 */
final class VoidType extends Type {
	
	/**
	 * @var VoidType 
	 */
	private static $instance;
	
	/**
	 * @return void
	 */
	private function __construct(){
	}
	
	/**
	 * @return VoidType 
	 */
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new VoidType();
		return self::$instance;
	}
	
	/**
	 *
	 * @param object $o
	 * @return boolean
	 */
	public function equals($o){
		return $this === $o;
	}
	
	public function __toString(){
		return "void";
	}
	
	
	/**
	 * Always return false.
	 * @param Type $lhs Type of the LHS.
	 * @return boolean Always returns false.
	 */
	public function assignableTo($lhs){
		return FALSE;
	}
	
}
