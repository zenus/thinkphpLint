<?php

namespace it\icosaedro\lint\expressions;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;

/**
 * Skip unknown element in expression. These methods always return the unknown
 * type as a result, and try to keep signaled errors to a minimum, since the
 * client code already complained and simply wants to continue anyway.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:50:32 $
 */
class SkipUnknown {
	
	/*. forward public static Type function anything(Globals $globals); .*/
	
	/**
	 *
	 * @param Symbol $sym
	 * @return boolean 
	 */
	public static function canSkip($sym){
		return $sym === Symbol::$sym_lround
			|| $sym === Symbol::$sym_arrow
			|| $sym === Symbol::$sym_double_colon
			|| $sym === Symbol::$sym_lsquare
			|| $sym === Symbol::$sym_incr
			|| $sym === Symbol::$sym_decr
			|| $sym === Symbol::$sym_variable
			|| $sym === Symbol::$sym_assign
			|| $sym === Symbol::$sym_plus_assign
			|| $sym === Symbol::$sym_minus_assign
			|| $sym === Symbol::$sym_times_assign
			|| $sym === Symbol::$sym_div_assign
			|| $sym === Symbol::$sym_period_assign
			|| $sym === Symbol::$sym_mod_assign
			|| $sym === Symbol::$sym_bit_and_assign
			|| $sym === Symbol::$sym_bit_or_assign
			|| $sym === Symbol::$sym_bit_xor_assign
			|| $sym === Symbol::$sym_lshift_assign
			|| $sym === Symbol::$sym_rshift_assign;
	}

	/**
	 * Skip unknown function or method call. Also used to skip variable-name
	 * calls $v().
	 * We enter with the symbol '('.
	 * @param Globals $globals
	 * @return void
	 */
	private static function functionCall($globals) {
		$scanner = $globals->curr_pkg->scanner;
		$scanner->readSym();
		if ($scanner->sym === Symbol::$sym_rround) {
			$scanner->readSym();
			return;
		}
		do {
			/* $ignore = */ Expression::parse($globals);
			if ($scanner->sym === Symbol::$sym_comma) {
				$scanner->readSym();
			} else if ($scanner->sym === Symbol::$sym_rround) {
				$scanner->readSym();
				return;
			}
		} while (TRUE);
	}

	/**
	 * Skip object dereferencing operator <code>-&gt;</code>.
	 * We enter with the symbol '-&gt;'.
	 * @param Globals $globals
	 * @return void
	 */
	private static function objectDereferencing($globals) {
		$scanner = $globals->curr_pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_identifier, "expected ->IDENTIFIER");
		$scanner->readSym();
	}

	/**
	 * Skip class dereferencing operator <code>::</code>.
	 * We enter with the symbol '::'.
	 * @param Globals $globals
	 * @return void
	 */
	private static function classDereferencing($globals) {
		$scanner = $globals->curr_pkg->scanner;
		$scanner->readSym();
		if( $scanner->sym === Symbol::$sym_identifier
		|| $scanner->sym === Symbol::$sym_variable )
			$scanner->readSym();
		else
			$globals->logger->error($scanner->here(), "expected ::CONSTANT, ::\$PROPERTY or ::METHOD() but found " . $scanner->sym);
	}

	/**
	 * Skip array dereferencing operator <code>[</code>.
	 * We enter with the symbol '['.
	 * @param Globals $globals
	 * @return void
	 */
	private static function arrayDereferencing($globals) {
		$scanner = $globals->curr_pkg->scanner;
		$scanner->readSym();
		/* $ignore = */ Expression::parse($globals);
		$globals->expect(Symbol::$sym_rsquare, "expected `]'");
		$scanner->readSym();
	}
	
	
	/**
	 * Skip anything unknown between function or method call, array
	 * dereferencing, object dereferencing, post-incremente, post-decrement,
	 * variable.
	 * Keeps skipping any following of these operators and stops on any other
	 * symbol or after increment/decrement operators.
	 * We enter with any of these symbols:
	 * <blockquote><pre>
	 * ( [ -&gt; :: ++ -- = .= += -= *= /= %= &amp;= |= ^= &lt;&lt;= &gt;&gt;= $VARIABLE instanceof
	 * </pre></blockquote>
	 * FIXME: misleading name: this function cannot skip "anything";
	 * use "canSkip()" first.
	 * @param Globals $globals 
	 * @return Type Always returns the unknown type.
	 */
	public static function anything($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$found = FALSE; // if we got at least one of the expected symbols
		
		//echo "FIXME: skip unknown: sym=".$scanner->sym."\n";
		
		do {
			
			$sym = $scanner->sym;
			
			if( $sym === Symbol::$sym_lround ){
				self::functionCall($globals);

			} else if( $sym === Symbol::$sym_arrow ){
				self::objectDereferencing($globals);

			} else if( $sym === Symbol::$sym_double_colon ){
				self::classDereferencing($globals);

			} else if( $sym === Symbol::$sym_lsquare ){
				self::arrayDereferencing($globals);
			
			} else if( $sym === Symbol::$sym_incr
			|| $scanner->sym === Symbol::$sym_decr ){
				$scanner->readSym();
				return Globals::$unknown_type;
			
			} else if( $sym === Symbol::$sym_variable ){
				$scanner->readSym();
			
			} else if( $sym === Symbol::$sym_assign
			|| $sym === Symbol::$sym_plus_assign
			|| $sym === Symbol::$sym_minus_assign
			|| $sym === Symbol::$sym_times_assign
			|| $sym === Symbol::$sym_div_assign
			|| $sym === Symbol::$sym_period_assign
			|| $sym === Symbol::$sym_mod_assign
			|| $sym === Symbol::$sym_bit_and_assign
			|| $sym === Symbol::$sym_bit_or_assign
			|| $sym === Symbol::$sym_bit_xor_assign
			|| $sym === Symbol::$sym_lshift_assign
			|| $sym === Symbol::$sym_rshift_assign){
				$scanner->readSym();
				/* $r = */ Expression::parse($globals);
				return Globals::$unknown_type;
				
			} else {
				
				if( ! $found )
					throw new \RuntimeException("cannot skip unexpected symbol $sym");
				
				return Globals::$unknown_type;
			}
			
			$found = TRUE;
			
		} while(TRUE);
	}

}
