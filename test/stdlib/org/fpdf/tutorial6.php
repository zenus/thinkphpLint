<?php
# Writing HTML-like text; putting links.

require_once __DIR__ . "/../../../../stdlib/all.php";
require_once __DIR__ . "/../../../../stdlib/utf8.php";
use it\icosaedro\utils\UString;
use org\fpdf\FPDF;
use org\fpdf\FontCore;
use org\fpdf\Image;
use it\icosaedro\io\File;
use it\icosaedro\io\IOException;

class PDF extends FPDF
{
	/** @var int[string] */
	private $element_nesting = array('A'=>0, 'B'=>0, 'I'=>0, 'U'=>0);
	
	/** @var string */
	private $href;

	/**
	 * @throws ErrorException
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param string $tag
	 * @param boolean $enable
	 */
	function SetStyle($tag, $enable)
	{
		// Modify style and select corresponding font
		$this->element_nesting[$tag] += ($enable ? 1 : -1);
		$style = '';
		foreach($this->element_nesting as $k => $s)
		{
			if($s>0)
				$style .= $k;
		}
		$bold = strpos($style, 'U') !== FALSE;
		$italic = strpos($style, 'I') !== FALSE;
		$this->setFont(FontCore::factory("helvetica", $bold, $italic),$style);
	}

	/**
	 * @param string $tag
	 * @param string[string] $attr
	 */
	function OpenTag($tag, $attr)
	{
		// Opening tag
		if($tag==='B' || $tag==='I' || $tag==='U')
			$this->SetStyle($tag,true);
		if($tag==='A')
			$this->href = $attr['HREF'];
		if($tag==='BR')
			$this->ln(5);
	}

	/**
	 * 
	 * @param string $tag
	 */
	function CloseTag($tag)
	{
		// Closing tag
		if($tag==='B' || $tag==='I' || $tag==='U')
			$this->SetStyle($tag,false);
		if($tag==='A')
			$this->href = '';
	}

	/**
	 * @param string $URL
	 * @param string $txt
	 * @throws ErrorException
	 */
	function PutLink($URL, $txt)
	{
		// Put a hyperlink
		$this->setTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->write(5,UString::fromISO88591($txt),$URL);
		$this->SetStyle('U',false);
		$this->setTextColor(0, 0, 0);
	}

	/**
	 * @param string $html
	 * @throws ErrorException
	 */
	function WriteHTML($html)
	{
		// HTML parser
		$html = (string) str_replace("\n",' ',$html);
		$a = cast("string[int]", preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE));
		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				// Text
				if(strlen($this->href) > 0)
					$this->PutLink($this->href,$e);
				else
					$this->write(5,UString::fromISO88591($e));
			}
			else
			{
				// Tag
				if($e[0]==='/')
					$this->CloseTag(strtoupper(substr($e,1)));
				else
				{
					// Extract attributes
					$a2 = explode(' ',$e);
					$tag = strtoupper((string)array_shift($a2));
					$attr = /*. (string[string]) .*/ array();
					foreach($a2 as $v)
					{
						if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3)!==FALSE)
							$attr[strtoupper($a3[1])] = $a3[2];
					}
					$this->OpenTag($tag,$attr);
				}
			}
		}
	}
}

/**
 * @throws ErrorException
 * @throws IOException
 */
function main()
{
	$html = <<<EOT
You can now easily print text mixing different styles: <b>bold</b>, <i>italic</i>,
<u>underlined</u>, or <b><i><u>all at once</u></i></b>!<br><br>You can also
insert links on text, such as <a href="http://www.fpdf.org">www.fpdf.org</a>,
or on an image: click on the logo.
EOT;

	$pdf = new PDF();
	// First page
	$pdf->addPage();
	$pdf->setFont(new FontCore(FontCore::HELVETICA),'',20);
	$pdf->write(5,UString::fromISO88591("To find out what's new in this tutorial, click "));
	$pdf->setFont(NULL,'U');
	$link = $pdf->addLink();
	$pdf->write(5,u('here'),$link);
	// Second page
	$pdf->addPage();
	$pdf->setLink($link);
	$logo = Image::fromFile(File::fromLocaleEncoded(__DIR__ . '/data/logo.png'));
	$pdf->image($logo,10,12,30,0,'http://www.fpdf.org');
	$pdf->setLeftMargin(45);
	$pdf->setFontSize(14);
	$pdf->WriteHTML($html);

	$pdf->output("doc.pdf", "F");
	echo "--> doc.pdf\n";
}

main();
