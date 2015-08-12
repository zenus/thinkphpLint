<?php

	/*. require_module 'spl'; .*/

	class Test implements Iterator, IteratorAggregate {

		/*. mixed .*/ function current() { return NULL; }
		/*. mixed .*/ function key() { return NULL; }
		/*. void  .*/ function next() {}
		/*. void  .*/ function rewind() {}
		/*. bool  .*/ function valid() { return FALSE; }

		/*. Traversable .*/ function getIterator() { return NULL; }

	}

