<?php
/** Shared Memory Functions.

See: {@link http://www.php.net/manual/en/ref.shmod.php}
@package shmop
*/



/*. int   .*/ function shmop_open(/*. int .*/ $key, /*. string .*/ $flags, /*. int .*/ $mode, /*. int .*/ $size){}
/*. string.*/ function shmop_read(/*. int .*/ $shmid, /*. int .*/ $start, /*. int .*/ $count){}
/*. void  .*/ function shmop_close(/*. int .*/ $shmid){}
/*. int   .*/ function shmop_size(/*. int .*/ $shmid){}
/*. int   .*/ function shmop_write(/*. int .*/ $shmid, /*. string .*/ $data, /*. int .*/ $offset){}
/*. bool  .*/ function shmop_delete(/*. int .*/ $shmid){}
