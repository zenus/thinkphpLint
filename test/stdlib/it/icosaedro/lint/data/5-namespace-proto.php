<?php

/*. require_module 'standard'; .*/
/*. require_module 'spl'; .*/

namespace proto;
###############

const MY_CONST = 123;

/*. forward \Exception function MyFunc(\Exception $e); .*/

/*. \Exception .*/ function MyFunc(/*. \Exception .*/ $e)
{
	return $e->getPrevious();
}


/*.
	forward class MyClass extends \Exception {}
.*/

class MyClass extends \Exception {}


/*.
	forward interface MyInterface {}
.*/

interface MyInterface {}


namespace proto\testing;
#######################

$n = 1 + \proto\MY_CONST;
echo "n=$n\n";

$e = \proto\MyFunc( new \Exception() );

$c = new \proto\MyClass();

$i = /*. (\proto\MyInterface) .*/ NULL;

use proto as p;

$n = 1 + p\MY_CONST;
echo "n=$n\n";

#$e = MyFunc( new \Exception() );

$c = new p\MyClass();

$i = /*. (p\MyInterface) .*/ NULL;

?>
