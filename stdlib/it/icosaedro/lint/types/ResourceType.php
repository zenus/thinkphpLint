<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the resource type.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:58 $
 */
final class ResourceType extends Type {
	
	/**
	 * @var ResourceType 
	 */
	private static $instance;
	
	/**
	 * @return void
	 */
	private function __construct(){
	}
	
	/**
	 *
	 * @return ResourceType 
	 */
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new ResourceType();
		return self::$instance;
	}
	
	/**
	 *
	 * @param object $o
	 * @return boolean 
	 */
	public function equals($o){
		return $o !== NULL and $this === $o;
	}
	
	/**
	 *
	 * @return string 
	 */
	public function __toString(){
		return "resource";
	}
	
	
	/**
	 * A resource value can be assigned to: mixed, resource, mixed and unknown
	 * type.
	 * @param Type $lhs Type of the LHS.
	 * @return boolean True if this type is assignable to the LHS type.
	 */
	public function assignableTo($lhs){
		return ($lhs instanceof MixedType)
		|| ($lhs instanceof ResourceType)
		|| ($lhs instanceof UnknownType);
	}
	
}
