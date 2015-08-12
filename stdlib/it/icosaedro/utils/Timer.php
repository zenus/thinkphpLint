<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\utils;

require_once __DIR__ . "/../containers/Printable.php";

use it\icosaedro\containers\Printable;

/**
	Timer with ms granularity that may be started and stopped several times
	to accumulate partial intervals. Example:
	<pre>
	use it\icosaedro\utils\Timer;
	$t = new Timer(TRUE);
	performTask();
	echo "elapsed time: $t"; # ==&gt; "elapsed time: 1 min 3.032 s"
	</pre>

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2014/02/12 23:56:32 $
*/
class Timer implements Printable {

	private /*. bool .*/ $is_running = FALSE;
	private /*. float .*/ $running_since = 0.0;
	private /*. int .*/ $counter = 0;


	/**
		Time elapsed since 1970.
		@return float Time elapsed since 1970-01-01 00:00:00.000 UTC (ms).
	*/
	static function timeMillisecondsAsFloat()
	{
		return 1000.0 * (float) microtime(true);
	}

	
	/**
		Creates a new timer.
		@param bool $start If to start the timer immediately.
		@return void
	*/
	function __construct($start = FALSE)
	{
		$this->is_running = $start;
		$this->counter = 0;
		if( $start ){
			$this->running_since = self::timeMillisecondsAsFloat();
		}
	}


	/**
		Indicated time.
		@return int Time elapsed since the start of the timer (ms).
	*/
	function elapsedMilliseconds()
	{
		if( $this->is_running )
			return $this->counter + (int) (self::timeMillisecondsAsFloat() - $this->running_since);
		else
			return $this->counter;
	}


	/**
		Indicated time.
		@return int Time elapsed since the start of the timer (s).
		The value is rounded from ms to the nearest integer number
		of seconds.
	*/
	function elapsedSeconds()
	{
		if( $this->is_running )
			return (int) (0.001 * ($this->counter + self::timeMillisecondsAsFloat() - $this->running_since + 500.0));
		else
			return (int) (0.001 * $this->counter + 500.0);
	}


	/**
		Start the timer or continue counting of the time elapsed since
		last stop.
		@return void
	*/
	function start()
	{
		if( ! $this->is_running ){
			$this->is_running = TRUE;
			$this->running_since = self::timeMillisecondsAsFloat();
		}
	}


	/**
		Stop timer. Does not reset the counter of the indicated elapsed time
		so you may restart the timer to account for multiple intervals.
		@return void
	*/
	function stop()
	{
		if( $this->is_running ){
			$this->counter += (int) (self::timeMillisecondsAsFloat() - $this->running_since + 0.5);
			$this->is_running = FALSE;
		}
	}


	/**
		Reset to zero the indicated elapsed time and stop counting.
		@return void
	*/
	function reset()
	{
		$this->is_running = FALSE;
		$this->counter = 0;
	}
	
	const
		sec = 1000,
		min = 60000,
		hour = 3600000,
		day = 86400000;
	
	/**
		Renders a time interval into a human-readable string.
		@param int $dt Time interval (ms).
		@return string Human-readable time interval, for example:
		"2 days 4 h 23 min 6.032 s".
	 */
	static function dtToHumanReadableString($dt)
	{
		$res = "";
		
		if( $dt == 0 )
			return "0.000 s";
		
		if( $dt < 0 ){
			$res .= "-";
			$dt = -$dt;
		}
		
		if( $dt >= self::day ){
			$day_no = (int) ($dt / self::day);
			$res .= " $day_no days";
			$dt -= $day_no * self::day;
		}
		
		if( $dt >= self::hour ){
			$hour_no = (int) ($dt / self::hour);
			$res .= " $hour_no h";
			$dt -= $hour_no * self::hour;
		}
		
		if( $dt >= self::min ){
			$min_no = (int) ($dt / self::min);
			$res .= " $min_no min";
			$dt -= $min_no * self::min;
		}
		
		if( $dt >= self::sec ){
			$sec_no = (int) ($dt / self::sec);
			$ms_no = $dt - $sec_no * self::sec;
			if( $ms_no == 0 )
				$res .= " $sec_no s";
			else {
				$res .= " $sec_no." . sprintf("%03d", $ms_no) . " s";
			}
		} else if( $dt > 0 ){
			$res .= " $dt ms";
		}
		
		if( strlen($res) > 0 && $res[0] === ' ' )
			$res = substr($res, 1);
		
		return $res;
	}
	
	
	/**
		Returns the current indicated elapsed time in human-readable form.
		@return string The current indicate elapsed time formatted as
		"2 days 4 h 23 min 6.032 s".
	*/
	function __toString()
	{
		return self::dtToHumanReadableString( $this->elapsedMilliseconds() );
	}

}
