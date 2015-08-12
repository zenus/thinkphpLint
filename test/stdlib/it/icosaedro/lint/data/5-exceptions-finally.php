<?php
/*. require_module 'standard'; .*/

function ex() /*. throws Exception .*/{}

function f1() {
	try { ex(); }
	finally { $v = 1; }
	echo $v; // $v is defined
}


function f2() {
	try { ex(); }
	finally { return; }
	echo ""; // unreachable
}

function f3() {
	try { ex(); $v = 1; return; }
	catch(Exception $e){ $v = 1; }
	//finally { return; }
	echo $v;
}

