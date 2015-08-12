<?php

# Hello world!

require_once __DIR__ . "/../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../stdlib/utf8.php";
use org\fpdf\FPDF;
use org\fpdf\FontCore;
use \ErrorException;

/**
 * @throws ErrorException
 */
function main()
{
	$doc = "doc.pdf";
	$pdf = new FPDF();
	$font = new FontCore(FontCore::HELVETICA_BOLD);
	$pdf->addPage();
	$pdf->setFont($font,'',16);
	$pdf->cell(40,10,u('Hello World!'));

	$pdf->output($doc, "F");
	echo "--> $doc\n";
}

main();

