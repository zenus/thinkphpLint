<?php

/*.
	require_module 'standard';
	require_module 'spl';
.*/


class Ex extends Exception {}
class Ex2 extends Exception {}
class Ex3 extends Exception {}
/*. unchecked .*/ class UEx extends Exception {}
/*. unchecked .*/ class UEx2 extends Exception {}

#### Proto of func: implementation cannot throw new exceptions:

/*. forward void function ThrowingFunc()
	throws Ex, UEx; .*/

/**
 * This function throws several exceptions.
 * @return void
 * @throws Ex   Decription for checked exception Ex.
 * @throws UEx  Decription for unchecked exception UEx.
 */
function ThrowingFunc()
{
	if( time() > 0 ) throw new Ex();
	if( time() > 0 ) throw new Ex2();
	if( time() > 0 ) throw new UEx();
	if( time() > 0 ) throw new UEx2();
}


#### Proto of method: implementation cannot throw new checked exceptions:

class ThrowingClassWithForward
{

	/*. forward void function ThrowingFunc()
		throws Ex, UEx; .*/

	/*. void .*/ function ThrowingFunc()
	{
		if( time() > 0 ) throw new Ex2();
		if( time() > 0 ) throw new Ex3();
		if( time() > 0 ) throw new UEx2();
	}

}


#### Proto of uncheked exception: implementation must be unchecked too:
#### Good:
/*. forward unchecked class ForwUncheckedEx extends Exception {} .*/
/*. unchecked .*/ class ForwUncheckedEx extends Exception {}
#### Bad:
/*. forward unchecked class ForwUncheckedEx2 extends Exception {} .*/
class ForwUncheckedEx2 extends Exception {}


#### Implementation of guessed function can throw unchecked exceptions:

guessed_func();
function guessed_func(){ throw new UEx(); }

guessed_func2();
function guessed_func2() /*. throws UEx .*/ {}
