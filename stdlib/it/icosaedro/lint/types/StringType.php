<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the string type.
 * Strings, under PHP, are bare arrays of bytes typically used to store
 * readable text, but the encoding of the characters is unspecified.
 * The only thing one may rely on is the ASCII charset, which is the common
 * subset of any practical character encoding.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/07/07 17:37:21 $
 */
final class StringType extends Type {
	
	/**
	 * @var StringType 
	 */
	private static $instance;
	
	/**
	 * @return void
	 */
	private function __construct(){
	}
	
	/**
	 *
	 * @return StringType
	 */
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new StringType();
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
		return "string";
	}
	
	
	/**
	 * Returns true if the left hand side (LHS) is string, mixed or unknown.
	 * @param Type $lhs Type of the LHS.
	 * @return boolean True if this type is assignable to the LHS type.
	 */
	public function assignableTo($lhs){
		return ($lhs instanceof StringType)
		|| ($lhs instanceof MixedType)
		|| ($lhs instanceof UnknownType);
	}
	
}
