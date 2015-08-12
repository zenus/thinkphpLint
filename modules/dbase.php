<?php
/**
dBase Functions.

See: {@link http://www.php.net/manual/en/ref.dbase.php}
@package dbase
*/


/*. int .*/ function dbase_open(/*. string .*/ $name, /*. int .*/ $mode){}
/*. bool .*/ function dbase_close(/*. int .*/ $identifier){}
/*. int .*/ function dbase_numrecords(/*. int .*/ $identifier){}
/*. int .*/ function dbase_numfields(/*. int .*/ $identifier){}
/*. bool .*/ function dbase_pack(/*. int .*/ $identifier){}
/*. bool .*/ function dbase_add_record(/*. int .*/ $identifier, /*. array .*/ $data){}
/*. bool .*/ function dbase_replace_record(/*. int .*/ $identifier, /*. array .*/ $data, /*. int .*/ $recnum){}
/*. bool .*/ function dbase_delete_record(/*. int .*/ $identifier, /*. int .*/ $record){}
/*. array .*/ function dbase_get_record(/*. int .*/ $identifier, /*. int .*/ $record){}
/*. array .*/ function dbase_get_record_with_names(/*. int .*/ $identifier, /*. int .*/ $record){}
/*. bool .*/ function dbase_create(/*. string .*/ $filename, /*. array .*/ $fields){}
/*. array .*/ function dbase_get_header_info(/*. int .*/ $database_handle){}
