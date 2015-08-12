<?php

namespace it\icosaedro\regex;

require_once __DIR__ . "/../../../all.php";

/*. require_module 'spl'; .*/

use it\icosaedro\containers\Printable;
use it\icosaedro\utils\Integers;
use it\icosaedro\utils\Strings;
use it\icosaedro\utils\UTF8;
use RuntimeException;
use OutOfRangeException;
use LogicException;
use InvalidArgumentException;

/**
 * Stack of the substrings that matches an element "(...)".
 * The class ElementMatcher, that matches an element (any sub-expression of
 * the regular expression enclosed between round parentheses), saves in this
 * stack the resulting matches that can be queried later by the client code.
 * Each entry on this stack contains 5 numbers: level, a, b, static index and
 * group count.
 * "Level" is the element number, counting open parentheses from left to right;
 * it is stored here to perform safety checks, but it is not otherwise used.
 * [a,b] is the range matched on the subject string, being (b-a) the total
 * number of bytes matched.
 * Static index and group count: don't remember anymore... :-)
 */
/*. private .*/ class MatchResult {

	public $marks_n = 0;
	public $marks = /*. (int[int][int]) .*/ array();

	/*. void .*/ function push(/*. int .*/ $level, /*. int .*/ $a, /*. int .*/ $b,
		/*. int .*/ $static_index, /*. int .*/ $subgroups_count){
		#echo "push($level, $a, $b, $static_index, $subgroups_count)\n";
		$this->marks[$this->marks_n++] = array($level, $a, $b, $static_index, $subgroups_count);
	}

	/*. int .*/ function pop(/*. int .*/ $level){
		#echo "pop($level)\n";
		if( $this->marks_n == 0 )
			throw new RuntimeException("empty marks array");
		if( $this->marks[ $this->marks_n - 1 ][0] != $level )
			throw new RuntimeException("invalid mark level $level, found " . $this->marks[ $this->marks_n - 1 ][0]);
		$a = $this->marks[ --$this->marks_n ][1];
		return $a;
	}

	/*. void .*/ function reset(){
		$this->marks_n = 0;
	}

}


/*. private .*/ final class EmptyGroup implements Group {

	private static /*. Group .*/ $instance;

	private /*. void .*/ function __construct(){}

	static /*. Group .*/ function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new EmptyGroup();
		return self::$instance;
	}

	/*. int .*/ function count(){
		return 0;
	}


	/*. Element .*/ function elem(/*. int .*/ $i){
		throw new OutOfRangeException("empty group");
	}

}


/*. forward private class MatchedGroup implements Group {
	void function __construct(string $s, int[int][int] $marks, int $mark);
	}
.*/


/*. private .*/ class MatchedElement implements Element {
	private /*. string .*/ $s;
	private /*. int[int][int] .*/ $marks;
	private $mark = 0;
	private $group_n = 0;
	private /*. int[int] .*/ $group_indeces;

	private /*. int .*/ function nextGroup(/*. int .*/ $i){
		$g = $this->marks[$i][0];
		do {
			$i--;
			if( $i < 0 or $this->marks[$i][0] < $g )
				return $i;
		} while(TRUE);
	}


	/*. void .*/ function __construct(/*. string .*/ $s,
		/*. int[int][int] .*/ $marks, /*. int .*/ $mark){
		$this->s = $s;
		$this->marks = $marks;
		$this->mark = $mark;
		$this->group_n = $marks[$mark][0];
		
		# Scan subgroups:
		$this->group_indeces = /*. (int[int]) .*/ array();
		$i = $mark - 1;
		while( $i >= 0 and $this->marks[$i][0] > $this->group_n ){
			$this->group_indeces[] = $i;
			$i = $this->nextGroup($i);
		}
	}


	/*. int .*/ function start(){
		return $this->marks[ $this->mark ][1];
	}


	/*. int .*/ function end(){
		return $this->marks[ $this->mark ][2];
	}


	/*. string .*/ function value(){
		return Strings::substring( $this->s,
			$this->start(), $this->end() );
	}


	function count(){
		return $this->marks[ $this->mark ][4];
	}


	/*. Group .*/ function group(/*. int .*/ $g){
		if( $g < 0 or $g >= $this->marks[ $this->mark ][4] )
			throw new OutOfRangeException("$g");
		
		foreach($this->group_indeces as $j){
			if( $this->marks[$j][3] == $g )
				return new MatchedGroup($this->s, $this->marks, $j);
		}
		return EmptyGroup::getInstance();
	}

}


/*. private .*/ class MatchedGroup implements Group {

	# Subject string:
	private /*. string .*/ $s;
	# Stack of found elems:
	private /*. int[int][int] .*/ $marks;
	# Index first elem of group in stack:
	private $mark = 0;
	# Indeces all elems in stack, reverse order:
	private /*. int[int] .*/ $elems_indeces;

	/*. void .*/ function __construct(/*. string .*/ $s,
		/*. int[int][int] .*/ $marks, /*. int .*/ $mark){
		$this->s = $s;
		$this->marks = $marks;
		$this->mark = $mark;

		# Retrieve indeces of all elems, reverse order:
		$g = $marks[$mark][0];
		for($i = $this->mark; $i >= 0; $i--){
			$m = $this->marks[$i][0];
			if( $m < $g )
				break;
			if( $m == $g )
				$this->elems_indeces[] = $i;
		}
	}


	/*. int .*/ function count(){
		return count($this->elems_indeces);
	}


	/*. Element .*/ function elem(/*. int .*/ $i){
		$n = count($this->elems_indeces);
		if( $i < 0 or $i >= $n )
			throw new OutOfRangeException("$i");
		return new MatchedElement($this->s, $this->marks,
			$this->elems_indeces[$n - $i - 1]);
	}

}


/**
 * Every specific matcher operator (literal string, set, AND, OR, element,
 * group, ...)  implements this interface.
 */
/*. private .*/ abstract class MatcherOperator {
	const SPECIAL = ".|()[]{}?*+^\$\\";

	/*
	 * Applies this operator to the string $s, starting from offset $i.
	 * On success returns the final ending index. On failere returns a
	 * negative value.
	 */
	abstract /*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i);

	/*
	 * Retry mathing this operator. The instance must save in a stack the
	 * original starting offset. If fails, internal stack is rolled-back
	 * and a negative value is returned.
	 */
	abstract /*. int .*/ function retry(/*. string .*/ $s);

	/*
	 * Forces a failure and rolls-back its internal stack.
	 */
	abstract /*. void .*/ function rollback();

	/*
	 * Resets its internal status and prepares for a new match.
	 */
	abstract /*. void .*/ function reset();

	/*
	 * Returns the sub-regex that represents itself.
	 */
	abstract /*. string .*/ function __toString();

	/*
	 * Escapes special characters so that the resulting string
	 * matches itself literally.
	 */
	static /*. string .*/ function escape(/*. string .*/ $s){
		$special = self::SPECIAL;
		for($i = strlen(self::SPECIAL)-1; $i >= 0; $i--){
			$c = $special[$i];
			$s = (string) str_replace($c, "\\$c", $s);
		}
		return $s;
	}
}


