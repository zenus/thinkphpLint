<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\utils;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\io\OutputStream;
use it\icosaedro\io\LineInputWrapper;
use it\icosaedro\io\IOException;


/**
 * Simple text differences finder. Basically, tells if two text files are
 * equal or not, but it can also report the differences.
 * The report generated is very simple and contains lines of the form:
 * <blockquote><pre>
 * &lt;123 linelinelineline
 * &gt;234 linelinelineline
 * </pre></blockquote>
 * where <code>&lt;123</code> is a line from the first source that has not been
 * found in the second, being 123 the number of that line (first being the no.
 * 1); <code>&gt;234</code> indicates a line from the second source that was not
 * found in the first file. Example of usage:
 * <blockquote><pre>
 * $fn1 = new File( UString::fromASCII("C:/home/data1.txt") );
 * $fn2 = new File( UString::fromASCII("C:/home/data2.txt") );
 * $out = new StringOutputStream();
 * $are_equal = SimpleDiff::areEqual(
 *	new LineInputWrapper( new FileInputStream($fn1) ),
 *	new LineInputWrapper( new FileInputStream($fn2) ),
 *	$out);
 * if( $are_equal )
 *	$out-&gt;writeBytes("files $fn1 and $fn2 are equal\n");
 * else
 *	$out-&gt;writeBytes("files $fn1 and $fn2 differ:\n$out");
 * </pre></blockquote>
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:37:43 $
 */
final class SimpleDiff {
	
	
	/**
	 *
	 * @var string[int]
	 */
	private $a, $b;
	
	private $ai = 0, $bi = 0;
	
	private $differ = FALSE;
	
	
	/**
	 *
	 * @var OutputStream 
	 */
	private $out;
	
	
	private function skipEqualLines()
	{
		while($this->ai < count($this->a)
		&& $this->bi < count($this->b)
		&& $this->a[$this->ai] === $this->b[$this->bi] ){
			$this->ai++;
			$this->bi++;
		}
	}
	
	
	/**
	 *
	 * @param LineInputWrapper $in1
	 * @param LineInputWrapper $in2
	 * @param OutputStream $out 
	 * @return void
	 * @throws IOException 
	 */
	private function __construct($in1, $in2, $out)
	{
		$this->a = /*.(string[int]).*/ array();
		$this->b = /*.(string[int]).*/ array();
		$this->out = $out;
		while( ($line = $in1->readLine()) !== NULL )
			$this->a[] = rtrim($line);
		while( ($line = $in2->readLine()) !== NULL )
			$this->b[] = rtrim($line);
	}
	
	
	/**
	 *
	 * @return void
	 * @throws IOException 
	 */
	private function parse()
	{
		do {
		$this->skipEqualLines();
		if( $this->ai >= count($this->a) ){
			if( $this->bi >= count($this->b) ){
				return;
			} else {
				$this->differ = TRUE;
				while($this->bi < count($this->b)){
					$this->out->writeBytes(">" . ($this->bi+1) . " " . $this->b[$this->bi] . "\n");
					$this->bi++;
				}
				return;
			}
		} else if( $this->bi >= count($this->b) ){
			$this->differ = TRUE;
			while($this->ai < count($this->a)){
				$this->out->writeBytes("<" . ($this->ai+1) . " " . $this->a[$this->ai] . "\n");
				$this->ai++;
			}
			return;
		}
		
		$this->differ = TRUE;
		
		// At least one line remaining in both left and right.
		// 
		// Search first left line that matches any of the right lines:
		// FIXME: set a limit to deep of the search
		$fml1 = count($this->a);
		$fmr1 = count($this->b);
		for($i = $this->ai; $i < count($this->a); $i++){
			for($j = $this->bi; $j < count($this->b); $j++){
				if( $this->a[$i] === $this->b[$j] ){
					$fml1 = $i;
					$fmr1 = $j;
					break;
				}
			}
			if( $fml1 < count($this->a) )
				break;
		}
		
		// Search first right line that matches any of the left lines:
		// FIXME: set a limit to deep of the search
		$fml2 = count($this->a);
		$fmr2 = count($this->b);
		for($i = $this->bi; $i < count($this->b); $i++){
			for($j = $this->ai; $j < count($this->a); $j++){
				if( $this->b[$i] === $this->a[$j] ){
					$fml2 = $j;
					$fmr2 = $i;
					break;
				}
			}
			if( $fml2 < count($this->a) )
				break;
		}
		
		// Choose the match that makes diff shorter:
		$diff1 = $fml1 - $this->ai + $fmr1 - $this->bi;
		$diff2 = $fml2 - $this->ai + $fmr2 - $this->bi;
		if( $diff1 <= $diff2 ){
			$fml = $fml1;
			$fmr = $fmr1;
		} else {
			$fml = $fml2;
			$fmr = $fmr2;
		}
		
		// Skip left non-matching block:
		while($this->ai < $fml){
			$this->out->writeBytes("<" . ($this->ai+1) . " " . $this->a[$this->ai] . "\n");
			$this->ai++;
		}
		
		// Skip right non-matching block:
		while($this->bi < $fmr){
			$this->out->writeBytes(">" . ($this->bi+1) . " " . $this->b[$this->bi] . "\n");
			$this->bi++;
		}
			
		} while(TRUE);
	}
	
	
	/**
	 * Compares two texts given as sources of lines.
	 * @param LineInputWrapper $in1 First text source to compare.
	 * @param LineInputWrapper $in2 Second text source to compare.
	 * @param OutputStream $out Destination or the report.
	 * @return boolean True if the two sources provide the same, exact text.
	 * @throws IOException 
	 */
	public static function areEqual($in1, $in2, $out)
	{
		$diff = new SimpleDiff($in1, $in2, $out);
		$diff->parse();
		return ! $diff->differ;
	}

}


//function test(/*.resource.*/ $out, /*. string .*/ $prompt, /*. string .*/ $a, /*. string .*/ $b, /*.boolean.*/$expected)
//{
//	echo $prompt;
//	$eq = SimpleDiff::areEqual(
//			new LineInputWrapper( new \it\icosaedro\io\StringInputStream($a) ),
//			new LineInputWrapper( new \it\icosaedro\io\StringInputStream($b) ),
//			$out);
//	if( $eq )
//		echo "--> are equal\n";
//	else
//		echo "--> found differences\n";
//	if( $eq !== $expected )
//		throw new \RuntimeException();
//}
//
//$out = new \it\icosaedro\io\ResourceOutputStream(STDOUT);
//
//test($out, "\n====> test 0:\n",
//	"",
//	"", TRUE);
//
//test($out, "\n====> test 1:\n",
//	"aaa",
//	"", FALSE);
//
//test($out, "\n====> test 2:\n",
//	"",
//	"aaa", FALSE);
//
//test($out, "\n====> test 3:\n",
//	"line1\nline2\nline3\nline4\nline5",
//	"line1\nline3\nline4\nline4.5\nline5",
//	FALSE);