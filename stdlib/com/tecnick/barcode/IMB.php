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
	require_module 'bcmath';
.*/

require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;

/**
 * IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200.
 * 
 * <center>{@img ./example-IMB.png}
 * <p>Generated with <tt>new IMB("11222333333444444444-55555")</tt>.</center>
 * 
 * Intelligent Mail barcode is a 65-bar code for use on mail in the United
 * States.
 * The fields are described as follows:
 * <ul>
 * <li>The <b>Barcode Identifier</b> shall be assigned by USPS to encode the
 * presort identification that is currently printed in human readable form
 * on the optional endorsement line (OEL) as well as for future USPS use.
 * This shall be two digits, with the second digit in the range of 0–4.
 * The allowable encoding ranges shall be 00–04, 10–14, 20–24, 30–34, 40–44,
 * 50–54, 60–64, 70–74, 80–84, and 90–94.</li>
 * <li>The <b>Service Type Identifier</b> shall be assigned by USPS for any
 * combination of services requested on the mailpiece. The allowable
 * encoding range shall be 000–999. Each 3-digit value shall correspond to
 * a particular mail class with a particular combination of service(s).
 * Each service program, such as OneCode Confirm and OneCode ACS, shall
 * provide the list of Service Type Identifier values.</li>
 * <li>The <b>Mailer or Customer Identifier</b> shall be assigned by USPS as a
 * unique, 6 or 9 digit number that identifies a business entity.
 * The allowable encoding range for the 6 digit Mailer ID shall be 000000-899999,
 * while the allowable encoding range for the 9 digit Mailer ID shall be
 * 900000000-999999999.</li>
 * <li>The <b>Serial or Sequence Number</b> shall be assigned by the mailer for
 * uniquely identifying and tracking mailpieces. The allowable encoding
 * range shall be 000000000–999999999 when used with a 6 digit Mailer ID
 * and 000000-999999 when used with a 9 digit Mailer ID.</li>
 * <li>The <b>Delivery
 * Point ZIP</b> Code shall be assigned by the mailer for routing the mailpiece.
 * This shall replace POSTNET for routing the mailpiece to its final delivery
 * point. The length may be 0, 5, 9, or 11 digits. The allowable encoding
 * ranges shall be no ZIP Code, 00000–99999,  000000000–999999999, and
 * 00000000000–99999999999.</li>
 * </ul>
 * 
 * <p>References: {@link http://en.wikipedia.org/wiki/Intelligent_Mail_barcode}
 * 
 * @version $Date: 2015/02/26 10:56:37 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class IMB extends Barcode {
	
	/**
	 * @var int[int]
	 */
	private static $table2of13;
	
	/**
	 * @var int[int]
	 */
	private static $table5of13;
	

	/**
	 * Convert large natural number to hexadecimal representation.
	 * (requires PHP bcmath extension).
	 * @param string $number Natural number to convert specified as a string,
	 * composed of one or more digits.
	 * @return string hexadecimal representation
	 */
	private static function dec_to_hex($number) {
		$hex = "";
		do {
			$hex = bcmod($number, '16') . $hex;
			$number = bcdiv($number, '16', 0);
		} while($number !== '0');
		return $hex;
	}
	
	
	/**
	 * Convert large hexadecimal number to decimal representation.
	 * @param string $hex hexadecimal number
	 * @return string decimal representation
	 */
	private static function hex_to_dec($hex) {
		$dec = '0';
		$len = strlen($hex);
		for($i = 0; $i < $len; $i++)
			$dec = bcadd(bcmul($dec, '16'), (string) (int) hexdec($hex[$i]));
		return $dec;
	}
	

	/**
	 * Returns the Frame Check Sequence.
	 * @param string[int] $code_arr hexadecimal values (13 bytes holding 102
	 * bits right justified).
	 * @return int 11 bits Frame Check Sequence.
	 */
	private static function imb_crc11fcs($code_arr) {
		$genpoly = 0x0F35; // generator polynomial
		$fcs = 0x07FF; // Frame Check Sequence
		// do most significant byte skipping the 2 most significant bits
		$data = (int) hexdec($code_arr[0]) << 5;
		for ($bit = 2; $bit < 8; ++$bit) {
			if ((($fcs ^ $data) & 0x400) != 0) {
				$fcs = ($fcs << 1) ^ $genpoly;
			} else {
				$fcs = ($fcs << 1);
			}
			$fcs &= 0x7FF;
			$data <<= 1;
		}
		// do rest of bytes
		for ($byte = 1; $byte < 13; ++$byte) {
			$data = (int) hexdec($code_arr[$byte]) << 3;
			for ($bit = 0; $bit < 8; ++$bit) {
				if ((($fcs ^ $data) & 0x400) != 0) {
					$fcs = ($fcs << 1) ^ $genpoly;
				} else {
					$fcs = ($fcs << 1);
				}
				$fcs &= 0x7FF;
				$data <<= 1;
			}
		}
		return $fcs;
	}
	
	
	/**
	 * Reverse unsigned short value
	 * @param int $num value to reversr
	 * @return int reversed value
	 */
	private static function imb_reverse_us($num) {
		$rev = 0;
		for ($i = 0; $i < 16; ++$i) {
			$rev <<= 1;
			$rev |= ($num & 1);
			$num >>= 1;
		}
		return $rev;
	}
	

	/**
	 * generate Nof13 tables used for Intelligent Mail Barcode
	 * @param int $n type of table: 2 for 2of13 table, 5 for 5of13table
	 * @param int $size size of table (78 for n=2 and 1287 for n=5)
	 * @return int[int] requested table
	 */
	private static function imb_tables($n, $size) {
		$table = /*. (int[int]) .*/ array();
		$lli = 0; // LUT lower index
		$lui = $size - 1; // LUT upper index
		for ($count = 0; $count < 8192; ++$count) {
			$bit_count = 0;
			for ($bit_index = 0; $bit_index < 13; ++$bit_index) {
				$bit_count += intval(($count & (1 << $bit_index)) != 0);
			}
			// if we don't have the right number of bits on, go on to the next value
			if ($bit_count == $n) {
				$reverse = (self::imb_reverse_us($count) >> 3);
				// if the reverse is less than count, we have already visited this pair before
				if ($reverse >= $count) {
					// If count is symmetric, place it at the first free slot from the end of the list.
					// Otherwise, place it at the first free slot from the beginning of the list AND place $reverse ath the next free slot from the beginning of the list
					if ($reverse == $count) {
						$table[$lui] = $count;
						--$lui;
					} else {
						$table[$lli] = $count;
						++$lli;
						$table[$lli] = $reverse;
						++$lli;
					}
				}
			}
		}
		return $table;
	}
	

	/**
	 * Builds an Intelligent Mail Barcode.
	 * @param string $code Code to print 20 digits, possibly followed by minus
	 * and ZIP.
	 * @throws InvalidArgumentException Invalid syntax for the code.
	 */
	public function __construct($code) {
		if ( preg_match("/^[0-9]{20}(-[0-9]{5,11})?\$/", $code) !== 1 )
			throw new InvalidArgumentException("expected 20 digits possibly followed by minus and ZIP: $code");
		$this->payload = $this->readout = $code;
		$asc_chr = array(4,0,2,6,3,5,1,9,8,7,1,2,0,6,4,8,2,9,5,3,0,1,3,7,4,6,8,9,2,0,5,1,9,4,3,8,6,7,1,2,4,3,9,5,7,8,3,0,2,1,4,0,9,1,7,0,2,4,6,3,7,1,9,5,8);
		$dsc_chr = array(7,1,9,5,8,0,2,4,6,3,5,8,9,7,3,0,6,1,7,4,6,8,9,2,5,1,7,5,4,3,8,7,6,0,2,5,4,9,3,0,1,6,8,2,0,4,5,9,6,7,5,2,6,3,8,5,1,9,8,7,4,0,2,6,3);
		$asc_pos = array(3,0,8,11,1,12,8,11,10,6,4,12,2,7,9,6,7,9,2,8,4,0,12,7,10,9,0,7,10,5,7,9,6,8,2,12,1,4,2,0,1,5,4,6,12,1,0,9,4,7,5,10,2,6,9,11,2,12,6,7,5,11,0,3,2);
		$dsc_pos = array(2,10,12,5,9,1,5,4,3,9,11,5,10,1,6,3,4,1,10,0,2,11,8,6,1,12,3,8,6,4,4,11,0,6,1,9,11,5,3,7,3,10,7,11,8,2,10,3,5,8,0,3,12,11,8,4,5,1,3,0,7,12,9,8,10);
		$code_arr = explode('-', $code);
		$tracking_number = $code_arr[0];
		if (isset($code_arr[1])) {
			$routing_code = $code_arr[1];
		} else {
			$routing_code = '';
		}
		// Conversion of Routing Code (ZIP).
		switch (strlen($routing_code)) {
			case 0:
				$binary_code = '0';
				break;
			case 5:
				$binary_code = bcadd($routing_code, '1');
				break;
			case 9:
				$binary_code = bcadd($routing_code, '100001');
				break;
			case 11:
				$binary_code = bcadd($routing_code, '1000100001');
				break;
			default:
				throw new InvalidArgumentException("invalid length for the routing code: $routing_code");
		}
		$binary_code = bcmul($binary_code, '10');
		$binary_code = bcadd($binary_code, $tracking_number[0]);
		$binary_code = bcmul($binary_code, '5');
		$binary_code = bcadd($binary_code, $tracking_number[1]);
		$binary_code .= substr($tracking_number, 2, 18);
		// convert to hexadecimal
		$binary_code = self::dec_to_hex($binary_code);
		// pad to get 13 bytes
		$binary_code = str_pad($binary_code, 26, '0', STR_PAD_LEFT);
		// convert string to array of bytes
		$s = chunk_split($binary_code, 2, "\r");
		$s = substr($s, 0, -1);
		$binary_code_arr = explode("\r", $s);
		// calculate frame check sequence
		$fcs = self::imb_crc11fcs($binary_code_arr);
		// exclude first 2 bits from first byte
		$first_byte = sprintf('%2s', dechex(((int) hexdec($binary_code_arr[0]) << 2) >> 2));
		$binary_code_102bit = $first_byte.substr($binary_code, 2);
		// convert binary data to codewords
		$data = self::hex_to_dec($binary_code_102bit);
		$codewords[0] = (int) bcmod($data, '636') * 2;
		$data = bcdiv($data, '636');
		for ($i = 1; $i < 9; ++$i) {
			$codewords[$i] = (int) bcmod($data, '1365');
			$data = bcdiv($data, '1365');
		}
		$codewords[9] = (int) $data;
		if (($fcs >> 10) == 1) {
			$codewords[9] += 659;
		}
		if( self::$table2of13 === NULL ){
			// lazy init of the lookup tables saves 30% time next run
			self::$table2of13 = self::imb_tables(2, 78);
			self::$table5of13 = self::imb_tables(5, 1287);
		}
		$table2of13 = self::$table2of13;
		$table5of13 = self::$table5of13;
		// convert codewords to characters
		$characters = /*. (int[int]) .*/ array();
		$bitmask = 512;
		foreach($codewords as $val) {
			if ($val <= 1286) {
				$chrcode = $table5of13[$val];
			} else {
				$chrcode = $table2of13[($val - 1287)];
			}
			if (($fcs & $bitmask) > 0) {
				// bitwise invert
				$chrcode = ((~$chrcode) & 8191);
			}
			$characters[] = $chrcode;
			$bitmask >>= 2;
		}
		$characters = cast("int[int]", array_reverse($characters));
		// build bars
		$bararray = /*. (Bar[int]) .*/ array();
		for ($i = 0; $i < 65; ++$i) {
			$asc = (($characters[$asc_chr[$i]] & (1 << $asc_pos[$i])) > 0);
			$dsc = (($characters[$dsc_chr[$i]] & (1 << $dsc_pos[$i])) > 0);
			if ($asc and $dsc) {
				// full bar (F)
				$p = 0;
				$h = 3;
			} elseif ($asc) {
				// ascender (A)
				$p = 0;
				$h = 2;
			} elseif ($dsc) {
				// descender (D)
				$p = 1;
				$h = 2;
			} else {
				// tracker (T)
				$p = 1;
				$h = 1;
			}
			$bararray[] = new Bar(true, 1, $h, $p);
			$bararray[] = new Bar(false, 1, 2, 0);
		}
		unset($bararray[count($bararray) - 1]);
		$this->bars = $bararray;
	}
	
}