/**
 * Implements the "^" operator that matches the beginning of the subject.
 */
/*. private .*/ final class BeginningMatcher extends MatcherOperator {

	private static /*. MatcherOperator .*/ $instance;

	private /*. void .*/ function __construct(){}

	static /*. MatcherOperator .*/ function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new self();
		return self::$instance;
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i)
	{
		#echo __CLASS__, " $this\n";
		if( $i == 0 )
			return $i;
		else
			return -1;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		return -1;
	}


	/*. void .*/ function rollback(){
	}

	/*. void .*/ function reset(){
	}

	/*. string .*/ function __toString(){
		return "^";
	}
}


/**
 * Implements the "$" operator that matches the ending of the subject.
 */
/*. private .*/ final class EndingMatcher extends MatcherOperator {

	private static /*. MatcherOperator .*/ $instance;

	private /*. void .*/ function __construct(){}

	static /*. MatcherOperator .*/ function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new self();
		return self::$instance;
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i)
	{
		#echo __CLASS__, " $this\n";
		if( $i == strlen($s) )
			return $i;
		else
			return -1;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		return -1;
	}


	/*. void .*/ function rollback(){
	}

	/*. void .*/ function reset(){
	}

	/*. string .*/ function __toString(){
		return "\$";
	}
}


/**
 * Matches a literal string that may be 1 or more bytes long.
 */
/*. private .*/ class LiteralMatcher extends MatcherOperator {
	public /*. string .*/ $literal;
	private $literal_len = 0;

	/*. void .*/ function __construct(/*. string .*/ $literal){
		$this->literal = $literal;
		$this->literal_len = strlen($literal);
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " s=$s i=$i literal=", " $this\n";
		if( $i + $this->literal_len <= strlen($s)
		and substr_compare($s, $this->literal, $i, $this->literal_len) == 0 )
			return $i + $this->literal_len;
		else
			return -1;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry ", " $this\n";
		return -1;
	}


	/*. void .*/ function rollback(){
	}

	/*. void .*/ function reset(){
	}

	/*. string .*/ function __toString(){
		return self::escape($this->literal);
	}
}


/**
 * Matches a single byte, that is the dot operator.
 * Used when the Pattern's constructor operates in non-UTF-8 mode. In UTF-8
 * mode, a single character can be up to 3 bytes long and this class cannot
 * be used.
 */
/*. private .*/ final class AnyMatcher extends MatcherOperator {

	private static /*. MatcherOperator .*/ $instance;

	private /*. void .*/ function __construct(){}

	static /*. MatcherOperator .*/ function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new self();
		return self::$instance;
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " $this\n";
		if( $i < strlen($s) )
			return $i + 1;
		else
			return -1;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		return -1;
	}

	/*. void .*/ function rollback(){
	}

	/*. void .*/ function reset(){
	}

	/*. string .*/ function __toString(){
		return ".";
	}
}


/**
 * Matches a single UTF-8 character. Used when the Pattern's constructor
 * operates in UTF-8 mode.
 */
/*. private .*/ class AnyUTF8Matcher extends MatcherOperator {

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " $this\n";
		if( $i >= strlen($s) )
			return -1;
		$seq_len = UTF8::sequenceLength( ord($s[$i]) );
		if( $seq_len <= 0 )
			return -1;  # Invalid encoding - can't match any UTF8 char.
		return $i + $seq_len;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		return -1;
	}

	/*. void .*/ function rollback(){
	}

	/*. void .*/ function reset(){
	}

	/*. string .*/ function __toString(){
		return ".";
	}
}


/**
 * Implements the set of bytes "{}" factor. Can be used only in non-UTF-8 mode.
 */
/*. private .*/ class SetMatcher extends MatcherOperator {
	private /*. string .*/ $literal_set;

	/*
	 * Selected bytes. Every int number represents 32 selected bytes for
	 * a total of 32*8 = 256 bits.
	 */
	private $set = array(0, 0, 0, 0, 0, 0, 0, 0);

	private /*. void .*/ function addToSet(/*. int .*/ $b){
		$this->set[ $b >> 5 ] |= 1 << ($b & 31);
	}

	/**
	 * Builds an internal representation of the set of bytes.
	 * @param string $set The string that represents the set, with surrounding
	 * square perenthesis removed and special characters resolved.
	 * Example: <code>"a-zA-Z0-9_"</code>
	 * @return void
	 */
	function __construct($set){
		$this->literal_set = $set;
		$complement = FALSE;
		for($i = 0; $i < strlen($set); $i++){
			$c = $set[$i];
			if( $c === "!" and $i == 0 ){
				$complement = TRUE;
			} else if( $i + 3 <= strlen($set) and $set[$i+1] === "-" ){
				$a = ord($c);
				$b = ord($set[$i+2]);
				if( $a > $b )
					throw new InvalidArgumentException("inverted range in set");
				for($j = $a; $j <= $b; $j++)
					$this->addToSet($j);
				$i += 2;
			} else {
				$this->addToSet(ord($c));
			}
		}
		if( $complement )
			for($i = 0; $i < 8; $i++)
				$this->set[$i] = ~ $this->set[$i];
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		if( $i >= strlen($s) )
			return -1;
		$b = ord($s[$i]);
		if( ($this->set[ $b >> 5 ] & (1 << ($b & 31))) != 0 )
			return $i + 1;
		else
			return -1;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		return -1;
	}


	/*. void .*/ function rollback(){
	}

	/*. void .*/ function reset(){
	}

	/*. string .*/ function __toString(){
		return "{" . self::escape($this->literal_set) . "}";
	}
}


/**
 *Implements the set of UTF-8 characters "{}". UTF-8 mode only. 
 */
