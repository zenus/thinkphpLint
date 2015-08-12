<?php

/* .
  require_module 'standard';
  require_module 'spl';
  . */

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../all.php";

use ErrorException;
use RuntimeException;
use InvalidArgumentException;
use CastException;
use it\icosaedro\io\IOException;
use it\icosaedro\utils\Strings;
use it\icosaedro\utils\UString;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\UPrintable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\containers\Hashable;

/**
 * Abstract representation of a file path. Instances of this class represent the
 * absolute path of a file or directory. This class avoids as long as it can
 * any actual access to the underling file system, and keeps manipulating only
 * simple strings in memory. Access to the underling file system may throw
 * exceptions, so it can be performed only on request by the client program.
 * The objects of this class are immutable, sortable and comparable.
 * 
 * <p>
 * File names are stored as Unicode strings; the zero byte is not allowed and
 * causes exception. While the constructor only accepts Unicode strings through
 * a <code>UString</code> object, the <code>fromLocaleEncoded()</code> method
 * is also provided that accept a locale encoded string instead.
 * The <code>getLocaleEncoded()</code> method returns a
 * locale encoded string that can be feed to I/O functions of PHP.
 * For more about file name encoding see {@link it\icosaedro\io\FileName}.
 * 
 * <p>
 * Under Windows, drive letters and UNC paths are supported; slash and
 * back-slash are the same; file name comparisons are performed in a
 * case-folded version of their string representation. Example:
 * 
 * <pre>
 * 	$fn = new File( UString::fromUTF8("x:/a/b/../c") );
 * 	echo $fn;  // "x:/a/c"
 * 	echo $fn-&gt;getParentFile(); // "x:/a"
 * 	echo $fn-&gt;getName();  // "c"
 * 	if( $fn-&gt;exists() ){
 * 		echo "Now reading $fn, ", $f-&gt;size(), " bytes:";
 * 		$f = fopen($fn-&gt;fromLocaleEncoded(), "rb");
 * 		...
 * 	}
 * </pre>
 * 
 * <p>
 * File names provided to the constructor of this class are stored in a
 * normalized form as absolute paths, with "." and ".." resolved, redundant
 * slashes removed, trailing slashes removed. Invalid paths with too many ".."
 * give exception, for example <code>"C:/a/../../b"</code> is not
 * valid.
 * Relative paths can be resolved only providing a second argument to the
 * constructor telling the directory against which the relative path must be
 * resolved.
 * Example:
 * 
 * <pre>
 * 	$cwd = File::fromLocaleEncoded("C:/Users/My name/datadir");
 * 	$d1 = File::fromLocaleEncoded("data1.txt", $cwd);
 * 	$d2 = File::fromLocaleEncoded("data2.txt", $cwd);
 * 	$summary = File::fromLocaleEncoded("../Documents/summary.txt", $cwd);
 * </pre>
 * 
 * <p>
 * File names are keep in an internal <b>normalized name</b>.
 * Under Unix/Linux this normalized name always begins with a slash
 * and may continue with one or more names separated by a single slash.
 * Under Windows the normalized name always starts either with a disk unit letter
 * followed by colon and a slash (example: "C:/") and it may continue with
 * several names separated by slash (not back-slash), or it may start with
 * a remote host name of a UNC path (example: "\\\\Host/"). Note that in the
 * latter case two back-slashes begin the string. Using a simplified EBNF
 * notation, the normalized file name can be described as follows:
 * 
 * <pre>
 * 	normalized_name = drive [name {"/" name}];
 * </pre>
 * 
 * Under Unix/Linux:
 * <pre>
 *	drive = "/";
 *	name = any sequence of bytes except '\0' and '/'; the names "." and ".."
 *	are never present in a normalized path.
 * </pre>
 * 
 * Under Windows:
 * <pre>
 * 	drive = "A".."Z" ":/" | "\\\\" host "/";
 * 	name = any sequence of bytes except '\0', '/' and '\\', names "." and ".."
 * 	are never present in a normalized path.
 * </pre>
 * 
 * <p>
 * Note that a normalized path is not necessarily a valid file name for
 * the underling file system, as several restriction may forbid some other
 * characters, may set a maximum length to the path, or may contain
 * directories that does not exist or are not accessible.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:05:09 $
 */
class File implements Printable, UPrintable, Sortable, Hashable {

