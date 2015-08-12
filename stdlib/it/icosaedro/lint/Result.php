<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\containers\Printable;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\NullType;
use it\icosaedro\lint\types\VoidType;
use it\icosaedro\lint\types\BooleanType;
use it\icosaedro\lint\types\IntType;
use it\icosaedro\lint\types\FloatType;
use it\icosaedro\lint\types\StringType;
use it\icosaedro\lint\types\UnknownType;
use it\icosaedro\lint\types\ArrayType;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\lint\types\MixedType;
use it\icosaedro\lint\types\ResourceType;

/**
 * Holds type and value as the result of the evaluation of an expression.
 * The objects of this class are immutable: once created, never change.
 * Also implements unary and binary operators to calculate simple static
 * expressions involving int, float and string. Warns if the result of an
 * intermediate calculation overflows the specified platform word size,
 * which may be either 32 bits or 64 bits.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/05 15:09:10 $
 */
final class Result implements Printable {
	
	/**
	 * Type of the result.
	 * @var Type 
	 */
	private $type;
	
	// FIXME: float can be INF and NAN.
	
	/**
	 * Best representation of the result value as a string. NULL if unknown.
	 * FIXME: NULL strings are represented by the string "NULL", which might
	 * be ambigue.
	 * @var string 
	 */
	private $value;
	
	
	/**
	 * Singleton instances of some common types.
	 * @var Type 
	 */
	private static $boolean_type, $int_type;
	
	
	/**
	 * Singleton instances of the most common results. The factory method
	 * returns these avoiding to create a new object every time.
	 * @var Result 
	 */
	private static $unknown_result, $void_result,
			$boolean_unknown_result, $false_result, $true_result,
			$int_unknown_result, $float_unknown_result, $string_unknown_result;
	
	
	/**
	 * Creates a new result. Client code must use the factory method.
	 * @param Type $type
	 * @param string $value 
	 * @return void
	 */
	private function __construct($type, $value){
		$this->type = $type;
		$this->value = $value;
	}
	
	
	/**
	 * Type of the result. It is always set, possibly to
	 * <code>UnknownType</code>.
	 * @return Type 
	 */
	public function getType(){
		return $this->type;
	}
	
	
	/**
	 * Returns the best string representation of the value. Integer numbers
	 * and floating-point numbers are represented in their usual textual form,
	 * in 10 base; float values can also be INF, -INF and NAN. Boolean values
	 * can be "FALSE" or "TRUE". Strings are stored as sequence of bytes,
	 * no quotes and no escapes. Arrays are "array()". NULL is "NULL".
	 * For other types a value may be set, but cannot be furtherly evaluated
	 * by this class.
	 * If NULL, the value is not available, only the type is; this may be
	 * either because the value was not parsed correctly (and an error had been
	 * reported) or the value went through calculations this class cannot
	 * perform and then it has been reset to NULL.
	 * @return string
	 */
	public function getValue(){
		return $this->value;
	}
	
	
	/**
	 * Returns the cached representation of the "unknown" value. The type is
	 * UnknownType and the value is null. Anywhere in the program the type of
	 * an expression cannot be determined, an error is reported and this
	 * result is returned. The unknown type is assignment-compatible with any
	 * other type, so if the same unknown result is used again, no further
	 * errors are reported.
	 * @return Result The cached "unknown" result.
	 */
	public static function getUnknown(){
		return self::$unknown_result;
	}
	
	
	/**
	 * @var Result 
	 */
	private static $null_result;
	
