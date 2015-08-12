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
 * Property.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:58 $
 */
class ClassProperty implements Printable, Sortable {
	
	const NAME = __CLASS__;
	
	/**
	 * Class to which this property belongs.
	 * @var ClassType
	 */
	public $class_;

	/**
	 * Name of the property, without leading dollar sign.
	 * @var string 
	 */
	public $name;
	
	/**
	 * Visibility modifier.
	 * @var Visibility
	 */
	public $visibility;
	
	/**
	 * @var boolean 
	 */
	public $is_static = FALSE;
	
	/**
	 * Type and initial value of the property.
	 * @var Result
	 */
	public $value;
		
	/**
	 * Where it has been declared.
	 * @var Where 
	 */
	public $decl_in;
	
	/**
	 * How many times has been used outside its class.
	 * @var int
	 */
	public $used = 0;
		
	/**
	 * Used inside its class.
	 * @var boolean 
	 */
	public $used_inside = FALSE;
	
	/**
	 * DocBlock, or null is not available.
	 * @var DocBlock 
	 */
	public $docblock;
	
	/**
	 * Programmer's readable representation of this property.
	 * @return string String of the form CLASSNAME::$PROPERTYNAME.
	 */
	public function __toString(){
		return $this->class_->name . "::$" . $this->name;
	}
	
	/**
	 * Compare for equality this property with another. Only the names of the
	 * properties are compared, disregarding the class to which they belong.
	 * @param object $other
	 * @return boolean 
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
	 * Compares this property with another by name (locale aware).
	 * Completely disregards the class to which the 2 properties belong.
	 * @param object $other Another property.
	 * @return int
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
	 * Builds a new property.
	 * @param Where $where Where it has been declared.
	 * @param ClassType $class_ Class to which this property belongs.
	 * @param DocBlock $docblock DocBlock, or null if not available.
	 * @param Visibility $visibility Visibility modifier.
	 * @param string $name Name of the property, without leading dollar sign.
	 * @param Result $value Type and initial value.
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
