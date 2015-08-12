<?php
/*.
	require_module 'standard';
	require_module 'zip';
	require_module 'spl';
.*/

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../all.php";

use it\icosaedro\containers\Printable;
use it\icosaedro\containers\UPrintable;
use it\icosaedro\utils\UString;
use it\icosaedro\io\codepage\CodePageInterface;
use ZipArchive;

/**
 * ZIP archive reader. A ZIP archive consists of a number of entries, each
 * one representing a file or a directory, and each one having a path and a
 * binary content. Once an object of this class has been created, subsequent
 * calls the getEntry() method return each entry of the archive. Example:
 * <pre>
 * $fn = "/tmp/myfile.zip";
 * $z = new ZipFileReader(File::fromLocaleEncoded($fn));
 * for($i = 0; $i &lt; $z-&gt;size(); $i++){
 *     $e = $z-&gt;getEntry($i);
 *     echo $e-&gt;getName()-&gt;toUTF8(), ", ",
 *          $e-&gt;getSize(), " bytes\n";
 * }
 * </pre>
 * One of the biggest problems with ZIP archives is how to guess the encoding
 * of the contained file names. In fact, these names are bare arrays of bytes
 * without any specific encoding. Normally, the originating computer where the
 * archive was produced used its current locale, which usually is UTF-8 on
 * Unix/Linux systems, and can be Windows-1252 for files generated under
 * Windows on western countries. The constructor of this class translates
 * according to the current system locale, but if you know from where the ZIP
 * archive came from, you may perform a better guess. Read the comments about
 * the constructor for more details.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:05:44 $
 */
class ZipFileReader implements Printable, UPrintable {

	private /*. File .*/ $fn;
	private /*. ZipArchive .*/ $a;
	private /*. CodePageInterface .*/ $codepage;


	/**
	 * Opens a ZIP archive.
	 * @param File $fn
	 * @param CodePageInterface $codepage Decoder used to translate file paths
	 * read from the archive. If not specified, uses the current file system
	 * locale encoding. Common values may be:
	 * <tt>new it\icosaedro\io\codepage\WindowsCodePage("1252")</tt>
	 * if the ZIP archive originates from Windows on a western PC,
	 * or <tt>new it\icosaedro\io\codepage\GenericCodePage("UTF-8")</tt>
	 * for files coming from Unix/Linux systems.
	 * @throws IOException Unknown code page. Access error.
	 * @throws ZipFileException ZIP specific error.
	 */
	function __construct($fn, $codepage = NULL)
	{
		$this->fn = $fn;
		$this->a = new ZipArchive();
		$err = $this->a->open($fn->getLocaleEncoded());
		if( $err !== TRUE )
			throw new ZipFileException("opening failed", $err);
		if( $codepage === NULL )
			$codepage = FileName::getEncoding();
		$this->codepage = $codepage;
	}

	
	/**
	 * Returns the number of entries in this ZIP archive.
	 * @return int Number of entries in this ZIP archive.
	 */
	public function size()
	{
		return $this->a->numFiles;
	}


	/**
	 * Returns an object that represents a specified ZIP entry, that is a file
	 * or a directory. That object offers several methods that returns further
	 * details about the entry.
	 * @param int $i Entry index in the ZIP archive, in the range from 0 up to
	 * (size()-1).
	 * @return ZipFileReaderEntry
	 * @throws ZipFileException
	 */
	public function getEntry($i)
	{
		return new ZipFileReaderEntry($this->a, $i, $this->codepage);
	}


	/**
	 * Releases all the resources allocated for this archive.
	 * It is safe to call this method more than once.
	 */
	public function close()
	{
		if( $this->a !== NULL ){
			$this->a->close();
			$this->a = NULL;
		}
	}


	/**
	 * Returns the path name of this ZIP archive, locale encoded.
	 * @return string Path name of this ZIP archive, locale encoded.
	 */
	public function __toString()
	{
		try { return $this->fn->getLocaleEncoded(); }
		catch( IOException $e ){ return $e->getMessage(); }
	}
	
	
	/**
	 * Returns the path name of this ZIP archive.
	 * @return UString Path name of this ZIP archive.
	 */
	public function toUString()
	{
		return $this->fn->toUString();
	}


	/*. void .*/ function __destruct()
	{
		$this->close();
	}

}
