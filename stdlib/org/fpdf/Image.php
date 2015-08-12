<?php

namespace org\fpdf;

require_once __DIR__ . "/../../all.php";
use ErrorException;
use InvalidArgumentException;
use it\icosaedro\io\FileInputStream;
use it\icosaedro\io\InputStream;
use it\icosaedro\io\IOException;
use it\icosaedro\io\File;
use it\icosaedro\io\StringInputStream;
use it\icosaedro\containers\Hashable;
use it\icosaedro\containers\Hash;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\UPrintable;
use it\icosaedro\utils\UString;

/*.
	require_module 'standard';
	require_module 'spl';
	require_module 'pcre';
	require_module 'gd';
	require_module 'zlib';
.*/

/**
 * Service class for FPDF that represents an image to be embedded in the PDF
 * document. See the documentation about the FPDF::image() method for examples.
 * Objects of this class must be comparable so that the main class FPDF can
 * avoid to add to the final document redundant copies of the same image.
 * <p>This source is an excerpt from the original FPDF 1.7 program of the author.
 * @version $Date: 2015/03/03 15:41:09 $
 * @author  Olivier PLATHEY
 * @author Umberto Salsi <salsi@icosaedro.it> (porting to PHPLint)
 * @copyright Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software to use, copy, modify, distribute,
 * sublicense, and/or sell copies of the software, and to permit persons to
 * whom the software is furnished to do so.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED.
 */
class Image implements Hashable, Printable, UPrintable {
	
	const NAME = __CLASS__;
	
	/**
	 * PDF image number.
	 * @var int
	 */
	public $i = 0;
	/**
	 * PDF object number.
	 * @var int
	 */
	public $n = 0;
	/**
	 * @var int
	 */
	public $w = 0;
	/**
	 * @var int
	 */
	public $h = 0;
	/**
	 * Palette.
	 * @var string
	 */
	private $pal;
	/**
	 * @var string
	 */
	private $cs;
	/**
	 * @var int
	 */
	private $bpc = 0;
	/**
	 * @var string
	 */
	private $dp;
	/**
	 * Filter.
	 * @var string
	 */
	private $f;
	/**
	 * Transparency.
	 * @var int[int]
	 */
	private $trns = array();
	/**
	 * Soft mask.
	 * @var string
	 */
	private $smask;
	/**
	 * @var string
	 */
	private $data;
	/**
	 * File of the image; set only for images read from file.
	 * @var File
	 */
	private $filename;
	private $hash = 0;




