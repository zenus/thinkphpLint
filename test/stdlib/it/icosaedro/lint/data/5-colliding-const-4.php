<?php

# class: colliding inherited constants

interface IF1
{
	const c = 1;
}

abstract class ABS
{
	const c = 1;
}

class C extends ABS implements IF1
{ }


class C2 extends ABS
{
	const c = 0;
}

class C4
{
	const c = 1;
}

class C5
{
	const c = "";
}


?>
