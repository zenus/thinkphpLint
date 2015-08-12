<?php

namespace it\icosaedro\lint\types;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Where;
use it\icosaedro\lint\docblock\DocBlock;
use it\icosaedro\lint\types\ClassConstant;
use it\icosaedro\lint\types\ClassProperty;
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\containers\Hashable;
use it\icosaedro\lint\CaseInsensitiveString;
use it\icosaedro\containers\HashMap;
use it\icosaedro\containers\Arrays;
use RuntimeException;
use CastException;

/**
 * Class type.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/05 15:12:21 $
 */
class ClassType extends Type implements Printable, Comparable, Sortable, Hashable {
	
	const NAME = __CLASS__;

	/**
	 * Fully qualified name of the class.
	 * @var FullyQualifiedName
	 */
	public $name;

	/**
	 * Where it has been declared.
	 * @var Where 
	 */
	public $decl_in;

	/**
	 * No. of usages outside itself.
	 * @var int
	 */
	public $used = 0;
	
	/**
	 * If this class extends Exception.
	 * @var boolean
	 */
	public $is_exception = FALSE;

	/**
	 * This class represents an unchecked exception. PHPLint does not tracks
	 * the propagation of the unchecked exceptions, does not mandates their
	 * declaration in the signature, and does not complains if these exceptions
	 * are not caught.
	 * @var boolean
	 */
	public $is_unchecked = FALSE;

	/**
	 * Dummy forward declaration encountered -- actual class still to be parsed.
	 * @var boolean
	 */
	public $is_forward = FALSE;

	/**
	 * Is this class private to the package where it is defined?
	 * @var boolean
	 */
	public $is_private = FALSE;

	/**
	 * @var boolean 
	 */
	public $is_final = FALSE;

	/**
	 * True if abstract class or interface.
	 * @var boolean 
	 */
	public $is_abstract = FALSE;

	/**
	 * @var boolean 
	 */
	public $is_interface = FALSE;

	/**
	 * Extended class. This field is NULL only for the "object" class and for
	 * the interfaces.
	 * @var ClassType 
	 */
	public $extended;

/*.
	forward void function __construct(FullyQualifiedName $name, Where $where);
	forward boolean function isSubclassOf(ClassType $other);
	forward boolean function isSubclassOfAny(ClassType[int] $others);
	forward boolean function implementsToString();
.*/

	/**
	 * Lists the implemented interfaces (if this is a regular or abstract
	 * class), or lists the extended interfaces (if this is an interface).
	 * The set of these interfaces, joined with the extended class is minimal,
	 * that is none is subclass of another.
	 * PHP allows to declare "... implements BaseInterface, DerivedInterface"
	 * in that order, but gives a fatal error if the order is reversed. This
	 * program is more conservative and forbids redundant interfaces.
	 * The Documentator assumes this set be minimal.
	 * Can be NULL otherwise contains at least one interface.
	 * @var ClassType[int]
	 */
	public $implemented;

	/**
	 * Maps constant names into constants.
	 * @var ClassConstant[string]
	 */
	public $constants;

	/**
	 * Maps property names into properties.
	 * @var ClassProperty[string]
	 */
	public $properties;

	/**
	 * Maps method names (CaseInsensitiveString) into methods (ClassMethod).
	 * @var HashMap 
	 */
	public $methods;

	/**
	 * Constructor method, or null if not available.
	 * @var ClassMethod 
	 */
	public $constructor;

	/**
	 * Set when we encounter "new C(...)": in this case the constructor
	 * invoked can be the default constructor, or the inherited
	 * constructor or the proper constructor of this class. Then, if the
	 * proper constructor gets parsed and this variable is already set, it
	 * means we used the wrong constructor; the programmer needs to define
	 * a forward declaration.
	 * @var Where
	 */
	public $constructor_first_used_here;

	/**
	 * Destructor method.
	 * @var ClassMethod 
	 */
	public $destructor;

	/**
	 * Does the overridding constructor of this class has called
	 * the parent constructor?
	 * @var boolean
	 */
	public $parent_constructor_called = FALSE;

	/**
	 * Does the overridding destructor of this class called the parent
	 * destructor? if not, it is an error.
	 * @var boolean
	 */
	public $parent_destructor_called = FALSE;

	/**
	 * DocBlock of this class, possibly NULL if not available.
	 * @var DocBlock
	 */
	public $docblock;
	
