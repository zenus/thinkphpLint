<?php

/*.
	require_module 'standard';
	require_module 'spl';
.*/

namespace it\icosaedro\utils;

require_once __DIR__ . "/../containers/Printable.php";

use RuntimeException;
use it\icosaedro\containers\Printable;

/**
	Evaluate basic statistical parameters about a set of samples.
	The initial set of samples is empty. Example:
	<pre>
	$s = new Statistics1D();
	$s-&gt;put(1.0);
	$s-&gt;put(2.0);
	$s-&gt;put(3.0);
	echo $s;
	</pre>
	displays:
	<pre>
	n=3 min=1 max=3 mean=2 dev=1
	</pre>

	Remember that if you generate an uniform distribution of values in the
	range [a,b], then the expected mean is (a+b)/2 and the standard deviation
	is (b-a)/sqrt(12) = 0.29 * (b-a).

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2015/02/06 10:20:37 $
*/
class Statistics1D implements Printable {

	private $n = 0;
	private $min_x = 0.0;
	private $max_x = 0.0;
	private $sum_x = 0.0;
	private $sum_x_2 = 0.0;


	/**
		Add a value to the set.
		@param float $x Value to add.
		@return void
	*/
	function put($x)
	{
		if( $this->n == 0 ){
			$this->min_x = $x;
			$this->max_x = $x;
		} else {
			if( $x < $this->min_x )
				$this->min_x = $x;
			if( $x > $this->max_x )
				$this->max_x = $x;
		}
		$this->sum_x += $x;
		$this->sum_x_2 += $x * $x;
		$this->n++;
	}


	/**
		Returns the number of samples accounted so far.
		@return int Number of sample accounted so far.
	*/
	function count()
	{
		return $this->n;
	}


	/**
		Minimum value accounted so far.
		@return float Minimum value accounted so far.
		@throws RuntimeException No samples accounted.
	*/
	function min()
	{
		if( $this->n == 0 )
			throw new RuntimeException("no sample");
		return $this->min_x;
	}


	/**
		Maximum value accounted so far.
		@return float Maximum value accounted so far.
		@throws RuntimeException No sample accounted.
	*/
	function max()
	{
		if( $this->n == 0 )
			throw new RuntimeException("no sample");
		return $this->max_x;
	}


	/**
		Mean value of the samples accounted so far.
		@return float Mean value of the samples accounted so far.
		@throws RuntimeException No sample accounted.
	*/
	function mean()
	{
		if( $this->n == 0 )
			throw new RuntimeException("no sample");
		return $this->sum_x / $this->n;
	}


	/**
		Standard deviation of the samples accounted so far.
		@return float Standard deviation of the samples accounted so far.
		Uses the tuned formula for small sets sqrt(.../(n-1)).
		@throws RuntimeException Less than 2 samples available.
	*/
	function deviation()
	{
		if( $this->n < 2 )
			throw new RuntimeException("less than 2 samples available");
		$m = $this->mean();
		return sqrt( ($this->sum_x_2 + $this->n * $m * $m
			- 2.0 * $m * $this->sum_x) / ($this->n - 1) );
	}


	/**
		Summary of the statistical parameters of the samples
		accounted so far.
		@return string One-line message including number of samples,
		min, max, mean and deviation.
	*/
	function __toString()
	{
		if( $this->n == 0 )
			return "n=0";

		$res = "n=" . $this->n . " min=" . $this->min()
			. " max=" . $this->max_x . " mean=" . $this->mean();
		if( $this->n >= 2 )
			$res .= " dev=" . $this->deviation();

		return $res;
	}

}