	/**
	 * File system type is Windows? If not, it is Unix/Linux.
	 * @var boolean 
	 */
	private static $is_win = FALSE;

	/**
	 * Normalized path.
	 * @var UString 
	 */
	private $path;

	/**
	 * Cached hash code.
	 * @var int
	 */
	private $hash = 0;

	/**
	 * If this file has to be deleted when this object gets destroyed.
	 * @var boolean
	 */
	private $delete_on_destruct = FALSE;

	/**
	 * Files to be automatically deleted on exit.
	 * @var File[int]
	 */
	private static $delete_on_exit = /*. (File[int]) .*/ array();

	/**
	 * For internal use only.
	 * @return void
	 */
	public static function static_init() {
		File::$is_win = DIRECTORY_SEPARATOR === "\\";
	}

	/**
	 * Returns this file name encoded in the current system locale. This
	 * function must be used whenever a PHP file system related function
	 * requires a file name.
	 * @return string This file name encoded in the current system locale.
	 * @throws IOException Unknown or unsupported from/to encodings. Failed
	 * conversion: some characters cannot be converted. NUL byte detected.
	 */
	public function getLocaleEncoded() {
		// Cannot cache the value: locale encoding might change.
		return FileName::encode($this->path);
	}

	/**
	 * Returns this normalized file name encoded in the current system locale.
	 * @return string This normalized file name encoded in the current system
	 * locale. If the file name cannot be represented in the current system
	 * locale encoding, an approximated ASCII-only representation is returned
	 * instead.
	 */
	public function __toString() {
		try {
			return $this->getLocaleEncoded();
		} catch (IOException $e) {
			// __toString() cannot throw exceptions. Workaround:
			return $this->path->toASCII();
		}
	}

	/**
	 * Returns this normalized file name.
	 * @return UString This normalized file name.
	 */
	public function toUString() {
		return $this->path;
	}

	/**
	 * @param string $c
	 * @return boolean 
	 */
	private static function isLetter($c) {
		$ch = ord($c);
		return ord('a') <= $ch && $ch <= ord('z')
				|| ord('A') <= $ch && $ch <= ord('Z');
	}

	/**
	 * @param string $c
	 * @return boolean 
	 */
	private static function isDigit($c) {
		$ch = ord($c);
		return ord('0') <= $ch && $ch <= ord('9');
	}

	/**
	 * Returns the volume name from a file name. This concept exists only on
	 * Windows; under Unix/Linux this function always returns the empty string.
	 * A volume name is the unit drive letter (example: "C:") or the UNC host
	 * part (example: "\\\\DataStore"). The drive letter "C:" is always
	 * returned uppercased.
	 * @param string $fn A file name.
	 * @return string The volume name, or the empty string if not available.
	 */
	private static function getVol($fn) {
		if (!self::$is_win)
			// No volume concept in Unix file paths.
			return "";

		if (strlen($fn) >= 3 && Strings::startsWith($fn, "\\\\")) {
			// UNC, ex. "\\\\host\a\b".
			// Determine start of the resource part:
			$r = strpos($fn, "\\", 2);
			if ($r === FALSE)
				$r = strpos($fn, "/", 2);
			if ($r === FALSE) {
				return $fn;
			} else if ($r == 3) {
				return "";
			} else {
				return substr($fn, 0, $r);
			}
		} else if (strlen($fn) >= 2
				&& self::isLetter($fn[0]) && $fn[1] === ':') {
			return strtoupper(substr($fn, 0, 2));
		} else {
			return "";
		}
	}

