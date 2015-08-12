<?php

# Detect circular reference in regular classes using proto:
/*. forward class A {} .*/
class B extends A {}
class A extends B {}

# Detect circular reference in regular class:
class C extends C {}

# Detect circular reference in proto classes:
/*. forward class D extends D {} .*/
