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
 * Builds a CODE 39 barcode with optional extension and checksum.
 * <center>{@img example-CODE39.png}
 * <p><b>Example of CODE39 barcode.</b></center>
 * 
 * <p>The <b>basic encoding</b> mode supports only a subset of 43 symbols from
 * the ASCII charset including digits 0-9, capital letters A-B, space and the
 * following punctuation:
 * <pre>- . $ / + % *</pre>
 * 
 * <p>An <b>extended mode</b> is also available where the full ASCII charset
 * is supported, from code 0 up to 127 at the cost a smaller code density
 * - non basic characters take two bytes. However, there is no way for the
 * reader of the barcode to distinguish if the basic mode or the extended mode
 * had been used, so writer and reader must agree which mode to use.
 * 
 * <p>A modulo 43 checksum may be added as last characters from the allowed set.
 * There is no way to establish from a given barcode if a checksum has or has
 * not been added, so writer and reader must agree here too.
 * 
 * <p>The readout from a barcode scanner set in basic mode should contain the
 * extended sequences and the checksum.
 * 
 * <p>Standards: ANSI MH10.8M-1983 - USD-3 - 3 of 9.
 * @version $Date: 2015/03/05 10:34:45 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class CODE39 extends Barcode {

	/**
	 * Encode a string to be used for CODE 39 Extended mode.
	 * @param string $code code to represent.
	 * @return string encoded string.
	 * @throws InvalidArgumentException
	 */
	private function encode_code39_ext($code)
	{
		$encode = array(
			chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
			chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
			chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => '$K',
			chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O',
			chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S',
			chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W',
			chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A',
			chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E',
			chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C',
			chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G',
			chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
			chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O',
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
			chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F',
			chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J',
			chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K',
			chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O',
			chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C',
			chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G',
			chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K',
			chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O',
			chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S',
			chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W',
			chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P',
			chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T');
		$code_ext = '';
		$clen = strlen($code);
		for ($i = 0 ; $i < $clen; ++$i) {
			if (ord($code[$i]) > 127)
				throw new InvalidArgumentException("invalid character");
			$code_ext .= $encode[$code[$i]];
		}
		return $code_ext;
	}
	

	/**
	 * Calculate CODE 39 checksum (modulo 43).
	 * @param string $code code to represent.
	 * @return string char checksum.
	 */
	private function checksum_code39($code) {
		$chars = array(
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%');
		$sum = 0;
		$clen = strlen($code);
		for ($i = 0 ; $i < $clen; ++$i) {
			// FIXME: use strpos() instead and check result
			$k = array_keys($chars, $code[$i]);
			$sum += (int) $k[0];
		}
		$j = ($sum % 43);
		return $chars[$j];
	}
	
	
	/**
	 * Builds a CODE 39 barcode.
	 * @param string $payload Data to represent.
	 * @param boolean $extended If true uses the extended mode (C39E, C39E+).
	 * @param boolean $checksum If true adds a CODE39 checksum to the result
	 * (C39+, C39E+).
	 * @throws InvalidArgumentException
	 */
	public function __construct($payload, $extended=false, $checksum=false)
	{
		if ( $extended ){
			if (preg_match("/^[\\x00-\\x7f]+\$/", $payload) !== 1)
				throw new InvalidArgumentException("empty payload or invalid characters found");
		} else {
			if (preg_match("@^[-0-9A-Z. $/+%*]+\$@", $payload) !== 1)
				throw new InvalidArgumentException("empty payload or non-ASCII characters found");
		}
		$chr['0'] = '111331311';
		$chr['1'] = '311311113';
		$chr['2'] = '113311113';
		$chr['3'] = '313311111';
		$chr['4'] = '111331113';
		$chr['5'] = '311331111';
		$chr['6'] = '113331111';
		$chr['7'] = '111311313';
		$chr['8'] = '311311311';
		$chr['9'] = '113311311';
		$chr['A'] = '311113113';
		$chr['B'] = '113113113';
		$chr['C'] = '313113111';
		$chr['D'] = '111133113';
		$chr['E'] = '311133111';
		$chr['F'] = '113133111';
		$chr['G'] = '111113313';
		$chr['H'] = '311113311';
		$chr['I'] = '113113311';
		$chr['J'] = '111133311';
		$chr['K'] = '311111133';
		$chr['L'] = '113111133';
		$chr['M'] = '313111131';
		$chr['N'] = '111131133';
		$chr['O'] = '311131131';
		$chr['P'] = '113131131';
		$chr['Q'] = '111111333';
		$chr['R'] = '311111331';
		$chr['S'] = '113111331';
		$chr['T'] = '111131331';
		$chr['U'] = '331111113';
		$chr['V'] = '133111113';
		$chr['W'] = '333111111';
		$chr['X'] = '131131113';
		$chr['Y'] = '331131111';
		$chr['Z'] = '133131111';
		$chr['-'] = '131111313';
		$chr['.'] = '331111311';
		$chr[' '] = '133111311';
		$chr['$'] = '131313111';
		$chr['/'] = '131311131';
		$chr['+'] = '131113131';
		$chr['%'] = '111313131';
		$chr['*'] = '131131311';
		$this->payload = $payload;
		if ($extended)
			$payload = $this->encode_code39_ext($payload);
		if ($checksum)
			$payload .= $this->checksum_code39($payload);
		$this->readout = $payload;
		// add start and stop codes
		$payload = '*'.$payload.'*';
		$bars = /*. (Bar[int]) .*/ array();
		$clen = strlen($payload);
		for ($i = 0; $i < $clen; ++$i) {
			$char = $payload[$i];
			for ($j = 0; $j < 9; ++$j) {
				$bars[] = new Bar(($j % 2) == 0, (int) $chr[$char][$j], 1, 0);
			}
			// intercharacter gap
			$bars[] = new Bar(false, 1, 1, 0);
		}
		$this->bars = $bars;
	}
	
}