	/**
	 * Normalize the file name.
	 * @param File $cwd  The directory to be used to resolve relative paths or
	 * missing drive letter. If NULL, may throw exception.
	 * @param string $fn File name to be normalized, UTF-8 encoded.
	 * @return string Normalized path.
	 * @throws InvalidArgumentException Empty or NULL file name.
	 * NUL byte in file name. Missing drive letter or UNC path, but no current
	 * working directory provided (Windows only). Relative path provided, but
	 * missing $cwd. Relative path provided for a disk unit or UNC path that
	 * does not match the current working directory (example: $fn="C:a/b" note
	 * the relative path "a/b" referring to the current working directory of the
	 * C: unit, but $cwd="D:/workdir" referring to another disk unit; Windows
	 * only). Too many ".." references.
	 */
	private static function normalize($cwd, $fn) {
		$ori = $fn;
		if (strlen($fn) == 0)
			throw new InvalidArgumentException("empty or NULL file name");

		// Detects ASCII NUL, not allowed in C strings used by fopen()
		// (anyway, forbidden both in win and unix):
		if (strpos($fn, 0) !== FALSE)
			throw new InvalidArgumentException("NUL byte found in file name: "
					. Strings::toLiteral($fn));

		// Split drive part from resource part:
		if (self::$is_win) {
			// Detect drive or host:
			$vol = self::getVol($fn);
			if (strlen($vol) == 0) {
				if ($cwd === NULL)
					throw new InvalidArgumentException(
							"missing drive or remote host in path: "
							. Strings::toLiteral($ori));
				$vol = self::getVol($cwd->toUString()->toUTF8());
				$res = $fn;
			} else {
				$res = Strings::substring($fn, strlen($vol), strlen($fn));
				if ($res === "")
					$res = "/";
			}

			// Convert backslash into slash.
			$res = (string) str_replace("\\", "/", $res);
		} else {
			$vol = "";
			$res = $fn;
		}

		// Makes abs the path of the resource:
		if (!Strings::startsWith($res, "/")) {
			if ($cwd === NULL)
				throw new InvalidArgumentException(
						"cannot resolve relative path: "
						. Strings::toLiteral($ori));
			$cwd_vol = self::getVol($cwd->toUString()->toUTF8());
			if (strcasecmp($cwd_vol, $vol) != 0)
				throw new InvalidArgumentException(
						"cannot resolve relative path: "
						. Strings::toLiteral($ori));
			$res = substr($cwd->toUString()->toUTF8(), strlen($vol)) . "/" . $res;
		}

		// Resolve . and .. :
		$a = explode("/", $res);
		$b = /*. (string[int]) .*/ array();
		$j = 0;
		for ($i = 0; $i < count($a); $i++) {
			if ($a[$i] === "" || $a[$i] === ".") {
				// ignore empty dir name "a//b" and .
			} else if ($a[$i] === "..") {
				if ($j == 0)
					throw new InvalidArgumentException("too many .. in path: "
							. Strings::toLiteral($ori));
				$j--;
				unset($b[$j]);
			} else {
				$b[$j++] = $a[$i];
			}
		}
		$res = "/" . implode("/", $b);

		return $vol . $res;
	}

	/**
	 * Creates an abstract instance of a file or directory name.
	 * The first argument is the path to be stored; this path can be either
	 * relative or absolute; under Windows, the path may o may not contain
	 * a disk unit letter or a UNC path. The second parameter is used to
	 * resolve relative paths (Windows and Unix/Linux) and to resolve missing
	 * drive (Windows only).
	 * @param UString $fn File name, possibly including a drive letter and
	 * a resource path. This drive or file does not need to really exist at the time
	 * in which this instance is created.
	 * If the drive letter is missing or the provided path is relative,
	 * the result is made absolute using the CWD.
	 * @param File $cwd Relative paths file names are resolved against this
	 * directory. If NULL, relative paths cannot be resolved and an exception
	 * is thrown.
	 * @return void
	 * @throws InvalidArgumentException Empty or NULL file name.
	 * NUL byte in file name. Missing drive letter or UNC path, but no current
	 * working directory provided (Windows only). Relative path provided, but
	 * missing $cwd. Relative path provided for a disk unit or UNC path that
	 * does not match the current working directory (example: $fn="C:a/b" note
	 * the relative path "a/b" referring to the current working directory of the
	 * C: unit, but $cwd is "D:/workdir" referring to another disk unit; Windows
	 * only). Too many ".." back references.
	 */
	public function __construct($fn, $cwd = NULL) {
		$this->path = UString::fromUTF8(self::normalize($cwd, $fn->toUTF8()));
	}

