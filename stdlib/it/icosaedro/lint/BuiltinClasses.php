<?php

namespace it\icosaedro\lint;

require_once __DIR__ . "/../../../all.php";

use it\icosaedro\lint\types\ClassType;

/**
 * Special classes that are built-in in PHP5 are listed here. Fully qualified
 * names of these classes allows the code that parses classes to easily
 * recognize them (the FullyQualifiedName class provides an equals() method
 * just to do that in the right way). Once detected, references to these
 * special classes are stored here.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/04 12:01:38 $
 */
class BuiltinClasses {
	
	private $is_php_5 = FALSE;
	
	/**
	 * Read-only FQN of some special, built-in classes.
	 * @var FullyQualifiedName
	 */
	private static $EXCEPTION_FQN, $TRAVERSABLE_FQN, $ITERATOR_FQN,
		$ITERATORAGGREGATE_FQN, $COUNTABLE_FQN, $ARRAYACCESS_FQN;
	
	/**
	 * Some special classes. These variables are set only if the specific
	 * module is loaded, that is 'standard' and 'spl'. Normally NULL.
	 * @var ClassType
	 */
	public
		/* Classes from standard module: */
		$ExceptionClass,
		/* Classes from spl module: */
		$TraversableClass,
		$IteratorClass,
		$IteratorAggregateClass,
		$CountableClass,
		$ArrayAccessClass;
	
	
	/**
	 * Static initializer, do no call.
	 * @return void
	 */
	public static function static_init()
	{
		self::$EXCEPTION_FQN = new FullyQualifiedName("Exception", FALSE);
		self::$TRAVERSABLE_FQN = new FullyQualifiedName("Traversable", FALSE);
		self::$ITERATOR_FQN = new FullyQualifiedName("Iterator", FALSE);
		self::$ITERATORAGGREGATE_FQN = new FullyQualifiedName("IteratorAggregate", FALSE);
		self::$COUNTABLE_FQN = new FullyQualifiedName("Countable", FALSE);
		self::$ARRAYACCESS_FQN = new FullyQualifiedName("ArrayAccess", FALSE);
	}
	
	
	/**
	 * Set the basic PHP version 4 or 5. Special classes exists only in 5,
	 * so this class does nothing in 4.
	 * @param boolean $is_php_5 True if PHP 5, otherwise it is PHP 4.
	 * @return void
	 */
	public function __construct($is_php_5)
	{
		$this->is_php_5 = $is_php_5;
	}
	
	
	/**
	 * Detects if the given class is one of the special classes and, if so,
	 * store it for later reference.
	 * @param ClassType $c Class parsed right now.
	 * @return void
	 */
	public function detect($c)
	{
		if( ! $this->is_php_5 )
			return;
		if( $c->name->equals(self::$EXCEPTION_FQN) ){
			$this->ExceptionClass = $c;
			$c->is_exception = TRUE;
		} else if( $c->name->equals(self::$TRAVERSABLE_FQN) ){
			$this->TraversableClass = $c;
		} else if( $c->name->equals(self::$ITERATOR_FQN) ){
			$this->IteratorClass = $c;
		} else if( $c->name->equals(self::$ITERATORAGGREGATE_FQN) ){
			$this->IteratorAggregateClass = $c;
		} else if( $c->name->equals(self::$COUNTABLE_FQN) ){
			$this->CountableClass = $c;
		} else if( $c->name->equals(self::$ARRAYACCESS_FQN) ){
			$this->ArrayAccessClass = $c;
		}
	}

}

BuiltinClasses::static_init();
