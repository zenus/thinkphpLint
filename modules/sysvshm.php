<?php
/** System V Shared Memory support.

See: {@link http://www.php.net/manual/en/ref.sem.php}
@package sysvshm
*/



/**
	If the $memsize parameter is not provided, it defaults to the
	sysvshm.init_mem in the php.ini, otherwise 10000 bytes.
*/
/*. int .*/ function shm_attach(/*. int .*/ $key, $memsize = 10000, $perm = 0666){}
/*. bool .*/ function shm_detach(/*. int .*/ $shm_identifier){}
/*. bool .*/ function shm_remove(/*. int .*/ $shm_identifier){}
/*. bool .*/ function shm_put_var(/*. int .*/ $shm_identifier, /*. int .*/ $variable_key, /*. mixed .*/ $variable){}
/*. mixed .*/ function shm_get_var(/*. int .*/ $id, /*. int .*/ $variable_key){}
/*. bool .*/ function shm_remove_var(/*. int .*/ $id, /*. int .*/ $variable_key){}
