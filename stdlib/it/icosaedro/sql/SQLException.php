<?php

/*. require_module 'standard'; .*/

namespace it\icosaedro\sql;

/**
	Any exception thrown by SQL related classes.
	Includes: failed connection to the remote data base,
	missing data base,
	invalid login, 
	invalid SQL syntax,
	invalid arguments provided,
	accessing data from an already closed connection,
	invalid usage of the provided API.

	@author Umberto Salsi <salsi@icosaedro.it>
	@version $Date: 2012/01/22 19:34:11 $
*/
class SQLException extends \Exception {}
