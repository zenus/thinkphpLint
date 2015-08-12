<?php

# Formatting a longer text, with title, chapters and page numbers. 

require_once __DIR__ . "/../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../stdlib/utf8.php";
use it\icosaedro\utils\UString;
use org\fpdf\FPDF;
use org\fpdf\Font;
use org\fpdf\FontCore;
use org\fpdf\Image;
use it\icosaedro\io\File;
use it\icosaedro\io\IOException;

class PDF extends FPDF
{
	/** @var Font */
	private $titleFont;
	
	/** @var Font */
	private $bodyFont;
	
	/** @var Font */
	private $bodyItalicFont;
	
	/** @var Font */
	private $footerFont;
	
	/** @var UString */
	private $title;
	

	/**
	 * @param UString $title
	 * @throws InvalidArgumentException
	 * @throws ErrorException
	 */
	function __construct($title) {
		parent::__construct();
		$this->title = $title;
		$this->titleFont = new FontCore(FontCore::HELVETICA_BOLD);
		$this->bodyFont = new FontCore(FontCore::TIMES_ROMAN);
		$this->bodyItalicFont = new FontCore(FontCore::TIMES_ITALIC);
		$this->footerFont = new FontCore(FontCore::HELVETICA_OBLIQUE);
	}
	
	
	/**
	 * @throws ErrorException
	 */
	function header()
	{
		// Helvetica bold 15
		$this->setFont($this->titleFont,'',15);
		// Calculate width of title and position
		$w = $this->getStringWidth($this->title)+6;
		$this->setX((210-$w)/2);
		// Colors of frame, background and text
		$this->setDrawColor(0,80,180);
		$this->setFillColor(230,230,0);
		$this->setTextColor(220,50,50);
		// Thickness of frame (1 mm)
		$this->setLineWidth(1);
		// Title
		$this->cell($w,9,$this->title,1,1,'C',true);
		// Line break
		$this->ln(10);
	}

	
	/**
	 * @throws ErrorException
	 */
	function footer()
	{
		// Position at 1.5 cm from bottom
		$this->setY(-15);
		// Helvetica italic 8
		$this->setFont($this->footerFont,'',8);
		// Text color in gray
		$this->setTextColor(128, 128, 128);
		// Page number
		$this->cell(0,10,u('Page ',$this->pageNo()),0,0,'C');
	}

	
	/**
	 * @param int $num
	 * @param UString $label
	 * @throws ErrorException
	 */
	function ChapterTitle($num, $label)
	{
		// Helvetica 12
		$this->setFont($this->titleFont,'',12);
		// Background color
		$this->setFillColor(200,220,255);
		// Title
		$this->cell(0,6,u("Chapter ",$num," : ")->append($label),0,1,'L',true);
		// Line break
		$this->ln(4);
	}

	
	/**
	 * @param string $file
	 * @throws ErrorException
	 */
	function ChapterBody($file)
	{
		// Read text file
		$txt = UString::fromISO88591( file_get_contents($file) );
		// Times 12
		$this->setFont($this->bodyFont,'',12);
		// Output justified text
		$this->multiCell(0,5,$txt);
		// Line break
		$this->ln();
		// Mention in italics
		$this->setFont($this->bodyItalicFont,'I');
		$this->cell(0,5,u('(end of excerpt)'));
	}

	
	/**
	 * @param int $num
	 * @param UString $title
	 * @param string $file
	 * @throws ErrorException
	 */
	function PrintChapter($num, $title, $file)
	{
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