	/**
	 * Singleton instance of the "object" class.
	 * @var ClassType 
	 */
	private static $object_class;
	
	
	/**
	 * Builds a new class type.
	 * @param FullyQualifiedName $name Name of the class.
	 * @param Where $where Where it has been declared.
	 * @return void
	 */
	public function __construct($name, $where){
		$this->name = $name;
		$this->decl_in = $where;
		$this->constants = /*.(ClassConstant[string]).*/ array();
		$this->properties = /*. (ClassProperty[string]) .*/ array();
		$this->methods = new HashMap();
	}
	
	
	/**
	 * Case-insensitive hash of the class name.
	 * @return int 
	 */
	public function getHash(){
		return $this->name->getHash();
	}
	
	
	/**
	 * True if this class is exactly the other one.
	 * @param object $o
	 * @return boolean 
	 */
	public function equals($o){
		// There is always one single instance of a given class.
		return $this === $o;
	}
	
	
	/**
	 * Compares the name of this class with the other.
	 * Non-exception classes come first and are sorted by FQN.
	 * Exception classes are sorted deep-first, then by FQN; in this way
	 * exceptions can be listed in the proper order for the <code>catch(){}</code>
	 * statement.
	 * @param object $o
	 * @return int
	 * @throws CastException 
	 */
	public function compareTo($o){
		if( $o === $this )
			return 0;
		if( $o === NULL )
			throw new CastException("NULL");
		$o2 = cast(__CLASS__, $o);
		if( $this->is_exception ){
			if( $o2->is_exception ){
				if( $this->isSubclassOf($o2) )
					return -1;
				else if( $o2->isSubclassOf($this) )
					return +1;
				else
					return $this->name->compareTo($o2->name);
			} else {
				return +1;
			}
		} else if( $o2->is_exception ){
			return -1;
		} else {
			return $this->name->compareTo($o2->name);
		}
	}
	
	
//	/**
//	 * Returns true if this class has the same prototype of the other.
//	 * The prototype includes: is interface / abstract / class, is private,
//	 * is final, is exception, is unchecked; the extended class and the
//	 * implemented interfaces must be exactly the same. Names and the "is
//	 * forward" flag are not compared.
//	 * @param ClassType $o Other class, typically a prototype.
//	 * @return boolean True if this class has the same prototype of the other.
//	 */
//	public function equalsPrototypeOf($o)
//	{
//		if( ! (
//			$this->is_exception == $o->is_exception
//			&& $this->is_unchecked == $o->is_unchecked
//			&& $this->is_private == $o->is_private
//			&& $this->is_final == $o->is_final
//			&& $this->is_abstract == $o->is_abstract
//			&& $this->is_interface == $o->is_interface
//			&& $this->extended === $o->extended
//			&& count($this->implemented) == count($o->implemented) 
//		) )
//			return FALSE;
//		if( count($this->implemented) == 0 )
//			return TRUE;
//		$a = cast("it\\icosaedro\\lint\\types\\ClassType[int]",
//			Arrays::sort($this->implemented) );
//		$b = cast("it\\icosaedro\\lint\\types\\ClassType[int]",
//			Arrays::sort($o->implemented) );
//		for($i = count($a) - 1; $i >= 0; $i--)
//			if( ! $a[$i]->equals($b[$i]) )
//				return FALSE;
//		return TRUE;
//	}
	
	
	/**
	 * Returns true if this class is a valid actual implementation of the
	 * prototype. The actual implementation may add an extended class and
	 * may add other implemented interfaces. The other flags must be equal:
	 * is interface / abstract / class, is private, is final, is exception,
	 * is unchecked. Names and the "is forward" flag are not compared.
	 * @param ClassType $proto Other class, typically a prototype.
	 * @return boolean True if this class has the same prototype of the other.
	 */
	public function extendsPrototype($proto)
	{
		if( ! (
			$this->is_exception == $proto->is_exception
			&& $this->is_unchecked == $proto->is_unchecked
			&& $this->is_private == $proto->is_private
			&& $this->is_final == $proto->is_final
			&& $this->is_abstract == $proto->is_abstract
			&& $this->is_interface == $proto->is_interface
			&& ($proto->extended === NULL
				|| $proto->extended === self::$object_class
				|| $proto->extended === $this->extended )
			&& count($this->implemented) >= count($proto->implemented) 
		) )
			return FALSE;
		if( count($proto->implemented) == 0 )
			return TRUE;
		// This must contains all the interfaces of the proto:
		foreach($proto->implemented as $proto_iface){
			$found = FALSE;
			foreach($this->implemented as $this_iface){
				if( $this_iface === $proto_iface ){
					$found = TRUE;
					break;
				}
			}
			if( ! $found )
				return FALSE;
		}
		return TRUE;
	}
	
	
	/**
	 * Returns the "prototype" that includes the part before the class members.
	 * @return string Prototype of this class, for example: <code>"private
	 * interface Name extends A implements B, C"</code>.
	 */
	public function prototype()
	{
		$s = "";
		if( $this->is_private )
			$s .= "private ";
		if( $this->is_final )
			$s .= "final ";
		if( $this->is_unchecked )
			$s .= "unchecked ";
		if( $this->is_interface )
			$s .= "interface ";
		else if( $this->is_abstract )
			$s .= "abstract class ";
		else
			$s .= "class ";
		$s .= $this->name;
		if( $this->extended !== NULL && $this->extended !== self::$object_class )
			$s .= " extends " . $this->extended;
		if( count($this->implemented) > 0 )
			$s .= " " . Arrays::implode($this->implemented, ", ");
		return $s;
	}
	
	
	/**
	 * Returns the FQN of this class.
	 * @return string 
	 */
	public function __toString() {
		return $this->name->getFullyQualifiedName();
//		$s = $this->name->getFullyQualifiedName();
//		$c = $this->extended;
//		while($c !== NULL){
//			$s .= "->" . $c->name;
//			$c = $c->extended;
//		}
//		return $s;
	}
	
	
	/**
	 * Returns the singleton instance of the <code>object</code> base class.
	 * @return ClassType 
	 */
	public static function getObject(){
		return self::$object_class;
	}
	
	
	/**
	 * Search a class in an array of classes.
	 * @param ClassType $c Class to search.
	 * @param ClassType[int] $a Array of classes, possibly NULL.
	 * @return int Index of the instance found, or -1 if not found.
	 */
	public static function indexOf($c, $a){
		for($i = count($a) - 1; $i >= 0; $i--)
			if( $a[$i]->equals($c) )
				return $i;
		return -1;
	}
	
	
	/**
	 * Returns true if this class is subclass of the other, possibly also equal.
	 * Every class or interface is subclass of <code>object</code>.
	 * @param ClassType $other Another class, possibly NULL.
	 * @return boolean True if this class is subclass of the other, possibly
	 * also equal.
	 */
	public function isSubclassOf($other){
		if( $other === NULL )
			return FALSE;
		if( $this === $other )
			return TRUE;
		if( $other->is_final )
			return FALSE;
		
		if( $this->is_interface ){
			if( $other->is_interface ){
				if( $this->implemented !== NULL ){
					foreach($this->implemented as $iface){
						if( $iface === $other || $iface->isSubclassOf($other) )
							return TRUE;
					}
				}
			} else if( $other === self::$object_class ){
				return TRUE;
			}
		} else {
			if( $other->is_interface ){
				if( $this->extended !== NULL
				&& $this->extended->isSubclassOf($other) )
					return TRUE;
				if( $this->implemented !== NULL ){
					foreach($this->implemented as $iface){
						if( $iface === $other || $iface->isSubclassOf($other) )
							return TRUE;
					}
				}
			} else {
				$c = $this->extended;
				while( $c !== $other && $c !== NULL )
					$c = $c->extended;
		
		if( $c !== $other && $other->name->getName() === "Object_"
		&& $this === self::$object_class )
			throw new RuntimeException("object is not subclass of Object_");
		
				return $c === $other;
			}
		}
		return FALSE;
	}
	
	
	/**
	 * Returns true if this class is strictly superclass of the other, not equal.
	 * Note that a class is subclass of itself, but it is not superclass of
	 * itself. The <code>object</code> base class is superclass of every other
	 * class or interface.
	 * @param ClassType $other Another class, possibly NULL.
	 * @return boolean True if this class is strictly superclass of the other.
	 */
	public function isSuperclassOf($other){
		if( $other === NULL )
			return FALSE;
		if( $this === $other )
			return FALSE;
		return $other->isSubclassOf($this);
	}
	
	
	/**
	 * Returns true if this class is subclass of at least one of the given
	 * classes.
	 * @param ClassType[int] $others Other classes, possibly NULL or empty.
	 * @return boolean True if this class is subclass of at least one of the
	 * given classes.
	 */
	public function isSubclassOfAny($others){
		if( $others === NULL )
			return FALSE;
		for($i = count($others) - 1; $i >= 0; $i--)
			if( $this->isSubclassOf($others[$i]) )
				return TRUE;
		return FALSE;
	}
	
	
	/**
	 * Static initializer of this class, do not call. 
	 */
	public static function static_init(){
		self::$object_class = new ClassType(
			new FullyQualifiedName("object", FALSE),
			Where::getSomewhere());
	}
	
	
	/**
	 * Returns true if this (right hand side) is subclass the left hand side
	 * (LHS), or the LHS is mixed or unknown.
	 * PHP allows implicit conversion to string for objects implementing
	 * __toString(), but currently it allows to do that only from some contexts,
	 * for example inside "echo" and inside literal string. Since here we don't
	 * know which context we are called from, we must be strict. See also
	 * {@link it\icosaedro\lint\Result::implementsToString()} which just serves
	 * to this pourpose.
	 * @param Type $lhs Type of the LHS.
	 * @return boolean True if this type is assignable to the LHS type.
	 */
	public function assignableTo($lhs){
		if( ($lhs instanceof MixedType)
		|| ($lhs instanceof UnknownType) )
			return TRUE;
		if( ! ($lhs instanceof ClassType) )
			return FALSE;
		$lhs_class = cast(__CLASS__, $lhs);
		return $this->isSubclassOf($lhs_class);
	}
	
	
	/**
	 * Search a constant, first looking in this class, then in the extended
	 * classes, and finally in the implemented interfaces.
	 * @param string $name Name of the constant.
	 * @return ClassConstant Found constant, or NULL if not found.
	 */
	public function searchConstant($name){
		if( array_key_exists($name, $this->constants) )
			return $this->constants[$name];
		
		if( $this->extended !== NULL ){
			$c = $this->extended->searchConstant($name);
			if( $c !== NULL )
				return $c;
		}
		
		for($i = count($this->implemented) - 1; $i >= 0; $i--){
			$c = $this->implemented[$i]->searchConstant($name);
			if( $c !== NULL )
				return $c;
		}
		
		return NULL;
	}
	
	
	/**
	 * Search a property, first looking in this class, then in the extended
	 * classes.
	 * @param string $name Name of the property, without leading dollar sign.
	 * @return ClassProperty Found property, or NULL if not found.
	 */
	public function searchProperty($name){
		if( array_key_exists($name, $this->properties) )
			return $this->properties[$name];
		if( $this->extended === NULL )
			return NULL;
		else
			return $this->extended->searchProperty($name);
	}
	
	
	/**
	 * Search a method, first looking in this class, then in the extended
	 * classes, and finally in the interfaces, in this order.
	 * @param CaseInsensitiveString $name Name of the method.
	 * @return ClassMethod Method found, or NULL if not found.
	 */
	public function searchMethod($name)
	{
		$m = cast(ClassMethod::NAME, $this->methods->get($name));
		if( $m !== NULL )
			return $m;
		
		if( $this->extended !== NULL ){
			$m = $this->extended->searchMethod($name);
			if( $m !== NULL )
				return $m;
		}
		
		if( $this->implemented !== NULL ){
			foreach($this->implemented as $iface){
				$m = $iface->searchMethod($name);
				if( $m !== NULL )
					return $m;
			}
		}
		
		return NULL;
	}
	
	
	/**
	 * Returns the first parent constructor.
	 * @return ClassMethod First parent constructor, or NULL.
	 */
	public function parentConstructor()
	{
		$c = $this->extended;
		while( $c !== NULL ){
			if( $c->constructor !== NULL )
				return $c->constructor;
			$c = $c->extended;
		}
		return NULL;
	}
	
	
	/**
	 * Returns the first parent destructor.
	 * @return ClassMethod First parent destructor, or NULL.
	 */
	public function parentDestructor()
	{
		$c = $this->extended;
		while( $c !== NULL ){
			if( $c->destructor !== NULL )
				return $c->destructor;
			$c = $c->extended;
		}
		return NULL;
	}
	
	
	public function implementsToString()
	{
		return $this->searchMethod(ClassMethod::$TO_STRING_NAME) !== NULL;
	}

}


ClassType::static_init();
