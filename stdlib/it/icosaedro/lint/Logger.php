<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\io\File;
use it\icosaedro\io\OutputStream;
use it\icosaedro\io\LineInputWrapper;
use it\icosaedro\io\IOException;

/**
 * Logger object. Holds: destination output stream of the report; several
 * preferences for the report format; errors and warnings counters.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/11 10:09:45 $
 */
class Logger {

	/** Always displays absolute file name. */
	const ABSOLUTE_PATH = 0;
	/** Displays path relative to the current working directory (see $cwd property). */
	const RELATIVE_PATH = 1;
	/** Choose the shortest between absolute and relative paths. */
	const SHORTEST_PATH = 2;

	/** Format of the file names path. One of the constants above. */
	public $print_path_fmt = self::ABSOLUTE_PATH;
	
	/** If to print the source currently parsed along with the report. */
	public $print_source = FALSE;
	
	/**
	 * If to display the line of source and the exact column position to
	 * which the diagnostic message is referring.
	 */
	public $print_context = FALSE;

	/**
	 * Print file name in error and warning messages also when the file
	 * interested is just the current source file. This generates shorter
	 * and more readable reports where messages lacking the file name are
	 * related to the main source file, that for which the parser was
	 * invocated.
	 */
	public $print_file_name = FALSE;
	
	/**
	 * Prints column number along with each logged message.
	 */
	public $print_column_number = FALSE;
	
	/**
	 * Main source file name for which the analyzer has been invoked.
	 * If the "--no-print-file-name" is on, and the reported message refers
	 * to this file, the the file name is not printed so that the report
	 * results shorter and more readable.
	 * @var File 
	 */
	public $main_file_name;


	/**
	 *  If the '--recursive' is on , with parent_file_name , we can control the recursive-level just
	 * to 2
	 *
	 */
	public $current_file_name;

	/**
	 * Preferred length of the HT. Used to align source lines along with
	 * generated diagnostic messages. Heretics prefers 4 here.
	 */
	public $tab_size = 8;
	
	public $print_notices = FALSE;
	public $print_warnings = FALSE;
	public $print_errors = FALSE;
	
	/**
	 * Prints line numbers along with each printed source line. This may
	 * disturb diff, so it is an option.
	 */
	public $print_line_numbers = FALSE;
	
	/** Number of error messages counted. */
	public $error_counter = 0;
	
	/** Number of warning messages counted. */
	public $warning_counter = 0;
	
	/**
	 * Destination of the report.
	 * @var OutputStream 
	 */
	private $os;
	
	/**
	 * Exception captured by writing the report. If not NULL, something went
	 * wrong and the report has not properly written.
	 * @var IOException 
	 */
	public $os_exception;

	/**
	 * Relative paths are calculated against this directory. Normally set to
	 * the CWD of the program or the directory of the source file being parsed
	 * first; in this latter case, errors occurring in the required packages can
	 * be displayed as relative path (see options for path formatting).
	 * @var File
	 */
	public $cwd;
	
	/**
	 * Initializes a new logger object.
	 * @param OutputStream $os Destination of the report; all the messages are
	 * sent here.
	 * @param File $cwd Current working directory. When relative file path
	 * option is enabled, relative paths are calculated against this directory.
	 * Usually it is the directory of the main package being parsed, but it
	 * might also be set with the directory of the project, or even the
	 * directory from which the lint tool has been launched.
	 * @return void
	 */
	public function __construct($os, $cwd)
	{
		$this->os = $os;
		$this->cwd = $cwd;
	}

	/**
	 * Build a relative file path.
	 * @param File $from
	 * @param File $to
	 * @return string 
	 */
	private static function relativePath($from, $to) {
		// FIXME: support Unicode file names
		return $to->relativeTo($from)->toASCII();
	}

	/**
	 * Formats a file name applying the format preferences.
	 * @param File $f
	 * @return string File path, that can be absolute or relative according
	 * to the preferences currently set.
	 */
	public function formatFileName($f) {
		switch ($this->print_path_fmt) {

			case self::ABSOLUTE_PATH:
				// FIXME: support Unicode file names
				return $f->toUString()->toASCII();

			case self::RELATIVE_PATH:
				return self::relativePath($this->cwd, $f);

			case self::SHORTEST_PATH:
				$r = self::relativePath($this->cwd, $f);
				// FIXME: support Unicode file names
				$s = $f->toUString()->toASCII();
				if (strlen($r) < strlen($s))
					return $r;
				else
					return $s;

			default:
				throw new \RuntimeException();
		}
	}

