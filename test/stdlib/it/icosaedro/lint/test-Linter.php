<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../../../stdlib/all.php";
use it\icosaedro\io\File;
use it\icosaedro\io\InputStream;
use it\icosaedro\io\OutputStream;
use it\icosaedro\io\FileInputStream;
use it\icosaedro\io\FileName;
use it\icosaedro\io\ResourceOutputStream;
use it\icosaedro\io\IOException;
use it\icosaedro\utils\Strings;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\utils\UString;
use it\icosaedro\utils\SimpleDiff;
use it\icosaedro\io\FileOutputStream;
use it\icosaedro\io\LineInputWrapper;
use RuntimeException;

/**
 * Runs PHPLint over all the *.php test files of the test/list/ directory.
 * The new report is saved in the file *.report.DIFFERS.txt of the same directory
 * and compared with the previous report file *.report.txt: if equal, the
 * new report is deleted; if differences are found, the new report is left
 * there, and is the responsability of the PHPLint developer to examine
 * carefully the differences found and possibly replace the old report with
 * the new one.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/05 15:12:54 $
 */
class TestLinter {


	/**
	* @param OutputStream $os
	* @param boolean $is_php5
	* @param File $fn 
	* @return void
	*/
	private static function test($os, $is_php5, $fn)
	{
		try {
			// PHPLint base directory:
			$base = __DIR__ . "/../../../../..";
			$argv = array(
				"phplint.php",
				#"--version",
				"--print-path", "shortest",
				"--modules-path", "$base/modules",
				"--php-version", ($is_php5? "5" : "4"),
				"--print-errors",
				"--print-warnings",
				"--print-notices",
				"--ascii-ext-check",
				"--ctrl-check",
				"--recursive",
				"--no-print-file-name",
				"--print-path", "relative",
				"--parse-phpdoc",
				"--print-context",
				"--print-source",
				"--print-line-numbers",
				"--report-unused",
				$fn->__toString()
			);
			
			$err = Linter::main($os, $argv);

		}
		catch(IOException $e){
			error_log($e->getMessage() . "\n");
			exit(1);
		}
	}


	/**
	 * Generates all the report files. Differences are sent to stdout.
	 * @return void
	 * @throws IOException Something went wrong accessing the test files;
	 * fix and retry. 
	 */
	public static function main()
	{
		$good = 0;  // new report equals old one
		$bad = 0; // new report differs
		$generated = 0;  // no old report available; report generated
		$tests_dir = File::fromLocaleEncoded(__DIR__ . "/data");
		$tests_dir->setCWD();
		$tests = $tests_dir->listFiles();
		$skip = 0; // how many initial tests to skip
		$os = new ResourceOutputStream(STDOUT);
		foreach($tests as $test){
			if( ! $test->getName()->endsWith( UString::fromASCII(".php") ) )
				continue;
			$is_php5 = TRUE;
			if( $test->getName()->startsWith(UString::fromASCII("4-") ) )
				$is_php5 = FALSE;
			if( ! $is_php5 )
				continue; // don't care PHP 4 now
			if( $skip > 0 ){
				$skip--;
				continue;
			}
			$report = new File($test->getBaseName()->append(
					UString::fromASCII(".report.txt")));
			// FIXME: if $report does not exist, we must generate it
			$report_new = new File($test->getBaseName()->append(
					UString::fromASCII(".report.DIFFERS.txt")));
			echo("\n==== $test: ====\n");
			if( $report->exists() ){
				//$os->writeBytes("   report: " . $report . "\n");
				$report_new_fos = new FileOutputStream( $report_new );
				self::test($report_new_fos, $is_php5, $test);
				$report_new_fos->close();
				$t1 = new LineInputWrapper( new FileInputStream($report));
				$t2 = new LineInputWrapper( new FileInputStream($report_new));
				$ok = SimpleDiff::areEqual($t1, $t2, $os);
				$t1->close();
				$t2->close();
				if( $ok ){
					echo "notice: test passed\n";
					$report_new->delete();
					$good++;
				} else {
					echo "ERROR: differences found\n";
					$bad++;
				}
			} else {
				//$os->writeBytes("   report: " . $report . "\n");
				self::test(new FileOutputStream( $report ), $is_php5, $test);
				echo "notice: test report generated\n";
				$generated++;
			}
		}
		//self::test($os, TRUE, File::fromLocaleEncoded(""));
		
		echo "Overall tests summary: $good passed, $bad differ, $generated generated.\n";
	}

}

TestLinter::main();
