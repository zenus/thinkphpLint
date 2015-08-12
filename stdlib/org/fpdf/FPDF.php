<?php
namespace org\fpdf;

/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'zlib';
.*/

require_once __DIR__ . "/../../all.php";

use ErrorException;
use InvalidArgumentException;
use it\icosaedro\utils\StringBuffer;
use it\icosaedro\utils\Strings;
use it\icosaedro\utils\UString;
use it\icosaedro\containers\HashMap;
use RuntimeException;


/**
 * Guess what?
 * @access private
 */
class Rect {
	public $x = 0.0, $y = 0.0, $w = 0.0, $h = 0.0;
	
	/**
	 * @param double $x
	 * @param double $y
	 * @param double $w
	 * @param double $h
	 */
	public function __construct($x, $y, $w, $h)
	{
		$this->x = $x;
		$this->y = $y;
		$this->w = $w;
		$this->h = $h;
	}
}


/**
 * Clickable area of the document.
 * @access private
 */
class LinkArea {

	/**
	 * @var Rect
	 */
	public $area;
	
	/**
	 * If not-null, the destination URI.
	 * @var string
	 */
	public $uri;
	
	/**
	 * If $uri==NULL, the internal link number as returned by FPDF::SetLink().
	 * @var int
	 */
	public $internal_link_no = 0;

	/**
	 * @param Rect $area
	 * @param string $uri Destination URI. If null, the destination is an
	 * internal link.
	 * @param int $internal_link_no Number of the internal link as returned
	 * by FPDF::SetLink().
	 */
	function __construct($area, $uri, $internal_link_no)
	{
		$this->area = $area;
		$this->uri = $uri;
		$this->internal_link_no = $internal_link_no;
	}
}


/**
 * Target of an internal link to the document.
 * @access private
 */
class LinkTarget {

	/**
	 * Number of the target page.
	 * @var int
	 */
	public $page = 0;
	
	/**
	 * Ordinate of target position.
	 * @var double
	 */
	public $y = 0.0;
	
	/**
	 * 
	 * @param int $page
	 * @param double $y
	 */
	public function __construct($page, $y)
	{
		$this->page = $page;
		$this->y = $y;
	}
}


/**
 * PDF document generator. This source is based on a work of Olivier Plathey
 * (FPDF 1.7) plus support for external TrueType font by Ian Back (tFPDF 1.24),
 * here adapted to pass the validation of PHPLint.
 * This class can generate a PDF 1.3 file with embedded fonts and images.
 * Unicode strings are supported through the {@link it\icosaedro\utils\UString}
 * class.
 * <p>This example generates a simple document:
 * <pre>
	use it\icosaedro\fpdf\FPDF;
	$pdf = new FPDF();
	$pdf-&gt;addPage();
	$pdf-&gt;cell(40,10,'Hello World!');
	$pdf-&gt;output('hello.pdf', 'F');
 * </pre>
 * <p>More examples are available under the directory test/stdlib/org/fpdf
 * of the PHPLint package. They illustrates how fonts and styles can be applied
 * to text; how text can be formatted in pages with header, footer and page
 * numbers; tables; images; internal and external links.
 * <p>Fonts are supported as external objects {@link ./FontCore.html} and
 * {@link ./FontTrueType.html}.
 * @version $Date: 2015/03/05 17:38:03 $
 * @author Olivier Plathey (original author of FPDF)
 * @author Ian Back <ianb@bpm1.com> (Unicode and TrueType support)
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software to use, copy, modify, distribute,
 * sublicense, and/or sell copies of the software, and to permit persons to
 * whom the software is furnished to do so.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED.
 * @deprecated The FPDF class is still under development and, although being
 * perfectly working, it may change in future.
 */
class FPDF implements PdfObjWriterInterface
{

const VERSION = '$Date: 2015/03/05 17:38:03 $';

/**
 * Version of the generated PDF code.
 */
const PDF_VERSION = "1.3";

/**
 * @access private
 */
const
	STATE_EMPTY = 0,
	STATE_OPEN = 1,
	STATE_PAGE = 2,
	STATE_CLOSE = 3;

/**
 * Current document state, see STATE_* constants.
 * @var int
 */
protected $state = self::STATE_EMPTY;

/**
 * Current page number.
 * @var int
 */
protected $page = 0;
/**
 * Current object number.
 * @var int
 */
protected $n = 0;
/**
 * Buffer holding in-memory PDF.
 * @var StringBuffer
 */
private $buffer;
/**
 * Array of object offsets in the destination PDF buffer.
 * @var int[int]
 */
protected $offsets;
/**
 * Array containing pages.
 * @var StringBuffer[int]
 */
private $pages;
/**
 * Compression flag.
 * @var boolean
 */
protected $compress = FALSE;
/**
 * Scale factor (number of points in user unit).
 * @var double
 */
public $k = 1.0;
/**
 * Orientation used for new added pages.
 * @var string
 */
public $defOrientation = "";
/**
 * Orientation of the current page.
 * @var string
 */
public $curOrientation = "";
/**
 * Standard page sizes (pt).
 * @var double[string][int]
 */
public $stdPageSizes = array(
	'a3'=>array(841.89,1190.55),
	'a4'=>array(595.28,841.89),
	'a5'=>array(420.94,595.28),
	'letter'=>array(612,792),
	'legal'=>array(612,1008));
/**
 * Page size used for new added pages, in portrait orientation (pt).
 * @var double[int]
 */
public $defPageSize;
/**
 * Size of the current page in portrait orientation (pt).
 * @var double[int]
 */
public $curPageSize;
/**
 * Each page size, accounting for its orientation (pt). First index is the
 * page number starting from 1.
 * @var double[int][int]
 */
public $pageSizes;
/**
 * Width of the current page, accounting for the current page orientation
 * (user's units).
 * @var double
 */
public $w = 1.0;
/**
 * Height of the current page, accounting for the current page orientation
 * (user's units).
 * @var double
 */
public $h = 1.0;
/**
 * Left margin.
 * @var double
 */
protected $lMargin = 1.0;
/**
 * Top margin.
 * @var double
 */
protected $tMargin = 1.0;
/**
 * Right margin.
 * @var double
 */
protected $rMargin = 1.0;
/**
 * Page break margin.
 * @var double
 */
protected $bMargin = 1.0;
/**
 * Cell margin.
 * @var double
 */
protected $cMargin = 1.0;
/**
 * Current position (user's units).
 * @var double
 */
protected $x = 1.0, $y = 1.0;
/**
 * Height of last printed cell.
 * @var double
 */
protected $lasth = 1.0;
/**
 * Line width in user unit.
 * @var double
 */
protected $lineWidth = 1.0;
/**
 * Array of core font names.
 * @var string[int]
 */
public $coreFonts;
/**
 * Used fonts; key and value are the font.
 * @var HashMap
 */
protected $fonts;
/**
 * Current font style.
 * @var string
 */
public $fontStyle;
/**
 * Underlining flag.
 * @var boolean
 */
public $underline = FALSE;
/**
 * Current font.
 * @var Font
 */
public $currentFont;
/**
 * Current font size in points.
 * @var double
 */
public $fontSizePt = 0.0;
/**
 * Current font size in user unit.
 * @var double
 */
public $fontSize = 0.0;
/**
 * Commands for drawing color.
 * @var string
 */
protected $drawColor;
/**
 * Commands for filling color.
 * @var string
 */
protected $fillColor;
/**
 * Commands for text color.
 * @var string
 */
protected $textColor;
/**
 * Indicates whether fill and text colors are different.
 * @var boolean
 */
protected $colorFlag = FALSE;
/**
 * Word spacing.
 * @var double
 */
protected $ws = 0.0;
/**
 * Embedded images; key and value are the image.
 * @var HashMap
 */
protected $images;
/**
 * Array of links in pages.
 * @var LinkArea[int][int]
 */
private $pageLinks;
/**
 * Array of internal links.
 * @var LinkTarget[int]
 */
private $links;
/**
 * Automatic page breaking.
 * @var boolean
 */
protected $autoPageBreak = FALSE;
/**
 * Threshold used to trigger page breaks.
 * @var double
 */
protected $pageBreakTrigger = 0.0;
/**
 * Flag set when processing header.
 * @var boolean
 */
protected $inHeader = FALSE;
/**
 * Flag set when processing footer.
 * @var boolean
 */
protected $inFooter = FALSE;
/**
 * Display mode in viewer: 'fullpage', 'fullwidth', 'real', 'default' or
 * a double number.
 * @var mixed
 */
protected $zoomMode;
/**
 * Layout display mode.
 * @var string
 */
protected $layoutMode;
/**
 * Title.
 * @var UString
 */
private $title;
/**
 * Subject.
 * @var UString
 */
private $subject;
/**
 * Author.
 * @var UString
 */
private $author;
/**
 * Keywords.
 * @var UString
 */
private $keywords;
/**
 * Creator.
 * @var UString
 */
private $creator;
/**
 * Alias for total number of pages.
 * @var string
 */
protected $aliasNbPages;


private function beginPage()
{
	$this->page++;
	$this->pages[$this->page] = new StringBuffer();
	$this->state = self::STATE_PAGE;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	// Check page size and orientation
	$orientation = $this->defOrientation;
	$size = $this->defPageSize;
	if($orientation==='P')
	{
		$this->w = $size[0]/$this->k;
		$this->h = $size[1]/$this->k;
	}
	else
	{
		$this->w = $size[1]/$this->k;
		$this->h = $size[0]/$this->k;
	}
	$this->pageBreakTrigger = $this->h-$this->bMargin;
	$this->curOrientation = $orientation;
	$this->curPageSize = $size;
	$this->pageSizes[$this->page] = array($this->w*$this->k, $this->h*$this->k);
}


private function endPage()
{
	$this->state = self::STATE_OPEN;
}


/**
 * Escape special characters in strings.
 * @param string $s
 */
private static function escape($s)
{
	$s = (string) str_replace('\\','\\\\',$s);
	$s = (string) str_replace('(','\\(',$s);
	$s = (string) str_replace(')','\\)',$s);
	$s = (string) str_replace("\r",'\\r',$s);
	return $s;
}


private static function encodeString(UString $s)
{
	if (Strings::isASCII($s->toUTF8()))
		$t = $s->toASCII();
	else
		$t = "\xfe\xff" . $s->toUCS2BE();
	return "(" . self::escape($t) . ")";
}


private function encodePageText(UString $s)
{
	if ($this->currentFont instanceof FontCore){
		// FIXME: invalid chars should be replaced by something, not removed
		return self::escape($s->toISO88591());
	} else {
		return self::escape($s->toUCS2BE());
	}
}


private function accountUsedChars(UString $s)
{
	$l = $s->length();
	$subset = & $this->currentFont->subset;
	for($i = 0; $i < $l; $i++)
		$subset[$s->codepointAt($i)] = TRUE;
}


/**
 * Returns the width of a string. A font must be selected.
 * @param UString $s The string whose length is to be computed.
 * @return double Width of the string (user's units).
 */
public function getStringWidth($s)
{
	$res = 0;
	$cw = $this->currentFont->charWidths;
	$l = $s->length();
	for($i = 0; $i < $l; $i++) {
		$codepoint = $s->codepointAt($i);
		if (isset($cw[$codepoint])){
			$w = $cw[$codepoint];
			if ($w == 0 || $w == 65535)
				$w = $this->currentFont->missingWidth;
		} else {
			$w = $this->currentFont->missingWidth;
		}
		$res += $w;
	}
	return $res*$this->fontSize/1000;
}


/**
 * Underline text.
 * @param double $x
 * @param double $y
 * @param UString $txt
 */
private function doUnderline($x, $y, $txt)
{
	$up = $this->currentFont->underlinePosition;
	$ut = $this->currentFont->underlineThickness;
	$w = $this->getStringWidth($txt)+$this->ws*substr_count($txt->toUTF8(),' ');
	return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->fontSize))*$this->k,$w*$this->k,-$ut/1000*$this->fontSizePt);
}


