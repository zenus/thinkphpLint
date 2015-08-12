<?php

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../all.php";

/*. require_module 'streams'; .*/

use ErrorException;

/**
 * Stream wrapper that implement a protocol to write on a generic OutputStream
 * object. In this way, PHP's stream functions like fopen(), etc. can be applied
 * to PHPLint OutputStream as well.
 * Note that only write methods are implemented; any attempt to read from this
 * resource throws UnimplementedException.
 * @access private
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:57:49 $
 */
class ResourceFromOutputStream extends StreamWrapper {
	
	const NAME = __CLASS__;
	
	/**
	 * The client must set here the destination output stream to be used by this
	 * wrapper before actually create a new stream. The constructor of this
	 * class will read here its parameter.
	 * @var OutputStream
	 */
	public static $out_param;
	
	/**
	 * The constructor of this class sets here the destination output stream.
	 * @var OutputStream
	 */
	private $out;
	
	/**
	 *
	 * @var string
	 */
	private static $lazy_protocol;
	
	
	/**
	 * Returns its own stream protocol name and registers that protocol.
	 * @return string
	 * @throws ErrorException
	 */
	public static function getProtocol()
	{
		if( self::$lazy_protocol === NULL ){
			self::$lazy_protocol = (string) str_replace("\\", ".", __CLASS__);
			stream_wrapper_register(self::$lazy_protocol, __CLASS__);
		}
		return self::$lazy_protocol;
	}
	

	/**
	 * @param string $path
	 * @param string $mode
	 * @param int $options
	 * @param string & $opened_path
	 * @return boolean
	 * @throws IOException
	 */
	public function stream_open($path, $mode, $options, &$opened_path) {
		$this->out = self::$out_param;
		self::$out_param = NULL;
		return TRUE;
	}

	/**
	 * @param string $data
	 * @return int
	 * @throws IOException
	 */
	public function stream_write($data) {
		$this->out->writeBytes($data);
		return strlen($data);
	}
	
	/**
	 * @return boolean
	 * @throws IOException
	 */
	public function stream_flush() {
		$this->out->flush();
		return TRUE;
	}
	
	/**
	 * @return void
	 * @throws IOException
	 */
	public function stream_close() {
		if( $this->out === NULL )
			return;
		$this->out->close();
		$this->out = NULL;
	}

}


/**
 * Maps a PHPLint's OutputStream into a PHP's stream resource, so that the full
 * set of PHP's stream functions can be applied.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:57:49 $
 */
class OutputStreamAsResource {

	/**
	 * Returns a PHP's stream resource from a PHPLint's OutputStream. Note that
	 * the returned stream can be used only to write data; any attempt to read
	 * from this resource throws UnimplementedException.
	 * @param OutputStream $out
	 * @return resource
	 * @throws IOException
	 */
	public static function get($out) {
		ResourceFromOutputStream::$out_param = $out;
		try {
			return fopen(ResourceFromOutputStream::getProtocol()."://", "r");
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage());
		}
	}

}
