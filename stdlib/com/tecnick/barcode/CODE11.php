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
 * Builds a CODE11 barcode.
 * 
 * <center>{@img example-CODE11.png}
 * <br><b>Generated with <tt>new CODE11("123-456-789")</tt>.</b></center>
 * 
 * It may encode only digits and the minus sign, for
 * example <tt>999-9999</tt>. Includes a check code. Used primarily for labeling
 * telecommunications equipment.
 * <p>References: {@link http://www.barcodeisland.com/code11.phtml}
 * @version $Date: 2015/02/26 10:48:59 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class CODE11 extends Barcode {
	
	/**
	 * Builds a CODE11 barcode.
	 * @param string $payload One or more digits and minus sign to encode.
	 * @throws InvalidArgumentException Empty payload or non-digits found.
	 */
	public function __construct($payload)
	{
		if (preg_match("/^[-0-9]+\$/", $payload) !== 1)
			throw new InvalidArgumentException("empty payload or non-digits found");
		// For each symbol, widths of each bar starting from black.
		// Last digit encodes the inter-symbol space; the last useless space
		// will be removed at the end with unset().
		$chr = array(
			'0' => '111121',
			'1' => '211121',
			'2' => '121121',
			'3' => '221111',
			'4' => '112121',
			'5' => '212111',
			'6' => '122111',
			'7' => '111221',
			'8' => '211211',
			'9' => '211111',
			'-' => '112111',
			'S' => '112211'
		);
		$this->payload = $this->readout = $payload;
		$len = strlen($payload);
		// calculate check digit C
		$p = 1;
		$check = 0;
		for ($i = $len - 1; $i >= 0; --$i) {
			$digit = $payload[$i];
			if ($digit === '-') {
				$dval = 10;
			} else {
				$dval = intval($digit);
			}
			$check += ($dval * $p);
			++$p;
			if ($p > 10) {
				$p = 1;
			}
		}
		$check %= 11;
		$payload .= $check == 10? '-' : "$check";
		if ($len > 10) {
			// calculate check digit K
			$p = 1;
			$check = 0;
			for ($i = $len; $i >= 0; --$i) {
				$digit = $payload[$i];
				if ($digit === '-') {
					$dval = 10;
				} else {
					$dval = intval($digit);
				}
				$check += ($dval * $p);
				++$p;
				if ($p > 9) {
					$p = 1;
				}
			}
			$check %= 11;
			$payload .= $check;
			++$len;
		}
		$payload = 'S'.$payload.'S';
		$len += 3;
		$bars = /*. (Bar[int]) .*/ array();
		for ($i = 0; $i < $len; ++$i) {
			$seq = $chr[$payload[$i]];
			for ($j = 0; $j < 6; ++$j) {
				$bars[] = new Bar(($j % 2) == 0, (int) $seq[$j], 1, 0);
			}
		}
		unset($bars[count($bars) - 1]); // remove last useless space
		$this->bars = $bars;
	}
	
}
