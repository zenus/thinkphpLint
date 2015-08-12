<?php

namespace it\icosaedro\lint\statements;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\ClassConstant;
use it\icosaedro\lint\types\ClassMethod;

/**
 * This class holds several methods that check inheritance in a sub-tree of
 * classes.
 * <p>
 * Class constants cannot collide (PHP limitation).
 * <p>
 * Properties defined in concrete or abstract classes cannot collide (PHPLint
 * quite arbitrary restriction). Interfaces cannot define properties.
 * <p>
 * Colliding methods inherited from the implemented interfaces must have
 * the same exact prototype, that is the same return type, same number and
 * type of the arguments and same errors and exception (PHPLint restriction).
 * The prototype of the implementing and overriding concrete methods must be
 * call-compatible with the interface, abstract or overridden method (PHPLint
 * restriction).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/18 22:35:34 $
 */
class ClassInheritance {
	
	
	/**
	 * Adds an interface to a class or interface checking for redundant
	 * parent interfaces, so that the resulting set of inherited interfaces be
	 * minimal. Example: if IF2 extends IF2, then adding both interfaces to
	 * a class is redundant and IF1 will be signaled as error. PHP already
	 * signals an error on "... implements IF2, IF1" but here we hare even more
	 * conservative and we forbid also "... implements IF1, IF2" so that the
	 * set of implemented interfaces results minimal and the Documentator
	 * will not show repetitions. Also the inheritance checks become faster
	 * if reduncies are removed.
	 * @param ClassType $c Class or interface.
	 * @param ClassType $iface 
	 * @return string Empty string if the new interface has been added with
	 * success, or the error message otherwise.
	 */
	public static function addInterfaceToClass($c, $iface)
	{
		$err = "";
		if( $c->extended !== NULL
		&& $c->extended->isSubclassOf($iface) ){
			return "redundat interface $iface already inherited from "
			. $c->extended;
		}
		
		/*
		 * Collect here interfaces that are already extended by $iface.
		 * PHP allows "... implements BaseInterface, DerivedInterface" but
		 * gives fatal error if the order is reversed. Here we are more
		 * conservative, so that the set of interface results minimal and the
		 * order of the listed interfaces does not matter.
		 */
		$err = "";
		if( $c->implemented !== NULL ){
			
			for($i = count($c->implemented) - 1; $i >= 0; $i--){
				if( $c->implemented[$i]->isSubclassOf($iface) ){
					return "redundant interface $iface already extended by "
					. $c->implemented[$i];
				}
			}
			
			for($i = count($c->implemented) - 1; $i >= 0; $i--){
				if( $iface->isSubclassOf($c->implemented[$i]) ){
					$err .= " " . $c->implemented[$i];
					$c->implemented[$i] = NULL;
				}
			}
			if( $err !== "" ){
				// recover NULL entries from $c->implemented:
				$impl = /*. (ClassType[int]) .*/ array();
				foreach($c->implemented as $a){
					if( $a !== NULL ){
						$impl[] = $a;
					}
				}
				$c->implemented = $impl;
			}
		}
		$c->implemented[] = $iface;
		if( $err === "" )
			return "";
		else
			return "redundant interface(s)$err already implemented by $iface. PHP allows BaseInterface, DerivedInterface in that order, but gives fatal error is the order is reversed. PHPLint is more conservative and requires a minimal set of interfaces.";
	}
	
	
	private static function hasTwoOrMoreParents(/*. ClassType .*/ $c)
	{
		$n = count($c->implemented);
		if( $n > 1 )
			return TRUE;
		if( $c->extended !== NULL && $c->extended !== ClassType::getObject() )
			$n++;
		return $n > 1;
	}
	
	
	/**
	 * Collects all constants from the specified class and all the parents.
	 * @param ClassType $c 
	 * @return ClassConstant[int]
	 */
	private static function collectConstants($c)
	{
		$consts = /*. (ClassConstant[int]) .*/ array();
		foreach($c->constants as $co)
			$consts[] = $co;
		if( $c->extended !== NULL )
			foreach( self::collectConstants($c->extended) as $co )
				$consts[] = $co;
		if( $c->implemented !== NULL )
			foreach($c->implemented as $impl)
				foreach( self::collectConstants($impl) as $co )
					$consts[] = $co;
		return $consts;
	}
	

