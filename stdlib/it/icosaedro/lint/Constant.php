<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../autoload.php";
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\docblock\DocBlock;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Sortable;
use CastException;

/**
 * An instance of this class stores a constant. Constants are defined either
 * with the <code>define();</code> function-like statement or through the
 * <code>const</code> statement. These are NOT class constants.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/04 12:01:38 $
 */
class Constant implements Printable, Sortable {
	
	const NAME = __CLASS__;

	/**
	 * Name of the constant.
	 * @var FullyQualifiedName
	 */
	public $name;

	/**
	 * Is this const private to the package where it is defined?
	 * @var boolean
	 */
	public $is_private = FALSE;

	/**
	 * If NULL, still not declared.
	 * @var Where
	 */
	public $decl_in;

	/**
	 * Usage counter as RHS.
	 * @var int
	 */
	public $used = 0;
	
	/**
	 * If it is a magic constants (__FILE__, __DIR__, __CLASS__, etc).
	 */
	public $is_magic = FALSE;

	/**
	 * Type and value of the constant. Magic constants must be resolved
	 * elsewhere (see package MagicConstants).
	 * @var Result
	 */
	public $value;

	/**
	 * 
	 * @var DocBlock
	 */
	public $docblock;

	/**
	 * Builds a new constant.
	 * @param FullyQualifiedName $name
	 * @return void
	 */
	public function __construct($name) {
		$this->name = $name;
	}
	
	
	/**
	 * Returns the FQN of this constant.
	 * @return string FQN of this constant.
	 */
	public function __toString() {
		return $this->name->__toString();
	}
	
	
	/**
	 * Alphabetical comparison of constants' FQNs.
	 * @param object $o Another constant.
	 * @return int As usual, negative, zero or positive.
	 * @throws CastException Argument does not belong to this class.
	 */
	public function compareTo($o) {
		$c = cast(__CLASS__, $o);
		return $this->name->compareTo($c->name);
	}
	
	
	/**
	 * Returns true if this constant is the exact same object of the other.
	 * The parser guarantees there is only one constant with a given name, so
	 * comparing object instances fits the need - no need to actually compare
	 * names.
	 * @param object $o
	 * @return boolean True if this constant is the exact same object of the
	 * other.
	 */
	public function equals($o){
		return $this === $o;
	}
	
}
