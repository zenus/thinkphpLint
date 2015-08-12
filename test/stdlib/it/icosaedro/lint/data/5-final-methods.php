<?php

# interface methods (and then, constructors too) cannot be `final':

interface aa {
	final /*. void .*/ function __construct();
	final /*. void .*/ function m();
}

# the same for interface proto:

/*. forward
interface aa2 {
	final void function __construct();
	final void function m();
}
.*/


# abstract methods (and the, constructors too) cannot be `final':

abstract class bb {
	abstract final /*. void .*/ function __construct();
	abstract final /*. void .*/ function m();
}

# the same for abstract class proto:

/*. forward
abstract class bb2 {
	abstract final void function __construct();
	abstract final void function m();
}
.*/


# concrete methods (and then, constructors too) can be `final':

class cc {
	final /*. void .*/ function __construct(){}
	final /*. void .*/ function m(){}
}

# the same for concrete methods:

/*. forward
class cc2 {
	final void function __construct();
	final void function m();
}
.*/

?>
