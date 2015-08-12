<?php

namespace it\icosaedro\io\codepage;
require_once __DIR__ . "/../../../../autoload.php";
use it\icosaedro\io\IOException;
use it\icosaedro\utils\UString;
/*. require_module 'iconv'; .*/
/*. require_module 'mbstring'; .*/

/**
 * Code page translator based on the mbstring or the iconv extensions.
 * For example, under <b>Unix and Linux</b> it is common to set the
 * environment variable LC_CTYPE to <code>"en_US.UTF-8"</code> so that file
 * names are encoded using UTF-8.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2012/03/18 20:56:14 $
 */
class GenericCodePage implements CodePageInterface {

	private /*. string .*/ $encoding;


	/**
	 * Returns encoding name implemented by this object.
	 * @return string Encoding name implemented by this object.
	 */
	function __toString(){
		return $this->encoding;
	}


	/**
	 * Creates new code page translator based on the iconv|mbstring extensions.
	 * @param string $encoding File system encoding, for example "UTF-8".
	 * @return void
	 */
	function __construct($encoding){
		$this->encoding = $encoding;
	}


	/**
	 * Converts a string from one encoding to another.  This implementation
	 * is specifically intended for file name conversions, where the NUL byte
	 * can be used to replace unconvertible characters and then to detect
	 * failed conversions.  This function should not be used for general
	 * use because the NUL byte is a legitimate code in most encodings.
	 * @param string $s String encoded according to $from.
	 * @param string $from Source encoding.
	 * @param string $to Destination encoding.
	 * @return string Result of the conversion of $s to the $to encoding.
	 * @throws IOException Unknown or unsupported from/to encodings. Failed
	 * conversion: some characters cannot be converted.
	 */
	private static function convertEncoding($s, $from, $to){
	
		if( function_exists( 'mb_convert_encoding' ) ) {
			# FIXME: the NUL char cannot be converted as we use it to replace
			# and then to detect unconvertible chars.
			# In any case, NUL is not allowed in ANSI file names anyway.
			$ori = ini_set("mbstring.substitute_character", "0");
			$res = mb_convert_encoding($s, $to, $from);
			ini_set("mbstring.substitute_character", $ori);
			if( strpos($res, "\x00") !== FALSE )
				throw new IOException("encoding conversion from $from to $to failed: non convertible characters found");
			return $res;

		} else if ( function_exists( 'iconv' ) ) {
			try {
				$res = iconv($from, $to, $s);
			}
			catch(\ErrorException $e){
				throw new IOException("encoding conversion from $from to $to failed: " . $e->getMessage());
			}
			if( $res === FALSE )
				throw new IOException("encoding conversion from $from to $to failed");
			return $res;

		} else {
			if( preg_match("/[\x80-\xff]/", $s) === 1 )
				throw new IOException("cannot convert from $from encoding to $to: both the iconv and the mbstring extensions are missing in the system");
			// We assume $s be bare ASCII:
			return $s;
		}
	}


	/**
	 * Encode file name to the current code page table.
	 * @param UString $name Unicode name of the file.
	 * @return string Translated file name.
	 * @throws IOException Translation failed.
	 */
	function encode($name){
		return self::convertEncoding($name->toUTF8(), "UTF-8", $this->encoding);
	}


	/**
	 * Decode file name from current code page table.
	 * @param string $name File name to decode.
	 * @return UString Translated file name.
	 * @throws IOException Translation failed.
	 */
	function decode($name){
		return UString::fromUTF8( self::convertEncoding($name, $this->encoding, "UTF-8") );
	}

}
