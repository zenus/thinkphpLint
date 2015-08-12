<?php

# Formatting text on 3 columns, with title, chapers and page numbers.

require_once __DIR__ . "/../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../stdlib/utf8.php";
use it\icosaedro\utils\UString;
use org\fpdf\FPDF;
use org\fpdf\Font;
use org\fpdf\FontCore;

class PDF extends FPDF
{
	/** @var UString */
	protected $title;
	// Current column
	protected $col = 0;
	// Ordinate of column start
	protected $y0 = 0.0;
	/** @var Font */
	private $header_font, $footer_font, $chapter_title_font,
		$chapter_body_font;

	/**
	 * @param UString $title
	 * @throws ErrorException
	 */
	function __construct($title) {
		parent::__construct();
		$this->title = $title;
		$this->header_font
			= $this->footer_font
			= $this->chapter_title_font
			= new FontCore(FontCore::HELVETICA);
		$this->chapter_body_font = new FontCore(FontCore::TIMES_ROMAN);
	}


	/**
	 * @throws ErrorException
	 */
	function header()
	{
		// Page header
		$this->setFont($this->header_font,'B',15);
		$w = $this->getStringWidth($this->title)+6;
		$this->setX((210-$w)/2);
		$this->setDrawColor(0,80,180);
		$this->setFillColor(230,230,0);
		$this->setTextColor(220,50,50);
		$this->setLineWidth(1);
		$this->cell($w,9,$this->title,1,1,'C',true);
		$this->ln(10);
		// Save ordinate
		$this->y0 = $this->getY();
	}


	/**
	 * @throws ErrorException
	 */
	function footer()
	{
		// Page footer
		$this->setY(-15);
		$this->setFont($this->footer_font,'I',8);
		$this->setTextColor(128, 128, 128);
		$this->cell(0,10,u('Page ',$this->pageNo()),0,0,'C');
	}

	/**
	 * @param int $col
	 */
	function SetCol($col)
	{
		// Set position at a given column
		$this->col = $col;
		$x = 10+$col*65;
		$this->setLeftMargin($x);
		$this->setX($x);
	}

	function acceptPageBreak()
	{
		// Method accepting or not automatic page break
		if($this->col<2)
		{
			// Go to next column
			$this->SetCol($this->col+1);
			// Set ordinate to top
			$this->setY($this->y0);
			// Keep on page
			return false;
		}
		else
		{
			// Go back to first column
			$this->SetCol(0);
			// Page break
			return true;
		}
	}

	/**
	 * @param int $num
	 * @param UString $label
	 * @throws ErrorException
	 */
	function ChapterTitle($num, $label)
	{
		// Title
		$this->setFont($this->chapter_title_font,'',12);
		$this->setFillColor(200,220,255);
		$this->cell(0,6,u("Chapter $num : ")->append($label),0,1,'L',true);
		$this->ln(4);
		// Save ordinate
		$this->y0 = $this->getY();
	}

	/**
	 * @param string $file
	 * @throws ErrorException
	 */
	function ChapterBody($file)
	{
		// Read text file
		$txt = UString::fromISO88591(file_get_contents($file));
		// Font
		$this->setFont($this->chapter_body_font,'',12);
		// Output text in a 6 cm width column
		$this->multiCell(60,5,$txt);
		$this->ln();
		// Mention
		$this->setFont($this->chapter_body_font,'I');
		$this->cell(0,5,u('(end of excerpt)'));
		// Go back to first column
		$this->SetCol(0);
	}

	/**
	 * @param int $num
	 * @param UString $title
	 * @param string $file
	 * @throws ErrorException
	 */
	function PrintChapter($num, $title, $file)
	{
		// Add chapter
		$this->addPage();
		$this->ChapterTitle($num,$title);
		$this->ChapterBody($file);
	}
}


/**
 * @throws ErrorException
 */
function main()
{
	$title = u('20000 Leagues Under the Seas');
	$pdf = new PDF($title);
	$pdf->setTitle($title);
	$pdf->setAuthor(u('Jules Verne'));
	$pdf->PrintChapter(1,u('A RUNAWAY REEF'), __DIR__.'/data/20k_c1.txt');
	$pdf->PrintChapter(2,u('THE PROS AND CONS'), __DIR__.'/data/20k_c2.txt');

	$pdf->output("doc.pdf", "F");
	echo "--> doc.pdf\n";
}

main();
