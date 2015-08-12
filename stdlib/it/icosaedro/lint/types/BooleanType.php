<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the boolean type.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/25 15:44:07 $
 */
final class BooleanType extends Type {
	
	/**
	 * @var BooleanType 
	 */
	private static $instance;
	
	private /*. void .*/ function __construct(){
	}
	
	/**
	 * Returns the singleton instance of the boolean type.
	 * @return BooleanType Singleton instance of the boolean type.
	 */
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new BooleanType();
		return self::$instance;
	}
	
	/**
	 * Returns true if the other type is boolean too.
	 * @param object $o
	 * @return boolean True if the argument is exactly the singleton instance
	 * of this class.
	 */
	public function equals($o){
		return $this === $o;
	}
	
	/**
	 * Returns "boolean".
	 * @return string 
	 */
	public function __toString(){
		return "boolean";
	}
	
	
	/**
	 * Returns true if this type is assignable to the LHS type. The boolean
	 * type is assignable to: boolean, mixed (automatic boxing), unknown.
	 * @param Type $lhs Type of the LHS.
	 * @return boolean True if the left hand side (LHS) is boolean, mixed or
	 * unknown.
	 */
	public function assignableTo($lhs){
		return ($lhs instanceof BooleanType)
		|| ($lhs instanceof MixedType)
		|| ($lhs instanceof UnknownType);
	}
	
}