/**
 * Begin a new object. For internal use; should not be called by user's
 * application.
 */
public function addObj()
{
	$this->n++;
	$this->offsets[$this->n] = $this->buffer->length();
	$this->put($this->n.' 0 obj');
	return $this->n;
}


/**
 * Add a line to the document. For internal use; should not be called by user's
 * application.
 * @param string $s
 */
public function put($s)
{
	switch($this->state){
		
		case self::STATE_PAGE:
			$this->pages[$this->page]->append($s);
			$this->pages[$this->page]->append("\n");
			break;
		
		case self::STATE_OPEN:
			$this->buffer->append($s);
			$this->buffer->append("\n");
			break;
		
		default:
			throw new RuntimeException("state=" . $this->state);
	}
}


/**
 * For internal use only; should not be called by user's application.
 * @param string $s
 */
public function putStream($s)
{
	$this->put('stream');
	$this->put($s);
	$this->put('endstream');
}


/**
 * @throws ErrorException
 */
private function putPages()
{
	$nb = $this->page;
	if(strlen($this->aliasNbPages) > 0)
	{
		// Replace total no. of pages marker
		$r = UString::fromASCII("$nb")->toUCS2BE();
		$alias = UString::fromUTF8($this->aliasNbPages)->toUCS2BE();
		foreach($this->pages as $page){
			$content = $page->__toString();
			// Replace no. of pages using core fonts (1 byte / char encoding)
			$content = (string) str_replace($this->aliasNbPages,$nb,$content);
			// Replace no. of pages using Unicode fonts (UCS encoding)
			$content = (string) str_replace($alias,$r,$content);
			$page->setLength(0);
			$page->append($content);
		}
	}
	if($this->defOrientation==='P')
	{
		$wPt = $this->defPageSize[0];
		$hPt = $this->defPageSize[1];
	}
	else
	{
		$wPt = $this->defPageSize[1];
		$hPt = $this->defPageSize[0];
	}
	$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
	for($n=1;$n<=$nb;$n++)
	{
		// Page
		$this->addObj();
		$this->put('<</Type /Page');
		$this->put('/Parent 1 0 R');
		$this->put(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->pageSizes[$n][0],$this->pageSizes[$n][1]));
		$this->put('/Resources 2 0 R');
		if(isset($this->pageLinks[$n]))
		{
			// Links
			$annots = '/Annots [';
			foreach($this->pageLinks[$n] as $pl)
			{
				$rect = sprintf('%.2F %.2F %.2F %.2F',$pl->area->x,$pl->area->y,$pl->area->x+$pl->area->w,$pl->area->y-$pl->area->h);
				$annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
				if(strlen($pl->uri) > 0)
					$annots .= '/A <</S /URI /URI ('.self::escape($pl->uri).')>>>>';
				else
				{
					$l = $this->links[$pl->internal_link_no];
					$h = $this->pageSizes[$l->page][1];
					$annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l->page,$h-$l->y*$this->k);
				}
			}
			$this->put($annots.']');
		}
		$this->put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
		$this->put('/Contents '.($this->n+1).' 0 R>>');
		$this->put('endobj');
		// Page content
		$p = ($this->compress) ? gzcompress($this->pages[$n]->__toString()) : $this->pages[$n]->__toString();
		$this->addObj();
		$this->put('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->putStream($p);
		$this->put('endobj');
	}
	// Pages root
	$this->offsets[1] = $this->buffer->length();
	$this->put('1 0 obj');
	$this->put('<</Type /Pages');
	$kids = '/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids .= (3+2*$i).' 0 R ';
	$this->put($kids.']');
	$this->put('/Count '.$nb);
	$this->put(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
	$this->put('>>');
	$this->put('endobj');
}


