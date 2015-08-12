<?php
//============================================================+
// File name   : qrcode.php (original name)
// Version     : 1.0.010
// Begin       : 2010-03-22
// Last Update : 2012-07-25
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2010-2012 Nicola Asuni - Tecnick.com LTD
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

namespace com\tecnick\barcode2d;

/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
.*/

require_once __DIR__ . "/../../../all.php";

use RuntimeException;
use InvalidArgumentException;


/** @access private */
class QRItem {
	public $mode = 0;
	public $size = 0;
	/** @var string[int] */
	public $data;
	/** @var int[int] */
	public $bstream;
	
	/**
	 * @param int $mode
	 * @param int $size
	 * @param string[int] $data
	 * @param int[int] $bstream
	 */
	public function __construct($mode, $size, $data, $bstream) {
		$this->mode = $mode;
		$this->size = $size;
		$this->data = $data;
		$this->bstream = $bstream;
	}
}


/** @access private */
class RSBlock {
	public $dataLength = 0;
	/** @var int[int] */
	public $data;
	public $eccLength = 0;
	/** @var int[int] */
	public $ecc;
}


/**
 * Reed-Solomen codec data. See "Reed Solomon encoder" by Phil Karn, KA9Q
 * (GNU-LGPLv2).
 * @access private
 */
class RSCodec {
	/**
	 * number of roots in the RS code generator polynomial, which is the
	 * same as the number of parity symbols in a block
	 */
	public $nroots = 0;
	/** bits per symbol */
	public $mm = 0;
	public $gfpoly = 0;
	/** first consecutive root, index form */
	public $fcr = 0;
	/** primitive element, index form */
	public $prim = 0;
	/** total number of symbols in a RS block */
	public $nn = 0;
	/**
	 * NN elements to convert Galois field elements in index (log) form to
	 * polynomial form
	 * @var int[int]
	 */
	public $alpha_to;
	/**
	 * NN elements to convert Galois field elements in polynomial form to
	 * index (log) form
	 * @var int[int]
	 */
	public $index_of;
	/**
	 * NROOTS+1 elements containing the generator polynomial in index form
	 * @var int[int]
	 */
	public $genpoly;
	/** prim-th root of 1, index form */
	public $iprim = 0;
	/** number of pad symbols in a block */
	public $pad = 0;
}


/**
 * Builds a QR Code 2D barcode.
 * QR Code symbol is a 2D barcode that can be scanned by handy terminals such as
 * a mobile phone with CCD.
 * The capacity of QR Code is up to 7000 digits or 4000 characters, and has high
 * robustness.
 * This class supports QR Code model 2, described in JIS (Japanese Industrial
 * Standards) X0510:2004 or ISO/IEC 18004.
 * Currently the following features are not supported: ECI and FNC1 mode, Micro
 * QR Code, QR Code model 1, Structured mode.
 * 
 * <center>{@img example-qrcode.png}<br><b>Example of QR code.</b></center>
 * 
 * <p>Although binary data are supported, an higher density can be obtained
 * limiting the range of the data (or at least part of them) to one of the
 * following recognized subsets:
 * 
 * <ul>
 * <li><b>Numbers</b> of digits 0-9. 3 characters are encoded to 10 bit length.
 * In theory, up to 7089 digits can be stored in a QRcode.</li>
 * <li><b>Alphanumeric</b> including digits, uppercase letters, space and some
 * punctuation symbols <tt>$ % * + - . / :</tt> for a total of 45 characters.
 * 2 characters are encoded to 11 bits length. Up to 4296 alpha chars can be
 * stored in a QRcode.</li>
 * <li><b>Kanji<sup>*</sup></b> multibyte characters are encoded to 13 bit length.
 * Up to 1817 characters can be stored in a QRCode. The constructor allows to
 * enable the detection of this encoding, which is disabled by default.</li>
 * <li><b>Binary</b> for anything else. Up to 2953 bytes can be stored in a
 * QRCode.</li>
 * </ul>
 * 
 * <blockquote>
 * * NOTE. Nowadays, there is no real reason to use the specific QR built-in
 * encoding for Kanji anymore, as modern scanners support several Unicode encodings
 * (UTF-8, UTF-16BE, UTF-16LE, etc.), possibly requiring only a leading Unicode
 * BOM. You may test it on-line at {@link http://online-barcode-reader.inliteresearch.com}
 * which automatically detects the presence of a BOM and shows the decoded string.
 * Example: <tt>$qr = new QRCODE("\xEF\xBB\xBFyour UTF-8 string here");
 * file_put_contents("qr.png", $qr-&gt;getPNG());</tt>
 * </blockquote>
 * 
 * <p>The size of the resulting symbol depends on the amount of the encoded
 * data and on the level of the correction code. For example, binary data and
 * high level of error correction bring to larger symbols than digits only at
 * low error correction level.
 * 
 * <p>The generation algorithm contains the {@link mt_rand()} function. This means
 * that <i>different QR symbols can be generated for the same set of data and
 * configuration parameters</i>, although all these result to carry the exact same
 * data. If you want to compare the results from two run of this program, for
 * example for debugging purposes, first set the seed of the pseudo-random number
 * generator by calling the {@link mt_srand(1234)} function with a given
 * fixed value so that the same sequence of pseudo-random numbers will be
 * generate hereafter. This also means that the results from two different
 * programs cannot be compared visually; only the decoded data are.
 *
 * <p>This class is derived from <i>PHP QR Code encoder</i> by Dominik Dzienia
 * ({@link http://phpqrcode.sourceforge.net/}) based on libqrencode C library 3.1.1
 * by Kentaro Fukuchi ({@link http://megaui.net/fukuchi/works/qrencode/index.en.html}),
 * contains Reed-Solomon code written by Phil Karn, KA9Q. QR Code is registered
 * trademark of DENSO WAVE INCORPORATED ({@link http://www.qrcode.com/}).
 * Please read comments on this class source file for full copyright and license
 * information.
 *
 * @version $Date: 2015/03/05 17:06:15 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint; several fix)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
class QRCODE extends Barcode2D {
	
	/** @access private */
	const

	// Encoding modes (characters which can be encoded in QRcode)
	// ==========================================================
	
	MODE_END_OF_PAYLOAD = -1,

	/* Encoding mode numeric (0-9). 3 characters are encoded to 10bit length.
	    In theory, 7089 characters or less can be stored in a QRcode. */
	MODE_NM = 0,

	/* Encoding mode alphanumeric (0-9A-Z $%*+-./:) 45characters. 2 characters
	    are encoded to 11bit length. In theory, 4296 characters or less can be
		stored in a QRcode. */
	MODE_AN = 1,

	/* Encoding mode 8bit byte data. In theory, 2953 characters or less can be
	    stored in a QRcode. */
	MODE_8B = 2,

	/* Encoding mode KANJI. A KANJI character (multibyte character) is encoded
	    to 13bit length. In theory, 1817 characters or less can be stored in a QRcode. */
	MODE_KJ = 3,

	/* Encoding mode STRUCTURED (currently unsupported). */
	MODE_ST = 4,

	/* Error correction level L : About 7% or less errors can be corrected. */
	ECLEVEL_L = 0,

	/* Error correction level M : About 15% or less errors can be corrected. */
	ECLEVEL_M = 1,

	/* Error correction level Q : About 25% or less errors can be corrected. */
	ECLEVEL_Q = 2,

	/* Error correction level H : About 30% or less errors can be corrected. */
	ECLEVEL_H = 3,

	/* Maximum QR Code version. */
	SPEC_VERSION_MAX = 40,

	/* Maximum matrix size for maximum version (version 40 is 177*177 matrix). */
	SPEC_WIDTH_MAX = 177,

	/* Matrix index to get width from $capacity array. */
	CAP_WIDTH =    0,

	/* Matrix index to get number of words from $capacity array. */
	CAP_WORDS =    1,

	/* Matrix index to get remainder from $capacity array. */
	CAP_REMINDER = 2,

	// Structure (currently usupported)

	/* Number of header bits for structured mode */
	STRUCTURE_HEADER_BITS =  20,

//	/* Max number of symbols for structured mode */
//	MAX_STRUCTURED_SYMBOLS = 16,

	// Masks

	/* Down point base value for case 1 mask pattern (concatenation of same
	    color in a line or a column) */
	N1 =  3,

	/* Down point base value for case 2 mask pattern (module block of same color) */
	N2 =  3,

	/* Down point base value for case 3 mask pattern
	    (1:1:3:1:1(dark:bright:dark:bright:dark)pattern in a line or a column) */
	N3 = 40,

	/* Down point base value for case 4 mask pattern (ration of dark modules in whole) */
	N4 = 10,

	// Optimization settings

	/* if true, estimates best mask (spec. default, but extremely slow; set
	    to false to significant performance boost but (probably) worst quality code */
	FIND_BEST_MASK = true,

	/* if false, checks all masks available, otherwise value tells count of
	    masks need to be checked, mask id are got randomly */
	FIND_FROM_RANDOM = 2,

	/* when QR_FIND_BEST_MASK === false */
	DEFAULT_MASK = 2;


	/**
	 * QR code version. Size of QRcode is defined as version. Version is from 1
	 * to 40. Version 1 is 21*21 matrix. And 4 modules increases whenever 1
	 * version increases. So version 40 is 177*177 matrix.
	 */
	private $version = 0;

	/**
	 * Levels of error correction. See definitions for possible values.
	 */
	private $level = self::ECLEVEL_L;

	/**
	 * If binary sequences that looks like Kanji characters encoding should be
	 * encoded as such. If unset, non-numeric and non alpha bytes are encoded
	 * as binary sequences.
	 */
	private $detectKanji = FALSE;

