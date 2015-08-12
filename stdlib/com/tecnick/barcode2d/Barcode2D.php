<?php
//============================================================+
// File name   : tcpdf_barcodes_2d.php (extract of)
// Version     : 1.0.015
// Begin       : 2009-04-07
// Last Update : 2014-05-20
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2009-2014 Nicola Asuni - Tecnick.com LTD
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
// -------------------------------------------------------------------
//
// Description : PHP class to creates array representations for
//               2D barcodes to be used with TCPDF.
//
//============================================================+

namespace com\tecnick\barcode2d;

/*.
	require_module 'standard';
	require_module 'hash';
	require_module 'gd';
	require_module 'spl';
.*/

require_once __DIR__ . "/../../../all.php";

use RuntimeException;
use ErrorException;
use it\icosaedro\containers\Printable;

/**
 * Base class for all the 2D barcode creation algorithms. Others specialized
 * classes in this namespace implement several 2D bar code formats.
 * @version $Date: 2015/02/26 19:21:50 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
abstract class Barcode2D implements Printable {
	
	protected $num_cols = 0;
	protected $num_rows = 0;
	
	/**
	 * Data encoded by this barcode.
	 * @var string
	 */
	protected $payload;
	
	/**
	 * Resulting matrix of dots: 0 white, 1 black.
	 * @var int[int][int]
	 */
	protected $matrix;
	
	/**
	 * Returns a textual representation of this barcode, mostly intended for
	 * debugging purposes.
	 * @return string Textual representation of this barcode.
	 */
	public function __toString() {
		$s = "[cols=" . $this->num_cols . ", rows=" . $this->num_rows;
		foreach($this->matrix as $row){
			$s .= ", ";
			foreach($row as $c)
				$s .= "$c";
		}
		return $s . "]";
	}
	

	/**
	 * Return a rectangular matrix representations of this barcode.
	 * Entries 0 are white, entries 1 are black.
 	 * @return array
	 */
	public function getMatrix() {
		return $this->matrix;
	}

	/**
	 * Return a SVG string representation of this barcode.
	 * @param int $w Width of a single rectangle element in user units.
	 * @param int $h Height of a single rectangle element in user units.
	 * @param string $color (string) Foreground color (in SVG format) for bar elements (background is transparent).
 	 * @return string SVG string representation of this barcode.
 	 */
	public function getSVG($w=3, $h=3, $color='black') {
		// replace table for special characters
		$repstr = array("\0" => '', '&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
		$svg = '<'.'?'.'xml version="1.0" standalone="no"'.'?'.'>'."\n";
		$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
		$svg .= '<svg width="'.round(($this->num_cols * $w), 3).'" height="'.round(($this->num_rows * $h), 3).'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";
		$svg .= "\t".'<desc>'.strtr($this->payload, $repstr).'</desc>'."\n";
		$svg .= "\t".'<g id="elements" fill="'.$color.'" stroke="none">'."\n";
		// print barcode elements
		$y = 0;
		// for each row
		for ($r = 0; $r < $this->num_rows; ++$r) {
			$x = 0;
			// for each column
			for ($c = 0; $c < $this->num_cols; ++$c) {
				if ($this->matrix[$r][$c] == 1) {
					// draw a single barcode cell
					$svg .= "\t\t".'<rect x="'.$x.'" y="'.$y.'" width="'.$w.'" height="'.$h.'" />'."\n";
				}
				$x += $w;
			}
			$y += $h;
		}
		$svg .= "\t".'</g>'."\n";
		$svg .= '</svg>'."\n";
		return $svg;
	}

	/**
	 * Return an HTML representation of barcode.
	 * @param int $w (int) Width of a single rectangle element in pixels.
	 * @param int $h (int) Height of a single rectangle element in pixels.
	 * @param string $color (string) Foreground color for bar elements (background is transparent).
 	 * @return string HTML code.
 	 */
	public function getHTML($w=10, $h=10, $color='black') {
		$html = '<div style="font-size:0;position:relative;width:'.($w * $this->num_cols).'px;height:'.($h * $this->num_rows).'px;">'."\n";
		// print barcode elements
		$y = 0;
		// for each row
		for ($r = 0; $r < $this->num_rows; ++$r) {
			$x = 0;
			// for each column
			for ($c = 0; $c < $this->num_cols; ++$c) {
				if ($this->matrix[$r][$c] == 1) {
					// draw a single barcode cell
					$html .= '<div style="background-color:'.$color.';width:'.$w.'px;height:'.$h.'px;position:absolute;left:'.$x.'px;top:'.$y.'px;">&nbsp;</div>'."\n";
				}
				$x += $w;
			}
			$y += $h;
		}
		$html .= '</div>'."\n";
		return $html;
	}

	/**
	 * Return a PNG image representation of barcode.
	 * @param int $w Width of a single rectangle element in pixels.
	 * @param int $h Height of a single rectangle element in pixels.
	 * @param int[int] $color RGB (0-255) foreground color for bar elements.
 	 * @return string PNG image representation of barcode.
 	 */
	public function getPNG($w=3, $h=3, $color=array(0,0,0)) {
		// calculate image size
		$width = ($this->num_cols * $w);
		$height = ($this->num_rows * $h);
//		if (function_exists('imagecreate')) {
//			// GD library
			$imagick = false;
			$png = imagecreate($width, $height);
			$bgcol = imagecolorallocate($png, 255, 255, 255);
//			imagecolortransparent($png, $bgcol);
			$fgcol = imagecolorallocate($png, $color[0], $color[1], $color[2]);
//		} elseif (extension_loaded('imagick')) {
//			$imagick = true;
//			$bgcol = new imagickpixel('rgb(255,255,255');
//			$fgcol = new imagickpixel('rgb('.$color[0].','.$color[1].','.$color[2].')');
//			$png = new Imagick();
//			$png->newImage($width, $height, 'none', 'png');
//			$bar = new imagickdraw();
//			$bar->setfillcolor($fgcol);
//		} else {
//			return false;
//		}
		// print barcode elements
		$y = 0;
		// for each row
		for ($r = 0; $r < $this->num_rows; ++$r) {
			$x = 0;
			// for each column
			for ($c = 0; $c < $this->num_cols; ++$c) {
				if ($this->matrix[$r][$c] == 1) {
					// draw a single barcode cell
//					if ($imagick) {
//						$bar->rectangle($x, $y, ($x + $w - 1), ($y + $h - 1));
//					} else {
						imagefilledrectangle($png, $x, $y, ($x + $w - 1), ($y + $h - 1), $fgcol);
//					}
				}
				$x += $w;
			}
			$y += $h;
		}
//		if ($imagick) {
//			$png->drawimage($bar);
//			return $png;
//		} else {
			ob_start();
			try {
				if( ! imagepng($png) )
					throw new RuntimeException("conversion to PNG failed");
			}
			catch(ErrorException $e){
				ob_get_clean();
				throw new RuntimeException($e->getMessage());
			}
			$imagedata = ob_get_clean();
			imagedestroy($png);
			return $imagedata;
//		}
	}
	
}
