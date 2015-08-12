<?php

require_once __DIR__ . "/../../../../../../stdlib/cast.php";

# Trick: "if(E);" has the only purpouse to raise and error that displays
# the detected type of E.

/*. mixed .*/ $m = NULL;
class MyClass {}
$obj = new MyClass();

if( cast("boolean", $m) );
if( cast("int", $m) );
if( cast("float", $m) );
if( cast("string", $m) );
if( cast("resource", $m) );
if( cast("object", $m) );
if( cast("MyClass", $m) );

if( cast("array", $m) );

if( cast("array[]", $m) );
if( cast("array[int]", $m) );
if( cast("array[string]", $m) );

if( cast("array[]MyClass", $m) );
if( cast("array[int]MyClass", $m) );
if( cast("array[string]MyClass", $m) );

if( cast("array[][]", $m) );
if( cast("array[int][]", $m) );
if( cast("array[string][]", $m) );

if( cast("array[][]int", $m) );
if( cast("array[int][]int", $m) );
if( cast("array[string][]int", $m) );

$ais = array("zero", "one");
if( cast("array[int]string", $ais) );
if( cast("array[]string", $ais) );
if( cast("array[int]int", $ais) );
if( cast("array[string]string", $ais) );
