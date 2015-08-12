<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../errors.php";
require_once __DIR__ . "/IOException.php";

/**
 * Writable stream of bytes. Several implementations of this abstract class
 * may provide, for example, access to the file system or to data in memory.
 * The following scheme illustrates the relationship between the main classes
 * derived from this class (names in slanted characters are abstract classes
 * you cannot use directly):
 * 
 * <blockquote><pre>
 * <i>OutputStream</i>
 * |
 * +----{@link ./Base64OutputStream.html Base64OutputStream}
 * |
 * +----{@link ./ResourceOutputStream.html ResourceOutputStream}
 * |    |
 * |    +----{@link ./FileOutputStream.html FileOutputStream}
 * |    |
 * |    +----{@link ./ZLIBCompressOutputStream.html ZLIBCompressOutputStream}
 * |         |
 * |         +----{@link ./GZIPCompressOutputStream.html GZIPCompressOutputStream}
 * |
 * +----{@link ./StringOutputStream.html StringOutputStream}
 * </pre></blockquote>
 * 
 * <p><b>Base64OutputStream</b> is a wrapper that encodes data in the Base64
 * format.
 * 
 * <p><b>ResourceOutputStream</b> is a wrapper that takes a PHP resource and
 * allows to write on it using the generic output stream interface.
 * 
 * <p><b>ZLIBCompressOutputStream</b> is a wrapper that compresses using the
 * ZLIB algorithm.
 * 
 * <p><b>GZIPCompressOutputStream</b> is a wrapper that compresses using the
 * GZIP algorithm.
 * 
 * <p><b>StringOutputStream</b> writes data inside a memory buffer.
 *
 * <p>In this example, the program reads itself by chunks of 512 bytes and
 * writes a copy to a GZIP-ped compressed file:
 *
 * <blockquote><pre>
 * $in_fn = File::fromLocaleEncoded(__FILE__ . '.gz');
 * $out_fn = File::fromLocaleEncoded(__FILE__);
 * $in = new FileInputStream($out_fn);
 * $out = new GZIPOutputStream( new FileOutputStream($in_fn) );
 * while(($bytes = $in-&gt;readBytes(512)) !== NULL)
 *     $out-&gt;writeBytes($bytes);
 * $in-&gt;close();
 * $out-&gt;close();
 * </pre></blockquote>
 * 
 * <p>Note that, in this way, large amount of data can be processed avoiding to
 * load the whole content of the file in memory.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:16:26 $
 */
abstract class OutputStream {

	
	/**
	 * Writes a single byte.
	 * @param int $b Byte to write. Only the lower 8 bits are actually
	 * written.
	 * @return void
	 * @throws IOException
	 */
	abstract public function writeByte($b);


	/**
	 * Writes a string of bytes. Does nothing if the string is NULL
	 * or empty.
	 * @param string $bytes
	 * @return void
	 * @throws IOException
	 */
	public function writeBytes($bytes) {
		$len = strlen($bytes);
		for($i = 0; $i < $len; $i++)
			$this->writeByte(ord($bytes[$i]));
	}


	/**
	 * Does its best to actually write any pending data stored the internal
	 * buffer.
	 * @return void
	 * @throws IOException
	 */
	function flush()
	{}


	/**
	 * Closes the file, flushing internal buffers.
	 * Does nothing if already closed.
	 * Once closed, this object cannot be used anymore.
	 * @return void
	 * @throws IOException
	 */
	public function close() {}


}
