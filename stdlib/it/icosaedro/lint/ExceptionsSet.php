<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\HashSet;
use it\icosaedro\containers\Arrays;
use it\icosaedro\lint\types\ClassType;
use LogicException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Holds a set of exceptions thrown by a function or method.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/05 15:07:27 $
 */
class ExceptionsSet extends HashSet implements Printable, Comparable {

	/**
	 * If this object is closed to modifications. Once a function or method has
	 * been parsed, the set of exceptions it may throw should be closed as
	 * safety measure.
	 */
	private $closed = FALSE;
	
	/**
	 * Singleton (closed) instance of the empty set of exceptions. The
	 * signature of every function and method is initialized with this empty
	 * set; then, only if exceptions are listed in the DocBlock or in meta-code,
	 * a specific set is created.
	 * @var ExceptionsSet
	 */
	private static $empty_set;
	
	
	/*. forward public boolean function callCompatibleWith(ExceptionsSet $other); .*/
	
	
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
	 * Returns the singleton instance of the empty set.
	 * This instance is already closed and then cannot be modified.
	 * @return ExceptionsSet Singleton instance of the empty set.
	 */
	public static function getEmpty(){
		if( self::$empty_set === NULL ){
			self::$empty_set = new ExceptionsSet();
			self::$empty_set->close();
		}
		return self::$empty_set;
	}
	
	
	/**
	 * Returns true if this set is empty.
	 * @return boolean True if this set is empty.
	 */
	public function isEmpty(){
		return $this->count() == 0;
	}
	
	
	/**
	 * Adds and exception to this set.
	 * @param mixed $e Exception to add.
	 * @return boolean True if the exception has been added to the set; false if
	 * it is already there.
	 * @throws InvalidArgumentException The parameter is not an exception.
	 */
	public function put($e){
		$this->checkClosed();
		if( ! ($e instanceof ClassType) )
			throw new InvalidArgumentException("not an exception");
		return parent::put($e);
	}
	
	
	/**
	 * Unimplemented method.
	 * @param HashSet $other
	 * @throws RuntimeException Always throws this exception.
	 */
	public function putSet($other){
		throw new RuntimeException("unimplemented");
	}
	
	
	/**
	 * Unimplemented method.
	 * @param mixed $e Exception to remove from this set.
	 * @throws InvalidArgumentException The parameter is not an exception.
	 */
	public function remove($e){
		$this->checkClosed();
		if( ! ($e instanceof ClassType) )
			throw new InvalidArgumentException("not an exception");
		parent::remove($e);
	}
	
	
	/**
	 * Returns a comma-separated list of exception names that represent
	 * this set. Exceptions are sorted according to
	 * {@link it\icosaedro\lint\types\ClassType::compareTo()}.
	 * @return string Comma-separated list of exception names that represent
	 * this set, possibly the empty string if the set is empty.
	 */
	public function __toString(){
		$a = cast(ClassType::NAME."[int]", $this->getElements());
		$a = cast(ClassType::NAME."[int]", Arrays::sort($a));
		return Arrays::implode($a, ", ");
	}
	
	
	/**
	 * Returns true if this set contains the specified exception or any of its
	 * parents. That is, this set "captures" the exception.
	 * @param ClassType $e Exception.
	 * @return boolean True if this set contains the specified exception or any
	 * of its parents.
	 */
	public function includes($e)
	{
		if( $this->contains($e) )
			return TRUE;
		
		foreach($this as $m){
			$t = cast(ClassType::NAME, $m);
			if( $e->isSubclassOf($t) )
				return TRUE;
		}
		return FALSE;
	}
	
	
	/**
	 * Removes an exception and all its subclasses from the set of
	 * thrown exceptions. Used to parse the try/catch statement.
	 * @param ClassType $e Exception to remove along with all its
	 * subclasses.
	 * @return int Number of items actually removed from the set.
	 */
	public function removeWithSubclasses($e)
	{
		$this->checkClosed();
		$remove = /*.(ClassType[int]).*/ array();
		foreach($this as $m){
			$t = cast(ClassType::NAME, $m);
			if( $t->isSubclassOf($e) )
				$remove[] = $t;
		}
		foreach($remove as $t)
			$this->remove($t);
		return count($remove);
	}
	
	
	/**
	 * Returns this set with all the specified exceptions and their subclasses
	 * removed.
	 * @param ExceptionsSet $other 
	 * @return ExceptionsSet This set with all the specified exceptions and
	 * their subclasses removed.
	 */
	public function difference($other)
	{
		$this->checkClosed();
		$res = new ExceptionsSet();
		foreach($other as $om){
			$o = cast(ClassType::NAME, $om);
			if( ! $this->includes($o) )
				$res->put($o);
		}
		return $res;
	}
	
	
	/**
	 * Returns true if these exceptions are call-compatible with another set
	 * thrown by related method. This set is call-compatible if all the
	 * exceptions are subclasses of the other, or are unchecked exceptions.
	 * @param ExceptionsSet $other Exceptions thrown by the implemented method,
	 * or overridden method, or forward declared method, or forward declared
	 * function.
	 * @return boolean True this set is call-compatible with the other.
	 */
	public function callCompatibleWith($other)
	{
		foreach($this as $tm){
			if( $other->contains($tm) )
				continue;
			
			$t = cast(ClassType::NAME, $tm);
			if( $t->is_unchecked )
				continue;
			
			$found_parent = FALSE;
			foreach($other as $om){
				$o = cast(ClassType::NAME, $om);
				if( $t->isSubclassOf($o) ){
					$found_parent = TRUE;
					break;
				} else if( $t->is_unchecked ){
					continue;
				}
			}
			if( ! $found_parent )
				return FALSE;
		}
		return TRUE;
	}

}
