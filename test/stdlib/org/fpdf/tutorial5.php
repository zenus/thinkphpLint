<?php
# Formatting tables, one page for every sample.

require_once __DIR__ . "/../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../stdlib/utf8.php";
use it\icosaedro\utils\UString;
use org\fpdf\FPDF;
use org\fpdf\Font;
use org\fpdf\FontCore;
use it\icosaedro\io\File;
use it\icosaedro\io\FileInputStream;
use it\icosaedro\io\LineInputWrapper;
use it\icosaedro\io\IOException;

class PDF extends FPDF
{
	/**
	 * Loads the matrix of data from the file.
	 * Every line is a row of the table; cells must be separated by ';'.
	 * The encoding of the file is assumed to be ISO-8859-1.
	 * @param File $file
	 * @return UString[int][int] Read matrix of data.
	 * @throws IOException
	 */
	function LoadData($file)
	{
		$f = new LineInputWrapper( new FileInputStream($file) );
		$data = /*. (UString[int][int]) .*/ array();
		$separator = u(";");
		while( ($line = $f->readLine()) !== NULL )
			$data[] = UString::fromISO88591($line)->explode($separator);
		return $data;
	}

	/**
	 * @param UString[int] $header
	 * @param UString[int][int] $data
	 * @throws ErrorException
	 */
	function BasicTable($header, $data)
	{
		// Header
		foreach($header as $col)
			$this->cell(40,7,$col,1);
		$this->ln();
		// Data
		foreach($data as $row)
		{
			foreach($row as $col)
				$this->cell(40,6,$col,1);
			$this->ln();
		}
	}
	
	/**
	 * @param UString $s
	 * @return UString
	 */
	private static function numberFormat($s)
	{
		$n = number_format((float)$s->toASCII());
		return UString::fromASCII($n);
	}

	/**
	 * @param UString[int] $header
	 * @param UString[int][int] $data
	 * @throws ErrorException
	 */
	function ImprovedTable($header, $data)
	{
		// Column widths
		$w = array(40, 35, 40, 45);
		// Header
		for($i=0;$i<count($header);$i++)
			$this->cell($w[$i],7,$header[$i],1,0,'C');
		$this->ln();
		// Data
		foreach($data as $row)
		{
			$this->cell($w[0],6,$row[0],'LR');
			$this->cell($w[1],6,$row[1],'LR');
			$this->cell($w[2],6,self::numberFormat($row[2]),'LR',0,'R');
			$this->cell($w[3],6,self::numberFormat($row[3]),'LR',0,'R');
			$this->ln();
		}
		// Closing line
		$this->cell(array_sum($w),0,u(""),'T');
	}

	/**
	 * @param UString[int] $header
	 * @param UString[int][int] $data
	 * @throws ErrorException
	 */
	function FancyTable($header, $data)
	{
		// Colors, line width and bold font
		$this->setFillColor(255,0,0);
		$this->setTextColor(255, 255, 255);
		$this->setDrawColor(128,0,0);
		$this->setLineWidth(0.3);
		$this->setFont(NULL,'B');
		// Header
		$w = array(40, 35, 40, 45);
		for($i=0;$i<count($header);$i++)
			$this->cell($w[$i],7,$header[$i],1,0,'C',true);
		$this->ln();
		// Color and font restoration
		$this->setFillColor(224,235,255);
		$this->setTextColor(0, 0, 0);
		// Data
		$fill = false;
		foreach($data as $row)
		{
			$this->cell($w[0],6,$row[0],'LR',0,'L',$fill);
			$this->cell($w[1],6,$row[1],'LR',0,'L',$fill);
			$this->cell($w[2],6,self::numberFormat($row[2]),'LR',0,'R',$fill);
			$this->cell($w[3],6,self::numberFormat($row[3]),'LR',0,'R',$fill);
			$this->ln();
			$fill = !$fill;
		}
		// Closing line
		$this->cell(array_sum($w),0,u(""),'T');
	}
}

/**
 * @throws ErrorException
 * @throws IOException
 */
function main()
{
	$pdf = new PDF();
	// Column headings
	$header = array(u('Country'), u('Capital'), u('Area (sq km)'), u('Pop. (thousands)'));
	// Data loading
	$data = $pdf->LoadData(File::fromLocaleEncoded(__DIR__ . '/data/countries.txt'));
	$pdf->setFont(new FontCore(FontCore::HELVETICA),'',14);
	$pdf->addPage();
	$pdf->BasicTable($header,$data);
	$pdf->addPage();
	$pdf->ImprovedTable($header,$data);
	$pdf->addPage();
	$pdf->FancyTable($header,$data);

	$pdf->output("doc.pdf", "F");
	echo "--> doc.pdf\n";
}

main();
