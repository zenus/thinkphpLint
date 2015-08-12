<?php

namespace it\icosaedro\io\codepage;
require_once __DIR__ . "/../../../../autoload.php";
require_once __DIR__ . "/../../../../errors.php";
use it\icosaedro\io\IOException;
use it\icosaedro\utils\UString;
use it\icosaedro\utils\UTF8;
use it\icosaedro\io\codepage\CodePageInterface;
/*. require_module 'standard'; .*/

/**
 * Translator for Windows file names encoded according to some "code page"
 * table. Code page tables are read from the <code>CPxxx.TXT</code> files
 * that are present under this same directory.
 *
 * Under <b>Windows</b> non-Unicode aware programs (like the PHP interpreter
 * itself) must use the "code page" table set in the Control Panel -&gt;
 * Regional and Language Options -&gt; Administrative -&gt; Language for
 * non-Unicode programs that allows to select one between several code
 * page tables.
 *
 * Unfortunately, it appear that under Windows UTF-8 is not supported as
 * code page.
 *
 * References:
 *
 * {@link http://en.wikipedia.org/wiki/Windows_code_page
 * Windows Code Page}
 *
 * {@link http://www.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/WINDOWS/
 * Mappings between Win code pages and Unicode}
 *
 * {@link http://www.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/WindowsBestFit/
 * Best fit mappings between Win code pages and Unicode}
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:27:23 $
 */
class WindowsCodePage implements CodePageInterface {

	private /*. string .*/ $cp_code;

	/* Single byte mapping to code point. */
	private /*. int[int] .*/ $single_byte;

	/* Two bytes mapping to code point. First index is the first byte,
	 * second index is the second byte.
	 */
	private /*. int[int][int] .*/ $two_bytes;

	/* Code point mapping to code page. */
	private /*. string[int] .*/ $code_points;


	/**
	 * Returns the code page number implemented by this object.
	 * @return string Code page number implemented by this object.
	 */
	function __toString(){
		return $this->cp_code;
	}


	/**
	 * Creates a new Windows code page translator. For example, if the
	 * "1252" code page is specified, the <code>CP1252.TXT</code> is read.
	 * @param string $cp_code Code page number, for example "1252".
	 * @return void
	 * @throws IOException Failed to load translation table.
	 */
	function __construct($cp_code){
		$this->cp_code = $cp_code;
		$this->single_byte = /*. (int[int]) .*/ array();
		$this->two_bytes = /*. (int[int][int]) .*/ array();
		$this->code_points = /*. (string[int]) .*/ array();
		$f = /*. (resource) .*/ NULL;
		$cp_file_name = __DIR__ . "/CP$cp_code.TXT";
		try {
			$f = fopen($cp_file_name, "rb");
			do {
				$line = fgets($f);
				if( $line === FALSE )
					break;
				if( strlen($line) > 0 and $line[0] === "0" ){
					$a = explode("x", $line);
					if( count($a) >= 3 ){
						$from = intval($a[1], 16);
						$to = intval($a[2], 16);
						#echo "FIXME: decoding line $line\n";
						#printf("   %04x ==> %04x\n", $from, $to);
						if( $from <= 255 ){
							$this->single_byte[$from] = $to;
							$this->code_points[$to] = chr($from);
						} else {
							$lo = $from & 255;
							$hi = $from >> 8;
							$this->two_bytes[$hi][$lo] = $to;
							$this->code_points[$to] = chr($hi) . chr($lo);
						}
					}
				}
			} while(true);
			fclose($f);
		}
		catch(\ErrorException $e){
			if( $f !== NULL )
				try { fclose($f); } catch(\ErrorException $ignore){}
			throw new IOException("reading code page file $cp_file_name: "
				. $e->getMessage());
		}
	}


	/**
	 * Encode file name to the current code page table.
	 * @param UString $name Unicode name of the file.
	 * @return string Translated file name.
	 * @throws IOException Translation failed.
	 */
	function encode($name){
		$r = "";
		$name_len = $name->length();
		for($i = 0; $i < $name_len; $i++){
			$c = $name->codepointAt($i);
			if( $c == 0 )
				throw new IOException("invalid 0 byte in file name");
			if( array_key_exists($c, $this->code_points) )
				$r .= $this->code_points[$c];
			else
				throw new IOException(sprintf("codepoint U+%04x does not exist in current code page %s", $c, $this->cp_code));
		}
		return $r;
	}


	/**
	 * Decode file name from current code page table.
	 * @param string $name File name to decode.
	 * @return UString Translated file name.
	 * @throws IOException Translation failed.
	 */
	function decode($name){

		/*
		 * Windows replaces with <code>"?"</code> characters that cannot be
		 * represented in the current locale; moreover, this character is
		 * not valid in the Windows file system, so if such a character is
		 * found, it means that the translation from the original Unicode
		 * name to the system locale failed and the resulting name is
		 * corrupted and must be rejected with exception.
		 */
		if( strpos($name, "?") !== FALSE )
			throw new IOException("file name contains extended characters that cannot be represented under the current system locale");

		$r = "";
		$name_len = strlen($name);
		for($i = 0; $i < $name_len; $i++){
			$b = ord($name[$i]);
			if( array_key_exists($b, $this->single_byte) ){
				$r .= UTF8::chr($this->single_byte[$b]);
			} else if( array_key_exists($b, $this->two_bytes) ){
				if( $i + 1 < $name_len ){
					$b2 = ord($name[$i+1]);
					if( array_key_exists($b2, $this->two_bytes[$b]) ){
						$r .= UTF8::chr($this->two_bytes[$b][$b2]);
						$i++;
					} else {
						throw new IOException(sprintf("unknown multibyte 0x%02x%02x for current code page %s", $b, $b2, $this->cp_code));
					}
				} else {
					throw new IOException("trunked multibyte sequence");
				}
			} else {
				throw new IOException(sprintf("unexpected byte 0x%02x for current code page %s", $b, $this->cp_code));
			}
		}
		return UString::fromUTF8($r);
	}

}
