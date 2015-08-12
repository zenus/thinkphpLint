<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/../../../errors.php";

use it\icosaedro\io\IOException;
use it\icosaedro\utils\UString;
use it\icosaedro\io\codepage\CodePageInterface;
use it\icosaedro\io\codepage\GenericCodePage;
use it\icosaedro\io\codepage\WindowsCodePage;

/**
 * File name encoding, decoding and manipulation.
 *
 * <b>File name encoding issue.</b>
 * At least up to PHP 5.4, file names are encoded using strings of bytes;
 * PHP does not specify and does not assume any specific encoding. Since
 * most modern file systems today support the full Unicode charset, a problem
 * arise when file names exchanged between PHP and the file system contains
 * non-ASCII characters in their name or in their path:
 * 
 * <ul>
 *
 * <li>
 * Under <b>Windows NT</b> file names are encoded with 16 bits per character,
 * and the full Unicode char set is supported. However, PHP sticks on the
 * "ANSI compatibility mode" where a conversion table (named <i>code
 * page</i>) performs the translation between the PHP internal string of
 * bytes and the Unicode char set.  Users can choose the code page that better
 * match their needs from the control panel "Regional and Language Options",
 * "Formats" tab panel (that sets the LC_CTYPE locale parameter) <b>and</b>
 * the "Administrative" tab panel, "Language for non-Unicode programs" (that
 * set the best-fit code page table that translates from Unicode to bytes and
 * vice-versa). Unfortunately, UTF-8 is not allowed here, so this solution is
 * far from being perfect because whatever option you choose only a subset of
 * the Unicode characters will be available for file names to non-Unicode
 * aware programs. Once your choice is made, the {@link setlocale()}
 * function provides the LC_LCTYPE parameter that may evaluate to something
 * like <code>"English_United States.1252"</code> giving the current code
 * page mapping ("1252" in this example).  For backward compatibility with
 * older DOS programs, the terminal emulator <code>cmd.exe</code> starts by
 * default with the 437 code page, so you may want to set the UTF-8 encoding
 * with the command <code>chcp 65001</code> and select the Lucida Console
 * font capable to display much more symbols than the default Raster Fonts.
 * </li>
 *
 * <li>
 * Under <b>Unix and Linux</b> file names are just arbitrary sequences of
 * bytes, with the only exception of the zero byte which is reserved for
 * internal use (it is the string ending marker). The environment variable
 * LC_CTYPE may be used to specify the intended encoding, for example
 * <code>"en_US.UTF-8"</code> is the best option to set as it allows to
 * represent the full Unicode char set. This same environment variable
 * is also recognized by terminal emulators, so that they set properly
 * the displayable char set.
 * </li>
 *
 * </ul>
 *
 * This class allows to manipulate file names as Unicode
 * {@link it\icosaedro\utils\UString}
 * strings and provides conversion functions between these Unicode strings
 * and the file system compatibility mode provided for non-Unicode aware
 * programs. For example:
 *
 * <blockquote>
 * <pre>
 * $cwd = FileName::decode( getcwd() );
 * ...
 *
 * $fn = UString::fromUTF8("Caffé Brillì.txt");
 * $f = fopen(FileName::encode($fn), "w");
 * ...
 * </pre>
 * </blockquote>
 *
 * The current system locale used for the translations is detected once
 * for all the first time it is requested by any function of this class
 * ("lazy initialization").
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:05:09 $
 */
class FileName {

	private static /*. CodePageInterface .*/ $codepage;


