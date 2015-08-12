<?php
/** System V Messages Support.

See: {@link http://www.php.net/manual/en/ref.sem.php}
@package sysvmsg
*/


# FIXME: dummy values
define('MSG_IPC_NOWAIT', 1);
define('MSG_NOERROR', 1);
define('MSG_EXCEPT', 1);

/*. bool .*/ function msg_set_queue(/*. resource .*/ $queue, /*. array .*/ $data){}
/*. array .*/ function msg_stat_queue(/*. resource .*/ $queue){}
/*. resource .*/ function msg_get_queue(/*. int .*/ $key, $perms = 0666){}
/*. bool .*/ function msg_remove_queue(/*. resource .*/ $queue){}

/*. if_php_ver_4 .*/

/*. mixed .*/ function msg_receive(
	/*. resource .*/ $queue,
	/*. int .*/ $desiredmsgtype,
	/*. return int .*/ & $msgtype,
	/*. int .*/ $maxsize,
	/*. return mixed .*/ & $message,
	$unserialize = TRUE,
	$flags = 0
	/*. , args .*/){}
/*. bool .*/ function msg_send(/*. resource .*/ $queue, /*. int .*/ $msgtype, /*. mixed .*/ $message, $serialize = TRUE, $blocking = TRUE /*. , args .*/){}

/*. else .*/

/*. mixed .*/ function msg_receive(
	/*. resource .*/ $queue,
	/*. int .*/ $desiredmsgtype,
	/*. return int .*/ & $msgtype,
	/*. int .*/ $maxsize,
	/*. return mixed .*/ & $message,
	$unserialize = TRUE,
	$flags = 0,
	/*. return .*/ & $errorcode = 0){}
/*. bool .*/ function msg_send(/*. resource .*/ $queue, /*. int .*/ $msgtype, /*. mixed .*/ $message, $serialize = TRUE, $blocking = TRUE, /*. return .*/ & $errorcode = 0){}

/*. end_if_php_ver .*/