/*. private .*/ class UTF8SetMatcher extends MatcherOperator {
	private /*. string .*/ $literal_set;

	/*
	 * Selected chars in the range [0,255]. Every int number represents 32
	 * selected chars for a total of 32*8 = 256 bits.
	 */
	private $lowset = array(0, 0, 0, 0, 0, 0, 0, 0);

	/*
	 * Selected chars in the range [256,65535].  Contains an even number of
	 * int numbers, every pair giving the range [a,b] of codepoints selected.
	 */
	private $hiset = /*. (int[int]) .*/ array();

	private $complement = FALSE;

	private /*. void .*/ function addToLowSet(/*. int .*/ $b){
		$this->lowset[ $b >> 5 ] |= 1 << ($b & 31);
	}

	/**
	 * Builds an internal representation of the set of characters.
	 * @param string $set The string that represents the set, with surrounding
	 * square parenthesis removed and special characters resolved.
	 * Example: <code>"a-zA-Z0-9_"</code>
	 * @return void
	 */
	function __construct($set){
		$this->literal_set = $set;
		$i = 0;
		while( $i < strlen($set) ){
			if( $i == 0 and $set[$i] === "!" ){
				$complement = TRUE;
				$i++;
			} else {
				$a = UTF8::codepointAtByteIndex($set, $i);
				$i += UTF8::sequenceLength( ord($set[$i]) );
				if( $i + 2 <= strlen($set) and $set[$i] === "-" ){
					# It is a range.
					$i++;
					$b = UTF8::codepointAtByteIndex($set, $i);
					$i += UTF8::sequenceLength( ord($set[$i]) );
					if( $a > $b )
						throw new InvalidArgumentException("inverted range in set");
					if( $b <= 255 ){
						for($j = $a; $j <= $b; $j++)
							$this->addToLowSet($j);
					} else if( $a >= 256 ){
						$this->hiset[] = $a;
						$this->hiset[] = $b;
					} else {
						for($j = $a; $j <= 255; $j++)
							$this->addToLowSet($j);
						$this->hiset[] = 256;
						$this->hiset[] = $b;
					}
				} else {
					# It is a single char.
					if( $a <= 255 ){
						$this->addToLowSet($a);
					} else {
						$this->hiset[] = $a;
						$this->hiset[] = $a;
					}
				}
			}
		}
	}


	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		if( $i >= strlen($s) )
			return -1;
		$c = UTF8::codepointAtByteIndex($s, $i);
		if( $c <= 255 ){
			$in = ($this->lowset[ $c >> 5 ] & (1 << ($c & 31))) != 0;
		} else {
			$in = FALSE;
			for($j = count($this->hiset) - 2; $j >= 0; $j -= 2){
				if( $this->hiset[$j] <= $c and $c <= $this->hiset[$j+1] ){
					$in = TRUE;
					break;
				}
			}
		}

		if( $this->complement )
			$in = ! $in;

		if( $in )
			return $i + UTF8::sequenceLength( ord($s[$i]) );
		else
			return -1;
	}


	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		return -1;
	}


	/*. void .*/ function rollback(){
	}

	/*. void .*/ function reset(){
	}


	/*. string .*/ function __toString(){
		return "{" . self::escape($this->literal_set) . "}";
	}
}


/**
 * Implements the OR operator "A|B", where A, B are sub-regex.
 */
/*. private .*/ class OrMatcher extends MatcherOperator {
	private /*. MatcherOperator .*/ $a, $b;
	private $tos = 0;
	private $retry_on_a = /*. (bool[int]) .*/ array();
	private $index = /*. (int[int]) .*/ array();

	/*. void .*/ function __construct(/*. MatcherOperator .*/ $a, /*. MatcherOperator .*/ $b){
		$this->a = $a;
		$this->b = $b;
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		$j = $this->a->match($s, $i);
		if( $j >= 0 ){
			$this->tos++;
			$this->retry_on_a[ $this->tos ] = TRUE;
			$this->index[ $this->tos ] = $i;
			return $j;
		} else {
			$j = $this->b->match($s, $i);
			if( $j >= 0 ){
				$this->tos++;
				$this->retry_on_a[ $this->tos ] = FALSE;
				$this->index[ $this->tos ] = $i;
				return $j;
			} else {
				return -1;
			}
		}
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		if( $this->retry_on_a[$this->tos] ){
			$j = $this->a->retry($s);
			if( $j >= 0 ){
				return $j;
			} else {
				$j = $this->b->match($s, $this->index[ $this->tos ]);
				if( $j >= 0 ){
					$this->retry_on_a[ $this->tos ] = FALSE;
					return $j;
				} else {
					$this->tos--;
					return -1;
				}
			}
		} else {
			$j = $this->b->retry($s);
			if( $j >= 0 ){
				return $j;
			} else {
				$this->tos--;
				return -1;
			}
		}
	}


	/*. void .*/ function rollback(){
		if( $this->retry_on_a[ $this->tos ] )
			$this->a->rollback();
		else
			$this->b->rollback();
		$this->tos--;
	}

	/*. void .*/ function reset(){
		$this->a->reset();
		$this->b->reset();
		$this->tos = 0;
	}

	/*. string .*/ function __toString(){
		return $this->a . "|" . $this->b;
	}
}


/**
 * Implements the implicit AND operator that joins two factors as in "AB".
 */
/*. private .*/ class AndMatcher extends MatcherOperator {
	public /*. MatcherOperator .*/ $a, $b;
	private $tos = 0;
	private $retry_on_b = /*. (int[int]) .*/ array();

	/*. void .*/ function __construct(/*. MatcherOperator .*/ $a, /*. MatcherOperator .*/ $b){
		$this->a = $a;
		$this->b = $b;
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		#echo __CLASS__, " tos=", $this->tos, "\n";

		$j = $this->a->match($s, $i);

		do {
			if( $j < 0 )
				return -1;

			$k = $this->b->match($s, $j);
			if( $k >= 0 ){
				$this->retry_on_b[$this->tos++] = $j;
				return $k;
			}

			$j = $this->a->retry($s);
		} while(TRUE);

	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";

		$k = $this->b->retry($s);
		if( $k >= 0 )
			return $k;

		do {
			$j = $this->a->retry($s);
			if( $j < 0 ){
				$this->tos--;
				return -1;
			}
			$k = $this->b->match($s, $j);
			if( $k >= 0 ){
				$this->retry_on_b[$this->tos-1] = $j;
				return $k;
			}
		} while(TRUE);
	}


	/*. void .*/ function rollback(){
		$this->b->rollback();
		$this->a->rollback();
		$this->tos--;
	}

	/*. void .*/ function reset(){
		$this->a->reset();
		$this->b->reset();
		$this->tos = 0;
	}

	/*. string .*/ function __toString(){
		return $this->a . $this->b;
	}
}


/**
 * Implements the element "()", that is a regex between round parentheses.
 */
/*. private .*/ class ElementMatcher extends MatcherOperator {

	private $level = 0;
	private /*. MatcherOperator .*/ $e;
	private /*. MatchResult .*/ $r;
	private $static_index = 0;
	private $groups_count = 0;

	/*. void .*/ function __construct(/*. int .*/ $level, /*. int .*/ $static_index, /*. int .*/ $groups_count, /*. MatcherOperator .*/ $e, /*. MatchResult .*/ $r){
		$this->level = $level;
		$this->e = $e;
		$this->r = $r;
		$this->static_index = $static_index;
		$this->groups_count = $groups_count;
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		$j = $this->e->match($s, $i);
		if( $j >= 0 ){
			$this->r->push($this->level, $i, $j, $this->static_index, $this->groups_count);
			return $j;
		} else {
			return -1;
		}
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		$i = $this->r->pop($this->level);
		#echo __CLASS__, " retry i=$i $this\n";
		$j = $this->e->retry($s);
		if( $j >= 0 ){
			$this->r->push($this->level, $i, $j, $this->static_index, $this->groups_count);
			return $j;
		} else {
			return -1;
		}
	}

	/*. void .*/ function rollback(){
		#echo __CLASS__ , " rollback level=", $this->level, "\n";
		$i = $this->r->pop($this->level);
		$this->e->rollback();
	}

	/*. void .*/ function reset(){
		$this->e->reset();
	}

	/*. string .*/ function __toString(){
		return "(" . $this->e->__toString() . ")";
	}
}