	/**
	 * Returns file system encoding as currently detected.  Determinates
	 * the current encoding from the {@link setlocale("LC_CTYPE",0)}
	 * parameter.
	 *
	 * <p>
	 * <b>Under Windows,</b> an instance of the
	 * {@link it\icosaedro\io\codepage\WindowsCodePage}
	 * class is returned that translates names according to one of the
	 * <code>CPxxx.TXT</code> files contained in the <code>code page</code>
	 * sub-directory.
	 * 
	 * <p>
	 * <b>Under Unix and Linux,</b> an instance of the {@link
	 * it\icosaedro\io\codepage\GenericCodePage} class is returned that
	 * translates names using either the mbstring extension or the iconv
	 * extension.
	 * 
	 * <p>
	 * Example:
	 *
	 * <pre>
	 * 	$locale = FileName::getEncoding();
	 * 	echo "Current locale character encoding is $locale";
	 * </pre>
	 *
	 * @return CodePageInterface Code page translator.
	 * @throws IOException Failed to load the requested code page. Stick
	 * to the "ASCII" translator; subsequent calls to this same function
	 * cannot fail again and always return the generic ASCII translator.
	 */
	static function getEncoding(){
		if( self::$codepage === NULL ){

			/* Extracts codeset part from current LC_CTYPE locale:
			 *
			 *     language[_territory][.codeset][@modifiers]
			 *
			 * where:
			 *
			 * language is the ISO639 code;
			 * territory is the ISO3166 country code;
			 * codeset is the encoding ID, like "ISO-8859-1" or
			 * code page number under Windows;
			 * modifiers are the format modifiers.
			 */
			$ctype = setlocale(LC_CTYPE, 0 );
			if( $ctype === FALSE ){
				# No locale available - keep default.
			} else {
				$dot = strpos($ctype, ".");
				if( $dot === FALSE ){
					# No codeset part - keep default.
				} else {
					$codeset = substr($ctype, $dot + 1);
					# Remove optional modifies after "@":
					$at = strpos($codeset, "@");
					if( $at !== FALSE )
						$codeset = substr($codeset, 0, $at);
					try {
						if ( 'WIN' === substr( PHP_OS, 0, 3 ) )
							self::$codepage = new WindowsCodePage($codeset);
						else // assuming Unix/Linux
							self::$codepage = new GenericCodePage($codeset);
					}
					catch(IOException $e){
						self::$codepage =new GenericCodePage("ASCII");
						throw $e;
					}
				}
			}

			# If codeset detection failed, use default "ASCII":
			if( self::$codepage === NULL )
				self::$codepage = new GenericCodePage("ASCII");
			
		}

		return self::$codepage;
	}


	/**
	 * Encodes the file name according to the current system locale.  Use
	 * this function to translate a generic Unicode file name into something
	 * that may be feed to PHP system functions like <code>fopen()</code>
	 * and <code>getcwd()</code>. Example:
	 *
	 * <pre>
	 * 	$unicode_fn = UString::fromUTF8(__DIR__ . "/Caffé Brillì.txt");
	 * 	$raw_fn = FileName::encode($unicode_fn);
	 * 	$f = fopen($raw_fn, "wb");
	 * 	fwrite($f, "hello");
	 * 	fclose($f);
	 * </pre>
	 *
	 * @param UString $name File name.
	 * @return string encoded file name.
	 * @throws IOException Unknown or unsupported from/to encodings. Failed
	 * conversion: some characters cannot be converted. NUL byte detected.
	 */
	static function encode($name){
		return self::getEncoding()->encode($name);
	}


	/**
	 * Decodes the file name from current system locale. Use this function to
	 * restore the full Unicode name of a file retrieved from a PHP system
	 * function like <code>getcwd()</code> and <code>file_exists()</code>.
	 * Example:
	 *
	 * <pre>
	 * 	# List contents of a directory:
	 * 	$d = dir(__DIR__);
	 * 	while( ($raw_fn = $d-&gt;read()) !== FALSE ){
	 * 		$unicode_fn = FileName::decode($raw_fn);
	 * 		echo $unicode_fn-&gt;toUTF8(), "\n";
	 * 	}
	 * 	$d-&gt;close();
	 * </pre>
	 *
	 * @param string $name Locale encoded file name.
	 * @return UString Decoded file name.
	 * @throws IOException Unknown or unsupported from/to encodings. Failed
	 * conversion: some characters cannot be converted. NUL byte detected.
	 */
	static function decode($name){
		return self::getEncoding()->decode($name);
	}


}
