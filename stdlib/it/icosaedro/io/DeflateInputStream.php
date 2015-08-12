<?php
/*. require_module 'streams'; .*/

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use ErrorException;


/**
 * Input stream filter that decompresses using the DEFLATE algorithm (RFC 1951).
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:52:37 $
 */
class DeflateInputStream extends ResourceInputStream {


	/**
	 * Creates a new DEFLATE decompressor stream reader.
	 * @param InputStream $in Compressed input stream to read.
	 * @throws IOException
	 */
	public function __construct($in)
	{
		try {
			$deflated = InputStreamAsResource::get($in);
			stream_filter_append($deflated, 'zlib.inflate', STREAM_FILTER_READ);
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage(), 0, $e);
		}
		
		parent::__construct($deflated);
	}

}
