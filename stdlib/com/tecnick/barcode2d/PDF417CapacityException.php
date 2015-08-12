<?php

namespace com\tecnick\barcode2d;

/*. require_module 'standard'; .*/

/**
 * PDF417 barcode maximum capacity exceeded. Try to reduce the error
 * correction level, or reduce the amount of data, or try to restrict the data
 * to digits only or alphanumeric only.
 */
class PDF417CapacityException extends \Exception {}