//	/**
//	 * Boolean flag, if true the input string will be converted to uppercase.
//	 */
//	private $casesensitive = true;

	/**
	 * Width.
	 */
	private $width = 0;

	/**
	 * Frame.
	 * @var string[int]
	 */
	private $frame;

	/**
	 * X position of bit.
	 */
	private $x = 0;

	/**
	 * Y position of bit.
	 */
	private $y = 0;

	/**
	 * Direction.
	 */
	private $dir = 0;

	/**
	 * Single bit value.
	 */
	private $bit = 0;

	/**
	 * Data code.
	 * @var int[int]
	 */
	private $datacode = array();

	/**
	 * Error correction code.
	 * @var int[int]
	 */
	private $ecccode = array();

	/**
	 * Blocks.
	 */
	private $blocks = 0;

	/**
	 * Reed-Solomon blocks.
	 * @var RSBlock[int]
	 */
	private $rsblocks = array();

	/**
	 * Counter.
	 */
	private $count = 0;

	/**
	 * Data length.
	 */
	private $dataLength = 0;

	/**
	 * Error correction length.
	 */
	private $eccLength = 0;

	/**
	 * Value b1.
	 */
	private $b1 = 0;

	/**
	 * Run length.
	 * @var int[int]
	 */
	private $runLength = array();

	/**
	 * Input data string.
	 * @var string
	 */
	private $dataStr;

	/**
	 * Input items.
	 * @var QRItem[int]
	 */
	private $items;

	/**
	 * Reed-Solomon codec items.
	 * @var RSCodec[int]
	 */
	private $rsitems = array();

	/**
	 * Array of frames.
	 * @var string[int][int]
	 */
	private $frames = array();

	/*
	 * Alphabet-numeric conversion table  (see JIS X0510:2004, pp.19).
	 */
	private static $anTable = array(
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, //
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, //
		36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43, //
		 0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 44, -1, -1, -1, -1, -1, //
		-1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, //
		25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1, //
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, //
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1  //
	);

	/*
	 * Array Table of the capacity of symbols.
	 * See Table 1 (pp.13) and Table 12-16 (pp.30-36), JIS X0510:2004.
	 */
	private static $capacity = array(
		array(  0,    0, 0), //
		array( 21,   26, 0), //  1
		array( 25,   44, 7), //
		array( 29,   70, 7), //
		array( 33,  100, 7), //
		array( 37,  134, 7), //  5
		array( 41,  172, 7), //
		array( 45,  196, 0), //
		array( 49,  242, 0), //
		array( 53,  292, 0), //
		array( 57,  346, 0), // 10
		array( 61,  404, 0), //
		array( 65,  466, 0), //
		array( 69,  532, 0), //
		array( 73,  581, 3), //
		array( 77,  655, 3), // 15
		array( 81,  733, 3), //
		array( 85,  815, 3), //
		array( 89,  901, 3), //
		array( 93,  991, 3), //
		array( 97, 1085, 3), // 20
		array(101, 1156, 4), //
		array(105, 1258, 4), //
		array(109, 1364, 4), //
		array(113, 1474, 4), //
		array(117, 1588, 4), // 25
		array(121, 1706, 4), //
		array(125, 1828, 4), //
		array(129, 1921, 3), //
		array(133, 2051, 3), //
		array(137, 2185, 3), // 30
		array(141, 2323, 3), //
		array(145, 2465, 3), //
		array(149, 2611, 3), //
		array(153, 2761, 3), //
		array(157, 2876, 0), // 35
		array(161, 3034, 0), //
		array(165, 3196, 0), //
		array(169, 3362, 0), //
		array(173, 3532, 0), //
		array(177, 3706, 0)  // 40
	);
	
	private static $ecc = array(
		array(   0,    0,    0,    0), //
		array(   7,   10,   13,   17), //  1
		array(  10,   16,   22,   28), //
		array(  15,   26,   36,   44), //
		array(  20,   36,   52,   64), //
		array(  26,   48,   72,   88), //  5
		array(  36,   64,   96,  112), //
		array(  40,   72,  108,  130), //
		array(  48,   88,  132,  156), //
		array(  60,  110,  160,  192), //
		array(  72,  130,  192,  224), // 10
		array(  80,  150,  224,  264), //
		array(  96,  176,  260,  308), //
		array( 104,  198,  288,  352), //
		array( 120,  216,  320,  384), //
		array( 132,  240,  360,  432), // 15
		array( 144,  280,  408,  480), //
		array( 168,  308,  448,  532), //
		array( 180,  338,  504,  588), //
		array( 196,  364,  546,  650), //
		array( 224,  416,  600,  700), // 20
		array( 224,  442,  644,  750), //
		array( 252,  476,  690,  816), //
		array( 270,  504,  750,  900), //
		array( 300,  560,  810,  960), //
		array( 312,  588,  870, 1050), // 25
		array( 336,  644,  952, 1110), //
		array( 360,  700, 1020, 1200), //
		array( 390,  728, 1050, 1260), //
		array( 420,  784, 1140, 1350), //
		array( 450,  812, 1200, 1440), // 30
		array( 480,  868, 1290, 1530), //
		array( 510,  924, 1350, 1620), //
		array( 540,  980, 1440, 1710), //
		array( 570, 1036, 1530, 1800), //
		array( 570, 1064, 1590, 1890), // 35
		array( 600, 1120, 1680, 1980), //
		array( 630, 1204, 1770, 2100), //
		array( 660, 1260, 1860, 2220), //
		array( 720, 1316, 1950, 2310), //
		array( 750, 1372, 2040, 2430) // 40
	);

	/*
	 * Array Length indicator.
	 */
	private static $lengthTableBits = array(
		array(10, 12, 14),
		array( 9, 11, 13),
		array( 8, 16, 16),
		array( 8, 10, 12)
	);

	/*
	 * Array Table of the error correction code (Reed-Solomon block).
	 * See Table 12-16 (pp.30-36), JIS X0510:2004.
	 */
	private static $eccTable = array(
		array(array( 0,  0), array( 0,  0), array( 0,  0), array( 0,  0)), //
		array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), //  1
		array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), //
		array(array( 1,  0), array( 1,  0), array( 2,  0), array( 2,  0)), //
		array(array( 1,  0), array( 2,  0), array( 2,  0), array( 4,  0)), //
		array(array( 1,  0), array( 2,  0), array( 2,  2), array( 2,  2)), //  5
		array(array( 2,  0), array( 4,  0), array( 4,  0), array( 4,  0)), //
		array(array( 2,  0), array( 4,  0), array( 2,  4), array( 4,  1)), //
		array(array( 2,  0), array( 2,  2), array( 4,  2), array( 4,  2)), //
		array(array( 2,  0), array( 3,  2), array( 4,  4), array( 4,  4)), //
		array(array( 2,  2), array( 4,  1), array( 6,  2), array( 6,  2)), // 10
		array(array( 4,  0), array( 1,  4), array( 4,  4), array( 3,  8)), //
		array(array( 2,  2), array( 6,  2), array( 4,  6), array( 7,  4)), //
		array(array( 4,  0), array( 8,  1), array( 8,  4), array(12,  4)), //
		array(array( 3,  1), array( 4,  5), array(11,  5), array(11,  5)), //
		array(array( 5,  1), array( 5,  5), array( 5,  7), array(11,  7)), // 15
		array(array( 5,  1), array( 7,  3), array(15,  2), array( 3, 13)), //
		array(array( 1,  5), array(10,  1), array( 1, 15), array( 2, 17)), //
		array(array( 5,  1), array( 9,  4), array(17,  1), array( 2, 19)), //
		array(array( 3,  4), array( 3, 11), array(17,  4), array( 9, 16)), //
		array(array( 3,  5), array( 3, 13), array(15,  5), array(15, 10)), // 20
		array(array( 4,  4), array(17,  0), array(17,  6), array(19,  6)), //
		array(array( 2,  7), array(17,  0), array( 7, 16), array(34,  0)), //
		array(array( 4,  5), array( 4, 14), array(11, 14), array(16, 14)), //
		array(array( 6,  4), array( 6, 14), array(11, 16), array(30,  2)), //
		array(array( 8,  4), array( 8, 13), array( 7, 22), array(22, 13)), // 25
		array(array(10,  2), array(19,  4), array(28,  6), array(33,  4)), //
		array(array( 8,  4), array(22,  3), array( 8, 26), array(12, 28)), //
		array(array( 3, 10), array( 3, 23), array( 4, 31), array(11, 31)), //
		array(array( 7,  7), array(21,  7), array( 1, 37), array(19, 26)), //
		array(array( 5, 10), array(19, 10), array(15, 25), array(23, 25)), // 30
		array(array(13,  3), array( 2, 29), array(42,  1), array(23, 28)), //
		array(array(17,  0), array(10, 23), array(10, 35), array(19, 35)), //
		array(array(17,  1), array(14, 21), array(29, 19), array(11, 46)), //
		array(array(13,  6), array(14, 23), array(44,  7), array(59,  1)), //
		array(array(12,  7), array(12, 26), array(39, 14), array(22, 41)), // 35
		array(array( 6, 14), array( 6, 34), array(46, 10), array( 2, 64)), //
		array(array(17,  4), array(29, 14), array(49, 10), array(24, 46)), //
		array(array( 4, 18), array(13, 32), array(48, 14), array(42, 32)), //
		array(array(20,  4), array(40,  7), array(43, 22), array(10, 67)), //
		array(array(19,  6), array(18, 31), array(34, 34), array(20, 61))  // 40
	);

	/*
	 * Array Positions of alignment patterns.
	 * This array includes only the second and the third position of the alignment
	 * patterns. Rest of them can be calculated from the distance between them.
	 * See Table 1 in Appendix E (pp.71) of JIS X0510:2004.
	 */
	private static $alignmentPattern = array(
		array( 0,  0),
		array( 0,  0), array(18,  0), array(22,  0), array(26,  0), array(30,  0), //  1- 5
		array(34,  0), array(22, 38), array(24, 42), array(26, 46), array(28, 50), //  6-10
		array(30, 54), array(32, 58), array(34, 62), array(26, 46), array(26, 48), // 11-15
		array(26, 50), array(30, 54), array(30, 56), array(30, 58), array(34, 62), // 16-20
		array(28, 50), array(26, 50), array(30, 54), array(28, 54), array(32, 58), // 21-25
		array(30, 58), array(34, 62), array(26, 50), array(30, 54), array(26, 52), // 26-30
		array(30, 56), array(34, 60), array(30, 58), array(34, 62), array(30, 54), // 31-35
		array(24, 50), array(28, 54), array(32, 58), array(26, 54), array(30, 58)  // 35-40
	);

	/*
	 * Array Version information pattern (BCH coded).
	 * See Table 1 in Appendix D (pp.68) of JIS X0510:2004.
	 * size: [QRSPEC_VERSION_MAX - 6]
	 */
	private static $versionPattern = array(
		0x07c94, 0x085bc, 0x09a99, 0x0a4d3, 0x0bbf6, 0x0c762, 0x0d847, 0x0e60d, //
		0x0f928, 0x10b78, 0x1145d, 0x12a17, 0x13532, 0x149a6, 0x15683, 0x168c9, //
		0x177ec, 0x18ec4, 0x191e1, 0x1afab, 0x1b08e, 0x1cc1a, 0x1d33f, 0x1ed75, //
		0x1f250, 0x209d5, 0x216f0, 0x228ba, 0x2379f, 0x24b0b, 0x2542e, 0x26a64, //
		0x27541, 0x28c69
	);

	/*
	 * Array Format information
	 */
	private static $formatInfo = array(
		array(0x77c4, 0x72f3, 0x7daa, 0x789d, 0x662f, 0x6318, 0x6c41, 0x6976), //
		array(0x5412, 0x5125, 0x5e7c, 0x5b4b, 0x45f9, 0x40ce, 0x4f97, 0x4aa0), //
		array(0x355f, 0x3068, 0x3f31, 0x3a06, 0x24b4, 0x2183, 0x2eda, 0x2bed), //
		array(0x1689, 0x13be, 0x1ce7, 0x19d0, 0x0762, 0x0255, 0x0d0c, 0x083b)  //
	);
		
	/**
	 * Set a byte in a string. Ratio: under PHPLint, string is immutable, then
	 * one cannot change a single byte with $s[$i] = "x". This function
	 * overcomes this restriction.
	 * @param string & $s
	 * @param int $i Offset of the char.
	 * @param int $b Value of the byte.
	 * @return void
	 */
	private static function setByte(& $s, $i, $b) {
		if (!(0 <= $i && $i < strlen($s)))
			throw new \OutOfRangeException("s=$s, i=$i");
		if (!(0 <= $b && $b <= 256))
			throw new InvalidArgumentException("b=$b");
		$s = substr($s, 0, $i) . chr($b) . substr($s, $i+1);
	}
	
	
	/**
	 * array_fill() cannot be validated by PHPLint, otherwise a cast() is
	 * required. This workaround is faster than a cast("int[int]", array_fill(...)).
	 * @param int $n
	 * @param int $v
	 * @return int[int]
	 */
	private static function newArrayInt($n, $v = 0) {
		$a = /*. (int[int]) .*/ array();
		while ( $n > 0 ) {
			$a[] = $v;
			$n--;
		}
		return $a;
	}
	
	
	/**
	 * Returns a square matrix $size*$size of zeros.
	 * @param int $size
	 * @return int[int][int]
	 */
	private static function newMatrix($size) {
		$f = /*. (int[int][int]) .*/ array();
		for($r=0; $r<$size; $r++){
			$f[] = self::newArrayInt($size);
		}
		return $f;
	}

	/**
	 * Set frame value at specified position
	 * @param int[string] $at x,y position
	 * @param int $val value of the character to set
	 */
	private function setFrameAt($at, $val) {
		self::setByte($this->frame[$at['y']], $at['x'], $val);
	}

	/**
	 * Return the next frame position
	 * @return int[string] of x,y coordinates
	 */
	private function getNextPosition() {
		do {
			if ($this->bit == -1) {
				$this->bit = 0;
				return array('x'=>$this->x, 'y'=>$this->y);
			}
			$x = $this->x;
			$y = $this->y;
			$w = $this->width;
			if ($this->bit == 0) {
				$x--;
				$this->bit++;
			} else {
				$x++;
				$y += $this->dir;
				$this->bit--;
			}
			if ($this->dir < 0) {
				if ($y < 0) {
					$y = 0;
					$x -= 2;
					$this->dir = 1;
					if ($x == 6) {
						$x--;
						$y = 9;
					}
				}
			} else {
				if ($y == $w) {
					$y = $w - 1;
					$x -= 2;
					$this->dir = -1;
					if ($x == 6) {
						$x--;
						$y -= 8;
					}
				}
			}
			if (($x < 0) or ($y < 0)) {
				return NULL;
			}
			$this->x = $x;
			$this->y = $y;
		} while((ord($this->frame[$y][$x]) & 0x80) != 0);
		return array('x'=>$x, 'y'=>$y);
	}

	/**
	 * Return Reed-Solomon block code.
	 * @return int rsblocks
	 */
	private function getCode() {
		if ($this->count < $this->dataLength) {
			$row = $this->count % $this->blocks;
			$col = (int) ($this->count / $this->blocks);
			if ($col >= $this->rsblocks[0]->dataLength) {
				$row += $this->b1;
			}
			$ret = $this->rsblocks[$row]->data[$col];
		} elseif ($this->count < $this->dataLength + $this->eccLength) {
			$row = ($this->count - $this->dataLength) % $this->blocks;
			$col = (int) (($this->count - $this->dataLength) / $this->blocks);
			$ret = $this->rsblocks[$row]->ecc[$col];
		} else {
			return 0;
		}
		$this->count++;
		return $ret;
	}

	/**
	 * Return BCH encoded version information pattern that is used for the
	 * symbol of version 7 or greater. Use lower 18 bits.
	 * @param int $version version
	 * @return int BCH encoded version information pattern
	 */
	private function getVersionPattern($version) {
		if (($version < 7) or ($version > self::SPEC_VERSION_MAX)) {
			return 0;
		}
		return self::$versionPattern[($version - 7)];
	}

	/**
	 * Return BCH encoded format information pattern.
	 * @param int $mask
	 * @param int $level error correction level
	 * @return int BCH encoded format information pattern
	 */
	private function getFormatInfo($mask, $level) {
		if ($mask < 0 or $mask > 7) {
			return 0;
		}
		if ($level < 0 or $level > 3) {
			return 0;
		}
		return self::$formatInfo[$level][$mask];
	}
	
	/**
	 * Write Format Information on frame and returns the number of black bits.
	 * @param int $width frame width
	 * @param string[int] & $frame frame
	 * @param int $mask masking mode
	 * @param int $level error correction level
	 * @return int blacks
	 */
	private function writeFormatInformation($width, & $frame, $mask, $level) {
		$blacks = 0;
		$format =  $this->getFormatInfo($mask, $level);
		for ($i=0; $i<8; ++$i) {
			if (($format & 1) != 0) {
				$blacks += 2;
				$v = 0x85;
			} else {
				$v = 0x84;
			}
			self::setByte($frame[8], $width - 1 - $i, $v);
			if ($i < 6) {
				self::setByte($frame[$i], 8, $v);
			} else {
				self::setByte($frame[$i + 1], 8, $v);
			}
			$format = $format >> 1;
		}
		for ($i=0; $i<7; ++$i) {
		if (($format & 1) != 0) {
			$blacks += 2;
			$v = 0x85;
		} else {
			$v = 0x84;
		}
		self::setByte($frame[$width - 7 + $i], 8, $v);
		if ($i == 0) {
			self::setByte($frame[8], 7, $v);
		} else {
			self::setByte($frame[8], 6 - $i, $v);
		}
		$format = $format >> 1;
		}
		return $blacks;
	}
	
	
	/**
	 * @param int $size
	 * @return string[int]
	 */
	private static function newBitmap($size) {
		$row = str_repeat("\0", $size);
		$m = /*. (string[int]) .*/ array();
		for($i=0; $i<$size; $i++)
			$m[] = $row;
		return $m;
	}
	

	/**
	 * Return bitmask
	 * @param int $maskNo mask number
	 * @param int $width width
	 * @param string[int] $frame frame
	 * @return int[int][int] bitmask
	 */
	private function generateMaskNo($maskNo, $width, $frame) {
		$bitMask = self::newMatrix($width);
		for ($y=0; $y<$width; ++$y) {
			for ($x=0; $x<$width; ++$x) {
				if ((ord($frame[$y][$x]) & 0x80) != 0) {
					$bitMask[$y][$x] = 0;
				} else {
					switch($maskNo){
						case 0: $maskFunc = ($x + $y) & 1;  break;
						case 1: $maskFunc = $y & 1;  break;
						case 2: $maskFunc = $x % 3;  break;
						case 3: $maskFunc = ($x + $y) % 3;  break;
						case 4: $maskFunc = (((int)($y / 2)) + ((int)($x / 3))) & 1;  break;
						case 5: $maskFunc = (($x * $y) & 1) + ($x * $y) % 3;  break;
						case 6: $maskFunc = ((($x * $y) & 1) + ($x * $y) % 3) & 1;  break;
						case 7: $maskFunc = ((($x * $y) % 3) + (($x + $y) & 1)) & 1;  break;
						default: throw new RuntimeException();
					}
					$bitMask[$y][$x] = ($maskFunc == 0)? 1 : 0;
				}
			}
		}
		return $bitMask;
	}

	/**
	 * @param int $maskNo
	 * @param int $width
	 * @param string[int] $s
	 * @param string[int] & $d
	 * @return int b
	 */
	private function makeMaskNo($maskNo, $width, $s, & $d) {
		$b = 0;
		$bitMask = $this->generateMaskNo($maskNo, $width, $s);
		$d = $s;
		for ($y=0; $y<$width; ++$y) {
			for ($x=0; $x<$width; ++$x) {
				if ($bitMask[$y][$x] == 1) {
					self::setByte($d[$y], $x, ord($s[$y][$x]) ^ $bitMask[$y][$x]);
				}
				$b += (ord($d[$y][$x]) & 1);
			}
		}
		return $b;
	}

	/**
	 * @param int $width
	 * @param string[int] $frame
	 * @param int $maskNo
	 * @param int $level
	 * @return string[int] mask
	 */
	private function makeMask($width, $frame, $maskNo, $level) {
		$masked = self::newBitmap($width);
		$this->makeMaskNo($maskNo, $width, $frame, $masked);
		$this->writeFormatInformation($width, $masked, $maskNo, $level);
		return $masked;
	}

	/**
	 * @param int $length
	 * @return int demerit
	 */
	private function calcN1N3($length) {
		$demerit = 0;
		for ($i=0; $i<$length; ++$i) {
			if ($this->runLength[$i] >= 5) {
				$demerit += (self::N1 + ($this->runLength[$i] - 5));
			}
			if (($i & 1) != 0) {
				if (($i >= 3) and ($i < ($length-2)) and ($this->runLength[$i] % 3 == 0)) {
					$fact = (int)($this->runLength[$i] / 3);
					if (($this->runLength[$i-2] == $fact)
						and ($this->runLength[$i-1] == $fact)
						and ($this->runLength[$i+1] == $fact)
						and ($this->runLength[$i+2] == $fact)) {
						if (($this->runLength[$i-3] < 0) or ($this->runLength[$i-3] >= (4 * $fact))) {
							$demerit += self::N3;
						} elseif ((($i+3) >= $length) or ($this->runLength[$i+3] >= (4 * $fact))) {
							$demerit += self::N3;
						}
					}
				}
			}
		}
		return $demerit;
	}

	/**
	 * @param int $width
	 * @param string[int] $frame
	 * @return int demerit
	 */
	private function evaluateSymbol($width, $frame) {
		$head = 0;
		$demerit = 0;
		$frameYM = "";
		for ($y=0; $y<$width; ++$y) {
			$head = 0;
			$this->runLength[0] = 1;
			$frameY = $frame[$y];
			if ($y > 0) {
				$frameYM = $frame[$y-1];
			}
			for ($x=0; $x<$width; ++$x) {
				if (($x > 0) and ($y > 0)) {
					$b22 = ord($frameY[$x]) & ord($frameY[$x-1]) & ord($frameYM[$x]) & ord($frameYM[$x-1]);
					$w22 = ord($frameY[$x]) | ord($frameY[$x-1]) | ord($frameYM[$x]) | ord($frameYM[$x-1]);
					if ((($b22 | ($w22 ^ 1)) & 1) != 0) {
						$demerit += self::N2;
					}
				}
				if (($x == 0) and (ord($frameY[$x]) & 1) != 0) {
					$this->runLength[0] = -1;
					$head = 1;
					$this->runLength[$head] = 1;
				} elseif ($x > 0) {
					if (((ord($frameY[$x]) ^ ord($frameY[$x-1])) & 1) != 0) {
						$head++;
						$this->runLength[$head] = 1;
					} else {
						$this->runLength[$head]++;
					}
				}
			}
			$demerit += $this->calcN1N3($head+1);
		}
		for ($x=0; $x<$width; ++$x) {
			$head = 0;
			$this->runLength[0] = 1;
			for ($y=0; $y<$width; ++$y) {
				if (($y == 0) and (ord($frame[$y][$x]) & 1) != 0) {
					$this->runLength[0] = -1;
					$head = 1;
					$this->runLength[$head] = 1;
				} elseif ($y > 0) {
					if (((ord($frame[$y][$x]) ^ ord($frame[$y-1][$x])) & 1) != 0) {
						$head++;
						$this->runLength[$head] = 1;
					} else {
						$this->runLength[$head]++;
					}
				}
			}
			$demerit += $this->calcN1N3($head+1);
		}
		return $demerit;
	}

	/**
	 * @param int $width
	 * @param string[int] $frame
	 * @param int $level
	 * @return string[int] best mask
	 */
	private function mask($width, $frame, $level) {
		$minDemerit = PHP_INT_MAX;
		$checked_masks = array(0, 1, 2, 3, 4, 5, 6, 7);
		if (self::FIND_FROM_RANDOM !== false) {
			$howManuOut = 8 - (self::FIND_FROM_RANDOM % 9);
			for ($i = 0; $i <  $howManuOut; ++$i) {
				$remPos = mt_rand(0, count($checked_masks)-1);
				unset($checked_masks[$remPos]);
				$checked_masks = cast("int[int]", array_values($checked_masks));
			}
		}
		$bestMask = $frame;
		foreach ($checked_masks as $i) {
			$mask = self::newBitmap($width);
			$demerit = 0;
			$blacks = 0;
			$blacks  = $this->makeMaskNo($i, $width, $frame, $mask);
			$blacks += $this->writeFormatInformation($width, $mask, $i, $level);
			$blacks  = (int)(100 * $blacks / ($width * $width));
			$demerit = (int)(abs($blacks - 50) / 5) * self::N4;
			$demerit += $this->evaluateSymbol($width, $mask);
			if ($demerit < $minDemerit) {
				$minDemerit = $demerit;
				$bestMask = $mask;
			}
		}
		return $bestMask;
	}

	/**
	 * @param string $c
	 * @return boolean
	 */
	private static function isDigit($c) {
		return ord('0') <= ord($c) && ord($c) <= ord('9');
	}

	/**
	 * @param string $c
	 * @return boolean
	 */
	private static function isAlpha($c) {
		 return strpos("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ \$%*+-./:", $c) !== FALSE;
	}

	/**
	 * @param int $pos
	 * @return int mode
	 */
	private function identifyMode($pos) {
		if ($pos >= strlen($this->dataStr)) {
			return self::MODE_END_OF_PAYLOAD;
		}
		$c = $this->dataStr[$pos];
		if (self::isDigit($c)) {
			return self::MODE_NM;
		} elseif (self::isAlpha($c)) {
			return self::MODE_AN;
		} elseif ($this->detectKanji) {
			if ($pos+1 < strlen($this->dataStr)) {
				$d = $this->dataStr[$pos+1];
				$word = (ord($c) << 8) | ord($d);
				if (($word >= 0x8140 && $word <= 0x9ffc) or ($word >= 0xe040 && $word <= 0xebbf)) {
					return self::MODE_KJ;
				}
			}
		}
		return self::MODE_8B;
	}

	/**
	 * Return the size of length indicator for the mode and version.
	 * @param int $mode encoding mode
	 * @param int $version version
	 * @return int the size of the appropriate length indicator (bits).
	 */
	private function lengthIndicator($mode, $version) {
		if ($mode == self::MODE_ST) {
			return 0;
		}
		if ($version <= 9) {
			$l = 0;
		} elseif ($version <= 26) {
			$l = 1;
		} else {
			$l = 2;
		}
		return self::$lengthTableBits[$mode][$l];
	}

	/**
	 * @param int $size
	 * @return int number of bits
	 */
	private function estimateBitsModeNum($size) {
		$w = (int)($size / 3);
		$bits = 10 * $w;
		switch($size % 3) {
			case 0:
				break;
			case 1: {
				$bits += 4;
				break;
			}
			case 2: {
				$bits += 7;
				break;
			}
			/*. missing_default: .*/
		}
		return $bits;
	}

	/**
	 * @param int $size
	 * @return int number of bits
	 */
	private function estimateBitsModeAn($size) {
		$bits = (int)($size * 5.5); // (size / 2 ) * 11
		if (($size & 1) != 0) {
			$bits += 6;
		}
		return $bits;
	}

	/**
	 * estimateBitsMode8
	 * @param int $size
	 * @return int number of bits
	 */
	private function estimateBitsMode8($size) {
		return $size * 8;
	}

	/**
	 * @param int $size
	 * @return int number of bits
	 */
	private function estimateBitsModeKanji($size) {
		return (int)($size * 6.5); // (size / 2 ) * 13
	}

	/**
	 * @param int $size
	 * @param string[int] $data
	 * @return boolean
	 */
	private function checkModeNum($size, $data) {
		for ($i=0; $i<$size; ++$i) {
			if ((ord($data[$i]) < ord('0')) or (ord($data[$i]) > ord('9'))){
				return false;
			}
		}
		return true;
	}

	/**
	 * Look up the alphabet-numeric conversion table (see JIS X0510:2004, pp.19).
	 * @param int $b byte value
	 * @return int Encoded alphanumeric value, or -1 if the byte does not belong
	 * to the alphanumeric subset.
	 */
	private static function lookAnTable($b) {
		return $b > 127? -1 : self::$anTable[$b];
	}

	/**
	 * @param int $size
	 * @param string[int] $data
	 * @return boolean
	 */
	private function checkModeAn($size, $data) {
		for ($i=0; $i<$size; ++$i) {
			if (self::lookAnTable(ord($data[$i])) == -1) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param int $size
	 * @param string[int] $data
	 * @return boolean
	 */
	private function checkModeKanji($size, $data) {
		if (($size & 1) != 0) {
			return false;
		}
		for ($i=0; $i<$size; $i+=2) {
			$val = (ord($data[$i]) << 8) | ord($data[$i+1]);
			if (($val < 0x8140) or (($val > 0x9ffc) and ($val < 0xe040)) or ($val > 0xebbf)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Validate the input data.
	 * @param int $mode encoding mode.
	 * @param int $size size of data (byte).
	 * @param string[int] $data data to validate
	 * @return boolean true in case of valid data, false otherwise
	 */
	private function check($mode, $size, $data) {
		if ($size <= 0) {
			return false;
		}
		switch($mode) {
			case self::MODE_NM: {
				return $this->checkModeNum($size, $data);
			}
			case self::MODE_AN: {
				return $this->checkModeAn($size, $data);
			}
			case self::MODE_KJ: {
				return $this->checkModeKanji($size, $data);
			}
			case self::MODE_8B: {
				return true;
			}
			case self::MODE_ST: {
				return true;
			}
			default: {
				break;
			}
		}
		return false;
	}

	/**
	 * @param QRItem[int] $items
	 * @param int $version
	 * @return int bits
	 */
	private function estimateBitStreamSize($items, $version) {
		$bits = 0;
		if ($version == 0) {
			$version = 1;
		}
		foreach ($items as $item) {
			switch($item->mode) {
				case self::MODE_NM: {
					$bits = $this->estimateBitsModeNum($item->size);
					break;
				}
				case self::MODE_AN: {
					$bits = $this->estimateBitsModeAn($item->size);
					break;
				}
				case self::MODE_8B: {
					$bits = $this->estimateBitsMode8($item->size);
					break;
				}
				case self::MODE_KJ: {
					$bits = $this->estimateBitsModeKanji($item->size);
					break;
				}
				case self::MODE_ST: {
					return self::STRUCTURE_HEADER_BITS;
				}
				default: {
					return 0;
				}
			}
			$l = $this->lengthIndicator($item->mode, $version);
			$m = 1 << $l;
			$num = (int)(($item->size + $m - 1) / $m);
			$bits += $num * (4 + $l);
		}
		return $bits;
	}

	/**
	 * @param int $mode
	 * @param int $size
	 * @param string[int] $data
	 * @param int[int] $bstream
	 * @return QRItem input item
	 */
	private function newInputItem($mode, $size, $data, $bstream=null) {
		$setData = cast("string[int]", array_slice($data, 0, $size));
		if (count($setData) < $size) {
			$setData = cast("string[int]", array_merge($setData, self::newArrayInt($size - count($setData))));
		}
		if (!$this->check($mode, $size, $setData)) {
			return NULL; // FIXME: exception?
		}
		return new QRItem($mode, $size, $setData, $bstream);
	}

	/**
	 * @return int run
	 */
	private function eat8() {
		$la = $this->lengthIndicator(self::MODE_AN, $this->version);
		$ln = $this->lengthIndicator(self::MODE_NM, $this->version);
		$p = 0;
		$s = $this->dataStr;
		$slen = strlen($s);
		while($p < $slen) {
			$mode = $this->identifyMode($p);
			if ($mode == self::MODE_KJ) {
				break;
			}
			if ($mode == self::MODE_NM) {
				$q = $p;
				while($q < $slen && self::isDigit($s[$q])) {
					$q++;
				}
				$dif = $this->estimateBitsMode8($p) // + 4 + l8
				+ $this->estimateBitsModeNum($q - $p) + 4 + $ln
				- $this->estimateBitsMode8($q); // - 4 - l8
				if ($dif < 0) {
					break;
				} else {
					$p = $q;
				}
			} elseif ($mode == self::MODE_AN) {
				$q = $p;
				while($q < $slen && self::isAlpha($s[$q])) {
					$q++;
				}
				$dif = $this->estimateBitsMode8($p)  // + 4 + l8
				+ $this->estimateBitsModeAn($q - $p) + 4 + $la
				- $this->estimateBitsMode8($q); // - 4 - l8
				if ($dif < 0) {
					break;
				} else {
					$p = $q;
				}
			} else {
				$p++;
			}
		}
		$run = $p;
		$this->items[] = $this->newInputItem(self::MODE_8B, $run, str_split($s));
		return $run;
	}

	/**
	 * @return int run
	 */
	private function eatAn() {
		$la = $this->lengthIndicator(self::MODE_AN,  $this->version);
		$ln = $this->lengthIndicator(self::MODE_NM, $this->version);
		$s = $this->dataStr;
		$slen = strlen($s);
		$p = 0;
		while($p < $slen && self::isAlpha($s[$p])) {
			if (self::isDigit($this->dataStr[$p])) {
				$q = $p;
				while($q < $slen && self::isDigit($s[$q])) {
					$q++;
				}
				$dif = $this->estimateBitsModeAn($p) // + 4 + la
				+ $this->estimateBitsModeNum($q - $p) + 4 + $ln
				- $this->estimateBitsModeAn($q); // - 4 - la
				if ($dif < 0) {
					break;
				} else {
					$p = $q;
				}
			} else {
				$p++;
			}
		}
		$run = $p;
		if (!($p < $slen && self::isAlpha($s[$p]))) {
			$dif = $this->estimateBitsModeAn($run) + 4 + $la
			+ $this->estimateBitsMode8(1) // + 4 + l8
			- $this->estimateBitsMode8($run + 1); // - 4 - l8
			if ($dif > 0) {
				return $this->eat8();
			}
		}
		$this->items[] = $this->newInputItem(self::MODE_AN, $run, str_split($s));
		return $run;
	}

	/**
	 * @return int run
	 */
	private function eatKanji() {
		$p = 0;
		while($this->identifyMode($p) == self::MODE_KJ) {
			$p += 2;
		}
		$run = $p;
		$this->items[] = $this->newInputItem(self::MODE_KJ, $p, str_split($this->dataStr));
		return $run;
	}

	/**
	 * @return int run
	 */
	private function eatNum() {
		$ln = $this->lengthIndicator(self::MODE_NM, $this->version);
		$s = $this->dataStr;
		$slen = strlen($s);
		$p = 0;
		while($p < $slen && self::isDigit($s[$p])) {
			$p++;
		}
		$run = $p;
		$mode = $this->identifyMode($p);
		if ($mode == self::MODE_8B) {
			$dif = $this->estimateBitsModeNum($run) + 4 + $ln
			+ $this->estimateBitsMode8(1)         // + 4 + l8
			- $this->estimateBitsMode8($run + 1); // - 4 - l8
			if ($dif > 0) {
				return $this->eat8();
			}
		}
		if ($mode == self::MODE_AN) {
			$dif = $this->estimateBitsModeNum($run) + 4 + $ln
			+ $this->estimateBitsModeAn(1)        // + 4 + la
			- $this->estimateBitsModeAn($run + 1);// - 4 - la
			if ($dif > 0) {
				return $this->eatAn();
			}
		}
		$this->items[] = $this->newInputItem(self::MODE_NM, $run, str_split($s));
		return $run;
	}

	/**
	 */
	private function splitString() {
		while (strlen($this->dataStr) > 0) {
			$mode = $this->identifyMode(0);
			switch ($mode) {
				case self::MODE_NM:
					$length = $this->eatNum();
					break;
				case self::MODE_AN:
					$length = $this->eatAn();
					break;
				case self::MODE_KJ:
					$length = $this->eatKanji();
					break;
				default:
					$length = $this->eat8();
					break;
			}
			if ($length == 0)
				return;
			$this->dataStr = substr($this->dataStr, $length);
		}
	}
	
//	private function toUpper() {
//		$stringLen = strlen($this->dataStr);
//		$p = 0;
//		while ($p < $stringLen) {
//			$mode = $this->identifyMode(substr($this->dataStr, $p));
//			if ($mode == self::QR_MODE_KJ) {
//				$p += 2;
//			} else {
//				if ((ord($this->dataStr[$p]) >= ord('a')) and (ord($this->dataStr[$p]) <= ord('z'))) {
//					// FIXME: string is immutable under PHPLint
//					//$this->dataStr[$p] = chr(ord($this->dataStr[$p]) - 32);
//				}
//				$p++;
//			}
//		}
//		return $this->dataStr;
//	}

	/**
	 * Return a version number that satisfies the input code length.
	 * @param int $size input code length (bytes)
	 * @param int $level error correction level
	 * @return int minimum version number
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	private function getMinimumVersion($size, $level) {
		for ($i = 1; $i <= self::SPEC_VERSION_MAX; ++$i) {
			$words = (self::$capacity[$i][self::CAP_WORDS] - self::$ecc[$i][$level]);
			if ($words >= $size) {
				return $i;
			}
		}
		throw new QRCODECapacityException("size of input data is greater than QR capacity - try to lover the error correction mode");
	}

	/**
	 * @param QRItem[int] $items
	 * @return int estimated version number
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	private function estimateVersion($items) {
		$version = 0;
		$prev = 0;
		do {
			$prev = $version;
			$bits = $this->estimateBitStreamSize($items, $prev);
			$version = $this->getMinimumVersion((int)(($bits + 7) / 8), $this->level);
		} while ($version > $prev);
		return $version;
	}

	/**
	 * Return the maximum length for the mode and version.
	 * @param int $mode encoding mode
	 * @param int $version version
	 * @return int the maximum length (bytes)
	 */
	private function maximumBytesNo($mode, $version) {
		if ($mode == self::MODE_ST) {
			return 3;
		}
		if ($version <= 9) {
			$l = 0;
		} else if ($version <= 26) {
			$l = 1;
		} else {
			$l = 2;
		}
		$bits = self::$lengthTableBits[$mode][$l];
		$words = (1 << $bits) - 1;
		if ($mode == self::MODE_KJ) {
			$words *= 2; // the number of bytes is required
		}
		return $words;
	}
	

	/**
	 * Return new bitstream from number
	 * @param int $bits number of bits
	 * @param int $num number
	 * @return int[int] bitstream
	 */
	private function newFromNum($bits, $num) {
		$bstream = self::newArrayInt($bits);
		$mask = 1 << ($bits - 1);
		for ($i=0; $i<$bits; ++$i) {
			if (($num & $mask) != 0) {
				$bstream[$i] = 1;
			} else {
				$bstream[$i] = 0;
			}
			$mask = $mask >> 1;
		}
		return $bstream;
	}

	/**
	 * Return new bitstream from bytes
	 * @param int $size size
	 * @param int[int] $data bytes
	 * @return int[int] bitstream
	 */
	private function newFromBytes($size, $data) {
		$bstream = self::newArrayInt($size * 8);
		$p=0;
		for ($i=0; $i<$size; ++$i) {
			$mask = 0x80;
			for ($j=0; $j<8; ++$j) {
				if (($data[$i] & $mask) != 0) {
					$bstream[$p] = 1;
				} else {
					$bstream[$p] = 0;
				}
				$p++;
				$mask = $mask >> 1;
			}
		}
		return $bstream;
	}

	/**
	 * Append one bitstream to another
	 * @param int[int] $bitstream original bitstream
	 * @param int[int] $append bitstream to append
	 * @return int[int] bitstream
	 */
	private function appendBitstream($bitstream, $append) {
		if (count($append) == 0)
			return $bitstream;
		if (count($bitstream) == 0)
			return $append;
		return cast("int[int]", array_values(array_merge($bitstream, $append)));
	}

	/**
	 * Append one bitstream created from number to another
	 * @param int[int] $bitstream original bitstream
	 * @param int $bits number of bits
	 * @param int $num number
	 * @return int[int] bitstream
	 */
	private function appendNum($bitstream, $bits, $num) {
		$b = $this->newFromNum($bits, $num);
		return $this->appendBitstream($bitstream, $b);
	}

	/**
	 * Append one bitstream created from bytes to another
	 * @param int[int] $bitstream original bitstream
	 * @param int $size size
	 * @param int[int] $data bytes
	 * @return int[int] bitstream
	 */
	private function appendBytes($bitstream, $size, $data) {
		$b = $this->newFromBytes($size, $data);
		return $this->appendBitstream($bitstream, $b);
	}

	/**
	 * @param QRItem $inputitem
	 * @param int $version
	 * @return QRItem
	 */
	private function encodeModeNum($inputitem, $version) {
		$words = (int)($inputitem->size / 3);
		$inputitem->bstream = array();
		$val = 0x1;
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, $val);
		$inputitem->bstream = $this->appendNum($inputitem->bstream, $this->lengthIndicator(self::MODE_NM, $version), $inputitem->size);
		for ($i=0; $i < $words; ++$i) {
			$val  = (ord($inputitem->data[$i*3  ]) - ord('0')) * 100;
			$val += (ord($inputitem->data[$i*3+1]) - ord('0')) * 10;
			$val += (ord($inputitem->data[$i*3+2]) - ord('0'));
			$inputitem->bstream = $this->appendNum($inputitem->bstream, 10, $val);
		}
		if ($inputitem->size - $words * 3 == 1) {
			$val = ord($inputitem->data[$words*3]) - ord('0');
			$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, $val);
		} elseif (($inputitem->size - ($words * 3)) == 2) {
			$val  = (ord($inputitem->data[$words*3  ]) - ord('0')) * 10;
			$val += (ord($inputitem->data[$words*3+1]) - ord('0'));
			$inputitem->bstream = $this->appendNum($inputitem->bstream, 7, $val);
		}
		return $inputitem;
	}

	/**
	 * @param QRItem $inputitem
	 * @param int $version
	 * @return QRItem
	 */
	private function encodeModeAn($inputitem, $version) {
		$words = (int)($inputitem->size / 2);
		$inputitem->bstream = array();
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, 0x02);
		$inputitem->bstream = $this->appendNum($inputitem->bstream, $this->lengthIndicator(self::MODE_AN, $version), $inputitem->size);
		for ($i=0; $i < $words; ++$i) {
			$val  = self::lookAnTable(ord($inputitem->data[$i*2])) * 45;
			$val += self::lookAnTable(ord($inputitem->data[($i*2)+1]));
			$inputitem->bstream = $this->appendNum($inputitem->bstream, 11, $val);
		}
		if (($inputitem->size & 1) != 0) {
			$val = self::lookAnTable(ord($inputitem->data[($words * 2)]));
			$inputitem->bstream = $this->appendNum($inputitem->bstream, 6, $val);
		}
		return $inputitem;
	}

	/**
	 * @param QRItem $inputitem
	 * @param int $version
	 * @return QRItem
	 */
	private function encodeMode8($inputitem, $version) {
		$inputitem->bstream = array();
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, 0x4);
		$inputitem->bstream = $this->appendNum($inputitem->bstream, $this->lengthIndicator(self::MODE_8B, $version), $inputitem->size);
		for ($i=0; $i < $inputitem->size; ++$i) {
			$inputitem->bstream = $this->appendNum($inputitem->bstream, 8, ord($inputitem->data[$i]));
		}
		return $inputitem;
	}

	/**
	 * @param QRItem $inputitem
	 * @param int $version
	 * @return QRItem
	 */
	private function encodeModeKanji($inputitem, $version) {
		$inputitem->bstream = array();
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, 0x8);
		$inputitem->bstream = $this->appendNum($inputitem->bstream, $this->lengthIndicator(self::MODE_KJ, $version), (int)($inputitem->size / 2));
		for ($i=0; $i<$inputitem->size; $i+=2) {
			$val = (ord($inputitem->data[$i]) << 8) | ord($inputitem->data[$i+1]);
			if ($val <= 0x9ffc) {
				$val -= 0x8140;
			} else {
				$val -= 0xc140;
			}
			$h = ($val >> 8) * 0xc0;
			$val = ($val & 0xff) + $h;
			$inputitem->bstream = $this->appendNum($inputitem->bstream, 13, $val);
		}
		return $inputitem;
	}

	/**
	 * @param QRItem $inputitem
	 * @return QRItem
	 */
	private function encodeModeStructure($inputitem) {
		$inputitem->bstream = array();
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, 0x03);
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, ord($inputitem->data[1]) - 1);
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 4, ord($inputitem->data[0]) - 1);
		$inputitem->bstream = $this->appendNum($inputitem->bstream, 8, ord($inputitem->data[2]));
		return $inputitem;
	}

	/**
	 * @param QRItem $inputitem
	 * @param int $version
	 * @return QRItem
	 */
	private function encodeBitStream($inputitem, $version) {
		$inputitem->bstream = array();
		$words = $this->maximumBytesNo($inputitem->mode, $version);
		if ($inputitem->size > $words) {
			$st1 = $this->newInputItem($inputitem->mode, $words, $inputitem->data);
			$st2 = $this->newInputItem($inputitem->mode, $inputitem->size - $words, cast("string[int]", array_slice($inputitem->data, $words)));
			$st1 = $this->encodeBitStream($st1, $version);
			$st2 = $this->encodeBitStream($st2, $version);
			$inputitem->bstream = array();
			$inputitem->bstream = $this->appendBitstream($inputitem->bstream, $st1->bstream);
			$inputitem->bstream = $this->appendBitstream($inputitem->bstream, $st2->bstream);
		} else {
			switch($inputitem->mode) {
				case self::MODE_NM: {
					$inputitem = $this->encodeModeNum($inputitem, $version);
					break;
				}
				case self::MODE_AN: {
					$inputitem = $this->encodeModeAn($inputitem, $version);
					break;
				}
				case self::MODE_8B: {
					$inputitem = $this->encodeMode8($inputitem, $version);
					break;
				}
				case self::MODE_KJ: {
					$inputitem = $this->encodeModeKanji($inputitem, $version);
					break;
				}
				case self::MODE_ST: {
					$inputitem = $this->encodeModeStructure($inputitem);
					break;
				}
				default: {
					break;
				}
			}
		}
		return $inputitem;
	}

	/**
	 * @param QRItem[int] $items
	 * @param int & $bits returns total bits
	 * @return QRItem[int] items
	 */
	private function createBitStream($items, /*. return .*/ & $bits) {
		$total = 0;
		foreach ($items as $key => $item) {
			$items[$key] = $this->encodeBitStream($item, $this->version);
			$total += count($items[$key]->bstream);
		}
		$bits = $total;
		return $items;
	}

	/**
	 * @param QRItem[int] $items
	 * @return QRItem[int] items
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	private function convertData($items) {
		$ver = $this->estimateVersion($items);
		if ($ver > $this->version) {
			$this->version = $ver;
		}
		while (true) {
			$items = $this->createBitStream($items, $bits);
			if ($bits < 0)
				throw new RuntimeException(); // FIXME: why exception here?
			$ver = $this->getMinimumVersion((int)(($bits + 7) / 8), $this->level);
			if ($ver > $this->version) {
				$this->version = $ver;
			} else {
				break;
			}
		}
		return $items;
	}

	/**
	 * @param QRItem[int] $items items
	 * @return int[int] bitstream
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	private function mergeBitStream($items) {
		$items = $this->convertData($items);
		if (!is_array($items)) {
			return null; // FIXME: ex?
		}
		$bstream = /*. (int[int]) .*/ array();
		foreach ($items as $item) {
			$bstream = $this->appendBitstream($bstream, $item->bstream);
		}
		return $bstream;
	}

