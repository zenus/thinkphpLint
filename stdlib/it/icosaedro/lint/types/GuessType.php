<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

/**
 * Singleton instance of the "still unknown" type.
 * When a function or a method does not explicitly declares its returned type,
 * PHPLint tries to guess the returned type from the first "return" statement
 * it found in the source. In the meanwhile, the type returned is set to
 * the singleton instance of this class.
 * The Signature class sets this type as default return type. Only the
 * code that parses functions, methods and the "return" statement are aware
 * of its existance and handle it properly in the attempt to guess automatically
 * the right returned type.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:58 $
 */
final class GuessType extends Type {
	
	/**
	 * @var GuessType 
	 */
	private static $instance;
	
	private /*. void .*/ function __construct(){
	}
	
	/**
	 *
	 * @return GuessType 
	 */
	public static function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new GuessType();
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
		return "\$RETURN_TYPE_STILL_TO_GUESS";
	}
	
	
	/**
	 * Always return false. This garantees that if the function or method
	 * appears in any expression, it is an error.
	 * @param Type $lhs Type of the LHS.
	 * @return boolean Always false
	 */
	public function assignableTo($lhs){
		return false;
	}
	
}
