<?php
/*. require_module 'standard'; .*/

/**
 * Unimplemented functions and methods may throw this exception.
 * @author salsi
 */
/*. unchecked .*/ class UnimplementedException extends Exception {
	
	function __construct($message = "") {
		parent::__construct($message);
	}
	
}