//	/**
//	 * Encode the input string to QR code
//	 * @param string $s input string to encode
//	 */
//	private function encodeString($s) {
//		$this->dataStr = $s;
//		if (!$this->casesensitive) {
//			$this->toUpper();
//		}
//		$ret = $this->splitString();
//		if ($ret < 0) {
//			return NULL;
//		}
//		$this->encodeMask(-1);
//	}

//	/**
//	 * @param int[int] $items
//	 * @param int $size
//	 * @param int $index
//	 * @param int $parity
//	 * @return array items
//	 */
//	private function insertStructuredAppendHeader($items, $size, $index, $parity) {
//		if ($size > MAX_STRUCTURED_SYMBOLS) {
//			return -1;
//		}
//		if (($index <= 0) or ($index > MAX_STRUCTURED_SYMBOLS)) {
//			return -1;
//		}
//		$buf = array($size, $index, $parity);
//		$entry = $this->newInputItem(self::QR_MODE_ST, 3, buf);
//		array_unshift($items, $entry);
//		return $items;
//	}

//	/**
//	 * @param int[int] $items
//	 * @return int parity
//	 */
//	private function calcParity($items) {
//		$parity = 0;
//		foreach ($items as $item) {
//			if ($item->mode != self::QR_MODE_ST) {
//				for ($i=$item->size-1; $i>=0; --$i) {
//					$parity ^= $item->data[$i];
//				}
//			}
//		}
//		return $parity;
//	}

