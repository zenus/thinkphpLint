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
use it\icosaedro\containers\Sortable;
use it\icosaedro\utils\UString;
use it\icosaedro\io\codepage\CodePageInterface;
use CastException;
use OutOfBoundsException;
use ZipArchive;

/**
 * Represents an entry of a ZIP file as returned from the ZipFileReader class.
 * 
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 21:04:07 $
 */
class ZipFileReaderEntry implements Printable, UPrintable, Sortable {

	private /*. ZipArchive .*/ $a;
	private $i = 0;
	private /*. CodePageInterface .*/ $codepage;
	private /*. mixed[string] .*/ $stat;
	private $opsys = 0;
	private $attr = 0;
	private /*. UString .*/ $name;

	/**
	 * Creates a file entry of a ZIP archive. This constructor method is
	 * called by it\icosaedro\io\ZipFileReader for every entry of the
	 * ZIP archive.
	 * @param ZipArchive $a
	 * @param int $i
	 * @param CodePageInterface $codepage
	 * @throws ZipFileException
	 * @throws OutOfBoundsException Invalid index.
	 */
	function __construct($a, $i, $codepage)
	{
		$this->a = $a;
		$this->i = $i;
		$this->codepage = $codepage;
		if( $i < 0 || $i >= $a->numFiles )
			throw new OutOfBoundsException("no this entry index $i");
		$this->stat = $a->statIndex($i);
		if( $this->stat === FALSE )
			throw new ZipFileException("failed retrieving stat data for entry $i");
		if( ! $a->getExternalAttributesIndex($i, $this->opsys, $this->attr) )
			throw new ZipFileException("failed retrieving attributes data for entry $i");
		try {
			$this->name = $codepage->decode((string) $this->stat["name"]);
		}
		catch(IOException $e){
			throw new ZipFileException("failed decoding name of entry $i: " . $e->getMessage());
		}
	}


	/**
	 * Returns the index of this entry in the ZIP archive.
	 * @return int Index of this entry in the ZIP archive, the first entry
	 * being zero.
	 */
	public function getIndex()
	{
		return (int) $this->stat["index"];
	}


	/**
	 * Returns the path of this file entry.
	 * @return UString Path of this file entry.
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Tells if this entry represents a directory, that is its name ends with
	 * the slash "/" character.
	 * @return bool True if this entry is a directory.
	 */
	public function isDirectory()
	{
		return $this->name->endsWith(UString::fromASCII("/"));
	}


	/**
	 * Returns the CRC32 of the original file.
	 * @return int CRC32 of the original file.
	 * FIXME: might return -1 if unknown?
	 */
	public function getCrc()
	{
		return (int) $this->stat["crc"];
	}


	/**
	 * Returns the size of the original file.
	 * @return int Size of the original file (bytes).
	 * FIXME: might return -1 if unknown?
	 */
	public function getSize()
	{
		return (int) $this->stat["size"];
	}


	/**
	 * Returns the size of the compressed file.
	 * @return int Size of the compressed file (bytes).
	 */
	public function getCompressedSize()
	{
		return (int) $this->stat["comp_size"];
	}


	/**
	 * Returns the modification time of the original file.
	 * @return int Modification time of the original file as number of seconds
	 * since the "Unix epoch".
	 */
	public function getTime()
	{
		return (int) $this->stat["mtime"];
	}


	/**
	 * Returns the compression method.
	 * @return int Compression method used for this file (see constants
	 * {@link \ZipArchive::CM_DEFAULT} etc.
	 */
	public function getMethod()
	{
		return (int) $this->stat["comp_method"];
	}


	/**
	 * Returns the original operating system of this file.
	 * @return int Original operating system of this file (see constants
	 * {@link \ZipArchive::OPSYS_UNIX} etc.
	 */
	public function getOperatingSystem()
	{
		return $this->opsys;
	}


	/**
	 * Returns the operating system specific attributes for this entry.
	 * @return int
	 */
	public function getAttributes()
	{
		return $this->attr;
	}


	/**
	 * Returns the uncompressed, original binary content of this file.
	 * @return string Uncompressed, original binary content of this file.
	 * @throws ZipFileException Invalid ZIP file format.
	 */
	public function getContent()
	{
		$c = $this->a->getFromIndex($this->i);
		if( is_string($c) )
			return $c;
		else
			throw new ZipFileException("failed reading entry " . $this->i);
	}


	/**
	 * Returns a resource from which the uncompressed, original binary content
	 * of this file can be read.
	 * @return resource Invalid ZIP file format.
	 * @throws ZipFileException
	 */
	public function getStream()
	{
		$r = $this->a->getStream($this->a->getNameIndex($this->i));
		if( is_resource($r) )
			return $r;
		else
			throw new ZipFileException("failed getting resource for entry " . $this->i);
	}


	/**
	 * Returns the path of this file.
	 * @return string Path of this file as read from the ZIP archive, and with
	 * unspecified encoding.
	 */
	public function __toString()
	{
		return (string) $this->stat["name"];
	}
	
	
	/**
	 * Returns the path of this file.
	 * @return UString Path of this file.
	 */
	public function toUString()
	{
		return $this->getName();
	}
	
	
	/**
	 * Returns true if this ZIP entry is the same ob
	 * @param object $other
	 * @return bool
	 */
	public function equals($other)
	{
		if( $other === NULL )
			return FALSE;
		
		# Fast, easy test:
		if( $this === $other )
			return TRUE;

		# If they belong to different classes, cannot be
		# equal, also if the 2 classes are relatives:
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		
		$other2 = cast(__CLASS__, $other);
		
		# Here, comparison specific of the class, field by field.
		# See also the Equality::areEqual($a,$b) method. Example:

		return $this->name->equals($other2->name);
	}
	
	/**
	 * @param object $other
	 * @return int
	 */
	public function compareTo($other)
	{
		if( $other === NULL )
			throw new CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new CastException("expected " . __CLASS__
			. " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		return $this->name->compareTo($other2->name);
	}

}
