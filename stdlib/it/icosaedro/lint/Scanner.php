<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\io\File;
use it\icosaedro\io\InputStream;
use it\icosaedro\io\LineInputWrapper;
use it\icosaedro\io\IOException;
use it\icosaedro\utils\Strings;
use it\icosaedro\bignumbers\BigInt;
use it\icosaedro\regex\Pattern;
use RuntimeException;

/**
 * PHP source scanner. Once opened, the first symbol read is available in the
 * property $sym. The end of the file is returned as Symbol::$sym_eof.
 * 
 * <p>
 * <b>On I/O error</b> an error message is logged in the report and the symbol
 * Symbol::$sym_eof is returned from there on.
 * 
 * <p>
 * The $s property is set only for symbols that have a variable value: literal
 * numbers, literal strings, identifiers. This property is not set when a
 * keyword is returned.
 * 
 * <p>
 * <b>Literal integer numbers</b> are returned as Symbol::$sym_lit_int, and
 * their value is available in the $s property as a number in ten base,
 * whatever their original base of representation might be in the source (octal
 * or hexadecimal). Then, for example, "0xffff" is returned as $s="65535".
 * This class uses {@link it\icosaedro\bignumbers\BigInt} to parse very big
 * numbers and to convert them to the normal ten-base representation, so
 * checking for overflow is in charge of the parser using this class; this
 * class only guarantees the syntax.
 * 
 * <p>
 * <b>Literal float numbers</b> are returned as Symbol::$sym_lit_float; only
 * the syntax is checked, not the range.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/09/14 10:12:06 $
 */
class Scanner {
	
	/**
	 * Destination of the report. "Where" object that marks specific positions
	 * in the source file being parsed, also require a logger object.
	 * @var Logger 
	 */
	private $logger;
	
	/**
	 * @var PhpVersion 
	 */
	private $php_ver;
	
	/** Check for control chars in strings. */
	public static $ctrl_check = FALSE;

	/** Check for ASCII extended chars in strings and identifiers. */
	public static $ascii_ext_check = FALSE;

	/**
	 * Abstract file name of the source file we are scanning. Used only to
	 * report messages, not to open the file (for which a line input stream
	 * has been provided in the constructor).
	 * @var File
	 */
	public $fn;

	/**
	 * Input stream of bytes from the source file.
	 * @var LineInputWrapper
	 */
	private $lis;

	/**
	 * Current symbol.
	 * @var Symbol
	 */
	public $sym;

	/**
	 * Last string, ID, or number read, depending on the current symbol.
	 * @var string
	 */
	public $s;

	/**
	 * Status code indicating which part of the source text is currently
	 * scanned. 
	 */
	/*. private .*/ const
	
		/* 
		 * Text surrounding the PHP code, typically HTML, either at the
		 * beginning of the file or just after the "?>" symbol.
		 */
		inside_text = 0,
			
		/*
		 * Inside PHP code.
		 */
		inside_code = 1,
		
		/*
		 * Inside PHPlint meta-code /&#42;. ... .&#42;/ (cannot reproduce here
		 * the exact spelling of slash and back-slash :-).
		 */
		inside_x_code = 2,

		/*
		 * Found variable embedded in a double quoted string.
		 * $this->c is the first char of the variable name.
		 * Implies inside_code.
		 * 
		 * "xxxx$VAR zzz"
		 *       ^ here, $this->c="V"
		 */
		inside_embedded_variable = 3,

		/*
		 * Just after the embedded variable.
		 * $this->c=next char after the variable name, possibly the closing
		 * double quote. Implies inside_code.
		 * 
		 * "xxxx$VAR zzz"
		 *          ^ here, $this->c=" "
		 */
		inside_double_quoted_string = 4;

	/**
	 * Status code indicating which part of the source text is currently
	 * scanned.
	 */
	private $part = self::inside_text;

	/**
	 * Current line being parsed.
	 */
	private $line = "";

	/**
	 * Current line no. First line is the no. 1.
	 */
	private $line_n = 0;

	/** Offset of the current char in line. */
	private $line_idx = 0;

	/**
	 * Text editor idea of the position of the current char. Basically, it is
	 * the offset of the current read byte in the current line, but tabulators
	 * changes this value jumping to the next tab position.
	 * This value is zero-bases, although editors usually displays the column
	 * number starting from 1 instead. We will take account for this difference
	 * while creating Where objects.
	 */
	private $line_pos = 0;

	/** Current char to be parsed, or NULL if end of the file or I/O error. */
	private $c = "";
	
//	private $c_props = 0;
	
	/*
	 * Subsets for each byte [0,255] to be assigned to the lookup table of the
	 * properties for each char/byte. Each read byte can then be quickly
	 * recognized.
	 */
	/*. private .*/ const
		ASCII_MASK = 1,
		CTRL_MASK = 2,
		DIGIT_MASK = 4,
		SPACE_MASK = 8,
		OCT_DIGIT_MASK = 16,
		HEX_DIGIT_MASK = 32,
		ID_FIRST_CHAR_MASK = 64,
		ID_NEXT_CHAR_MASK = 128;
	