	/**
	 * Builds a relative, possibly short, reference from a location to
	 * another.
	 * @param Where $from Base location from which we reference the target
	 * location. Can be NULL.
	 * @param Where $to Target location. Can be NULL.
	 * @return string If the target location $to is in the same file of the base
	 * $from, then "line N" is returned, otherwise a longer "$to:N" is
	 * returned with the file path absolute or relative according to the
	 * current preferences set.
	 */
	public function reference($from, $to) {
		if( $to === NULL || $to->getFile() === NULL ){
			return "?:?";
		} else if ($from !== NULL && $to->getFile()->equals($from->getFile())) {
			return "line " . $to->getLineNo();
		} else {
			return $this->formatFileName($to->getFile()) . ":" . $to->getLineNo();
		}
	}

	/**
	 * Reports a message. Since in this program/library it is assumed the
	 * new-line sequence be simply '\n', this function changes that character
	 * to comply with the current system convention as set in the
	 * {@link PHP_EOL} constant.
	 * @param string $s Message to report verbatim.
	 * @return void
	 */
	public function printVerbatim($s) {
		if( $this->os_exception !== NULL )
			return;
		
		if( PHP_EOL !== "\n" )
			$s = (string) str_replace("\n", PHP_EOL, $s);
		try {
			$this->os->writeBytes($s);
		}
		catch(IOException $e){
			$this->os_exception = $e;
		}
	}

	/**
	 * Print file name and line number.
	 * @param Where $where
	 * @return void
	 */
	private function printLocation($where) {
		if ($this->print_context || $this->print_source) {
			# Human-readable report: make error msgs more evident:
			$this->printVerbatim("==== ");
		}
		if( $where === NULL || $where->getFile() === NULL ){
			$this->printVerbatim("?:?: ");
			return;
		}
		
		$fn = $where->getFile();
		if( $fn === NULL )
			$this->printVerbatim("?:");
		else if( $this->print_file_name || ! $fn->equals($this->main_file_name) )
			$this->printVerbatim($this->formatFileName($where->getFile()) . ":");
		
		if( $where->getLineNo() > 0 )
			if(isset(Thinkphp::$lineDistance) && !empty(Thinkphp::$lineDistance) && ($where->getLineNo()-Thinkphp::$lineDistance)>0){
				$this->printVerbatim((string)($where->getLineNo()-Thinkphp::$lineDistance));
			}else{
				$this->printVerbatim((string)$where->getLineNo());
			}
		else
			$this->printVerbatim("?");
		
		if( $this->print_column_number ){
			if( $where->getColumn() > 0 )
				$this->printVerbatim(":" . $where->getColumn());
			else
				$this->printVerbatim(":?");
		}
		
		$this->printVerbatim(": ");
	}
	

	/**
	 * Reports a line of source context with exact column position marker,
	 * then file name and line number.
	 * @param Where $where
	 * @return void
	 */
	private function printContextAndLocation($where) {
		if( $where !== NULL
		&& $this->print_context
		&& $where->getColumn() > 0 ) {
			$line = rtrim($where->getLine());
			$this->printVerbatim("\n\t" . $line . "\n\t"
			. str_repeat(" ", $where->getColumn() - 1) . "\\_ HERE\n");
		}

		$this->printLocation($where);
	}

	/**
	 * Reports a message. The string is trimmed and new-line '\n' replaced
	 * to comply with the current system convention. Continuation lines are
	 * indented.
	 * @param string $s Message to report.
	 * @return void
	 */
	public function println($s) {
		$s = (string) str_replace("\n", "\n\t", $s);
		$this->printVerbatim("$s\n");
	}

	/**
	 * Reports an error and increments the errors counter. The string is
	 * trimmed and new-line '\n' replaced to comply with the current system
	 * convention. Continuation lines are indented.
	 * @param Where $where
	 * @param string $s Message to report.
	 * @return void
	 */
	public function error($where, $s) {
		$this->error_counter++;
		if (!$this->print_errors)
			return;
		$this->printContextAndLocation($where);
		$this->println("ERROR: $s");
	}

	/**
	 * Reports an error and increments the warnings counter. The string is
	 * trimmed and new-line '\n' replaced to comply with the current system
	 * convention. Continuation lines are indented.
	 * @param Where $where
	 * @param string $s Message to report.
	 * @return void
	 */
	public function warning($where, $s) {
		$this->warning_counter++;
		if (!$this->print_warnings)
			return;
		$this->printContextAndLocation($where);
		$this->println("Warning: $s");
	}

	/**
	 * Reports a notice. The string is trimmed and new-line '\n' replaced to
	 * comply with the current system convention. Continuation lines are
	 * indented.
	 * @param Where $where
	 * @param string $s Message to report.
	 * @return void
	 */
	public function notice($where, $s) {
		if (!$this->print_notices)
			return;
		$this->printContextAndLocation($where);
		$this->println("notice: $s");
	}
	
}
