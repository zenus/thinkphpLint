<?php

namespace it\icosaedro\lint\documentator;

require_once __DIR__ . "/../../../../all.php";

/**
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/18 22:37:58 $
 */
class HtmlUtils {
	
	
	/**
	 * Escape the fragment part of the URL that follows '#'.
	 * @param string $s
	 * @return string
	 */
	public static function escapeFragment($s)
	{
		return rawurlencode($s);
	}

}
