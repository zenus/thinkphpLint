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
	require_module 'gd';
	require_module 'spl';
	require_module 'pcre';
.*/

require_once __DIR__ . "/../../../all.php";

use RuntimeException;
use ErrorException;
use it\icosaedro\containers\Printable;

/**
 * Base class for all the 1D barcode creation algorithms. Others specialized
 * classes in this namespace implement several bar code formats. For example,
 * here is how a PNG containing an ISBN book code can be generated:
 * <pre>
 *	$isbn = "9780131471498";
 *	$b = new com\tecnick\barcode\EAN($isbn, 13);
 *	$png = $b-&gt;getPNG();
 * </pre>
 * @version $Date: 2015/02/27 13:00:49 $
 * @author Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
 * @license GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
 */
abstract class Barcode implements Printable {
	
	/**
	 * Payload data as set in the constructor method. The actual barcode
	 * generated might include other codes as required by its specific algorithm
	 * (start and stop codes, checksum, error correction code, etc.): these
	 * added codes are not contained here because they do not belong to the
	 * payload.
	 */
	protected $payload = "";

	/**
	 * Bars that represent this symbol.
	 * @var Bar[int]
	 */
	protected $bars;
	
	/**
	 * Expected binary readout from a barcode reader set in basic mode,
	 * possibly including transcoding of extended charsets, checksum and more.
	 * @var string
	 */
	protected $readout;
	
	
	/**
	 * Returns the payload data that were passed to the constructor. The actual
	 * barcode generated might include even more codes as required by its
	 * specific algorithm (start and stop codes, checksum, error correction
	 * code, etc.) but these are not returned.
	 * @return string
	 */
	public function getPayload() {
		return $this->payload;
	}
	
	
	/**
	 * Returns the expected binary readout from a barcode scanner set in basic
	 * mode, possibly including extended charsets transcoding, checksum and
	 * more. May contain arbitrary binary data, for example a binary checksum.
	 * For most of the modern barcode specification, the readout exactly matches
	 * the payload.
	 * See the documentation about each specific implementation for details.
	 * @return string
	 */
	public function getReadout() {
		return $this->readout;
	}
	

	/**
	 * Return a matrix representations of barcode. 1 = black, 0 = white.
 	 * @return Bar[int]
	 */
	public function getBars() {
		return $this->bars;
	}
	
	
	public function getWidth() {
		$w = 0;
		foreach($this->bars as $bar)
			$w += $bar->w;
		return $w;
	}
	
	
	public function getHeight() {
		$h = 0;
		foreach($this->bars as $bar)
			$h = (int) max($h, $bar->h);
		return $h;
	}
	
	
	/**
	 * Returns a textual representation of this barcode, mostly intended for
	 * debugging purposes.
	 * @return string Textual representation of this barcode.
	 */
	public function __toString() {
		$s = "[code=" . $this->payload
			. ",w=" . $this->getWidth()
			. ",h=" . $this->getHeight()
			. ",bars=";
		foreach($this->bars as $bar)
			$s .= $bar;
		return $s . "]";
	}
	

	/**
	 * Convert binary barcode sequence to barcode array. For example, "1101"
	 * generates a 2 units width black bar for "11", a one unit width white bar
	 * for "0" and a one unit black bar for "1".
	 * @param string $seq Binary sequence, "1"=black bar, "0"=white bar;
	 * ajacent bars of the same color are merged into a single Bar object.
	 * @return void
	 */
	protected function encodeBars($seq) {
		$len = strlen($seq);
		$seq .= "X";
		$w = 0;
		$bars = /*. (Bar[int]) .*/ array();
		for ($i = 0; $i < $len; ++$i) {
			$w += 1;
			if ($seq[$i] !== $seq[$i+1]) {
				$bars[] = new Bar($seq[$i] === '1', $w, 1, 0);
				$w = 0;
			}
		}
		$this->bars = $bars;
	}
	