/**
 * Implements the reluctant group matcher "A[a,b]?", where A is a sub-regex
 * and [a,b] is the (optional) range of allowed matches.
 */
/*. private .*/ class GroupMatcherReluctant extends MatcherOperator {
	private /*. MatcherOperator .*/ $f;
	private $min = 0, $max = 0;
	private $tos = 0;
	private $n = /*. (int[int]) .*/ array();
	private $stack = /*. (int[int][int]) .*/ array();

	/*. void .*/ function __construct(/*. MatcherOperator .*/ $f, /*. int .*/ $min, /*. int .*/ $max){
		$this->f = $f;
		$this->min = $min;
		$this->max = $max;
	}

	/*. void .*/ function rollback(){
		$n = $this->n[--$this->tos];
		while( $n > 0 ){
			$this->f->rollback();
			$n--;
		}
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		$this->tos++;
		$this->n[$this->tos-1] = 0;
		$this->stack[$this->tos-1][0] = $i;
		# Mandatory matches:
		while( $this->n[$this->tos-1] < $this->min ){
			$i = $this->f->match($s, $i);
			if( $i < 0 ){
				$this->rollback();
				return -1;
			}
			$this->n[$this->tos-1]++;
			$this->stack[$this->tos-1][$this->n[$this->tos-1]] = $i;
		}
		return $i;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";

		do {

			// go forward:
			if( $this->n[$this->tos-1] < $this->max ){
				// go forward:
				$i = $this->f->match($s, $this->stack[$this->tos-1][ $this->n[$this->tos-1] ]);
				if( $i >= 0 ){
					$this->n[$this->tos-1]++;
					$this->stack[$this->tos-1][$this->n[$this->tos-1]] = $i;
					if( $this->n[$this->tos-1] >= $this->min )
						return $i;
					else
						continue;
				}
			}

			// retry:
			do {
				if( $this->n[$this->tos-1] == 0 ){
					$this->tos--;
					return -1;
				}

				$i = $this->f->retry($s);
				if( $i >= 0 ){
					$this->stack[$this->tos-1][$this->n[$this->tos-1]] = $i;
					if( $this->n[$this->tos-1] >= $this->min )
						return $i;
					else
						break;
				}

				// go back one step and retry:
				$this->n[$this->tos-1]--;
			} while( TRUE );

		} while( TRUE );
	}

	/*. void .*/ function reset(){
		$this->f->reset();
		$this->tos = 0;
	}

	/*. string .*/ function __toString(){
		if( $this->min == 0 and $this->max == 1 )
			$m = "??";
		else if( $this->min == 0 and $this->max == PHP_INT_MAX )
			$m = "*?";
		else if( $this->min == 1 and $this->max == PHP_INT_MAX )
			$m = "+?";
		else if( $this->max == PHP_INT_MAX )
			$m = "[" . $this->min . ",]?";
		else
			$m = "[" . $this->min . "," . $this->max . "]?";
		return $this->f->__toString() . $m;
	}
}


/**
 * Implements the greedy group matcher "A[a,b]*", where A is a sub-regex
 * and [a,b] is the (optional) range of requested matches.
 */
/*. private .*/ class GroupMatcherGreedy extends MatcherOperator {
	private /*. MatcherOperator .*/ $f;
	private $min = 0, $max = 0;
	private $tos = 0;
	private $n = /*. (int[int]) .*/ array();
	private $stack = /*. (int[int][int]) .*/ array();

	/*. void .*/ function __construct(/*. MatcherOperator .*/ $f, /*. int .*/ $min, /*. int .*/ $max){
		$this->f = $f;
		$this->min = $min;
		$this->max = $max;
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		$this->tos++;
		$n = 0;
		do {
			$j = $this->f->match($s, $i);
			if( $j < 0 ){
				$this->n[$this->tos-1] = $n;
				if( $n < $this->min ){
					$this->rollback();
					return -1;
				} else {
					return $i;
				}
			}
			$this->stack[$this->tos-1][$n] = $i;
			$n++;
			$i = $j;
			if( $n == $this->max ){
				$this->n[$this->tos-1] = $n;
				return $i;
			}
		} while(TRUE);
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";

		do {

			// retry:
			do {
				if( $this->n[$this->tos-1] == 0 ){
					$this->tos--;
					return -1;
				}

				$i = $this->f->retry($s);
				if( $i >= 0 ){
					$this->stack[$this->tos-1][$this->n[$this->tos-1]] = $i;
					break;
				} else {
					$this->n[$this->tos-1]--;
					if( $this->n[$this->tos-1] >= $this->min ){
						return $this->stack[$this->tos-1][$this->n[$this->tos-1]];
					}
				}

			} while( TRUE );

			// go forward:
			do {
				if( $this->n[$this->tos-1] < $this->max ){
					// go forward:
					$i = $this->f->match($s, $i);
					if( $i >= 0 ){
						$this->n[$this->tos-1]++;
						$this->stack[$this->tos-1][$this->n[$this->tos-1]] = $i;
					} else {
						if( $this->n[$this->tos-1] >= $this->min ){
							return $this->stack[$this->tos-1][$this->n[$this->tos-1]];
						} else {
							break;
						}
					}
				} else {
					return $i;
				}
			} while(TRUE);

		} while( TRUE );
	}

	/*. void .*/ function rollback(){
		$n = $this->n[ --$this->tos ];
		while( $n > 0 ){
			$this->f->rollback();
			$n--;
		}
	}

	/*. void .*/ function reset(){
		$this->f->reset();
		$this->tos = 0;
	}

	/*. string .*/ function __toString(){
		if( $this->min == 0 and $this->max == 1 )
			$m = "?*";
		else if( $this->min == 0 and $this->max == PHP_INT_MAX )
			$m = "**";
		else if( $this->min == 1 and $this->max == PHP_INT_MAX )
			$m = "+*";
		else if( $this->max == PHP_INT_MAX )
			$m = "[" . $this->min . ",]*";
		else
			$m = "[" . $this->min . "," . $this->max . "]";
		return $this->f->__toString() . $m;
	}
}



/**
 * Implements the possessive group matcher "A[a,b]", where A is a sub-regex
 * and [a,b] is the (optional) range of allowed matches.
 */
