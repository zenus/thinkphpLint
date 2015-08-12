<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\io\File;
use it\icosaedro\io\OutputStream;
use it\icosaedro\io\IOException;
use it\icosaedro\utils\Strings;
use it\icosaedro\lint\documentator\Documentator;


/**
 * Simple programming interface to the PHPLint validator. Initializes the
 * PHPLint classes according to a set of options passed as array.
 * Sends output to the specified output stream.
 * Parameters are read from an array of strings; the first entry, being the
 * name of the program, is ignored and can then be anything. The available
 * options are exactly those of the PHPLint: in fact, THIS is the actual
 * PHPLint program. This example displays on standard output all the available
 * options:
 * <blockquote><pre>
 * $os = new ResourceOutputStream(STDOUT);
 * $err = Linter::main($os, array("PHPLint", "--help");
 * echo "Errors counter: $err\n";
 * </pre></blockquote>
 * This programming interface accepts all the strings as locale-encoded, so
 * file names and their paths must be locale-encoded. On Unix and Linux, where
 * the UTF-8 locale encoding is commonly available, any file name is accessible.
 * On Windows, only locale-encoded files and paths are accessible. The same
 * restriction holds for the PHP require_once statement, for the fopen()
 * function and for any other PHP file access feature in general, and is not an
 * actual limitation of PHPLint.
 * <p>
 * 
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/05 15:18:07 $
 */
class Linter {
	
