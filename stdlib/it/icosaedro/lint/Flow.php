<?php

namespace it\icosaedro\lint;

/**
 * Execution path for static flow analysis.
 * 
 * For every statement it parses, PHPLint keeps track of every
 * possible execution path, so that invalid source structures can
 * be detected. The picture below illustrates the execution paths
 * of which PHPLint takes care:
 *
 * <pre>
 *                          :            ^
 *                          :            | continue
 *                          v            |
 *                    +-----------+      /
 *         return     |           |-----/ 
 *    &lt;---------------| statement |
 *         or exit    |           |-----\ 
 *         or throw   +-----------+      \
 *                          |            | break
 *                          | next       |
 *                          v            v
 * </pre>
 * 
 * where:
 *
 * <blockquote>
 * 	`next' means that the statement that follows may be
 * 	executed at least in some cases. If this path is
 * 	missing, the statement that follows is unreachable.
 *
 * 	`return' means that the statement may cause a return to
 * 	the caller or elsewhere (exit, die, throw).
 *
 * 	`continue' means a jump to the beginning or the current
 * 	loop.
 *
 * 	`break' means a jump outside the loop or switch()
 * 	statement.
 * </blockquote>
 * 
 * Every statement sets one or more execution paths that define
 * which statement will be executed next. The simplest statements,
 * like the assignment <code>$i=123;</code> implies that the statement
 * executed next will be the next that follows in the source, so the
 * execution path is `next'. A structured statement like
 *
 * <pre>
 * if( expression )
 *     break;
 * else
 *     return;
 * </pre>
 *
 * sets the execution path to `break|return'. Note that the lack of
 * the `next' path also means that the following statement, if it
 * exists, is unreachable and must be signaled as error.
 *
 * The algebraic rules of composition of the execution paths
 * inside the structured statements for(), while(), etc. are
 * detailed in the code.
 *
 * The body of a function or method returning void must have the
 * execution path `next' or `return'. If the function or method returns
 * a non-void value then the body must be `return', as `next'
 * would indicate an execution path in which the function or
 * method terminates without returning the promised value, while
 * `break' and `continue' should never be set by the internal
 * logic of PHPLint (they may be set only *inside* a loop or switch()).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/12 14:48:04 $
 */
class Flow {
	
	/**
	 * Continue with the next statement. 
	 */
	const NEXT_MASK = 1;
	
	/**
	 * Interrupted by `break'.
	 */
	const BREAK_MASK = 2;
	
	/**
	 * Interrupted by `continue'.
	 */
	const CONTINUE_MASK = 4;
	
	/**
	 * Interrupted by `return', `exit', `die', `throw'.
	 */
	const RETURN_MASK = 8;
	
}
