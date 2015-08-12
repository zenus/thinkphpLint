<?php

class C1 {}
class C2 extends C1 {}
class C3 extends C2 {}

function f(C2 $obj){}
f(new C1());  # ERR
f(new C3());

abstract class Abstr {
	abstract public function f1(C2 $c);
}

class Work1 extends Abstr {
	public function f1(C1 $c){} # ERR
}

class Work3 extends Abstr {
	public function f1(C3 $c){} # ERR
}

function foo(Abstr $obj) {
	$X = new C2();
	$obj->f1($X);
}

foo(new Work1);
foo(new Work3);

?>
