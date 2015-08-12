<?php
/** Hash Functions.

See: {@link http://www.php.net/manual/en/ref.hash.php}
@package hash
*/

define('HASH_HMAC', 1);

/*. string .*/ function hash(/*. string .*/ $algo, /*. string .*/ $data, $raw_output = FALSE){}
/*. string .*/ function hash_file(/*. string .*/ $algo, /*. string .*/ $filename, $raw_output = FALSE){}
/*. string .*/ function hash_hmac(/*. string .*/ $algo, /*. string .*/ $data, /*. string .*/ $key, $raw_output = FALSE){}
/*. string .*/ function hash_hmac_file(/*. string .*/ $algo, /*. string .*/ $filename, /*. string .*/ $key, $raw_output = FALSE){}
/*. resource .*/ function hash_init(/*. string .*/ $algo, $options = 0, /*. string .*/ $key = NULL){}
/*. bool   .*/ function hash_update(/*. resource .*/ $context, /*. string .*/ $data){}
/*. int    .*/ function hash_update_stream(/*. resource .*/ $context, /*. resource .*/ $handle, $lenght = -1){}
/*. bool   .*/ function hash_update_file(/*. resource .*/ $context, /*. string .*/ $filename, /*. resource .*/ $stream_context = NULL){}
/*. string .*/ function hash_final(/*. resource .*/ $context, $raw_output = FALSE){}
/*. array  .*/ function hash_algos(){}
/*. string .*/ function md5(/*. string .*/ $str, $raw_output = FALSE){}
/*. string .*/ function md5_file(/*. string .*/ $filename, $raw_output = FALSE){}
/*. string .*/ function sha1(/*. string .*/ $str, $raw_output = FALSE){}
/*. string .*/ function sha1_file(/*. string .*/ $filename, $raw_output = FALSE){}
/*. resource .*/ function hash_copy(/*. resource .*/ $context){}
