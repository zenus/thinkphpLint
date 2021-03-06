<?php
/** SNMP Functions.

See: {@link http://www.php.net/manual/en/ref.snmp.php}
@package snmp
*/


# FIXME: dummy values
define('SNMP_VALUE_LIBRARY', 1);
define('SNMP_VALUE_PLAIN', 1);
define('SNMP_VALUE_OBJECT', 1);
define('SNMP_BIT_STR', 1);
define('SNMP_OCTET_STR', 1);
define('SNMP_OPAQUE', 1);
define('SNMP_NULL', 1);
define('SNMP_OBJECT_ID', 1);
define('SNMP_IPADDRESS', 1);
define('SNMP_COUNTER', 1);
define('SNMP_UNSIGNED', 1);
define('SNMP_TIMETICKS', 1);
define('SNMP_UINTEGER', 1);
define('SNMP_INTEGER', 1);
define('SNMP_COUNTER64', 1);
define('SNMP_OID_OUTPUT_FULL', 1);
define('SNMP_OID_OUTPUT_NUMERIC', 1);

/*. string .*/ function snmpget(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. string .*/ function snmpgetnext(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. array .*/ function snmpwalk(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. array .*/ function snmprealwalk(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. bool .*/ function snmp_get_quick_print(){}
/*. void .*/ function snmp_set_quick_print(/*. int .*/ $quick_print){}
/*. void .*/ function snmp_set_enum_print(/*. int .*/ $enum_print){}
/*. void .*/ function snmp_set_oid_numeric_print(/*. int .*/ $oid_numeric_print){}
/*. int .*/ function snmpset(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id, /*. string .*/ $type, /*. mixed .*/ $value /*., args .*/){}
/*. string .*/ function snmp2_get(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. string .*/ function snmp2_getnext(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. array .*/ function snmp2_walk(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. array .*/ function snmp2_real_walk(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id /*., args .*/){}
/*. int .*/ function snmp2_set(/*. string .*/ $host, /*. string .*/ $community, /*. string .*/ $object_id, /*. string .*/ $type, /*. mixed .*/ $value /*., args .*/){}
/*. int .*/ function snmp3_get(/*. string .*/ $host, /*. string .*/ $sec_name, /*. string .*/ $sec_level, /*. string .*/ $auth_protocol, /*. string .*/ $auth_passphrase, /*. string .*/ $priv_protocol, /*. string .*/ $priv_passphrase, /*. string .*/ $object_id /*., args .*/){}
/*. int .*/ function snmp3_getnext(/*. string .*/ $host, /*. string .*/ $sec_name, /*. string .*/ $sec_level, /*. string .*/ $auth_protocol, /*. string .*/ $auth_passphrase, /*. string .*/ $priv_protocol, /*. string .*/ $priv_passphrase, /*. string .*/ $object_id /*., args .*/){}
/*. int .*/ function snmp3_walk(/*. string .*/ $host, /*. string .*/ $sec_name, /*. string .*/ $sec_level, /*. string .*/ $auth_protocol, /*. string .*/ $auth_passphrase, /*. string .*/ $priv_protocol, /*. string .*/ $priv_passphrase, /*. string .*/ $object_id /*., args .*/){}
/*. int .*/ function snmp3_real_walk(/*. string .*/ $host, /*. string .*/ $sec_name, /*. string .*/ $sec_level, /*. string .*/ $auth_protocol, /*. string .*/ $auth_passphrase, /*. string .*/ $priv_protocol, /*. string .*/ $priv_passphrase, /*. string .*/ $object_id /*., args .*/){}
/*. int .*/ function snmp3_set(/*. string .*/ $host, /*. string .*/ $sec_name, /*. string .*/ $sec_level, /*. string .*/ $auth_protocol, /*. string .*/ $auth_passphrase, /*. string .*/ $priv_protocol, /*. string .*/ $priv_passphrase, /*. string .*/ $object_id, /*. string .*/ $type, /*. mixed .*/ $value /*., args .*/){}
/*. int .*/ function snmp_set_valueretrieval(/*. int .*/ $method){}
/*. int .*/ function snmp_get_valueretrieval(){}
/*. int .*/ function snmp_read_mib(/*. string .*/ $filename){}
/*. void .*/ function snmp_set_oid_output_format(/*. int .*/ $oid_format){}
