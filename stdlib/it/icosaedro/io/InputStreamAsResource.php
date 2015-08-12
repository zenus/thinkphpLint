<?php

/*. require_module 'streams'; .*/

namespace it\icosaedro\io;

require_once __DIR__ . "/../../../all.php";

use ErrorException;

/**
 * PHP's stream wrapper that reads from an InputStream. The input stream to
 * be read must be set in the static property $in_param before creating the
 * resource - see the next class for an example.
 * @access private
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/23 15:48:54 $
 */
class ResourceFromInputStream extends StreamWrapper {
	
	const NAME = __CLASS__;
	
	/**
	 * The client must set here the input stream from which data wil be read
	 * by this stream wrapper. The first instance of this class created next,
	 * will read here its configuration parameter.
	 * @var InputStream
	 */
	public static $in_param;
	
	/**
	 * Input stream from which this wrapper returns data. The constructor
	 * retrieves its value from the static property $in_param.
	 * @var InputStream
	 */
	private $in;
	
	/**
	 * What to return on end of the input stream. PHP's I/O functions seems to
	 * return an empty string.
	 */
	const RETURN_ON_EOF = "";
	
	private $eof = FALSE;
	
	/**
	 * Name of the PHP's stream protocol "NAME://", lazy-initialized.
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
		$this->in = self::$in_param;
		self::$in_param = NULL;
		return TRUE;
	}
	

	/**
	 * Private implementation of the stream_read() method with slight different
	 * signature: it may throw exceptions.
	 * @param int $count
	 * @return string
	 * @throws IOException
	 */
	private function readBytes($count) {
		if( $count <= 0 )
			return "";
		if( $this->eof )
			return self::RETURN_ON_EOF;
		$bytes = $this->in->readBytes($count);
		if( $bytes === NULL ){
			$this->eof = TRUE;
			return self::RETURN_ON_EOF;
		}
		return $bytes;
	}
	

	/**
	 * @param int $count
	 * @return string
	 * @throws IOException
	 */
	public function stream_read($count) {
		return $this->readBytes($count);
	}
	

	/**
	 * @return boolean
	 */
	public function stream_eof() {
		return $this->eof;
	}
	
	
	/**
	 * @return boolean
	 */
	function stream_flush(){
		//return fflush($this->context);
		return TRUE; // FIXME: ?
	}
	
	
	/**
	 * @return void
	 * @throws IOException
	 */
	public function stream_close() {
		if( $this->in === NULL )
			return;
		$this->in->close();
		//fclose($this->context);
		$this->in = NULL;
	}

}


/**
 * Converts an InputStream into a regular PHP's input resource.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/23 15:48:54 $
 */
class InputStreamAsResource {

	/**
	 * Converts a resource into an InputStream.
	 * @param InputStream $in Existing input stream.
	 * @return resource PHP's stream resource available for read operations.
	 * @throws IOException
	 */
	public static function get($in) {
		ResourceFromInputStream::$in_param = $in;
		try {
			return fopen(ResourceFromInputStream::getProtocol() . "://", "r");
		}
		catch(ErrorException $e){
			throw new IOException($e->getMessage());
		}
	}

}
