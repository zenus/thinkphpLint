<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../errors.php";
require_once __DIR__ . "/OutputStream.php";
require_once __DIR__ . "/IOException.php";
use ErrorException;

/**
 * Writes bytes to a resource, typically opened with fopen() or fsocketopen()
 * and turns it into an output stream. Examples:
 * <pre>
 *		$out = new ResourceOutputStream( fopen("php://stdout", "w") );
 *		$err = new ResourceOutputStream( fopen("php://stderr", "w") );
 *		...
 *		$socket = fsockopen("www.icosaedro.it", "80");
 *		$out = new ResourceOutputStream($socket);
 *		$in  = new ResourceInputStream($socket);
 *		$out-&gt;writeBytes("GET / HTTP/1.0\r\n"
 *			. "Host: www.icosaedro.it\r\n"
 *			. "\r\n");
 *		$out-&gt;flush();
 *		while( ($line = $in-&gt;readBytes(100)) !== NULL )
 *			echo $line;
 *		$out-&gt;close(); // this also closes $socket
 * </pre>
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/25 20:41:17 $
 */
class ResourceOutputStream extends OutputStream {
	
	private /*. resource .*/ $r;
	
	
	/**
	 * Initializes writing on resource.
	 * @param resource $r 
	 * @return void
	 */
	public function __construct($r){
		$this->r = $r;
	}

	
	/**
	 * Writes a single byte.
	 * @param int $b Byte to write. Only the lower 8 bits are actually
	 * written.
	 * @return void
	 * @throws IOException
	 */
	public function writeByte($b){
		try {
			if( fwrite($this->r, chr($b & 255)) !== 1 )
				throw new IOException("zero bytes written");
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage());
		}
	}


	/**
	 * Writes a string of bytes. Does nothing if the string is NULL
	 * or empty.
	 * @param string $bytes
	 * @return void
	 * @throws IOException
	 */
	public function writeBytes($bytes) {
		if( strlen($bytes) == 0 )
			return;
		try {
			$n = fwrite($this->r, $bytes);
			if( $n !== strlen($bytes) )
				throw new IOException("only $n bytes written");
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage());
		}
	}


	/*. void .*/ function flush()
		/*. throws IOException .*/
	{
		try {
			if( ! fflush($this->r) )
				throw new IOException("flushing failed");
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage());
		}
	}


	/**
	 * Closes the file. Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close(){
		if( $this->r !== NULL ){
			try {
				fclose($this->r);
			}
			catch(ErrorException $e){
				throw new IOException($e->getMessage());
			}
			$this->r = NULL;
		}
	}


}
