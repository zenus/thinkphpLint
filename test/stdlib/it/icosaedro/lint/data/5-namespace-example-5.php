<?php
namespace Foo\Bar\subnamespace;

const FOO = 1;
function foo() {}
class foo
{
    static function staticmethod() {}
}

namespace test_package;

echo \Foo\Bar\subnamespace\FOO;
\Foo\Bar\subnamespace\foo();
new \Foo\Bar\subnamespace\foo;
\Foo\Bar\subnamespace\foo::staticmethod();
