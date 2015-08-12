<?php

/*. require_module 'standard'; .*/

/**
	Exception thrown if the magic function cast(T,V) failed the test
	because the passed value V does not match the expected type T.
	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/02/09 10:09:45 $
*/
/*. unchecked .*/ class CastException extends Exception {}
