<?php

namespace it\icosaedro\lint\types;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\utils\Strings;
use it\icosaedro\regex\Pattern;
use it\icosaedro\lint\Scanner;
use it\icosaedro\lint\ClassResolver;
use it\icosaedro\lint\Logger;
use it\icosaedro\lint\Where;

/**
 * Parses a type descriptor given as a string. Used to parse DocBlock types
 * and the first argument of the magic <code>cast()</code> function.
 * A type descriptor is a list of one or more types separated by a vertical bar:
 *
 * <blockquote><pre>
 * type_descriptor = type {"|" type};
 * </pre></blockquote>
 *
 * where:
 *
 * <blockquote><pre>
 * type = name | array_old_syntax | array_new_syntax;
 * 
 * array_old_syntax = "array" [ index {index} [name] ];
 * 
 * array_new_syntax = name index {index};
 * 
 * index = "[]" | "[int]" | "[string]";
 * 
 * name = "void"     | "bool"   | "boolean" | "int" | "integer"
 *      | "float"    | "double" | "real"    | "string"
 *      | "resource" | "mixed"  | "object"  | class_name
 *      | "self"     | "parent"
 *      | "FALSE"    | "NULL";
 * 
 * class_name = ["\\"] identifier {"\\" identifier};
 * </pre></blockquote>
 * 
 * Multiple types separated by a vertical bar, for example
 * <code>int|string</code> is allowed, but only the first type is returned
 * and an error is signaled because mutable types are not supported by PHPLint.
 * 
 * Exceptionally <code>FALSE</code> and <code>NULL</code> are allowed as type
 * names and returned as <code>boolean</code> and <code>mixed</code>
 * respectively, but an error is signaled. Again, such bad "types" are used
 * typically along with multiple types separated by vertical bar.
 * 
 * Names matching is case-sensitive; improperly mixing upper- and lower-case
 * letters is signaled as an error, but the expected type is returned anyway.
 * 
 * Examples:
 * 
 * <blockquote><pre>
 * int
 * resource|FALSE
 * float[int][int]
 * array[int][int]float
 * Exception
 * \some\name\space\MyClass
 * </pre></blockquote>
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/23 18:07:52 $
 */
class TypeDescriptor {
	
	
	/**
	 * Cached pattern matching a name.
	 * @var Pattern
	 */
	private static $name_pattern;
	
	/**
	 * Cached pattern matching an ASCII name.
	 * @var Pattern
	 */
	private static $ascii_name_pattern;
	
	/**
	 * Cached pattern matching old array type syntax:
	 * array[K2][K1]E.
	 * @var Pattern
	 */
	private static $old_array_pattern;
	
