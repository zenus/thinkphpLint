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
 * Builds a UPC-Based Extensions barcode.
 * <center>{@img example-EANEXT-5.png}
 * <p><b>Created with <tt>new EANEXT("01239")</tt>.</b></center>
 * It may contain either 2 or 5 digits.
 * Two digits are used to indicate magazines and newspaper issue numbers.
 * Five digits are used to mark suggested retail price of books.
 * <br>Barcode scanners must return the same payload.
 * @version $Date: 2015/02/26 10:56:25 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class EANEXT extends Barcode {

	/**
	 * Builds a UPC-Based Extensions barcode.
	 * @param string $payload Data to represent, either 2 or 5 digits.
	 * @throws InvalidArgumentException Expected either 2 or 5 digits.
	 */
	public function __construct($payload) {
		if (preg_match("/^[0-9]{2}|[0-9]{5}\$/", $payload) !== 1)
			throw new InvalidArgumentException("expected either 2 or 5 digits");
		// calculate check digit
		$len = strlen($payload);
		if ($len == 2) {
			$r = (int) $payload % 4;
		} else { // 5 digits
			$r = (3 * ((int) $payload[0] + (int) $payload[2] + (int) $payload[4]))
				+ (9 * ((int) $payload[1] + (int) $payload[3]));
			$r %= 10;
		}
		//Convert digits to bars
		$codes = array(
			'A'=>array( // left odd parity
				'0'=>'0001101',
				'1'=>'0011001',
				'2'=>'0010011',
				'3'=>'0111101',
				'4'=>'0100011',
				'5'=>'0110001',
				'6'=>'0101111',
				'7'=>'0111011',
				'8'=>'0110111',
				'9'=>'0001011'),
			'B'=>array( // left even parity
				'0'=>'0100111',
				'1'=>'0110011',
				'2'=>'0011011',
				'3'=>'0100001',
				'4'=>'0011101',
				'5'=>'0111001',
				'6'=>'0000101',
				'7'=>'0010001',
				'8'=>'0001001',
				'9'=>'0010111')
		);
		$parities[2] = array(
			0=>array('A','A'),
			1=>array('A','B'),
			2=>array('B','A'),
			3=>array('B','B')
		);
		$parities[5] = array(
			0=>array('B','B','A','A','A'),
			1=>array('B','A','B','A','A'),
			2=>array('B','A','A','B','A'),
			3=>array('B','A','A','A','B'),
			4=>array('A','B','B','A','A'),
			5=>array('A','A','B','B','A'),
			6=>array('A','A','A','B','B'),
			7=>array('A','B','A','B','A'),
			8=>array('A','B','A','A','B'),
			9=>array('A','A','B','A','B')
		);
		$p = $parities[$len][$r];
		$seq = '1011'; // left guard bar
		$seq .= $codes[$p[0]][$payload[0]];
		for ($i = 1; $i < $len; ++$i) {
			$seq .= '01'; // separator
			$seq .= $codes[$p[$i]][$payload[$i]];
		}
		$this->payload = $payload;
		$this->encodeBars($seq);
	}
	
}
