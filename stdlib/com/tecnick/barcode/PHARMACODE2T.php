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
 * Builds a Pharmacode two-track barcode.
 * <center>{@img example-PHARMACODE2T.png}
 * <p><b>Generated with <tt>new PHARMACODE2T("01239")</tt>.</b></center>
 * @version $Date: 2015/02/27 13:00:49 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class PHARMACODE2T extends Barcode {
	
	/**
	 * Builds a Pharmacode two-track barcode.
	 * @param string $code code to represent, up to 8 digits in the range
	 * [4,64570080].
	 */
	public function __construct($code)
	{
		if (preg_match("/^[0-9]{1,8}\$/", $code) !== 1)
			throw new InvalidArgumentException("expected up to 8 digits: $code");
		$this->payload = $code;
		$seq = '';
		$n = intval($code);
		$this->readout = "$n";
		if (!(4 <= $n && $n <= 64570080) )
			throw new InvalidArgumentException("code out of the range: $n");
		do {
			switch ($n % 3) {
				case 0: {
					$seq .= '3';
					$n = (int) (($n - 3) / 3);
					break;
				}
				case 1: {
					$seq .= '1';
					$n = (int) (($n - 1) / 3);
					break;
				}
				case 2: {
					$seq .= '2';
					$n = (int) (($n - 2) / 3);
					break;
				}
				default: throw new RuntimeException();
			}
		} while($n != 0);
		$seq = strrev($seq);
		$bars = /*. (Bar[int]) .*/ array();
		$len = strlen($seq);
		for ($i = 0; $i < $len; ++$i) {
			switch ($seq[$i]) {
				case '1': {
					$p = 1;
					$h = 1;
					break;
				}
				case '2': {
					$p = 0;
					$h = 1;
					break;
				}
				case '3': {
					$p = 0;
					$h = 2;
					break;
				}
				default: throw new RuntimeException();
			}
			$bars[] = new Bar(true, 1, $h, $p);
			$bars[] = new Bar(false, 1, 2, 0);
		}
		unset($bars[count($bars) - 1]);
		$this->bars = $bars;
	}
}
