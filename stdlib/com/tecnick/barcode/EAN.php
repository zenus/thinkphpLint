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
 * Builds a EAN13 or EAN-8 barcode.
 * <center>{@img example-EAN13.png}
 * <p><b>EAN-13 barcode for ISBN 80-131-47149 generated with
 * <tt>new EAN("9780131471498")</tt>.</b></center>
 * 
 * This class may generate one of the following barcodes:
 * <ul>
 * <li>EAN13: European Article Numbering international retail product code,
 * 12 data digits + 1 check digit.</li>
 * <li>EAN8: European Article Numbering international retail product code,
 * 7 data digits + 1 check digit.</li>
 * </ul>
 * 
 * @version $Date: 2015/03/05 10:34:56 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class EAN extends Barcode {
	
	/** @access private */
	const
		EAN13 = 13,
		EAN8 = 8;


	/**
	 * Builds a EAN8 or EAN13 barcode. Also add the check digit, if missing.
	 * @param string $payload EAN-8 or EAN-13 code with or without check digit.
	 * 13 digits for EAN-13 (12 without check digit), or 8 digits for EAN-8
	 * (7 without check digit).
	 * @throws InvalidArgumentException The code must contains one or more digits.
	 * Invalid type parameter. Wrong check digit. Invalid length of the payload.
	 */
	public function __construct($payload)
	{
		if (preg_match("/^[0-9]+\$/", $payload) !== 1)
			throw new InvalidArgumentException("empty payload or no-digits found: $payload");
		
		// detect type from len
		$payload_len = strlen($payload);
		if ($payload_len == 7 || $payload_len == 8) {
			$type = self::EAN8;
			$data_len = 7;
		} else if ($payload_len == 12 || $payload_len == 13) {
			$type = self::EAN13;
			$data_len = 12;
		} else {
			throw new InvalidArgumentException("invalid length of the payload");
		}
		
		// calculate check digit
		$sum_a = 0;
		for ($i = 1; $i < $data_len; $i+=2) {
			$sum_a += (int) $payload[$i];
		}
		if ($type == self::EAN13) {
			$sum_a *= 3;
		}
		$sum_b = 0;
		for ($i = 0; $i < $data_len; $i+=2) {
			$sum_b += (int) $payload[$i];
		}
		if ($type == self::EAN8) {
			$sum_b *= 3;
		}
		$chk = ($sum_a + $sum_b) % 10;
		if($chk > 0) {
			$chk = 10 - $chk;
		}
		
		// add check digit if missing
		if ($payload_len < $data_len + 1)
			$payload .= $chk;
		else if ($chk !== intval($payload[$data_len]))
			throw new InvalidArgumentException("invalid check digit");
		
		$this->payload = $this->readout = $payload;
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
				'9'=>'0010111'),
			'C'=>array( // right
				'0'=>'1110010',
				'1'=>'1100110',
				'2'=>'1101100',
				'3'=>'1000010',
				'4'=>'1011100',
				'5'=>'1001110',
				'6'=>'1010000',
				'7'=>'1000100',
				'8'=>'1001000',
				'9'=>'1110100')
		);
		$seq = '101'; // left guard bar
		$this->readout = $payload;
		$half_len = $type == self::EAN8? 4 : 7;
		if ($type == self::EAN8) {
			for ($i = 0; $i < $half_len; ++$i) {
				$seq .= $codes['A'][$payload[$i]];
			}
		} else {
			$parities = array(
				'0'=>array('A','A','A','A','A','A'),
				'1'=>array('A','A','B','A','B','B'),
				'2'=>array('A','A','B','B','A','B'),
				'3'=>array('A','A','B','B','B','A'),
				'4'=>array('A','B','A','A','B','B'),
				'5'=>array('A','B','B','A','A','B'),
				'6'=>array('A','B','B','B','A','A'),
				'7'=>array('A','B','A','B','A','B'),
				'8'=>array('A','B','A','B','B','A'),
				'9'=>array('A','B','B','A','B','A')
			);
			$p = $parities[$payload[0]];
			for ($i = 1; $i < $half_len; ++$i) {
				$seq .= $codes[$p[$i-1]][$payload[$i]];
			}
		}
		$seq .= '01010'; // center guard bar
		for ($i = $half_len; $i <= $data_len; ++$i) {
			$seq .= $codes['C'][$payload[$i]];
		}
		$seq .= '101'; // right guard bar
		$this->encodeBars($seq);
	}
	
}
