<?php

namespace it\icosaedro\lint\types;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Hashable;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\CaseInsensitiveString;
use it\icosaedro\lint\Where;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\Signature;
use it\icosaedro\lint\docblock\DocBlock;
use CastException;

/**
 * Method.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/20 16:18:58 $
 */
class ClassMethod implements Printable, Sortable, Hashable {
	
	const NAME = __CLASS__;
	
	/**
	 * Name of some special method. Defining this special name as
	 * case-insensitive string, allows a faster comparison with names found
	 * in the source.
	 * @var CaseInsensitiveString 
	 */
	public static $CONSTRUCT_NAME, $DESTRUCT_NAME, $TO_STRING_NAME,
		$CLONE_NAME;
	
	/**
	 * Class or interface to which this method belongs.
	 * @var ClassType
	 */
	public $class_;
	
	/**
	 * DocBlock associated with this method, or NULL if no DocBlock.
	 * @var DocBlock 
	 */
	public $docblock;
	
	/**
	 * Visibility of the method.
	 * @var Visibility 
	 */
	public $visibility;
	
	/**
	 * Name of the method.
	 * @var CaseInsensitiveString 
	 */
	public $name;
	
	/**
	 * If this is not a real method of the class, but it is a prototype
	 * declared using the PHPLint meta-code. PHPLint checks that every
	 * prototype be actually defined in PHP code before passing validation.
	 * Once the actual method has been found, this flag is reset to false.
	 * @var boolean 
	 */
	public $is_forward = FALSE;
		
	/**
	 * True if abstract method of abstract class or interface method.
	 * @var boolean 
	 */
	public $is_abstract = FALSE;
		
	/**
	 * @var boolean 
	 */
	public $is_static = FALSE;
		
	/**
	 * @var boolean 
	 */
	public $is_final = FALSE;
	
	/**
	 * @var boolean
	 */
	public $is_constructor = FALSE;
	
	/**
	 * @var boolean
	 */
	public $is_destructor = FALSE;
	
	/**
	 * Signature of this method, including: return type, arguments, triggered
	 * errors and thrown exceptions.
	 * @var Signature 
	 */
	public $sign;
	
	/**
	 * Where this method was declared.
	 * @var Where 
	 */
	public $decl_in;