	/**
	 * For images retrieved from file, two images are equal if belong to the
	 * same file; generated images are equal if are the same object.
	 * @param object $other
	 * @return boolean
	 */
	function equals($other)
	{
		if( $other === NULL )
			return FALSE;
		if( $this === $other )
			return TRUE;
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		$other2 = cast(__CLASS__, $other);
		return $this->filename !== NULL
			&& $other2->filename !== NULL
			&& $this->filename->equals($other2->filename);
	}
	
	
	/**
	 * return int
	 */
	function getHash() {
		if( $this->hash == 0 ){
			if( $this->filename === NULL )
				$this->hash = Hash::hashOfObject($this);
			else
				$this->hash = $this->filename->getHash();
		}
		return $this->hash;
	}
	
	
	function __toString() {
		if( $this->filename === NULL )
			return "generated:" . spl_object_hash($this);
		else
			return $this->filename->__toString();
	}
	
	
	function toUString() {
		if( $this->filename === NULL )
			return UString::fromASCII("generated:" . spl_object_hash($this));
		else
			return $this->filename->toUString();
	}

	
	/**
	 * Extract info from a JPEG file.
	 * @param File $file
	 * @param mixed[] $a Data from the imagesize() function.
	 * @throws IOException
	 * @throws ErrorException Missing or incorrect image file.
	 */
	private function parseJPEG($file, $a)
	{
		if(!isset($a['channels']) || $a['channels']===3)
			$colspace = 'DeviceRGB';
		elseif($a['channels']===4)
			$colspace = 'DeviceCMYK';
		else
			$colspace = 'DeviceGray';
		$bpc = isset($a['bits']) ? (int)$a['bits'] : 8;
		$this->w = (int) $a[0];
		$this->h = (int) $a[1];
		$this->cs = $colspace;
		$this->bpc = $bpc;
		$this->f = 'DCTDecode';
		$this->data = file_get_contents($file->getLocaleEncoded());
	}

	
	/**
	 * Read a 4-byte integer from stream.
	 * @param InputStream $f
	 * @return int
	 * @throws IOException
	 */
	private function readInt($f)
	{
		$a = unpack('Ni',$f->readFully(4));
		return (int) $a['i'];
	}

	
	/**
	 * @param InputStream $f
	 * @throws IOException
	 * @throws ErrorException Error reading image file. Invalid format.
	 * Feature of the image file not supported.
	 */
	private function parsePNGStream($f)
	{
		// Check signature
		if($f->readFully(8)!==chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
			throw new ErrorException('not a PNG file');

		// Read header chunk
		$f->readFully(4);
		if($f->readFully(4)!=='IHDR')
			throw new ErrorException('invalid PNG file');
		$w = $this->readInt($f);
		$h = $this->readInt($f);
		$bpc = ord($f->readFully(1));
		if($bpc>8)
			throw new ErrorException("more than 8-bits per color plane not supported: $bpc");
		$ct = ord($f->readFully(1));
		if($ct==0 || $ct==4)
			$colspace = 'DeviceGray';
		elseif($ct==2 || $ct==6)
			$colspace = 'DeviceRGB';
		elseif($ct==3)
			$colspace = 'Indexed';
		else
			throw new ErrorException("unknown color type");
		if(ord($f->readFully(1))!=0)
			throw new ErrorException('unknown compression method');
		if(ord($f->readFully(1))!=0)
			throw new ErrorException('unknown filter method');
		if(ord($f->readFully(1))!=0)
			throw new ErrorException('interlacing not supported');
		$f->readFully(4);
		$dp = '/Predictor 15 /Colors '.($colspace==='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;

		// Scan chunks looking for palette, transparency and image data
		$pal = '';
		$trns = /*. (int[int]) .*/ array();
		$data = '';
		do
		{
			$n = $this->readInt($f);
			$type = $f->readFully(4);
			if($type==='PLTE')
			{
				// Read palette
				$pal = $f->readFully($n);
				$f->readFully(4);
			}
			elseif($type==='tRNS')
			{
				// Read transparency info
				$t = $f->readFully($n);
				if($ct==0)
					$trns = array(ord(substr($t,1,1)));
				elseif($ct==2)
					$trns = array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
				else
				{
					$pos = strpos($t,chr(0));
					if($pos!==false)
						$trns = array($pos);
				}
				$f->readFully(4);
			}
			elseif($type==='IDAT')
			{
				// Read image data block
				$data .= $f->readFully($n);
				$f->readFully(4);
			}
			elseif($type==='IEND')
				break;
			else
				$f->readFully($n+4);
		}
		while($n>0);

		if($colspace==='Indexed' && strlen($pal) == 0)
			throw new ErrorException('missing palette');
		$this->w = $w;
		$this->h = $h;
		$this->cs = $colspace;
		$this->bpc = $bpc;
		$this->f = 'FlateDecode';
		$this->dp = $dp;
		$this->pal = $pal;
		if(count($trns)>0)
			$this->trns = $trns;
		if($ct>=4)
		{
			// Extract alpha channel
			$data = gzuncompress($data);
			$color = '';
			$alpha = '';
			if($ct==4)
			{
				// Gray image
				$len = 2*$w;
				for($i=0;$i<$h;$i++)
				{
					$pos = (1+$len)*$i;
					$color .= $data[$pos];
					$alpha .= $data[$pos];
					$line = substr($data,$pos+1,$len);
					$color .= preg_replace('/(.)./s','$1',$line);
					$alpha .= preg_replace('/.(.)/s','$1',$line);
				}
			}
			else
			{
				// RGB image
				$len = 4*$w;
				for($i=0;$i<$h;$i++)
				{
					$pos = (1+$len)*$i;
					$color .= $data[$pos];
					$alpha .= $data[$pos];
					$line = substr($data,$pos+1,$len);
					$color .= preg_replace('/(.{3})./s','$1',$line);
					$alpha .= preg_replace('/.{3}(.)/s','$1',$line);
				}
			}
			$data = gzcompress($color);
			$this->smask = gzcompress($alpha);
		}
		$this->data = $data;
	}

	
	/**
	 * Extract info from a PNG file.
	 * @param File $file
	 * @throws IOException
	 * @throws ErrorException Invalid or unsupported image file format.
	 */
	private function parsePNG($file)
	{
		$this->parsePNGStream(new FileInputStream($file));
	}
	
	
	/**
	 * Reads image info from a GD handle.
	 * @param resource $im GD image handle.
	 * @return void
	 * @throws ErrorException Bad image file format.
	 */
	private function parseGD($im)
	{
		ob_start();
		try {
			if( ! imagepng($im) )
				throw new ErrorException("conversion to PNG failed");
		}
		catch(ErrorException $e){
			ob_get_clean();
			throw $e;
		}
		$data = ob_get_clean();
		try {
			$this->parsePNGStream(new StringInputStream($data));
		}
		catch(IOException $e){
			// We are operating all in memory, IO exceptions can be related
			// only to internal bugs, and not to external events the client
			// might be interested to - revert to unchecked to simplify the
			// signature of this method.
			throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
		}
	}

	
	/**
	 * Extract info from a GIF file.
	 * @param File $file
	 * @throws IOException
	 * @throws ErrorException Bad image file format.
	 */
	private function parseGIF($file)
	{
		// Converts GIF to PNG then applies the function above.
		$im = imagecreatefromgif($file->getLocaleEncoded());
		imageinterlace($im,0);
		$this->parseGD($im);
	}
	
	
	/**
	 * Returns data from an image file.
	 * @param File $file File name of the image. The supported types are:
	 * JPEG, PNG, GIF.
	 * @return Image Image data to be used by FPDF.
	 * @throws IOException
	 * @throws ErrorException Failed reading image file. Image file format or
	 * some of its features not supported.
	 */
	public static function fromFile($file)
	{
		$img = new Image();
		$img->filename = $file;
		$a = getimagesize($file->getLocaleEncoded());
		$type = (int) $a[2];
		switch($type){
			case IMAGETYPE_JPEG: $img->parseJPEG($file, $a);  break;
			case IMAGETYPE_PNG:  $img->parsePNG($file);  break;
			case IMAGETYPE_GIF:  $img->parseGIF($file);  break;
			case IMAGETYPE_UNKNOWN: throw new InvalidArgumentException("not an image");
			default: throw new InvalidArgumentException("image type not supported");
		}
		return $img;
	}
	
	
	/**
	 * Returns data from an image created with the functions of the GD library.
	 * @param resource $gd Handle of the image created with {@link imagecreate()}
	 * or with {@link imagecreatetruecolor()} and others functions of GD.
	 * @return Image Image data to be used by FPDF.
	 * @throws ErrorException
	 */
	public static function fromGD($gd)
	{
		$img = new Image();
		$img->parseGD($gd);
		return $img;
	}
	
	
	/**
	 * @return Image
	 */
	private function buildMask()
	{
		$smask = new Image();
		$smask->w = $this->w;
		$smask->h = $this->h;
		$smask->cs = 'DeviceGray';
		$smask->bpc = 8;
		$smask->f = $this->f;
		$smask->dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$this->w;
		$smask->data = $this->smask;
		return $smask;
	}
	
	
	/**
	 * Service method called by the FPDF class, do not call in the application
	 * code.
	 * Embeds this image into a PDF object and sets the image number property $n.
	 * This method is called automatically by FPDF.
	 * @param PdfObjWriterInterface $w PDF document writer. Currently, this
	 * interface is implemented only by the FPDF class itself, so this object
	 * actually is a FPDF.
	 * @param boolean $compress
	 * @return void
	 * @throws ErrorException
	 */
	public function put($w, $compress)
	{
		$this->n = $w->addObj();
		$w->put('<</Type /XObject');
		$w->put('/Subtype /Image');
		$w->put('/Width '.$this->w);
		$w->put('/Height '.$this->h);
		if($this->cs==='Indexed')
			$w->put('/ColorSpace [/Indexed /DeviceRGB '.(strlen($this->pal)/3-1).' '.($this->n+1).' 0 R]');
		else
		{
			$w->put('/ColorSpace /'.$this->cs);
			if($this->cs==='DeviceCMYK')
				$w->put('/Decode [1 0 1 0 1 0 1 0]');
		}
		$w->put('/BitsPerComponent '.$this->bpc);
		if($this->f!==NULL)
			$w->put('/Filter /'.$this->f);
		if($this->dp!==NULL)
			$w->put('/DecodeParms <<'.$this->dp.'>>');
		if(count($this->trns)>0)
		{
			$trns = "";
			for($i=0;$i<count($this->trns);$i++)
				$trns .= $this->trns[$i].' '.$this->trns[$i].' ';
			$w->put('/Mask ['.$trns.']');
		}
		if($this->smask!==NULL)
			$w->put('/SMask '.($this->n+1).' 0 R');
		$w->put('/Length '.strlen($this->data).'>>');
		$w->putStream($this->data);
		$w->put('endobj');
		// Soft mask
		if($this->smask!==NULL)
		{
			$smask = $this->buildMask();
			$smask->put($w, $compress);
		}
		// Palette
		if($this->cs==='Indexed')
		{
			$filter = ($compress) ? '/Filter /FlateDecode ' : '';
			$pal = ($compress) ? gzcompress($this->pal) : $this->pal;
			$w->addObj();
			$w->put('<<'.$filter.'/Length '.strlen($pal).'>>');
			$w->putStream($pal);
			$w->put('endobj');
		}
	}
	
}
