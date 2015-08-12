<?php
/*. require_module 'hash'; .*/

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;
use it\icosaedro\utils\UString;


/**
 * Writes a stream of compressed data using the GZIP format (RFC 1952).
 * GZIP-ped streams contain an header of variable length, a body with
 * compressed data using the DEFLATE algorithm (RFC 1951) and a tail containing
 * the CRC32 and the original length of the uncompressed data.
 * <p>
 * The header may contain several informations, some are mandatory and some are
 * not: the original name and the comment are optional arguments of the
 * constructor; the modification time is set with the current system time;
 * the OS type is guessed as being either Unix or NTFS depending from the
 * current value of the PHP's DIRECTORY_SEPARATOR constant.
 * The header's data are be protected by a checksum.
 * <p>
 * A final CRC32 checksum and original length of the uncompressed data are
 * added to the end of the stream, when the close() method is called.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/12/09 20:09:20 $
 */
class GZIPOutputStream extends DeflateOutputStream {
	
	/**
	 * @access private
	 */
	const MAGIC = "\x1f\x8b";
	
	/**
	 * If to add a CRC to the GZIP header. Note that the command-line version
	 * of gzip up through version 1.3.x do not support header CRC. Modern
	 * versions of GZIP (and other utilities) should be ok.
	 * @access private
	 */
	const ADD_HCRC = TRUE;
	
	/**
	 * Compression method: DEFLATE.
	 * @access private
	 */
	const CM = 8;
	
	/**
	 * @access private
	 */
	const
		FLG_FTEXT_MASK = 1,
		FLG_FHCRC_MASK = 2,
		FLG_FEXTRA_MASK = 4,
		FLG_FNAME_MASK = 8,
		FLG_FCOMMENT_MASK = 16;
	
	/**
	 * @var OutputStream
	 */
	private $out;
	
	/**
	 * @var resource
	 */
	private $crc;
	
	/**
	 * Number of bytes written so far (unsigned 32 bit integer).
	 * @var int
	 */
	private $length = 0;
	
	/**
	 * Creates a new GZIP compressed output stream.
	 * @param OutputStream $out Destination of the compressed data.
	 * @param UString $name Optional name of the data or file. Cannot contain
	 * the byte zero.
	 * The name is stored in the GZIP file using the ISO-8859-1 encoding, so
	 * invalid characters are silently removed.
	 * @param UString $comment Optional comment. Cannot contain the zero byte.
	 * The comment is stored in the GZIP file using the ISO-8859-1 encoding, so
	 * invalid characters are silently removed.
	 * @param int $level Compression level, ranging from 0 (no compression) up
	 * to 9 (maximum compression); -1 is the internal default of the library.
	 * @throws InvalidArgumentException The name or the comment contains the
	 * zero byte. Invalid compression level.
	 * @throws IOException Error writing to the output stream.
	 */
	public function __construct($out, $name = NULL, $comment = NULL, $level = -1)
	{
		if( $level < -1 || $level > 9 )
			throw new InvalidArgumentException("level=$level");
		
		$crc = hash_init("crc32b"); // header's crc
		
		$out->writeBytes(self::MAGIC);
		hash_update($crc, self::MAGIC);
		
		$out->writeByte(self::CM);
		hash_update($crc, chr(self::CM));
		
		$FLG = 0;
		if( self::ADD_HCRC )
			$FLG |= self::FLG_FHCRC_MASK;
		if( $name !== NULL )
			$FLG |= self::FLG_FNAME_MASK;
		if( $comment !== NULL )
			$FLG |= self::FLG_FCOMMENT_MASK;
		$out->writeByte($FLG);
		hash_update($crc, chr($FLG));
		
		$MTIME = pack("V", time());
		$out->writeBytes($MTIME);
		hash_update($crc, $MTIME);
		
		// XFL:
		$out->writeByte(0);
		hash_update($crc, chr(0));
		
		$OS = DIRECTORY_SEPARATOR === "\\"? 11 /* WinNT */ : 3 /* Unix */;
		$out->writeByte($OS);
		hash_update($crc, chr($OS));
		
		if( $name !== NULL ){
			$name_iso = $name->toISO88591();
			if( strpos($name_iso, "\0") !== FALSE )
				throw new InvalidArgumentException("name contains the zero byte");
			$out->writeBytes($name_iso.chr(0));
			hash_update($crc, $name_iso.chr(0));
		}
		
		if( $comment !== NULL ){
			$comment_iso = $comment->toISO88591();
			if( strpos($comment_iso, "\0") !== FALSE )
				throw new InvalidArgumentException("comment contains the zero byte");
			$out->writeBytes($comment_iso.chr(0));
			hash_update($crc, $comment_iso.chr(0));
		}
		
		if( self::ADD_HCRC ){
			$crc_bytes = hash_final($crc, TRUE);
			$out->writeByte(ord($crc_bytes[3]));
			$out->writeByte(ord($crc_bytes[2]));
		}
		
		$this->crc = hash_init("crc32b");
		$this->out = $out;
		
		parent::__construct(new UncloseableOutputStream($out), $level);
	}
	
	
	/**
	 * Compresses an writes a string of bytes. Does nothing if the string is
	 * NULL or empty.
	 * @param string $bytes
	 * @throws IOException
	 */
	public function writeBytes($bytes)
	{
		if( strlen($bytes) > 0 ){
			parent::writeBytes($bytes);
			hash_update($this->crc, $bytes);
			$this->length = ($this->length + strlen($bytes)) & 0xffffffff;
		}
	}
	
	
	/**
	 * Compresses and writes a single byte.
	 * @param int $b Byte to write. Only the lower 8 bits are actually
	 * written.
	 * @throws IOException
	 */
	public function writeByte($b)
	{
		parent::writeByte($b);
		hash_update($this->crc, chr($b & 255));
		$this->length = ($this->length + 1) & 0xffffffff;
	}


	/**
	 * Saves the CRC and length of the uncompressed data, and closes the stream.
	 * Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		if( $this->out === NULL )
			return;
		parent::close();
		$crc = hash_final($this->crc, TRUE);
		$this->out->writeBytes($crc[3].$crc[2].$crc[1].$crc[0]);
		$this->out->writeBytes(pack("V", $this->length));
		$this->out->close();
		$this->out = NULL;
	}
	
}
