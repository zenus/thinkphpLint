<?php

namespace org\fpdf;

/*.
	require_module 'standard';
	require_module 'zlib';
.*/
require_once __DIR__ . "/../../all.php";
use ErrorException;
use it\icosaedro\utils\StringBuffer;
use it\icosaedro\io\IOException;
use it\icosaedro\io\SeekableInputStream;
use it\icosaedro\io\FileInputStream;
use it\icosaedro\io\File;

/**
 * @access private
 */
class TableEntry {
	/** @var string */
	public $tag;
	public $checksum_hi = 0;
	public $checksum_lo = 0;
	public $offset = 0;
	public $length = 0;
}


/**
 * Service class to build ranges of character widths.
 * @access private
 */
class CidRange {
	/** @var int[int] */
	public $arr;
	public $isInterval = FALSE;
	
	/**
	 * @param int[int] $arr
	 * @param boolean $isInterval
	 */
	function __construct($arr, $isInterval) {
		$this->arr = $arr;
		$this->isInterval = $isInterval;
	}
}


/**
 * Parses a TrueType or OpenType font file to be used with FPDF.
 * These files may have file name extensions .ttf, .otf or .ttc.
 * This class is based on The ReportLab Open Source PDF library
 * written in Python - {@link http://www.reportlab.com/software/opensource/}
 * together with ideas from the OpenOffice source code and others.
 * <p>This source is an excerpt from the original tFPDF 1.24 program of the author.
 * @version $Date: 2015/03/03 15:41:09 $
 * @author Ian Back <ianb@bpm1.com>
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @license  LGPL
 * @copyright (c) Ian Back, 2010 - This header must be retained in any
 * redistribution or modification of the file.
 */
class FontTrueType extends Font {
	
	/**
	 * Define the value used in the "head" table of a created TTF file.
	 * 0x74727565 "true" for Mac.
	 * 0x00010000 for Windows.
	 * Either seems to work for a font embedded in a PDF file
	 * when read by Adobe Reader on a Windows PC(!).
	 * @access private
	 */
	const _TTF_MAC_HEADER = false;

	/**
	 * TrueType Font Glyph operators.
	 * @access private
	 */
	const
		GF_WORDS = 1,
		GF_SCALE = 8,
		GF_MORE = 32,
		GF_XYSCALE = 64,
		GF_TWOBYTWO = 128;

	private $maxUni = 0;
	/**
	 * @var TableEntry[string]
	 */
	private $tables;
	/**
	 * @var string[string]
	 */
	private $otables;
	/**
	 * @var File
	 */
	private $filename;
	/**
	 * @var SeekableInputStream
	 */
	private $fh;
	/**
	 * @var int[int]
	 */
	private $glyphPos;
	/**
	 * @var int[int]
	 */
	private $charToGlyph;
	/**
	 * @var int[int][int]
	 */
	private $glyphToChar;
	private $ascent = 0.0;
	private $descent = 0.0;
	/**
	 * @var string
	 */
	private $familyName;
	/**
	 * @var string
	 */
	private $styleName;
	/**
	 * @var string
	 */
	private $fullName;
	/**
	 * @var string
	 */
	private $uniqueFontID;
	private $unitsPerEm = 0;
	/**
	 * @var double[int]
	 */
	private $bbox;
	private $capHeight = 0.0;
	private $stemV = 0;
	private $italicAngle = 0.0;
	private $flags = 0;
	private $version = 0;
	private $sFamilyClass = 0;
	private $sFamilySubClass = 0;
	/**
	 * Maximum size of glyph table to read in as string (otherwise reads each
	 * glyph from file).
	 * @var int
	 */
	private $maxStrLenRead = 0;
	/**
	 * If the license of this font allows embedding in PDF document.
	 * @var boolean
	 */
	public $restrictedUse = FALSE;
	private $maxUniChar = 0;
	/**
	 * @var int[int]
	 */
	private $codeToGlyph;
	/**
	 * @var int[int][string][int]
	 */
	private $glyphdata;
	
	/**
	 * @var string[string]
	 */
	private $desc;
	private $indexToLocFormat = 0;
	private $glyphDataFormat = 0;
	private $metricDataFormat = 0;
	private $numberOfHMetrics = 0;
	private $numGlyphs = 0;
	
	
	/**
	 * @param object $other
	 * @return boolean
	 */
	function equals($other)
	{
		if( $other === NULL )
			return FALSE;
		if( $this === $other )
			return TRUE;
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->filename->equals($other2->filename);
	}
	
	
	/**
	 * return int
	 */
	function getHash() {
		return $this->filename->getHash();
	}
	
	
	function __toString() {
		return $this->filename->__toString();
	}
	
	
	function toUString() {
		return $this->filename->toUString();
	}

	
//	/**
//	 * @param string $s
//	 */
//	private static function unpack_short($s) {
//		$a = (ord($s[0])<<8) + ord($s[1]);
//		if (($a & (1 << 15)) != 0 ) { 
//			$a = ($a - (1 << 16)); 
//		}
//		return $a;
//	}
	
	/**
	 * Decodes a unsigned BE 16-bit number.
	 * @param string $s Must contain at least 2 bytes.
	 * @return int Decoded number.
	 */
	private static function unpack_ushort($s) {
		return 256*ord($s[0]) + ord($s[1]);
	}
	
	/**
	 * @param string $s
	 * @return int[int] Decoded unsigned short (16 bits) numbers as array with
	 * 1-base index (NOT 0-based).
	 */
	private static function unpack_ushort_array($s) {
		$a = unpack("n*", $s);
		$b = /*. (int[int]) .*/ array();
		$n = count($a);
		for($i = 1; $i <= $n; $i++)
			$b[$i] = (int) $a[$i];
		return $b;
	}
	
	/**
	 * @param string $s
	 * @return int[int] Decoded unsigned long (32 bits) numbers as array with
	 * 1-base index (NOT 0-based).
	 */
	private static function unpack_ulong_array($s) {
		$a = unpack("N*", $s); // FIXME: may overflow on 32-bits PHP!
		$b = /*. (int[int]) .*/ array();
		$n = count($a);
		for($i = 1; $i <= $n; $i++)
			$b[$i] = (int) $a[$i];
		return $b;
	}
	

