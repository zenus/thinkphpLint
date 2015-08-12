<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the PHP NULL type. Under PHPLint there is not such
 * type, but still there is the "NULL" constant that may be assigned to
 * any "complex" type (string, array, resource, object) then this special
 * value may appear in the code and then must have its own type.
 * Anyway, the NULL constant can be assigned to a variable that already has a
 * type, or to a formal argument of function/method; in any other case it must
 * cast to a proper type using the meta-code formal typecast of PHPLint
 * <code>/&#42;(TYPE).&#42;/ NULL</code>.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/23 16:25:16 $
 */
final class NullType extends Type {
	
	/**
	 * @var NullType 
	 */
	private static $instance;
	
	private /*. void .*/ function __construct(){
	}
	
	/**
	 *
	 * @return NullType 
	 */
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new NullType();
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
		return "null";
	}
	
	
	/**
	 * The NULL value can be assigned to: array, class, mixed, resource, string,
	 * null and unknown type.
	 * @param Type $lhs 
	 * @return boolean True if this type is assignable to the LHS type.
	 */
	public function assignableTo($lhs){
		return ($lhs instanceof ArrayType)
		|| ($lhs instanceof ClassType)
		|| ($lhs instanceof MixedType)
		|| ($lhs instanceof ResourceType)
		|| ($lhs instanceof StringType)
		|| ($lhs instanceof UnknownType)
		|| ($lhs instanceof self);
	}
	
}
