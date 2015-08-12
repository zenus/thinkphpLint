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
 * Builds a MSI barcode.
 * <center>{@img example-MSI.png}<p>
 * <b>Generated with <tt>new MSI("01239", true)</tt>.</b></center>
 * Variation of Plessey code, with similar applications.
 * Contains digits (0 to 9) and encodes the data only in the width of bars.
 * <p>The resulting readout from a scanner set in basic mode may contain the
 * added checksum.
 * @version $Date: 2015/02/27 13:00:49 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class MSI extends Barcode {
	
	/**
	 * Builds a MSI barcode.
	 * @param string $code code to represent, only digits allowed.
	 * @param boolean $add_checksum if true adds a checksum to the code (modulo 11)
	 */
	public function __construct($code, $add_checksum)
	{
		if (preg_match("/^[0-9]+\$/", $code) !== 1)
			throw new InvalidArgumentException("only digits allowed: `$code'");
		$chr['0'] = '100100100100';
		$chr['1'] = '100100100110';
		$chr['2'] = '100100110100';
		$chr['3'] = '100100110110';
		$chr['4'] = '100110100100';
		$chr['5'] = '100110100110';
		$chr['6'] = '100110110100';
		$chr['7'] = '100110110110';
		$chr['8'] = '110100100100';
		$chr['9'] = '110100100110';
		// for checksum computation only:
		$chr['A'] = '110100110100';
		$chr['B'] = '110100110110';
		$chr['C'] = '110110100100';
		$chr['D'] = '110110100110';
		$chr['E'] = '110110110100';
		$chr['F'] = '110110110110';
		if ($add_checksum) {
			// add checksum
			$clen = strlen($code);
			$p = 2;
			$check = 0;
			for ($i = ($clen - 1); $i >= 0; --$i) {
				$check += (int)hexdec($code[$i]) * $p;
				++$p;
				if ($p > 7) {
					$p = 2;
				}
			}
			$check %= 11;
			if ($check > 0) {
				$check = 11 - $check;
			}
			$code .= $check;
		}
		$this->readout = $code;
		$seq = '110'; // left guard
		$clen = strlen($code);
		for ($i = 0; $i < $clen; ++$i) {
			$digit = $code[$i];
			$seq .= $chr[$digit];
		}
		$seq .= '1001'; // right guard
		$this->payload = $code;
		$this->encodeBars($seq);
	}
	
}
