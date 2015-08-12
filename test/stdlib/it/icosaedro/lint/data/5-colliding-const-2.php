<?php

# abstract class: colliding inherited constants

interface IF1
{
	const c = 1;
}

interface IF2
{
	const c = 1;
}

abstract class ABS implements IF1, IF2
{ }


?>
