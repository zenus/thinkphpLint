<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";
use UnimplementedException;

/**
 * PHP's stream wrapper abstract class as described in
 * {@link http://php.net/manual/en/class.streamwrapper.php} and provided here
 * as a reference.
 * Although the PHP manual nothing says about errors, here is that all the
 * methods throw IOException; usually, PHP functions triggers E_WARNING
 * instead.
 * All the methods throw {@link UnimplementedException} so that missing
 * implementations can be readily detected and only the methods that are
 * actually needed can be implemented.
 * FIXME: some of the methods should be static, I suspect, but I'm not sure.
 */
abstract class StreamWrapper {
	public /*. resource .*/ $context ;
	
	// /*. void .*/ function __construct(){}
	// 
	// /*. void .*/ function __destruct(){}
	
	/**
	 * Trap for any possible missing new methods PHP might add in the future to
	 * this class.
	 */
	public function __call(/*. string .*/ $method, /*. mixed .*/ $a)
	{throw new UnimplementedException("missing method $method");}
	
	/**
	 * @return boolean
	 * @throws IOException
	 */
	function dir_closedir()
	{throw new UnimplementedException();}
	
	/**
	 * @param string $path
	 * @param int $options
	 * @return boolean
	 * @throws IOException
	 */
	function dir_opendir($path, $options){throw new UnimplementedException();}
	
	/**
	 * @return string
	 * @throws IOException
	 */
	function dir_readdir(){throw new UnimplementedException();}
	
	/**
	 * @return boolean
	 * @throws IOException
	 */
	function dir_rewinddir(){throw new UnimplementedException();}
	
	/**
	 * @param string $path
	 * @param int $mode
	 * @param int $options
	 * @return boolean
	 * @throws IOException
	 */
	function mkdir($path, $mode, $options){throw new UnimplementedException();}
	
	/**
	 * @param string $path_from
	 * @param string $path_to
	 * @return boolean
	 * @throws IOException
	 */
	function rename($path_from, $path_to){throw new UnimplementedException();}
	
	/**
	 * @param string $path
	 * @param int $options
	 * @return boolean
	 * @throws IOException
	 */
	function rmdir($path, $options){throw new UnimplementedException();}
	
	/**
	 * @param int $cast_as
	 * @return resource
	 * @throws IOException
	 */
	function stream_cast($cast_as){throw new UnimplementedException();}
	
	/**
	 * @return void
	 * @throws IOException
	 */
	function stream_close(){throw new UnimplementedException();}
	
	/**
	 * @return boolean
	 * @throws IOException
	 */
	function stream_eof(){throw new UnimplementedException();}
	
	/**
	 * @return boolean
	 * @throws IOException
	 */
	function stream_flush(){throw new UnimplementedException();}
	
	/**
	 * @param int $operation
	 * @return boolean
	 * @throws IOException
	 */
	function stream_lock($operation){throw new UnimplementedException();}
	
	/**
	 * @param string $path
	 * @param int $option
	 * @param int $var_
	 * @return boolean
	 * @throws IOException
	 */
	function stream_metadata($path, $option, $var_)
	{throw new UnimplementedException();}
	
	/**
	 * @param string $path
	 * @param string $mode
	 * @param int $options
	 * @param string & $opened_path
	 * @return boolean Always returns true.
	 * @throws IOException
	 */
	function stream_open($path, $mode, $options, &$opened_path)
	{throw new UnimplementedException();}
	
	/**
	 * @param int $count
	 * @return string Bytes read, in a number not greather than $count.
	 * @throws IOException
	 */
	function stream_read($count){throw new UnimplementedException();}
	
	/**
	 * @param int $offset
	 * @param int $whence
	 * @return boolean
	 * @throws IOException
	 */
	function stream_seek($offset, $whence = SEEK_SET)
	{throw new UnimplementedException();}
	
	/**
	 * @param int $option
	 * @param int $arg1
	 * @param int $arg2
	 * @return boolean
	 * @throws IOException
	 */
	function stream_set_option($option, $arg1, $arg2)
	{throw new UnimplementedException();}
	
	/**
	 * @return array[]int
	 * @throws IOException
	 */
	function stream_stat(){throw new UnimplementedException();}
	
	/**
	 * @return int
	 * @throws IOException
	 */
	function stream_tell(){throw new UnimplementedException();}
	
	/**
	 * @param string $data
	 * @return int Number of bytes actually written.
	 * @throws IOException
	 */
	function stream_write($data){throw new UnimplementedException();}
	
	/**
	 * @param string $path
	 * @return boolean
	 * @throws IOException
	 */
	function unlink($path){throw new UnimplementedException();}
	
	/**
	 * @param string $path
	 * @param int $flags
	 * @return array[]int
	 * @throws IOException
	 */
	function url_stat($path, $flags){throw new UnimplementedException();}
}