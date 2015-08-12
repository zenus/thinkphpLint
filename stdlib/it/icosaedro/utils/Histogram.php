<?php

namespace it\icosaedro\utils;

require_once __DIR__ . "/Statistics1D.php";

use it\icosaedro\utils\Statistics1D;

/**
 * Generates an histogram of a set of samples. No fancy graphics, only
 * ASCII-art: this is only a tool to test other algorithms, for example
 * random number generators, hash key generators, etc.  Example:
 * 
 * <pre>
 * # Test random generator in [0,100[:
 * $h = new Histogram(0.0, 100.0, 10);
 * srand(0);
 * for($i = 0; $i &lt; 1000; $i++){
 *     $h-&gt;put(rand() % 100);
 * }
 * echo $h;
 * </pre>
 * displays:
 * <pre>
 *            n=1000 min=0 max=99 mean=50.295 dev=29.084289072812
 *       &lt; 0 || 0 (0.0%)
 *      &lt; 10 |****************************| 88 (8.8%)
 *      &lt; 20 |*****************************| 94 (9.4%)
 *      &lt; 30 |****************************************| 127 (12.7%)
 *      &lt; 40 |********************************| 101 (10.1%)
 *      &lt; 50 |**************************| 83 (8.3%)
 *      &lt; 60 |********************************| 103 (10.3%)
 *      &lt; 70 |***************************| 87 (8.7%)
 *      &lt; 80 |******************************| 95 (9.5%)
 *      &lt; 90 |**********************************| 108 (10.8%)
 *     &lt; 100 |************************************| 114 (11.4%)
 *    &gt;= 100 || 0 (0.0%)
 * </pre>
 * @version $Date: 2014/02/23 18:13:53 $
 * @author Umberto Salsi <salsi@icosaedro.it>
 */
class Histogram extends Statistics1D {

	private $min = 0.0, $max = 0.0;
	private $intervals = 0, $n = 0;
	private /*. int[int] .*/ $h;


/**
 * Creates a new histogram. The set of data is initially empty.
 * @param float $min Beginning of the range.
 * @param float $max End of the range.
 * @param int $intervals Number of discrete intervals in the range
 * [$min,$max]. Every interval is then $dx=($max-$min)/$intervals wide.
 * The first interval holds values in the range [$min,$min+$dx[; the second
 * interval holds values in the range [$min+$dx,$min+2*$dx[ and so on. Two
 * more intervals are added, one at the beginning of the histogram that
 * holds values &lt; $min, and another at the end of the histogram that holds
 * values &ge; $max.
 * @return void
 */
function __construct($min, $max, $intervals)
{
	$this->min = $min;
	$this->max = $max;
	$this->intervals = $intervals;
	$this->n = 0;
	$this->h[] = 0;
	$this->h[] = 0;
	for($i = $intervals - 1; $i >= 0; $i--)
		$this->h[] = 0;
}


/**
 * Adds a sample to the set.
 * @param float $v Sample to add.
 * @return void
 */
function put($v)
{
	parent::put($v);
	if( $v < $this->min )
		$this->h[0]++;
	else if( $v >= $this->max )
		$this->h[$this->intervals + 1]++;
	else {
		$i = (int) (($v - $this->min) / ($this->max - $this->min) * $this->intervals) + 1;
		$this->h[$i]++;
	}
	$this->n++;
}


/**
 * @param string $label Label for this bar.
 * @param int $len Length of the bar (characters).
 * @param int $n No. of samples in this interval.
 * @return string
 */
private function bar($label, $len, $n)
{
	if( $this->n > 0 )
		$percent = 100.0 * $n / $this->n;
	else
		$percent = 0.0;
	return sprintf("%20s", $label) . " |" . str_repeat("*", $len) . "| $n"
	. sprintf(" (%2.1f%%)\n", $percent);
}


/**
 * Returns a textual messages with the histogram drawn as ASCII-art.
 * Includes the basic statistical data: number of samples, mean, standard
 * deviation.  The total width of the histogram fits the standard width of
 * a terminal screen, that is 80 columns.
 * @return string
 */
function __toString()
{
	$w = 40; // max width bar
	$m = 0;
	for($i = count($this->h) - 1; $i >= 0; $i--)
		if( $this->h[$i] > $m )
			$m = $this->h[$i];
	if( $m == 0 )
		$m = 1;
	$k = ($w + 0.5) / $m;
	$res = "";
	$res .= $this->bar("< ".$this->min, (int) ($k * $this->h[0]), $this->h[0]);
	for($i = 1; $i < count($this->h) - 1; $i++){
		$x = $this->min + $i * ($this->max - $this->min) / $this->intervals;
		$res .= $this->bar("< $x", (int) ($k * $this->h[$i]), $this->h[$i]);
	}
	$i = $this->intervals + 1;
	$res .= $this->bar(">= ".$this->max, (int) ($k * $this->h[$i]), $this->h[$i]);
	return str_repeat(" ", 22) . parent::__toString() . "\n" . $res;
}

}