	/**
	 * Creates an abstract instance of a file or directory name from a locale
	 * encoded string.
	 * The first argument is the path to be stored; this path can be either
	 * relative or absolute; under Windows, the path may o may not contain
	 * a disk unit letter or a UNC path. The second parameter is used to
	 * resolve relative paths (Windows and Unix/Linux) and to resolve missing
	 * drive (Windows only).
	 * @param string $fn  File name locale encoded, possibly including a drive
	 * letter and a resource path. This drive or file does not need to really
	 * exist at the time in which this instance is created.
	 * If the drive letter is missing or the provided path is relative,
	 * the result is made absolute using the CWD.
	 * @param File $cwd Relative paths file names are resolved against this
	 * directory. If NULL, relative paths cannot be resolved and an exception
	 * is thrown.
	 * @return File Abstract representation of the file name.
	 * @throws InvalidArgumentException Empty or NULL file name.
	 * NUL byte in file name. Missing drive letter or UNC path, but no current
	 * working directory provided (Windows only). Relative path provided, but
	 * missing $cwd. Relative path provided for a disk unit or UNC path that
	 * does not match the current working directory (example: $fn="C:a/b" note
	 * the relative path "a/b" referring to the current working directory of the
	 * C: unit, but $cwd is "D:/workdir" referring to another disk unit; Windows
	 * only). Too many ".." back references. Unknown or unsupported locale
	 * encoding. Some characters cannot be converted from the current locale.
	 * Invalid characters for the underlying file system.
	 */
	public static function fromLocaleEncoded($fn, $cwd = NULL) {
		try {
			$ufn = FileName::decode($fn);
		}
		catch(IOException $e){
			throw new InvalidArgumentException($e->getMessage());
		}
		return new File($ufn, $cwd);
	}

	/**
	 * Implements the {@link it\icosaedro\containers\Sortable} interface.
	 * Under Windows the comparison
	 * is case-insensitive using
	 * {@link it\icosaedro\utils\UString::compareIgnoreCaseTo()}.
	 * @param object $o Another object of this class.
	 * @return int Negative, zero or positive depending on if the name of this
	 * file comes before, is equals or comes next to the other.
	 * @throws CastException Provided object is either NULL or it is not
	 * exactly instance of this class.
	 */
	public function compareTo($o) {
		if ($o === NULL)
			throw new CastException("NULL");
		if ($this === $o)
			return 0;
		if ( ! ($o instanceof File) )
			throw new CastException("expected " . __CLASS__
				. " but got " . get_class($o));
		$o2 = cast(__CLASS__, $o);
		if (self::$is_win)
			return $this->path->compareIgnoreCaseTo($o2->path);
		else
			return $this->path->compareTo($o2->path);
	}
	

	/**
	 * Implements the {@link it\icosaedro\containers\Hashable} interface.
	 * @return int
	 */
	public function getHash() {
		if ($this->hash == 0) {
			if (self::$is_win)
				$this->hash = $this->path->getHashIgnoreCase();
			else
				$this->hash = $this->path->getHash();
		}
		return $this->hash;
	}
	

	/**
	 * Implements the {@link it\icosaedro\containers\Comparable} interface.
	 * Under Unix/Linux two file
	 * names are equal is their names are equal at binary level, no locale
	 * dependency is involved. Under Windows, case-folded versions of the
	 * paths are compared.
	 * @param object $o
	 * @return boolean
	 */
	public function equals($o) {
		if ($o === NULL)
			return FALSE;
		if ($this === $o)
			return TRUE;
		if (get_class($o) !== __CLASS__)
			return FALSE;
		$o2 = cast(__CLASS__, $o);
		if (self::$is_win){
			if( $this->getHash() != $o2->getHash() )
				return FALSE;
			return $this->path->equalsIgnoreCase($o2->path);
		} else
			return $this->path->equals($o2->path);
	}

	/**
	 * Cached value from getName().
	 * @var UString 
	 */
	private $cached_name;

	/**
	 * Name of this file, that is anything after the last slash.
	 * @return UString Last component of the path. Returns the empty string for
	 * the drive unit root directory "/" (Unix/Linux) and the root directory
	 * of the disk unit "C:/" or remote UNC "\\\\host/" (Windows).
	 */
	public function getName() {
		if ($this->cached_name !== NULL)
			return $this->cached_name;
		$s = $this->path->toUTF8();
		$i = strrpos($s, "/");
		if ($i === FALSE)
			throw new RuntimeException("missing slash in normalized path: "
					. $this->__toString());
		if ($i == strlen($s) - 1)
			$this->cached_name = UString::fromASCII(""); // already root dir
		else
			$this->cached_name = UString::fromUTF8(substr($s, $i + 1));
		return $this->cached_name;
	}

	/**
	 * Cached value from getExtension().
	 * @var string
	 */
	private $cached_extension;