/*. private .*/ class GroupMatcherPossessive extends MatcherOperator {
	private /*. MatcherOperator .*/ $f;
	private $min = 0, $max = 0;
	private $tos = 0;
	private $n = /*. (int[int]) .*/ array();

	/*. void .*/ function __construct(/*. MatcherOperator .*/ $f, /*. int .*/ $min, /*. int .*/ $max){
		$this->f = $f;
		$this->min = $min;
		$this->max = $max;
	}

	/*. void .*/ function rollback(){
		$n = $this->n[ --$this->tos];
		while( $n > 0 ){
			$this->f->rollback();
			$n--;
		}
	}

	/*. int .*/ function match(/*. string .*/ $s, /*. int .*/ $i){
		#echo __CLASS__, " i=$i $this\n";
		$n = 0;
		while( $n < $this->max ){
			$j = $this->f->match($s, $i);
			if( $j < 0 ){
				if( $n < $this->min ){
					$this->n[$this->tos++] = $n;
					$this->rollback();
					return -1;
				} else {
					$this->n[$this->tos++] = $n;
					return $i;
				}
			}
			$i = $j;
			$n++;
		}
		$this->n[$this->tos++] = $n;
		return $i;
	}

	/*. int .*/ function retry(/*. string .*/ $s){
		#echo __CLASS__, " retry $this\n";
		$this->rollback();
		return -1;
	}

	/*. void .*/ function reset(){
		$this->f->reset();
		$this->tos = 0;
	}

	/*. string .*/ function __toString(){
		if( $this->min == 0 and $this->max == 1 )
			$m = "?";
		else if( $this->min == 0 and $this->max == PHP_INT_MAX )
			$m = "*";
		else if( $this->min == 1 and $this->max == PHP_INT_MAX )
			$m = "+";
		else if( $this->max == PHP_INT_MAX )
			$m = "[" . $this->min . ",]";
		else
			$m = "[" . $this->min . "," . $this->max . "]";
		return $this->f->__toString() . $m;
	}
}


