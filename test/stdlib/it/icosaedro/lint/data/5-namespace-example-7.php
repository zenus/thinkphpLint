<?php
# Accessing global classes, functions and constants from within a namespace
/*. require_module 'standard'; .*/
namespace Foo;

/*. string .*/ function strlen(/*. string .*/ $s) { return $s; }
const INI_ALL = "my new constant";
class Exception {}

if( \strlen('hi') ); // calls global function strlen
if( \INI_ALL ); // accesses global constant INI_ALL
if( new \Exception('error') ); // instantiates global class Exception

if( strlen('xx') );
if( INI_ALL );
if( new Exception() );
