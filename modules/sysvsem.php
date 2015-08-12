<?php
/** System V Semaphore Support.

See: {@link http://www.php.net/manual/en/ref.sem.php}
@package sysvsem
*/


/*. resource .*/ function sem_get(/*. int .*/ $key, $max_acquire = 1, $perm = 0666, $auto_release = 1){}
/*. bool .*/ function sem_acquire(/*. resource .*/ $id){}
/*. bool .*/ function sem_release(/*. resource .*/ $id){}
/*. bool .*/ function sem_remove(/*. resource .*/ $id){}
