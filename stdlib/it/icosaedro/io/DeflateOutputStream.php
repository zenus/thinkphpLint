<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;


/**
 * Output stream filter that compresses using the DEFLATE algorithm (RFC 1951).
 * This compressed format is not used normally alone, but it is the base of
 * several other safer file formats, like ZLIB Compress (RFC 1950) and GZIP
 * (RFC 1952).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:52:37 $
 */
class DeflateOutputStream extends ZLIBCompressOutputStream {
	
	/*
	 * Implementation note. It seems that a PHP's stream wrapper performing
	 * the DEFLATE compression does not exist. As a workaround, this class
	 * extends the ZLIB compressor and strips away the header (2 B) and the
	 * trailer (4 B) to get back the compressed stream. In a perfect world
	 * it should be the vice-versa, and the ZLIBOutputStream class should wrap
	 * this one.
	 */

	/**
	 * Creates a new DEFLATE compressor stream writer.
	 * @param OutputStream $out Destination of the compressed stream.
	 * @param int $level Compression level ranging from 0 (no compression) up to
	 * 9 (maximum compression level, possibly slower); -1 is the internal
	 * default of the library.
	 * @throws IOException
	 * @throws InvalidArgumentException Invalid level.
	 */
	public function __construct($out, $level = -1)
	{
		parent::__construct(
			new SkipHeadOutputStream(
				new SkipTailOutputStream($out, 4),
				2
			),
			$level
		);
	}
	

}
