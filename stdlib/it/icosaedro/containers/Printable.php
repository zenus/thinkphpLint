<?php

namespace it\icosaedro\containers;

/**
 * An object that has a textual representation as an ASCII string.
 * The string should contain only ASCII printable characters an possibly
 * tabulation and new-line separators. Non-ASCII codes might yield
 * unpredictable results in the client code, so implementations capable
 * to generate Unicode strings should implement the UPrintable interface
 * instead.
 */
interface Printable {

	/**
	 * Return a readable representation of the object.
	 * @return string Readable representation of the object.
	 */
	function __toString();

}