/**
 * Parses a subject string of bytes or an UTF-8 encoded string according to
 * a pattern given by a regular expression. Basically this implementation
 * operates at byte level, so any string that appear in this class has to be
 * considered as an array of bytes, but an option of the constructor enables
 * the parsing of UTF-8 encoded strings.  An instance of this class compiles
 * and holds an internal representation of the regular expression that may
 * be used several times to match against different subject strings. After
 * every successful match, designated matching sub-parts, the elements, can be
 * extracted.  The subject string may or may not match the pattern; only if
 * it match, the parts of the subject we are interested on can be extracted.
 *
 * <b>Syntax of the pattern.</b> The pattern is the logical OR of one or more terms
 * separated by vertical bar. Using the EBNF formalism, this statement can be
 * expressed as follows:
 *
 * <pre>
 * 	expression = term {"|" term};
 * </pre>
 *
 * The matching between the expression and the subject string always starts from the
 * beginning of the subject string trying every term one by one, in the order, searching
 * for a matching term. If no term matches, the whole matching fails.
 *
 * A term is a sequence of one or more factors:
 *
 * <pre>
 * 	term = factor {factor};
 * </pre>
 *
 * The term matches if all the factors match, in the order. Factors have several forms
 * that may represent a single byte, a set of bytes, a sub-expression and some other
 * special symbols, and may include a repetition quantifier:
 *
 * <pre>
 * 	factor = "^" | "$"
 * 		| "." [quantifier]
 * 		| "(" expression ")"
 * 		| "{" set "}"
 * 		| byte [quantifier];
 * </pre>
 *
 * where:
 *
 * <blockquote>
 * <code>.</code> (dot) matches a single byte or, it the UTF-8 parsing is
 * enabled, a single character.
 *
 * <code>^</code> matches the beginning of the subject.
 *
 * <code>$</code> matches the ending of the subject.
 *
 * <code>(E)</code> is a sub-expression, also named <b>element</b> through
 * this document.  Elements can be introduced to alter the order of the
 * evaluation between terms an factors or to group a sequence of factors
 * to which a quantifier has to be applied.  Any part of the subject string
 * that matches an element, at any nesting level, can be extracted from the
 * result of the parsing through the {@link it\icosaedro\regex\Element} and the {@link it\icosaedro\regex\Group}
 * interfaces, as will be explained later.
 *
 * <code>set</code> is a list of bytes (or a list of UTF-8 encoded characters
 * if UTF-8 encoding is enabled) that may match a single byte (or character)
 * of the subject.  Ranges can be expressed as <code>a-b</code>. A leading
 * exclamation character <code>!</code> yields the complementary set of the
 * set that follows. If the hyphen character has to be included literally,
 * it can be inserted either as the first character or in the last position
 * in the set; if the exclamation mark has to be included literally, it cannot
 * appear as the first character. The empty set <code>[]</code> always fails.
 * The complement of the empty set <code>[!]</code> matches any byte (or character
 * in UTF-8 mode).
 *
 * </blockquote>
 *
 * Normally every factor matches exactly once or it fails. If
 * a <b>quantifier</b> is added then the factor may match the desired
 * number of times, possibly with several attempts performed with different
 * number of matching factors.  The most general quantifier is the interval
 * <code>[min,max]</code> where min and max are two non-negative integer numbers
 * that give the minimum and the maximum number of times the factor must
 * match. Both these numbers can be omitted: if min is omitted it defaults
 * to 0; if max is omitted it defaults to PHP_INT_MAX which is also the
 * maximum allowed number. Some common abbreviations are also allowed:
 *
 * <blockquote>
 * <code>F?</code> is the same as <code>F[0,1]</code> (optional factor F)
 *
 * <code>F*</code> is the same as <code>F[0,]</code> (zero or more)
 *
 * <code>F+</code> is the same as <code>F[1,]</code> (one or more)
 * </blockquote>
 *
 * If the quantifier is present, the matching algorithm operates
 * in <b>possessive</b> mode, where the maximum number of matches is
 * attempted and no further attempts are made.  For example, the pattern
 * <code>.*</code> consumes all the remaining subject string, then the
 * matching either succeeds or fails without further attempts.
 *
 * Two modifiers can follow the quantifier to select two more alternative
 * algorithms:
 *
 * <blockquote>
 *
 * <code>?</code> performs the <b>reluctant</b> algorithm, where the
 * minimum number of matches is attempted first, then (min+1), (min+2),
 * ..., max attempts are made until the expression succeeds. For example,
 * the expression <code>.*?</code> first tries with the empty string (that
 * always succeeds), then consumes 1 byte and retries, and so on.
 *
 * <code>*</code> performs the <b>greedy</b> algorithm, first trying to
 * consume up to max factors (but not less than min) and continuing with
 * the rest of the expression; if the rest of the expression does not match,
 * then performs backtracking and retries to consume as much factors as it
 * can generating more attempts and continues the evaluation of the rest of
 * the expression; the evaluation of the factor stops when less than min
 * matching are possible.  Then, for example, the pattern <code>.**</code>
 * first tries to consume the whole remaining subject string and, if the rest
 * of the expression fails, further attempts are made consuming less bytes.
 *
 * </blockquote>
 *
 *
 * <b>Encoding of the special characters.</b> The following characters
 * have a special meaning and can match their literal value only if escaped
 * by back-slash:
 *
 * <pre>
 * 	\  .  |  (  )  [  ]  {  }  ?  *  +  ^  $
 * </pre>
 *
 * Characters that are special under PHP requires to be further escaped
 * so that, for example, the literal back-slash becomes a double back-slash
 * to meet the requirements of this class, so ending with 4 back-slashes in the
 * final PHP string "\\\\" just to match a single literal back-slash.
 * Escaping non-special characters is forbidden to leave space for future
 * enhancements of this specification.
 *
 * <b>Example 1 - Matching an integer number.</b> An integer number can
 * have a sign followed by one or more digits. In the chunk of code below,
 * we compile the regular expression first and then we test if a given
 * string does match it:
 *
 * <pre>
 * 	$p = new Pattern("{-\\+}{0-9}+\$");
 * 	$s = "1234";
 * 	if( $p-&gt;match($s) )
 *		echo "ok";
 * </pre>
 *
 * The same compiled pattern can be applied several times. Note how the special
 * characters must be escaped. Also note that a leading <code>^</code> is not required
 * because expressions are always applied starting from the beginning of the
 * subject string.
 * 	
 * <b>Enumerating and extracting groups and elements.</b> Sub-expressions enclosed
 * between round parentheses are <b>elements</b>. The element along with its
 * quantifier is a <b>group</b> of elements that match zero, one or several times.
 * For example, the group
 *
 * <center><code>(X)[1,3]</code></center>
 *
 * may match the element <code>(X)</code> from 1 up to 3 times.  Since the
 * body of the element, <code>X</code>, may in turn contain others groups,
 * this class provides an interface to retrieve also these sub-groups as
 * detailed below.
 *
 * The whole pattern must be considered as the element number 0, as if it
 * where enclosed between parentheses. This zero element may contain several
 * sub-groups that are numbered starting from 0, so that the first group
 * may be identified with the sequence of numbers 0.0 and continuing with
 * 0.1 for the second group, 0.2 for the third group and so on. Even these
 * sub-groups may contain other sub-sub-groups that are numbered starting
 * from 0 and so on:
 * 
 * <center><code>
 * <sup>0.0</sup>(<sup>0.0.0</sup>(A)B<sup>0.0.1</sup>(C))<sup>0.1</sup>(<sup>0.1.0</sup>(D)E)
 * </code></center>
 *
 * The Pattern class provides the {@link it\icosaedro\regex\Element} interface that allows
 * to access the outermost element number 0: the {@link it\icosaedro\regex\Element::start()}
 * method returns the offset of the beginning of the subject string that
 * matches the whole pattern; the {@link it\icosaedro\regex\Element::end()} method returns
 * the ending of the portion that matched the pattern; finally, the {@link
 * it\icosaedro\regex\Element::value()} method returns this portion of the subject string:
 *
 * <pre>
 * 	$p-&gt;start() =&gt; start offset of the matching
 * 	$p-&gt;end()   =&gt; end offset of the matching
 * 	$p-&gt;value() =&gt; portion of the subject string that matches
 * </pre>
 *
 * The Element interface also provides the {@link it\icosaedro\regex\Element::group($g)}
 * that retrieves the specified group as instance of the {@link it\icosaedro\regex\Group}
 * interface. Looking at the example above, $g can be only 0 or 1. The
 * Group::count() method retrieves the number of matches for the given
 * element, and {@link it\icosaedro\regex\Group::elem($i)} retrieves the element number $i with
 * 0 &le; $i &lt; count().
 *
 * Always referring to the example above, since there are no quantifiers,
 * every element must match exactly once for every group and then the
 * argument of the <code>elem($i)</code> method is elways 0 in this case:
 *
 * <pre>
 * 	$p-&gt;value() =&gt; "ABCDE"
 * 	$p-&gt;group(0)-&gt;elem(0)-&gt;value() =&gt; "ABC"
 * 	$p-&gt;group(0)-&gt;elem(0)-&gt;group(0)-&gt;elem(0)-&gt;value() =&gt; "A"
 * 	$p-&gt;group(0)-&gt;elem(0)-&gt;group(1)-&gt;elem(0)-&gt;value() =&gt; "C"
 * 	$p-&gt;group(1)-&gt;elem(0)-&gt;value() =&gt; "DE"
 * 	$p-&gt;group(1)-&gt;elem(0)-&gt;group(0)-&gt;elem(0)-&gt;value() =&gt; "D"
 * </pre>
 *
 * Note that for every element retrieved, the list of the <code>group($g)</code>
 * arguments exactly matches the path that brings from the outermost element 0
 * to the requested group, so for example 0.1.0 is the group <code>(E)</code>.
 *
 * <b>Example 2 - Parsing a string of key=value pairs.</b> Supposing a sequence
 * of lines of the form
 *
 * <pre>
 * 	$line = "alpha = 1, beta = 2, gamma = 3";
 * </pre>
 *
 * be given, we start compiling the pattern:
 *
 * <pre>
 * 	# A key is a sequence of letters and digits:
 * 	$K = "{a-zA-Z_}{a-zA-Z_0-9}*+";
 * 	# A value is an integer number:
 * 	$V = "{-\\+}{0-9}++";
 * 	# White space:
 * 	$SP = "{ \t}*+";
 * 	$p = new Pattern("$SP($K)$SP=$SP($V)$SP(,$SP($K)$SP=$SP($V))++$SP\\$");
 * </pre>
 *
 * For each line of input, we test if it matches the pattern and we extract groups
 * and elements:
 *
 * <pre>
 * 	if( $p-&gt;match($line) ){
 * 		echo $p-&gt;group(0)-&gt;elem(0)-&gt;value(); # =&gt; "alpha"
 * 		echo $p-&gt;group(1)-&gt;elem(0)-&gt;value(); # =&gt; "1"
 * 		$group2 = $p-&gt;group(2);
 * 		for($i = 0; $i &lt; $group2-&gt;count(); $i++){
 * 			echo $group2-&gt;elem($i)-&gt;group(0)-&gt;elem(0)-&gt;value(); # =&gt; "beta" and "gamma"
 * 			echo $group2-&gt;elem($i)-&gt;group(1)-&gt;elem(0)-&gt;value(); # =&gt; "2" and "3"
 * 		}
 * 	}
 * </pre>
 *
 * Note that more complex results can be easily explored by a recursive algoritm.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:18:44 $
 */
class Pattern implements Element, Printable {

	/*. private .*/ const
		SYM_LITERAL = 0,
		SYM_ANY = 1,
		SYM_OR = 2,
		SYM_LROUND = 3,
		SYM_RROUND = 4,
		SYM_LSQUARE = 5,
		SYM_RSQUARE = 6,
		SYM_LBRACE = 7,
		SYM_RBRACE = 8,
		SYM_QUESTION_MARK = 9,
		SYM_PLUS = 10,
		SYM_ASTERISK = 11,
		SYM_CIRCUMFLEX = 12,
		SYM_DOLLAR = 13,
		SYM_EOT = 14;
	
	private /*. string .*/ $re;
	private /*. string .*/ $subject;
	private $utf8 = FALSE;
	private $re_len = 0;
	private $i = 0;
	private $sym = 0;
	private $b = 0;
	private /*. MatcherOperator .*/ $p;

