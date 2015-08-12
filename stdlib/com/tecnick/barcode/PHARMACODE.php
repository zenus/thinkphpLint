<?php
//============================================================+
// File name   : tcpdf_barcodes_1d.php (extract of)
// Version     : 1.0.027
// Begin       : 2008-06-09
// Last Update : 2014-10-20
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with TCPDF.  If not, see <http://www.gnu.org/licenses/>.
//
// See LICENSE.TXT file for more information.
//============================================================+

namespace com\tecnick\barcode;

/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
.*/

require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;

/**
 * Builds a Pharmacode barcode.
 * <center>{@img example-PHARMACODE.png}
 * <p><b>Generated with <tt>new PHARMACODE("01239")</tt>.</b></center>
 * @version $Date: 2015/02/27 13:00:49 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class PHARMACODE extends Barcode {

	/**
	 * Builds a Pharmacode barcode.
	 * @param string $code code to represent, up to 6 digits in the range
	 * [3,131070].
	 */
	public function __construct($code) {
		if (preg_match("/^[0-9]{1,6}\$/", $code) !== 1)
			throw new InvalidArgumentException("expected up to 6 digits: $code");
		$seq = '';
		$n = (int) $code;
		if ( !(3 <= $n && $n <= 131070) )
			throw new InvalidArgumentException("code out of the range: $code");
		while ($n > 0) {
			if (($n % 2) == 0) {
				$seq .= '11100';
				$n -= 2;
			} else {
				$seq .= '100';
				$n -= 1;
			}
			$n >>= 1;
		}
		$seq = substr($seq, 0, -2);
		$seq = strrev($seq);
		$this->payload = $this->readout = $code;
		$this->encodeBars($seq);
	}
}
