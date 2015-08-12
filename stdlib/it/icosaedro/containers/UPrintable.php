<?php

namespace it\icosaedro\containers;

require_once __DIR__ . "/../utils/UString.php";
use it\icosaedro\utils\UString;

/**
 * Object capable to provide its own readable representation as Unicode
 * string.
 * @package UPrintable
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2012/03/07 21:26:26 $
 */
interface UPrintable {

	/**
	 * Return a readable representation of the object.
	 * @return UString Readable representation of the object.
	 */
	function toUString();

}
