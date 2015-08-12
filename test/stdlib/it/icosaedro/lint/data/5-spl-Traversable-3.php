<?php

	/*. require_module 'spl'; .*/

	interface Test1 extends Iterator, IteratorAggregate {
	}

	class Test2 implements Test1 {
	}

