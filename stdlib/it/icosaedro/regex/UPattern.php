<?php

namespace it\icosaedro\regex;

require_once __DIR__ . "/../../../all.php";

/*. require_module 'spl'; .*/

use InvalidArgumentException;
use LogicException;
use OutOfRangeException;
use RuntimeException;
use it\icosaedro\containers\Printable;
use it\icosaedro\utils\Strings;
use it\icosaedro\utils\UString;
use it\icosaedro\utils\UTF8;


/*. private .*/ final class UEmptyGroup implements UGroup {

	private static /*. UGroup .*/ $instance;

	private /*. void .*/ function __construct(){}

	static /*. UGroup .*/ function getInstance(){
		if( self::$instance == NULL )
			self::$instance = new UEmptyGroup();
		return self::$instance;
	}

	/*. int .*/ function count(){
		return 0;
	}


	/*. UElement .*/ function elem(/*. int .*/ $i){
		throw new OutOfRangeException("empty group");
	}

}


/*. forward private class UMatchedGroup implements UGroup {
	void function __construct(UString $s, Group $pg);
	}
.*/


/** Wraps the Element object. */
/*. private .*/ class UMatchedElement implements UElement {

	private /*. UString .*/ $s;
	private /*. Element .*/ $pe;


	/*. void .*/ function __construct(/*. UString .*/ $s, /*. Element .*/ $pe){
		$this->s = $s;
		$this->pe = $pe;
	}


	/*. int .*/ function start(){
		return UTF8::codepointIndex($this->s->toUTF8(), $this->pe->start());
	}


	/*. int .*/ function end(){
		return UTF8::codepointIndex($this->s->toUTF8(), $this->pe->end());
	}


	/*. UString .*/ function value(){
		return UString::fromUTF8(
			Strings::substring(
				$this->s->toUTF8(), $this->pe->start(), $this->pe->end() )
		);
	}


	function count(){
		return  $this->pe->count();
	}


	/*. UGroup .*/ function group(/*. int .*/ $g){
		$pg = $this->pe->group($g);
		if( $pg->count() == 0 )
			return UEmptyGroup::getInstance();
		else
			return new UMatchedGroup($this->s, $pg);
	}

}


/** Wraps the Group object. */
/*. private .*/ class UMatchedGroup implements UGroup {

	private /*. UString .*/ $s;
	private /*. Group .*/ $pg;

	/*. void .*/ function __construct(/*. UString .*/ $s, /*. Group .*/ $pg){
		$this->s = $s;
		$this->pg = $pg;
	}


	/*. int .*/ function count(){
		return $this->pg->count();
	}


	/*. UElement .*/ function elem(/*. int .*/ $i){
		return new UMatchedElement($this->s, $this->pg->elem($i));
	}

}


