<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\io\File;
use it\icosaedro\containers\Printable;

/**
 * Holds a location in a source text file, including file name, line number,
 * column and the line itself.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:26:50 $
 */
class Where implements Printable {

	/**
	 * Source file. If NULL, the diagnostic message is not referring to a
	 * specific file.
	 * @var File
	 */
	private $f;

	/**
	 * Line number, the first being the no. 1.
	 * If 0, error happened even before the first line had been read.
	 * If less that 0, not available.
	 * @var int
	 */
	private $line_n = 0;

	/**
	 * Column no. starting from 1.
	 * If less than 0, not available.
	 * @var int 
	 */
	private $col = 0;

	/**
	 * A line of the source, possibly with trailing "\r\n" or "\n" and
	 * tabulators. If NULL, not available.
	 * @var string 
	 */
	private $line;

	/**
	 * Creates a "where" object referring to a specific position in the source
	 * file being parsed. Unspecified arguments are left as unknown.
	 * @param File $f Source file being parsed, that is a "package" in PHPLint
	 * terminology. Set to null if unspecified.
	 * @param int $line_n Line number. First line is the no. 1. Set to negative
	 * or zero if not specified.
	 * @param int $col Column no. First char in line is the no. 1. Set to
	 * negative or zero if not specified.
	 * @param string $line The source line. Set to NULL if not specified.
	 * @return void
	 */
	public function __construct($f = NULL, $line_n = -1, $col = -1, $line = NULL) {
		$this->f = $f;
		$this->line_n = $f !== NULL ? $line_n : -1;
		$this->col = $this->line_n >= 1 ? $col : -1;
		$this->line = $this->col >= 1 ? $line : /*. (string) .*/ NULL;
	}
	
	/**
	 * Returns the file name of this location.
	 * @return File File name of this location, possibly NULL if unknown.
	 */
	public function getFile(){
		return $this->f;
	}
	
	
	/**
	 * Returns the line number of this location.
	 * @return int Line number of this location, possibly &lt; 1 if unknown.
	 */
	public function getLineNo(){
		return $this->line_n;
	}
	
	/**
	 * Returns the column number of this location.
	 * @return int Column number of this location, possibly &lt; 1 if unknown.
	 */
	public function getColumn(){
		return $this->col;
	}
	
	/**
	 * Returns the source line at this location.
	 * @return string Source line at this location, possibly NULL if unknown.
	 * The line can be empty and may include trailing '\r\n' or '\n'.
	 */
	public function getLine(){
		return $this->line;
	}
	
	
	/** @var Where */
	private static $cached_nowhere, $cached_somewhere;

	/**
	 * Convenience method that returns an instance of a "nowhere"
	 * reference. A location which is still unknown may be initialized to this
	 * value. Since no file is set in it, it is likely to produce a fatal
	 * error if used. Logic of the program should garantee such an object
	 * will never be used.
	 * @return Where Nowhere.
	 */
	public static function getNowhere() {
		if( self::$cached_nowhere === NULL )
			self::$cached_nowhere = new Where();
		return self::$cached_nowhere;
	}

	/**
	 * Convenience method that returns a singleton instance of "somewhere"
	 * reference. Built-in entities of PHP and PHPLint are an example.
	 * @return Where Somewhere.
	 */
	public static function getSomewhere() {
		if( self::$cached_somewhere === NULL )
			self::$cached_somewhere = new Where(
				File::fromLocaleEncoded(__DIR__ . "\\PHPLint.php"),
				1, 1, "(PHP or PHPLint built-in entity)");
		return self::$cached_somewhere;
	}
	
	
	/**
	 * Returns this position as "filename:lineno".
	 * @return string
	 */
	public function __toString() {
		return $this->f . ":" . $this->line_n . ":" . $this->col;
	}

}