	/**
	 * Return a SVG string representation of this barcode.
	 * @param int $w Minimum width of a single bar in user units.
	 * @param int $h Height of barcode in user units.
	 * @param string $color Foreground color (in SVG format) for bar elements.
 	 * @return string SVG code.
	 */
	public function getSVG($w=2, $h=30, $color='black') {
		$maxw = $this->getWidth();
		$maxh = $this->getHeight();
		// replace table for special characters
		$repstr = array("\0" => '', '&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
		$svg = '<'.'?'.'xml version="1.0" standalone="no"'.'?'.'>'."\n";
		$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
		$svg .= '<svg width="'.round(($maxw * $w), 3).'" height="'.$h.'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";
		$svg .= "\t".'<desc>'.strtr($this->payload, $repstr).'</desc>'."\n";
		$svg .= "\t".'<g id="bars" fill="'.$color.'" stroke="none">'."\n";
		$x = 0;
		foreach ($this->bars as $bar) {
			$bw = $bar->w * $w;
			$bh = (int) ($bar->h * $h / $maxh);
			if ($bar->t) {
				$y = (int) ($bar->p * $h / $maxh);
				$svg .= "\t\t".'<rect x="'.$x.'" y="'.$y
					.'" width="'.$bw.'" height="'.$bh.'" />'."\n";
			}
			$x += $bw;
		}
		$svg .= "\t".'</g>'."\n";
		$svg .= '</svg>'."\n";
		return $svg;
	}
	

	/**
	 * Return an HTML representation of this barcode.
	 * @param int $w Width of a single bar element in pixels.
	 * @param int $h Height of a single bar element in pixels.
	 * @param string $color Foreground color for bar elements.
 	 * @return string HTML code.
	 */
	public function getHTML($w=2, $h=30, $color='black') {
		$maxw = $this->getWidth();
		$maxh = $this->getHeight();
		$html = '<div style="font-size:0;position:relative;width:'.($maxw * $w).'px;height:'.($h).'px;">'."\n";
		// print bars
		$x = 0.0;
		foreach ($this->bars as $bar) {
			$bw = round(($bar->w * $w), 3);
			$bh = round(($bar->h * $h / $maxh), 3);
			if ($bar->t) {
				$y = round(($bar->p * $h / $maxh), 3);
				$html .= '<div style="background-color:'.$color
					.';width:'.$bw.'px;height:'.$bh.'px;position:absolute;left:'.$x.'px;top:'.$y.'px;">&nbsp;</div>'."\n";
			}
			$x += $bw;
		}
		$html .= '</div>'."\n";
		return $html;
	}
	

	/**
	 * Return a GD image representation of this barcode created with
	 * {@link imagecreate()} and that allows further processing with the other
	 * functions of the GD library. Bars are drawn in black color (0,0,0) over
	 * a white background (255,255,255). A transparent background can be set
	 * later over the retrieved $gd image by calling the function
	 * {@link imagecolortransparent($gd,array(0,0,0))}.
	 * @param int $w Width of a single bar element in pixels.
	 * @param int $h Height of a single bar element in pixels.
	 * @param int $m Margin to the border of the image.
	 * @param int[int] $color RGB (0-255) foreground color for bar elements.
 	 * @return resource GD resource handle of the generated image.
	 */
	public function getGD($w=2, $h=30, $m = 5, $color=array(0,0,0)) {
		// calculate image size
		$maxw = $this->getWidth();
		$maxh = $this->getHeight();
		$width = $m + $maxw * $w + $m;
		$height = $m + $h + $m;
		$gd = imagecreate($width, $height);
		$bgcol = imagecolorallocate($gd, 255, 255, 255);
		//imagecolortransparent($png, $bgcol);
		$fgcol = imagecolorallocate($gd, $color[0], $color[1], $color[2]);
		$x = $m;
		foreach ($this->bars as $bar) {
			$bw = $bar->w * $w;
			$bh = (int) ($bar->h * $h / $maxh);
			if ($bar->t) {
				$y = $m + (int) ($bar->p * $h / $maxh);
				imagefilledrectangle($gd, $x, $y, $x + $bw - 1, $y + $bh - 1, $fgcol);
			}
			$x += $bw;
		}
		return $gd;
	}
	

	/**
	 * Return a PNG image representation of this barcode.
	 * @param int $w Width of a single bar element in pixels.
	 * @param int $h Height of a single bar element in pixels.
	 * @param int $m Margin to the border of the image.
	 * @param int[int] $color RGB (0-255) foreground color for bar elements.
 	 * @return string PNG image.
	 */
	public function getPNG($w=2, $h=30, $m = 5, $color=array(0,0,0)) {
		$gd = $this->getGD($w, $h, $m, $color);
		ob_start();
		try {
			if( ! imagepng($gd) )
				throw new RuntimeException("conversion to PNG failed");
		}
		catch(ErrorException $e){
			ob_get_clean();
			throw new RuntimeException($e->getMessage());
		}
		imagedestroy($gd);
		return ob_get_clean();
	}
	
}