	private $level = 0;
	private $groups_count = 0;
	private $static_index = 0;
	private /*. MatchResult .*/ $r;
	private $end = -1;
	private /*. Element .*/ $e0;


	private /*. void .*/ function nextSym(){
		if( $this->i >= $this->re_len ){
			$this->b = -1;
			$this->sym = self::SYM_EOT;
		} else {
			$this->b = ord($this->re[ $this->i++ ]);
			// FIXME: check numbers and string juggling in switch()
			switch( chr($this->b) ){
			case ".": $this->sym = self::SYM_ANY; break;
			case "|": $this->sym = self::SYM_OR; break;
			case "(": $this->sym = self::SYM_LROUND; break;
			case ")": $this->sym = self::SYM_RROUND; break;
			case "[": $this->sym = self::SYM_LSQUARE; break;
			case "]": $this->sym = self::SYM_RSQUARE; break;
			case "{": $this->sym = self::SYM_LBRACE; break;
			case "}": $this->sym = self::SYM_RBRACE; break;
			case "?": $this->sym = self::SYM_QUESTION_MARK; break;
			case "+": $this->sym = self::SYM_PLUS; break;
			case "*": $this->sym = self::SYM_ASTERISK; break;
			case "^": $this->sym = self::SYM_CIRCUMFLEX; break;
			case "\$": $this->sym = self::SYM_DOLLAR; break;
			case "\\":
				if( $this->i >= $this->re_len )
					throw new InvalidArgumentException("missing character after escape");
				$escaped = $this->re[ $this->i++ ];
				if( strpos(MatcherOperator::SPECIAL, $escaped) === FALSE )
					throw new InvalidArgumentException("escaping non-special characters is forbidden, special characters are only: " . MatcherOperator::SPECIAL);
				$this->sym = self::SYM_LITERAL;
				$this->b = ord($escaped);
				break;
			default: $this->sym = self::SYM_LITERAL;
			}
		}
	}


	private /*. bool .*/ function isDigit()
	{
		return $this->sym == self::SYM_LITERAL
		and $this->b >= ord("0") and $this->b <= ord("9");
	}


	private /*. int .*/ function parseInt()
		/*. throws InvalidArgumentException .*/
	{
		$i = $this->i - 1;
		do {
			$this->nextSym();
		} while( $this->isDigit() );
		return Integers::parseInt( substr($this->re, $i, $this->i - $i - 1) );
	}


	/*. forward private MatcherOperator function compileExpression(); .*/

	private /*. MatcherOperator .*/ function compileFactor(){
		/*. MatcherOperator .*/ $res = NULL;
		if( $this->sym == self::SYM_LITERAL ){
			if( ! $this->utf8 ){
				$res = new LiteralMatcher(chr($this->b));
				$this->nextSym();
			} else {
				$seq_len = UTF8::sequenceLength($this->b);
				if( $seq_len <= 0 )
					throw new InvalidArgumentException("invalid UTF-8 encoding ");
				$lit = chr($this->b);
				$this->nextSym();
				for($i = 2; $i <= $seq_len; $i++){
					if( $this->sym != self::SYM_LITERAL )
						throw new InvalidArgumentException("invalid UTF-8 encoding");
					$lit = $lit . chr($this->b);
					$this->nextSym();
				}
				$res = new LiteralMatcher($lit);
			}
		} else if( $this->sym == self::SYM_LBRACE ){
			$this->nextSym();
			$set = "";
			while( $this->sym == self::SYM_LITERAL ){
				$set .= chr($this->b);
				$this->nextSym();
			}
			if( $this->sym != self::SYM_RBRACE )
				throw new InvalidArgumentException("expected `}'");
			$this->nextSym();
			if( $this->utf8 )
				$res = new UTF8SetMatcher($set);
			else
				$res = new SetMatcher($set);
		} else if( $this->sym == self::SYM_ANY ){
			if( ! $this->utf8 )
				$res = AnyMatcher::getInstance();
			else
				$res = new AnyUTF8Matcher();
			$this->nextSym();
		} else if( $this->sym == self::SYM_LROUND ){
			$level = $this->level++;
			$si = $this->static_index;  $this->static_index = 0;
			$gc = $this->groups_count;  $this->groups_count = 0;
			$this->nextSym();
			$e = $this->compileExpression();
			if( $this->sym != self::SYM_RROUND )
				throw new InvalidArgumentException("expected `)'");
			$this->nextSym();
			$res = new ElementMatcher($level, $si, $this->static_index, $e, $this->r);
			$this->static_index = $si + 1;
			$this->groups_count = $gc + 1;
		} else if( $this->sym == self::SYM_CIRCUMFLEX ){
			$this->nextSym();
			return BeginningMatcher::getInstance();
		} else if( $this->sym == self::SYM_DOLLAR ){
			$this->nextSym();
			return EndingMatcher::getInstance();
		} else {
			return NULL;
		}

		# Parse quantifier:
		if( $this->sym == self::SYM_QUESTION_MARK ){
			$this->nextSym();
			$a = 0;  $b = 1;
		} else if( $this->sym == self::SYM_PLUS ){
			$this->nextSym();
			$a = 1;  $b = PHP_INT_MAX;
		} else if( $this->sym == self::SYM_ASTERISK ){
			$this->nextSym();
			$a = 0;  $b = PHP_INT_MAX;
		} else if( $this->sym == self::SYM_LSQUARE ){
			$this->nextSym();
			$a = 0;
			if( $this->isDigit() )
				$a = $this->parseInt();
			$b = $a;
			if( $this->sym == self::SYM_LITERAL and $this->b == ord(",") ){
				$this->nextSym();
				if( $this->isDigit() )
					$b = $this->parseInt();
				else
					$b = PHP_INT_MAX;
			}
			if( ! (0 <= $a and $a <= $b and 1 <= $b ) )
				throw new InvalidArgumentException("invalid range");
			if( $this->sym != self::SYM_RSQUARE )
				throw new InvalidArgumentException("expected `]'");
			$this->nextSym();
		} else {
			# No quantifier.
			return $res;
		}

		# Choose the algo: possessive (default), reluctant or greedy:
		if( $this->sym == self::SYM_ASTERISK ){
			$this->nextSym();
			$res = new GroupMatcherGreedy($res, $a, $b);
		} else if( $this->sym == self::SYM_QUESTION_MARK ){
			$this->nextSym();
			$res = new GroupMatcherReluctant($res, $a, $b);
		} else {
			$res = new GroupMatcherPossessive($res, $a, $b);
		}

		return $res;
	}


