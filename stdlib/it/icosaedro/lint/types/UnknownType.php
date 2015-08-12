<?php

namespace it\icosaedro\lint\types;
require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the unknown type.
 * Entities whose type is unknown are immediately signaled and
 * their type is set to the singleton instance of this class. If the same
 * entity is found again in the source, the program should not complain again
 * and should do its best to continue parsing. In this way every thing has
 * its own not-NULL type specifier making source code simpler.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:58 $
 */
final class UnknownType extends Type {
	
	/**
	 * @var UnknownType 
	 */
	private static $instance;
	
	private /*. void .*/ function __construct(){
	}
	
	/**
	 *
	 * @return UnknownType 
	 */
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new UnknownType();
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
		return "unknown";
	}
	
	
	/**
	 * Always return true: unknown values are always assignable to anything
	 * except than void; an error has been already signaled when the unknown
	 * value was first detected.
	 * @param Type $lhs Type of the LHS.
	 * @return boolean Always true, except for the void type.
	 */
	public function assignableTo($lhs){
		return ! ($lhs instanceof VoidType);
	}
	
}