//	/**
//	 * @param int $mode
//	 * @param int $version
//	 * @param int $bits
//	 * @return int size
//	 */
//	private function lengthOfCode($mode, $version, $bits) {
//		$payload = $bits - 4 - $this->lengthIndicator($mode, $version);
//		switch($mode) {
//			case self::QR_MODE_NM: {
//				$chunks = (int)($payload / 10);
//				$remain = $payload - $chunks * 10;
//				$size = $chunks * 3;
//				if ($remain >= 7) {
//					$size += 2;
//				} elseif ($remain >= 4) {
//					$size += 1;
//				}
//				break;
//			}
//			case self::QR_MODE_AN: {
//				$chunks = (int)($payload / 11);
//				$remain = $payload - $chunks * 11;
//				$size = $chunks * 2;
//				if ($remain >= 6) {
//					++$size;
//				}
//				break;
//			}
//			case self::QR_MODE_8B: {
//				$size = (int)($payload / 8);
//				break;
//			}
//			case self::QR_MODE_KJ: {
//				$size = (int)(($payload / 13) * 2);
//				break;
//			}
//			case self::QR_MODE_ST: {
//				$size = (int)($payload / 8);
//				break;
//			}
//			default: {
//				$size = 0;
//				break;
//			}
//		}
//		$maxsize = $this->maximumWords($mode, $version);
//		if ($size < 0) {
//			$size = 0;
//		}
//		if ($size > $maxsize) {
//			$size = $maxsize;
//		}
//		return $size;
//	}

	/**
	 * Convert bitstream to bytes
	 * @param int[int] $bstream original bitstream
	 * @return int[int] of bytes
	 */
	private function bitstreamToByte($bstream) {
		if (is_null($bstream)) {
	 		return null;
	 	}
		$size = count($bstream);
		if ($size == 0) {
			return array();
		}
		$data = self::newArrayInt((int)(($size + 7) / 8));
		$bytes = (int)($size / 8);
		$p = 0;
		for ($i=0; $i<$bytes; $i++) {
			$v = 0;
			for ($j=0; $j<8; $j++) {
				$v = $v << 1;
				$v |= $bstream[$p];
				$p++;
			}
			$data[$i] = $v;
		}
		if (($size & 7) != 0) {
			$v = 0;
			for ($j=0; $j<($size & 7); $j++) {
				$v = $v << 1;
				$v |= $bstream[$p];
				$p++;
			}
			$data[$bytes] = $v;
		}
		return $data;
	}

	/**
	 * Replace a value on the array at the specified position
	 * @param string[int] $srctab
	 * @param int $x X position
	 * @param int $y Y position
	 * @param string $repl value to replace
	 * @param int $replLen length of the repl string
	 * @return string[int] srctab
	 */
	private function qrstrset($srctab, $x, $y, $repl, $replLen=-1) {
		$srctab[$y] = (string) substr_replace($srctab[$y],
			($replLen >= 0)? substr($repl,0,$replLen) : $repl, $x,
			($replLen >= 0)? $replLen : strlen($repl));
		return $srctab;
	}

	/**
	 * Return maximum data code length (bytes) for the version.
	 * @param int $version version
	 * @param int $level error correction level
	 * @return int maximum size (bytes)
	 */
	private function getDataLength($version, $level) {
		return self::$capacity[$version][self::CAP_WORDS] - self::$ecc[$version][$level];
	}

	/**
	 * Return maximum error correction code length (bytes) for the version.
	 * @param int $version version
	 * @param int $level error correction level
	 * @return int ECC size (bytes)
	 */
	private function getECCLength($version, $level){
		return self::$ecc[$version][$level];
	}

	/**
	 * Return the width of the symbol for the version.
	 * @param int $version version
	 * @return int width
	 */
	private function getWidth($version) {
		return self::$capacity[$version][self::CAP_WIDTH];
	}

	/**
	 * Return the numer of remainder bits.
	 * @param int $version version
	 * @return int number of remainder bits
	 */
	private function getRemainder($version) {
		return self::$capacity[$version][self::CAP_REMINDER];
	}

	/**
	 * Return an array of ECC specification.
	 * @param int $version version
	 * @param int $level error correction level
	 * @param int[int] $spec an array of ECC specification contains as following: {# of type1 blocks, # of data code, # of ecc code, # of type2 blocks, # of data code}
	 * @return int[int] spec
	 */
	private function getEccSpec($version, $level, $spec) {
		if (count($spec) < 5) {
			$spec = array(0, 0, 0, 0, 0);
		}
		$b1 = self::$eccTable[$version][$level][0];
		$b2 = self::$eccTable[$version][$level][1];
		$data = $this->getDataLength($version, $level);
		$ecc = $this->getECCLength($version, $level);
		if ($b2 == 0) {
			$spec[0] = $b1;
			$spec[1] = (int)($data / $b1);
			$spec[2] = (int)($ecc / $b1);
			$spec[3] = 0;
			$spec[4] = 0;
		} else {
			$spec[0] = $b1;
			$spec[1] = (int)($data / ($b1 + $b2));
			$spec[2] = (int)($ecc  / ($b1 + $b2));
			$spec[3] = $b2;
			$spec[4] = $spec[1] + 1;
		}
		return $spec;
	}

	/**
	 * Append Padding Bit to bitstream
	 * @param int[int] $bstream
	 * @return int[int] bitstream
	 */
	private function appendPaddingBit($bstream) {
	 	if (is_null($bstream)) {
	 		return null;
	 	}
		$bits = count($bstream);
		$maxwords = $this->getDataLength($this->version, $this->level);
		$maxbits = $maxwords * 8;
		if ($maxbits == $bits) {
			return $bstream;
		}
		if ($maxbits - $bits < 5) {
			return $this->appendNum($bstream, $maxbits - $bits, 0);
		}
		$bits += 4;
		$words = (int)(($bits + 7) / 8);
		$padding = /*. (int[int]) .*/ array();
		$padding = $this->appendNum($padding, $words * 8 - $bits + 4, 0);
		$padlen = $maxwords - $words;
		if ($padlen > 0) {
			$padbuf = /*. (int[int]) .*/ array();
			for ($i=0; $i<$padlen; ++$i) {
				$padbuf[$i] = ($i & 1) != 0? 0x11 : 0xec;
			}
			$padding = $this->appendBytes($padding, $padlen, $padbuf);
		}
		return $this->appendBitstream($bstream, $padding);
	}

	/**
	 * Returns a stream of bits.
	 * @param QRItem[int] $items
	 * @return int[int] padded merged byte stream
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	private function getBitStream($items) {
		$bstream = $this->mergeBitStream($items);
		return $this->appendPaddingBit($bstream);
	}

	/**
	 * Pack all bit streams padding bits into a byte array.
	 * @param QRItem[int] $items
	 * @return int[int] padded merged byte stream
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	private function getByteStream($items) {
		$bstream = $this->getBitStream($items);
		return $this->bitstreamToByte($bstream);
	}

	/**
	 * Put an alignment marker.
	 * @param string[int] $frame frame
	 * @param int $ox X center coordinate of the pattern
	 * @param int $oy Y center coordinate of the pattern
	 * @return string[int] frame
	 */
	private function putAlignmentMarker($frame, $ox, $oy) {
		$finder = array(
			"\xa1\xa1\xa1\xa1\xa1",
			"\xa1\xa0\xa0\xa0\xa1",
			"\xa1\xa0\xa1\xa0\xa1",
			"\xa1\xa0\xa0\xa0\xa1",
			"\xa1\xa1\xa1\xa1\xa1"
			);
		$yStart = $oy - 2;
		$xStart = $ox - 2;
		for ($y=0; $y < 5; $y++) {
			$frame = $this->qrstrset($frame, $xStart, $yStart+$y, $finder[$y]);
		}
		return $frame;
	}

	/**
	 * Put an alignment pattern.
	 * @param int $version version
	 * @param string[int] $frame frame
	 * @param int $width width
	 * @return string[int] frame
	 */
	private function putAlignmentPattern($version, $frame, $width) {
		if ($version < 2) {
			return $frame;
		}
		$d = self::$alignmentPattern[$version][1] - self::$alignmentPattern[$version][0];
		if ($d < 0) {
			$w = 2;
		} else {
			$w = (int)(($width - self::$alignmentPattern[$version][0]) / $d + 2);
		}
		if ($w * $w - 3 == 1) {
			$x = self::$alignmentPattern[$version][0];
			$y = self::$alignmentPattern[$version][0];
			$frame = $this->putAlignmentMarker($frame, $x, $y);
			return $frame;
		}
		$cx = self::$alignmentPattern[$version][0];
		$wo = $w - 1;
		for ($x=1; $x < $wo; ++$x) {
			$frame = $this->putAlignmentMarker($frame, 6, $cx);
			$frame = $this->putAlignmentMarker($frame, $cx,  6);
			$cx += $d;
		}
		$cy = self::$alignmentPattern[$version][0];
		for ($y=0; $y < $wo; ++$y) {
			$cx = self::$alignmentPattern[$version][0];
			for ($x=0; $x < $wo; ++$x) {
				$frame = $this->putAlignmentMarker($frame, $cx, $cy);
				$cx += $d;
			}
			$cy += $d;
		}
		return $frame;
	}

	/**
	 * Put a finder pattern.
	 * @param string[int] $frame frame
	 * @param int $ox X center coordinate of the pattern
	 * @param int $oy Y center coordinate of the pattern
	 * @return string[int] frame
	 */
	private function putFinderPattern($frame, $ox, $oy) {
		$finder = array(
		"\xc1\xc1\xc1\xc1\xc1\xc1\xc1",
		"\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
		"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
		"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
		"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
		"\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
		"\xc1\xc1\xc1\xc1\xc1\xc1\xc1"
		);
		for ($y=0; $y < 7; $y++) {
			$frame = $this->qrstrset($frame, $ox, ($oy + $y), $finder[$y]);
		}
		return $frame;
	}

	/**
	 * Return a copy of initialized frame.
	 * @param int $version version
	 * @return string[int]
	 */
	private function createFrame($version) {
		$width = self::$capacity[$version][self::CAP_WIDTH];
		$frame = self::newBitmap($width);
		// Finder pattern
		$frame = $this->putFinderPattern($frame, 0, 0);
		$frame = $this->putFinderPattern($frame, $width - 7, 0);
		$frame = $this->putFinderPattern($frame, 0, $width - 7);
		// Separator
		$yOffset = $width - 7;
		for ($y=0; $y < 7; ++$y) {
			self::setByte($frame[$y], 7, 0xc0);
			self::setByte($frame[$y], $width - 8, 0xc0);
			self::setByte($frame[$yOffset], 7, 0xc0);
			++$yOffset;
		}
		$setPattern = str_repeat("\xc0", 8);
		$frame = $this->qrstrset($frame, 0, 7, $setPattern);
		$frame = $this->qrstrset($frame, $width-8, 7, $setPattern);
		$frame = $this->qrstrset($frame, 0, $width - 8, $setPattern);
		// Format info
		$setPattern = str_repeat("\x84", 9);
		$frame = $this->qrstrset($frame, 0, 8, $setPattern);
		$frame = $this->qrstrset($frame, $width - 8, 8, $setPattern, 8);
		$yOffset = $width - 8;
		for ($y=0; $y < 8; ++$y,++$yOffset) {
			self::setByte($frame[$y], 8, 0x84);
			self::setByte($frame[$yOffset], 8, 0x84);
		}
		// Timing pattern
		$wo = $width - 15;
		for ($i=1; $i < $wo; ++$i) {
			self::setByte($frame[6], 7+$i, 0x90 | ($i & 1));
			self::setByte($frame[7+$i], 6, 0x90 | ($i & 1));
		}
		// Alignment pattern
		$frame = $this->putAlignmentPattern($version, $frame, $width);
		// Version information
		if ($version >= 7) {
			$vinf = $this->getVersionPattern($version);
			$v = $vinf;
			for ($x=0; $x<6; ++$x) {
				for ($y=0; $y<3; ++$y) {
					self::setByte($frame[($width - 11)+$y], $x, 0x88 | ($v & 1));
					$v = $v >> 1;
				}
			}
			$v = $vinf;
			for ($y=0; $y<6; ++$y) {
				for ($x=0; $x<3; ++$x) {
					self::setByte($frame[$y], $x+($width - 11), 0x88 | ($v & 1));
					$v = $v >> 1;
				}
			}
		}
		// and a little bit...
		self::setByte($frame[$width - 8], 8, 0x81);
		return $frame;
	}

	/**
	 * Set new frame for the specified version.
	 * @param int $version version
	 * @return string[int] Array of unsigned char.
	 */
	private function newFrame($version) {
		if (($version < 1) or ($version > self::SPEC_VERSION_MAX)) {
			return NULL;
		}
		if (!isset($this->frames[$version])) {
			$this->frames[$version] = $this->createFrame($version);
		}
		if (is_null($this->frames[$version])) {
			return NULL;
		}
		return $this->frames[$version];
	}

	/**
	 * Return block number 0
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsBlockNum($spec) {
		return ($spec[0] + $spec[3]);
	}

	/**
	* Return block number 1
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsBlockNum1($spec) {
		return $spec[0];
	}

	/**
	 * Return data codes 1
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsDataCodes1($spec) {
		return $spec[1];
	}

	/**
	 * Return ecc codes 1
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsEccCodes1($spec) {
		return $spec[2];
	}

	/**
	 * Return block number 2
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsBlockNum2($spec) {
		return $spec[3];
	}

	/**
	 * Return data codes 2
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsDataCodes2($spec) {
		return $spec[4];
	}

	/**
	 * Return ecc codes 2
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsEccCodes2($spec) {
		return $spec[2];
	}

	/**
	 * Return data length
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsDataLength($spec) {
		return ($spec[0] * $spec[1]) + ($spec[3] * $spec[4]);
	}

	/**
	 * Return ecc length
	 * @param int[int] $spec
	 * @return int value
	 */
	private function rsEccLength($spec) {
		return ($spec[0] + $spec[3]) * $spec[2];
	}

	/**
	 * @param RSCodec $rs
	 * @param int $x X position
	 * @return int X position
	 */
	private function modnn($rs, $x) {
		while ($x >= $rs->nn) {
			$x -= $rs->nn;
			$x = ($x >> $rs->mm) + ($x & $rs->nn);
		}
		return $x;
	}

	/**
	 * Initialize a Reed-Solomon codec and returns an array of values.
	 * @param int $symsize symbol size, bits
	 * @param int $gfpoly  Field generator polynomial coefficients
	 * @param int $fcr  first root of RS code generator polynomial, index form
	 * @param int $prim  primitive element to generate polynomial roots
	 * @param int $nroots RS code generator polynomial degree (number of roots)
	 * @param int $pad  padding bytes at front of shortened block
	 * @return RSCodec
	 */
	private function init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad) {
		// Based on Reed solomon encoder by Phil Karn, KA9Q (GNU-LGPLv2)
		// Check parameter ranges
		if (($symsize < 0) or ($symsize > 8)
		or ($fcr < 0) or ($fcr >= (1<<$symsize))
		or ($prim <= 0) or ($prim >= (1<<$symsize))
		or ($nroots < 0) or ($nroots >= (1<<$symsize))
		or ($pad < 0) or ($pad >= ((1<<$symsize) -1 - $nroots))
		)
			throw new RuntimeException();
		$rs = new RSCodec();
		$rs->mm = $symsize;
		$rs->nn = (1 << $symsize) - 1;
		$rs->pad = $pad;
		$rs->alpha_to = self::newArrayInt(($rs->nn + 1));
		$rs->index_of = self::newArrayInt(($rs->nn + 1));
		// PHP style macro replacement ;)
		$NN =& $rs->nn;
		$A0 =& $NN;
		// Generate Galois field lookup tables
		$rs->index_of[0] = $A0; // log(zero) = -inf
		$rs->alpha_to[$A0] = 0; // alpha**-inf = 0
		$sr = 1;
		for ($i=0; $i<$rs->nn; ++$i) {
			$rs->index_of[$sr] = $i;
			$rs->alpha_to[$i] = $sr;
			$sr <<= 1;
			if (($sr & (1 << $symsize)) != 0) {
				$sr ^= $gfpoly;
			}
			// FIXME: "Cannot convert to ordinal value"!?
			//$sr &= $rs->nn;
			// FIX:
			$sr = ($sr & $rs->nn);
		}
		if ($sr != 1)
			throw new RuntimeException("field generator polynomial is not primitive");
		// Form RS code generator polynomial from its roots
		$rs->genpoly = self::newArrayInt($nroots + 1);
		$rs->fcr = $fcr;
		$rs->prim = $prim;
		$rs->nroots = $nroots;
		$rs->gfpoly = $gfpoly;
		// Find prim-th root of 1, used in decoding
		for ($iprim=1; ($iprim % $prim) != 0; $iprim += $rs->nn) {
			; // intentional empty-body loop!
		}
		$rs->iprim = (int)($iprim / $prim);
		$rs->genpoly[0] = 1;
		for ($i = 0,$root=$fcr*$prim; $i < $nroots; $i++, $root += $prim) {
			$rs->genpoly[$i+1] = 1;
			// Multiply rs->genpoly[] by  @**(root + x)
			for ($j = $i; $j > 0; --$j) {
				if ($rs->genpoly[$j] != 0) {
					$rs->genpoly[$j] = $rs->genpoly[$j-1] ^ $rs->alpha_to[$this->modnn($rs, $rs->index_of[$rs->genpoly[$j]] + $root)];
				} else {
					$rs->genpoly[$j] = $rs->genpoly[$j-1];
				}
			}
			// rs->genpoly[0] can never be zero
			$rs->genpoly[0] = $rs->alpha_to[$this->modnn($rs, $rs->index_of[$rs->genpoly[0]] + $root)];
		}
		// convert rs->genpoly[] to index form for quicker encoding
		for ($i = 0; $i <= $nroots; ++$i) {
			$rs->genpoly[$i] = $rs->index_of[$rs->genpoly[$i]];
		}
		return $rs;
	}

	/**
	 * Initialize a Reed-Solomon codec and add it to existing rsitems
	 * @param int $symsize symbol size, bits
	 * @param int $gfpoly  Field generator polynomial coefficients
	 * @param int $fcr  first root of RS code generator polynomial, index form
	 * @param int $prim  primitive element to generate polynomial roots
	 * @param int $nroots RS code generator polynomial degree (number of roots)
	 * @param int $pad  padding bytes at front of shortened block
	 * @return RSCodec
	 */
	private function init_rs($symsize, $gfpoly, $fcr, $prim, $nroots, $pad) {
		foreach ($this->rsitems as $rs) {
			if (($rs->pad != $pad) or ($rs->nroots != $nroots)
			or ($rs->mm != $symsize) or ($rs->gfpoly != $gfpoly)
			or ($rs->fcr != $fcr) or ($rs->prim != $prim)) {
				continue;
			}
			return $rs;
		}
		$rs = $this->init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad);
		array_unshift($this->rsitems, $rs);
		return $rs;
	}

	/**
	 * Encode a Reed-Solomon codec and returns the parity array
	 * @param RSCodec $rs RS values
	 * @param int[int] $data data
	 * @param int[int] $parity parity
	 * @return int[int] parity array
	 */
	private function encode_rs_char($rs, $data, $parity) {
		$NN       =& $rs->nn;
		$ALPHA_TO =& $rs->alpha_to;
		$INDEX_OF =& $rs->index_of;
		$GENPOLY  =& $rs->genpoly;
		$NROOTS   =& $rs->nroots;
		$PAD      =& $rs->pad;
		$A0       =& $NN;
		$parity = self::newArrayInt($NROOTS);
		for ($i=0; $i < ($NN - $NROOTS - $PAD); $i++) {
			$feedback = $INDEX_OF[$data[$i] ^ $parity[0]];
			if ($feedback != $A0) {
				// feedback term is non-zero
				// This line is unnecessary when GENPOLY[NROOTS] is unity, as it must
				// always be for the polynomials constructed by init_rs()
				$feedback = $this->modnn($rs, $NN - $GENPOLY[$NROOTS] + $feedback);
				for ($j=1; $j < $NROOTS; ++$j) {
				$parity[$j] ^= $ALPHA_TO[$this->modnn($rs, $feedback + $GENPOLY[($NROOTS - $j)])];
				}
			}
			// Shift
			array_shift($parity);
			if ($feedback != $A0) {
				array_push($parity, $ALPHA_TO[$this->modnn($rs, $feedback + $GENPOLY[0])]);
			} else {
				array_push($parity, 0);
			}
		}
		return $parity;
	}
	

	/**
	 * Initialize code.
	 * @param int[int] $spec array of ECC specification
	 */
	private function init($spec) {
		$dl = $this->rsDataCodes1($spec);
		$el = $this->rsEccCodes1($spec);
		$rs = $this->init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
		$blockNo = 0;
		$dataPos = 0;
		$eccPos = 0;
		$endfor_ = $this->rsBlockNum1($spec);
		for ($i=0; $i < $endfor_; ++$i) {
			$ecc = cast("int[int]", array_slice($this->ecccode, $eccPos));
			$rsblock = new RSBlock();
			$rsblock->dataLength = $dl;
			$rsblock->data = cast("int[int]", array_slice($this->datacode, $dataPos));
			$rsblock->eccLength = $el;
			$ecc = $this->encode_rs_char($rs, $rsblock->data, $ecc);
			$rsblock->ecc = $ecc;
			$this->ecccode = cast("int[int]", array_merge(array_slice($this->ecccode,0, $eccPos), $ecc));
			$dataPos += $dl;
			$eccPos += $el;
			$this->rsblocks[$blockNo] = $rsblock;
			$blockNo++;
		}
		if ($this->rsBlockNum2($spec) == 0)
			return;
		$dl = $this->rsDataCodes2($spec);
		$el = $this->rsEccCodes2($spec);
		$rs = $this->init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
		if ($rs == NULL)
			throw new RuntimeException();
		$endfor_ = $this->rsBlockNum2($spec);
		for ($i=0; $i < $endfor_; ++$i) {
			$ecc = cast("int[int]", array_slice($this->ecccode, $eccPos));
			$rsblock = new RSBlock();
			$rsblock->dataLength = $dl;
			$rsblock->data = cast("int[int]", array_slice($this->datacode, $dataPos));
			$rsblock->eccLength = $el;
			$ecc = $this->encode_rs_char($rs, $rsblock->data, $ecc);
			$rsblock->ecc = $ecc;
			$this->ecccode = cast("int[int]", array_merge(array_slice($this->ecccode, 0, $eccPos), $ecc));
			$dataPos += $dl;
			$eccPos += $el;
			$this->rsblocks[$blockNo] = $rsblock;
			$blockNo++;
		}
	}

	/**
	 * Encode mask
	 * @param int $mask (int) masking mode
	 * @return string[int]
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	private function encodeMask($mask) {
		$spec = array(0, 0, 0, 0, 0);
		$this->datacode = $this->getByteStream($this->items);
		if (is_null($this->datacode))
			throw new \RuntimeException(); // FIXME: why exception here?
		$spec = $this->getEccSpec($this->version, $this->level, $spec);
		$this->b1 = $this->rsBlockNum1($spec);
		$this->dataLength = $this->rsDataLength($spec);
		$this->eccLength = $this->rsEccLength($spec);
		$this->ecccode = self::newArrayInt($this->eccLength);
		$this->blocks = $this->rsBlockNum($spec);
		$this->init($spec);
		$this->count = 0;
		$this->width = $this->getWidth($this->version);
		$this->frame = $this->newFrame($this->version);
		$this->x = $this->width - 1;
		$this->y = $this->width - 1;
		$this->dir = -1;
		$this->bit = -1;
		// inteleaved data and ecc codes
		for ($i=0; $i < ($this->dataLength + $this->eccLength); $i++) {
			$code = $this->getCode();
			$bit = 0x80;
			for ($j=0; $j<8; $j++) {
				$addr = $this->getNextPosition();
				// FIMXE: BUG, ... | boolean
				//$this->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
				// FIX:
				$this->setFrameAt($addr, 0x02 | ((($bit & $code) != 0)? 1 : 0));
				$bit = $bit >> 1;
			}
		}
		// remainder bits
		$j = $this->getRemainder($this->version);
		for ($i=0; $i<$j; $i++) {
			$addr = $this->getNextPosition();
			$this->setFrameAt($addr, 0x02);
		}
		// masking
		$this->runLength = self::newArrayInt(self::SPEC_WIDTH_MAX + 1);
		if ($mask < 0) {
			if (self::FIND_BEST_MASK) {
				$masked = $this->mask($this->width, $this->frame, $this->level);
			} else {
				$masked = $this->makeMask($this->width, $this->frame, (intval(self::DEFAULT_MASK) % 8), $this->level);
			}
		} else {
			$masked = $this->makeMask($this->width, $this->frame, $mask, $this->level);
		}
		if (count($masked) == 0)
			throw new \RuntimeException(); // FIXME: why exception here?
		return $masked;
	}
	

	/**
	 * Creates a QR Code barcode.
	 * @param string $payload Data to represent. May contain arbitrary bytes.
	 * @param string $eclevel Error correction level:
	 * <ul>
	 * <li><tt>'L'</tt>: About 7% or less errors can be corrected (default).</li>
	 * <li><tt>'M'</tt>: About 15% or less errors can be corrected.</li>
	 * <li><tt>'Q'</tt>: About 25% or less errors can be corrected.</li>
	 * <li><tt>'H'</tt>: About 30% or less errors can be corrected.</li>
	 * </ul>
	 * @param boolean $detectKanji If set, binary sequences that looks like
	 * multi-byte Kanji encoding are encoded as such. Enable if binary data
	 * of the payload have to be interpreted as Kanji rather than as binary data.
	 * @throws InvalidArgumentException Empty code. Invalid error correction level.
	 * Invalid encoding mode. Unsupported structured encoding mode. The chosen
	 * encoding does not support the data provided.
	 * @throws QRCODECapacityException The size of input data is greater than QR
	 * capacity - try to lover the error correction mode.
	 */
	public function __construct($payload, $eclevel = 'L', $detectKanji = FALSE) {
		if (strlen($payload) < 1 )
			throw new InvalidArgumentException("empty code");
		$this->payload = $payload;
		$level = array_search($eclevel, array(self::ECLEVEL_L=>'L',
			self::ECLEVEL_M=>'M', self::ECLEVEL_Q=>'Q', self::ECLEVEL_H=>'H'));
		if ($level === false)
			throw new InvalidArgumentException("invalid error correction level");
		$this->level = (int) $level;
		$this->detectKanji = $detectKanji;
		$this->items = array();
		$this->dataStr = $payload;
		$this->splitString();
		$mask_data = $this->encodeMask(-1);
		$size = count($mask_data);
		$this->num_rows = $size;
		$this->num_cols = $size;
		$matrix = /*. (int[int][int]) .*/ array();
		foreach ($mask_data as $line) {
			$matrix_row = /*. (int[int]) .*/ array();
			for($i = 0; $i < $size; $i++)
				$matrix_row[] = ord($line[$i]) & 1;
			$matrix[] = $matrix_row;
		}
		$this->matrix = $matrix;
	}

}