/**
 * Parses subject Unicode string according to a pattern given by a regular
 * expression.  An instance of this class compiles and holds an internal
 * representation of the regular expression that may be used several
 * times to match against different subject strings. After every successful
 * match, designated matching sub-parts, the elements, can be extracted.
 * The subject string may or may not match the pattern; only if it match,
 * the parts of the subject we are interested on can be extracted.
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
 * The term matches if all the factors match, in the order. Factors have
 * several forms that may represent a single character, a set of characters,
 * a sub-expression and some other special symbols, and may include a
 * repetition quantifier:
 *
 * <pre>
 * 	factor = "^" | "$"
 * 		| "." [quantifier]
 * 		| "(" expression ")"
 * 		| "{" set "}"
 * 		| character [quantifier];
 * </pre>
 *
 * where:
 *
 * <blockquote>
 * <code>.</code> (dot) matches a single character.
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
 * result of the parsing through the {@link it\icosaedro\regex\UElement} and the {@link it\icosaedro\regex\UGroup}
 * interfaces, as will be explained later.
 *
 * <code>set</code> is a list of characters that may match a single character
 * of the subject.  Ranges can be expressed as <code>a-b</code>. A leading
 * exclamation character <code>!</code> yields the complement set of the
 * set that follows. If the hyphen character has to be included literally, it
 * can be inserted either as the first character or in the last position in
 * the set; if the exclamation mark has to be included literally, it cannot
 * appear as the first character. The empty set <code>[]</code> always fails.
 * The complement of the empty set <code>[!]</code> matches any character.
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
 * always succeeds), then consumes 1 character and retries, and so on.
 *
 * <code>*</code> performs the <b>greedy</b> algorithm, first trying to
 * consume up to max factors (but not less than min) and continuing with
 * the rest of the expression; if the rest of the expression does not match,
 * then performs backtracking and retries to consume as much factors as it
 * can generating more attempts and continues the evaluation of the rest of
 * the expression; the evaluation of the factor stops when less than min
 * matching are possible.  Then, for example, the pattern <code>.**</code>
 * first tries to consume the whole remaining subject string and, if the rest
 * of the expression fails, further attempts are made consuming less characters.
 *
 * </blockquote>
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
 * 	$p = new UPattern( UString::fromASCII("{-\\+}{0-9}+\$") );
 * 	$s = UString::fromASCII("1234");
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
 * The UPattern class provides the {@link it\icosaedro\regex\UElement} interface that allows
 * to access the outermost element number 0: the {@link it\icosaedro\regex\UElement::start()}
 * method returns the offset of the beginning of the subject string that
 * matches the whole pattern; the {@link it\icosaedro\regex\UElement::end()} method returns
 * the ending of the portion that matched the pattern; finally, the {@link
 * it\icosaedro\regex\UElement::value()} method returns this portion of the subject string:
 *
 * <pre>
 * 	$p-&gt;start() =&gt; start offset of the matching
 * 	$p-&gt;end()   =&gt; end offset of the matching
 * 	$p-&gt;value() =&gt; portion of the subject string that matches
 * </pre>
 *
 * The UElement interface also provides the {@link it\icosaedro\regex\UElement::group($g)}
 * that retrieves the specified group as instance of the {@link it\icosaedro\regex\UGroup}
 * interface. Looking at the example above, $g can be only 0 or 1. The
 * UGroup::count() method retrieves the number of matches for the given
 * element, and {@link it\icosaedro\regex\UGroup::elem($i)} retrieves the element number $i with
 * 0 &le; $i &lt; count().
 *
 * Always referring to the example above, since there are no quantifiers,
 * every element must match exactly once for every group and then the
 * argument of the <code>elem($i)</code> method is always 0 in this case:
 *
 * <pre>
 * 	$p-&gt;value() =&gt; "ABCDE" (as UString object)
 * 	$p-&gt;group(0)-&gt;elem(0)-&gt;value() =&gt; "ABC" (as UString object)
 * 	$p-&gt;group(0)-&gt;elem(0)-&gt;group(0)-&gt;elem(0)-&gt;value() =&gt; "A" (as UString object)
 * 	$p-&gt;group(0)-&gt;elem(0)-&gt;group(1)-&gt;elem(0)-&gt;value() =&gt; "C" (as UString object)
 * 	$p-&gt;group(1)-&gt;elem(0)-&gt;value() =&gt; "DE" (as UString object)
 * 	$p-&gt;group(1)-&gt;elem(0)-&gt;group(0)-&gt;elem(0)-&gt;value() =&gt; "D" (as UString object)
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
 * 	$line = UString::fromASCII("alpha = 1, beta = 2, gamma = 3");
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
 * 	$pattern = UString::fromASCII("$SP($K)$SP=$SP($V)$SP(,$SP($K)$SP=$SP($V))++$SP\\$");
 * 	$p = new UPattern($pattern);
 * </pre>
 *
 * For each line of input, we test if it matches the pattern and we extract groups
 * and elements:
 *
 * <pre>
 * 	if( $p-&gt;match($line) ){
 * 		echo $p-&gt;group(0)-&gt;elem(0)-&gt;value()-&gt;toASCII(); # =&gt; "alpha"
 * 		echo $p-&gt;group(1)-&gt;elem(0)-&gt;value()-&gt;toASCII(); # =&gt; "1"
 * 		$group2 = $p-&gt;group(2);
 * 		for($i = 0; $i &lt; $group2-&gt;count(); $i++){
 * 			echo $group2-&gt;elem($i)-&gt;group(0)-&gt;elem(0)-&gt;value()-&gt;toASCII(); # =&gt; "beta" and "gamma"
 * 			echo $group2-&gt;elem($i)-&gt;group(1)-&gt;elem(0)-&gt;value()-&gt;toASCII(); # =&gt; "2" and "3"
 * 		}
 * 	}
 * </pre>
 *
 * Note that more complex results can be easily explored by a recursive algoritm.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:18:44 $
 */
