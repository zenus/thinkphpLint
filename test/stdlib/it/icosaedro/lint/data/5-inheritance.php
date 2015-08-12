<?php

/*
 * Abstract class may redefine inherited abstract method
 * (PHP bug 43200).
 */

interface IF1 {
	function m();
}

interface IF2 {
	function m();
}

abstract class AC1 implements IF1, IF2 {
	abstract function m();
}


/**
 * Private [non-]static method may re-define inherited private [non-]static
 * method (PHP bug 61761). 
 */

class C1 {
	private function m(){}
	private static function sm(){}
}

class C2 extends C1 {
	private function m(/*. int .*/ $x){}
	private static function sm(/*. int .*/ $x){}
}

