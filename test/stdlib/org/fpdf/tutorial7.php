<?php
# Using TrueType font.

require_once __DIR__ . "/../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../stdlib/utf8.php";
use org\fpdf\FPDF;
use org\fpdf\FontTrueType;
use it\icosaedro\io\File;
use it\icosaedro\io\IOException;

/**
 * @throws ErrorException
 * @throws IOException
 */
function main()
{
	$font = new FontTrueType(File::fromLocaleEncoded(__DIR__ . '/data/calligra.ttf'));

	$pdf = new FPDF();
	$pdf->addPage();
	$pdf->setFont($font,'',35);
	$pdf->cell(0,10,u('Enjoy new fonts with FPDF!'));

	$pdf->output("doc.pdf", "F");
	echo "--> doc.pdf\n";
}

main();