	/**
	 * Returns a result of type and value specified. Common values are stored
	 * internally and returned as singleton instances of this class.
	 * @param Type $type
	 * @param string $value 
	 * @return Result
	 */
	public static function factory($type, $value = NULL){
		if( $type === NULL || $type === UnknownType::getInstance() )
			return self::$unknown_result;
		if( $type instanceof VoidType )
			return self::$void_result;
		if( $type instanceof NullType )
			return self::$null_result;
		if( $type instanceof BooleanType && $value === "FALSE" )
			return self::$false_result;
		if( $type instanceof BooleanType && $value === "TRUE" )
			return self::$true_result;
		if( $type instanceof IntType && $value === NULL )
			return self::$int_unknown_result;
		if( $type instanceof StringType && $value === NULL )
			return self::$string_unknown_result;
		// FIXME: check range numeric values passed for any platform
		return new Result($type, $value);
	}
	
	
	/**
	 * Returns this result as "TYPE(VALUE)" string.
	 * @return string 
	 */
	public function __toString(){
		return $this->type === NULL? "?" : $this->type->__toString()
			. "(" . ($this->value === NULL? "?" : $this->value) . ")";
	}
	
	
	/**
	 * Returns true if this result is an array.
	 */
	public function isArray(){
		return $this->type instanceof ArrayType;
	}
	
	
	/**
	 * Returns true if this result is an empty array. Under PHPLint, empty
	 * arrays can be cast to any more specific array type.
	 */
	public function isEmptyArray(){
		return $this->type instanceof ArrayType && $this->value === "array()";
	}
	
	
	/**
	 * Returns true if this result is a boolean.
	 */
	public function isBoolean(){
		return $this->type === BooleanType::getInstance();
	}
	
	
	/**
	 * Returns true if this result is a boolean FALSE value.
	 */
	public function isFalse(){
		return $this->type === BooleanType::getInstance()
			&& $this->value === "FALSE";
	}
	
	
	/**
	 * Returns true if this result is a boolean TRUE value.
	 */
	public function isTrue(){
		return $this->type === BooleanType::getInstance()
			&& $this->value === "TRUE";
	}
	
	
	/**
	 * Returns true if this result is an object.
	 */
	public function isClass(){
		return $this->type instanceof ClassType;
	}
	
	
	/**
	 * Returns true if this result is a floating point number.
	 */
	public function isFloat(){
		return $this->type === FloatType::getInstance();
	}
	
	
	/**
	 * Returns true if this result is an integer number.
	 */
	public function isInt(){
		return $this->type === IntType::getInstance();
	}
	
	
	/**
	 * Returns true if this result is a mixed.
	 */
	public function isMixed(){
		return $this->type instanceof MixedType;
	}
	
	
	/**
	 * Returns true if this result is the literal NULL value. This value can be
	 * cast to any type that, under PHPLint, allows NULL as a value, that is:
	 * string, array, resource and object.
	 */
	public function isNull(){
		return $this === self::$null_result;
	}
	
	
	/**
	 * Returns true if this result is a resource.
	 */
	public function isResource(){
		return $this->type instanceof ResourceType;
	}
	
	
	/**
	 * Returns true if this result is a string.
	 */
	public function isString(){
		return $this->type === StringType::getInstance();
	}
	
	
	/**
	 * Returns true if this result is of unknown type.
	 */
	public function isUnknown(){
		return $this === self::$unknown_result;
	}
	
	
	/**
	 * Returns true if this result is a void. Only functions and methods may
	 * return such a non-value.
	 */
	public function isVoid(){
		return $this === self::$void_result;
	}
	
	
	/**
	 * Returns true if this result is assignment-compatible with a variable
	 * of the type specified. Basically applies the
	 * {@link it\icosaedro\lint\types\Type::assignableTo()}
	 * method, but also allows the assignment of the empty array to any array
	 * type.
	 * @param Type $type Type of the variable.
	 * @return boolean True if this result is assignment-compatible with a
	 * variable of the type specified.
	 */
	public function assignableTo($type){
		return $this->type->assignableTo($type)
		|| $this->isEmptyArray() && $type instanceof ArrayType;
		
	}
	
	
	/**
	 * Applies the unary plus operator to this result.
	 * @param Logger $logger
	 * @param Where $where
	 * @return Result
	 */
	public function unaryPlus($logger, $where){
		if( $this->isUnknown() || $this->isInt() || $this->isFloat() ){
			return $this;
			
		} else {
			$logger->error($where, "unary plus applied to " . $this->type);
			return self::$unknown_result;
		}
	}
	
	
	/**
	 * Applies the unary minus operator to this result.
	 * @param Logger $logger
	 * @param Where $where
	 * @return Result
	 */
	public function unaryMinus($logger, $where){
		if( $this->isUnknown() )
			return self::$unknown_result;
		
		$v = $this->value;
		if( $this->isInt() ){
			if( $v === NULL )
				return $this;
			// FIXME: check int overflow
			if( $v[0] === "-" )
				$v = substr($v, 1);
			else
				$v = "-$v";
			return new Result(IntType::getInstance(), $v);
			
		} else if( $this->isFloat() ){
			if( $v === NULL )
				return $this;
			if( $v === "INF" )
				$v = "-INF";
			else if( $v === "-INF" )
				$v = "INF";
			else if( $v === "NAN" )
				$v = $v; // :-)
			else if( $v[0] === "+" )
				$v = "-" . substr($v, 1);
			else if( $v[0] === "-" )
				$v = substr($v, 1);
			else
				$v = "-$v";
			return new Result(FloatType::getInstance(), $v);
			
		} else {
			$logger->error($where, "unary minus applied to " . $this->type);
			return self::$unknown_result;
		}
	}
	
	
	/**
	 * 
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other Right-hand side argument.
	 * @return Result
	 */
	public function plus($logger, $where, $other){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		if( $this->isInt() ){
			if( $other->isInt() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					return self::factory(IntType::getInstance(),
						(string) ((int)$this->value + (int)$other->value));
				else
					return self::$int_unknown_result;
			} else if( $other->isFloat() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					// FIXME: check NAN INF
					return self::factory(FloatType::getInstance(),
						(string) ((float)$this->value + (float)$other->value));
				else
					return self::$float_unknown_result;
				
			} else {
				$logger->error($where, "`... + EXPR': expected number but found "
				. $other->type);
			}
				
		} else if( $this->isFloat() ){
			if( $other->isInt() || $other->isFloat() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					// FIXME: check NAN INF
					return self::factory(FloatType::getInstance(),
						(string) ((float)$this->value + (float)$other->value));
				else
					return self::$float_unknown_result;
				
			} else {
				$logger->error($where, "`... + EXPR': expected number but found "
				. $other->type);
			}
		
		} else if( $this->type instanceof ArrayType ){
			if( $other->type instanceof ArrayType ){
				if( $this->type->equals($other->type) )
					return self::factory($this->type);
				else {
					$logger->error($where, "adding arrays of different types: left is "
					. $this->type . ", right is " . $other->type);
					return self::$unknown_result;
				}
			} else {
				$logger->error($where, "undefined addition: left is "
				. $this->type . ", right is " . $other->type);
				return self::$unknown_result;
			}
			
		} else {
			$logger->error($where, "`EXPR + ...': expected number or array but found "
			. $this->type);
		}
		return self::$unknown_result;
	}
	
	
	/**
	 * Evaluates the minus binary operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function minus($logger, $where, $other){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		if( $this->isInt() ){
			if( $other->isInt() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					return self::factory(IntType::getInstance(),
						(string) ((int)$this->value - (int)$other->value));
				else
					return self::$int_unknown_result;
			} else if( $other->isFloat() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					// FIXME: check NAN INF
					return self::factory(FloatType::getInstance(),
						(string) ((float)$this->value - (float)$other->value));
				else
					return self::$float_unknown_result;
				
			} else {
				$logger->error($where, "`... - EXPR': expected number but found "
				. $other->type);
			}
				
		} else if( $this->isFloat() ){
			if( $other->isInt() || $other->isFloat() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					// FIXME: check NAN INF
					return self::factory(FloatType::getInstance(),
						(string) ((float)$this->value - (float)$other->value));
				else
					return self::$float_unknown_result;
				
			} else {
				$logger->error($where, "`... - EXPR': expected number but found "
				. $other->type);
			}
			
		} else {
			$logger->error($where, "`EXPR - ...': expected number but found "
			. $this->type);
		}
		return self::$unknown_result;
	}
	
	
	/**
	 * Evaluates the multiplication binary operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function times($logger, $where, $other){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		if( $this->isInt() ){
			if( $other->isInt() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					return self::factory(IntType::getInstance(),
						(string) ((int)$this->value * (int)$other->value));
				else
					return self::$int_unknown_result;
			} else if( $other->isFloat() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					// FIXME: check NAN INF
					return self::factory(FloatType::getInstance(),
						(string) ((float)$this->value * (float)$other->value));
				else
					return self::$float_unknown_result;
				
			} else {
				$logger->error($where, "`... * EXPR': expected number but found "
				. $other->type);
			}
				
		} else if( $this->isFloat() ){
			if( $other->isInt() || $other->isFloat() ){
				if( $this->value !== NULL && $other->value !== NULL )
					// FIXME: check overflow
					// FIXME: check NAN INF
					return self::factory(FloatType::getInstance(),
						(string) ((float)$this->value * (float)$other->value));
				else
					return self::$float_unknown_result;
				
			} else {
				$logger->error($where, "`... * EXPR': expected number but found "
				. $other->type);
			}
			
		} else {
			$logger->error($where, "`EXPR * ...': expected number but found "
			. $this->type);
		}
		return self::$unknown_result;
	}
	
	
	/**
	 * Evaluates the division operator. Under PHPLint, division is assumed
	 * to always return a float number, also when at runtime actually an
	 * int is generated. For example, "4/2" gives int(2), but "3/2" gives
	 * float(1.5).
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function divide($logger, $where, $other){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		if( ($this->isInt() || $this->isFloat())
		&& ($other->isInt() || $other->isFloat()) ){
			if( $this->value !== NULL && $other->value !== NULL )
				// FIXME: check overflow
				// FIXME: check NAN INF
				// FIXME: check div by zero
				return self::factory(FloatType::getInstance(),
					(string) ((float)$this->value / (float)$other->value));
			else
				return self::$float_unknown_result;
			
		} else {
			$logger->error($where, $this->type . " / " . $other->type
			. ": invalid type(s)");
			return self::$unknown_result;
		}
	}
	
	
	/**
	 * Evaluates the modulus operator. Under PHPLint, modulus is allowed only
	 * between int numbers.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function modulus($logger, $where, $other){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		if( $this->isInt() && $other->isInt() ){
			if( $this->value !== NULL && $other->value !== NULL ){
				$m = (int) $other->value;
				if( $m == 0 ){
					$logger->error($where, "modulus by zero");
					return self::$int_unknown_result;
				} else {
					return self::factory(IntType::getInstance(),
						(string) ((int)$this->value % $m));
				}
			} else {
				return self::$int_unknown_result;
			}
			
		} else {
			$logger->error($where, $this->type . " % " . $other->type
			. ": invalid type(s), expected int for both sides");
			return self::$unknown_result;
		}
	}
	
	
	/**
	 * Evaluates the left shift operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function leftShift($logger, $where, $other){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		if( $this->isInt() && $other->isInt() ){
			if( $this->value !== NULL && $other->value !== NULL ){
				return self::factory(IntType::getInstance(),
					(string) ((int)$this->value << (int) $other->value));
			} else {
				return self::$int_unknown_result;
			}
			
		} else {
			$logger->error($where, $this->type . " << " . $other->type
			. ": invalid type(s)");
			return self::$unknown_result;
		}
	}
	
	
	/**
	 * Evaluates the right shift operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function rightShift($logger, $where, $other){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		if( $this->isInt() && $other->isInt() ){
			if( $this->value !== NULL && $other->value !== NULL ){
				return self::factory(IntType::getInstance(),
					(string) ((int)$this->value >> (int) $other->value));
			} else {
				return self::$int_unknown_result;
			}
			
		} else {
			$logger->error($where, $this->type . " >> " . $other->type
			. ": invalid type(s)");
			return self::$unknown_result;
		}
	}
	
	
	/**
	 * Returns true if the argument is a class that implements __toString().
	 * From user code, the same test should be performed checking for
	 * subclass of {@link it\icosaedro\containers\Printable}.
	 * @param Type $x 
	 * @return boolean True if $x is a class that implements the __toString()
	 * method; false in any other case.
	 */
	public static function implementsToString($x){
		if( ! $x instanceof ClassType )
			return FALSE;
		$c = cast(ClassType::NAME, $x);
		return $c->implementsToString();
	}
	
	
	/**
	 * Evaluate implicit conversion of this result to string, as it might
	 * happen for the argument of the `echo' statement or for a variable
	 * embedded in a double-quoted string. Also displays a diagnostic message
	 * if this conversion cannot be done. Only int, float, string and objects
	 * implementing the <code>__toString()</code> method can be converted to
	 * string. Boolean values give a notice; any other type gives error.
	 * @param Logger $logger
	 * @param Where $where
	 * @return Result Converted value as string type, or the unknown result if
	 * the type cannot be converted to string.
	 */
	public function convertToString($logger, $where)
	{
		if( $this->isUnknown() ){
			return $this;
		
		} else if( $this->isBoolean() ){
			$logger->notice($where, "implicit conversion to string of a boolean value:"
			. " remember that FALSE gets rendered as empty string \"\""
			. " while TRUE gets rendered as \"1\"");
			if( $this->isFalse() )
				$value = "";
			else if( $this->isTrue() )
				$value = "1";
			else
				$value = NULL;
			
		} else if( $this->isInt() || $this->isFloat() || $this->isString() ){
			$value = $this->value;
			
		} else if( self::implementsToString($this->type) ){
			$value = NULL;
			
		} else {
			$logger->error($where, "no suitable implicit conversion to string for " . $this->type);
			return self::$unknown_result;
		}
		
		return self::factory(StringType::getInstance(), $value);
	}
	
	
	/**
	 * Evaluates the PHP string concatenation operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function dot($logger, $where, $other){
		
		$a = $this->convertToString($logger, $where);
		$b = $other->convertToString($logger, $where);
		
		if( $a->isUnknown() || $b->isUnknown() )
			return self::$unknown_result;
		
		if( $a->value === NULL || $b->value === NULL )
			return self::$string_unknown_result;
		else
			return self::factory(StringType::getInstance(),
				$a->value . $b->value);
	}
	
	
	/**
	 * Evaluates the bitwise "~" unary operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @return Result
	 */
	public function bitNot($logger, $where){
		if( $this->isUnknown() ){
			// ignore
		} else if( $this->isInt() ){
			if( $this->value !== NULL ){
				return self::factory(self::$int_type,
					(string) (~ (int) $this->value));
			}
		} else {
			$logger->error($where,
			"invalid type for the ~ operator: " . $this->type);
		}
		return self::$int_unknown_result;
	}
	
	
	/**
	 * Evaluates the bitwise "&amp;" operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function bitAnd($logger, $where, $other){
		if( $this->isInt() && $other->isInt() ){
			if( $this->value !== NULL && $other->value !== NULL )
				return self::factory(self::$int_type,
					(string)((int) $this->value & (int) $other->value));
			else
				return self::$int_unknown_result;
		}
		
		if( ! ($this->isUnknown() || $this->isInt() ) )
			$logger->error($where,
			"invalid left-hand type for the & operator: " . $this->type);
		
		if( ! ($other->isUnknown() || $other->isInt() ) )
			$logger->error($where,
			"invalid right-hand type for the & operator: " . $other->type);
		
		return self::$int_unknown_result;
	}
	
	
	/**
	 * Evaluates the bitwise "|" operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function bitOr($logger, $where, $other){
		if( $this->isInt() && $other->isInt() ){
			if( $this->value !== NULL && $other->value !== NULL )
				return self::factory(self::$int_type,
					(string) ((int) $this->value | (int) $other->value));
			else
				return self::$int_unknown_result;
		}
		
		if( ! ($this->isUnknown() || $this->isInt() ) )
			$logger->error($where,
			"invalid left-hand type for the | operator: " . $this->type);
		
		if( ! ($other->isUnknown() || $other->isInt() ) )
			$logger->error($where,
			"invalid right-hand type for the | operator: " . $other->type);
		
		return self::$int_unknown_result;
	}
	
	
	/**
	 * Evaluates the bitwise "^" operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function bitXor($logger, $where, $other){
		if( $this->isInt() && $other->isInt() ){
			if( $this->value !== NULL && $other->value !== NULL )
				return self::factory(self::$int_type,
					(string) ((int) $this->value ^ (int) $other->value));
			else
				return self::$int_unknown_result;
		}
		
		if( ! ($this->isUnknown() || $this->isInt() ) )
			$logger->error($where,
			"invalid left-hand type for the ^ operator: " . $this->type);
		
		if( ! ($other->isUnknown() || $other->isInt() ) )
			$logger->error($where,
			"invalid right-hand type for the ^ operator: " . $other->type);
		
		return self::$int_unknown_result;
	}
	
	
	/**
	 * Reverses the left/right order of a weak comparison operator.
	 * @param string $op
	 * @return string 
	 */
	private static function reverseWeakComparisonOp($op){
		switch($op){
		case "<": return ">";
		case "<=": return ">=";
		case ">=": return "<=";
		case ">": return "<";
		default: return $op;
		}
	}
	
	
	/**
	 * 
	 * @param Result $a
	 * @param string $op
	 * @param Result $b 
	 * @return string Result of the comparison: "TRUE", "FALSE" or NULL if the
	 * values are not determined.
	 */
	private static function weakCompareInt($a, $op, $b){
		if( $a->value === NULL || $b->value === NULL )
			return NULL;
		$i = (int) $a->value;
		$j = (int) $b->value;
		switch($op){
		case "<": $r = $i < $j; break;
		case "<=": $r = $i <= $j; break;
		case "==": $r = $i == $j; break;
		case ">=": $r = $i >= $j; break;
		case ">": $r = $i > $j; break;
		case "!=": $r = $i != $j; break;
		default: throw new \RuntimeException("op=$op");
		}
		return $r? "TRUE" : "FALSE";
	}
	
	
	/**
	 * 
	 * @param Result $a
	 * @param string $op
	 * @param Result $b 
	 * @return string Result of the comparison: "TRUE", "FALSE" or NULL if the
	 * values are not determined.
	 */
	private static function weakCompareFloat($a, $op, $b){
		if( $a->value === NULL || $b->value === NULL )
			return NULL;
		$i = (float) $a->value;
		$j = (float) $b->value;
		switch($op){
		case "<": $r = $i < $j; break;
		case "<=": $r = $i <= $j; break;
		case "==": $r = $i == $j; break;
		case ">=": $r = $i >= $j; break;
		case ">": $r = $i > $j; break;
		case "!=": $r = $i != $j; break;
		default: throw new \RuntimeException("op=$op");
		}
		return $r? "TRUE" : "FALSE";
	}
	
	
	/**
	 * Evaluates the weak comparison operator.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @param string $op Weak comparison operator, one of "&lt;", "&lt;=", "==",
	 * "&gt;=", "&gt;", "!=".
	 * @return Result
	 */
	public function weakCompare($logger, $where, $other, $op){
		if( $this->isUnknown() || $other->isUnknown() )
			return self::$unknown_result;
		
		// Sort types by name so only the triangular matrix remains to examine:
		$a = $this;
		$b = $other;
		if( strcmp(get_class($a->type), get_class($b->type)) > 0 ){
			$c = $a; $a = $b; $b = $c;
			$op = self::reverseWeakComparisonOp($op);
		}
		
		$ok = FALSE;
		$value = /*.(string).*/ NULL;
		$hint = "";
		if( $a->isArray() ){
			if( $b->isNull() ){
				if($op === "==" )
					$hint = " - Hint: use strict comparison operator `===' instead.";
				else if($op === "!=")
					$hint = " - Hint: use strict comparison operator `!==' instead.";
			}
		} else if( $a->isBoolean() ){
			if( $b->isBoolean() ){
				if( $op === "==" ){
					$ok = TRUE;
					if( $a->value !== NULL && $b->value !== NULL )
						$value = ($a->value === $b->value)? "TRUE" : "FALSE";
				} else if( $op === "!=" ){
					$ok = TRUE;
					if( $a->value !== NULL && $b->value !== NULL )
						$value = ($a->value === $b->value)? "FALSE" : "TRUE";
				}
			}
		} else if( $a->isClass() ){
			if( $b->isNull() ){
				$ok = ($op === "==" || $op === "!=");
			} else if( $b->isClass() ){
				if($op === "==" )
					$hint = " - Hint: use strict comparison operator `===' instead.";
				else if($op === "!=")
					$hint = " - Hint: use strict comparison operator `!==' instead.";
			}
		} else if( $a->isFloat() ){
			if( $b->isFloat() ){
				$ok = TRUE;
				$value = self::weakCompareFloat($a, $op, $b);
				if( $op === "==" || $op === "!=" )
					$logger->notice($where, "comparison by equality/inequality between float numbers. Remember that float numbers have limited precision, and that expressions algebrically equivalent might give different results. For example, 0.57-0.56==0.1 would give FALSE.");
			} else if( $b->isInt() ){
				$ok = TRUE;
				// implicit promotion of $b int --> float:
				$value = self::weakCompareFloat($a, $op, $b);
			}
		} else if( $a->isInt() ){
			if( $b->isFloat() ){
				$ok = TRUE;
				// implicit promotion of $a int --> float:
				$value = self::weakCompareFloat($a, $op, $b);
			} else if( $b->isInt() ){
				$ok = TRUE;
				$value = self::weakCompareInt($a, $op, $b);
			}
		} else if( $a->isNull() ){
			if( $b->isString() || $b->isArray() ){
				if($op === "==" )
					$hint = " - Hint: use strict comparison operator `===' instead.";
				else if($op === "!=")
					$hint = " - Hint: use strict comparison operator `!==' instead.";
			} else if( $b->isResource() ){
				$ok = $op === "==" || $op === "!=";
			}
		} else if( $a->isResource() ){
			if( $b->isNull() || $b->isResource() ){
				$ok = $op === "==" || $op === "!=";
			}
		} else if( $a->isString() ){
			if( $b->isNull() ){
				if($op === "==" )
					$hint = " - Hint: use strict comparison operator `===' instead.";
				else if($op === "!=")
					$hint = " - Hint: use strict comparison operator `!==' instead.";
			} else if( $b->isString() ){
				if($op === "==" )
					$hint = " - Hint: use strict comparison operator `===' instead.";
				else if($op === "!=")
					$hint = " - Hint: use strict comparison operator `!==' instead.";
				else
					$hint = " - Hint: use safer comparison expression strcmp(\$s1, \$s2) $op 0 instead.";
			}
		}
		
		if( ! $ok )
			$logger->error($where, "comparing (" . $this->type . ") $op ("
				. $other->type . ")$hint");
		
		if( $value === "TRUE" )
			return self::$true_result;
		else if( $value === "FALSE" )
			return self::$false_result;
		else
			return self::$boolean_unknown_result;
	}
	
	
	/**
	 * Evaluates the strong comparison operators <code>===</code> and
	 * <code>!==</code>.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @param string $op Strong comparison operator, one of "===", "!==".
	 * @return Result
	 */
	public function strongCompare($logger, $where, $other, $op){
		// FIXME: ===, !==: some check to do with these?
		return self::$boolean_unknown_result;
	}
	
	
	/**
	 * 
	 * @param Logger $logger
	 * @param Where $where
	 * @return Result
	 */
	public function booleanNot($logger, $where){
		// Try to calculate:
		if( $this->isBoolean() ){
			if( $this->isTrue() )
				return self::$true_result;
			else if( $this->isFalse() )
				return self::$false_result;
			else
				return self::$boolean_unknown_result;
		}
		
		if( ! $this->isUnknown() )
			$logger->error($where, "invalid type for the `!' operator: "
			. $this->type);
		
		return self::$boolean_unknown_result;
	}
	
	
	/**
	 * 
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @param string $op Either "&amp;&amp;" or "and". Used to build a
	 * consistent error message if the operands are not boolean.
	 * @return Result
	 */
	public function booleanAnd($logger, $where, $other, $op){
		// Try to calculate:
		if( $this->isBoolean() && $other->isBoolean() ){
			if( $this->value === NULL || $other->value === NULL ){
				return self::$boolean_unknown_result;
			} else {
				if( $this->isTrue() && $other->isTrue() )
					return self::$true_result;
				else
					return self::$false_result;
			}
		}
		
		if( ! ( $this->isUnknown() || $this->isBoolean() ) )
			$logger->error($where, "invalid left-hand type for the `$op' operator: "
			. $this->type);
		
		if( ! ( $other->isUnknown() || $other->isBoolean() ) )
			$logger->error($where, "invalid right-hand type for the `$op' operator: "
			. $other->type);
		
		return self::$boolean_unknown_result;
	}
	
	
	/**
	 * 
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @param string $op Either "||" or "or". Used to build a consistent error
	 * message.
	 * @return Result
	 */
	public function booleanOr($logger, $where, $other, $op){
		// Try to calculate:
		if( $this->isBoolean() && $other->isBoolean() ){
			if( $this->value === NULL || $other->value === NULL ){
				return self::$boolean_unknown_result;
			} else {
				if( $this->isTrue() || $other->isTrue() )
					return self::$true_result;
				else
					return self::$false_result;
			}
		}
		
		if( ! ( $this->isUnknown() || $this->isBoolean() ) )
			$logger->error($where, "invalid left-hand type for the `$op' operator: "
			. $this->type);
		
		if( ! ( $other->isUnknown() || $other->isBoolean() ) )
			$logger->error($where, "invalid right-hand type for the `$op' operator: "
			. $other->type);
		
		return self::$boolean_unknown_result;
	}
	
	
	/**
	 * 
	 * @param Logger $logger
	 * @param Where $where
	 * @param Result $other
	 * @return Result
	 */
	public function booleanXor($logger, $where, $other){
		// Try to calculate:
		if( $this->isBoolean() && $other->isBoolean() ){
			if( $this->value === NULL || $other->value === NULL ){
				return self::$boolean_unknown_result;
			} else {
				if( $this->isTrue() && $other->isFalse()
				|| $this->isFalse() && $other->isTrue() )
					return self::$true_result;
				else
					return self::$false_result;
			}
		}
		
		if( ! ( $this->isUnknown() || $this->isBoolean() ) )
			$logger->error($where, "invalid left-hand type for the `xor' operator: "
			. $this->type);
		
		if( ! ( $other->isUnknown() || $other->isBoolean() ) )
			$logger->error($where, "invalid right-hand type for the `xor' operator: "
			. $other->type);
		
		return self::$boolean_unknown_result;
	}
	
	
	/**
	 * Applies PHPLint's type conversion operator
	 * <code>/&#42;.(T).&#42;/</code> to this result.
	 * Note that these operators does not change the type and neither change
	 * the value of their argument at runtime, but simply tell to PHPLint how
	 * some special values (notably, NULL and empty array) must be correctly
	 * interpreted.
	 * Under PHP 5 these type cast operators can be applied only to NULL and
	 * empty array: caller must take care to check for the current version of
	 * PHP. PHP 5 programs should use cast() in any other case.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Type $to Type specified in the cast operator.
	 * @return Result
	 */
	public function typeCast($logger, $where, $to){
		if( $to instanceof UnknownType ){
			return self::$unknown_result;
		
		} else if( $this->isUnknown() ){
			return self::factory($to);
		
		} else if( $this->isNull() ){
			if( $this->type->assignableTo($to) ){
				return self::factory($to, "NULL");
			}
		
		} else if( $this->isMixed() ){
			// This case for PHP 4 only.
			if( $to instanceof BooleanType || $to instanceof IntType
			|| $to instanceof FloatType || $to instanceof StringType
			|| $to instanceof ArrayType || $to instanceof ResourceType
			|| $to instanceof ClassType ){
				return self::factory($to);
			}
		
		} else if( $this->isArray() ){
			if( $to instanceof ArrayType ){
				if( $this->isEmptyArray() )
					// Can't copy "array()" value because isEmptyArray()
					// just checks that to detect empty, still untyped, arrays.
					return self::factory($to, "array(...)");
				else if( $this->type->assignableTo($to) ){
					return self::factory($to);
				}
			}
		
		} else if( $this->isClass() ){
			// PHP 4 only.
			if( $to instanceof ClassType )
				return self::factory($to);
			
		}
		
		// No "return" case found - error:
		$logger->error($where, "undefined type conversion operator from " . $this->type
		. " to $to");
		return self::factory($to);
	}

	
	
