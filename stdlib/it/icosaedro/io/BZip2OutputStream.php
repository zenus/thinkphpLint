<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;
use ErrorException;


/**
 * Output stream filter that compresses using the BZIP2 format.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 22:13:19 $
 */
class BZip2OutputStream extends ResourceOutputStream {

	
	/**
	 * Creates a new BZIP2 compressed stream writer.
	 * @param OutputStream $out Destination of the compressed stream.
	 * @param int $blocks Value from 1 to 9 specifying the number of 100 KB
	 * blocks of memory to allocate for workspace.
	 * @param int $work Value ranging from 0 to 250 indicating how much
	 * effort to expend using the normal compression method before falling
	 * back on a slower, but more reliable method. Tuning this parameter
	 * effects only compression speed. Neither size of compressed output
	 * nor memory usage are changed by this setting. A work factor of 0
	 * instructs the bzip library to use an internal default.
	 * @throws IOException
	 * @throws InvalidArgumentException Invalid arguments.
	 */
	public function __construct($out, $blocks = 1, $work = 0)
	{
		if( $blocks < 1 || $blocks > 9 )
			throw new InvalidArgumentException("blocks=$blocks");
		if( $work < 0 || $work > 250 )
			throw new InvalidArgumentException("work=$work");
		try {
			$bz = OutputStreamAsResource::get($out);
			
			stream_filter_append($bz, 'bzip2.compress',
				STREAM_FILTER_WRITE,
				array('blocks' => $blocks, 'work' => $work) );
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage(), 0, $e);
		}
		parent::__construct($bz);
	}
	

}
