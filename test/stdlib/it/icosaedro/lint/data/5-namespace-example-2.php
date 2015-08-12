<?php
namespace MyProject {

const CONNECT_OK = 1;
class Connection { /* ... */ }
function connect() { /* ... */  }
}

namespace AnotherProject {

const CONNECT_OK = 1;
class Connection { /* ... */ }
function connect() { /* ... */  }
}

echo MyProject\CONNECT_OK;
echo MyProject\connect_ok;
echo myproject\CONNECT_OK;

$o1 = new MyProject\Connection();
$o1 = new MyProject\connection();
$o1 = new myproject\Connection();

MyProject\connect();
echo AnotherProject\CONNECT_OK;
$o2 = new AnotherProject\Connection();
