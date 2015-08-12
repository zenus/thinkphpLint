<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\Where;
use it\icosaedro\lint\Result;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\docblock\DocBlock;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Sortable;
use CastException;

/**
 * Class constant.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:58 $
 */
class ClassConstant implements Printable, Sortable {
	
	const NAME = __CLASS__;
	
	/**
	 * The class to which this constant belongs.
	 * @var ClassType
	 */
	public $class_;
	
	/**
	 * DocBlock associated to this constant. Null if not available.
	 * @var DocBlock 
	 */
	public $docblock;

	/**
	 * Under PHPLint, class constants can have a visibility modifier
	 * implemented as meta-code.
	 * @var Visibility 
	 */
	public $visibility;

	/**
	 * Name of the constant.
	 * @var string 
	 */
	public $name;

	/**
	 * Type and value of the constant.
	 * @var Result 
	 */
	public $value;

	/**
	 * Where it was declared.
	 * @var Where 
	 */
	public $decl_in;

	/**
	 * How many times has been used outside its class.
	 * @var int 
	 */
	public $used = 0;

	/**
	 * Is a dummy forward declaration.
	 * @var boolean
	 */
	public $is_forward = FALSE;
	
	/**
	 * Returns the programmer's name of this constant.
	 * @return string Name of this constant as CLASSNAME::NAME.
	 */
	public function __toString(){
		return $this->class_->name . "::" . $this->name;
	}
	
	/**
	 * Compare for equality this constant with another. Only the names of the
	 * constants are compared, disregarding the class to which they belong.
	 * This method serves only to sort constants, typically belonging to the
	 * same class, or inherited. In PHP all the inherited constants must have
	 * distinct names, collisions are not allowed.
	 * @param object $other Other class constant.
	 * @return boolean True if $other is another class constant with the same
	 * name.
	 */
	public function equals($other)
	{
		if( $other === NULL )
			return FALSE;
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->name === $other2->name;
	}
	
	
	/**
	 * Compares this constant with another by name (locale aware).
	 * Completely disregards the class to which the 2 constants belong.
	 * @param object $other Another constant.
	 * @return int
	 * @throws CastException The other is not a ClassConstant.
	 */
	public function compareTo($other)
	{
		if( $other === NULL )
			throw new CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new CastException("expected " . __CLASS__
			. " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		return strcmp($this->name, $other2->name);
	}
	
	
	/**
	 * Builds a new class constant.
	 * @param Where $where Where it is declared.
	 * @param ClassType $class_ Class to which it belongs.
	 * @param DocBlock $docblock DocBlock, possibly null if not available.
	 * @param Visibility $visibility Visibility modifier.
	 * @param string $name Name of the constant, with no specified encoding.
	 * @param Result $value Type and value of the constant.
	 * @return void
	 */
	public function __construct($where, $class_, $docblock, $visibility, $name, $value){
		$this->decl_in = $where;
		$this->class_ = $class_;
		$this->docblock = $docblock;
		$this->visibility = $visibility;
		$this->name = $name;
		$this->value = $value;
	}

}
