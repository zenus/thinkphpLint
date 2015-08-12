<?php

# class: colliding inherited constants

interface IF1
{
	const c = 1;
}

interface IF2
{
	const c = 1;
}

class C implements IF1, IF2
{ }


?>