	/**
	 * Usage counter. Details unspecified here, but basically used to detect
	 * private methods that are never used and should signaled as "dead code".
	 * @var int 
	 */
	public $used = 0;
	
	
	/**
	 * Returns the readable prototype of this method.
	 * The prototype is a string of the form:
	 * <blockquote><pre>
	 * 	[visibility] [final] [static] type([&amp;]type, [type], ...)
	 * </pre></blockquote>
	 * where
	 * <blockquote><pre>
	 * 	visibility = public | protected | final
	 * </pre></blockquote>
	 * For example,
	 * <code>public function f($a)</code>
	 * becomes
	 * <code>public unknown(mixed)</code>
	 * Note that the abstract qualifier is omitted.
	 * @return string Signature of this method.
	 */
	public function prototype(){
		$s = $this->visibility->__toString();
		#IF m[abstract] THEN s = s + " abstract" END
		if( $this->is_final )
			$s = $s . " final";
		if( $this->is_static )
			$s = $s . " static";
		return $s . " " . $this->sign;
	}
	
	
	/**
	 * Builds a new method.
	 * @param Where $where Where it has been declared.
	 * @param ClassType $class_ Class to which it belongs.
	 * @param DocBlock $docblock DocBlock, or null if not available.
	 * @param Visibility $visibility Visibility modifier.
	 * @param CaseInsensitiveString $name Name of the method.
	 * @param Signature $signature Signature of the method.
	 * @return void
	 */
	public function __construct($where, $class_, $docblock, $visibility, $name, $signature){
		$this->decl_in = $where;
		$this->class_ = $class_;
		$this->docblock = $docblock;
		$this->visibility = $visibility;
		$this->name = $name;
		$this->sign = $signature;
	}
	
	
	/**
	 * Returns the full name of the method.
	 * @return string Full name of the method in the form CLASS::METHOD.
	 */
	public function __toString(){
		return $this->class_->name . "::" . $this->name;
	}
	
	
	/**
	 * Hash code, case-independent.
	 * @return int 
	 */
	public function getHash()
	{
		return $this->name->getHash();
	}
	
	
	/**
	 * Compares this method name with another method name,
	 * disregarding any difference between upper-case and lower-case
	 * letters.
	 * @param object $other
	 * @return boolean True if the other object is an instance of this class
	 * and holds the same string compared in case insensitive way.
	 */
	public function equals($other)
	{
		if($other === $this)
			return TRUE;
		if( ! ($other instanceof self) )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->name->equals($other2->name);
	}
	
	
	/**
	 * Case-insensitive comparison of method names. This method has nothing
	 * to do with the {@link self::equals()} method, as this method only
	 * compares methods' names, completely disregarding their signature, and
	 * it is useful only to sort methods of a single class, where names are
	 * already distinct. So, in general, 2 methods of unrelated classes
	 * can be equal according to this criteria and different eccording to
	 * <code>equals()</code> or vice-versa without any relation between the
	 * two results.
	 * @param object $other Another ClassMethod object.
	 * @return int Negative, zero or positive if the name of this method is
	 * less, equal or greater than $other respectively compared in
	 * case-insensitive way.
	 * @throws CastException The other object belongs to a different
	 * class and cannot be compared with this.
	 */
	public function compareTo($other)
	{
		if( $other === NULL )
			throw new CastException("NULL");
		if( ! ($other instanceof self) )
			throw new CastException("expected " . __CLASS__
			. " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		return $this->name->compareTo($other2->name);
	}
	
	
	/**
	 * Return true if this method has the same visibility, the same static/
	 * non-static modifier and the same signature of the argument.
	 * Method's final modifier, abstract modifier, name and classes to which
	 * the methods belongs are not compared.
	 * @param self $o Other method.
	 * @return boolean
	 */
	public function equalsPrototypeOf($o){
		if( $o === NULL )
			return FALSE;
		if( $o === $this )
			return TRUE;
		if( ! ($o instanceof ClassMethod) )
			return FALSE;
		$o2 = cast(__CLASS__, $o);
		return $this->visibility === $o2->visibility
		&& $this->is_static == $o2->is_static
		&& $this->sign->equals($o2->sign);
	}
	
	
//	/**
//	 * Checks if this method is call-compatible with another implemented or
//	 * overridden method.
//	 * @param ClassMethod $other Implemented or overridden method.
//	 * @return boolean True if this method is call-compatible with the other.
//	 */
//	public function callCompatibleWith($other)
//	{
//		if( $other->is_constructor || $other->is_destructor
//		|| $this->is_constructor || $this->is_destructor )
//			return FALSE;
//		if( $other->is_final )
//			return FALSE;
//		
//		if( $other->visibility === Visibility::$private_ ){
//			return FALSE;
//		} else if( $other->visibility === Visibility::$protected_ ){
//			if( $this->visibility === Visibility::$private_ )
//				return FALSE;
//		} else if( $this->visibility !== Visibility::$public_ ){
//			return FALSE;
//		}
//		
//		if( $other->is_static !== $this->is_static )
//			return FALSE;
//		return $this->sign->callCompatibleWith($other->sign);
//	}
	
	
	/**
	 * Checks if this method is call-compatible with another implemented or
	 * overridden method.
	 * @param ClassMethod $other Implemented or overridden method.
	 * @return string Empty string if this method is call-compatible with the
	 * other.
	 */
	public function callCompatibleWithReason($other)
	{
		if( $other->is_constructor || $other->is_destructor
		|| $this->is_constructor || $this->is_destructor )
			return "special method";
		if( $other->is_final )
			return "final method cannot be implemented/overridden";
		
		if( $other->visibility === Visibility::$private_ ){
			/*
			 * Private [non-]static method may redefine private
			 * [non-]static method since 5.4 (bug #61761).
			 */
			if( $this->visibility !== Visibility::$private_ )
				return "cannot raise visibility of re-implemented private method";
			if( $other->is_static !== $this->is_static )
				return "static/non-static missmatch";
			return "";
			//return "cannot implement/override private method";
		} else if( $other->visibility === Visibility::$protected_ ){
			if( $this->visibility === Visibility::$private_ )
				return "cannot lower visibility";
		} else if( $this->visibility !== Visibility::$public_ ){
			return "cannot lower visibility";
		}
		
		if( $other->is_static !== $this->is_static )
			return "static/non-static missmatch";
		return $this->sign->callCompatibleWithReason($other->sign);
	}
	
	
	/**
	 * Initializes this class, do not use. 
	 */
	public static function static_init()
	{
		self::$CONSTRUCT_NAME = new CaseInsensitiveString("__construct");
		self::$DESTRUCT_NAME = new CaseInsensitiveString("__destruct");
		self::$TO_STRING_NAME = new CaseInsensitiveString("__toString");
		self::$CLONE_NAME = new CaseInsensitiveString("__clone");
	}
		
}

ClassMethod::static_init();