class UPattern implements UElement, Printable {

	private /*. UString .*/ $subject;
	private /*. Pattern .*/ $p;

	/**
	 * Compiles the specified regular expression for later usage.
	 * Once compiled, the same pattern can be applied several times to
	 * different subject strings.
	 * @param UString $re The regular expression to compile.
	 * @return void
	 * @throws InvalidArgumentException Invalid regular expression syntax.
	 */
	function __construct($re){
		$this->p = new Pattern($re->toUTF8(), TRUE);
	}


	/**
	 * Tells if the subject string matches this pattern.
	 * @param UString $s The subject string.
	 * @param int $start Matching of the subject string starts from this
	 * offset.
	 * @return bool True if the subject string matches this pattern.
	 */
	function match($s, $start = 0){
		$this->subject = $s;
		return $this->p->match($s->toUTF8(), UTF8::byteIndex($s->toUTF8(), $start));
	}


	/*. int .*/ function start(){
		return UTF8::codepointIndex($this->subject->toUTF8(), $this->p->start());
	}


	/*. int .*/ function end(){
		return UTF8::codepointIndex($this->subject->toUTF8(), $this->p->end());
	}


	/*. UString .*/ function value(){
		# FIXME: performs useless UTF-8 encoding check.
		# The same also for the UElement::value() method.
		return UString::fromUTF8( $this->p->value() );
	}


	/*. int .*/ function count(){
		return $this->p->count();
	}


	/*. UGroup .*/ function group(/*. int .*/ $g){
		$pg = $this->p->group($g);
		if( $pg->count() == 0 )
			return UEmptyGroup::getInstance();
		else
			return new UMatchedGroup($this->subject, $pg);
	}


	/**
	 * Returns this pattern in canonicized, ASCII form.
	 * @return string This pattern in canonicized, ASCII form.
	 */
	function __toString(){
		return $this->p->__toString();
	}


	private /*. UString .*/ function elementToString(/*. UElement .*/ $e, /*. UString .*/ $indent, /*. UString .*/ $separator){
		$res = $indent->append( UString::fromASCII(" ") )
			->append( $e->value()->toLiteral() );
		if( $e->count() > 0 ){
			for($i = 0; $i < $e->count(); $i++){
				$g = $e->group($i);
				for($j = 0; $j < $g->count(); $j++){
					$res = $res
						->append($separator)
						->append(
							$this->elementToString($g->elem($j),
							$indent->append( UString::fromASCII(".") )->append( UString::fromASCII("$i") ),
							$separator)
						);
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
	 * of groups; the literal string between double quotes is the literal
	 * representation of the matching string.
	 * @param UString $separator Separator string between elements.
	 * @return UString Readable representation of all the matched groups and
	 * elements.
	 * @throws LogicException Matching failed or no match performed yet.
	 */
	function resultAsUString($separator){
		return $this->elementToString($this, UString::fromASCII("0"), $separator);
	}


	/**
	 * Tells if the regular expression matches a given subject string.
	 * Convenience method for simple one-shot tests.
	 * @param UString $re Regular expression.
	 * @param UString $s Subject string. NULL behaves just like the empty string.
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
