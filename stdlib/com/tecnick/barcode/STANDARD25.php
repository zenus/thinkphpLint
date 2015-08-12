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
 * Builds a Standard Two of Five barcode. Used in airline ticket marking,
 * photofinishing. Contains digits (0 to 9) and encodes the data only in the
 * width of bars.
 * @version $Date: 2015/02/26 10:58:39 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class STANDARD25 extends Barcode {

	
	/**
	 * Checksum for standard 2 of 5 barcodes.
	 * @param string $code code to process (digits only).
	 * @return int checksum.
	 */
	private function checksum_s25($code) {
		$len = strlen($code);
		$sum = 0;
		for ($i = 0; $i < $len; $i+=2) {
			$sum += (int) $code[$i];
		}
		$sum *= 3;
		for ($i = 1; $i < $len; $i+=2) {
			$sum += (int) $code[$i];
		}
		$r = $sum % 10;
		if($r > 0) {
			$r = (10 - $r);
		}
		return $r;
	}
	

	/**
	 * Builds a Standard 2 of 5 barcode.
	 * @param string $code code to represent, only digits allowed.
	 * @param boolean $checksum if true add a checksum to the code
	 * @throws InvalidArgumentException Invalid character.
	 */
	public function __construct($code, $checksum)
	{
		if (preg_match("/^[0-9]+\$/", $code) !== 1)
			throw new InvalidArgumentException("only digits allowed: $code");
		$chr['0'] = '10101110111010';
		$chr['1'] = '11101010101110';
		$chr['2'] = '10111010101110';
		$chr['3'] = '11101110101010';
		$chr['4'] = '10101110101110';
		$chr['5'] = '11101011101010';
		$chr['6'] = '10111011101010';
		$chr['7'] = '10101011101110';
		$chr['8'] = '10101110111010';
		$chr['9'] = '10111010111010';
		if ($checksum) {
			// add checksum
			$code .= $this->checksum_s25($code);
		}
		if((strlen($code) % 2) != 0) {
			// add leading zero if code-length is odd
			$code = '0'.$code;
		}
		$seq = '11011010';
		$clen = strlen($code);
		for ($i = 0; $i < $clen; ++$i) {
			$digit = $code[$i];
			$seq .= $chr[$digit];
		}
		$seq .= '1101011';
		$this->payload = $code;
		$this->encodeBars($seq);
	}
	
}
