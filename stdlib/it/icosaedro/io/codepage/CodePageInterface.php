<?php

namespace it\icosaedro\io\codepage;
require_once __DIR__ . "/../../../../autoload.php";
use it\icosaedro\io\IOException;
use it\icosaedro\utils\UString;
use it\icosaedro\containers\Printable;

/**
 * Code page translators may implement this interface.
 * A code page translator translates between file names given as Unicode
 * strings and file names given as array of bytes.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2012/03/17 17:10:43 $
 */
interface CodePageInterface extends Printable {

#	/**
#	 * Returns the code page name implemented by this object.
#	 * @return string Code page number implemented by this object.
#	 * Examples: "1252" (Windows western european code page), "UTF-8"
#	 * (Unix, Linux), "ASCII".
#	 */
#	function __toString();

	/**
	 * Encodes the file name to the current code page table.
	 * @param UString $name Unicode name of the file.
	 * @return string Translated file name.
	 * @throws IOException Translation failed.
	 */
	function encode($name);

	/**
	 * Decodes the file name from current code page table.
	 * @param string $name File name to decode.
	 * @return UString Translated file name.
	 * @throws IOException Translation failed.
	 */
	function decode($name);

}