	private /*. MatcherOperator .*/ function buildMatchAnd(/*. MatcherOperator .*/ $a, /*. MatcherOperator .*/ $b){
		if( $a instanceof LiteralMatcher ){
			$al = cast("it\\icosaedro\\regex\\LiteralMatcher", $a);
			if( $b instanceof LiteralMatcher ){
				# Optimize: "a" AND "b" => "ab":
				$bl = cast("it\\icosaedro\\regex\\LiteralMatcher", $b);
				return new LiteralMatcher($al->literal . $bl->literal);
			} else if( $b instanceof AndMatcher ){
				# Optimize: "a" AND ("b" AND X) => "ab" AND X:
				$ba = cast("it\\icosaedro\\regex\\AndMatcher", $b);
				if( $ba->a instanceof LiteralMatcher ){
					$baa = cast("it\\icosaedro\\regex\\LiteralMatcher", $ba->a);
					return new AndMatcher( new LiteralMatcher($al->literal . $baa->literal), $ba->b);
				}
			}
		}
		# No optimization possible.
		return new AndMatcher($a, $b);
	}


	private /*. MatcherOperator .*/ function compileTerm(){
		$a = /*. (MatcherOperator[int]) .*/ array();
		while( ($f = $this->compileFactor()) !== NULL )
			$a[] = $f;
		$n = count($a);
		if( $n == 0 )
			throw new InvalidArgumentException("expected factor");
		if( $n == 1 )
			return $a[0];
		$res = $a[$n-1];
		for($i = $n - 2; $i >= 0; $i--)
			$res = $this->buildMatchAnd($a[$i], $res);
		return $res;
	}
		

	private /*. MatcherOperator .*/ function compileExpression(){
		$res = $this->compileTerm();
		if( $this->sym == self::SYM_OR ){
			$this->nextSym();
			$res = new OrMatcher($res, $this->compileExpression());
		}
		return $res;
	}


	/**
	 * Generates a literal representation of the string that matches itself.
	 * Characters that have special meaning in the regular expression are
	 * properly escaped, so that dynamically generated patterns can be built.
	 * @param string $s The string intended to be matched literally.
	 * @return string Escaped string that may be inserted in a regular
	 * expression to match itself literally.
	 */
	static function escape($s){
		return MatcherOperator::escape($s);
	}
		

	/**
	 * Compiles the specified regular expression for later usage.
	 * Once compiled, the same pattern can be applied several times to
	 * different subject strings.
	 * @param string $re The regular expression to compile.
	 * @param bool $utf8 True if the regular expression and the string to
	 * be matched are to be assumed UTF-8 BMP encoded.
	 * @return void
	 * @throws InvalidArgumentException Invalid regular expression syntax.
	 * The message also reporsts the exact offset in the $re where parsing
	 * stopped as byte index in the $re string.
	 */
	function __construct($re, $utf8 = FALSE){
		$this->re = $re;
		$this->utf8 = $utf8;
		$this->re_len = strlen($re);
		$this->r = new MatchResult();
		$this->level = 1;  # level 0 is the matched portion of the subject string
		$this->i = 0;
		try {
			$this->nextSym();
			$this->p = $this->compileExpression();
			if( $this->sym != self::SYM_EOT )
				throw new InvalidArgumentException("unknown/unexpected symbol");
		}
		catch(InvalidArgumentException $e){
			throw new InvalidArgumentException($e->getMessage()
				. " in " . Strings::toLiteral($re) . " at byte offset " . $this->i);
		}
	}


	/**
	 * Tells if the subject string matches this pattern.
	 * @param string $s The subject string of bytes. The NULL value
	 * behaves just like the empty string.
	 * @param int $start Matching of the subject string starts from this
	 * offset.
	 * @return bool True if the subject string matches this pattern.
	 */
	function match($s, $start = 0){
		$this->subject = $s;
		if( $this->r->marks_n > 0 ){
			$this->r->reset();
			$this->p->reset();
		}
		$this->e0 = NULL;
		$this->end = $this->p->match($s, $start);
		if( $this->end < 0 )
			return FALSE;
		# add element 0, the portion of the subject string that matches:
		$this->r->push(0, $start, $this->end, 0, $this->groups_count);

		/* ! ! ! ! ! ! ! ! ! ! !
		for($i = 0; $i < $this->r->marks_n; $i++){
			echo "mark: ", $this->r->marks[$i][0], " ", $this->r->marks[$i][1],
				" ", $this->r->marks[$i][2], "\n";
		}
		*/
		return TRUE;
	}


	private /*. Element .*/ function element0(){
		if( $this->end < 0 )
			throw new LogicException("cannot select element 0 -- either the matching failed or no match performed yet");
		if( $this->e0 == NULL ){
			$this->e0 = new MatchedElement(
				$this->subject,
				$this->r->marks, $this->r->marks_n - 1);
		}
		return $this->e0;
	}


	/*. int .*/ function start(){
		return $this->element0()->start();
	}


	/*. int .*/ function end(){
		return $this->element0()->end();
	}


	/*. string .*/ function value(){
		return $this->element0()->value();
	}


	/*. int .*/ function count(){
		return $this->element0()->count();
	}


	/*. Group .*/ function group(/*. int .*/ $g){
		return $this->element0()->group($g);
	}


	/**
	 * Returns this pattern in canonicized, ASCII form.
	 * @return string This pattern in canonicized, ASCII form.
	 */
	function __toString(){
		return $this->p->__toString();
	}


	private /*. string .*/ function elementToString(/*. Element .*/ $e, /*. string .*/ $indent, /*. string .*/ $separator){
		$res = "$indent " . Strings::toLiteral($e->value());
		if( $e->count() > 0 ){
			for($i = 0; $i < $e->count(); $i++){
				$g = $e->group($i);
				for($j = 0; $j < $g->count(); $j++){
					$res .= $separator . $this->elementToString($g->elem($j), "$indent.$i", $separator);
				}
			}
		}
		return $res;
	}


	/**
	 * Returns the result of the last successful match as a structured
	 * string. Mostly useful for testing. The returned string may have a form
	 * similar to this one, although it might vary in future implementations:
	 * <pre>
	 * 0 "alpha = 1, beta = 2, gamma = 3"
	 * 0.0 "alpha"
	 * 0.1 "1"
	 * 0.2 ", beta = 2"
	 * 0.2.0 "beta"
	 * 0.2.1 "2"
	 * 0.2 ", gamma = 3"
	 * 0.2.0 "gamma"
	 * 0.2.1 "3"
	 * </pre>
	 * Every line is an element; the numbers separated by dot are paths
	 * of groups; the literal string between double quotes is the ASCII
	 * representation of the matching string.
	 * @param string $separator Separator string between elements.
	 * @return string Readable representation of all the matched groups and
	 * elements.
	 * @throws LogicException Matching failed or no match performed yet.
	 */
	function resultAsString($separator = "\n"){
		return $this->elementToString($this->element0(), "0", $separator);
	}


	/**
	 * Tells if the regular expression matches a given subject string.
	 * Convenience method for simple one-shot tests.
	 * @param string $re Regular expression.
	 * @param string $s Subject string. NULL behaves just like the empty string.
	 * @param int $start Matching of the subject string starts from this
	 * offset.
	 * @return bool True if the subject string matches the regular expression.
	 * @throws InvalidArgumentException Invalid regular expression syntax.
	 */
	static function matches($re, $s, $start = 0){
		$p = new self($re);
		return $p->match($s, $start);
	}

}
