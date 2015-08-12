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
 * Builds a POSTNET or PLANET barcode.
 * <center>{@img example-POSTNET.png}<p>
 * <b>POSTNET barcode generated with <tt>new POSTNET("12345-6789",
 * false)</tt>.</b></center>
 * Used by U.S. Postal Service for automated mail sorting.
 * @version $Date: 2015/03/05 10:35:29 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class POSTNET extends Barcode {
	
	
	/**
	 * Builds a POSTNET or PLANET barcode.
	 * @param string $code zip code to represent. Must be a string containing
	 * a zip code of the form DDDDD or DDDDD-DDDD.
	 * @param boolean $planet if true generates the PLANET barcode, otherwise
	 * generates POSTNET
	 */
	public function __construct($code, $planet) {
		if (preg_match("/^[0-9]{5}(-[0-9]{4})?\$/", $code) !== 1)
			throw new InvalidArgumentException("invalid ZIP: `$code'");
		$this->payload = $code;
		// bar length
		if ($planet) {
			$barlen = array(
				0 => array(1,1,2,2,2),
				1 => array(2,2,2,1,1),
				2 => array(2,2,1,2,1),
				3 => array(2,2,1,1,2),
				4 => array(2,1,2,2,1),
				5 => array(2,1,2,1,2),
				6 => array(2,1,1,2,2),
				7 => array(1,2,2,2,1),
				8 => array(1,2,2,1,2),
				9 => array(1,2,1,2,2)
			);
		} else {
			$barlen = array(
				0 => array(2,2,1,1,1),
				1 => array(1,1,1,2,2),
				2 => array(1,1,2,1,2),
				3 => array(1,1,2,2,1),
				4 => array(1,2,1,1,2),
				5 => array(1,2,1,2,1),
				6 => array(1,2,2,1,1),
				7 => array(2,1,1,1,2),
				8 => array(2,1,1,2,1),
				9 => array(2,1,2,1,1)
			);
		}
		$bars = /*. (Bar[int]) .*/ array();
		$code = (string) str_replace('-', '', $code);
		$len = strlen($code);
		// calculate checksum
		$sum = 0;
		for ($i = 0; $i < $len; ++$i) {
			$sum += (int) $code[$i];
		}
		$chkd = ($sum % 10);
		if($chkd > 0) {
			$chkd = (10 - $chkd);
		}
		$code .= $chkd;
		$this->readout = $code;
		$len = strlen($code);
		// start bar
		$bars[] = new Bar(true, 1, 2, 0);
		$bars[] = new Bar(false, 1, 2, 0);
		for ($i = 0; $i < $len; ++$i) {
			for ($j = 0; $j < 5; ++$j) {
				$h = $barlen[(int) $code[$i]][$j];
				$p = 2 - $h;
				$bars[] = new Bar(true, 1, $h, $p);
				$bars[] = new Bar(false, 1, 2, 0);
			}
		}
		// end bar
		$bars[] = new Bar(true, 1, 2, 0);
		$this->bars = $bars;
	}
	
}
