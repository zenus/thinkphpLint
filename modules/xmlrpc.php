<?php
/** XML-RPC Functions.

See: {@link http://www.php.net/manual/en/ref.xmlrpc.php}
@package xmlrpc
*/


/*. string .*/ function xmlrpc_encode_request(/*. string .*/ $method, /*. mixed .*/ $params){}
/*. string .*/ function xmlrpc_encode(/*. mixed .*/ $value){}
/*. array .*/ function xmlrpc_decode_request(/*. string .*/ $xml, /*. string .*/ &$method /*., args .*/){}
/*. array .*/ function xmlrpc_decode(/*. string .*/ $xml, $encoding = "iso-8859-1"){}
/*. resource .*/ function xmlrpc_server_create(){}
/*. int .*/ function xmlrpc_server_destroy(/*. resource .*/ $server){}
/*. bool .*/ function xmlrpc_server_register_method(/*. resource .*/ $server, /*. string .*/ $method_name, /*. string .*/ $function_){}
/*. bool .*/ function xmlrpc_server_register_introspection_callback(/*. resource .*/ $server, /*. string .*/ $function_){}
/*. mixed .*/ function xmlrpc_server_call_method(/*. resource .*/ $server, /*. string .*/ $xml, /*. mixed .*/ $user_data /*., args .*/){}
/*. int .*/ function xmlrpc_server_add_introspection_data(/*. resource .*/ $server, /*. array .*/ $desc){}
/*. array .*/ function xmlrpc_parse_method_descriptions(/*. string .*/ $xml){}
/*. bool .*/ function xmlrpc_set_type(/*. string .*/ $value, /*. string .*/ $type){}
/*. string .*/ function xmlrpc_get_type(/*. mixed .*/ $value){}
/*. bool .*/ function xmlrpc_is_fault(/*. array .*/ $arg){}