	/**
	 * Returns the file name extension. The extension starts from the last dot
	 * in the name up to the end of the name, and must contain one or more ASCII
	 * letters and digits, the dot itself is returned by this function as well.
	 * The extension is considered invalid also if the remaining base name of
	 * the file would result to be "." or "..".
	 * Examples:
	 * <blockquote><pre>
	 * "/home/MyName/index.html" --&gt; ".html"
	 * "/home/MyName/data.bak_1" --&gt; ""
	 * "/home/MyName/.prefs"     --&gt; ""
	 * "/home/MyName/..prefs"    --&gt; ""
	 * </pre></blockquote>
	 * @return string File name extension including the dot, or the empty string
	 * if no recognizable extension has been found.
	 */
	public function getExtension() {
		if ($this->cached_extension !== NULL)
			return $this->cached_extension;
		$this->cached_extension = ""; // if not avail., this is the result
		$s = $this->path->toUTF8();
		$dot = strrpos($s, ".");
		$slash = strrpos($s, "/"); // cannot be FALSE on normalized path
		if (!($dot !== FALSE && $slash + 2 <= $dot && $dot <= strlen($s) - 2))
			return "";
		$ext = substr($s, $dot);
		// Remaining base name cannot be "." or "..":
		$b = substr($s, $slash, $dot - $slash);
		if ($b === "/." || $b === "/..")
			return "";
		// Extension must be "." followed by 1 or more letters and digits:
		for ($i = strlen($ext) - 1; $i >= 1; $i--)
			if (!(self::isLetter($ext[$i]) || self::isDigit($ext[$i]) ))
				return "";
		$this->cached_extension = $ext;
		return $ext;
	}

	/**
	 * Cached value from getBaseName().
	 * @var UString
	 */
	private $cached_basename;

	/**
	 * Returns the base name of this file, that is the full path name with the
	 * extension removed. For the definition of "extension" see the method
	 * {@link self::getExtension()}. Note that if no extension is detected, the
	 * full name of the file is returned instead.
	 * @return UString This file name without its extension. If no extension
	 * available, return itself.
	 */
	public function getBaseName() {
		if ($this->cached_basename !== NULL)
			return $this->cached_basename;
		$ext = $this->getExtension();
		if ($ext === "")
			return $this->path;
		$s = $this->path->toUTF8();
		$this->cached_basename = UString::fromUTF8(substr($s, 0, strlen($s) - strlen($ext)));
		return $this->cached_basename;
	}

	/**
	 * Returns the parent directory of this file, that is anything before the
	 * last slash.
	 * @return File Directory to which the file belongs, that is the path
	 * with the last component removed. If we are already at the root level,
	 * returns NULL.
	 */
	public function getParentFile() {
		$s = $this->path->toUTF8();
		$i = strrpos($s, "/");
		if ($i === FALSE)
			throw new RuntimeException("missing slash in normalized path: "
					. $this->__toString());
		if ($i == strpos($s, "/"))
			return NULL; // already root dir "/" (Unix) or "X:/" (Win)
		return new File(UString::fromUTF8(substr($s, 0, $i)), NULL);
	}

