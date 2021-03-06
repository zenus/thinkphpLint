<?php
/** Mohawk Software Session Handler Functions.

See: {@link http://www.php.net/manual/en/ref.msession.php}
@package msession
*/


/*. bool .*/ function msession_connect(/*. string .*/ $host, /*. string .*/ $port){}
/*. void .*/ function msession_disconnect(){}
/*. int .*/ function msession_count(){}
/*. bool .*/ function msession_create(/*. string .*/ $session){}
/*. bool .*/ function msession_destroy(/*. string .*/ $name){}
/*. int .*/ function msession_lock(/*. string .*/ $name){}
/*. int .*/ function msession_ctl(/*. string .*/ $name){}
/*. int .*/ function msession_unlock(/*. string .*/ $session, /*. int .*/ $key){}
/*. bool .*/ function msession_set(/*. string .*/ $session, /*. string .*/ $name, /*. string .*/ $value){}
/*. string .*/ function msession_get(/*. string .*/ $session, /*. string .*/ $name, /*. string .*/ $default_value){}
/*. string .*/ function msession_uniq(/*. int .*/ $num_chars){}
/*. string .*/ function msession_randstr(/*. int .*/ $num_chars){}
/*. array .*/ function msession_find(/*. string .*/ $name, /*. string .*/ $value){}
/*. array .*/ function msession_list(){}
/*. array .*/ function msession_get_array(/*. string .*/ $session){}
/*. bool .*/ function msession_set_array(/*. string .*/ $session, /*. array .*/ $tuples){}
/*. array .*/ function msession_listvar(/*. string .*/ $name){}
/*. int .*/ function msession_timeout(/*. string .*/ $session /*., args .*/){}
/*. string .*/ function msession_inc(/*. string .*/ $session, /*. string .*/ $name){}
/*. string .*/ function msession_get_data(/*. string .*/ $session){}
/*. bool .*/ function msession_set_data(/*. string .*/ $session, /*. string .*/ $value){}
/*. string .*/ function msession_plugin(/*. string .*/ $session, /*. string .*/ $val /*., args .*/){}
/*. string .*/ function msession_call(/*. string .*/ $fn_name /*., args .*/){}
/*. string .*/ function msession_exec(/*. string .*/ $cmdline){}
/*. bool .*/ function msession_ping(){}
