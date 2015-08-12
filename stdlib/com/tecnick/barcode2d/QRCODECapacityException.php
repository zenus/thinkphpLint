<?php

namespace com\tecnick\barcode2d;

/*.
	require_module 'standard';
.*/


/**
 * The size of input data is greater than QR Code
 * capacity. Try to lower the error correction mode.
 */
class QRCODECapacityException extends \Exception { }