	const VERSION = "2.1_20150305";


/**
 * Splits a list of directory into an array of File.
 * @param Logger $logger Errors reported here.
 * @param File $cwd Relative path are resolved against this directory.
 * @param string $path List of directories. The separator character depends
 * from the underlying file system, and should be a colon on Unix/Linux
 * and a semicolon on Windows.
 * @return File[int]
 */
private static function splitPath($logger, $cwd, $path){
	$a = explode(PATH_SEPARATOR, $path);
	$b = /*. (File[int]) .*/ array();
	foreach($a as $p){
		try {
			$d = File::fromLocaleEncoded($p, $cwd);
			if( ! $d->exists() ){
				$logger->error(NULL, "$d: directory does not exist");
			} else if( ! $d->isDirectory() ){
				$logger->error(NULL, "$d: not a directory");
			} else {
				$b[] = $d;
			}
		}
		catch(IOException $e){
			$logger->error(NULL, "$p: " . $e->getMessage());
		}
	}
	return $b;
}


/**
 * @param Logger $logger
 * @return void 
 */
private static function version($logger){
	$logger->printVerbatim(
	"PHPLint " . self::VERSION . "\n" .
	"Copyright 2014 by icosaedro.it di Umberto Salsi\n" .
	"This is free software; see the license for copying conditions.\n" .
	"More info: http://www.icosaedro.it/phplint\n" .
	"\n");
}


/**
 * @param Logger $logger
 * @return void 
 */
private static function help($logger)
{
	$logger->printVerbatim(
"Usage:   phplint [OPTION] FILE ...\n" .
"A PHP language parser and validator with extended syntax.\n" .
"\nOPTIONS:\n\n" .
"Between round parentheses is the default value.\n" .
"  --version               print version\n" .
"  --help                  this help\n" .
"  --php-version V         set PHP version V to 4 or 5 (5)\n" .
"  --modules-path PATH     set the path[s] to modules dir[s] (\".\")\n" .
"  --[no-]is-module        parsed file is a module (FALSE)\n" .
"  --[no-]recursive        follows require_once recursively (TRUE)\n" .
"  --[no-]print-file-name  print file name along error also for the file\n" .
"                          currently being parsed (TRUE)\n" .
"  --[no-]print-column-number  print column number along error (FALSE)\n" .
"  --print-path absolute   print file names as absolute path (default)\n" .
"  --print-path relative   ...or relative to the current working directory\n" .
"  --print-path shortest   ...or the shortest of the two above\n" .
"  --[no-]ctrl-check       check control chars in strings (TRUE)\n" .
"  --[no-]ascii-ext-check  check extended ASCII chars in strings (TRUE)\n" .
"  --[no-]print-notices    print notices  (TRUE)\n" .
"  --[no-]print-warnings   print warnings (TRUE)\n" .
"  --[no-]print-errors     print errors   (TRUE)\n" .
"  --[no-]parse-phpdoc     parse DocBlock comments (TRUE)\n" .
"  --[no-]print-context    print error context (FALSE)\n" .
"  --[no-]print-source     print source (FALSE)\n" .
"  --[no-]print-line-numbers   print the line numbers along the source (TRUE)\n" .
"  --[no-]report-unused    report unused IDs (TRUE)\n" .
"  --[no-]fails-on-warning exit status 1 also for warnings (FALSE)\n" .
"  --tab-size N            set tabulation to N spaces (8)\n" .
"  --project-root          set root directory of project\n" .
"  --[no-]overall          displays total no. of errors and warnings (TRUE)\n" .
"  --doc-help              help about the PHPLint Documentator\n" .
"The name of the FILE(s) must be locale-encoded.\n" .
"Exit status 0 if no errors, 1 on errors; if the --fails-on-warning option is\n" .
"set, exit status 1 also for warnings.\n" .
"\nReport bugs to phplint@icosaedro.it (but read README before submit).\n" .
"Info and updates: www.icosaedro.it/phplint\n" .
"\n");
}


/**
 * Parses the PHPLint command line arguments and invokes the lint libraries
 * accordingly.
 * @param OutputStream $os Writes the report here.
 * @param string[int] $argv Arguments from the command line. The first one is
 * the program itself, and is ignored.
 * @return int Exit status 0 (success) or 1 (error).
 * @throws IOException
 */
public static function main($os, $argv)
{
	$logger = new Logger($os, File::getCWD());
	$logger->print_errors = TRUE;
	$logger->print_warnings = TRUE;
	$logger->print_notices = TRUE;
	$logger->print_context = FALSE;
	$logger->print_source = FALSE;
	$logger->print_line_numbers = TRUE;
	$logger->print_file_name = TRUE;
	$logger->print_column_number = FALSE;
	$logger->print_path_fmt = Logger::ABSOLUTE_PATH;
	$logger->tab_size = 8;
	
	$globals = new GlobalsImplementation($logger);
	$globals->php_ver = PhpVersion::$php5;
	$globals->recursive_parsing = TRUE;
	$globals->parse_phpdoc = TRUE;
	$globals->report_unused = TRUE;
	
	Scanner::$ctrl_check = TRUE;
	Scanner::$ascii_ext_check = TRUE;
	
	$is_module = FALSE;
	$fails_on_warning = FALSE;
	$overall = TRUE;
	$doc = /*. (Documentator) .*/ NULL;
	$do_report = FALSE;

	for($i = 1; $i < count($argv); $i++){
		
		$arg = $argv[$i];
		switch($arg){
		
		case "--help"             : self::help($logger);  break;
		case "--version"          : self::version($logger);  break;
		case "--recursive"        : $globals->recursive_parsing = TRUE;  break;
		case "--no-recursive"     : $globals->recursive_parsing = FALSE;  break;
		case "--print-file-name"  : $logger->print_file_name = TRUE;  break;
		case "--no-print-file-name" : $logger->print_file_name = FALSE;  break;
		case "--print-column-number"  : $logger->print_column_number = TRUE;  break;
		case "--no-print-column-number" : $logger->print_column_number = FALSE;  break;
		case "--ctrl-check"       : Scanner::$ctrl_check = TRUE;  break;
		case "--no-ctrl-check"    : Scanner::$ctrl_check = FALSE;  break;
		case "--ascii-ext-check"  : Scanner::$ascii_ext_check = TRUE;  break;
		case "--no-ascii-ext-check" : Scanner::$ascii_ext_check = FALSE;  break;
		case "--print-notices"    : $logger->print_notices = TRUE;  break;
		case "--no-print-notices" : $logger->print_notices = FALSE;  break;
		case "--print-warnings"   : $logger->print_warnings = TRUE;  break;
		case "--no-print-warnings" : $logger->print_warnings = FALSE;  break;
		case "--print-errors"     : $logger->print_errors = TRUE;  break;
		case "--no-print-errors"  : $logger->print_errors = FALSE;  break;
		case "--print-context"    : $logger->print_context = TRUE;  break;
		case "--no-print-context" : $logger->print_context = FALSE;  break;
		case "--print-source"     : $logger->print_source = TRUE;  break;
		case "--no-print-source"  : $logger->print_source = FALSE;  break;
		case "--print-line-numbers"  : $logger->print_line_numbers = TRUE;  break;
		case "--no-print-line-numbers" : $logger->print_line_numbers = FALSE;  break;
		case "--parse-phpdoc"     : $globals->parse_phpdoc = TRUE;  break;
		case "--no-parse-phpdoc"  : $globals->parse_phpdoc = FALSE;  break;
		case "--report-unused"    : $globals->report_unused = TRUE;  break;
		case "--no-report-unused" : $globals->report_unused = FALSE;  break;
		case "--is-module"        : $is_module = TRUE;  break;
		case "--no-is-module"     : $is_module = FALSE;  break;
		case "--fails-on-warning" : $fails_on_warning = TRUE;  break;
		case "--no-fails-on-warning" : $fails_on_warning = FALSE;  break;
		case "--overall"          : $overall = TRUE;  break;
		case "--no-overall"       : $overall = FALSE;  break;
		
		case "--print-path":
			$i++;
			if( $i >= count($argv) ){
				$logger->error(NULL, "phplint: missing argument for --print-path\n");
				exit(1);
			}
			if( $argv[$i] === "absolute" ){
				$logger->print_path_fmt = Logger::ABSOLUTE_PATH;
			} else if( $argv[$i] === "relative" ){
				$logger->print_path_fmt = Logger::RELATIVE_PATH;
			} else if( $argv[$i] === "shortest" ){
				$logger->print_path_fmt = Logger::SHORTEST_PATH;
			} else {
				$logger->error(NULL, "phplint: invalid argument for --print-path");
				exit(1);
			}
			break;
			
		case "--php-version":
			$i++;
			if( $i >= count($argv) ){
				$logger->error(NULL, "phplint: missing argument for --php-version");
				exit(1);
			}
			if( $argv[$i] === "4" ){
				$globals->php_ver = PhpVersion::$php4;
			} else if( $argv[$i] === "5" ){
				$globals->php_ver = PhpVersion::$php5;
			} else {
				$logger->error(NULL, "phplint: invalid PHP version - must be 4 or 5");
				exit(1);
			}
			break;
			
		case "--modules-path":
			$i++;
			if( $i >= count($argv) ){
				$logger->error(NULL, "phplint: missing argument for --modules-path");
				exit(1);
			}
			$globals->modules_dirs = self::splitPath($logger, $logger->cwd, $argv[$i]);
			break;

			/** add project-root for thinkphp framework*/
        case "--project-root":
            $i++;
            if( $i >= count($argv) ){
                $logger->error(NULL, "phplint: missing argument for --project-root");
                exit(1);
            }
            $globals->project_root = current(self::splitPath($logger, $logger->cwd, $argv[$i]));
            break;
		case "--tab-size":
			$i++;
			if( $i >= count($argv) ){
				$logger->error(NULL, "phplint: missing argument for --tab-size");
				exit(1);
			}
			$logger->tab_size = (int) $argv[$i]; // FIXME: use Integer $c
			break;
		default:
			if( Strings::startsWith($arg, "--doc") ){
				if( $doc === NULL )
					$doc = new Documentator($globals);
				$i = $doc->parseCommandLine($i, $argv) - 1;
			} else if( (strlen($arg) > 0) && ($arg[0] === "-") ){
				$logger->error(NULL, "phplint: unknown option `$arg'");
				exit(1);
			}else {
				if($globals->project_root == NULL){
					$logger->error(NULL, "phplint:  --project-root required");
					exit(1);
				}
				/** before phplint start analyze, Thinkphp  needs to do some preparation work */
				 $arg = Thinkphp::bootstrap($arg,$globals);
				$fn = File::fromLocaleEncoded($arg, $logger->cwd);
				$logger->main_file_name = $fn;
				$logger->current_file_name = $fn;
				$globals->loadPackage($fn, $is_module);
				Thinkphp::destruct();
				if( $doc !== NULL && $globals->getPackage($fn) !== NULL )
					$doc->generate($fn);
				$do_report = TRUE;
			}
		
		}
	}

	if( $do_report ){

		if( $logger->print_notices )
			Report::reportUndeclaredUnusedRequired($globals);
		
		// FIXME: remove test code:
//		$logger->println("" . $g->packages->count() . " packages parsed, "
//			. $g->total_source_length . " bytes, "
//			. count($g->vars) . " max vars");
//		
//		for($i = count($g->vars)-1; $i >= 0; $i--){
//			$v = $g->vars[$i];
//			if( $v !== NULL )
//				$logger->println("var: $i $v");
//		}

		if( $overall )
			$logger->println("Overall test results: "
				. $logger->error_counter . " errors, "
				. $logger->warning_counter . " warnings.");
	}
	
	if( $logger->os_exception !== NULL ){
		error_log("PHPLint output stream failure: " . $logger->os_exception . "\n");
		$logger->error_counter++;
	}

	if( $logger->error_counter == 0
	&& (! $fails_on_warning || $logger->warning_counter == 0) ){
		return 0;
	} else {
		return 1;
	}

}
	
}