	/**
	 * Check collisions between constants inherited from the extended class and
	 * from the implemented interfaces. This method MUST be calles just once
	 * the list of the extended and implemented interfaces has been parsed but
	 * befire the parsing of the class body itself (constants defined in the
	 * class itself are checked for collisions when will be parsed next, one
	 * by one).
	 * @param ClassType $c Interface, abstract or concrete class being parsed.
	 * @return string Detailed report of the colliding constants, or the empty
	 * string if no collisions.
	 */
	public static function checkCollidingConstants($c)
	{
		if( ! self::hasTwoOrMoreParents($c) )
			return "";
		
		$report = "";
		// Since this class is (should be...) still empty, the following
		// method will return all the inherited constants:
		$consts = self::collectConstants($c);
		for($i = count($consts) - 1; $i >= 1; $i--){
			$a = $consts[$i];
			for($j = $i - 1; $j >= 0; $j--){
				$b = $consts[$j];
				if($a->name === $b->name)
					$report .= "\n$a <---> $b";
			}
		}
		return $report;
	}
	
	
	/**
	 * Adds all the methods of the specified class and all the inherited
	 * methods from the extended class and implemented interfaces..
	 * @param ClassType $c
	 * @param ClassMethod[int] & $methods Collected methods. The methods of
	 * this class are added first, the those inherited from the extended class,
	 * and then interface methods, in this order, recursively. In this way
	 * concrete methods come first, starting from the most deeply derived ones.
	 * Interface methods are listed at the end, with no particular order.
	 * @return void
	 */
	private static function collectMethods($c, & $methods)
	{
		foreach($c->methods as $mm){
			$m = cast(ClassMethod::NAME, $mm);
			$methods[] = $m;
		}
		if( $c->extended !== NULL )
			self::collectMethods($c->extended, $methods);
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				self::collectMethods($iface, $methods);
	}
	
	
	/**
	 * Checks incompatible inherited concrete, abstract and interface methods.
	 * This function MUST be called just once the parsing of "extends" and
	 * "implements" is finished but before the parsing of the actual methods
	 * of the class. In other words, here we check if the set of all the
	 * methods collected from the extended class and implemented interfaces is
	 * self-consistent.
	 * @param ClassType $c Class or interface whose inherited methods has to
	 * checked.
	 * @return string Empty string if ok, or a detailed report of the
	 * incompatible methods found.
	 */
	public static function checkIncompatibleInheritedMethods($c)
	{
		if( ! self::hasTwoOrMoreParents($c) )
			return "";
		
		// Collects all inherited interface methods:
		$methods = /*.(ClassMethod[int]).*/ array();
		
		// We are interested only on inherited methods, but note that the
		// method called below tries to collect also methods of this
		// class $c, which is still empty, so it does not hurt.
		self::collectMethods($c, $methods);
		
		// We scan the array of the methods starting from its end, were there
		// are interface methods, and continuing backward to the concrete,
		// possibly implementing methods.
		$res = "";
		for($i = count($methods) - 2; $i >= 1; $i--){
			$a = $methods[$i];
			if( $a->is_constructor || $a->is_destructor )
				continue;
			for($j = $i - 1; $j >= 0; $j--){
				$b = $methods[$j];
				
				if( $b->is_constructor || $b->is_destructor )
					continue;
				
				if( ! $a->name->equals($b->name) )
					continue;
				
				if( $a->name->__toString() !== $b->name->__toString() )
					$res .= "\nCheck case spelling of $b against $a.";

				// Since $b precedes $a in the array, $b may implement $a:
				if( $b->class_->is_interface ){
					// also $a is an interface method, then signatures must match:
					if( ! $b->equalsPrototypeOf($a) )
						$res .= "\nInterface method $a with prototype\n"
						. $a->prototype()
						. "\ndoes not match $b with prototype\n"
						. $b->prototype();

				} else {
					// $b is concrete or abstract, so it must be call-compatible
					// with $a:
					$err = $b->callCompatibleWithReason($a);
					if( $err !== "" )
						$res .= "\nMethod $a with prototype\n"
						. $a->prototype()
						. "\nis not call-compatible with $b with prototype\n"
						. $b->prototype()
						. "\n($err)";
				}
			}
		}
		return $res;
	}
	
	
	/**
	 * Checks if the given method overrides, implements or joins any method
	 * of the given concrete, abstract or interface class.
	 * A concrete method that overrides another concrete method or that
	 * implements an abstract or interface method, must be call-compatible.
	 * Two abstract or interface methods with the same name must "join"
	 * togheter, that is must ave the same exact prototype.
	 * @param ClassMethod $m Method.
	 * @param ClassType $c Recursivley explores this class looking for
	 * overridden or implemented methods.
	 * @return string Empty string if ok, or the description of the errors
	 * found.
	 */
	private static function checkImplementedOrOverriddenInClass($m, $c)
	{
		$res = "";
		$found = FALSE;
		foreach($c->methods as $mo){ // $mo = (mixed) overriding/implementing method
			
			$o = cast(ClassMethod::NAME, $mo);
			
			if( $o->is_constructor || $o->is_destructor )
				continue;
			
			if( ! $o->name->equals($m->name) )
				continue;
				
			if( $o->name->__toString() !== $m->name->__toString() )
				$res .= "\nname does not match exactly $o";

			if( $m->class_->is_interface ){
				if( ! $m->equalsPrototypeOf($o) )
					$res .= "\ndoes not match $o with prototype\n"
					. $o->prototype();

			} else {
				$err = $m->callCompatibleWithReason($o);
				if( $err !== "" )
					$res .= "\nis not call-compatible with $o with prototype\n"
					. $o->prototype()
					. "\n($err)";
			}
			$found = TRUE;
			break;
		}
		if( ! $found && $c->extended !== NULL )
			$res .= self::checkImplementedOrOverriddenInClass($m, $c->extended);
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				$res .= self::checkImplementedOrOverriddenInClass($m, $iface);
		return $res;
	}
	
	
	/**
	 * Checks if the method properly "joins" the set of methods from extended
	 * classes and implemented interfaces. If this method belongs to a concrete
	 * class, its prototype must be call-compatible with any other inherited
	 * method. If this method belongs to an interface, its prototype must be
	 * exactly the same of any other inherited method with same name.
	 * @param ClassMethod $m Method to check.
	 * @return string Empty string or the reason why some abstract or interface
	 * method is not properly implemented.
	 */
	public static function checkImplementedOrOverridden($m)
	{
		if( $m->is_constructor || $m->is_destructor )
			return "";
		$c = $m->class_;
		$res = "";
		if( $c->extended !== NULL )
			$res .= self::checkImplementedOrOverriddenInClass($m, $c->extended);
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				$res .= self::checkImplementedOrOverriddenInClass($m, $iface);
		return $res;
	}
	
	
	/**
	 * Checks if the class passed as first argument ($c) can resolve all the
	 * abstract and interface methods inherited from the parent class.
	 * @param ClassType $c Concrete class to check.
	 * @param ClassType $parent_class Parent class.
	 * @return string Empty string if all ok, or a detailed description of the
	 * methods whose implementation is missing.
	 */
	private static function missingImplementationsInClass($c, $parent_class)
	{
		$res = "";
		// Check abstract methods in parent class:
		if( $parent_class->is_abstract || $parent_class->is_interface ){
			// 
			foreach($parent_class->methods as $mpm){
				$pm = cast(ClassMethod::NAME, $mpm);
				if( $pm->is_abstract ){
					$m = $c->searchMethod($pm->name);
					if( $m === NULL || $m->is_abstract )
						$res .= "\n$pm";
				}
			}
		}
		if( $parent_class->extended !== NULL )
			$res .= self::missingImplementationsInClass($c, $parent_class->extended);
		if( $parent_class->implemented !== NULL )
			foreach($parent_class->implemented as $iface)
				$res .= self::missingImplementationsInClass($c, $iface);
		return $res;
	}
	
	
	/**
	 * Checks for missing implementation of abstract and interface methods in
	 * concrete class.
	 * @param ClassType $c Class to check.
	 * @return string Empty string if all ok, or a detailed description of the
	 * methods whose implementation is missing. If the class is not concrete
	 * (that is, if abstract or interface) nothing is done and the empty string
	 * is returned.
	 */
	public static function missingImplementations($c)
	{
		if( $c->is_interface || $c->is_abstract )
			return "";
		$res = "";
		if( $c->extended !== NULL )
			$res .= self::missingImplementationsInClass($c, $c->extended);
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				$res .= self::missingImplementationsInClass($c, $iface);
		return $res;
	}

}