	/**
	 * @param string $tag
	 */
	private function get_table_pos($tag) {
		$offset = $this->tables[$tag]->offset;
		$length = $this->tables[$tag]->length;
		return array($offset, $length);
	}
	

	/**
	 * @param string $tag
	 * @param int $offset_in_table
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function seek_table($tag, $offset_in_table = 0) {
		$tpos = $this->get_table_pos($tag);
		$this->fh->setPosition($tpos[0] + $offset_in_table);
		return $this->fh->getPosition();
	}
	

	/**
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function read_tag() {
		return $this->fh->readFully(4);
	}

	/**
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function read_short() {
		$s = $this->fh->readFully(2);
		$a = (ord($s[0])<<8) + ord($s[1]);
		if (($a & (1 << 15)) != 0 ) { $a = ($a - (1 << 16)) ; }
		return $a;
	}

	/**
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function read_ushort() {
		$s = $this->fh->readFully(2);
		return self::unpack_ushort($s);
	}

	/**
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function read_ulong() {
		$s = $this->fh->readFully(4);
		// FIXME: if large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
	}

	/**
	 * @param int $pos
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function get_ushort($pos) {
		$this->fh->setPosition($pos);
		return $this->read_ushort();
	}

//	/**
//	 * @param int $pos
//	 * @throws ErrorException
//	 */
//	private function get_ulong($pos) {
//		fseek($this->fh,$pos);
//		$s = fread($this->fh,4);
//		// iF large uInt32 as an integer, PHP converts it to -ve
//		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
//	}

//	/**
//	 * @param int $val
//	 */
//	private function pack_short($val) {
//		if ($val<0) { 
//			$val = -$val;
//			$val = ~$val;
//			$val += 1;
//		}
//		return pack("n",$val); 
//	}

	/**
	 * @param string $stream
	 * @param int $offset
	 * @param string $value
	 * @return string
	 */
	private static function splice($stream, $offset, $value) {
		return substr($stream,0,$offset) . $value . substr($stream,$offset+strlen($value));
	}

	/**
	 * @param string $stream
	 * @param int $offset
	 * @param int $value Unsigned short number in [0,65535]. Only the two
	 * lowest bytes are actually considered, the rest is ignored.
	 * @return string
	 */
	private static function set_ushort($stream, $offset, $value) {
		$up = pack("n", $value);
		return self::splice($stream, $offset, $up);
	}

//	/**
//	 * 
//	 * @param string $stream
//	 * @param int $offset
//	 * @param int $val
//	 * @return string
//	 */
//	private static function _set_short($stream, $offset, $val) {
//		if ($val<0) { 
//			$val = -$val;
//			$val = ~$val;
//			$val += 1;
//		}
//		$up = pack("n",$val); 
//		return self::splice($stream, $offset, $up);
//	}

	/**
	 * @param int $pos
	 * @param int $length
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function get_chunk($pos, $length) {
		$this->fh->setPosition($pos);
		return $this->fh->readFully($length);
	}

	/**
	 * @param string $tag
	 * @return string
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function get_table($tag) {
		$a = $this->get_table_pos($tag);
		$pos = $a[0];
		$length = $a[1];
		if ($length == 0)
			throw new ErrorException("empty table: $tag");
		return $this->get_chunk($pos, $length);
	}

	/**
	 * @param string $tag
	 * @param string $data
	 */
	private function add($tag, $data) {
		if ($tag === 'head') {
			$data = self::splice($data, 8, "\0\0\0\0");
		}
		$this->otables[$tag] = $data;
	}

	/**
	 * 
	 * @param int[int] $x
	 * @param int[int] $y
	 * @return int[int]
	 */
	private static function sub32($x, $y) {
		$xlo = $x[1];
		$xhi = $x[0];
		$ylo = $y[1];
		$yhi = $y[0];
		if ($ylo > $xlo) { $xlo += 1 << 16; $yhi += 1; }
		$reslo = $xlo-$ylo;
		if ($yhi > $xhi) { $xhi += 1 << 16;  }
		$reshi = $xhi-$yhi;
		$reshi = $reshi & 0xFFFF;
		return array($reshi, $reslo);
	}

	/**
	 * @param string $data
	 */
	private static function calcChecksum($data)  {
		if ((strlen($data) % 4) != 0) { $data .= str_repeat("\0",(4-(strlen($data) % 4))); }
		$hi=0x0000;
		$lo=0x0000;
		for($i=0;$i<strlen($data);$i+=4) {
			$hi += (ord($data[$i])<<8) + ord($data[$i+1]);
			$lo += (ord($data[$i+2])<<8) + ord($data[$i+3]);
			$hi += $lo >> 16;
			$lo = $lo & 0xFFFF;
			$hi = $hi & 0xFFFF;
		}
		return array($hi, $lo);
	}

	/**
	 * CMAP Format 4.
	 * @param int $unicode_cmap_offset
	 * @param int[int][int] & $glyphToChar
	 * @param int[int] & $charToGlyph
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function getCMAP4($unicode_cmap_offset, &$glyphToChar, &$charToGlyph ) {
		$this->maxUniChar = 0;
		$this->fh->setPosition($unicode_cmap_offset + 2);
		$length = $this->read_ushort();
		$limit = $unicode_cmap_offset + $length;
		$this->fh->readFully(2);

		$segCount = $this->read_ushort() / 2;
		$this->fh->readFully(6);
		$endCount = /*. (int[int]) .*/ array();
		for($i=0; $i<$segCount; $i++) { $endCount[] = $this->read_ushort(); }
		$this->fh->readFully(2);
		$startCount = /*. (int[int]) .*/ array();
		for($i=0; $i<$segCount; $i++) { $startCount[] = $this->read_ushort(); }
		$idDelta = /*. (int[int]) .*/ array();
		for($i=0; $i<$segCount; $i++) { $idDelta[] = $this->read_short(); }		// ???? was unsigned short
		$idRangeOffset_start = $this->fh->getPosition();
		$idRangeOffset = /*. (int[int]) .*/ array();
		for($i=0; $i<$segCount; $i++) { $idRangeOffset[] = $this->read_ushort(); }

