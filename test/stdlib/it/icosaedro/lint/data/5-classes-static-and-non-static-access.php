<?php

class C {
static function s(){}
function ns(){}
function m(){ C::ns(); }
static function sm(){ C::ns(); } # ERR
}

$o = new C();
$o->s();
$o->ns();
$o->m();
C::s();
C::ns(); # ERR
