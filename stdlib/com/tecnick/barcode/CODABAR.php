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
 * Builds a CODABAR barcode.
 * 
 * <center>{@img example-CODABAR.png}
 * <p><b>Generated with <tt>new CODABAR("A31117013206375B")</tt></b></center>
 * 
 * Older code often used in library systems, sometimes in blood banks.
 * Only the following 20 characters are allowed:
 * <pre>
 * 0 1 2 3 4 5 6 7 8 9 - $ : / . + A B C D
 * </pre>
 * 
 * <p>The alphabetic characters A, B, C, D are normally used to mark the beginning
 * and end of the barcode and do not appear in the body of a CODABAR payload.
 * There are then 16 possible combinations of start and stop characters; the
 * combination chosen may be used to distinguish different applications.
 * For example, the library bar code illustrated begins with A and ends with B.
 * FedEx tracking number barcodes, on the other hand, begin with C and end with D.
 * 
 * <p>This class allows to put any sequence of the characters listed above in
 * the generated barcode, with or without start and stop markers. Possible
 * restrictions are left to the requirements of the specific application.
 * 
 * <p>Some standards that use CODABAR define a check digit, but the algorithm is
 * not universal. This implementation does not calculate any checksum.
 * 
 * <p>The resulting readout from a barcode scanner should match the payload.
 * 
 * <p>Reference: {@link http://en.wikipedia.org/wiki/Codabar}
 * 
 * @version $Date: 2015/03/05 10:34:32 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class CODABAR extends Barcode {
	
	/**
	 * Builds a CODABAR barcode.
	 * @param string $payload Code to represent.
	 * @throws InvalidArgumentException Empty payload or invalid characters.
	 */
	public function __construct($payload) {
		if (preg_match("@^[-0-9\$:/.+ABCD]+\$@", $payload) !== 1)
			throw new InvalidArgumentException("empty payload or invalid characters: $payload");
		$chr = array(
			'0' => '11111221',
			'1' => '11112211',
			'2' => '11121121',
			'3' => '22111111',
			'4' => '11211211',
			'5' => '21111211',
			'6' => '12111121',
			'7' => '12112111',
			'8' => '12211111',
			'9' => '21121111',
			'-' => '11122111',
			'$' => '11221111',
			':' => '21112121',
			'/' => '21211121',
			'.' => '21212111',
			'+' => '11222221',
			'A' => '11221211',
			'B' => '12121121',
			'C' => '11121221',
			'D' => '11122211'
		);
		$this->payload = $this->readout = $payload;
		$bars = /*. (Bar[int]) .*/ array();
		$len = strlen($payload);
		for ($i = 0; $i < $len; ++$i) {
			$seq = $chr[$payload[$i]];
			for ($j = 0; $j < 8; ++$j)
				$bars[] = new Bar(($j % 2) == 0, (int) $seq[$j], 1, 0);
		}
		$this->bars = $bars;
	}
	
}