	/**
	 * Applies PHP's value conversion operator <code>(T)</code> to this result.
	 * Note that these operators does not merely change the type of their
	 * argument: they change the value too.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Type $to Type specified in the cast operator.
	 * @return Result
	 */
	public function valueCast($logger, $where, $to)
	{
		if( $this->isUnknown() ){
			return self::factory($to);
		
		} else if( $this->isBoolean() ){
			if( $to instanceof IntType ){
				if( $this->isFalse() )
					return self::factory(self::$int_type, "0");
				else if( $this->isTrue() )
					return self::factory(self::$int_type, "1");
				else
					return self::$int_unknown_result;
			}
		
		} else if( $this->isInt() ){
			if( $to instanceof BooleanType
			|| $to instanceof FloatType
			|| $to instanceof StringType ){
				return self::factory($to);
			}
		
		} else if( $this->isFloat() ){
			if( $to instanceof IntType
			|| $to instanceof StringType ){
				return self::factory($to);
			}
			
		} else if( $this->isString() ){
			if( $to instanceof IntType
			|| $to instanceof FloatType ){
				return self::factory($to);
			}
		
		} else if( $this->isMixed() ){
			if( $to instanceof BooleanType
			|| $to instanceof IntType
			|| $to instanceof FloatType
			|| $to instanceof StringType ){
				return self::factory($to);
			}
		
		} else if( $this->isClass() ){
			if( $to instanceof StringType ){
				if( self::implementsToString($this->type) ){
					return self::$string_unknown_result;
				}
			}
		
		} else if( $this->isNull() ){
			if( $this->type->assignableTo($to) ){
				return self::factory($to, "NULL");
			}
		}
		
		// No "return" case found - error:
		$logger->error($where, "undefined value conversion operator from "
		. $this->type . " to $to");
		return self::factory($to);
	}
	
	
	/**
	 * Checks the type of this result. If the result is not of the expected
	 * type, reports a detailed description of the error.
	 * @param Logger $logger
	 * @param Where $where
	 * @param Type $expect
	 * @return void
	 */
	public function checkExpectedType($logger, $where, $expect)
	{
		if( $this->isUnknown() ){
			// ignore
			
		} else if( $this->type->equals($expect) ){
			// ok
			
		} else {
			
			$hint = "";
		
			if( $expect === self::$boolean_type ){
				if( $this->isInt() ){
					$hint = ". Remember that 0 evaluates to FALSE, and any other integer value evaluates to TRUE.";

				} else if( $this->isFloat() ){
					$hint = ". Remember that 0.0 evaluates to FALSE and any other value evaluates to TRUE.";

				} else if( $this->isString() ){
					$hint = ". Remember that the empty string \"\", the string \"0\" and the NULL string all evaluate to FALSE and any other string evaluates to TRUE.";

				} else if( $this->isArray() ){
					$hint = ". Remember that an array with zero elements evaluates to FALSE, and an array with one or more elements evaluates to TRUE.";

				} else if( $this->isResource() ){
					$hint = ". Remember that a resource always evaluates to TRUE, so that this expression is useless. Remember too that some functions, formally declared to return a resource, might return the boolean value FALSE on error; if this is the case, rewrite as (EXPR) !== FALSE.";

				} else if( $this->isClass() ){
					$hint = ". Remember that an object evaluates to FALSE if it has no properties, and evaluates to TRUE if it has at least one property.";
				}
			}
			
			$logger->error($where, 
				"found expression of type "
				. $this->getType()
				. ", expected type is $expect$hint");
		}
	}
	
	
	/**
	 * Static initializer of this class, do not call directly.
	 */
	public static function static_init(){
		self::$boolean_type = BooleanType::getInstance();
		self::$int_type = IntType::getInstance();
		self::$unknown_result = new Result(UnknownType::getInstance(), "?");
		self::$null_result = new Result(NullType::getInstance(), "NULL");
		self::$void_result = new Result(VoidType::getInstance(), "");
		self::$boolean_unknown_result = new Result(BooleanType::getInstance(), NULL);
		self::$false_result = new Result(BooleanType::getInstance(), "FALSE");
		self::$true_result = new Result(BooleanType::getInstance(), "TRUE");
		self::$int_unknown_result = new Result(IntType::getInstance(), NULL);
		self::$float_unknown_result = new Result(FloatType::getInstance(), NULL);
		self::$string_unknown_result = new Result(StringType::getInstance(), NULL);
	}
	
}


Result::static_init();
