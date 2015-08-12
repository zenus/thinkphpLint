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
 * Builds a Interleaved Two of Five (ITF) barcode.
 * <center>{@img example-INTERLEAVED25.png}
 * <p><b>Example of INTERLEAVED25 barcode.</b></center>
 * Compact numeric code, widely used in industry, air cargo.
 * <p>Contains digits (0 to 9) and encodes the data in the width of both bars
 * and spaces.
 * <p>A module 10 checksum may be added to the payload (optional) and the
 * resulting code must contain an even number of digits, so a further leading
 * zero might be silently added to pad the result to a even number of digits.
 * <p>The resulting readout from a scanner set in basic mode may contain a
 * additional zero, the original payload and the added checksum.
 * @version $Date: 2015/03/05 10:35:09 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class INTERLEAVED25 extends Barcode {

	
	/**
	 * Checksum for standard 2 of 5 barcodes (same code as for S25).
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
	 * Builds an Interleaved Two of Five barcode.
	 * @param string $code code to represent. Only digits allowed. If the number
	 * of digits is odd, adds a leading zero.
	 * @param boolean $add_checksum if true adds a checksum to the code
	 * @throws InvalidArgumentException Empty code, or non-digits present.
	 */
	public function __construct($code, $add_checksum)
	{
		if (preg_match("/^[0-9]+\$/", $code) !== 1)
			throw new InvalidArgumentException("only digits, A and Z allowed");
		$chr['0'] = '11221';
		$chr['1'] = '21112';
		$chr['2'] = '12112';
		$chr['3'] = '22111';
		$chr['4'] = '11212';
		$chr['5'] = '21211';
		$chr['6'] = '12211';
		$chr['7'] = '11122';
		$chr['8'] = '21121';
		$chr['9'] = '12121';
		$chr['A'] = '11';
		$chr['Z'] = '21';
		$this->payload = $code;
		if ($add_checksum) {
			// add checksum
			$code .= $this->checksum_s25($code);
		}
		if((strlen($code) % 2) != 0) {
			// add leading zero if code-length is odd
			$code = '0'.$code;
		}
		$this->readout = $code;
		// add start and stop codes
		$code = 'AA'.$code.'ZA';
		$bararray = /*. (Bar[int]) .*/ array();
		$clen = strlen($code);
		for ($i = 0; $i < $clen; $i = ($i + 2)) {
			$char_bar = $code[$i];
			$char_space = $code[$i+1];
			// create a bar-space sequence
			$seq = '';
			$chrlen = strlen($chr[$char_bar]);
			for ($s = 0; $s < $chrlen; $s++){
				$seq .= $chr[$char_bar][$s];
				if ( $s < strlen($chr[$char_space]) )
					$seq .= $chr[$char_space][$s];
			}
			$seqlen = strlen($seq);
			for ($j = 0; $j < $seqlen; ++$j) {
				$bararray[] = new Bar(($j % 2) == 0, (int) $seq[$j], 1, 0);
			}
		}
		$this->bars = $bararray;
	}
	
}