	/**
	 * Lookup table containig exactly 256 entries for each byte, each entry
	 * holding the sub-sets mask to which the byte belong (ASCII, space,
	 * control, etc.).
	 * @var int[int] 
	 */
	private static $bytes_props;
	
	
	/**
	 * Adds a property to a sub-set of bytes in self::$bytes_props.
	 * @param int $from
	 * @param int $to
	 * @param int $prop 
	 * @return void
	 */
	private static function setBytesProp($from, $to, $prop){
		for($i = $from; $i <= $to; $i++){
			self::$bytes_props[$i] |= $prop;
		}
	}
	
	
	/**
	 * Adds a property to a specific byte.
	 * @param int $b
	 * @param int $prop 
	 * @return void
	 */
	private static function setByteProp($b, $prop){
		self::$bytes_props[$b] |= $prop;
	}
	
	
	/**
	 * Initializes the lookup table self::$bytes_props with the properties
	 * of each byte.
	 * @return void 
	 */
	private static function static_init_bytes_props(){
		for($i = 0; $i <= 255; $i++){
			self::$bytes_props[$i] = 0;
		}
		
		self::setBytesProp(0, 127, self::ASCII_MASK);
		
		self::setBytesProp(0, 31, self::CTRL_MASK);
		self::setByteProp(127, self::CTRL_MASK);
		
		self::setByteProp(ord(" "), self::SPACE_MASK);
		self::setByteProp(ord("\t"), self::SPACE_MASK);
		self::setByteProp(ord("\n"), self::SPACE_MASK);
		self::setByteProp(ord("\r"), self::SPACE_MASK);
		self::setBytesProp(ord("0"), ord("9"), self::DIGIT_MASK);
		
		self::setBytesProp(ord("0"), ord("7"), self::OCT_DIGIT_MASK);
		
		self::setBytesProp(ord("0"), ord("9"), self::HEX_DIGIT_MASK);
		self::setBytesProp(ord("a"), ord("f"), self::HEX_DIGIT_MASK);
		self::setBytesProp(ord("A"), ord("F"), self::HEX_DIGIT_MASK);
		
		self::setBytesProp(ord("a"), ord("z"), self::ID_FIRST_CHAR_MASK);
		self::setBytesProp(ord("A"), ord("Z"), self::ID_FIRST_CHAR_MASK);
		self::setBytesProp(ord("_"), ord("_"), self::ID_FIRST_CHAR_MASK);
		self::setBytesProp(128, 255,           self::ID_FIRST_CHAR_MASK);
		
		self::setBytesProp(ord("a"), ord("z"), self::ID_NEXT_CHAR_MASK);
		self::setBytesProp(ord("A"), ord("Z"), self::ID_NEXT_CHAR_MASK);
		self::setBytesProp(ord("_"), ord("_"), self::ID_NEXT_CHAR_MASK);
		self::setBytesProp(128, 255,           self::ID_NEXT_CHAR_MASK);
		self::setBytesProp(ord("0"), ord("9"), self::ID_NEXT_CHAR_MASK);
	}



/**
 * @param string $c
 * @return boolean 
 */
private static function isAscii($c){
	return (self::$bytes_props[ord($c)] & self::ASCII_MASK) != 0;
}

/**
 * @param string $c
 * @return boolean 
 */
private static function isCtrl($c){
	return (self::$bytes_props[ord($c)] & self::CTRL_MASK) != 0;
}

/**
 * @param string $c
 * @return boolean 
 */
private static function isSpace($c){
	return (self::$bytes_props[ord($c)] & self::SPACE_MASK) != 0;
	
//	return ($c === " ") || ($c === "\t")
//		|| ($c === "\n") || ($c === "\r");
}


/**
 * Tells if the given character may start a valid identifier.
 * FIXME: PHP allows also "\x7F" (ASCII DEL).
 * We forbid that char.
 * @param string $c
 * @return boolean TRUE if $c is a valid leading character for an identifier.
 */
private static function isIdFirstChar($c){
	
	return (self::$bytes_props[ord($c)] & self::ID_FIRST_CHAR_MASK) != 0;
	
//	$ch = ord($c);
//	if( $ch >= ord("a") )
//		return ($ch <= ord("z")) || ($ch >= ord("\x80"));
//	else if( $ch >= ord("A") )
//		return ($ch <= ord("Z")) || ($ch == ord("_"));
//	else
//		return FALSE;
		
//	// Simpler but slower:
//	return ($c >= "a") AND (($c <= "z") OR ($c >= "\x80"))
//		OR ($c >= "A") AND (($c <= "Z") OR ($c = "_"));
}


/**
 * Tells if the given character may follow the first one in a valid identifier.
 * FIXME: PHP allows also "\x7F" (ASCII DEL).
 * We forbid that char.
 * @param string $c
 * @return boolean TRUE if $c is a valid leading character for an identifier.
 */
private static function isIdChar($c){
	return (self::$bytes_props[ord($c)] & self::ID_NEXT_CHAR_MASK) != 0;
	
//	$ch = ord($c);
//	if( $ch >= ord("a") )
//		return ($ch <= ord("z")) || ($ch >= ord("\x80"));
//	else if( $ch >= ord("A") )
//		return ($ch <= ord("Z")) || ($ch == ord("_"));
//	else
//		return ($ch >= ord("0")) && ($ch <= ord("9"));
	
	/* ****
	return ($this->c >= "a") AND (($this->c <= "z") OR ($this->c >= "\x80"))
		OR ($this->c >= "A") AND (($this->c <= "Z") OR ($this->c = "_"))
		OR ($this->c >= "0") AND ($this->c <= "9")
	*** */
}


/**
 * @param string $c
 * @return boolean 
 */
private static function isOct($c){
	return (self::$bytes_props[ord($c)] & self::OCT_DIGIT_MASK) != 0;
	
//	$ch = ord($c);
//	return ($ch >= ord("0")) && ($ch <= ord("9"));
}


/**
 * @param string $c
 * @return boolean 
 */
private static function isDigit($c){
	return (self::$bytes_props[ord($c)] & self::DIGIT_MASK) != 0;
	
//	$ch = ord($c);
//	return ($ch >= ord("0")) && ($ch <= ord("9"));
}


/**
 * @param string $c
 * @return boolean 
 */
private static function isHex($c){
	return (self::$bytes_props[ord($c)] & self::HEX_DIGIT_MASK) != 0;
	
//	$ch = ord($c);
//	return ($ch >= ord("0")) && ($ch <= ord("9"))
//		|| ($ch >= ord("A")) && ($ch <= ord("F"))
//		|| ($ch >= ord("a")) && ($ch <= ord("f"));
}


/**
 * Blindly decodes a single hexadecimal digit into its numerical value.
 * Fast (more or less...) but does not perform any check over the passed
 * parameter.
 * @param string $c Hex digit {0-9a-fA-F}. No range check performed here.
 * @return int Value of the digit, in the range [0,15].
 */
private static function hexValue($c){
	$ch = ord($c);
	if( $ch <= ord("9") )
		return $ch - ord("0");
	else if( $ch <= ord("Z") ) // also supports base 36!
		return 10 + $ch - ord("A");
	else
		return 10 + $ch - ord("a");
}


/**
 * Returns the current position of the scanner in the file.
 * @return Where Current position of the scanner in the file.
 */
public function here(){
	return new Where($this->fn, $this->line_n, $this->line_pos+1, $this->line);
}


/**
 * Prints current line of source, possibly with line number.
 * @return void
 */
private function printLineSource()
{
	$s = "";
	if( $this->logger->print_line_numbers )
		$s .= (string) $this->line_n;
	
	// Remove trailing "\r\n" or "\n" (and possibly other space chars):
	$line = rtrim($this->line);
	
	$this->logger->printVerbatim("$s:\t" . $line . "\n");
}


/**
 * Reads next byte from the source file. The read bytes goes in the $c property
 * and the line position and line number are incremented accordingly. At the
 * end of the file, $c is set to NULL. On error, and erro message is displayed
 * and $c is set to NULL.
 * @return void
 */
private function readCh()
{
	# update line_n, line_pos:
	if( $this->c === NULL ){
		// either EOF or got IOException
	} else if( $this->c === "\t" ){
		$this->line_pos = $this->line_pos + $this->logger->tab_size
			- $this->line_pos % $this->logger->tab_size;
	} else if( $this->c === "\n" ){
		$this->line_n++;
		$this->line_pos = 0;
	} else {
		$this->line_pos++;
	}

	# get next char '$this->c' and update line_idx:
	if( $this->line === NULL ){ // either EOF or got IOException
		$this->c = NULL;
	} else if( $this->line_idx < strlen($this->line) ){
		$this->c = $this->line[$this->line_idx];
		$this->line_idx++;
	} else {
		try {
			$this->line = $this->lis->readLine();
		}
		catch(IOException $e){
			$this->logger->error($this->here(), $e->__toString());
			$this->line = NULL; // prevent further readings
			$this->c = NULL; // prevent scanner from keep asking for chars
//			$this->c_props = 0;
			return;
		}
		if( $this->line === NULL ){
			$this->c = NULL;
//		} else if( strlen($this->line) == 0 ){
//			$this->c = "\n";
		} else {
			$this->c = $this->line[0];
		}
		$this->line_idx = 1;
		if( $this->logger->print_source && $this->line !== NULL )
			$this->printLineSource();
//		if( $this->c === NULL )
//			$this->c_props = 0;
//		else
//			$this->c_props = self::$bytes_props[ord($this->c)];
	}
}


/**
 * Tells if the characters starting from the current one (property $c) matches
 * the given string on the current line. Allow for a fast "look forward".
 * @param string $s String to match.
 * @return boolean True if the the current line contains exactly $s starting
 * from the current position.
 */
private function followingCharsMatch($s){
	if( strlen($this->line) - $this->line_idx < strlen($s) )
		return FALSE;
	return substr($this->line, $this->line_idx, strlen($s)) === $s;
}


/**
 * Associative array that maps PHP keywords into Symbol.
 * @var Symbol[string]
 */
private static $php_keywords;

/**
 * Associative array that maps PHPLint meta-code keywords into Symbol.
 * @var Symbol[string]
 */
private static $phplint_keywords;


/**
 * Search a keyword on a specified associative array of keywords.
 * @param string $word Identifier-like word that might be a keyword.
 * @param Symbol[string] $keywords Associative array that maps keywords
 * into Symbol.
 * @return Symbol Returns the symbol corresponding to the keyword, or
 * sym_unknown if not found. If sym_unimplemented_keyword raises a fatal
 * error.
 */
private function searchKeyword($word, $keywords){
	if( array_key_exists($word, $keywords) ){
		$this->sym = $keywords[$word];
		if( $this->sym === Symbol::$sym_unimplemented_keyword )
			throw new ScannerException($this->here(), "unimplemented keyword `" . $word . "'. I'm sorry...");
		return $this->sym;
	} else {
		return Symbol::$sym_unknown;
	}
}


public static function static_init(){
	
	self::static_init_bytes_props();
	
	self::$php_keywords = array(
		"FALSE" => Symbol::$sym_false,
		"INF" => Symbol::$sym_inf,
		"NAN" => Symbol::$sym_nan,
		"NULL" => Symbol::$sym_null,
		"TRUE" => Symbol::$sym_true,
		"abstract" => Symbol::$sym_abstract,
		"and" => Symbol::$sym_and2,
		"array" => Symbol::$sym_array,
		"as" => Symbol::$sym_as,
		"bool" => Symbol::$sym_boolean,
		"boolean" => Symbol::$sym_boolean,
		"break" => Symbol::$sym_break,
                "callable" => Symbol::$sym_unimplemented_keyword,
		"case" => Symbol::$sym_case,
		"catch" => Symbol::$sym_catch,
		"class" => Symbol::$sym_class,
		"clone" => Symbol::$sym_clone,
		"const" => Symbol::$sym_const,
		"continue" => Symbol::$sym_continue,
		"declare" => Symbol::$sym_declare,
		"default" => Symbol::$sym_default,
		"define" => Symbol::$sym_define,
		"die" => Symbol::$sym_exit,
		"do" => Symbol::$sym_do,
		"double" => Symbol::$sym_float,
		"echo" => Symbol::$sym_echo,
		"else" => Symbol::$sym_else,
		"elseif" => Symbol::$sym_elseif,
		"enddeclare" => Symbol::$sym_unimplemented_keyword,
		"endfor" => Symbol::$sym_unimplemented_keyword,
		"endforeach" => Symbol::$sym_unimplemented_keyword,
		"endif" => Symbol::$sym_unimplemented_keyword,
		"endswitch" => Symbol::$sym_unimplemented_keyword,
		"endwhile" => Symbol::$sym_unimplemented_keyword,
		"exit" => Symbol::$sym_exit,
		"extends" => Symbol::$sym_extends,
		"false" => Symbol::$sym_false,
		"final" => Symbol::$sym_final,
		"finally" => Symbol::$sym_finally,
		"float" => Symbol::$sym_float,
		"for" => Symbol::$sym_for,
		"foreach" => Symbol::$sym_foreach,
		"function" => Symbol::$sym_function,
		"global" => Symbol::$sym_global,
		"goto" => Symbol::$sym_goto,
		"if" => Symbol::$sym_if,
		"implements" => Symbol::$sym_implements,
		"include" => Symbol::$sym_include,
		"include_once" => Symbol::$sym_include_once,
		"instanceof" => Symbol::$sym_instanceof,
                "insteadof" => Symbol::$sym_unimplemented_keyword,
		"int" => Symbol::$sym_int,
		"integer" => Symbol::$sym_int,
		"interface" => Symbol::$sym_interface,
		"isset" => Symbol::$sym_isset,
		"list" => Symbol::$sym_list,
		"namespace" => Symbol::$sym_namespace,
		"new" => Symbol::$sym_new,
		"null" => Symbol::$sym_null,
		"object" => Symbol::$sym_object,
		"or" => Symbol::$sym_or2,
		"parent" => Symbol::$sym_parent,
		"print" => Symbol::$sym_print,
		"private" => Symbol::$sym_private,
		"protected" => Symbol::$sym_protected,
		"public" => Symbol::$sym_public,
		"real" => Symbol::$sym_float,
		"require" => Symbol::$sym_require,
		"require_once" => Symbol::$sym_require_once,
		"return" => Symbol::$sym_return,
		"self" => Symbol::$sym_self,
		"static" => Symbol::$sym_static,
		"string" => Symbol::$sym_string,
		"switch" => Symbol::$sym_switch,
		"throw" => Symbol::$sym_throw,
                "trait" => Symbol::$sym_unimplemented_keyword,
		"trigger_error" => Symbol::$sym_trigger_error,
		"true" => Symbol::$sym_true,
		"try" => Symbol::$sym_try,
		"use" => Symbol::$sym_use,
		"var" => Symbol::$sym_var,
		"while" => Symbol::$sym_while,
		"xor" => Symbol::$sym_xor,
                "yield" => Symbol::$sym_unimplemented_keyword
	);

	self::$phplint_keywords = array(
		"abstract" => Symbol::$sym_x_abstract,
		"args" => Symbol::$sym_x_args,
		"array" => Symbol::$sym_x_array,
		"bool" => Symbol::$sym_x_boolean,
		"boolean" => Symbol::$sym_x_boolean,
		"class" => Symbol::$sym_x_class,
		"const" => Symbol::$sym_x_const,
		"double" => Symbol::$sym_x_float,
		"else" => Symbol::$sym_x_else,
		"end_if_php_ver" => Symbol::$sym_x_end_if_php_ver,
		"extends" => Symbol::$sym_x_extends,
		"final" => Symbol::$sym_x_final,
		"float" => Symbol::$sym_x_float,
		"forward" => Symbol::$sym_x_forward,
		"function" => Symbol::$sym_x_function,
		"if_php_ver_4" => Symbol::$sym_x_if_php_ver_4,
		"if_php_ver_5" => Symbol::$sym_x_if_php_ver_5,
		"implements" => Symbol::$sym_x_implements,
		"int" => Symbol::$sym_x_int,
		"integer" => Symbol::$sym_x_int,
		"interface" => Symbol::$sym_x_interface,
		"missing_break" => Symbol::$sym_x_missing_break,
		"missing_default" => Symbol::$sym_x_missing_default,
		"mixed" => Symbol::$sym_x_mixed,
		"namespace" => Symbol::$sym_x_namespace,
		"object" => Symbol::$sym_x_object,
		"parent" => Symbol::$sym_x_parent,
		"pragma" => Symbol::$sym_x_pragma,
		"private" => Symbol::$sym_x_private,
		"protected" => Symbol::$sym_x_protected,
		"public" => Symbol::$sym_x_public,
		"real" => Symbol::$sym_x_float,
		"require_module" => Symbol::$sym_x_require_module,
		"resource" => Symbol::$sym_x_resource,
		"return" => Symbol::$sym_x_return,
		"self" => Symbol::$sym_x_self,
		"static" => Symbol::$sym_x_static,
		"string" => Symbol::$sym_x_string,
		"throws" => Symbol::$sym_x_throws,
		"triggers" => Symbol::$sym_x_triggers,
		"unchecked" => Symbol::$sym_x_unchecked,
		"void" => Symbol::$sym_x_void
	);

}


/**
 * Scans text up to "&lt;?" or "&lt;?php" or "&lt;?=" or EOF. If at least a
 * char of text is found, returns sym_text and s contains the text, otherwise
 * returns sym_open_tag or sym_open_tag_with_echo or sym_eof.
 * @return void
 */
private function parseText(){
	$b = "";
	/* scan chars up to "<?", "<?= or EOF: */
	do {
		if( $this->c === NULL ){
			if( strlen($b) == 0 ){
				$this->sym = Symbol::$sym_eof;
			} else {
				$this->sym = Symbol::$sym_text;
				$this->s = $b;
			}
			return;
		} else if( $this->c === "<" ){
			if( self::followingCharsMatch("?") ){
				if( strlen($b) == 0 ){
					if( self::followingCharsMatch("?=") ){
						# Always allowed since PHP 5.4 -- don't warn:
						#$this->Warning("using deprecated short tag `<?= EXPR, ... ?"
						#	. >' -- Hint: use `<?php echo EXPR, ... ?" . ">' instead");
						$this->sym = Symbol::$sym_open_tag_with_echo;
						$this->part = self::inside_code;
						$this->readCh();
						$this->readCh();
						$this->readCh();
						return;
					} else if( self::followingCharsMatch("?php") ){
						$this->sym = Symbol::$sym_open_tag;
						$this->part = self::inside_code;
						$this->readCh();
						$this->readCh();
						$this->readCh();
						$this->readCh();
						$this->readCh();
						if( ($this->c !== NULL) && ! self::isSpace($this->c) ){
							throw new ScannerException($this->here(), "invalid opening tag, expected `<" . "?php', found " . Strings::toLiteral($this->c));
						}
						return;
					} else {
						$this->logger->warning($this->here(), "using deprecated short tag `<"
							. "?' -- Hint: use `<" . "?php' instead");
						$this->readCh();
						$this->readCh();
						$this->sym = Symbol::$sym_open_tag;
						$this->part = self::inside_code;
						return;
					}
				} else {
					$this->sym = Symbol::$sym_text;
					$this->s = $b;
					return;
				}
			} else {
				$b .= $this->c;
				$this->readCh();
			}
		} else {
			$b .= $this->c;
			$this->readCh();
		}
	} while(TRUE);
}


/**
 * The first new-line '\n' or '\r\n' after "?&gt;" has to be ignored since it
 * is not sent to standard output.
 * All the tests executed on PHP 5.0.4
 * We enter with $this-&gt;c="&gt;".
 * @return void
 */
private function skipNewLineAfterCloseTag()
{
	$this->readCh();
	if( $this->c === "\n" ){
		$this->readCh();
	} else if( $this->c === "\r" ){
		$this->readCh();
		if( $this->c === "\n" ){
			$this->readCh();
		}
	}
}


/**
 * Returns TRUE if closing tag "?&gt;" found.
 * @return boolean
 */
private function skipSingleLineComment(){
	do {
		$prev = $this->c;
		$this->readCh();
		if( $this->c === NULL ){
			return FALSE;
		} else if( $this->c === "\n" ){
			$this->readCh();
			return FALSE;
		} else if( ($this->c === ">") && ($prev === "?") ){
			$this->skipNewLineAfterCloseTag();
			return TRUE;
		}
	} while(true);
}


/**
 * Skip spaces, HT, LF, CR and # comments.
 * @return boolean TRUE if closing tag "?&gt;" found.
 */
private function skipSpaces(){
	while( $this->c !== NULL && self::isSpace($this->c) || ($this->c === "#") ){
		if( $this->c === "#" ){
			if( $this->skipSingleLineComment() ){
				return TRUE;
			}
		} else {
			$this->readCh();
		}
	}
	return FALSE;
}


/**
 * Parses multi-line comment, possibly a DocBlock.
 * Returns the result into s="/&#42; ... &#42;/".
 * @return void
 */
private function skipMultilineComment(){
	$b = "/*";
	$start_line_n = $this->line_n;
	$c1 = ""; // char before $this->c
	$c2 = ""; // char before $c1
	do {
		$b .= $this->c;
		if( $this->c === NULL ){
			throw new ScannerException($this->here(), "missing closing '*/' in comment beginning in line "
				. $start_line_n);
		} else if( ($this->c === "*") && ($c1 === "/") ){
			$this->logger->warning($this->here(), "possible nested multiline comment in comment beginning in line " . $start_line_n);
		} else if( ($this->c === "/") && ($c1 === "*") ){
			if( $c2 === "." ){
				$this->logger->warning($this->here(), "possible missing `.' in multiline comment beginning in line " . $start_line_n);
			}
			$this->readCh();
			$this->s = $b;
			return;
		}
		$c2 = $c1;
		$c1 = $this->c;
		$this->readCh();
	} while(true);
}


/**
 * @param string $c
 * @return string 
 */
private function reportChar($c){
	$ch = ord($c);
	if( ($ch >= 32) && ($ch <= 126) )
		return "`" . chr($ch) . "'";
	else if( $ch == 9 )
		return "horizontal tabulator, HT, 9";
	else if( $ch == 10 )
		return "line feed, LF, 10";
	else if( $ch == 13 )
		return "carriage return, CR, 13";
	else if( $ch == 127 )
		return "delete, DEL, 127";
	else if( $ch > 127 )
		return "code " . $ch;
	else
		return "control code " . $ch;
}


/**
 * Reports a detailed description of a control character. Does nothing if
 * control character reporting is disabled.
 * @param string $c Control character to report.
 * @return void
 */
private function reportCTRL($c){
	if( ! self::$ctrl_check )
		return;
	$this->logger->warning($this->here(), "found control character (" . $this->reportChar($c)
		. ") in literal string. This msg is reported only once for each string");
}


/**
 * Reports a detailed description of a non-ASCII character. Does nothing if
 * non-ASCII characters reporting is disabled.
 * @param string $c Non-ASCII character to report.
 * @param string $in Context where that char was found.
 * @return void
 */
private function reportASCIIExt($c, $in){
	if( ! self::$ascii_ext_check )
		return;
	$this->logger->warning($this->here(), "non-ASCII character code in " . $in . " ("
		. $this->reportChar($c)
		. "). This msg is reported only once for each " . $in);
}


/**
 * LABEL of the here-doc &lt;&lt;&lt;LABEL or now-doc &lt;&lt;&lt;'LABEL'.
 * NULL if not currently parsing a now-doc/here-doc. The status code of the
 * parser tells if we are really parsing a here-doc.
 * @var string
 */
private $here_doc_id;

/* If currently parsing now-doc/here-doc (that is here_doc_id!==NULL)
then these are the possible terminating lines. */
private /*. string .*/ $end1, $end2, $end3, $end4;

/* If currently parsing now-doc/here-doc (that is here_doc_id!==NULL)
holds the regex that matches the typical invalid terminating line
containing invisible spaces: "^[ \t]*LABEL[ \t]*;[ \t]*\r?$" */
private /*. Pattern .*/ $wrong_end;

/** Line at which the literal string begins. It is set for: single
quoted, double quoted, now-doc, here-doc in order to give a meaningful
error message in case of unclosed string, missing terminating label or
premature end of the file. */
private $start_line_n = 0;


/**
 * Parses either single quoted strings (when here_doc_id = NULL)
 * and now-docs (here_doc_id is the ID used after `&lt;&lt;&lt;').
 * @return string
 */
private function parseSingleQuotedString(){
	$b = "";
	$skip = FALSE;
	$report_ctrl = self::$ctrl_check;
	$report_ascii_ext = self::$ascii_ext_check;
	$start_line_n = $this->line_n;
	$this->readCh();
	do {
		if( $this->c === NULL ){
			if( $this->here_doc_id === NULL )
				throw new ScannerException($this->here(),
				"missing terminating ' character in string beginning in line " . $start_line_n);
			else
				throw new ScannerException($this->here(),
				"unclosed now-doc string beginning in line " . $start_line_n);
		}

		/* Detect ctrl chars: */
		if( $report_ctrl && self::isCtrl($this->c)
		&& ( $this->here_doc_id === NULL || ! self::isSpace($this->c) ) ){
			$this->reportCTRL($this->c);
			$report_ctrl = FALSE;
		}

		if( $report_ascii_ext && ! self::isAscii($this->c) ){
			$this->reportASCIIExt($this->c, "literal string");
			$report_ascii_ext = FALSE;
		}

		if( $skip ){
			if( ($this->c === "'") || ($this->c === "\\") ){
				$b .= $this->c;
			} else {
				$b .= "\\";
				$b .= $this->c;
				$this->logger->warning($this->here(),
				"invalid escape sequence. Hint: allowed escape sequences are only \\' \\\\");
			}
			$skip = FALSE;
			$this->readCh();

		/* Detects end of the single-quoted string: */
		} else if( $this->here_doc_id === NULL && $this->c === "'" ){
			$this->readCh();
			return $b;

		/* Detect end of the now-doc: */
		} else if( $this->here_doc_id !== NULL && $this->line_idx == 1
		&& $this->wrong_end->match($this->line) ){

			/* Detect wrong ending line of the now-doc: */
			if( ! (
				$this->line === $this->end1 || $this->line === $this->end2
				|| $this->line === $this->end3 || $this->line === $this->end4
			) ){
				$this->logger->error($this->here(),
				"invisible spaces in terminating line not allowed (PHPLint restriction): "
				. Strings::toLiteral($this->line));
			}

			# skip terminating label:
			do {
				$this->readCh();
			} while( ! ($this->c === NULL || $this->c === ";" || $this->c === "\n") );

			$this->here_doc_id = NULL;

			return $b;

		} else if( ($this->here_doc_id === NULL) && ($this->c === "\\") ){
			$skip = TRUE;
			$this->readCh();
		} else {
			$b .= $this->c;
			$this->readCh();
		}
	} while(true);
}


/**
 * Parses an escaped character in double-quoted string and here-doc.
 * @return string Decoded, literal character.
 */
private function parseEscapeCode(){
	if( $this->c === "n"  ){
		$this->readCh();
		return "\n";
	} else if( $this->c === "r" ){
		$this->readCh();
		return "\r";
	} else if( $this->c === "t" ){
		$this->readCh();
		return "\t";
	} else if( $this->c === "v" ){
		$this->readCh();
		return "\x0b";
	} else if( $this->c === "f" ){
		$this->readCh();
		return "\x0c";
	} else if( $this->c === "\\" ){
		$this->readCh();
		return "\\";
	} else if( $this->c === "$" ){
		$this->readCh();
		return "$";
	} else if( $this->c === "\"" ){
		$this->readCh();
		return "\"";
	} else if( self::isOct($this->c) ){
		$x = ord($this->c) - ord("0");
		$this->readCh();
		if( self::isOct($this->c) ){
			$x = 8*$x + ord($this->c) - ord("0");
			$this->readCh();
			if( self::isOct($this->c) ){
				$x = 8*$x + ord($this->c) - ord("0");
				$this->readCh();
			}
		}
		if( $x > 255 ){
			$this->logger->error($this->here(), "invalid octal code in escape sequence: too big");
			return NULL;
		}
		return chr($x);
	} else if( ($this->c === "x") || ($this->c === "X") ){
		$this->readCh();
		if( ! self::isHex($this->c) ){
			$this->logger->error($this->here(), "invalid hexadecimal digit in escape sequence");
			return NULL;
		}
		$x = self::hexValue($this->c);
		$this->readCh();
		if( self::isHex($this->c) ){
			$x = 16*$x + self::hexValue($this->c);
			$this->readCh();
		}
		return chr($x);
	} else {
		$this->logger->warning($this->here(), "invalid escape sequence. Hint: allowed escape sequences are only \\n \\r \\t \\v \\f \\$ \\\" \\\\ \\0-\\377 (octal) \\x0-\\xff (hexadecimal)");
		return "\\" . $this->c;
	}
}


/**
  Parses either double quoted strings (when here_doc_id = NULL)
  and here-docs (here_doc_id is the ID used after `&lt;&lt;&lt;').
  The differences between double q. strings and here-docs are:

  - strings cannot contain \n \r
  - strings must be terminated by " and " must be \"
  - here-docs can contain \n \r
  - here-docs are terminated when the line begin with
  here_doc_id possibly followed by ; end then \n \r

  Apart from these diff., the two are handled the same way.
 * @return boolean 
 */
private function parseDoubleQuotedString(){
	$b = "";
	$report_ctrl = self::$ctrl_check;
	$report_ascii_ext = self::$ascii_ext_check;
	do {
		if( $this->c === NULL ){
			if( $this->here_doc_id === NULL ){
				throw new ScannerException($this->here(),
				"missing terminating \" character in literal string");
			} else {
				throw new ScannerException($this->here(),
				"here-doc `<<< " . $this->here_doc_id . "' not closed"
				. " beginning in line " . $this->start_line_n
				. ". Expected a line containing exactly `" . $this->here_doc_id
				. "' possibly followed by `;' then a new-line, no spaces allowed.");
			}
		}

		/* Detect ctrl chars: */
		if( $report_ctrl && self::isCtrl($this->c)
		&& ( $this->here_doc_id === NULL || ! self::isSpace($this->c) ) ){
			$this->reportCTRL($this->c);
			$report_ctrl = FALSE;
		}

		/* Detect non-ASCII chars: */
		if( $report_ascii_ext && ! self::isAscii($this->c) ){
			$this->reportASCIIExt($this->c, "literal string");
			$report_ascii_ext = FALSE;
		}

		/*
			Detect end of the double quoted string:
		*/
		if( $this->here_doc_id === NULL && $this->c === "\"" ){

			# skip terminating ":
			$this->readCh();

			$this->s = $b;
			if( $this->part === self::inside_double_quoted_string ){
				$this->part = self::inside_code;
				if( strlen($this->s) == 0 ){
					# String terminated just after prev. embedded var.:
					# ".....$VAR"
					# Do not return empty string and skip to next sym.
					return TRUE;
				} else {
					# Some chars found after prev. embedded var.
					$this->sym = Symbol::$sym_continuing_double_quoted_string;
					return FALSE;
				}
			} else {
				$this->part = self::inside_code;
				$this->sym = Symbol::$sym_double_quoted_string;
				return FALSE;
			}

		/*
			Detect end of the here-doc:
		*/
		} else if( $this->here_doc_id !== NULL && $this->line_idx == 1
		&& $this->wrong_end->match($this->line) ){

			/* Detect wrong ending line of the here-doc: */
			if( ! (
				$this->line === $this->end1 || $this->line === $this->end2
				|| $this->line === $this->end3 || $this->line === $this->end4
			) ){
				$this->logger->error($this->here(),
				"invisible spaces in terminating line not allowed (PHPLint restriction): "
				. Strings::toLiteral($this->line));
			}

			# skip terminating label:
			do {
				$this->readCh();
			} while( ! ( $this->c === NULL || $this->c === ";" || $this->c === "\n" ) );

			$this->here_doc_id = NULL;

			$this->s = $b;
			if( $this->part == self::inside_double_quoted_string ){
				$this->part = self::inside_code;
				if( strlen($this->s) == 0 ){
					# String terminated just after prev. embedded var.:
					# ".....$VAR"
					# Do not return empty string and skip to next sym.
					return TRUE;
				} else {
					# Some chars found after prev. embedded var.
					$this->sym = Symbol::$sym_continuing_double_quoted_string;
					return FALSE;
				}
			} else {
				$this->part = self::inside_code;
				$this->sym = Symbol::$sym_here_doc;
				return FALSE;
			}

		} else if( $this->c === "\\" ){
			$this->readCh();
			$b .= $this->parseEscapeCode();
		} else if( $this->c === "{" ){
			$b .= $this->c;
			$this->readCh();
			if( $this->c === "\$" ){
				$this->logger->error($this->here(), "embedded variable in string:"
				. " curly braces notation not allowed"
				. " (PHPLint limitation)");
				$b .= $this->c;
				$this->readCh();
			}
		} else if( $this->c === "\$" ){
			$this->readCh();
			if( self::isIdFirstChar($this->c) ){
				# WARNING: code optimization: the parser can't evaluate
				# the resulting string when embedded variables are
				# present; so it is completely useless to set `s':
				#$s = $b
				# We set a dummy value instead:
				$this->s = "DUMMY_CONTINUATION_DOUBLE_QUOTED_STRING";
				if( $this->part === self::inside_code ){
					/*
						Found embedded var. just at the beginning of the
						string.	 Always returns sym_double_quoted_string
						or sym_here_doc, although s may be empty.
					*/
					$this->part = self::inside_embedded_variable;
					if( $this->here_doc_id === NULL ){
						$this->sym = Symbol::$sym_double_quoted_string;
					} else {
						$this->sym = Symbol::$sym_here_doc;
					}
					return FALSE;
				} else { /* code = inside_embedded_string */
					/*
						We encountered a sequence of embedded variables
						"$VAR1$VAR2$VAR3".	Do not return empty strings
						between them.
					*/
					$this->part = self::inside_embedded_variable;
					$this->sym = Symbol::$sym_continuing_double_quoted_string;
					return strlen($this->s) == 0;
				}
			} else {
				if( $this->c === "[" ){
					# FIXME: "xxx$[]" unsupported
					$this->logger->error($this->here(),
					"embedded variable in string: array selector not allowed"
					. " (PHPLint limitation)");
				} else if( $this->c === "{" ){
					# FIXME: "xxx${}" unsupported
					$this->logger->error($this->here(),
					"embedded variable in string: curly braces notation not allowed"
					. " (PHPLint limitation)");
				}
				$b .= "\$";
			}
		} else {
			$b .= $this->c;
			$this->readCh();
		}
	} while(true);
}


/**
 * @return boolean 
 */
private function parseHereAndNowDoc(){
	
	# FIXME: the last LF should not be added to the string.
	# FIXME: cannot be used to initialize class properties in PHP < 5.3.

	$this->start_line_n = $this->line_n;

	while( $this->c === " " || $this->c === "\t" ){
		$this->readCh();
	}

	$single_quoted_label = FALSE;
	$double_quoted_label = FALSE;
	if( $this->c === "'" ){
		$single_quoted_label = TRUE;
		$this->readCh();
	} else if( $this->c === "\"" ){
		$double_quoted_label = TRUE;
		$this->readCh();
	}

	if( $this->php_ver === PhpVersion::$php4
	&& ($single_quoted_label || $double_quoted_label) )
		$this->logger->error($this->here(),
		"quoted label in here-doc allowed only since PHP 5.3.0");

	# Get the ID into id:
	if( ! self::isIdFirstChar($this->c) )
		throw new ScannerException($this->here(),
		"expected identifier after `<<<'");
	
	$b = $this->c;
	$this->readCh();
	while( self::isIdChar($this->c) ){
		$b .= $this->c;
		$this->readCh();
	}
	$id = $b;

	if( $single_quoted_label ){
		if( $this->c !== "'" )
			throw new ScannerException($this->here(),
			"expected closing single quote in here-doc label");
		$this->readCh();
	} else if( $double_quoted_label ){
		if( $this->c !== "\"" )
			throw new ScannerException($this->here(),
			"expected closing double quote in here-doc label");
		$this->readCh();
	}

	if( $this->c === " " || $this->c === "\t" )
		$this->logger->error($this->here(),
		"spaces not allowed after `<<<" . $id . "'");
	
	while( $this->c === " " || $this->c === "\t" || $this->c === "\r" )
		$this->readCh();

	if( $this->c !== "\n" )
		throw new ScannerException($this->here(),
		"expected end of the line (ASCII code LF) after here-doc ID");
	
	$this->readCh();

	$this->here_doc_id = $id;
	$this->end1 = $id . "\n";
	$this->end2 = $id . "\r\n";
	$this->end3 = $id . ";\n";
	$this->end4 = $id . ";\r\n";
	$this->wrong_end = new Pattern("{ \t}*" . Pattern::escape($id)
		. "{ \t}*;?{ \t}*\r?\n?\$");

	if( $single_quoted_label ){
		$this->s = $this->parseSingleQuotedString();
		$this->sym = Symbol::$sym_single_quoted_string;
		return FALSE;
	} else {
		return $this->parseDoubleQuotedString();
	}
}


/**
 * Parse an ID and set s and $this-&gt;sym global vars accordingly. If
 * $this-&gt;sym isn't a keyword, set $this-&gt;sym=sym_unknown.
 * Report as an error IDs that look like a keyword, apart lowercase
 * or uppercase letters. Ex. ForEach, $var, $Var.
 * @return void
 */
private function parseKeyword(){
	$b = "";
	$report_ascii_ext = self::$ascii_ext_check;
	do {
//		if( $report_ascii_ext && (ord($this->c) >= 127) ){
		if( $report_ascii_ext && ! self::isAscii($this->c) ){
			$this->reportASCIIExt($this->c, "identifier");
			$report_ascii_ext = FALSE;
		}
		$b .= $this->c;
		$this->readCh();
		if( ! self::isIdChar($this->c) ){
		//if( ($this->c_props & self::ID_NEXT_CHAR_MASK) == 0 ){
			$this->s = $b;
			break;
		}
	} while(TRUE);

	/* Since PHP and PHPLint share some keywords, it is important
	   the order of search between php_keywords and phplint_keywords: */
	if( $this->part === self::inside_x_code ){	
		$this->sym = self::searchKeyword($this->s, self::$phplint_keywords);
		if( $this->sym !== Symbol::$sym_unknown )
			return;
		$this->sym = self::searchKeyword($this->s, self::$php_keywords);
		if( $this->sym !== Symbol::$sym_unknown ){
			$this->logger->error($this->here(), "invalid keyword `" . $this->s . "' inside PHPLint meta-code");
			$this->sym = Symbol::$sym_unknown;
			return;
		}
	} else {
		$this->sym = self::searchKeyword($this->s, self::$php_keywords);
		if( $this->sym !== Symbol::$sym_unknown )
			return;
		$this->sym = self::searchKeyword($this->s, self::$phplint_keywords);
		if( $this->sym !== Symbol::$sym_unknown ){
			$this->logger->error($this->here(), "invalid PHPLint keyword `" . $this->s . "' inside PHP code");
			$this->sym = Symbol::$sym_unknown;
			return;
		}
	}

	# Not a keyword. Check misspelled PHP keyword:
	$low = strtolower($this->s);
	$this->sym = $this->searchKeyword($low, self::$php_keywords);
	if( $this->sym !== Symbol::$sym_unknown ){
		$this->logger->error($this->here(), "`" . $this->s . "': invalid identifier similar to the keyword `"
		. $low . "'");
		#$this->sym = Symbol::$sym_unknown;
		return;
	}

	# Check misspelled PHPLint keyword:
	$this->sym = self::searchKeyword($low, self::$phplint_keywords);
	if( $this->sym !== Symbol::$sym_unknown ){
		$this->logger->error($this->here(), "`" . $this->s . "': invalid identifier similar to the PHPLint keyword `" . $low . "'");
		$this->sym = Symbol::$sym_unknown;
		return;
	}

}


/**
 * Parse a qualified identifier, either in PHP code or in meta-code.
 * Precondition: $this-&gt;c="\\".
 * Return the full identifier.
 * @return string 
 */
private function parseQualifiedIdentifier(){
	$this->readCh();
	
	if( $this->skipSpaces() )
		throw new ScannerException($this->here(), "expected identifier after `\\'");
	
	if( ! self::isIdFirstChar($this->c) )
		throw new ScannerException($this->here(), "expected identifier after `\\'");
	
	$b = "";
	do {
		$this->parseKeyword();
		if( $this->sym === Symbol::$sym_unknown ){
			$b .= "\\";
			$b .= $this->s;
		} else {
			throw new ScannerException($this->here(), "keyword used in qualified identifier");
		}
		if( $this->skipSpaces() )
			break;
		if( $this->c === "\\" ){
			$this->readCh();
		} else {
			break;
		}
	} while(TRUE);
	return $b;
}


/**
 * Parses a float.
 * We enter this func. only after the float has been recognized, that is when
 * $this-&gt;c="."|"e"|"E"; the initial part of the number (the integral part)
 * is available in $b.
 * Final result of the parsing in $this-&gt;s, as usual.
 * @param string $b Integral part already parsed.
 * @return void
 */
private function parseFloat($b)
{
	// Decimal part:
	if( $this->c === "." ){
		$b .= $this->c;
		$this->readCh();
		if( ! self::isDigit($this->c) )
			throw new ScannerException($this->here(),
			"literal float number: required digit after decimal point");
		do {
			$b .= $this->c;
			$this->readCh();
		} while( self::isDigit($this->c) );
	}
	
	// Ten exponent part:
	if( $this->c === "e" || $this->c === "E" ){
		$b .= $this->c;
		$this->readCh();
		if( $this->c === "+" || $this->c === "-" ){
			$b .= $this->c;
			$this->readCh();
		}
		if( ! self::isDigit($this->c) )
			$this->logger->error($this->here(),
			"literal float number: required digit in exponent");
		do {
			$b .= $this->c;
			$this->readCh();
		} while( self::isDigit($this->c) );
	}
	
	$this->s = $b;
}


/**
 * Parses a number, that is anything starting with a digit.
 * @return Symbol 
 */
private function parseNumber(){
	/*
	 * FIXME: check ranges of int and float;
	 * big integers might be translated into float by PHP at runtime, so the
	 * resulting type becomes invalid! (this may happen anyway, as PHP
	 * automatically promotes int to float on int overflow...)
	 * 
	 */
	if( $this->c === "0" ){
		$this->readCh();
		
		if( $this->c === "x" ){
			# Hexadecimal number:
			$this->readCh();
			if( ! self::isHex($this->c) ){
				$this->logger->error($this->here(), "invalid hexadecimal number");
				$this->s = "0";
			} else {
				$n = new BigInt(0);
				$_16 = new BigInt(16);
				do {
					$digit = new BigInt( self::hexValue($this->c) );
					$n = $n->mul($_16)->add($digit);
					$this->readCh();
				} while( self::isHex($this->c) );
				$this->s = $n->__toString();
			}
			return Symbol::$sym_lit_int;
			
		} else if( self::isOct($this->c) ){
			# Octal number:
			$n = new BigInt(0);
			$_8 = new BigInt(8);
			do {
				$digit = new BigInt( ord($this->c) - ord("0") );
				$n = $n->mul($_8)->add($digit);
				$this->readCh();
			} while( self::isOct($this->c) );
			if( self::isDigit($this->c) ){
				$this->logger->error($this->here(), "invalid digit `" . $this->c . "' in octal number");
				$this->readCh();
			}
			$this->s = $n->__toString();
			return Symbol::$sym_lit_int;
			
		} else if( self::isDigit($this->c) ){
			$this->logger->error($this->here(), "invalid digit `" . $this->c . "' in octal number");
			$this->readCh();
			$this->s = "0";
			return Symbol::$sym_lit_int;
			
		} else if( $this->c === "." ){
			# Float 0.xxxx:
			$this->parseFloat("0");
			return Symbol::$sym_lit_float;
			
		} else {
			# Simply a zero.
			$this->s = "0";
			return Symbol::$sym_lit_int;
		}
	} else {
		$b = "";
		do {
			$b .= $this->c;
			$this->readCh();
		} while( self::isDigit($this->c) );
		
		if( ($this->c === ".") || ($this->c === "e") || ($this->c === "E") ){
			# Float number:
			$this->parseFloat($b);
			return Symbol::$sym_lit_float;
			
		} else {
			# Ten-base int number:
			$this->s = $b;
			return Symbol::$sym_lit_int;
		}
	}
}


/**
 * Parses the PHPLint specific documentation block, that is a meta-code block
 * starting with the (unregistered) keyword "DOC".
 * @return void
 */
private function parseDoc(){
	$line_start = $this->line_n;
	while( ($this->c === " ") || ($this->c === "\t") ){
		$this->readCh();
	}
	$b = "";
	do {
		if( $this->c === NULL ){
			$this->logger->error($this->here(), "unclosed DOC comment openend in line "
				. $line_start);
			break;
		} else if( $this->c === "." ){
			$this->readCh();
			if( $this->c === "*" ){
				$this->readCh();
				if( $this->c === "/" ){
					$this->readCh();
					break;
				} else {
					$b .= ".*";
				}
			} else {
				$b .= ".";
			}
		} else if( $this->c === "*" ){
			$this->readCh();
			if( $this->c === "/" ){
				$this->logger->error($this->here(), "missing `.' in closing `.*/'");
				$this->readCh();
				break;
			} else {
				$b .= "*";
			}
		} else {
			$b .= $this->c;
			$this->readCh();
		}
	} while(TRUE);
	$this->s = $b;
}


private function parseVarName(){
	$this->readCh();
	if( $this->c === "\$" ){
		$this->logger->error($this->here(), "unsupported variable-variable feature \$\$var -- trying to continue anyway");
		do {
			$this->readCh();
		} while( $this->c === "\$" );
	}
	if( ! self::isIdFirstChar($this->c) )
		throw new ScannerException($this->here(), "missing variable name after `$'");
	$this->parseKeyword();
	if( $this->sym !== Symbol::$sym_unknown )
		$this->logger->error($this->here(), "the name `$" . $this->s . "' is a keyword."
		. " This is deprecated by PHP and forbidden by PHPLint.");
}


/**
 * Parse symbols of the PHPLint extended syntax.
 * On exit from the x-code (".* /" found), or unexpected termination of
 * the comment ("* /" found), or invalid char, returns TRUE so allowing
 * the scanner to skip right to the next symbol.
 * @return boolean
 */
private function parseXCode(){
	if( $this->skipSpaces() )
		throw new ScannerException($this->here(), "unexpected closing tag ?> inside meta-code");
	
	if( $this->c === NULL ){
		$this->sym = Symbol::$sym_eof;
		return FALSE;
	}
	
	$case_found = TRUE;
	// FIXME: switch(): what if $this->c is a digit?
	switch($this->c){
	case "(":
		$this->sym = Symbol::$sym_x_lround;
		$this->readCh();
		break;
	case  ")":
		$this->sym = Symbol::$sym_x_rround;
		$this->readCh();
		break;
	case  "[":
		$this->sym = Symbol::$sym_x_lsquare;
		$this->readCh();
		break;
	case  "]":
		$this->sym = Symbol::$sym_x_rsquare;
		$this->readCh();
		break;
	case  "{":
		$this->sym = Symbol::$sym_x_lbrace;
		$this->readCh();
		break;
	case  "}":
		$this->sym = Symbol::$sym_x_rbrace;
		$this->readCh();
		break;
	case  "&":
		$this->sym = Symbol::$sym_x_bit_and;
		$this->readCh();
		break;
	case  "=":
		$this->sym = Symbol::$sym_x_assign;
		$this->readCh();
		break;
	case  "\$":
		$this->parseVarName();
		$this->sym = Symbol::$sym_x_variable;
		break;
	case  "\\":
		$this->s = $this->parseQualifiedIdentifier();
		$this->sym = Symbol::$sym_x_identifier;
		break;
	case  ".":
		$this->readCh();
		if( ($this->c === NULL) || ($this->c !== "*") ){
			$this->logger->error($this->here(), "invalid syntax in extended code");
			return TRUE;
		}
		$this->readCh();
		if( ($this->c === NULL) || ($this->c !== "/") ){
			$this->logger->error($this->here(), "invalid syntax in extended code");
			return TRUE;
		}
		$this->readCh();
		$this->part = self::inside_code;
		return TRUE;
	case  ",":
		$this->readCh();
		$this->sym = Symbol::$sym_x_comma;
		break;
	case  "'":
		$this->s = $this->parseSingleQuotedString();
		$this->sym = Symbol::$sym_x_single_quoted_string;
		break;
	case  ";":
		$this->readCh();
		$this->sym = Symbol::$sym_x_semicolon;
		break;
	case  ":":
		$this->readCh();
		$this->sym = Symbol::$sym_x_colon;
		break;
	case  "*":
		$this->readCh();
		if( $this->c === "/" ){
			$this->logger->error($this->here(), "expected `.*/', found `*/' (missing `.')");
			$this->readCh();
			$this->part = self::inside_code;
			return TRUE;
		} else {
			throw new ScannerException($this->here(), "unexpected char `*' in extended code");
		}
	case  "<":
		$this->readCh();
		$this->sym = Symbol::$sym_x_lt;
		break;
	case  ">":
		$this->readCh();
		$this->sym = Symbol::$sym_x_gt;
		break;
	default:
		$case_found = FALSE;
	}
	
	if( $case_found )
		return FALSE;
	
	if( self::isIdFirstChar($this->c) ){
		$this->parseKeyword();
		if( $this->sym === Symbol::$sym_unknown ){
			if( $this->s === "DOC" ){
				$this->parseDoc();
				$this->part = self::inside_code;
				$this->sym = Symbol::$sym_x_doc;
			} else {
				if( $this->skipSpaces() )
					throw new ScannerException($this->here(), "unexpected closing tag ?> inside meta-code");
				if( $this->c === "\\" ){
					$q = $this->s;
					$this->s = $q . $this->parseQualifiedIdentifier();
				}
				$this->sym = Symbol::$sym_x_identifier;
			}
		}
		return FALSE;
		
	} else {
		$this->logger->error($this->here(), "unexpected char " . $this->reportChar($this->c)
			. " in PHPLint meta-code - ignore");
		$this->readCh();
		return FALSE;
	}
}


/**
 * Scan next PHP statement/expression symbol inside PHP code.
 * Returns TRUE is the caller must continue with the next symbol.
 * @return boolean
 */
private function parseCode(){
	if( $this->skipSpaces() ){
		# Found closing tag "? >".
		$this->part = self::inside_text;
		$this->sym = Symbol::$sym_close_tag;
		return FALSE;
	}
	
	if( $this->c === NULL ){
		$this->sym = Symbol::$sym_eof;
		return FALSE;
	}
	
	$case_found = TRUE;
	// FIXME: switch(): what if $this->c is a digit?
	switch($this->c){
	
	case "\\":
		$this->s = $this->parseQualifiedIdentifier();
		$this->sym = Symbol::$sym_identifier;
		break;

	case "$":
		$this->parseVarName();
		$this->sym = Symbol::$sym_variable;
		break;

	case "\"":
		$this->readCh();
		return $this->parseDoubleQuotedString();
		
	case "'":
		$this->s = $this->parseSingleQuotedString();
		$this->sym = Symbol::$sym_single_quoted_string;
		break;
		
	case "`":
		throw new ScannerException($this->here(), "unimplemented execution operator \"`\". Use shell_exec() instead.");
	case "@":
		$this->sym = Symbol::$sym_at;
		$this->readCh();
		break;
	case "{":
		$this->sym = Symbol::$sym_lbrace;
		$this->readCh();
		break;
	case"}":
		$this->sym = Symbol::$sym_rbrace;
		$this->readCh();
		break;
	case "[":
		$this->sym = Symbol::$sym_lsquare;
		$this->readCh();
		break;
	case "]":
		$this->sym = Symbol::$sym_rsquare;
		$this->readCh();
		break;
	case "(":
		$this->sym = Symbol::$sym_lround;
		$this->readCh();
		break;
	case ")":
		$this->sym = Symbol::$sym_rround;
		$this->readCh();
		break;
	case ",":
		$this->sym = Symbol::$sym_comma;
		$this->readCh();
		break;
	case ";":
		$this->sym = Symbol::$sym_semicolon;
		$this->readCh();
		break;
	case "~":
		$this->sym = Symbol::$sym_bit_not;
		$this->readCh();
		break;
	case ":":
		$this->readCh();
		if( $this->c === ":" ){
			$this->readCh();
			$this->sym = Symbol::$sym_double_colon;
		} else {
			$this->sym = Symbol::$sym_colon;
		}
		break;
		
	case "?":
		$this->readCh();
		if( $this->c === ">" ){
			$this->skipNewLineAfterCloseTag();
			$this->part = self::inside_text;
			$this->sym = Symbol::$sym_close_tag;
		} else {
			$this->sym = Symbol::$sym_question;
		}
		break;
		
	case "+":
		$this->readCh();
		if( $this->c === "+" ){
			$this->readCh();
			$this->sym = Symbol::$sym_incr;
		} else if( $this->c === "=" ){
			$this->readCh();
			$this->sym = Symbol::$sym_plus_assign;
		} else {
			$this->sym = Symbol::$sym_plus;
		}
		break;
		
	case "-":
		$this->readCh();
		if( $this->c === ">" ){
			$this->readCh();
			$this->sym = Symbol::$sym_arrow;
		} else if( $this->c === "-" ){
			$this->readCh();
			$this->sym = Symbol::$sym_decr;
		} else if( $this->c === "=" ){
			$this->readCh();
			$this->sym = Symbol::$sym_minus_assign;
		} else {
			$this->sym = Symbol::$sym_minus;
		}
		break;
		
	case "*":
		$this->readCh();
		if( $this->c === "=" ){
			$this->readCh();
			$this->sym = Symbol::$sym_times_assign;
		} else {
			$this->sym = Symbol::$sym_times;
		}
		break;
		
	case "/":
		$this->readCh();
		if( $this->c === "*" ){
			$this->readCh();
			if( $this->c === "." ){
				# Meta-code block:
				$this->part = self::inside_x_code;
				$this->readCh();
				return TRUE;
			} else {
				$this->skipMultilineComment();
				if( (strlen($this->s) > 5) && (substr($this->s, 0, 3) === "/**") ){
					# phpDocumentor docBlock
					$this->sym = Symbol::$sym_x_docBlock;
				} else {
					# Regular multiline comment
					return TRUE;
				}
			}
		} else if( $this->c === "/" ){
			if( $this->skipSingleLineComment() ){
				# Found closing tag "? >".
				$this->part = self::inside_text;
				$this->sym = Symbol::$sym_close_tag;
				return FALSE;
			}
			return TRUE;
		} else if( $this->c === "=" ){
			$this->sym = Symbol::$sym_div_assign;
			$this->readCh();
		} else {
			$this->sym = Symbol::$sym_div;
		}
		break;
		
	case "%":
		$this->readCh();
		if( $this->c === "=" ){
			$this->sym = Symbol::$sym_mod_assign;
			$this->readCh();
		} else {
			$this->sym = Symbol::$sym_mod;
		}
		break;
		
	case "=":
		$this->readCh();
		if( $this->c === "=" ){
			$this->readCh();
			if( $this->c === "=" ){
				$this->readCh();
				$this->sym = Symbol::$sym_eeq;
			} else {
				$this->sym = Symbol::$sym_eq;
			}
		} else if( $this->c === ">" ){
			$this->readCh();
			$this->sym = Symbol::$sym_rarrow;
		} else {
			$this->sym = Symbol::$sym_assign;
		}
		break;
		
	case "<":
		$this->readCh();
		if( $this->c === "=" ){
			$this->sym = Symbol::$sym_le;
			$this->readCh();
		} else if( $this->c === ">" ){
			$this->sym = Symbol::$sym_ne;
			$this->readCh();
		} else if( $this->c === "<" ){
			$this->readCh();
			if( $this->c === "=" ){
				$this->readCh();
				$this->sym = Symbol::$sym_lshift_assign;
			} else if( $this->c === "<" ){
				$this->readCh();
				return $this->parseHereAndNowDoc();
			} else {
				$this->sym = Symbol::$sym_lshift;
			}
		} else {
			$this->sym = Symbol::$sym_lt;
		}
		break;
		
	case ">":
		$this->readCh();
		if( $this->c === "=" ){
			$this->sym = Symbol::$sym_ge;
			$this->readCh();
		} else if( $this->c === ">" ){
			$this->readCh();
			if( $this->c === "=" ){
				$this->readCh();
				$this->sym = Symbol::$sym_rshift_assign;
			} else {
				$this->sym = Symbol::$sym_rshift;
			}
		} else {
			$this->sym = Symbol::$sym_gt;
		}
		break;
		
	case "!":
		$this->readCh();
		if( $this->c === "=" ){
			$this->readCh();
			if( $this->c === "=" ){
				$this->readCh();
				$this->sym = Symbol::$sym_nee;
			} else {
				$this->sym = Symbol::$sym_ne;
			}
		} else {
			$this->sym = Symbol::$sym_not;
		}
		break;
		
	case "|":
		$this->readCh();
		if( $this->c === "|" ){
			$this->readCh();
			$this->sym = Symbol::$sym_or;
		} else if( $this->c === "=" ){
			$this->sym = Symbol::$sym_bit_or_assign;
			$this->readCh();
		} else {
			$this->sym = Symbol::$sym_bit_or;
		}
		break;
		
	case "&":
		$this->readCh();
		if( $this->c === "&" ){
			$this->readCh();
			$this->sym = Symbol::$sym_and;
		} else if( $this->c === "=" ){
			$this->sym = Symbol::$sym_bit_and_assign;
			$this->readCh();
		} else {
			$this->sym = Symbol::$sym_bit_and;
		}
		break;
		
	case ".":
		$this->readCh();
		if( $this->c === "=" ){
			$this->readCh();
			$this->sym = Symbol::$sym_period_assign;
		} else {
			$this->sym = Symbol::$sym_period;
		}
		break;
		
	case "^":
		$this->readCh();
		if( $this->c === "=" ){
			$this->sym = Symbol::$sym_bit_xor_assign;
			$this->readCh();
		} else {
			$this->sym = Symbol::$sym_bit_xor;
		}
		break;
	
	default:
		$case_found = FALSE;
	}
	
	if( $case_found )
		return FALSE;
	
	if( self::isIdFirstChar($this->c) ){
		$this->parseKeyword();
		if( $this->sym === Symbol::$sym_unknown ){
			if( $this->skipSpaces() ){
				$this->sym = Symbol::$sym_identifier;
			} else if( $this->c === "\\" ){
				$q = $this->s;
				$this->s = $q . $this->parseQualifiedIdentifier();
			}
			$this->sym = Symbol::$sym_identifier;
		}
		return FALSE;
	
	} else if( self::isDigit($this->c) ){
		$this->sym = $this->parseNumber();
		return FALSE;
		
	} else {
		throw new ScannerException($this->here(),
		"unexpected character " . self::reportChar($this->c));
	}
}


