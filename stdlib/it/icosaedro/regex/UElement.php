<?php

namespace it\icosaedro\regex;

require_once __DIR__ . "/../../../all.php";

use OutOfRangeException;
use it\icosaedro\utils\UString;

/**
 * Interface to access one matched element of a regular expression.
 * An element is any sub-expression enclosed between round parentheses.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:18:44 $
 */
interface UElement {

	/**
	 * Returns the starting offset of the element in the subject string.
	 * @return int Starting offset of the element in the subject string.
	 */
	function start();

	/**
	 * Returns the ending offset of the element in the subject string.
	 * @return int Ending offset of the element in the subject string.
	 */
	function end();

	/**
	 * Returns the element as a string of bytes.
	 * @return UString The element as a string of bytes. The returned string
	 * is exactly <code>(end()-start())</code> bytes long.
	 */
	function value();

	/**
	 * Returns the number of nested groups. For example, the element
	 * <code>((A)B(C)+)</code> contains two nested groups: <code>(A)</code>
	 * and <code>(C)+</code>; the first group has index 0, the second group
	 * has index 1.
	 * @return int Number of nested groups.
	 */
	function count();

	/**
	 * Returns a nested group.
	 * @param int $g Index of the nested group in the range <code>0 &le; $g &lt;
	 * count()</code>. The first group of this element has index 0.
	 * @return UGroup The requested group. If the specified group.
	 * @throws OutOfRangeException Index out the range.
	 */
	function group($g);
}
