<?php

# colliding inherited methods

interface IF1
{
	/*. void .*/ function m(/*. int .*/ $i);
}

abstract class ABS
{
	abstract /*. void .*/ function m(/*. int .*/ $i);
}

class C extends ABS implements IF1
{ }

?>