	/**
	 * Reads the next symbol.
	 * @return void
	 */
	public function readSym() {
		do {

			$new_sym_found = FALSE;

			switch ($this->part) {

				case self::inside_text:
					$this->parseText();
					$new_sym_found = FALSE;
					break;

				case self::inside_code:
					$new_sym_found = $this->parseCode();
					break;

				case self::inside_x_code:
					$new_sym_found = $this->parseXCode();
					break;

				case self::inside_embedded_variable:
					/*
					  Parsing double quoted string with embedded vars.
					  We are here:
					  "aaaaaa$VARIABLE zzz"
					  HERE____^ on the 'V'
					 */
					$this->parseKeyword();
					if ($this->sym !== Symbol::$sym_unknown) {
						$this->logger->error($this->here(),
						"the name `\$" . $this->s . "' is a keyword."
						. " This is deprecated by PHP and forbidden by PHPLint.");
					}
					$this->sym = Symbol::$sym_embedded_variable;
					$this->part = self::inside_double_quoted_string;
					break;

				case self::inside_double_quoted_string:
					/*
					  Parsing double quoted string with embedded vars.
					  We are here:
					  "...$VARIABLE...."
					  HERE_________^ after the var
					 */
					$new_sym_found = $this->parseDoubleQuotedString();
					break;

				default:
					throw new RuntimeException();
			}
			
			if( $this->sym === Symbol::$sym_x_doc ){
				$this->logger->error($this->here(), "old style DOC annotations are not supported anymore. Use DocBlocks or PHPLint meta-code instead.");
				$new_sym_found = TRUE;
			}
			
		} while ($new_sym_found);
	}

