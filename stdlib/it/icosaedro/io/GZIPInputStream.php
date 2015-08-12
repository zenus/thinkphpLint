<?php
/*. require_module 'hash'; .*/

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use it\icosaedro\utils\UString;

/**
 * Reads a stream of compressed data using the GZIP format (RFC 1952).
 * GZIP-ped streams contain an header of variable length, a body with
 * compressed data using the DEFLATE algorithm (RFC 1951) and a tail containing
 * the CRC32 and the original length of the uncompressed data.
 * <p>
 * The header may contain several informations, including the original file name,
 * a comment, the modification time and the originating operating system.
 * The header's data may be protected by a checksum that is recognized and
 * checked.
 * <p>
 * A final CRC32 checksum located at the very end of the stream allows to verify
 * the integrity of the carried data. This checksum is then verified only at the
 * end, when the close() method is called.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/12/09 20:08:37 $
 */
class GZIPInputStream extends DeflateInputStream {
	
	/**
	 * The first two bytes of the GZIP stream must be these.
	 */
	const MAGIC = "\x1f\x8b";
	
	/** Compression method, mandatory. */
	public $CM = 0;
	
	/** FLG flags, mandatory. */
	public $FLG = 0;
	
	/** @access private */
	const
		FLG_FTEXT_MASK = 1,
		FLG_FHCRC_MASK = 2,
		FLG_FEXTRA_MASK = 4,
		FLG_FNAME_MASK = 8,
		FLG_FCOMMENT_MASK = 16;
	
	/**
	 * Modification time; Unix timestamp, mandatory.
	 */
	public $MTIME = 0;
	
	public $XFL = 0;
	
	/**
	 * Operating system where the stream was generated: 3=Unix, 11=NTFS.
	 * The complete list is available in RFC 1952.
	 * @var int
	 */
	public $OS = 0;
	
	// Optional GZIP header's fields follow:
	
	/**
	 * Extra field of binary data, NULL if missing.
	 * @var string
	 */
	public $EXTRA = NULL;
	
	/**
	 * Name of the file, NULL if missing.
	 * @var UString
	 */
	public $NAME = NULL;
	
	/**
	 * Comment, NULL if missing.
	 * @var UString
	 */
	public $COMMENT = NULL;
	
	/**
	 * @var KeepTailInputStream
	 */
	private $keep_tail;
	
	/**
	 * Number of uncompressed bytes read so far (unsigned 32 bit integer).
	 * @var int
	 */
	private $length = 0;
	
	/**
	 * Checksum context for the uncompressed data.
	 * @var resource
	 */
	private $crc;
	
	/**
	 * Starts reading a GZIP-ped stream and read the header data setting the
	 * public properties of this object.
	 * @param InputStream $in Source of the GZIP-ped, compressed data.
	 * @throws IOException Error reading the input stream.
	 * @throws CorruptedException Invalid GZIP format. Incomplete GZIP header.
	 * Invalid or unexpected values in GZIP header. Bad header's CRC.
	 */
	public function __construct($in) {
		$keep_tail = new KeepTailInputStream($in, 8);
		$crc = hash_init("crc32b");
		$header = $keep_tail->readBytes(10);
		if( $header === NULL || strlen($header) < 10
			|| substr($header, 0, 2) !== self::MAGIC )
			throw new CorruptedException("not a GZIP file");
		hash_update($crc, $header);
		$this->CM = ord($header[2]);
		$this->FLG = ord($header[3]);
		$this->MTIME = ((ord($header[7])*256 + ord($header[6]))*256
				+ ord($header[5])) * 256 + ord($header[4]);
		$this->XFL = ord($header[8]);
		$this->OS = ord($header[9]);
		
		
		// RFC 1952, 2.3.1.2 Compliance: highest 3 bits of FLG must be zero:
		if( ($this->FLG & 0xe0) != 0 )
			throw new CorruptedException("unsupported or invalid GZIP format: FLG=" . $this->FLG);
		
		if( ($this->FLG & self::FLG_FEXTRA_MASK) != 0 ){
			$len_s = $keep_tail->readBytes(2);
			if( strlen($len_s) != 2 )
				throw new CorruptedException("premature end");
			hash_update($crc, $len_s);
			$len = ord($len_s[1])*256 + ord($len_s[0]);
			$this->EXTRA = $keep_tail->readBytes($len);
			if( strlen($this->EXTRA) != $len )
				throw new CorruptedException("premature end");
			hash_update($crc, $header);
		}
		
		if( ($this->FLG & self::FLG_FNAME_MASK) != 0 ){
			$s = "";
			do {
				$b = $keep_tail->readByte();
				if( $b < 0 )
					throw new CorruptedException("premature end");
				hash_update($crc, chr($b));
				if( $b == 0 )
					break;
				$s .= chr($b);
			} while(TRUE);
			$this->NAME = UString::fromISO88591($s);
		}
		
		if( ($this->FLG & self::FLG_FCOMMENT_MASK) != 0 ){
			$s = "";
			do {
				$b = $keep_tail->readByte();
				if( $b < 0 )
					throw new CorruptedException("premature end");
				hash_update($crc, chr($b));
				if( $b == 0 )
					break;
				$s .= chr($b);
			} while(TRUE);
			$this->COMMENT = UString::fromISO88591($s);
		}
		
		if( ($this->FLG & self::FLG_FHCRC_MASK) != 0 ){
			$HCRC = $keep_tail->readBytes(2);
			if( strlen($HCRC) != 2 )
				throw new CorruptedException("premature end");
			$crc_final = hash_final($crc, TRUE);
			$crc_final = $crc_final[3] . $crc_final[2];
			if( $HCRC !== $crc_final )
				throw new CorruptedException("corrupted header");
		}
		
		$this->keep_tail = $keep_tail;
		parent::__construct($keep_tail);
		$this->crc = hash_init("crc32b");
	}
	
