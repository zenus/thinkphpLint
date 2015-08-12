<?php

/*. require_module 'standard'; .*/

error_reporting(E_ALL | E_STRICT);

class C1 {}
class C2 extends C1 {}
class C3 extends C2 {}


class W2 {
	function m(C2 $obj){}
}

class W1 extends W2 {
	function m(C1 $obj){}
}

class W3 extends W2 {
	function m(C3 $obj){}
}


?>
