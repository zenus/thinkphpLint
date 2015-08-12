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
 * Builds an UPC-A or UPC-E barcode.
 * <center>{@img example-UPC-E-042100005264.png}
 * <br><tt>04252614</tt>
 * <p><b>UPC-E barcode generated with <tt>new UPC("04210000526", true)</tt>.</b>
 * </center>
 * UPC-A is the Universal Product Code seen on almost all retail products in the
 * USA and Canada. It contains 12 digits including a check digit. Example
 * (spaces added for readability): <center><tt>0 42100 00526 4</tt></center>
 * where 0 is the <i>number system encoding</i>, 42100 is the <i>manufacturer
 * code</i>, 00526 is the <i>product code</i> and 4 is the check digit.
 * 
 * <p>UPC-E is the equivalent shorter version of the UPC-A symbol; it encodes the
 * number system encoding, manufacturer and product code by removing redundant
 * zero digits. Barcode readers may retrieve the original UPC-A code from this
 * shorter form. However only specific particular patterns SMMMMMPPPPPC can be
 * simplified in this way. First, the number system encoding S must be 0 or 1,
 * then the following matching rules must be attempted in the order:
 * 
 * <blockquote><pre>
 * Original UPC-A     Equivalent UPC-E
 * ---------------    ----------------
 * S MM000 00PPP C    SMMPPP0C
 * S MM100 00PPP C    SMMPPP1C
 * S MM200 00PPP C    SMMPPP2C
 * S MMM00 000PP C    SMMMPP3C
 * S MMMM0 0000P C    SMMMMP4C
 * S MMMMM 0000P C    SMMMMMPC (with P in [5,9])
 * </pre></blockquote>
 * 
 * If none matches, an equivalent shorter code does not exist. This shorter form
 * is applicable to the example above to obtain the following UPC-E code:
 * <center><tt>04252614</tt></center>
 * 
 * <p>Note that the UPC-E barcode does not contain explicitly the S and C digits,
 * as these are implied in the rest of the encoding; still, these digits might
 * be printed as readable characters just below the barcode.
 * 
 * <p>References: {@link http://www.barcodeisland.com/upce.phtml}
 * @version $Date: 2015/03/05 10:35:39 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class UPC extends Barcode {

	/**
	 * Builds an UPC-A or UPC-E barcode.
	 * @param string $payload UPC-A code with check digit (12 digits) or without
	 * (11 digits). If the check digit is present it is checked, if missing it
	 * is added to the payload.
	 * @param boolean $short If to generate the shorter UPC-E variant instead.
	 * @throws InvalidArgumentException The payload length is invalid.
	 * The payload contains non-digits. Invalid check digit. No UPC-E equivalent
	 * encoding possible.
	 */
	public function __construct($payload, $short = false) {
		$this->payload = $payload;
		if (preg_match("/^[0-9]{11,12}\$/", $payload) !== 1)
			throw new InvalidArgumentException("invalid UPC code, expected 11 or 12 digits: $payload");
		// calculate check digit
		$sum = 0;
		for ($i = 1; $i < 11; $i+=2) {
			$sum += (int) $payload[$i] + 3 * (int) $payload[$i+1];
		}
		$r = $sum % 10;
		if($r > 0)
			$r = 10 - $r;
		// add check digit if missing
		if (strlen($payload) == 11)
			$payload .= $r;
		else if ($r !== intval($payload[11]))
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
		if ($short) {
			// generate the equivalent UPC-E
			if (preg_match("/^[01]..[012]0000....\$/", $payload) === 1)
				$upce = $payload[0] . substr($payload, 1, 2) . substr($payload, 8, 3) . $payload[3] . "$r";
			else if (preg_match("/^[01]...00000...\$/", $payload) === 1)
				$upce = $payload[0] . substr($payload, 1, 3) . substr($payload, 9, 2) . "3$r";
			else if (preg_match("/^[01]....00000..\$/", $payload) === 1)
				$upce = $payload[0] . substr($payload, 1, 4) . $payload[10] . "4$r";
			else if (preg_match("/^[01].....0000[5-9].\$/", $payload) === 1)
				$upce = $payload[0] . substr($payload, 1, 5) . $payload[10] . "$r";
			else
				throw new InvalidArgumentException("no equivalent UPC-E for $payload - use UPC-A");
			//$this->readout = $upce;
			$upce_parities[0] = array(
				0=>array('B','B','B','A','A','A'),
				1=>array('B','B','A','B','A','A'),
				2=>array('B','B','A','A','B','A'),
				3=>array('B','B','A','A','A','B'),
				4=>array('B','A','B','B','A','A'),
				5=>array('B','A','A','B','B','A'),
				6=>array('B','A','A','A','B','B'),
				7=>array('B','A','B','A','B','A'),
				8=>array('B','A','B','A','A','B'),
				9=>array('B','A','A','B','A','B')
			);
			$upce_parities[1] = array(
				0=>array('A','A','A','B','B','B'),
				1=>array('A','A','B','A','B','B'),
				2=>array('A','A','B','B','A','B'),
				3=>array('A','A','B','B','B','A'),
				4=>array('A','B','A','A','B','B'),
				5=>array('A','B','B','A','A','B'),
				6=>array('A','B','B','B','A','A'),
				7=>array('A','B','A','B','A','B'),
				8=>array('A','B','A','B','B','A'),
				9=>array('A','B','B','A','B','A')
			);
			$p = $upce_parities[(int) $upce[0]][$r];
			for ($i = 0; $i < 6; ++$i) {
				$seq .= $codes[$p[$i]][$upce[$i+1]];
			}
			$seq .= '010101'; // right guard bar
			
		} else {
			// UPC-A
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
			for ($i = 0; $i < 6; ++$i) {
				$seq .= $codes[$p[$i]][$payload[$i]];
			}
			$seq .= '01010'; // center guard bar
			for ($i = 6; $i < 12; ++$i) {
				$seq .= $codes['C'][$payload[$i]];
			}
			$seq .= '101'; // right guard bar
		}
			
		$this->encodeBars($seq);
	}
	
}