	/**
	 * Returns the current working directory of this process.
	 * @return File
	 * @throws IOException
	 */
	public static function getCWD() {
		try {
			$cwd = getcwd();
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage());
		}
		return self::fromLocaleEncoded($cwd, NULL);
	}
	
	
	/**
	 * Returns the current temporary directory.
	 * @return File The current temporary directory.
	 * @throws IOException 
	 */
	public static function getTempDir(){
		try {
			$d = sys_get_temp_dir();
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage());
		}
		return self::fromLocaleEncoded($d);
	}
	

	/**
	 * Creates a temporary file with arbitrary name and path.
	 * @return File Name of the temporary file just created.
	 * @throws IOException Cannot create temporary file.
	 */
	public static function createTempFile() {
		$prefix = "ico";
		$tmp = tempnam("", $prefix);
		if( $tmp === FALSE )
			throw new IOException("tempnam(\"\", \"$prefix\") failed");
		return self::fromLocaleEncoded($tmp);
	}
	
	
	/**
	 * Rename this file. On success, the old file does not exists anymore on
	 * disk.
	 * @param File $other New name.
	 * @return void
	 * @throws IOException
	 */
	public function rename($other){
		$a = $this->getLocaleEncoded();
		$b = $other->getLocaleEncoded();
		try {
			rename($a, $b);
		}
		catch(ErrorException $e){
			throw new IOException("rename($a, $b): " . $e->getMessage());
		}
	}
	
	
	/**
	 * Set this as current working directory.
	 * @return void
	 * @throws IOException
	 */
	public function setCWD(){
		try {
			chdir($this->getLocaleEncoded());
		}
		catch(ErrorException $e){
			throw new IOException("cannot change directory to "
				. $this->path . ": " . $e->getMessage());
		}
	}
	

	/**
	 * Check for the actual existence of the file or directory.
	 * @return boolean True if the file or directory exists, false in any other
	 * case or the drive does not exist or the directory is not accessible, etc.
	 * @throws IOException
	 */
	public function exists() {
		clearstatcache(TRUE, $this->getLocaleEncoded());
		return file_exists($this->getLocaleEncoded());
	}

	/**
	 * Checks if the path (that must already exist) represents an existing
	 * and accessible file.
	 * @return boolean True if the file does exist and it is accessible.
	 * If this file does not exist, or it is not accessible, return false.
	 * @throws IOException
	 */
	public function isFile() {
		clearstatcache(TRUE, $this->getLocaleEncoded()); // FIXME: really needed?
		return is_file($this->getLocaleEncoded());
	}

	/**
	 * Checks if the path (that must already exist) represents an existing
	 * and accessible directory.
	 * @return boolean True if the directory does exist and it is accessible.
	 * If this directory does not exist, or it is not accessible, returns false.
	 * @throws IOException
	 */
	public function isDirectory() {
		clearstatcache(TRUE, $this->getLocaleEncoded()); // FIXME: really needed?
		return is_dir($this->getLocaleEncoded());
	}

	/**
	 * Return the length of this file or directory.
	 * @return int Length of the file or directory, in bytes.
	 * @throws IOException File does not exits. File or some of the directories
	 * of its path are not accessible. File length too big, cannot be retrieved
	 * as an integer number.
	 */
	public function length() {
		clearstatcache(TRUE, $this->getLocaleEncoded());
		try {
			$l = filesize($this->getLocaleEncoded());
		} catch (ErrorException $e) {
			throw new IOException($e->getMessage());
		}
		if ($l === FALSE)
			throw new IOException($this->__toString() . ": filesize() retuns FALSE");
		if (!is_int($l) || $l < 0) // safety check
			throw new IOException($this->__toString() . ": file size too big");
		return $l;
	}

	/**
	 * Return the timestamp of the last modification of this file or directory.
	 * @return int Timestamp of the last modification as number of
	 * seconds since 1970-01-01 00:00:00 UTC (aka "Unix epoch").
	 * @throws IOException
	 */
	public function lastModified() {
		clearstatcache(TRUE, $this->getLocaleEncoded());
		try {
			return filemtime($this->getLocaleEncoded());
		} catch (ErrorException $e) {
			throw new IOException($e->getMessage());
		}
	}

	/**
	 * Returns the list of the files in this directory.
	 * @return File[int] List of the files in this directory.
	 * @throws IOException
	 */
	public function listFiles() {
		try {
			$d = dir($this->getLocaleEncoded());
		} catch (ErrorException $e) {
			throw new IOException($e->getMessage());
		}
		$list_ = /*. (File[int]) .*/ array();
		do {
			$entry = $d->read();
			if ($entry === FALSE)
				break;
			if ($entry !== "." && $entry !== "..") {
				$f = File::fromLocaleEncoded($entry, $this);
				$list_[] = $f;
			}
		} while (TRUE);
		return $list_;
	}
	
	
	/**
	 * Returns relative path of this file or directory versus the base directory.
	 * For example, if this is <code>/a/b/c/d</code> and the base directory is
	 * <code>/a/b/e/f/g</code>, the relative path returned is
	 * <code>../../e/f/g</code> that brings from the base directory to this
	 * file or directory. You may think at the base directory as the current
	 * working directory were your program is operating, so that it is
	 * convenient to represent all the file paths in a shorter, more readable
	 * relative form.
	 * 
	 * <p>
	 * Suppose, for example, the file <code>$data1</code> must refers to
	 * another file <code>$data2</code> with a relative path:
	 * <blockquote><pre>
	 * $data1 = new File( UString::fromASCII("/a/b/c/d") );
	 * $data2 = new File( UString::fromASCII("/a/b/e/f/g") );
	 * $base = $data2-&gt;getParentFile();
	 * $relative = $data1-&gt;relativeTo($base);
	 * // now $relative contains "../../e/f/g"
	 * </pre></blockquote>
	 * 
	 * <p>
	 * This method garantees that the resulting relative path can be
	 * combined with the original directory to retrieve the original file:
	 * <blockquote><pre>
	 * $data1_restored = new File($relative, $base);
	 * // now $data1_restored equals $data1
	 * </pre></blockquote>
	 * 
	 * <p>
	 * If a relative path does not exist, this is returned instead. If this
	 * equals base, "." is returned.
	 * @param File $base Base directory. Blindly assumes this is a directory,
	 * not a file. If it is a file, you may use <code>getParentFile()</code> as
	 * in the example above.
	 * @return UString Relative path of this file or directory versus the base
	 * directory.
	 */
	public function relativeTo($base) {
		if( $this->equals($base) )
			return UString::fromASCII(".");
		
		$slash = UString::fromASCII("/");
		$back = 0; // counts how many "../" required on base
		do {
			// In this algo, the common path must end with a single slash
			// to prevent collisions with identifiers that share a prefix,
			// example "/com" would match the start of "/common":
			if( $base->path->endsWith($slash) )
				// Already ends with slash ("/" on Unix, "C:/" on Win):
				$common = $base->path;
			else
				$common = $base->path->append($slash);
			
			// Does this start with the common path?
			if( self::$is_win )
				$hit = $common->length() <= $this->path->length()
					&& $this->path->substring(0, $common->length())
						->equalsIgnoreCase($common);
			else
				$hit = $this->path->startsWith($common);
			if( $hit ){
				return UString::fromASCII( str_repeat("../", $back) )
					->append( $this->path->substring($common->length(),
					$this->path->length()));
			}
			
			// Move up one dir and retry:
			$up = $base->getParentFile();
			if( $up == NULL || $up->equals($base) )
				return $this->path;
			$base = $up;
			$back++;
		} while(TRUE);
	}
	

	/**
	 * Register this file for deletion when this object gets destroyed by the
	 * garbage collector. See also the deleteOnExit() method.
	 * @return void 
	 */
	public function deleteOnDestruct() {
		$this->delete_on_destruct = TRUE;
	}

	/**
	 * Deletes this file or directory. If directory, it must be empty.
	 * @return void
	 * @throws IOException File or directory does not exist, or access denied.
	 * Directory not empty. Not a file or directory.
	 */
	public function delete() {
		$done = FALSE;
		try {
			if ($this->isFile())
				$done = unlink($this->getLocaleEncoded());
			else if ($this->isDirectory())
				$done = rmdir($this->getLocaleEncoded());
			else
				throw new IOException("not a file or directory: " . $this);
		} catch (ErrorException $e) {
			throw new IOException($e->getMessage());
		}
		if ($done)
			$this->delete_on_destruct = FALSE;
	}

	public function __destruct() {
		if (!$this->delete_on_destruct)
			return;
		try {
			$this->delete();
		} catch (IOException $e) {
			// can't throw exception in __destroy()
		}
	}

	/**
	 * Register this file or directory for automatic deletion on exit() or end
	 * of the request. Files registered for automatic deletion are deleted in
	 * reverse order.
	 * The file will not be deleted if the process is killed with a
	 * SIGTERM or SIGKILL signal.
	 * @return void
	 */
	public function deleteOnExit() {
		self::$delete_on_exit[] = $this;
	}

	/**
	 * Do not use this function. It is called to delete files registered for
	 * automatic deletion on normal termination of the program.
	 * @return void
	 */
	public static function exitFunction() {
		for ($i = count(self::$delete_on_exit) - 1; $i >= 0; $i--) {
			$f = self::$delete_on_exit[$i];
			try {
				$f->delete();
			} catch (IOException $e) {
				// can't throw exception in __destroy()
			}
		}
		// Just in case sombody calls me before program termination:
		self::$delete_on_exit = /*. (File[int]) .*/ array();
	}

}

File::static_init();
register_shutdown_function(array("it\\icosaedro\\io\\File", "exitFunction"));