	/**
	 * Cached pattern matching new array type syntax:
	 * E[K2][K1]
	 * @var Pattern
	 */
	private static $new_array_pattern;
	
	
	/**
	 * Parses a name.
	 * @param Logger $logger
	 * @param Where $where
	 * @param string $name Anything ranging from a simple "int" up to a fully
	 * qualified class name.
	 * @param ClassResolver $resolver Class name resolver.
	 * @param boolean $is_fqn
	 * @return Type
	 */
	private static function parseName($logger, $where, $name, $resolver, $is_fqn){
		
		if( Scanner::$ascii_ext_check && ! self::$ascii_name_pattern->match($name) )
			$logger->error($where, "non-ASCII characters in identifier: "
			. Strings::toLiteral($name));
		
		$t = /*.(Type).*/ NULL;
		$name_low = strtolower($name);
		switch("X$name_low"){
		case "Xvoid":     $t = VoidType::getInstance(); break;
		case "Xboolean":
		case "Xbool":     $t = BooleanType::getInstance(); break;
		case "Xint":
		case "Xinteger":  $t = IntType::getInstance(); break;
		case "Xfloat":
		case "Xdouble":
		case "Xreal":     $t = FloatType::getInstance(); break;
		case "Xstring":   $t = StringType::getInstance(); break;
		case "Xmixed":    $t = MixedType::getInstance(); break;
		case "Xresource": $t = ResourceType::getInstance(); break;
		case "Xobject":   $t = ClassType::getObject(); break;
		case "Xarray":
			$t = ArrayType::factory(MixedType::getInstance(), MixedType::getInstance());
			break;
		
		// Detects some common mistakes:
		case "Xnull":
			$logger->error($where, "`$name' is not a type - assuming mixed");
			$t = MixedType::getInstance();
			break;
		case "Xfalse":
			$logger->error($where, "`$name' is not a type - assuming boolean");
			$t = BooleanType::getInstance();
			break;
		
		default:
		}
		if( $t !== NULL ){
			// FIXME: FALSE and NULL are upper-case under PHPLint
			if( $name !== $name_low )
				$logger->error($where, "spelling check: expected `$name_low' but found `$name'");
			return $t;
		}
		$c = $resolver->searchClass($name, $is_fqn);
		if( $c === NULL ){
			$logger->error($where, "unknown type $name");
			return UnknownType::getInstance();
		} else {
			$resolver->accountClass($c);
			return $c;
		}
	}
	
	
	/**
	 * Build an array of a given index type and elements type.
	 * @param string $i Index specifier, one of "[]", "[int]", "[string]".
	 * @param Type $e Type of the elements.
	 * @return Type Array of the requested type.
	 * @throws \InvalidArgumentException Unexpected argument $i.
	 */
	private static function buildArray($i, $e){
		$t = /*.(Type).*/ NULL;
		if( $i === "[]" )
			$t = MixedType::getInstance();
		else if( $i === "[int]" )
			$t = IntType::getInstance();
		else if( $i === "[string]" )
			$t = StringType::getInstance();
		else
			// Cannot happen, regex already assures the key type be valid:
			throw new \InvalidArgumentException("not an index specifier");
		return ArrayType::factory($t, $e);
	}
	
	
	/**
	 * Parses a type descriptor.
	 * @param Logger $logger
	 * @param Where $where Location in the source. Error messages are reported
	 * through this object.
	 * @param string $s Type descriptor. NULL and empty strings are signaled
	 * as errors.
	 * @param boolean $resolve_ns True if identifiers and qualified names can
	 * be resolved in the current namespace; false if all the names must be
	 * fully qualified. Namespace resolution can be performed in DocBlocks.
	 * the <code>cast()</code> requires a fully qualified namespace.
	 * @param ClassResolver $resolver Class name resolver.
	 * @param boolean $is_fqn If true, assumes the name be already fully
	 * qualified or absolute and does not applies the namespace resolution
	 * algorithm. Set to true only to resolve classes in the magic
	 * <code>cast(T,V)</code>, where <code>T</code> must be resolvable
	 * at runtime outside the current namespace context.
	 * @return Type Parses type. On error, the {@link it\icosaedro\lint\types\UnknownType} singleton
	 * instance is returned instead.
	 */
	public static function parse($logger, $where, $s, $resolve_ns, $resolver,
		$is_fqn){
		if( self::$name_pattern === NULL ){
			// Sub-pattern matching an ASCII identifier:
			$id_ascii = "{_a-zA-Z}{_a-zA-Z0-9}*";
			self::$ascii_name_pattern = new Pattern("\\\\?$id_ascii(\\\\$id_ascii)*\$");
			
			// Sub-pattern matching an identifier:
			$id = "{_a-zA-Z\x80-\xff}{_a-zA-Z\x80-\xff0-9}*";
			// Sub-pattern matching a name:
			$name = "\\\\?$id(\\\\$id)*";
			// Sub-pattern matching array index type:
			$idx = "\\[\\]|\\[int\\]|\\[string\\]";
			self::$name_pattern = new Pattern("$name\$");
			self::$old_array_pattern = new Pattern("({aA}{rR}{rR}{aA}{yY})($idx)+($name)?\$");
			self::$new_array_pattern = new Pattern("($name)($idx)+\$");
		}
		
		if( self::$name_pattern->match($s) ){
			return self::parseName($logger, $where, $s, $resolver, $is_fqn);
		
		} else if( self::$old_array_pattern->match($s) ){
			$p = self::$old_array_pattern;
			// Copies indeces types in an array outside the Pattern obj because
			// element class might trigger autoloading of another package that
			// might overwrite this Pattern variable:
			$indeces = /*.(string[int]).*/ array();
			$g = $p->group(1);
			for($i = 0; $i < $g->count(); $i++)
				$indeces[] = $g->elem($i)->value();
			$a = $p->group(0)->elem(0)->value();
			if( $a !== "array" )
				$logger->error($where, "spelling check: expected `array' but found `$a'");
			// Resolves element type:
			$t = /*.(Type).*/ NULL;
			if( $p->group(2)->count() == 0 )
				$t = MixedType::getInstance();
			else
				$t = self::parseName($logger, $where, $p->group(2)->elem(0)->value(), $resolver, $is_fqn);
			// Builds array if there are indeces:
			for($i = count($indeces) - 1; $i >= 0; $i--)
				$t = self::buildArray($indeces[$i], $t);
			return $t;
			
		} else if( self::$new_array_pattern->match($s) ){
			$p = self::$new_array_pattern;
			// Copies indeces types in an array outside the Pattern obj because
			// element class might trigger autoloading of another package that
			// might overwrite this Pattern variable:
			$indeces = /*.(string[int]).*/ array();
			$g = $p->group(1);
			for($i = 0; $i < $g->count(); $i++)
				$indeces[] = $g->elem($i)->value();
			// Resolves element type:
			$t = self::parseName($logger, $where, $p->group(0)->elem(0)->value(), $resolver, $is_fqn);
			// Builds array if there are indeces:
			for($i = count($indeces) - 1; $i >= 0; $i--)
				$t = self::buildArray($indeces[$i], $t);
			return $t;
			
		} else {
			$logger->error($where, "invalid type syntax: " . Strings::toLiteral($s));
			return UnknownType::getInstance();
		}
		
	}
	
}