		for ($n=0;$n<$segCount;$n++) {
			$endpoint = ($endCount[$n] + 1);
			for ($unichar=$startCount[$n];$unichar<$endpoint;$unichar++) {
				if ($idRangeOffset[$n] == 0)
					$glyph = ($unichar + $idDelta[$n]) & 0xFFFF;
				else {
					$offset = ($unichar - $startCount[$n]) * 2 + $idRangeOffset[$n];
					$offset = $idRangeOffset_start + 2 * $n + $offset;
					if ($offset >= $limit)
						$glyph = 0;
					else {
						$glyph = $this->get_ushort($offset);
						if ($glyph != 0)
						   $glyph = ($glyph + $idDelta[$n]) & 0xFFFF;
					}
				}
				$charToGlyph[$unichar] = $glyph;
				if ($unichar < 196608) { $this->maxUniChar = (int) max($unichar,$this->maxUniChar); }
				$glyphToChar[$glyph][] = $unichar;
			}
		}
	}

	/**
	 * @param int $numberOfHMetrics
	 * @param int $numGlyphs
	 * @param int[int][int] & $glyphToChar
	 * @param double $scale
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function getHMTX($numberOfHMetrics, $numGlyphs, &$glyphToChar, $scale) {
		$start = $this->seek_table("hmtx");
		$aw = 0;
		$this->charWidths = array(0=>0); // prepare entry 0
		$nCharWidths = 0;
		$arr = /*. (int[int]) .*/ array();
		if (($numberOfHMetrics*4) < $this->maxStrLenRead) {
			$data = $this->get_chunk($start,($numberOfHMetrics*4));
			$arr = self::unpack_ushort_array($data);
		}
		else { $this->fh->setPosition($start); }
		for( $glyph=0; $glyph<$numberOfHMetrics; $glyph++) {

			if (($numberOfHMetrics*4) < $this->maxStrLenRead) {
				$aw = $arr[($glyph*2)+1];
			}
			else {
				$aw = $this->read_ushort();
				/* $lsb = */ $this->read_ushort();
			}
			if (isset($glyphToChar[$glyph]) || $glyph == 0) {
				if ($aw >= (1 << 15) ) { $aw = 0; }	// 1.03 Some (arabic) fonts have -ve values for width
					// although should be unsigned value - comes out as e.g. 65108 (intended -50)
				if ($glyph == 0) {
					$this->missingWidth = (int) round($scale*$aw);
					continue;
				}
				foreach($glyphToChar[$glyph] as $char) {
					if ($char != 0 && $char != 65535) {
 						$w = (int) round($scale*$aw);
						if ($w == 0) { $w = 65535; }
						if ($char < 196608) {
							$this->charWidths[$char] = $w;
							$nCharWidths++;
						}
					}
				}
			}
		}
		$data = $this->get_chunk(($start+$numberOfHMetrics*4),($numGlyphs*2));
		$arr = self::unpack_ushort_array($data);
		$diff = $numGlyphs-$numberOfHMetrics;
		for( $pos=0; $pos<$diff; $pos++) {
			$glyph = $pos + $numberOfHMetrics;
			if (isset($glyphToChar[$glyph])) {
				foreach($glyphToChar[$glyph] as $char) {
					if ($char != 0 && $char != 65535) {
						$w = (int) round($scale*$aw);
						if ($w == 0) { $w = 65535; }
						if ($char < 196608) {
							$this->charWidths[$char] = $w;
							$nCharWidths++;
						}
					}
				}
			}
		}
		// NB 65535 is a set width of 0
		// First entry defines number of chars in font
		$this->charWidths[0] = $nCharWidths;
		ksort($this->charWidths);
	}


	/**
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function extractInfo() {
		///////////////////////////////////
		// name - Naming table
		///////////////////////////////////
		$this->sFamilyClass = 0;
		$this->sFamilySubClass = 0;

		$name_offset = $this->seek_table("name");
		$format = $this->read_ushort();
		if ($format != 0)
			throw new ErrorException("unknown name table format ".$format);
		$numRecords = $this->read_ushort();
		$string_data_offset = $name_offset + $this->read_ushort();
		$names = array(1=>'',2=>'',3=>'',4=>'',6=>'');
		$K = array_keys($names);
		$nameCount = count($names);
		for ($i=0;$i<$numRecords; $i++) {
			$platformId = $this->read_ushort();
			$encodingId = $this->read_ushort();
			$languageId = $this->read_ushort();
			$nameId = $this->read_ushort();
			$length = $this->read_ushort();
			$offset = $this->read_ushort();
			if (!in_array($nameId,$K)) continue;
			$N = '';
			if ($platformId == 3 && $encodingId == 1 && $languageId == 0x409) { // Microsoft, Unicode, US English, PS Name
				$opos = $this->fh->getPosition();
				$this->fh->setPosition($string_data_offset + $offset);
				if ($length % 2 != 0)
					throw new ErrorException("PostScript name is UTF-16BE string of odd length");
				$length = (int) ($length / 2);
				$N = '';
				while ($length > 0) {
					$char = $this->read_ushort();
					$N .= (chr($char));
					$length -= 1;
				}
				$this->fh->setPosition($opos);
			}
			else if ($platformId == 1 && $encodingId == 0 && $languageId == 0) { // Macintosh, Roman, English, PS Name
				$opos = $this->fh->getPosition();
				$N = $this->get_chunk($string_data_offset + $offset, $length);
				$this->fh->setPosition($opos);
			}
			if (strlen($N) > 0 && $names[$nameId]==='') {
				$names[$nameId] = $N;
				$nameCount -= 1;
				if ($nameCount==0) break;
			}
		}
		if ($names[6] !== '')
			$psName = $names[6];
		else if ($names[4] !== '')
			$psName = $names[4];
		else if ($names[1] !== '')
			$psName = $names[1];
		else
			throw new ErrorException("could not find PostScript font name");
		$this->name = (string) str_replace(' ', '-', $psName);
		if ($names[1] !== '') { $this->familyName = $names[1]; } else { $this->familyName = $psName; }
		if ($names[2] !== '') { $this->styleName = $names[2]; } else { $this->styleName = 'Regular'; }
		if ($names[4] !== '') { $this->fullName = $names[4]; } else { $this->fullName = $psName; }
		if ($names[3] !== '') { $this->uniqueFontID = $names[3]; } else { $this->uniqueFontID = $psName; }
		if ($names[6] !== '') { $this->fullName = $names[6]; }

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		$this->fh->readFully(18); 
		$this->unitsPerEm = $unitsPerEm = $this->read_ushort();
		$scale = 1000 / $unitsPerEm;
		$this->fh->readFully(16);
		$xMin = $this->read_short();
		$yMin = $this->read_short();
		$xMax = $this->read_short();
		$yMax = $this->read_short();
		$this->bbox = array(($xMin*$scale), ($yMin*$scale), ($xMax*$scale), ($yMax*$scale));
		$this->fh->readFully(3*2);
		$this->indexToLocFormat = $this->read_ushort();
		$this->glyphDataFormat = $this->read_ushort();
		if ($this->glyphDataFormat != 0)
			throw new ErrorException("unknown glyph data format ".$this->glyphDataFormat);

		///////////////////////////////////
		// hhea metrics table
		///////////////////////////////////
		// ttf2t1 seems to use this value rather than the one in OS/2 - so put in for compatibility
		if (isset($this->tables["hhea"])) {
			$this->seek_table("hhea");
			$this->fh->readFully(4);
			$hheaAscender = $this->read_short();
			$hheaDescender = $this->read_short();
			$this->ascent = ($hheaAscender *$scale);
			$this->descent = ($hheaDescender *$scale);
		}

		///////////////////////////////////
		// OS/2 - OS/2 and Windows metrics table
		///////////////////////////////////
		if (isset($this->tables["OS/2"])) {
			$this->seek_table("OS/2");
			$version = $this->read_ushort();
			$this->fh->readFully(2);
			$usWeightClass = $this->read_ushort();
			$this->fh->readFully(2);
			$fsType = $this->read_ushort();
			if ($fsType == 0x0002 || ($fsType & 0x0300) != 0)
				$this->restrictedUse = true;
			$this->fh->readFully(20);
			$sF = $this->read_short();
			$this->sFamilyClass = ($sF >> 8);
			$this->sFamilySubClass = ($sF & 0xFF);
			//PANOSE = 10 byte length
			/* $panose = */ $this->fh->readFully(10);
			$this->fh->readFully(26);
			$sTypoAscender = $this->read_short();
			$sTypoDescender = $this->read_short();
			if ($this->ascent == 0.0) $this->ascent = ($sTypoAscender*$scale);
			if ($this->descent == 0.0) $this->descent = ($sTypoDescender*$scale);
			if ($version > 1) {
				$this->fh->readFully(16);
				$sCapHeight = $this->read_short();
				$this->capHeight = ($sCapHeight*$scale);
			}
			else {
				$this->capHeight = $this->ascent;
			}
		}
		else {
			$usWeightClass = 500;
			if ($this->ascent == 0.0) $this->ascent = ($yMax*$scale);
			if ($this->descent == 0.0) $this->descent = ($yMin*$scale);
			$this->capHeight = $this->ascent;
		}
		$this->stemV = 50 + intval(pow(($usWeightClass / 65.0),2));

		///////////////////////////////////
		// post - PostScript table
		///////////////////////////////////
		$this->seek_table("post");
		$this->fh->readFully(4); 
		$this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
		$this->underlinePosition = $this->read_short() * $scale;
		$this->underlineThickness = $this->read_short() * $scale;
		$isFixedPitch = $this->read_ulong();

		$this->flags = 4;

		if ($this->italicAngle != 0.0) 
			$this->flags |= 64;
		if ($usWeightClass >= 600)
			$this->flags |= 262144;
		if ($isFixedPitch != 0)
			$this->flags |= 1;

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		$this->fh->readFully(32); 
		$this->metricDataFormat = $this->read_ushort();
		if ($this->metricDataFormat != 0)
			throw new ErrorException("unknown horizontal metric data format ".$this->metricDataFormat);
		$this->numberOfHMetrics = $this->read_ushort();
		if ($this->numberOfHMetrics == 0) 
			throw new ErrorException("number of horizontal metrics is 0");

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		$this->fh->readFully(4); 
		$this->numGlyphs = $this->read_ushort();

		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->fh->readFully(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->fh->getPosition();
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					if ($unicode_cmap_offset == 0) $unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->fh->setPosition($save_pos);
		}
		if ($unicode_cmap_offset == 0)
			throw new ErrorException("missing cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)");

		$this->getCMAP4($unicode_cmap_offset, $this->glyphToChar, $this->charToGlyph );

		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$this->getHMTX($this->numberOfHMetrics, $this->numGlyphs, $this->glyphToChar, $scale);

	}


	/**
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function readTableDirectory() {
	    $numTables = $this->read_ushort();
		/* $searchRange = */ $this->read_ushort();
		/* $entrySelector = */ $this->read_ushort();
		/* $rangeShift = */ $this->read_ushort();
		$this->tables = array();	
		for ($i=0;$i<$numTables;$i++) {
			$record = new TableEntry();
			$record->tag = $this->read_tag();
			$record->checksum_hi = $this->read_ushort();
			$record->checksum_lo = $this->read_ushort();
			$record->offset = $this->read_ulong();
			$record->length = $this->read_ulong();
			$this->tables[$record->tag] = $record;
		}
	}

	/**
	 * loca - Index to location.
	 * @param int $indexToLocFormat
	 * @param int $numGlyphs
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function getLOCA($indexToLocFormat, $numGlyphs) {
		$start = $this->seek_table('loca');
		$this->glyphPos = array();
		if ($indexToLocFormat == 0) {
			$data = $this->get_chunk($start,($numGlyphs*2)+2);
			$arr = self::unpack_ushort_array($data);
			for ($n=0; $n<=$numGlyphs; $n++) {
				$this->glyphPos[] = $arr[$n+1] * 2;
			}
		}
		else if ($indexToLocFormat == 1) {
			$data = $this->get_chunk($start,($numGlyphs*4)+4);
			$arr = self::unpack_ulong_array($data);
			for ($n=0; $n<=$numGlyphs; $n++) {
				$this->glyphPos[] = $arr[$n+1];
			}
		}
		else 
			throw new ErrorException("unknown location table format $indexToLocFormat");
	}


	/**
	 * Recursively get composite glyphs.
	 * @param int $originalGlyphIdx
	 * @param int & $start
	 * @param int[int] & $glyphSet
	 * @param boolean[int] & $subsetglyphs
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function getGlyphs($originalGlyphIdx, &$start, &$glyphSet, &$subsetglyphs) {
		$glyphPos = $this->glyphPos[$originalGlyphIdx];
		$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
		if ($glyphLen == 0) { 
			return;
		}
		$this->fh->setPosition($start + $glyphPos);
		$numberOfContours = $this->read_short();
		if ($numberOfContours < 0) {
			$this->fh->readFully(8);
			$flags = self::GF_MORE;
			while (($flags & self::GF_MORE) != 0) {
				$flags = $this->read_ushort();
				$glyphIdx = $this->read_ushort();
				if (!isset($glyphSet[$glyphIdx])) {
					$glyphSet[$glyphIdx] = count($subsetglyphs);	// old glyphID to new glyphID
					$subsetglyphs[$glyphIdx] = true;
				}
				$savepos = $this->fh->getPosition();
				$this->getGlyphs($glyphIdx, $start, $glyphSet, $subsetglyphs);
				$this->fh->setPosition($savepos);
				if (($flags & self::GF_WORDS) != 0)
					$this->fh->readFully(4);
				else
					$this->fh->readFully(2);
				if (($flags & self::GF_SCALE) != 0)
					$this->fh->readFully(2);
				else if (($flags & self::GF_XYSCALE) != 0)
					$this->fh->readFully(4);
				else if (($flags & self::GF_TWOBYTWO) != 0)
					$this->fh->readFully(8);
			}
		}
	}

	/**
	 * 
	 * @param int $numberOfHMetrics
	 * @param int $gid
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function getHMetric($numberOfHMetrics, $gid) {
		$start = $this->seek_table("hmtx");
		if ($gid < $numberOfHMetrics) {
			$this->fh->setPosition($start+($gid*4));
			$hm = $this->fh->readFully(4);
		}
		else {
			$this->fh->setPosition($start+(($numberOfHMetrics-1)*4));
			$hm = $this->fh->readFully(2);
			$this->fh->setPosition($start+($numberOfHMetrics*2)+($gid*2));
			$hm .= $this->fh->readFully(2);
		}
		return $hm;
	}

	/**
	 * Put the TTF file together.
	 * @param string & $stm
	 */
	private function endTTFile(&$stm) {
		$stm = '';
		$numTables = count($this->otables);
		$searchRange = 1;
		$entrySelector = 0;
		while ($searchRange * 2 <= $numTables) {
			$searchRange = $searchRange * 2;
			$entrySelector = $entrySelector + 1;
		}
		$searchRange = $searchRange * 16;
		$rangeShift = $numTables * 16 - $searchRange;

		// Header
		if (self::_TTF_MAC_HEADER) {
			$stm .= (pack("Nnnnn", 0x74727565, $numTables, $searchRange, $entrySelector, $rangeShift));	// Mac
		}
		else {
			$stm .= (pack("Nnnnn", 0x00010000 , $numTables, $searchRange, $entrySelector, $rangeShift));	// Windows
		}

		// Table directory
		$tables = $this->otables;

		ksort ($tables); 
		$offset = 12 + $numTables * 16;
		$head_start = 0; // (the 'head' tag is always present)
		foreach ($tables as $tag=>$data) {
			if ($tag === 'head') { $head_start = $offset; }
			$stm .= $tag;
			$checksum = self::calcChecksum($data);
			$stm .= pack("nn", $checksum[0],$checksum[1]);
			$stm .= pack("NN", $offset, strlen($data));
			$paddedLength = (strlen($data)+3)&~3;
			$offset = $offset + $paddedLength;
		}

		// Table data
		foreach ($tables as $tag=>$data) {
			$data .= "\0\0\0";
			$stm .= substr($data,0,(strlen($data)&~3));
		}

		$checksum = self::calcChecksum($stm);
		$checksum = self::sub32(array(0xB1B0,0xAFBA), $checksum);
		$chk = pack("nn", $checksum[0],$checksum[1]);
		$stm = $this->splice($stm,($head_start + 8),$chk);
		return $stm ;
	}
	

	/**
	 * Parses a TrueType font file, generatig an object that can be sent to
	 * the FPDF::setFont() method.
	 * @param File $file File name of the font file.
	 * @throws IOException
	 * @throws ErrorException
	 */
	function __construct($file) {
		$this->filename = $file;
		try {
			$this->fh = new FileInputStream($file);
			$this->maxStrLenRead = 200000; // FIXME: ??
			$this->charWidths = array();
			$this->glyphPos = array();
			$this->charToGlyph = array();
			$this->glyphToChar = array();
			$this->tables = array();
			$this->otables = array();
			$this->version = $version = $this->read_ulong();
			if ($version==0x4F54544F) 
				throw new ErrorException("Postscript outlines are not supported");
			if ($version==0x74746366) 
				throw new ErrorException("TrueType Fonts Collections not supported");
			if (!in_array($version, array(0x00010000,0x74727565)))
				throw new ErrorException("not a TrueType font (version = $version)");
			$this->readTableDirectory();
			$this->extractInfo();
			$this->getLOCA($this->indexToLocFormat, $this->numGlyphs);
			$this->fh->close();
			$this->fh = NULL;
			$this->desc = array(
				'Ascent'=>(string)round($this->ascent),
				'Descent'=>(string)round($this->descent),
				'CapHeight'=>(string)round($this->capHeight),
				'Flags'=>(string)$this->flags,
				'FontBBox'=>'['.round($this->bbox[0])." ".round($this->bbox[1])." ".round($this->bbox[2])." ".round($this->bbox[3]).']',
				'ItalicAngle'=>(string)$this->italicAngle,
				'StemV'=>(string)round($this->stemV),
				'MissingWidth'=>(string)$this->missingWidth
			);
		}
		catch(ErrorException $e){
			// add file name to the error message:
			throw new ErrorException($file . ": " .$e->getMessage(),
				$e->getCode(), $e->getSeverity(), $e->getFile(), $e->getLine(), $e);
		}
	}
	

	/**
	 * Returns a subset of this font containing only the glyphs actually used
	 * according to the $subset accounting set.
	 * @return string
	 * @throws IOException
	 * @throws ErrorException
	 */
	private function makeSubset() {
		$this->fh = new FileInputStream($this->filename);
		$this->fh->readFully(4);
		
		$this->maxUni = 0;
		$subsetglyphs = array(0=>TRUE); 
		$subsetCharToGlyph = /*. (int[int]) .*/ array();
		unset($this->subset[0]);
		foreach($this->subset as $code => $ignore) {
			if (isset($this->charToGlyph[$code])) {
				$subsetglyphs[$this->charToGlyph[$code]] = TRUE;
				$subsetCharToGlyph[$code] = $this->charToGlyph[$code];	// Unicode to old GlyphID
			}
			$this->maxUni = (int) max($this->maxUni, $code);
		}

		$a = $this->get_table_pos('glyf');
		$start = $a[0];

		$glyphSet = /*. (int[int]) .*/ array();
		ksort($subsetglyphs);
		$n = 0;
		//$fsLastCharIndex = 0;	// maximum Unicode index (character code) in this font, according to the cmap subtable for platform ID 3 and platform- specific encoding ID 0 or 1.
		foreach($subsetglyphs as $originalGlyphIdx => $uni) {
			//$fsLastCharIndex = (int) max($fsLastCharIndex , $uni);
			$glyphSet[$originalGlyphIdx] = $n;	// old glyphID to new glyphID
			$n++;
		}

		ksort($subsetCharToGlyph);
		$codeToGlyph = /*. (int[int]) .*/ array();
		foreach($subsetCharToGlyph as $codepoint => $originalGlyphIdx) {
			$codeToGlyph[$codepoint] = $glyphSet[$originalGlyphIdx] ;
		}
		$this->codeToGlyph = $codeToGlyph;

		ksort($subsetglyphs);
		foreach($subsetglyphs as $originalGlyphIdx => $uni) {
			$this->getGlyphs($originalGlyphIdx, $start, $glyphSet, $subsetglyphs);
		}

		$numGlyphs = $numberOfHMetrics = count($subsetglyphs);

		//tables copied from the original
		$tags = array ('name');
		foreach($tags as $tag) { $this->add($tag, $this->get_table($tag)); }
		$tags = array ('cvt ', 'fpgm', 'prep', 'gasp');
		foreach($tags as $tag) {
			if (isset($this->tables[$tag])) { $this->add($tag, $this->get_table($tag)); }
		}

		// post - PostScript
		$opost = $this->get_table('post');
		$post = "\x00\x03\x00\x00" . substr($opost,4,12) . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
		$this->add('post', $post);

		// Sort CID2GID map into segments of contiguous codes
		ksort($codeToGlyph);
		unset($codeToGlyph[0]);
		//unset($codeToGlyph[65535]);
		$rangeid = 0;
		$range = /*. (int[int][int]) .*/ array();
		$prevcid = -2;
		$prevglidx = -1;
		// for each character
		foreach ($codeToGlyph as $cid => $glidx) {
			if ($cid == ($prevcid + 1) && $glidx == ($prevglidx + 1)) {
				$range[$rangeid][] = $glidx;
			} else {
				// new range
				$rangeid = $cid;
				$range[$rangeid] = array();
				$range[$rangeid][] = $glidx;
			}
			$prevcid = $cid;
			$prevglidx = $glidx;
		}

		// cmap - Character to glyph mapping - Format 4 (MS / )
		$segCount = count($range) + 1;	// + 1 Last segment has missing character 0xFFFF
		$searchRange = 1;
		$entrySelector = 0;
		while ($searchRange * 2 <= $segCount ) {
			$searchRange = $searchRange * 2;
			$entrySelector = $entrySelector + 1;
		}
		$searchRange = $searchRange * 2;
		$rangeShift = $segCount * 2 - $searchRange;
		$length = 16 + (8*$segCount ) + ($numGlyphs+1);
		$cmap = array(0, 1,		// Index : version, number of encoding subtables
			3, 1,				// Encoding Subtable : platform (MS=3), encoding (Unicode)
			0, 12,			// Encoding Subtable : offset (hi,lo)
			4, $length, 0, 		// Format 4 Mapping subtable: format, length, language
			$segCount*2,
			$searchRange,
			$entrySelector,
			$rangeShift);

		// endCode(s)
		foreach($range as $start=>$subrange) {
			$endCode = $start + (count($subrange)-1);
			$cmap[] = $endCode;	// endCode(s)
		}
		$cmap[] =	0xFFFF;	// endCode of last Segment
		$cmap[] =	0;	// reservedPad

		// startCode(s)
		foreach($range as $start=>$subrange) {
			$cmap[] = $start;	// startCode(s)
		}
		$cmap[] =	0xFFFF;	// startCode of last Segment
		// idDelta(s) 
		foreach($range as $start=>$subrange) {
			$idDelta = -($start-$subrange[0]);
			$n += count($subrange);
			$cmap[] = $idDelta;	// idDelta(s)
		}
		$cmap[] =	1;	// idDelta of last Segment
		// idRangeOffset(s) 
		foreach($range as $subrange) {
			$cmap[] = 0;	// idRangeOffset[segCount]  	Offset in bytes to glyph indexArray, or 0

		}
		$cmap[] =	0;	// idRangeOffset of last Segment
		foreach($range as $subrange) {
			foreach($subrange as $glidx) {
				$cmap[] = $glidx;
			}
		}
		$cmap[] = 0;	// Mapping for last character
		$cmapstr = '';
		foreach($cmap as $cm) { $cmapstr .= pack("n",$cm); }
		$this->add('cmap', $cmapstr);


		// glyf - Glyph data
		$a = $this->get_table_pos('glyf');
		$glyfOffset = $a[0];
		$glyfLength = $a[1];
		if ($glyfLength < $this->maxStrLenRead)
			$glyphData = $this->get_table('glyf');
		else
			$glyphData = NULL;

		$offsets = /*. (int[int]) .*/ array();
		$glyf = '';
		$pos = 0;

		$hmtxstr = '';
		$maxComponentElements = 0;	// number of glyphs referenced at top level
		$this->glyphdata = array();

		foreach($subsetglyphs as $originalGlyphIdx => $uni) {
			// hmtx - Horizontal Metrics
			$hm = $this->getHMetric($this->numberOfHMetrics, $originalGlyphIdx);	
			$hmtxstr .= $hm;

			$offsets[] = $pos;
			$glyphPos = $this->glyphPos[$originalGlyphIdx];
			$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
			if ($glyphData !== NULL) {
				$data = substr($glyphData,$glyphPos,$glyphLen);
			}
			else {
				if ($glyphLen > 0) $data = $this->get_chunk($glyfOffset+$glyphPos,$glyphLen);
				else $data = '';
			}

			if ($glyphLen > 0) {
				$up = self::unpack_ushort(substr($data,0,2));
				

				if ($glyphLen > 2 && ($up & (1 << 15)) != 0 ) {	// If number of contours <= -1 i.e. composiste glyph
					$pos_in_glyph = 10;
					$flags = self::GF_MORE;
					$nComponentElements = 0;
					while (($flags & self::GF_MORE) != 0) {
						$nComponentElements += 1;	// number of glyphs referenced at top level
						$up = self::unpack_ushort(substr($data,$pos_in_glyph,2));
						$flags = $up;
						$up = self::unpack_ushort(substr($data,$pos_in_glyph+2,2));
						$glyphIdx = $up;
						$this->glyphdata[$originalGlyphIdx]['compGlyphs'][] = $glyphIdx;
						$data = self::set_ushort($data, $pos_in_glyph + 2, $glyphSet[$glyphIdx]);
						$pos_in_glyph += 4;
						if (($flags & self::GF_WORDS) != 0) { $pos_in_glyph += 4; }
						else { $pos_in_glyph += 2; }
						if (($flags & self::GF_SCALE) != 0) { $pos_in_glyph += 2; }
						else if (($flags & self::GF_XYSCALE) != 0) { $pos_in_glyph += 4; }
						else if (($flags & self::GF_TWOBYTWO) != 0) { $pos_in_glyph += 8; }
					}
					$maxComponentElements = (int) max($maxComponentElements, $nComponentElements);
				}
			}

			$glyf .= $data;
			$pos += $glyphLen;
			if ($pos % 4 != 0) {
				$padding = 4 - ($pos % 4);
				$glyf .= str_repeat("\0",$padding);
				$pos += $padding;
			}
		}

		$offsets[] = $pos;
		$this->add('glyf', $glyf);

		// hmtx - Horizontal Metrics
		$this->add('hmtx', $hmtxstr);

		// loca - Index to location
		$locastr = '';
		if ((($pos + 1) >> 1) > 0xFFFF) {
			$indexToLocFormat = 1;        // long format
			foreach($offsets as $offset) { $locastr .= pack("N",$offset); }
		}
		else {
			$indexToLocFormat = 0;        // short format
			foreach($offsets as $offset) { $locastr .= pack("n",($offset/2)); }
		}
		$this->add('loca', $locastr);

		// head - Font header
		$head = $this->get_table('head');
		$head = self::set_ushort($head, 50, $indexToLocFormat);
		$this->add('head', $head);


		// hhea - Horizontal Header
		$hhea = $this->get_table('hhea');
		$hhea = self::set_ushort($hhea, 34, $numberOfHMetrics);
		$this->add('hhea', $hhea);

		// maxp - Maximum Profile
		$maxp = $this->get_table('maxp');
		$maxp = self::set_ushort($maxp, 4, $numGlyphs);
		$this->add('maxp', $maxp);

		// OS/2 - OS/2
		$os2 = $this->get_table('OS/2');
		$this->add('OS/2', $os2 );

		$this->fh->close();

		// Put the TTF file together
		$stm = '';
		$this->endTTFile($stm);
		return $stm ;
	}


	/**
	 * Write chars widths of this embedded font.
	 * @param PdfObjWriterInterface $w
	 * @throws ErrorException
	 */
	private function putWidths($w)
	{
		/* According to the specs. 9.7.4.3 page 279, writes
		 *     /W [ entries ]
		 * where each entry of the list can have 2 forms:
		 *     c1 [ w1 w2 ... wN ]
		 *     c1 c2 w
		 * being c1 the first CID of the range, c2 the last CID, w, w1, ..., wN the
		 * widths or each char in the range.
		 */
		$rangeid = 0;
		$range = /*. (CidRange[int]) .*/ array();
		$prevcid = -2;
		$prevwidth = -1;
		$interval = false;
		$maxUni = $this->maxUni;
		$subset = $this->subset;
		$cw = $this->charWidths;
		for ($cid = 1; $cid <= $maxUni; $cid++) {
			if (!( isset($cw[$cid]) && isset($subset[$cid])))  continue;
	//		if ($cid > 255 && !isset($subset[$cid])) { continue; }
			$width = $cw[$cid];
			if ($width == 0) { continue; }
			if ($width == 65535) { $width = 0; } // FIXME: ????
			if ($cid == ($prevcid + 1)) {
				if ($width == $prevwidth) {
					if ($width == $range[$rangeid]->arr[0]) {
						$range[$rangeid]->arr[] = $width;
					} else {
						array_pop($range[$rangeid]->arr);
						// new range
						$rangeid = $prevcid;
						$range[$rangeid] = new CidRange(array($prevwidth, $width), TRUE);
					}
					$interval = TRUE;
					$range[$rangeid]->isInterval = TRUE;
				} else {
					if ($interval) {
						// new range
						$rangeid = $cid;
						$range[$rangeid] = new CidRange(array($width), FALSE);
					} else {
						$range[$rangeid]->arr[] = $width;
					}
					$interval = FALSE;
				}
			} else {
				$rangeid = $cid;
				$range[$rangeid] = new CidRange(array($width), FALSE);
				$interval = false;
			}
			$prevcid = $cid;
			$prevwidth = $width;
		}
		$prevk = -1;
		$nextk = -1;
		$prevint = false;
		foreach ($range as $k => $ws) {
			$cws = count($ws->arr);
			if ($k == $nextk and !$prevint and (!$ws->isInterval or $cws < 4)) {
				$ws->isInterval = FALSE;
				$range[$prevk]->arr = cast("int[int]", array_merge($range[$prevk]->arr, $ws->arr));
				unset($range[$k]);
			}
			else { $prevk = $k; }
			$nextk = $k + $cws;
			if ($ws->isInterval) {
				if ($cws > 3) { $prevint = true; }
				else { $prevint = false; }
				$ws->isInterval = FALSE;
				--$nextk;
			}
			else { $prevint = false; }
		}
		$s = '';
		foreach ($range as $k => $ws) {
	//		if (count(array_count_values($ws->arr)) == 1) { $s .= ' '.$k.' '.($k + count($ws->arr) - 1).' '.$ws->arr[0]; }
	//		else { $s .= ' '.$k.' [ '.implode(' ', $ws->arr).' ]' . "\n"; }
			if (count(array_count_values($ws->arr)) == 1) {
				$s .= ' '.$k.' '.($k + count($ws->arr) - 1).' '.$ws->arr[0];
			} else {
				$s .= " $k [";
				foreach($ws->arr as $width)
					$s .= " $width";
				$s .= " ]\n";
			}
		}
		$w->put('/W ['.$s.' ]');
	}
	
	
	/**
	 * Called only by FPDF; do not call this method in application code.
	 * Puts this font into the PDF document and sets the $n property.
	 * @param PdfObjWriterInterface $w
	 * @throws ErrorException
	 */
	public function put($w)
	{

		try {
			if ($this->restrictedUse)
				throw new ErrorException('font cannot be embedded due to copyright restrictions.');
			$ttfontstream = $this->makeSubset();
		}
		catch(IOException $e){
			// capture e rethrow exception incompatible with signature:
			throw new ErrorException($this->filename . ": " .$e->getMessage(),
				$e->getCode(), 0, $e->getFile(), $e->getLine(), $e);
		}
		catch(ErrorException $e){
			// add file name to the error message:
			throw new ErrorException($this->filename . ": " .$e->getMessage(),
				$e->getCode(), $e->getSeverity(), $e->getFile(), $e->getLine(), $e);
		}
		
		$ttfontsize = strlen($ttfontstream);
		$fontstream = gzcompress($ttfontstream);
		$fontname = 'MPDFAA' . '+' . $this->name;

		// 0. Type0 Font
		// A composite font - a font composed of other fonts, organized hierarchically
		$this->n = $w->addObj();
		$w->put('<</Type /Font');
		$w->put('/Subtype /Type0');
		$w->put('/BaseFont /'.$fontname.'');
		$w->put('/Encoding /Identity-H'); 
		$w->put('/DescendantFonts ['.($this->n + 1).' 0 R]');
		$w->put('/ToUnicode '.($this->n + 2).' 0 R');
		$w->put('>>');
		$w->put('endobj');

		// 1. CIDFontType2
		// A CIDFont whose glyph descriptions are based on TrueType font technology
		$w->addObj();
		$w->put('<</Type /Font');
		$w->put('/Subtype /CIDFontType2');
		$w->put('/BaseFont /'.$fontname.'');
		$w->put('/CIDSystemInfo '.($this->n + 3).' 0 R'); 
		$w->put('/FontDescriptor '.($this->n + 4).' 0 R');
		if (isset($this->desc['MissingWidth'])){
			$w->put('/DW '.$this->desc['MissingWidth']); 
		}
		$this->putWidths($w);
		$w->put('/CIDToGIDMap '.($this->n + 5).' 0 R');
		$w->put('>>');
		$w->put('endobj');

		// 2. ToUnicode
		$w->addObj();
		$toUni = "/CIDInit /ProcSet findresource begin\n";
		$toUni .= "12 dict begin\n";
		$toUni .= "begincmap\n";
		$toUni .= "/CIDSystemInfo\n";
		$toUni .= "<</Registry (Adobe)\n";
		$toUni .= "/Ordering (UCS)\n";
		$toUni .= "/Supplement 0\n";
		$toUni .= ">> def\n";
		$toUni .= "/CMapName /Adobe-Identity-UCS def\n";
		$toUni .= "/CMapType 2 def\n";
		$toUni .= "1 begincodespacerange\n";
		$toUni .= "<0000> <FFFF>\n";
		$toUni .= "endcodespacerange\n";
		$toUni .= "1 beginbfrange\n";
		$toUni .= "<0000> <FFFF> <0000>\n";
		$toUni .= "endbfrange\n";
		$toUni .= "endcmap\n";
		$toUni .= "CMapName currentdict /CMap defineresource pop\n";
		$toUni .= "end\n";
		$toUni .= "end";
		$w->put('<</Length '.(strlen($toUni)).'>>');
		$w->putStream($toUni);
		$w->put('endobj');

		// 3. CIDSystemInfo dictionary
		$w->addObj();
		$w->put('<</Registry (Adobe)'); 
		$w->put('/Ordering (UCS)');
		$w->put('/Supplement 0');
		$w->put('>>');
		$w->put('endobj');

		// 4. Font descriptor
		$w->addObj();
		$w->put('<</Type /FontDescriptor');
		$w->put('/FontName /'.$fontname);
		foreach($this->desc as $kd => $v) {
			if ($kd === 'Flags') {
				// SYMBOLIC font flag
				$vi = (int) $v;
				$vi = $vi | 4;
				$v = (string) ($vi & ~32);
			}
			$w->put(' /'.$kd.' '.$v);
		}
		$w->put('/FontFile2 '.($this->n + 6).' 0 R');
		$w->put('>>');
		$w->put('endobj');

		// 5. Embed CIDToGIDMap
		// A specification of the mapping from CIDs to glyph indices
		$codeToGlyph = $this->codeToGlyph;
		$cidtogidmap = new StringBuffer();
		$cidtogidmap->append("\x00\x00"); // first entry always reset
		for($cid = 1; $cid <= 65535; $cid++) {
			if( isset($codeToGlyph[$cid]) ) {
				$glyph = $codeToGlyph[$cid];
				$cidtogidmap->append(pack("n", $glyph));
			} else {
				$cidtogidmap->append("\x00\x00");
			}
		}
		$cidtogidmap_compressed = gzcompress($cidtogidmap->__toString());
		$w->addObj();
		$w->put('<</Length '.strlen($cidtogidmap_compressed).'');
		$w->put('/Filter /FlateDecode');
		$w->put('>>');
		$w->putStream($cidtogidmap_compressed);
		$w->put('endobj');

		// 6. Font file
		$w->addObj();
		$w->put('<</Length '.strlen($fontstream));
		$w->put('/Filter /FlateDecode');
		$w->put('/Length1 '.$ttfontsize);
		$w->put('>>');
		$w->putStream($fontstream);
		$w->put('endobj');
	}


}
