<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/IOException.php";

/**
 * Input stream of bytes. Possible implementations of this abstract class
 * may provide, for example, access to the file system or to data in memory.
 * The classes derived from InputStream can be combined in several ways,
 * allow to write generic algorithms and increase the performances of an
 * application by processing streams of data on a chain of filters, reducing
 * memory consumption and fragmentation.
 * 
 * <p>The following scheme summarizes the relationship between some of the
 * main classes under this namespace. <i>Slanted</i> names are abstract classes
 * you cannot really instantiate.
 * 
 * <blockquote><pre>
 *  <i>InputStream</i>
 *  |
 *  +---{@link ./Base64InputStream.html Base64InputStream}
 *  |
 *  +---{@link ./LineInputWrapper.html LineInputWrapper}
 *  |
 *  +---{@link ./ResourceInputStream.html ResourceInputStream}
 *  |   |
 *  |   +---{@link ./BZip2InputStream.html BZip2InputStream}
 *  |   |
 *  |   +---{@link ./DeflateInputStream.html DeflateInputStream}
 *  |       |
 *  |       +---{@link ./ZLIBCompressInputStream.html ZLIBCompressInputStream}
 *  |       |
 *  |       +-- {@link ./GZIPInputStream.html GZIPInputStream}
 *  |
 *  +---{@link ./SeekableInputStream.html <i>SeekableInputStream</i>}
 *      |
 *      +---{@link ./StringInputStream.html StringInputStream}
 *      |
 *      +---{@link ./SeekableResourceInputStream.html SeekableResourceInputStream}
 *          |
 *          +---{@link ./FileInputStream.html FileInputStream}
 * </pre></blockquote>
 * 
 * <p>The <b><i>InputStream</i></b> is the basic ancestor of all these
 * classes and provides only the basic methods to read bytes from a generic
 * source: readByte() and readBytes().
 * 
 * <p><b>Base64InputStream</b> is a wrapper whose constructor takes an
 * input stream of Base-64 encoded data and whose readByte() and readBytes()
 * methods returns the decoded, original data.
 * 
 * <p><b>DeflateInputStream</b> and <b>GZIPInputStream</b> are wrapper whose
 * constructor takes an input stream of data compressed with the DEFLATE
 * or GZIP algorithm, and whose readByte() and readBytes() methods return
 * the decodempressed, original bytes.
 * 
 * <p><b>ResourceInputStream</b> is a wrapper whose constructor takes a
 * readable resource (one of those returned by the PHP library) and turns
 * it into an object that behaves just like a InputStream.
 * 
 * <p><b><i>SeekableInputStream</i></b> extends an <i>InputStream</i>
 * provising methods to move the read position back and forth over the
 * stream by setting the offset from the beginning of the file. Files on
 * disk and blocks of memory can provide this useful feature, while other
 * types of input (net sockets, serial ports, etc.) cannot.
 * 
 * <p><b>SeekableResourceInputStream</b> is a wrapper whose constructor
 * takes a readable resource (one of those returned by the PHP library)
 * and turns it into an object that behaves just like a SeekableInputStream.
 * 
 * <p><b>FileInputStream</b> allows to read data from a file on
 * disk. Basically, it is a replacement for the PHP's fopen(), fread(),
 * fclose(), with the important difference that it implements the
 * SeekableInputStream interface and then also the InputStream interface.
 * 
 * <p><b>StringInputStream</b> allows to read the content of a string as
 * if it where an input stream of bytes.
 * 
 * <p>These classes can be combined in several ways. For example, we may
 * read a GZIP-ped compressed file containing lines of text:
 * 
 * <blockquote><pre>
 * $fn = File::fromLocaleEncoded(__DIR__ . '/data.txt.gz');
 * $in = new LineInputWrapper(
 *     new GZIPInputStream(
 *         new FileInputStream($fn) ) );
 * while( ($line = $in-&gt;readLine()) !== NULL)
 *     echo $line;
 * $in-&gt;close();
 * </pre></blockquote>
 * 
 * <p>In this example, data are read by small chunks from the file, are
 * decompressed, the the end of each line is detected and the resulting
 * assembled complete line is returned. Arbitrarily large files can be processed
 * in this way avoiding to load their whole content in the memory and avoiding
 * to manipulate large strings causing memory fragmentation.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:16:14 $
 */
abstract class InputStream {


	/**
	 * Reads one byte.
	 * @return int Byte read in [0,255], or -1 on end of file.
	 * @throws IOException
	 */
	abstract public function readByte();


	/**
	 * Reads bytes.
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
		$bytes = "";
		while( $n > 0 ){
			$b = $this->readByte();
			if( $b < 0 )
				break;
			$bytes .= chr($b);
		}
		if( $n > 0 and strlen($bytes) == 0 )
			return NULL;
		else
			return $bytes;
	}


	/**
	 * Reads exactly the number of bytes given or fails with exception.
	 * @param int $n Number of bytes to read.
	 * @return string The $n bytes read. If $n &le; 0 does nothing and the
	 * empty string is returned.
	 * @throws IOException 
	 */
	public function readFully($n)
	{
		if( $n <= 0 )  return "";
		$bytes = $this->readBytes($n);
		if( strlen($bytes) != $n )
			throw new IOException("$n bytes requested, but read ".strlen($bytes));
		return $bytes;
	}


//	/**
//	 * Skip bytes.
//	 * @param int $n Number of bytes to skip. Does nothing if this number
//	 * is <= 0.
//	 * @return void
//	 * @throws IOException
//	 */
//	public function skip($n)
//	{
//		if( $n <= 0 )
//			return;
//		$bytes = $this->readBytes($n);
//		if( strlen($bytes) < $n )
//			throw new IOException("beyond end of file");
//	}


	/**
	 * Closes the stream. Does nothing if the stream has been already closed.
	 * @return void
	 * @throws IOException
	 */
	public function close(){}

}