private function putResourceDict()
{
	$this->put('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->put('/Font <<');
	foreach($this->fonts as $font_){
		$font = cast(Font::NAME, $font_);
		$this->put('/F'.$font->i.' '.$font->n.' 0 R');
	}
	$this->put('>>');
	$this->put('/XObject <<');
	foreach($this->images as $image_){
		$image = cast(Image::NAME, $image_);
		$this->put('/I'.$image->i.' '.$image->n.' 0 R');
	}
	$this->put('>>');
}


/**
 * @throws ErrorException
 */
private function putResources()
{
	foreach($this->fonts as $font_){
		$font = cast(Font::NAME, $font_);
		$font->put($this);
	}
	
	foreach($this->images as $image_){
		$image = cast(Image::NAME, $image_);
		$image->put($this, $this->compress);
	}
	
	// Resource dictionary
	$this->offsets[2] = $this->buffer->length();
	$this->put('2 0 obj');
	$this->put('<<');
	$this->putResourceDict();
	$this->put('>>');
	$this->put('endobj');
}


private function putInfo()
{
	$n = $this->addObj();
	$this->put('<<');
	$this->put('/Producer ' . self::encodeString(
			UString::fromASCII('FPDF/PHPLint '.self::VERSION)));
	if($this->title !== NULL)
		$this->put('/Title '.self::encodeString($this->title));
	if($this->subject !== NULL)
		$this->put('/Subject '.self::encodeString($this->subject));
	if($this->author !== NULL)
		$this->put('/Author '.self::encodeString($this->author));
	if($this->keywords !== NULL)
		$this->put('/Keywords '.self::encodeString($this->keywords));
	if($this->creator !== NULL)
		$this->put('/Creator '.self::encodeString($this->creator));
	$this->put('/CreationDate '.self::encodeString(
		UString::fromASCII('D:' . (string) str_replace(":", "'", date('YmdHisP')))));
	$this->put('>>');
	$this->put('endobj');
	return $n;
}

private function putCatalog()
{
	$n = $this->addObj();
	$this->put('<<');
	$this->put('/Type /Catalog');
	$this->put('/Pages 1 0 R');
	if($this->zoomMode==='fullpage')
		$this->put('/OpenAction [3 0 R /Fit]');
	elseif($this->zoomMode==='fullwidth')
		$this->put('/OpenAction [3 0 R /FitH null]');
	elseif($this->zoomMode==='real')
		$this->put('/OpenAction [3 0 R /XYZ null null 1]');
	else
		$this->put('/OpenAction [3 0 R /XYZ null null '.sprintf('%.2F',(double)$this->zoomMode/100).']');
	if($this->layoutMode==='single')
		$this->put('/PageLayout /SinglePage');
	elseif($this->layoutMode==='continuous')
		$this->put('/PageLayout /OneColumn');
	elseif($this->layoutMode==='two')
		$this->put('/PageLayout /TwoColumnLeft');
	$this->put('>>');
	$this->put('endobj');
	return $n;
}


/**
 * @throws ErrorException
 */
private function endDoc()
{
	$this->put('%PDF-'.self::PDF_VERSION);
	$this->putPages();
	$this->putResources();
	$info = $this->putInfo();
	$catalog = $this->putCatalog();
	// Cross-ref
	$o = $this->buffer->length();
	$this->put('xref');
	$this->put('0 '.($this->n+1));
	$this->put('0000000000 65535 f ');
	for($i=1;$i<=$this->n;$i++)
		$this->put(sprintf('%010d 00000 n ',$this->offsets[$i]));
	// Trailer
	$this->put('trailer');
	$this->put('<<');
	$this->put('/Size '.($this->n+1));
	$this->put('/Root '.$catalog.' 0 R');
	$this->put('/Info '.$info.' 0 R');
	$this->put('>>');
	$this->put('startxref');
	$this->put("$o");
	$this->put('%%EOF');
	$this->state = self::STATE_CLOSE;
}


/**
 * Defines the left, top and right margins. By default, they equal 1 cm. Call
 * this method to change them.
 * <p>See also: {@link self::setLeftMargin()}, {@link self::setTopMargin()},
 * {@link self::setRightMargin()}, {@link self::setAutoPageBreak()}. 
 * @param double $left Left margin.
 * @param double $top Top margin.
 * @param double $right Right margin. Default value is the left one.
 */
function setMargins($left, $top, $right=0.0)
{
	$this->lMargin = $left;
	$this->tMargin = $top;
	if(func_num_args() == 2)
		$right = $left;
	$this->rMargin = $right;
}

/**
 * Defines the left margin. The method can be called before creating the first
 * page.
 * @param double $margin The margin, user unit.
 */
function setLeftMargin($margin)
{
	$this->lMargin = $margin;
	if($this->page>0 && $this->x<$margin)
		$this->x = $margin;
}

/**
 * Defines the top margin. The method can be called before creating the first
 * page.
 * @param double $margin The margin, user units.
 */
function setTopMargin($margin)
{
	$this->tMargin = $margin;
}

/**
 * Defines the right margin. The method can be called before creating the first
 * page.
 * @param double $margin The margin, user units.
 */
function setRightMargin($margin)
{
	$this->rMargin = $margin;
}

/**
 * Enables or disables the automatic page breaking mode. When enabled, the
 * second parameter is the distance from the bottom of the page that defines
 * the triggering limit. By default, the mode is on and the margin is 2 cm.
 * <p>See also: {@link self::cell()}, {@link self::multiCell()},
 * {@link self::acceptPageBreak()}.
 * @param boolean $auto If mode should be on or off.
 * @param double $margin Distance from the bottom of the page.
 */
function setAutoPageBreak($auto, $margin=0)
{
	$this->autoPageBreak = $auto;
	$this->bMargin = $margin;
	$this->pageBreakTrigger = $this->h-$margin;
}

/**
 * Defines the way the document is to be displayed by the viewer. The zoom level
 * can be set: pages can be displayed entirely on screen, occupy the full width
 * of the window, use real size, be scaled by a specific zooming factor or use
 * viewer default (configured in the Preferences menu of Adobe Reader). The page
 * layout can be specified too: single at once, continuous display, two columns
 * or viewer default.
 * @param string $zoom The zoom to use. It can be one of the following string
 * values:
 * <ul>
 * <li><tt>fullpage</tt>: displays the entire page on screen</li>
 * <li><tt>fullwidth</tt>: uses maximum width of window</li>
 * <li><tt>real</tt>: uses real size (equivalent to 100% zoom)</li>
 * <li><tt>default</tt>: uses viewer default mode</li>
 * </ul>
 * or a number indicating the zooming factor to use. 
 * @param string $layout The page layout. Possible values are:
 * <ul>
 * <li><tt>single</tt>: displays one page at once</li>
 * <li><tt>continuous</tt>: displays pages continuously</li>
 * <li><tt>two</tt>: displays two pages on two columns</li>
 * <li><tt>default</tt>: uses viewer default mode</li>
 * </ul>
 * Default value is <tt>default</tt>. 
 * @throws InvalidArgumentException Invalid $zoom. Invalid $layout.
 */
function setDisplayMode($zoom, $layout='default')
{
	if($zoom==='fullpage' || $zoom==='fullwidth' || $zoom==='real' || $zoom==='default' || !is_string($zoom))
		$this->zoomMode = $zoom;
	else
		throw new InvalidArgumentException('Incorrect zoom display mode: '.$zoom);
	if($layout==='single' || $layout==='continuous' || $layout==='two' || $layout==='default')
		$this->layoutMode = $layout;
	else
		throw new InvalidArgumentException('Incorrect layout display mode: '.$layout);
}

/**
 * Activates or deactivates page compression. When activated, the internal
 * representation of each page is compressed, which leads to a compression ratio
 * of about 2 for the resulting document.
 * Compression is on by default. 
 * @param boolean $compress If compression must be enabled.
 */
function setCompression($compress)
{
	$this->compress = $compress;
}

/**
 * Defines the title of the document.
 * @param UString $title The title.
 */
function setTitle($title)
{
	$this->title = $title;
}

/**
 * Defines the subject of the document.
 * @param UString $subject The subject.
 */
function setSubject($subject)
{
	$this->subject = $subject;
}

/**
 * Defines the author of the document.
 * @param UString $author The name of the author.
 */
function setAuthor($author)
{
	$this->author = $author;
}

/**
 * Associates keywords with the document, generally in the form
 * 'keyword1 keyword2 ...'.
 * @param UString $keywords The list of keywords.
 */
function setKeywords($keywords)
{
	$this->keywords = $keywords;
}

/**
 * Defines the creator of the document. If the document was converted to PDF
 * from another format, the name of the conforming product that created the
 * original document from which it was converted.
 * @param UString $creator The name of the creator.
 */
function setCreator($creator)
{
	$this->creator = $creator;
}

/**
 * Defines a placeholder string for the total number of pages.
 * It will be substituted as the document is closed and the actual total number
 * of pages is known. Example:
 * <pre>
	class PDF extends FPDF
	{
		function footer()
		{
			// Select Helvetica bold 15
			$this-&gt;setFont(FontCore('helvetica', true, false),'',15);
			// Go to 1.5 cm from bottom
			$this-&gt;setY(-15);
			// Print current and total page numbers
			$this-&gt;cell(0,10,UString::fromASCII('Page '.$this-&gt;pageNo().'/{nb}'),0,0,'C');
		}
	}

	$pdf = new PDF();
	$pdf-&gt;aliasNbPages();
 * </pre>
 * <p>See also: {@link self::pageNo()}, {@link self::footer()}.
 * @param string $alias The alias. Default value: <tt>{nb}</tt>.
 */
function aliasNbPages($alias='{nb}')
{
	$this->aliasNbPages = $alias;
}


/**
 * This method is used to render the page header. It is automatically called by
 * addPage() and should not be called directly by the application.
 * The implementation in FPDF is empty, so you have to subclass it and override
 * the method if you want a specific header formatting. Example:
 * <pre>
	class PDF extends FPDF
	{
		function header()
		{
			// Select Helvetica bold 15
			$this-&gt;setFont(FontCore('helvetica', true, false),'',15);
			// Move to the right
			$this-&gt;cell(80);
			// Framed title
			$this-&gt;cell(30,10,UString::fromASCII('Title'),1,0,'C');
			// Line break
			$this-&gt;ln(20);
		}
	}
 * </pre>
 * <p>See also: {@link self::footer()}.
 * @throws ErrorException
 */
function header()
{
}


/**
 * This method is used to render the page footer. It is automatically called by
 * addPage() and close() and should not be called directly by the application.
 * The implementation in FPDF is empty, so you have to subclass it and override
 * the method if you want a specific processing. Example:
 * <pre>
	class PDF extends FPDF
	{
		function footer()
		{
			// Go to 1.5 cm from bottom
			$this-&gt;setY(-15);
			// Select Helvetica italic 8
			$this-&gt;setFont(FontCore('helvetica', false, true),'',8);
			// Print centered page number
			$this-&gt;cell(0,10,'Page '.$this-&gt;pageNo(),0,0,'C');
		}
	}
 * </pre>
 * <p>See also: {@link self::header()}.
 * @throws ErrorException
 */
function footer()
{
}


/**
 * Sets the font used to print character strings. The font object belongs to
 * an abstract class; currently 2 implementations are available: font core
 * and font TrueType. Core fonts are already embedded in any PDF reader,
 * so they are not added to the generated document. TrueType fonts, on the
 * contrary have to embedded in the generated document; to reduce the occupied
 * space, only the characters actually put in the document are copied in the
 * file.
 * <br>The same font can be set several times: it will be added to the document
 * only once.
 * <br>This method can be called before the first page is created and the font is
 * kept from page to page.
 * <br>If you just wish to change the current font size, it is simpler to call
 * {@link self::setFontSize()}.  Example:
 * <pre>
	// Times regular 12
	$this-&gt;setFont(FontCore('times', false, false),'',12);
	// Helvetica bold 14
	$this-&gt;setFont(FontCore('helvetica', true, false),'',14);
	// Times bold, italic and underlined 14
	$this-&gt;setFont(FontCore('helvetica', true, true),'U',14);
	// TrueType font underlined 12:
	$font = new FontTrueType(File::fromLocalEncoded(__DIR__.'/MyFont.ttf'));
	$this-&gt;setFont($font,'U',14);
 * </pre>
 * <p>See also: {@link self::setFontSize()},
 * {@link self::cell()}, {@link self::multiCell()}, {@link self::write()}.
 * @param Font $font The font. If NULL, uses the current font.
 * @param string $style Font style. Possible values are (case insensitive):
 * <ul>
 * <li>empty string: regular</li>
 * <li><tt>U</tt>: underline</li>
 * </ul>
 * The default value is regular.
 * @param double $size Font size in points. The default value is the current
 * size. If no size has been specified since the beginning of the document, the
 * value taken is 12.
 */
function setFont($font, $style='', $size=0.0)
{
	if( $font === NULL ){
		$font = $this->currentFont;
	} else {
		$font_ = $this->fonts->get($font);
		if( $font_ === NULL ){
			$this->fonts->put($font, $font);
			$font->i = $this->fonts->count() + 1;
		} else {
			$font = cast(Font::NAME, $font_);
		}
	}
	$style = strtoupper($style);
	if(strpos($style,'U')!==false)
	{
		$this->underline = true;
		$style = (string) str_replace('U','',$style);
	}
	else
		$this->underline = false;
	if($size==0)
		$size = $this->fontSizePt;
	// Test if font, style, size is already selected
	if($this->currentFont===$font && $this->fontStyle===$style && $this->fontSizePt==$size)
		return;
	// Select it
	$this->currentFont = $font;
	$this->fontStyle = $style;
	$this->fontSizePt = $size;
	$this->fontSize = $size/$this->k;
	if($this->page>0)
		$this->put(sprintf('BT /F%d %.2F Tf ET',$this->currentFont->i,$this->fontSizePt));
}


/**
 * Adds a new page to the document. If a page is already present, the
 * {@link self::footer()} method is called first to output the footer. Then the
 * page is added, the current position set to the top-left corner according to
 * the left and top margins, and {@link self::header()} is called to display
 * the header. The font which was set before calling is automatically restored.
 * There is no need to call {@link self::setFont()} again if you want to
 * continue with the same font. The same is true for colors and line width.
 * The origin of the coordinate system is at the top-left corner and increasing
 * ordinates go downwards.
 * <br>
 * See also: {@link self::header()}, {@link self::footer()},
 * {@link self::setMargins()}.
 * @throws ErrorException
 */
function addPage()
{
	if($this->state == self::STATE_EMPTY){
		$this->state = self::STATE_OPEN;
	}
	
	// Save current context before invoking footer()/header():
	$font = $this->currentFont;
	$style = $this->fontStyle.($this->underline ? 'U' : '');
	$fontsize = $this->fontSizePt;
	$lw = $this->lineWidth;
	$dc = $this->drawColor;
	$fc = $this->fillColor;
	$tc = $this->textColor;
	$cf = $this->colorFlag;
	if($this->page > 0)
	{
		// Close current page:
		// Page footer
		$this->inFooter = true;
		$this->footer();
		$this->inFooter = false;
		// Close page
		$this->endPage();
	}
	// Start new page
	$this->beginPage();
	// Set line cap style to square
	$this->put('2 J');
	// Set line width
	$this->lineWidth = $lw;
	$this->put(sprintf('%.2F w',$lw*$this->k));
	// Set font
	$this->currentFont = NULL; // foces font at the beginning of the page
	$this->setFont($font,$style,$fontsize);
	// Set colors
	$this->drawColor = $dc;
	if($dc!=='0 G')
		$this->put($dc);
	$this->fillColor = $fc;
	if($fc!=='0 g')
		$this->put($fc);
	$this->textColor = $tc;
	$this->colorFlag = $cf;
	// Page header
	$this->inHeader = true;
	$this->header();
	$this->inHeader = false;
	// Restore line width
	if($this->lineWidth!=$lw)
	{
		$this->lineWidth = $lw;
		$this->put(sprintf('%.2F w',$lw*$this->k));
	}
	// Restore font
	$this->setFont($font,$style,$fontsize);
	// Restore colors
	if($this->drawColor!==$dc)
	{
		$this->drawColor = $dc;
		$this->put($dc);
	}
	if($this->fillColor!==$fc)
	{
		$this->fillColor = $fc;
		$this->put($fc);
	}
	$this->textColor = $tc;
	$this->colorFlag = $cf;
}

/**
 * Terminates the PDF document. It is not necessary to call this method
 * explicitly because output() does it automatically. If the document contains
 * no pages, addPage() is called to prevent from getting an invalid document.
 * <p>See also: {@link self::output()}.
 * @throws ErrorException
 */
function close()
{
	if($this->state == self::STATE_CLOSE)
		return;
	if($this->page == 0)
		$this->addPage();
	// Page footer
	$this->inFooter = true;
	$this->footer();
	$this->inFooter = false;
	// Close page
	$this->endPage();
	// Close document
	$this->endDoc();
}

/**
 * Returns the current page number.
 * <p>See also: {@link self::aliasNbPages()}.
 * @return int Number of the current page, or zero if no page has been created
 * yet.
 */
function pageNo()
{
	return $this->page;
}

/**
 * Defines the color used for all drawing operations (lines, rectangles and
 * cell borders). The method can be called before the first page is created and
 * the value is retained from page to page.
 * <p>See also: {@link self::setFillColor()}, {@link self::setTextColor()},
 * {@link self::line()}, {@link self::rect()}, {@link self::cell()},
 * {@link self::multiCell()}.
 * @param int $r Red component (between 0 and 255).
 * @param int $g Green component (between 0 and 255). 
 * @param int $b Blue component (between 0 and 255).
 */
function setDrawColor($r, $g, $b)
{
	$this->drawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
	if($this->page>0)
		$this->put($this->drawColor);
}

/**
 * Defines the color used for all filling operations (filled rectangles and
 * cell backgrounds). The method can be called before the first page is created
 * and the value is retained from page to page.
 * <p>See also: {@link self::setDrawColor()}, {@link self::setTextColor()},
 * {@link self::rect()}, {@link self::cell()}, {@link self::multiCell()}.
 * @param int $r Red component (between 0 and 255).
 * @param int $g Green component (between 0 and 255). 
 * @param int $b Blue component (between 0 and 255).
 */
function setFillColor($r, $g, $b)
{
	$this->fillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->colorFlag = ($this->fillColor!==$this->textColor);
	if($this->page>0)
		$this->put($this->fillColor);
}

/**
 * Defines the color used for text. The method can be called before the first
 * page is created and the value is retained from page to page.
 * <p>See also: {@link self::setDrawColor()}, {@link self::setFillColor()},
 * {@link self::text()}, {@link self::cell()}, {@link self::multiCell()}.
 * @param int $r Red component (between 0 and 255).
 * @param int $g Green component (between 0 and 255). 
 * @param int $b Blue component (between 0 and 255).
 */
function setTextColor($r, $g, $b)
{
	$this->textColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->colorFlag = ($this->fillColor!==$this->textColor);
}

/**
 * Defines the line width of borders and shapes. By default, the value equals
 * 0.2 mm. The method can be called before the first page is created and the
 * value is retained from page to page.
 * <p>See also: {@link self::line()}, {@link self::rect()},
 * {@link self::cell()}, {@link self::multiCell()}.
 * @param double $width The width.
 */
function setLineWidth($width)
{
	$this->lineWidth = $width;
	if($this->page>0)
		$this->put(sprintf('%.2F w',$width*$this->k));
}

/**
 * Draws a line between two points.
 * <p>See also: {@link self::setLineWidth()}, {@link self::setDrawColor()}.
 * @param double $x1 Abscissa of first point.
 * @param double $y1 Ordinate of first point.
 * @param double $x2 Abscissa of second point.
 * @param double $y2 Ordinate of second point.
 */
function line($x1, $y1, $x2, $y2)
{
	$this->put(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

/**
 * Outputs a rectangle. It can be drawn (border only), filled (with no border)
 * or both.
 * <p>See also: {@link self::setLineWidth()}, {@link self::setDrawColor()},
 * {@link self::setFillColor()}.
 * @param double $x Abscissa of upper-left corner.
 * @param double $y Ordinate of upper-left corner.
 * @param double $w Width.
 * @param double $h Height.
 * @param string $style Style of rendering. Possible values are:
 * <ul>
 * <li>D or empty string: draw. This is the default value.</li>
 * <li>F: fill</li>
 * <li>DF or FD: draw and fill</li>
 * </ul>
 */
function rect($x, $y, $w, $h, $style='')
{
	if($style==='F')
		$op = 'f';
	elseif($style==='FD' || $style==='DF')
		$op = 'B';
	else
		$op = 'S';
	$this->put(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

/**
 * Defines the size of the current font.
 * <p>See also: {@link self::setFont()}.
 * @param int $size The size (in points).
 */
function setFontSize($size)
{
	if($this->fontSizePt==$size)
		return;
	$this->fontSizePt = $size;
	$this->fontSize = $size/$this->k;
	if($this->page>0)
		$this->put(sprintf('BT /F%d %.2F Tf ET',$this->currentFont->i,$this->fontSizePt));
}

/**
 * Creates a new internal link and returns its identifier. An internal link is
 * a clickable area which directs to another place within the document.
 * The identifier can then be passed to cell(), write(), image() or link().
 * The destination is defined with setLink().
 * See also: {@link self::cell()}, {@link self::write()}, {@link self::image()},
 * {@link self::link()}, {@link self::setLink()}.
 * @return int Generated internal link number.
 */
function addLink()
{
	$n = count($this->links);
	$this->links[$n] = new LinkTarget(0, 0.0);
	return $n;
}

/**
 * Defines the page and position an internal link points to.
 * @param int $link The link identifier returned by {@link self::addLink()}.
 * @param double $y Ordinate of target position; -1 indicates the current
 * position. The default value is 0 (top of page). 
 * @param int $page Number of target page; -1 indicates the current page. This
 * is the default value. 
 */
function setLink($link, $y=0.0, $page=-1)
{
	if($y==-1)
		$y = $this->y;
	if($page==-1)
		$page = $this->page;
	$this->links[$link]->page = $page;
	$this->links[$link]->y = $y;
}


/**
 * Puts a link on a rectangular area of the page. Text or image links are
 * generally put via {@link self::cell()}, {@link self::write()} or
 * {@link self::image()}, but this method can be useful for instance to define
 * a clickable area inside an image. 
 * @param double $x Abscissa of the upper-left corner of the rectangle.
 * @param double $y Ordinate of the upper-left corner of the rectangle.
 * @param double $w Width of the rectangle.
 * @param double $h Height of the rectangle.
 * @param mixed $link URL or identifier returned by {@link self::addLink()}.
 */
function link($x, $y, $w, $h, $link)
{
	$this->pageLinks[$this->page][] = new LinkArea(
		new Rect($x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, $h*$this->k),
		is_string($link)? (string) $link : (string) NULL,
		is_string($link)? 0 : (int) $link);
}


/**
 * Prints a character string. The origin is on the left of the first character,
 * on the baseline. This method allows to place a string precisely on the page,
 * but it is usually easier to use cell(), multiCell() or write() which are the
 * standard methods to print text.
 * <p>See also: {@link self::setFont()}, {@link self::setTextColor()},
 * {@link self::cell()}, {@link self::multiCell()}, {@link self::write()}.
 * @param double $x Abscissa of the origin.
 * @param double $y Ordinate of the origin.
 * @param UString $txt String to print.
 */
function text($x, $y, $txt)
{
	$this->accountUsedChars($txt);
	$txt2 = '('.$this->encodePageText($txt).')';
	$s = sprintf('BT %.2F %.2F Td %s Tj ET',$x*$this->k,($this->h-$y)*$this->k,$txt2);
	if($this->underline && $txt->length() > 0)
		$s .= ' '.$this->doUnderline($x,$y,$txt);
	if($this->colorFlag)
		$s = 'q '.$this->textColor.' '.$s.' Q';
	$this->put($s);
}


/**
 * Whenever a page break condition is met, the method is called, and the break
 * is issued or not depending on the returned value. The default implementation
 * returns a value according to the mode selected by setAutoPageBreak().
 * This method is called automatically and should not be called directly by the
 * application. In this example the method is overridden in an inherited class
 * in order to obtain a 3 column layout: 
 * <pre>
	class PDF extends FPDF
	{
		var $col = 0;

		function setCol($col)
		{
			// Move position to a column
			$this-&gt;col = $col;
			$x = 10+$col*65;
			$this-&gt;setLeftMargin($x);
			$this-&gt;setX($x);
		}

		function acceptPageBreak()
		{
			if($this-&gt;col&lt;2)
			{
				// Go to next column
				$this-&gt;setCol($this-&gt;col+1);
				$this-&gt;setY(10);
				return false;
			}
			else
			{
				// Go back to first column and issue page break
				$this-&gt;setCol(0);
				return true;
			}
		}
	}

	$pdf = new PDF();
	$pdf-&gt;addPage();
	for($i=1;$i&lt;=300;$i++)
		$pdf-&gt;cell(0,5,UString::fromASCII("Line $i"),0,1);
	$pdf-&gt;output();
 * </pre>
 * <p>See also: {@link self::setAutoPageBreak()}.
 */
function acceptPageBreak()
{
	return $this->autoPageBreak;
}


/**
 * Prints a cell (rectangular area) with optional borders, background color and
 * character string. The upper-left corner of the cell corresponds to the
 * current position. The text can be aligned or centered. After the call, the
 * current position moves to the right or to the next line. It is possible to
 * put a link on the text. If automatic page breaking is enabled and the cell
 * goes beyond the limit, a page break is done before outputting.
 * Example:
 * <pre>
 * // Set font
 * $pdf-&gt;setFont(FontCore::factory("helvetica", true, false),'',16);
 * // Move to 8 cm to the right
 * $pdf-&gt;cell(80);
 * // Centered text in a framed 20*10 mm cell and line break
 * $pdf-&gt;cell(20,10,UString::fromASCII('Title'),1,1,'C');
 * </pre>
 * See also: {@link self::setFont()}, {@link self::setDrawColor()},
 * {@link self::setFillColor()}, {@link self::setTextColor()},
 * {@link self::setLineWidth()}, {@link self::addLink()}, {@link self::ln()},
 * {@link self::multiCell()}, {@link self::write()},
 * {@link self::setAutoPageBreak()}. 
 * @param double $w Cell width. If 0, the cell extends up to the right margin.
 * @param double $h Cell height. Default value: 0.
 * @param UString $txt String to print.
 * @param mixed $border Indicates if borders must be drawn around the cell.
 * The value can be either a number:
 * <ul>
 * <li>0: no border</li>
 * <li>1: frame</li>
 * </ul>
 * or a string containing some or all of the following characters (in any
 * order):
 * <ul>
 * <li>L: left</li>
 * <li>T: top</li>
 * <li>R: right</li>
 * <li>B: bottom</li>
 * </ul>
 * Default value: 0. 
 * @param int $ln Indicates where the current position should go after the call.
 * Possible values are:
 * <ul>
 * <li>0: to the right</li>
 * <li>1: to the beginning of the next line</li>
 * <li>2: below</li>
 * </ul>
 * Putting 1 is equivalent to putting 0 and calling ln() just after. Default
 * value: 0. 
 * @param string $align Allows to center or align the text. Possible values are:
 * <ul>
 * <li>L or empty string: left align (default value)</li>
 * <li>C: center</li>
 * <li>R: right align</li>
 * </ul>
 * @param boolean $fill Indicates if the cell background must be painted
 * (true) or transparent (false). Default value: false. 
 * @param mixed $link URL or identifier returned by {@link self::addLink()}.
 * @throws ErrorException This exception comes from the signature of Footer()
 * and Header(). In fact, a page break can cause a call to AddPage() which, in
 * turn, calls these methods.
 */
function cell($w, $h=0, $txt=NULL, $border=0, $ln=0, $align='', $fill=false, $link=NULL)
{
	$k = $this->k;
	if($this->y+$h>$this->pageBreakTrigger && !$this->inHeader && !$this->inFooter && $this->acceptPageBreak())
	{
		// Automatic page break
		$x = $this->x;
		$ws = $this->ws;
		if($ws>0)
		{
			$this->ws = 0;
			$this->put('0 Tw');
		}
		$this->addPage();
		$this->x = $x;
		if($ws>0)
		{
			$this->ws = $ws;
			$this->put(sprintf('%.3F Tw',$ws*$k));
		}
	}
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$s = '';
	if($fill || $border===1)
	{
		if($fill)
			$op = ($border===1) ? 'B' : 'f';
		else
			$op = 'S';
		$s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}
	if(is_string($border))
	{
		$x = $this->x;
		$y = $this->y;
		if(strpos((string)$border,'L')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
		if(strpos((string)$border,'T')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
		if(strpos((string)$border,'R')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		if(strpos((string)$border,'B')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	}
	if($txt !== NULL && $txt->length() > 0)
	{
		if($align==='R')
			$dx = $w-$this->cMargin-$this->getStringWidth($txt);
		elseif($align==='C')
			$dx = ($w-$this->getStringWidth($txt))/2;
		else
			$dx = $this->cMargin;
		if($this->colorFlag)
			$s .= 'q '.$this->textColor.' ';

		// If multibyte, Tw has no effect - do word spacing using an adjustment before each space
//		if ($this->ws != 0.0) {
//			$this->accountUsedChars($txt);
//			//$space = $this->encodePageText(UString::fromASCII(' '));
//			// ...but this is faster:
//			if($this->CurrentFont instanceof FontCore)
//				$space = ' ';
//			else
//				$space = "\x00\x20";
//			$s .= sprintf('BT 0 Tw %.2F %.2F Td [',($this->x+$dx)*$k,($this->h-($this->y+0.5*$h+0.3*$this->FontSize))*$k);
//			$t = explode(' ',$txt->toUTF8());
//			$numt = count($t);
//			for($i=0;$i<$numt;$i++) {
//				$tx = $t[$i];
//				// FIXME: very inefficient
//				$tx = '('.$this->encodePageText(UString::fromUTF8($tx)).')';
//				$s .= sprintf('%s ',$tx);
//				if (($i+1)<$numt) {
//					$adj = -($this->ws*$this->k)*1000/$this->FontSizePt;
//					$s .= sprintf('%d(%s) ',$adj,$space);
//				}
//			}
//			$s .= '] TJ';
//			$s .= ' ET';
//		}
//		else {
			$this->accountUsedChars($txt);
			$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+0.5*$h+0.3*$this->fontSize))*$k,
					$this->encodePageText($txt));
//		}
		if($this->underline)
			$s .= ' '.$this->doUnderline($this->x+$dx,$this->y+0.5*$h+0.3*$this->fontSize,$txt);
		if($this->colorFlag)
			$s .= ' Q';
		if($link !== NULL){
			$this->link($this->x+$dx,$this->y+0.5*$h-0.5*$this->fontSize,$this->getStringWidth($txt),$this->fontSize,$link);
		}
	}
	if(strlen($s) > 0)
		$this->put($s);
	$this->lasth = $h;
	if($ln>0)
	{
		// Go to next line
		$this->y += $h;
		if($ln==1)
			$this->x = $this->lMargin;
	}
	else
		$this->x += $w;
}


/**
 * This method allows printing text with line breaks. They can be automatic
 * (as soon as the text reaches the right border of the cell) or explicit
 * (via the \n character). As many cells as necessary are output, one below the
 * other. Text can be aligned, centered or justified. The cell block can be
 * framed and the background painted.
 * <p>See also: {@link self::setFont()}, {@link self::setDrawColor()},
 * {@link self::setFillColor()}, {@link self::setTextColor()},
 * {@link self::setLineWidth()}, {@link self::cell()}, {@link self::write()},
 * {@link self::setAutoPageBreak()}.
 * @param double $w Width of cells. If 0, they extend up to the right margin of
 * the page.
 * @param double $h Height of cells.
 * @param UString $txt String to print.
 * @param mixed $mixed_border Indicates if borders must be drawn around the
 * cell block. The value can be either a number:
 * <ul>
 * <li><tt>0</tt>: no border</li>
 * <li><tt>1</tt>: frame</li>
 * </ul>
 * or a string containing some or all of the following characters (in any
 * order):
 * <ul>
 * <li><tt>L</tt>: left</li>
 * <li><tt>T</tt>: top</li>
 * <li><tt>R</tt>: right</li>
 * <li><tt>B</tt>: bottom</li>
 * </ul>
 * Default value: <tt>0</tt>.
 * @param string $align Sets the text alignment. Possible values are:
 * <ul>
 * <li><tt>L</tt>: left alignment</li>
 * <li><tt>C</tt>: center</li>
 * <li><tt>R</tt>: right alignment</li>
 * <li><tt>J</tt>: justification (default value)</li>
 * </ul>
 * @param boolean $fill Indicates if the cell background must be painted (true)
 * or transparent (false). Default value: false.
 * @throws ErrorException
 */
function multiCell($w, $h, $txt, $mixed_border=0, $align='J', $fill=false)
{
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin);
	// FIXME: why removing \r?
	//$s = $txt->replace(UString::fromASCII("\r"), UString::fromASCII(""));
	$s = $txt;
	$nb = $s->length();
	while($nb > 0 && $s->codepointAt($nb-1) == ord("\n"))
		$nb--;
	$b = "0";
	$border = (string) $mixed_border;
	$b2 = '';
	if($border!=='0')
	{
		if($border==="1")
		{
			$border = 'LTRB';
			$b = 'LRT';
			$b2 = 'LR';
		}
		else
		{
			$b2 = '';
			if(strpos($border,'L')!==false)
				$b2 .= 'L';
			if(strpos($border,'R')!==false)
				$b2 .= 'R';
			$b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
		}
	}
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0.0;
	$ns = 0;
	$nl = 1;
	$ls = 0.0;
	while($i<$nb)
	{
		// Get next character
		$c = $s->codepointAt($i);
		if($c == ord("\n"))
		{
			// Explicit line break
			if($this->ws > 0)
			{
				$this->ws = 0;
				$this->put('0 Tw');
			}
			$this->cell($w,$h,$s->substring($j, $i),$b,2,$align,$fill);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border!=='0' && $nl==2)
				$b = $b2;
			continue;
		}
		if($c == ord(' '))
		{
			$sep = $i;
			$ls = $l;
			$ns++;
		}
		
		$l += $this->getStringWidth(UString::chr($c));
		
		if($l>$wmax)
		{
			// Automatic line break
			if($sep==-1)
			{
				if($i == $j)
					$i++;
				if($this->ws > 0)
				{
					$this->ws = 0;
					$this->put('0 Tw');
				}
				$this->cell($w,$h,$s->substring($j,$i),$b,2,$align,$fill);
			}
			else
			{
				if($align === 'J')
				{
					$this->ws = ($ns>1) ? ($wmax-$ls)/($ns-1) : 0.0;
					$this->put(sprintf('%.3F Tw',$this->ws*$this->k));
				}
				$this->cell($w,$h,$s->substring($j,$sep),$b,2,$align,$fill);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border!=='0' && $nl==2)
				$b = $b2;
		}
		else
			$i++;
	}
	// Last chunk
	if($this->ws > 0)
	{
		$this->ws = 0;
		$this->put('0 Tw');
	}
	if($border !== '0' && strpos($border,'B')!==false)
		$b .= 'B';
	$this->cell($w,$h,$s->substring($j,$i),$b,2,$align,$fill);
	$this->x = $this->lMargin;
}


/**
 * This method prints text from the current position. When the right margin is
 * reached (or the \n character is met) a line break occurs and text continues
 * from the left margin. Upon method exit, the current position is left just at
 * the end of the text. It is possible to put a link on the text. Example:
 * <pre>
	// Begin with regular font
	$pdf-&gt;setFont(FontCore::factory('helvetica', false, false),'',14);
	$pdf-&gt;write(5,'Visit ');
	// Then put a blue underlined link
	$pdf-&gt;setTextColor(0,0,255);
	$pdf-&gt;setFont(NULL,'U');
	$pdf-&gt;write(5,'www.fpdf.org','http://www.fpdf.org');
 * </pre>
 * <p>See also: {@link self::setFont()}, {@link self::setTextColor()},
 * {@link self::addLink()}, {@link self::multiCell()},
 * {@link self::setAutoPageBreak()}.
 * @param double $h Line height.
 * @param UString $txt String to print.
 * @param mixed $link URL or identifier returned by {@link self::addLink()}.
 * @throws ErrorException
 */
function write($h, $txt, $link=NULL)
{
	$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin);
	// FIXME: why removing \r?
	//$s = $txt->replace(UString::fromASCII("\r"), UString::fromASCII(""));
	$s = $txt;
	$nb = $s->length();
	if($nb==1 && $s->toUTF8()===" ") {
		$this->x += $this->getStringWidth($s);
		return;
	}
	$sep = -1;
	$i = 0; // index current char analyzed
	$j = 0; // [0,$j] is the range already sent to output
	$l = 0.0;
	$nl = 1;
	while($i < $nb)
	{
		$c = $s->codepointAt($i);
		if($c == ord("\n"))
		{
			// Explicit line break
			$this->cell($w,$h,$s->substring($j, $i),0,2,'',FALSE,$link);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			if($nl == 1)
			{
				$this->x = $this->lMargin;
				$w = $this->w-$this->rMargin-$this->x;
				$wmax = ($w-2*$this->cMargin);
			}
			$nl++;
			continue;
		}
		if($c == ord(' '))
			$sep = $i;
		$l += $this->getStringWidth(UString::chr($c));
		if($l > $wmax)
		{
			// Automatic line break
			if($sep==-1)
			{
				if($this->x > $this->lMargin)
				{
					// Move to next line
					$this->x = $this->lMargin;
					$this->y += $h;
					$w = $this->w-$this->rMargin-$this->x;
					$wmax = ($w-2*$this->cMargin);
					$i++;
					$nl++;
					continue;
				}
				if($i == $j)
					$i++;
				$this->cell($w,$h,$s->substring($j, $i),0,2,'',FALSE,$link);
			}
			else
			{
				$this->cell($w,$h,$s->substring($j, $sep),0,2,'',FALSE,$link);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			if($nl == 1)
			{
				$this->x = $this->lMargin;
				$w = $this->w-$this->rMargin-$this->x;
				$wmax = ($w-2*$this->cMargin);
			}
			$nl++;
		}
		else
			$i++;
	}
	// Last chunk
	if($i > $j)
		$this->cell($l,$h,$s->substring($j, $i),0,0,'',FALSE,$link);
}


/**
 * Performs a line break. The current abscissa goes back to the left margin and
 * the ordinate increases by the amount passed in parameter.
 * <p>See also: {@link self::cell()}.
 * @param double $h The height of the break. By default, the value equals the
 * height of the last printed cell. 
 */
function ln($h=-1)
{
	$this->x = $this->lMargin;
	if(func_num_args() == 0)
		$this->y += $this->lasth;
	else
		$this->y += $h;
}


/**
 * Puts an image in the document. The size it will take on the page can be
 * specified in different ways:
 * <ul>
 * <li>explicit width and height (expressed in user unit or dpi)</li>
 * <li>one explicit dimension, the other being calculated automatically in
 * order to keep the original proportions</li>
 * <li>no explicit dimension, in which case the image is put at 96 dpi</li>
 * </ul>
 * Supported formats are JPEG, PNG and GIF. The GD extension is required for GIF.
 * <p>For JPEGs, all flavors are allowed:
 * <ul>
 * <li>gray scales</li>
 * <li>true colors (24 bits)</li>
 * <li>CMYK (32 bits)</li>
 * </ul>
 * <p>For PNGs, are allowed:
 * <ul>
 * <li>gray scales on at most 8 bits (256 levels)</li>
 * <li>indexed colors</li>
 * <li>true colors (24 bits)</li>
 * </ul>
 * <p>For GIFs: in case of an animated GIF, only the first frame is displayed.
 * <p>Transparency is supported.
 * <p>The format can be specified explicitly or inferred from the file extension.
 * <p>It is possible to put a link on the image.
 * <p>Remark: if an image is used several times, only one copy is embedded in
 * the file.
 * <p>Example:
 * <pre>
 * // Insert a logo in the top-left corner at 300 dpi
 * $img = Image::fromFile(File::fromLocaleEncoded('logo.png'));
 * $pdf-&gt;image($mg,10,10,-300);
 * </pre>
 * The same image can be put in the document several times, for example in every
 * header. The binary content of the image shall be embedded in the PDF document
 * only once.
 * <p>See alse: {@link self::addLink()}.
 * @param Image $image The image to put in the page.
 * @param double $x Abscissa of the upper-left corner. If not specified or
 * equal to null, the current abscissa is used. 
 * @param double $y Ordinate of the upper-left corner. If not specified or
 * equal to null, the current ordinate is used; moreover, a page break is
 * triggered first if necessary (in case automatic page breaking is enabled)
 * and, after the call, the current ordinate is moved to the bottom of the image. 
 * @param double $w Width of the image in the page. There are three cases:
 * <ul>
 * <li>If the value is positive, it represents the width in user unit</li>
 * <li>If the value is negative, the absolute value represents the horizontal
 * resolution in dpi</li>
 * <li>If the value is not specified or equal to zero, it is automatically
 * calculated</li>
 * </ul>
 * @param double $h Height of the image in the page. There are three cases:
 * <ul>
 * <li>If the value is positive, it represents the height in user unit</li>
 * <li>If the value is negative, the absolute value represents the vertical
 * resolution in dpi</li>
 * <li>If the value is not specified or equal to zero, it is automatically
 * calculated</li>
 * </ul>
 * @param string $link URL or identifier returned by {@link self::addLink()}.
 * @throws ErrorException Automatic page brak may trigger a call to the page
 * header and page footer methods, which are allowed to throw exception.
 */
function image($image, $x=0, $y=0, $w=0, $h=0, $link='')
{
	$image_ = $this->images->get($image);
	if( $image_ === NULL ){
		$this->images->put($image, $image);
		$image->i = $this->images->count() + 1;
	} else {
		$image = cast(Image::NAME, $image_);
	}

	// Automatic width and height calculation if needed
	if($w==0 && $h==0)
	{
		// Put image at 96 dpi
		$w = -96;
		$h = -96;
	}
	if($w<0)
		$w = -$image->w*72/$w/$this->k;
	if($h<0)
		$h = -$image->h*72/$h/$this->k;
	if($w==0)
		$w = $h*$image->w/$image->h;
	if($h==0)
		$h = $w*$image->h/$image->w;

	// Flowing mode
	if(func_num_args() < 3 /* missing $y arg */)
	{
		if($this->y+$h>$this->pageBreakTrigger && !$this->inHeader && !$this->inFooter && $this->acceptPageBreak())
		{
			// Automatic page break
			$x2 = $this->x;
			$this->addPage();
			$this->x = $x2;
		}
		$y = $this->y;
		$this->y += $h;
	}

	if(func_num_args() < 2 /* missing $x arg */)
		$x = $this->x;
	$this->put(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$image->i));
	if(func_num_args() >= 7 /* arg $link available */)
		$this->link($x,$y,$w,$h,$link);
}


/**
 * Returns the abscissa of the current position.
 * <p>See also: {@link self::setX()}, {@link self::getY()}, {@link self::setY()}.
 */
function getX()
{
	return $this->x;
}

/**
 * Defines the abscissa of the current position.
 * <p>See also: {@link self::getX()}, {@link self::getY()},
 * {@link self::setY()}, {@link self::setXY()}.
 * @param double $x The value of the abscissa. If negative, it is relative to
 * the right of the page.
 */
function setX($x)
{
	if($x>=0)
		$this->x = $x;
	else
		$this->x = $this->w+$x;
}

/**
 * Returns the ordinate of the current position.
 * <p>See also: {@link self::setY()}, {@link self::getX()}, {@link self::setX()}.
 */
function getY()
{
	return $this->y;
}

/**
 * Moves the current abscissa back to the left margin and sets the ordinate.
 * <p>See also: {@link self::getX()}, {@link self::getY()},
 * {@link self::setX()}, {@link self::setXY()}. 
 * @param double $y The value of the ordinate. If negative, it is relative to
 * the bottom of the page.
 */
function setY($y)
{
	$this->x = $this->lMargin;
	if($y>=0)
		$this->y = $y;
	else
		$this->y = $this->h+$y;
}

/**
 * Defines the abscissa and ordinate of the current position.
 * @param double $x The value of the abscissa. If negative, it is relative to
 * the right of the page.
 * @param double $y The value of the ordinate. If negative, it is relative to
 * the bottom of the page.
 */
function setXY($x, $y)
{
	$this->setY($y);
	$this->setX($x);
}


/**
 * Set the preferred user's unit for values passed to the methods of this class.
 * @param string $unit User unit. Possible values are:
 * <ul>
 * <li><tt>pt</tt>: point</li>
 * <li><tt>mm</tt>: millimeter</li>
 * <li><tt>cm</tt>: centimeter</li>
 * <li><tt>in</tt>: inch</li>
 * </ul>
 * A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being
 * 2.54 cm). This is a very common unit in typography; font sizes are expressed
 * in that unit.
 */
public function setUnit($unit)
{
	if($unit==='pt')
		$this->k = 1;
	elseif($unit==='mm')
		$this->k = 72/25.4;
	elseif($unit==='cm')
		$this->k = 72/2.54;
	elseif($unit==='in')
		$this->k = 72;
	else
		throw new InvalidArgumentException('Incorrect unit: '.$unit);
}


/**
 * Set page size by width and height.
 * Dimensions can be specified in any order.
 * The value only affects the new added pages, not the curret one.
 * <p>See also: {@link self::setPageFormat()}, {@link self::setPageOrientation()}.
 * @param double $a First dimension of the page (user's units).
 * @param double $b Second dimension of the page (user's units).
 * @return void
 */
public function setPageSize($a, $b)
{
	$a *= $this->k;
	$b *= $this->k;
	$this->defPageSize = $a < $b? array($a, $b) : array($b, $a);
}


/**
 * Set page size by page format name.
 * The value only affects the new added pages, not the current one.
 * @param string $format The page format name: A3, A4, A5, Letter, Legal.
 * The value is case-insensitive.
 * @return void
 * @throws InvalidArgumentException Invalid page format name.
 */
public function setPageFormat($format)
{
	$format = strtolower($format);
	if(!isset($this->stdPageSizes[$format]))
		throw new InvalidArgumentException('Unknown page size: '.$format);
	$this->defPageSize = $this->stdPageSizes[$format];
}


/**
 * Sets page orientation. The value only affects the new added pages, not the
 * current one.
 * @param string $orientation Default page orientation. Possible values are
 * (case insensitive):
 * <ul>
 * <li><tt>P</tt> or Portrait: narrow size is put horizontal</li>
 * <li><tt>L</tt> or Landscape: wide size is put horizontal</li>
 * </ul>
 * @return void
 * @throws InvalidArgumentException Invalid orientation.
 */
public function setPageOrientation($orientation)
{
	$orientation = strtolower($orientation);
	if($orientation==='p' || $orientation==='portrait')
		$this->defOrientation = 'P';
	else if($orientation==='l' || $orientation==='landscape')
		$this->defOrientation = 'L';
	else
		throw new InvalidArgumentException('Incorrect orientation: '.$orientation);
}


/**
 * Initializes a new empty PDF document.
 * The default page size is A4 in portrait orientation.
 * The default measurement unit is mm.
 * The default font is Courier 12 pt.
 * A new page MUST be added before starting putting text in the document.
 * @throws ErrorException
 */
function __construct()
{
	$overload = ini_get('mbstring.func_overload');
	if($overload !== FALSE && ((int)$overload & 2) != 0)
		throw new RuntimeException('mbstring overloading must be disabled');
	
	$this->page = 0;
	// obj 1: pages indeces, obj 2 resources dict., next obj no. will be 3:
	$this->n = 2;
	$this->buffer = new StringBuffer();
	$this->pages = array();
	$this->pageSizes = array();
	$this->state = self::STATE_EMPTY;
	$this->fonts = new HashMap();
	$this->images = new HashMap();
	$this->links = array();
	$this->inHeader = false;
	$this->inFooter = false;
	$this->lasth = 0;
	$this->setFont(new FontCore("Courier"), "", 12.0);
	$this->drawColor = '0 G';
	$this->fillColor = '0 g';
	$this->textColor = '0 g';
	$this->colorFlag = false;
	$this->ws = 0;
	$this->coreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
	$this->setUnit("mm");
	$this->setPageFormat("A4");
	$this->curPageSize = $this->defPageSize;
	$this->setPageOrientation("P");
	$this->curOrientation = $this->defOrientation;
	// Page margins (1 cm)
	$margin = 28.35/$this->k;
	$this->setMargins($margin,$margin);
	// Interior cell margin (1 mm)
	$this->cMargin = $margin/10;
	// Line width (0.2 mm)
	$this->lineWidth = 0.567/$this->k;
	// Automatic page break
	$this->setAutoPageBreak(true,2*$margin);
	// Default display mode
	$this->setDisplayMode('default');
	// Enable compression
	$this->setCompression(true);
}


/**
 * Send the document to a given destination: browser, file or string. In the
 * case of browser, the plug-in may be used (if present) or a download
 * ("Save as" dialog box) may be forced.
 * The method first calls close() if necessary to terminate the document.
 * @param string $name The name of the file. If not specified, the document
 * will be sent to the browser (destination <tt>I</tt>) with the name <tt>doc.pdf</tt>.
 * @param string $dest Destination where to send the document. It can take one
 * of the following values:
 * <ul>
 * <li><tt>I</tt>: send the file in-line to the browser. The plug-in is used if
 * available. The name given by name is used when one selects the "Save as"
 * option on the link generating the PDF.</li>
 * <li><tt>D</tt>: send to the browser and force a file download with the name
 * given by name.</li>
 * <li><tt>F</tt>: save to a local file with the name given by name (may
 * include a path).</li>
 * <li><tt>S</tt>: return the document as a string. name is ignored.</li>
 * </ul>
 * <p>See also: {@link self::close()}.
 * @return string The empty string or the PDF contents, depending on the
 * previous parameters.
 * @throws ErrorException
 * @throws InvalidArgumentException Invalid output destination.
 */
function output($name='', $dest='')
{
	if($this->state < self::STATE_CLOSE)
		$this->close();
	$dest = strtoupper($dest);
	if($dest==='')
	{
		if($name==='')
		{
			$name = 'doc.pdf';
			$dest = 'I';
		}
		else
			$dest = 'F';
	}
	switch($dest)
	{
		case 'I':
			// Send to standard output
			echo $this->buffer;
			break;
		case 'D':
			// Download file
			header('Content-Type: application/x-download');
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			echo $this->buffer;
			break;
		case 'F':
			// Save to local file
			$f = fopen($name,'wb');
			fwrite($f,$this->buffer->__toString(),$this->buffer->length());
			fclose($f);
			break;
		case 'S':
			// Return as a string
			return $this->buffer->__toString();
		default:
			throw new InvalidArgumentException('Incorrect output destination: '.$dest);
	}
	return '';
}

}