	/**
	 * Returns a short description of this GZIP stream containing most of the
	 * header's data.
	 * @return string
	 */
	public function __toString() {
		$s = "[GZIP:"
			. " compression method: " . ($this->CM == 8? "DEFLATE" : (string)$this->CM)
			. ", FLG: " . $this->FLG
			. ", MTIME: " . ($this->MTIME == 0? "N.A." : gmdate("Y-m-d\\TH:i:s\\Z", $this->MTIME))
			. ", XFL: " . $this->XFL
			. ", OS: " . $this->OS;
		if( $this->EXTRA !== NULL )
			$s .= ", EXTRA: 0x" . bin2hex($this->EXTRA);
		if( $this->NAME !== NULL )
			$s .= ", NAME: " . $this->NAME;
		if( $this->COMMENT !== NULL )
			$s .= ", COMMENT: " . $this->COMMENT;
		return $s . "]";
	}
	
	
	/**
	 * Reads one byte of uncompressed data.
	 * @return int Byte read in [0,255], or -1 on end of file.
	 * @throws IOException
	 */
	public function readByte()
	{
		$b = parent::readByte();
		if( $b >= 0 ){
			$this->length = ($this->length + 1) & 0xffffffff;
			hash_update($this->crc, chr($b));
		}
		return $b;
	}
	

	/**
	 * Reads bytes of uncompressed data.
	 * @param int $n Maximum number of bytes to read.
	 * @return string Bytes read, possibly in a number less than requested,
	 * either because the end of the file has been reached, or the input
	 * buffer is short but still data are available. If $n &le; 0 does nothing
	 * and the empty string is returned. If $n &gt; 0 and the returned string
	 * is NULL, the end of the file is reached.
	 * @throws IOException
	 */
	public function readBytes($n)
	{
		$res = parent::readBytes($n);
		if( strlen($res) > 0 ){
			$this->length = ($this->length + strlen($res)) & 0xffffffff;
			hash_update($this->crc, $res);
		}
		return $res;
	}
	
	
	/**
	 * Closes the stream and checks the final CRC and length.
	 * @return void
	 * @throws IOException Error reading the input stream.
	 * @throws CorruptedException Premature end of the input stream. The length
	 * of the decompressed data do not match the expected value. The CRC of the
	 * decompressed data do not match the expected value.
	 */
	public function close()
	{
		if( $this->keep_tail === NULL )
			return;
		// Skip to the end, just in case the client stopped to read:
		while( $this->readBytes(512) !== NULL ) ;
		$tail = $this->keep_tail->getTail();
		if( strlen($tail) != 8 )
			throw new CorruptedException("premature end");
		
		// "length" is the modulo 2^32 of the original len:
		$length = (((ord($tail[7])*256 + ord($tail[6]))*256
				+ ord($tail[5])) * 256 + ord($tail[4])) & 0xffffffff;
		if( $length != $this->length )
			throw new CorruptedException("invalid length $length, read " . $this->length);
		
		$crc = hash_final($this->crc, TRUE);
		if( !( $crc[0] === $tail[3] && $crc[1] === $tail[2]
				&& $crc[2] === $tail[1] && $crc[3] === $tail[0] ) )
			throw new CorruptedException("invalid CRC");
		
		$this->keep_tail->close();
		$this->keep_tail = NULL;
	}
	
}
