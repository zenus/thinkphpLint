<?php

# BUG FIX: comma before args must be sym_x_comma:
/*. void .*/ function bug_comma_before_args($i=0 , /*. args .*/)
{ }
