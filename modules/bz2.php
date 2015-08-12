<?php
/**
Bzip2 Compression Functions.

See: {@link http://www.php.net/manual/en/ref.bzip2.php}
@package bz2
*/


/*. int   .*/ function bzerrno(/*. resource .*/ $bz){}
/*. string.*/ function bzerrstr(/*. resource .*/ $bz){}
/*. array .*/ function bzerror(/*. resource .*/ $bz){}
/*. mixed .*/ function bzcompress(/*. string .*/ $source, $blocksize = 4, $workfactor = 0){}

/**
 * WARNING. Looking at the C source code, on error this function may return
 * several types of results and values, including: FALSE, int.
 */
/*. mixed .*/ function bzdecompress(/*. string .*/ $source, $small = FALSE){}

/*. string.*/ function bzread(/*. int .*/ $bz /*., args .*/){}
/*. resource .*/ function bzopen(/*. string .*/ $filename, /*. string .*/ $mode ){}
