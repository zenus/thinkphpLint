<?php

# interface: colliding inherited constants

interface IF1
{
	const c = 1;
	const c2 = 1;
	const c3 = 1;
}

interface IF2
{
	const c = 1; // collision
	const c2 = 1; // collision
}

interface IF3 extends IF1, IF2
{ }


?>