	/**
	 * Creates a PHP source scanner.
	 * @param Logger $logger Error messages are reported here.
	 * @param File $fn Source file name used to build Where objects. This file
	 * name is not used to read the actual source file (see the following
	 * parameter).
	 * @param InputStream $is Source file opened as input stream. Note that it
	 * is the responsability of the caller to open this file. Normally this
	 * file corresponds to the $fn parameter.
	 * @param PhpVersion $php_ver Version of PHP that applies.
	 * @return void
	 * @throws IOException Exceptionally, the constructor may throw exception,
	 * but the rest of this class avoids exceptions and logs errors instead.
	 * This simplifies the client code, that can ignore exceptions. The symbol
	 * Symbol::$sym_eof is returned either at the end of the source file or
	 * after an I/O error; in this latter case, an error has already been
	 * reported to through the logger.
	 */
	public function __construct($logger, $fn, $is, $php_ver) {
		$this->logger = $logger;
		$this->fn = $fn;
		$this->lis = new LineInputWrapper($is);
		$this->php_ver = $php_ver;
		$this->part = self::inside_text;
		$this->line = $this->lis->readLine();
		$this->line_n = 1;
		$this->line_idx = 0;
		$this->line_pos = 0;
		$this->c = NULL;
		if ($logger->print_source)
			$this->printLineSource();
		$this->readCh();
		$this->readSym();
	}


	/**
	 * Closes the scanner and the input file.
	 * @return void
	 * @throws IOException
	 */
	public function close() {
		$this->lis->close();
	}


}

Scanner::static_init();
