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
use RuntimeException;

/**
 * Builds a RMS4CC or CBC or KIX barcode. 
 * RMS4CC is the Royal Mail 4-state Customer Code.
 * CBC is the Customer Bar Code.
 * KIX is the Klant index - Customer index.
 * RM4SCC is the name of the barcode symbology used by the Royal Mail for
 * its Cleanmail service.
 * @version $Date: 2015/02/26 10:58:28 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class RMS4CC extends Barcode {
	
	/**
	 * Builds a RMS4CC or CBC or KIX barcode.
	 * @param string $code code to print, digits and upper case letters allowed
	 * @param boolean $kix if true prints the KIX variation (doesn't use the
	 * start and end symbols, and the checksum) - in this case the house number
	 * must be sufficed with an X and placed at the end of the code.
	 */
	public function __construct($code, $kix)
	{
		if (preg_match("/^[0-9A-Z]+\$/", $code) !== 1)
			throw new InvalidArgumentException("only upper case letters and digits allowed");
		$this->payload = $code;
		$notkix = !$kix;
		// bar mode
		// 1 = pos 1, length 2
		// 2 = pos 1, length 3
		// 3 = pos 2, length 1
		// 4 = pos 2, length 2
		$barmode = array(
			'0' => array(3,3,2,2),
			'1' => array(3,4,1,2),
			'2' => array(3,4,2,1),
			'3' => array(4,3,1,2),
			'4' => array(4,3,2,1),
			'5' => array(4,4,1,1),
			'6' => array(3,1,4,2),
			'7' => array(3,2,3,2),
			'8' => array(3,2,4,1),
			'9' => array(4,1,3,2),
			'A' => array(4,1,4,1),
			'B' => array(4,2,3,1),
			'C' => array(3,1,2,4),
			'D' => array(3,2,1,4),
			'E' => array(3,2,2,3),
			'F' => array(4,1,1,4),
			'G' => array(4,1,2,3),
			'H' => array(4,2,1,3),
			'I' => array(1,3,4,2),
			'J' => array(1,4,3,2),
			'K' => array(1,4,4,1),
			'L' => array(2,3,3,2),
			'M' => array(2,3,4,1),
			'N' => array(2,4,3,1),
			'O' => array(1,3,2,4),
			'P' => array(1,4,1,4),
			'Q' => array(1,4,2,3),
			'R' => array(2,3,1,4),
			'S' => array(2,3,2,3),
			'T' => array(2,4,1,3),
			'U' => array(1,1,4,4),
			'V' => array(1,2,3,4),
			'W' => array(1,2,4,3),
			'X' => array(2,1,3,4),
			'Y' => array(2,1,4,3),
			'Z' => array(2,2,3,3)
		);
		$len = strlen($code);
		$bars = /*. (Bar[int]) .*/ array();
		if ($notkix) {
			// table for checksum calculation (row,col)
			$checktable = array(
				'0' => array(1,1),
				'1' => array(1,2),
				'2' => array(1,3),
				'3' => array(1,4),
				'4' => array(1,5),
				'5' => array(1,0),
				'6' => array(2,1),
				'7' => array(2,2),
				'8' => array(2,3),
				'9' => array(2,4),
				'A' => array(2,5),
				'B' => array(2,0),
				'C' => array(3,1),
				'D' => array(3,2),
				'E' => array(3,3),
				'F' => array(3,4),
				'G' => array(3,5),
				'H' => array(3,0),
				'I' => array(4,1),
				'J' => array(4,2),
				'K' => array(4,3),
				'L' => array(4,4),
				'M' => array(4,5),
				'N' => array(4,0),
				'O' => array(5,1),
				'P' => array(5,2),
				'Q' => array(5,3),
				'R' => array(5,4),
				'S' => array(5,5),
				'T' => array(5,0),
				'U' => array(0,1),
				'V' => array(0,2),
				'W' => array(0,3),
				'X' => array(0,4),
				'Y' => array(0,5),
				'Z' => array(0,0)
			);
			$row = 0;
			$col = 0;
			for ($i = 0; $i < $len; ++$i) {
				$row += $checktable[$code[$i]][0];
				$col += $checktable[$code[$i]][1];
			}
			$row %= 6;
			$col %= 6;
			$chk = array_keys($checktable, array($row,$col));
			$code .= (string) $chk[0];
			++$len;
		}
		$this->readout = $code;
		if ($notkix) {
			// start bar
			$bars[] = new Bar(true, 1, 2, 0);
			$bars[] = new Bar(false, 1, 2, 0);
		}
		for ($i = 0; $i < $len; ++$i) {
			for ($j = 0; $j < 4; ++$j) {
				switch ($barmode[$code[$i]][$j]) {
					case 1: {
						$p = 0;
						$h = 2;
						break;
					}
					case 2: {
						$p = 0;
						$h = 3;
						break;
					}
					case 3: {
						$p = 1;
						$h = 1;
						break;
					}
					case 4: {
						$p = 1;
						$h = 2;
						break;
					}
					default: throw new RuntimeException();
				}
				$bars[] = new Bar(true, 1, $h, $p);
				$bars[] = new Bar(false, 1, 2, 0);
			}
		}
		if ($notkix) {
			// stop bar
			$bars[] = new Bar(true, 1, 3, 0);
		}
		$this->bars = $bars;
	}
}
