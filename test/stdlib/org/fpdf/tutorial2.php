<?php

# Putting header and footer on each page, with image and page no.

require_once __DIR__ . "/../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../stdlib/utf8.php";
use org\fpdf\FPDF;
use org\fpdf\Font;
use org\fpdf\FontCore;
use org\fpdf\Image;
use it\icosaedro\io\File;
use it\icosaedro\io\IOException;

class PDF extends FPDF
{
	/** @var Font */
	private $headerAndFooterFont;
	
	/** @var Image */
	private $logo;
	
	/**
	 * @param Font $headerAndFooterFont
	 * @param Image $logo
	 * @throws ErrorException
	 */
	function __construct($headerAndFooterFont, $logo) {
		parent::__construct();
		$this->headerAndFooterFont = $headerAndFooterFont;
		$this->logo = $logo;
	}
	
	/**
	 * Page header.
	 * @throws ErrorException
	 */
	function header()
	{
		// Put image in header
		$this->image($this->logo,10,6,30);
		// Set font:
		$this->setFont($this->headerAndFooterFont,'B',15);
		// Move to the right
		$this->cell(80);
		// Title
		$this->cell(30,10,u('Title'),1,0,'C');
		// Line break
		$this->ln(20);
	}

	/**
	 * Page footer.
	 * @throws ErrorException
	 */
	function footer()
	{
		// Position at 1.5 cm from bottom
		$this->setY(-15);
		// Helvetica italic 8
		$this->setFont($this->headerAndFooterFont,'I',8);
		// Page number
		$this->cell(0,10,u('Page ', $this->pageNo(), '/{nb}'),0,0,'C');
	}
}


/**
 * @throws ErrorException
 * @throws IOException
 */
function main()
{
	$headerAndFooterFont = new FontCore(FontCore::HELVETICA);
	$bodyFont = new FontCore(FontCore::TIMES_ROMAN);
	$logo = Image::fromFile(new File(u(__DIR__ . '/data/logo.png')));
	
	$pdf = new PDF($headerAndFooterFont, $logo);
	
	$pdf->aliasNbPages();
	$pdf->addPage();
	$pdf->setFont($bodyFont,'',12);
	for($i=1;$i<=40;$i++)
		$pdf->cell(0,10,u('Printing line number ', $i),0,1);

	$doc = "doc.pdf";
	$pdf->output($doc, "F");
	echo "--> $doc\n";
}

main();
