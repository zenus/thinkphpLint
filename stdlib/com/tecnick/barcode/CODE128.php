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

use RuntimeException;
use InvalidArgumentException;

/**
 * Builds a C128 barcode.
 * <center>{@img example-CODE128.png}
 * <p><b>Generated with <tt>new CODE128("01239ABCabz")</tt>.</b></center>
 * 
 * <p>Code 128 is used extensively world wide in shipping and packaging
 * industries as a product identification code for the container and pallet
 * levels in the supply chain. 
 * 
 * <p>Arbitary sequences of bytes can be encoded. Normally bytes in the range
 * 0-128 are interpreted as ASCII, and bytes in the range 128-255 are interpreted
 * according to the ISO-8859-1 charset. Actually, internally only three subsets
 * of bytes A B and C are supported, and special codes are added to switch from
 * a subset to another in order to encode the whole payload. An automatic
 * encoding mode is also provided, that splits the payload in sequences that
 * can properly encoded and added to the result.
 * 
 * <p>A modulo 103 checksum is added to the resulting encoded data.
 * 
 * <p>The expected readout from a barcode scanner must match the payload.
 * 
 * <p>References: {@link http://en.wikipedia.org/wiki/Code_128}
 * 
 * @version $Date: 2015/02/26 10:54:34 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class CODE128 extends Barcode {
	
	/**
	 * Split text code in A/B sequence for 128 code
	 * @param string $code code to split.
	 * @return string[int][int] sequence, each entry containing the type (A or B)
	 * and the the code.
	 */
	private static function get128ABsequence($code) {
		$len = strlen($code);
		$sequence = /*. (string[int][int]) .*/ array();
		// get A sequences (if any)
		$numseq = /*. (string[int][int]) .*/ array();
		preg_match_all('/([\\0-\\31])/', $code, $numseq, PREG_OFFSET_CAPTURE);
		if (isset($numseq[1]) and !empty($numseq[1])) {
			$end_offset = 0;
			foreach ($numseq[1] as $val) {
				$offset = (int) $val[1];
				if ($offset > $end_offset) {
					// B sequence
					$sequence[] = array('B', substr($code, $end_offset));
				}
				// A sequence
				$slen = strlen($val[0]);
				$sequence[] = array('A', substr($code, $offset, $slen));
				$end_offset = $offset + $slen;
			}
			if ($end_offset < $len) {
				$sequence[] = array('B', substr($code, $end_offset));
			}
		} else {
			// only B sequence
			$sequence[] = array('B', $code);
		}
		return $sequence;
	}
	
	
	/**
	 * @param string[int][int] $x
	 * @param string[int][int] $y
	 * @return string[int][int]
	 */
	private static function mergeAB128Sequences($x, $y)
	{
		foreach($y as $seq)
			$x[] = $seq;
		return $x;
	}
	
	
	/**
	 * Builds a CODE128 barcode.
	 * @param string $payload Data to represent, normally an ASCII or ISO-8859-1
	 * string.
	 * @param string $type Encoding type: A, B, C or empty for automatic switch.
	 * The automatic mode (the default) takes care to properly encode the
	 * payload. The other modes allows to the application the maximum control
	 * over the result.
	 * @throws InvalidArgumentException Invalid encoding type. Invalid payload
	 * for the specified encoding type A, B, C.
	 */
	public function __construct($payload, $type = '')
	{
		$this->payload = $this->readout = $payload;
		$chr = array(
			'212222', /* 00 */
			'222122', /* 01 */
			'222221', /* 02 */
			'121223', /* 03 */
			'121322', /* 04 */
			'131222', /* 05 */
			'122213', /* 06 */
			'122312', /* 07 */
			'132212', /* 08 */
			'221213', /* 09 */
			'221312', /* 10 */
			'231212', /* 11 */
			'112232', /* 12 */
			'122132', /* 13 */
			'122231', /* 14 */
			'113222', /* 15 */
			'123122', /* 16 */
			'123221', /* 17 */
			'223211', /* 18 */
			'221132', /* 19 */
			'221231', /* 20 */
			'213212', /* 21 */
			'223112', /* 22 */
			'312131', /* 23 */
			'311222', /* 24 */
			'321122', /* 25 */
			'321221', /* 26 */
			'312212', /* 27 */
			'322112', /* 28 */
			'322211', /* 29 */
			'212123', /* 30 */
			'212321', /* 31 */
			'232121', /* 32 */
			'111323', /* 33 */
			'131123', /* 34 */
			'131321', /* 35 */
			'112313', /* 36 */
			'132113', /* 37 */
			'132311', /* 38 */
			'211313', /* 39 */
			'231113', /* 40 */
			'231311', /* 41 */
			'112133', /* 42 */
			'112331', /* 43 */
			'132131', /* 44 */
			'113123', /* 45 */
			'113321', /* 46 */
			'133121', /* 47 */
			'313121', /* 48 */
			'211331', /* 49 */
			'231131', /* 50 */
			'213113', /* 51 */
			'213311', /* 52 */
			'213131', /* 53 */
			'311123', /* 54 */
			'311321', /* 55 */
			'331121', /* 56 */
			'312113', /* 57 */
			'312311', /* 58 */
			'332111', /* 59 */
			'314111', /* 60 */
			'221411', /* 61 */
			'431111', /* 62 */
			'111224', /* 63 */
			'111422', /* 64 */
			'121124', /* 65 */
			'121421', /* 66 */
			'141122', /* 67 */
			'141221', /* 68 */
			'112214', /* 69 */
			'112412', /* 70 */
			'122114', /* 71 */
			'122411', /* 72 */
			'142112', /* 73 */
			'142211', /* 74 */
			'241211', /* 75 */
			'221114', /* 76 */
			'413111', /* 77 */
			'241112', /* 78 */
			'134111', /* 79 */
			'111242', /* 80 */
			'121142', /* 81 */
			'121241', /* 82 */
			'114212', /* 83 */
			'124112', /* 84 */
			'124211', /* 85 */
			'411212', /* 86 */
			'421112', /* 87 */
			'421211', /* 88 */
			'212141', /* 89 */
			'214121', /* 90 */
			'412121', /* 91 */
			'111143', /* 92 */
			'111341', /* 93 */
			'131141', /* 94 */
			'114113', /* 95 */
			'114311', /* 96 */
			'411113', /* 97 */
			'411311', /* 98 */
			'113141', /* 99 */
			'114131', /* 100 */
			'311141', /* 101 */
			'411131', /* 102 */
			'211412', /* 103 START A */
			'211214', /* 104 START B */
			'211232', /* 105 START C */
			'233111', /* STOP */
			'200000'  /* END */
		);
		// ASCII characters for code A (ASCII 00 - 95)
		$keys_a = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
		for($c = 0; $c < 32; $c++)
			$keys_a .= chr($c);
		// ASCII characters for code B (ASCII 32 - 127)
		$keys_b = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~'.chr(127);
		// special codes
		$fnc_a = array(241 => 102, 242 => 97, 243 => 96, 244 => 101);
		$fnc_b = array(241 => 102, 242 => 97, 243 => 96, 244 => 100);
		// array of symbols
		$code_data = /*. (int[int]) .*/ array();
		// length of the code
		$len = strlen($payload);
		switch($type) {
			case 'A': { // MODE A
				$startid = 103;
				for ($i = 0; $i < $len; ++$i) {
					$char = $payload[$i];
					$char_id = ord($char);
					if (($char_id >= 241) and ($char_id <= 244)) {
						$code_data[] = $fnc_a[$char_id];
					} elseif (($char_id >= 0) and ($char_id <= 95)) {
						$code_data[] = strpos($keys_a, $char);
					} else {
						throw new InvalidArgumentException("invalid character code $char_id");
					}
				}
				break;
			}
			case 'B': { // MODE B
				$startid = 104;
				for ($i = 0; $i < $len; ++$i) {
					$char = $payload[$i];
					$char_id = ord($char);
					if (($char_id >= 241) and ($char_id <= 244)) {
						$code_data[] = $fnc_b[$char_id];
					} elseif (($char_id >= 32) and ($char_id <= 127)) {
						$code_data[] = strpos($keys_b, $char);
					} else {
						throw new InvalidArgumentException("invalid character code $char_id");
					}
				}
				break;
			}
			case 'C': { // MODE C
				$startid = 105;
				if (ord($payload[0]) == 241) {
					$code_data[] = 102;
					$payload = substr($payload, 1);
					--$len;
				}
				if (preg_match("/^[0-9]*\$/", $payload) !== 1)
					throw new InvalidArgumentException("digits only allowed");
				if (($len % 2) != 0)
					throw new InvalidArgumentException("odd number of digits");
				for ($i = 0; $i < $len; $i+=2) {
					$chrnum = $payload[$i].$payload[$i+1];
					$code_data[] = intval($chrnum);
				}
				break;
			}
			case '': { // MODE AUTO
				// split code into sequences
				$sequence = /*. (string[int][int]) .*/ array();
				// get numeric sequences (if any)
				$numseq = /*. (string[int][int]) .*/ array();
				preg_match_all('/([0-9]{4,})/', $payload, $numseq, PREG_OFFSET_CAPTURE);
				if (isset($numseq[1]) and !empty($numseq[1])) {
					$end_offset = 0;
					foreach ($numseq[1] as $val) {
						$offset = (int) $val[1];
						if ($offset > $end_offset) {
							// non numeric sequence
							$sequence = self::mergeAB128Sequences($sequence, CODE128::get128ABsequence(substr($payload, $end_offset, ($offset - $end_offset))));
						}
						// numeric sequence
						$slen = strlen($val[0]);
						if (($slen % 2) != 0) {
							// the length must be even
							--$slen;
						}
						$sequence[] = array('C', substr($payload, $offset, $slen));
						$end_offset = $offset + $slen;
					}
					if ($end_offset < $len) {
						$sequence = self::mergeAB128Sequences($sequence, self::get128ABsequence(substr($payload, $end_offset)));
					}
				} else {
					// text code (non C mode)
					$sequence = self::mergeAB128Sequences($sequence, self::get128ABsequence($payload));
				}
				// process the sequence
				$startid = 0;
				foreach ($sequence as $key => $seq) {
					switch($seq[0]) {
						case 'A': {
							if ($key == 0) {
								$startid = 103;
							} elseif ($sequence[($key - 1)][0] !== 'A') {
								if ((strlen($seq[1]) == 1) and ($key > 0)
								and ($sequence[($key - 1)][0] === 'B')
								and (!isset($sequence[($key - 1)][3]))) {
									// single character shift
									$code_data[] = 98;
									// mark shift
									$sequence[$key][3] = "SHIFT";
								} elseif (!isset($sequence[($key - 1)][3])) {
									$code_data[] = 101;
								}
							}
							for ($i = 0; $i < strlen($seq[1]); ++$i) {
								$char = $seq[1][$i];
								$char_id = ord($char);
								if (($char_id >= 241) and ($char_id <= 244)) {
									$code_data[] = $fnc_a[$char_id];
								} else {
									$code_data[] = strpos($keys_a, $char);
								}
							}
							break;
						}
						case 'B': {
							if ($key == 0) {
								$tmpchr = ord($seq[1][0]);
								if ((strlen($seq[1]) == 1) and ($tmpchr >= 241)
								and ($tmpchr <= 244) and isset($sequence[($key + 1)])
								and ($sequence[($key + 1)][0] !== 'B')) {
									switch ($sequence[($key + 1)][0]) {
										case 'A': {
											$startid = 103;
											$sequence[$key][0] = 'A';
											$code_data[] = $fnc_a[$tmpchr];
											break;
										}
										case 'C': {
											$startid = 105;
											$sequence[$key][0] = 'C';
											$code_data[] = $fnc_a[$tmpchr];
											break;
										}
										default: throw new RuntimeException();
									}
									break;
								} else {
									$startid = 104;
								}
							} elseif ($sequence[($key - 1)][0] !== 'B') {
								if ((strlen($seq[1]) == 1) and ($key > 0)
								and ($sequence[($key - 1)][0] === 'A')
								and (!isset($sequence[($key - 1)][3]))) {
									// single character shift
									$code_data[] = 98;
									// mark shift
									$sequence[$key][3] = "SHIFT";
								} elseif (!isset($sequence[($key - 1)][3])) {
									$code_data[] = 100;
								}
							}
							for ($i = 0; $i < strlen($seq[1]); ++$i) {
								$char = $seq[1][$i];
								$char_id = ord($char);
								if (($char_id >= 241) and ($char_id <= 244)) {
									$code_data[] = $fnc_b[$char_id];
								} else {
									$code_data[] = strpos($keys_b, $char);
								}
							}
							break;
						}
						case 'C': {
							if ($key == 0) {
								$startid = 105;
							} elseif ($sequence[($key - 1)][0] !== 'C') {
								$code_data[] = 99;
							}
							for ($i = 0; $i < strlen($seq[1]); $i+=2) {
								$chrnum = $seq[1][$i].$seq[1][$i+1];
								$code_data[] = intval($chrnum);
							}
							break;
						}
						default: throw new RuntimeException();
					}
				}
				break;
			}
			default: throw new InvalidArgumentException("invalid encoding type: $type");
		}
		// calculate check character
		$sum = $startid;
		foreach ($code_data as $key => $v) {
			$sum += ($v * ($key + 1));
		}
		// add check character
		$code_data[] = ($sum % 103);
		// add stop sequence
		$code_data[] = 106;
		$code_data[] = 107;
		// add start code at the beginning
		array_unshift($code_data, $startid);
		// build barcode array
		$bars = /*. (Bar[int]) .*/ array();
		foreach ($code_data as $v) {
			$s = $chr[$v];
			for ($j = 0; $j < 6; ++$j) {
				$bars[] = new Bar(($j % 2) == 0, (int) $s[$j], 1, 0);
			}
		}
		$this->bars = $bars;
	}
}
