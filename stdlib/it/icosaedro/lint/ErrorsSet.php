<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Printable;
use InvalidArgumentException;
use LogicException;

/**
 * Represents a set of errors. The singleton instance of the "no errors"
 * empty set is also available from the <code>getEmpty()</code> method.
 * Once created, an object of this class contains the empty set of errors
 * (that is no errors at all). Client code can then add errors through the
 * <code>put()</code> method. The object can be closed to prevent further
 * modifications calling the <code>close()</code> method, that makes the object
 * immutable. A <code>parse()</code> static method is also added to parse
 * single error names in their corresponding mask of bits.
 * Example:
 * <blockquote><pre>
 * $e = new Errors();
 * $e-&gt;put(E_WARNING);
 * $e-&gt;put(E_NOTICE);
 * echo $e; # E_WARNING, E_NOTICES
 * if( $e-&gt;contains(E_NOTICE|E_WARNING) )
 *	echo "contains E_NOTICE and E_WARNING";
 * </pre></blockquote>
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/04 12:01:38 $
 */
class ErrorsSet implements Printable, Comparable {
	
	private static $MASKS = array(
		'E_ERROR' => 1,
		'E_WARNING' => 2,
		'E_PARSE' => 4,
		'E_NOTICE' => 8,
		'E_CORE_ERROR' => 16,
		'E_CORE_WARNING' => 32,
		'E_COMPILE_ERROR' => 64,
		'E_COMPILE_WARNING' => 128,
		'E_USER_ERROR' => 256,
		'E_USER_WARNING' => 512,
		'E_USER_NOTICE' => 1024,
		'E_STRICT' => 2048,
		'E_RECOVERABLE_ERROR' => 4096,
		'E_DEPRECATED' => 8192,
		'E_USER_DEPRECATED' => 16384,
		'E_ALL' => 32767);

	/**
	 * Errors set, every bit being an error.
	 */
	private $errors_set = 0;
	
	/**
	 * If this object is closed to modifications. Once a function or method has
	 * been parsed, the set of errors it may trigger should be closed as safety
	 * measure.
	 */
	private $closed = FALSE;
	
	/**
	 * Singleton (closed) instance of the empty set of errors. The signature of
	 * every function and method is initialized with this empty set; then, only
	 * if errors are listed in the DocBlock or in meta-code, a specific set is
	 * created.
	 * @var ErrorsSet
	 */
	private static $empty_set;
	
	
	/**
	 * Closes this object making it immutable. Does nothing if already closed.
	 * @return void
	 */
	public function close(){
		$this->closed = TRUE;
	}
	
	
	/**
	 * Throws exception if this object is closed for modifications. Methods that
	 * change the set must call this first.
	 * @return void
	 * @throws LogicException This object is closed to modifications.
	 */
	private function checkClosed(){
		if( $this->closed )
			throw new LogicException("object closed to modifications");
	}
	
	
	/**
	 * Adds a single error mask to this errors bit mask. You cannot set more
	 * than one error at a time with this method.
	 * @param int $f Error mask containing a single bit set.
	 * @return void
	 * @throws LogicException Object already closed to modifications.
	 * @throws InvalidArgumentException No error with this mask.
	 */
	public function put($f){
		$this->checkClosed();
		$found = FALSE;
		foreach(self::$MASKS as $v){
			if( $v == $f ){
				$found = TRUE;
				break;
			}
		}
		if( ! $found )
			throw new InvalidArgumentException("unknown error mask: $f");
		$this->errors_set |= $f;
	}
	
	
	/**
	 * Adds all the errors specified.
	 * @param ErrorsSet $others Errors mask to add to this.
	 * @return void
	 * @throws LogicException Object already closed to modifications.
	 */
	public function putSet($others){
		$this->checkClosed();
		$this->errors_set |= $others->errors_set;
	}
	
	
	/**
	 * Returns true if this set is empty.
	 * @return boolean True if this set is empty.
	 */
	public function isEmpty(){
		return $this->errors_set == 0;
	}
	
	
	/**
	 * Empties this set.
	 * @return void
	 * @throws LogicException Object already closed to modifications.
	 */
	public function clear(){
		$this->checkClosed();
		$this->errors_set = 0;
	}
	
	
	/**
	 * Returns the singleton instance of the "no errors" mask.
	 * This instance is already closed and then cannot be modified.
	 * @return ErrorsSet Singleton instance of the "no errors" mask.
	 */
	public static function getEmpty(){
		if( self::$empty_set === NULL ){
			self::$empty_set = new ErrorsSet();
			self::$empty_set->close();
		}
		return self::$empty_set;
	}
	
	
	/**
	 * Parses an error name.
	 * @param string $name One of the allowed error names.
	 * @return int Error mask corresponding to the error name.
	 * @throws InvalidArgumentException Unknown error name.
	 */
	public static function parse($name){
		if( array_key_exists($name, self::$MASKS) )
			return self::$MASKS[$name];
		throw new InvalidArgumentException("unknown error name: $name");
	}
	
	
	/**
	 * Returns the name of the error code given.
	 * @param int $err Error code.
	 * @return string Name of the error code; if invalid, returns the number
	 * as a string.
	 */
	public static function nameOf($err){
		foreach(self::$MASKS as $name => $v)
			if( $v == $err )
				return $name;
		return "$err";
	}
	
	
	/**
	 * Returns the error mask that represents this set of errors.
	 * @return int Error mask that represents this set of errors.
	 */
	public function getErrors(){
		return $this->errors_set;
	}
	
	
	/**
	 * Returns true if the error mask contains the error specified. In other
	 * words, returns true if $f represents a subset of this errors mask.
	 * @param int $f Bit mask of the error(s).
	 * @return boolean True if the error mask contains the all the errors bits
	 * specified in the argument. 
	 */
	public function contains($f){
		return ($f & ~$this->errors_set) == 0;
	}
	
	
	/**
	 * Returns true if this set contains the other set.
	 * @param ErrorsSet $other 
	 * @return boolean True if this set contains the other set.
	 */
	public function containsAll($other)
	{
		return (~ $this->errors_set & $other->errors_set) == 0;
	}
	
	
	/**
	 * Returns this set minus the other set.
	 * @param ErrorsSet $other 
	 * @return ErrorsSet This set minus the other.
	 */
	public function difference($other)
	{
		$diff = new ErrorsSet();
		$diff->errors_set = $this->errors_set & ~ $other->errors_set;
		return $diff;
	}
	
	
	/**
	 * Returns a comma-separated list of the error names that represent this
	 * set of errors.
	 * @return string Comma-separated list of the error names that represent
	 * this set of errors, possibly the empty string if the set is empty.
	 */
	public function __toString(){
		$a = /*.(string[int]).*/ array();
		foreach(self::$MASKS as $n => $v)
			if( $this->contains($v) )
				$a[] = $n;
		return implode(", ", $a);
	}
	
	
	/**
	 * Compares this errors mask with the other for equality.
	 * @param object $other Other error mask.
	 * @return boolean True if this errors mask exactly equals the other.
	 */
	public function equals($other){
		if( $other === NULL )
			return FALSE;
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->errors_set == $other2->errors_set;
	}
	
}
