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
 * Builds a CODE93 - USS-93 barcode.
 * 
 * <center>{@img example-CODE93.png}
 * <p><b>Created with <tt>new CODE93("Hello guys!")</tt></b></center>
 * 
 * <p>CODE93 encodes the full ASCII charset and adds a two modulo-47 checksums.
 * The basic characters (digits, uppercase letters and some punctuation) are
 * combined to encode lowercase letters and the other missing characters, so
 * an higher density can be achieved when the payload contains only basic
 * characters. Binary content (that is, byte codes above 127) is not allowed.
 * 
 * <p>The expected readout from a barcode scanner must match the payload.
 * @version $Date: 2015/02/26 10:55:03 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class CODE93 extends Barcode {
	
	
	/**
	 * Calculate CODE 93 checksum (modulo 47).
	 * @param string $code code to represent.
	 * @return string checksum code.
	 */
	protected function checksum_code93($code) {
		$chars = array(
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%',
			'<', '=', '>', '?');
		// translate special characters
		$code = strtr($code, chr(128).chr(131).chr(129).chr(130), '<=>?');
		$len = strlen($code);
		// calculate check digit C
		$p = 1;
		$check = 0;
		for ($i = ($len - 1); $i >= 0; --$i) {
			// FIXME: use strpos() instead and check result
			$k = array_keys($chars, $code[$i]);
			$check += ( (int) $k[0] * $p);
			++$p;
			if ($p > 20) {
				$p = 1;
			}
		}
		$check %= 47;
		$c = $chars[$check];
		$code .= $c;
		// calculate check digit K
		$p = 1;
		$check = 0;
		for ($i = $len; $i >= 0; --$i) {
			// FIXME: use strpos() instead and check result
			$k = array_keys($chars, $code[$i]);
			$check += ( (int) $k[0] * $p);
			++$p;
			if ($p > 15) {
				$p = 1;
			}
		}
		$check %= 47;
		$checksum = $c.$chars[$check];
		// restore special characters
		$checksum = strtr($checksum, '<=>?', chr(128).chr(131).chr(129).chr(130));
		return $checksum;
	}
	
	/*
	 * Maps ASCII codes to CODE93 sequences of bytes.
	 */
	private static $map = array(
		"\203U", "\200A", "\200B", "\200C", "\200D", "\200E", "\200F", "\200G",
		"\200H", "\200I", "\200J", "K",     "\200L", "\200M", "\200N", "\200O",
		"\200P", "\200Q", "\200R", "\200S", "\200T", "\200U", "\200V", "\200W",
		"\200X", "\200Y", "\200Z", "\203A", "\203B", "\203C", "\203D", "\203E",
		" ",     "\201A", "\201B", "\201C", "\201D", "\201E", "\201F", "\201G",
		"\201H", "\201I", "\201J", "\201K", "\201L", "-",     ".",     "\201O",
		"0",     "1",     "2",     "3",     "4",     "5",     "6",     "7",    
		"8",     "9",     "\201Z", "\203F", "\203G", "\203H", "\203I", "\203J",
		"\203V", "A",     "B",     "C",     "D",     "E",     "F",     "G",    
		"H",     "I",     "J",     "K",     "L",     "M",     "N",     "O",    
		"P",     "Q",     "R",     "S",     "T",     "U",     "V",     "W",    
		"X",     "Y",     "Z",     "\203K", "\203L", "\203M", "\203N", "\203O",
		"\203W", "\202A", "\202B", "\202C", "\202D", "\202E", "\202F", "\202G",
		"\202H", "\202I", "\202J", "\202K", "\202L", "\202M", "\202N", "\202O",
		"\202P", "\202Q", "\202R", "\202S", "\202T", "\202U", "\202V", "\202W",
		"\202X", "\202Y", "\202Z", "\203P", "\203Q", "\203R", "\203S", "\203T"
	);
	
	
	/**
	 * Builds a CODE 93 - USS-93 barcode.
	 * @param string $payload Any ASCII string containing at least one character.
	 * @throws InvalidArgumentException Empty payload. Non-ASCII character in
	 * payload.
	 */
	public function __construct($payload) {
		if (preg_match("/^[\\x00-\\x7f]+\$/", $payload) !== 1)
			throw new InvalidArgumentException("payload contains non-ASCII character");
		$this->payload = $this->readout = $payload;
		$chr[48] = '131112'; // 0
		$chr[49] = '111213'; // 1
		$chr[50] = '111312'; // 2
		$chr[51] = '111411'; // 3
		$chr[52] = '121113'; // 4
		$chr[53] = '121212'; // 5
		$chr[54] = '121311'; // 6
		$chr[55] = '111114'; // 7
		$chr[56] = '131211'; // 8
		$chr[57] = '141111'; // 9
		$chr[65] = '211113'; // A
		$chr[66] = '211212'; // B
		$chr[67] = '211311'; // C
		$chr[68] = '221112'; // D
		$chr[69] = '221211'; // E
		$chr[70] = '231111'; // F
		$chr[71] = '112113'; // G
		$chr[72] = '112212'; // H
		$chr[73] = '112311'; // I
		$chr[74] = '122112'; // J
		$chr[75] = '132111'; // K
		$chr[76] = '111123'; // L
		$chr[77] = '111222'; // M
		$chr[78] = '111321'; // N
		$chr[79] = '121122'; // O
		$chr[80] = '131121'; // P
		$chr[81] = '212112'; // Q
		$chr[82] = '212211'; // R
		$chr[83] = '211122'; // S
		$chr[84] = '211221'; // T
		$chr[85] = '221121'; // U
		$chr[86] = '222111'; // V
		$chr[87] = '112122'; // W
		$chr[88] = '112221'; // X
		$chr[89] = '122121'; // Y
		$chr[90] = '123111'; // Z
		$chr[45] = '121131'; // -
		$chr[46] = '311112'; // .
		$chr[32] = '311211'; //
		$chr[36] = '321111'; // $
		$chr[47] = '112131'; // /
		$chr[43] = '113121'; // +
		$chr[37] = '211131'; // %
		$chr[128] = '121221'; // ($)
		$chr[129] = '311121'; // (/)
		$chr[130] = '122211'; // (+)
		$chr[131] = '312111'; // (%)
		$chr[42] = '111141'; // start-stop
		
		$code_ext = '';
		$clen = strlen($payload);
		$encode = self::$map;
		for ($i = 0 ; $i < $clen; ++$i) {
			$code_ext .= $encode[ord($payload[$i])];
		}
		// checksum
		$code_ext .= $this->checksum_code93($code_ext);
		// add start and stop codes
		$payload = '*'.$code_ext.'*';
		$bars = /*. (Bar[int]) .*/ array();
		$clen = strlen($payload);
		for ($i = 0; $i < $clen; ++$i) {
			$char = ord($payload[$i]);
			for ($j = 0; $j < 6; ++$j) {
				$bars[] = new Bar(($j % 2) == 0, (int) $chr[$char][$j], 1, 0);
			}
		}
		$bars[] = new Bar(true, 1, 1, 0);
		$this->bars = $bars;
	}
}
