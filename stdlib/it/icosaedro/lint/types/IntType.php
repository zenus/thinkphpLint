<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the integer number type.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/25 15:44:07 $
 */
final class IntType extends Type {
	
	/**
	 * @var IntType 
	 */
	private static $instance;
	
	private /*. void .*/ function __construct(){
	}
	
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new IntType();
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
		return "int";
	}
	
	
	/**
	 * Returns true if the left hand side (LHS) is int, float, mixed or unknown.
	 * Note that int cannot be assigned to string for efficiency reasons: at
	 * runtime, a variable formally detected to be string might be accessed
	 * character by character although being a number, so forcing PHP to
	 * perform a number-to-string conversion each time the "string" content is
	 * accessed. So, a int to string assignment requires a value typecast
	 * <code>$s = (int) $i;</code>
	 * @param Type $lhs Type of the LHS.
	 * @return boolean True if this type is assignable to the LHS type.
	 */
	public function assignableTo($lhs){
		return ($lhs instanceof IntType)
		|| ($lhs instanceof FloatType)
		|| ($lhs instanceof MixedType)
		|| ($lhs instanceof UnknownType);
	}
	
}
