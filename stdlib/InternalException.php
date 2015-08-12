<?php

/*. require_module 'standard'; .*/

/**
 * Reports a non-fatal PHP core engine error, for example division by zero or
 * accessing an array at undefined key. See the package {@link ./errors.html}
 * for a detailed description of the meaning of this exception.
 */
/*. unchecked .*/ class InternalException extends ErrorException {}
