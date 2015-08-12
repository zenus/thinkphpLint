<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\docblock\DocBlock;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Sortable;

/**
 * Function (not method; methods have a class by their own).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/11 10:08:15 $
 */
class Function_ implements Printable, Sortable {
	
	const NAME = __CLASS__;

	/**
	 * Name of the function.
	 * @var FullyQualifiedName 
	 */
	public $name;

	/**
	 * Dummy forward declaration, also known as "prototype".
	 * @var boolean 
	 */
	public $is_forward = FALSE;

	/**
	 * Is this func. private to the package where it is defined?
	 * @var boolean 
	 */
	public $is_private = FALSE;

	/**
	 * Where the prototype or its actual code has been declared.
	 * @var Where 
	 */
	public $decl_in;

	/**
	 * Usages counter. Private functions that are never used are reported.
	 * @var int 
	 */
	public $used = 0;

	/**
	 * Signature of the function.
	 * @var Signature 
	 */
	public $sign;
	
	/**
	 * DocBlock of this function. Null if no DocBlock.
	 * @var DocBlock 
	 */
	public $docblock;
	
	
	/**
	 * @param FullyQualifiedName $name 
	 * @return void
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	
	/**
	 *
	 * @return string 
	 */
	public function __toString()
	{
		return $this->name->__toString();
	}
	
	
	/**
	 * Return true if this function has the same visibility and the same
	 * signature of the argument.
	 * @param self $o Other function.
	 * @return boolean
	 */
	public function equalsPrototypeOf($o){
		if( $o === NULL )
			return FALSE;
		if( $o === $this )
			return TRUE;
		if( ! ($o instanceof self) )
			return FALSE;
		$o2 = cast(__CLASS__, $o);
		return $this->is_private === $o2->is_private
		&& $this->sign->equals($o2->sign);
	}
	
	
	/**
	 * Returns the prototype of this function. The string returned has form
	 * <blockquote><code>
	 * [private] TYPE [&amp;] function NAME(arguments)
	 * [triggers ...] [throws ...]
	 * </code></blockquote>
	 * @return string Prototype of this function.
	 */
	public function prototype()
	{
		$signature = $this->sign->__toString();
		$lparen = strpos($signature, "(");
		
		return ($this->is_private? "private " : "")
		. substr($signature, 0, $lparen) . " "
		. "function " . $this->name . "("
		. substr($signature, $lparen + 1);
	}
	
	
	/**
	 *
	 * @param object $o 
	 * @return int
	 */
	public function compareTo($o) {
		$f = cast(__CLASS__, $o);
		return $this->name->compareTo($f->name);
	}
	
	
	/**
	 * @param object $o
	 */
	public function equals($o){
		$f = cast(__CLASS__, $o);
		return $this->name->equals($f->name);
	}
	
}

