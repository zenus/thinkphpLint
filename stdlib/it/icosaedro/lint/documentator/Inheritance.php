<?php

namespace it\icosaedro\lint\documentator;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\ClassConstant;
use it\icosaedro\lint\types\ClassProperty;
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\containers\Arrays;
use it\icosaedro\containers\HashSet;

/**
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/18 22:37:58 $
 */
class Inheritance {

	/**
	 * Collects all the non-private constants from this class and its parents.
	 * @param ClassType $c Collects contants from this class and its parents.
	 * @param ClassConstant[int] & $res Found constants are added here.
	 * @return void
	 */
	private static function inheritedConstantsRecurse($c, & $res)
	{
		// From this class:
		if( $c->constants !== NULL )
			foreach($c->constants as $co)
				if( $co->visibility !== Visibility::$private_ )
					$res[] = $co;
		// From extended:
		if( $c->extended !== NULL )
			self::inheritedConstantsRecurse($c->extended, $res);
		// From implemented:
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				self::inheritedConstantsRecurse($iface, $res);
	}

	/**
	 * Returns the list of the inherited, non-private constants.
	 * @param ClassType $c Class that inherits.
	 * @return ClassConstant[int] Inherited, non-private constants, in
	 * alphabetical order (locale aware).
	 */
	public static function inheritedConstants($c)
	{
		$res = /*.(ClassConstant[int]).*/ array();
		if( $c->extended !== NULL )
			self::inheritedConstantsRecurse($c->extended, $res);
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				self::inheritedConstantsRecurse($iface, $res);
		return cast(ClassConstant::NAME."[int]", Arrays::sort($res));
	}
	

	/**
	 * Collects all the non-private properties from this class and its parents.
	 * @param ClassType $c Collects properties from this class and its parents.
	 * @param ClassProperty[int] & $res Found properties are added here.
	 * @return void
	 */
	private static function inheritedPropertiesRecurse($c, & $res)
	{
		// From this class:
		if( $c->properties !== NULL )
			foreach($c->properties as $p)
				if( $p->visibility !== Visibility::$private_ )
					$res[] = $p;
		if( $c->extended !== NULL )
			self::inheritedPropertiesRecurse($c->extended, $res);
	}
	

	/**
	 * Returns the list of the inherited, non-private properties.
	 * @param ClassType $c Class that inherits.
	 * @return ClassProperty[int] Inherited, non-private properties, in
	 * alphabetical order (locale aware).
	 */
	public static function inheritedProperties($c)
	{
		$res = /*.(ClassProperty[int]).*/ array();
		if( $c->extended !== NULL )
			self::inheritedPropertiesRecurse($c->extended, $res);
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				self::inheritedPropertiesRecurse($iface, $res);
		return cast(ClassProperty::NAME."[int]", Arrays::sort($res));
	}
	
	
	/**
	 * Recursively collects inherited methods.
	 * @param ClassType $c Class that inherits.
	 * @param HashSet $established_methods Concrete methods + abstract methods
	 * of the original class. No further methods with the same name are added
	 * to this set or the the list of the abstract methods.
	 * @param ClassMethod[int] & $abstract_methods List of abstract methods to
	 * which new abstract methods found are added, with repetitions.
	 * @return void
	 */
	private static function inheritedMethodsRecurse($c, $established_methods, & $abstract_methods)
	{
		foreach($c->methods as $mm){
			$m = cast(ClassMethod::NAME, $mm);
			if( $m !== $c->constructor ){
				if( $m->visibility !== Visibility::$private_
				&& ! $established_methods->contains($m) ){
					if( $m->is_abstract )
						$abstract_methods[] = $m;
					else
						$established_methods->put($m);
				}
			}
		}
		if( $c->extended !== NULL )
			self::inheritedMethodsRecurse(
				$c->extended, $established_methods, $abstract_methods);
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				self::inheritedMethodsRecurse(
					$iface, $established_methods, $abstract_methods);
	}
	
	
	/**
	 * Returns inherited methods, concrete and abstract. Abstract methods
	 * do not have concrete implementation and there may be repetitions of the
	 * same abstract method name inherited from different interfaces.
	 * @param ClassType $c Class that inherits.
	 * @return ClassMethod[int] Inherited methods, concrete and abstract.
	 */
	public static function inheritedMethods($c)
	{
		// "Established" methods, that is
		// set of methods from this class + inherited concrete methods.
		// These methods are univocal, reason why this variable is a set.
		// Methods of this class will be removed before returning, so only the
		// inherited methods remain.
		$established_methods = new HashSet(); // no duplicates here
		
		// Abstract methods that have no implementation and are not established.
		// There may be duplicated names from different interfaces,
		// reason why this variable is an array.
		$abstract_methods = /*.(ClassMethod[int]).*/ array();
		
		// Adds methods from this class (concrete and abs.) to the established
		// (will be removed later):
		foreach($c->methods as $mm){
			if( $mm !== $c->constructor )
				$established_methods->put($mm);
		}
		
		// Follow recursively the extended chain first, so that concrete
		// methods are available before the abstract ones:
		if( $c->extended !== NULL )
			self::inheritedMethodsRecurse(
				$c->extended, $established_methods, $abstract_methods);
		
		// ... then continue with the interfaces:
		if( $c->implemented !== NULL )
			foreach($c->implemented as $iface)
				self::inheritedMethodsRecurse(
					$iface, $established_methods, $abstract_methods);
		
		// Remove methods of this class from the established methods:
		foreach($c->methods as $mm)
			$established_methods->remove($mm);
		
		// Joins established + abstract methods:
		$res = cast(ClassMethod::NAME."[int]", $established_methods->getElements());
		foreach($abstract_methods as $m)
			if( $m !== NULL )
				$res[] = $m;
		
		return cast(ClassMethod::NAME."[int]", Arrays::sort($res));
	}

}
