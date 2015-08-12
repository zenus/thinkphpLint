<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../all.php";

use RuntimeException;


/**
 * Input stream filter that retains the latest N bytes (the tail) of the filtered
 * stream. The tail is NOT returned to the client through the normal read*()
 * method; this tail is returned by the special getTail() method.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/11/24 20:57:20 $
 */
class KeepTailInputStream extends InputStream {
	
	/**
	 * @var InputStream
	 */
	private $in;
	
	private $tail_length = 0;
	
	/**
	 * @var boolean
	 */
	private $eof = false;
	
	/**
	 * @var string
	 */
	private $buf = "";
	
	/**
	 * @param InputStream $in
	 * @param int $tail_length
	 */
	public function __construct($in, $tail_length)
	{
		$this->in = $in;
		$this->tail_length = $tail_length;
	}
	
	
	/**
	 * @param int $n
	 * @return string
	 * @throws IOException
	 */
	public function readBytes($n)
	{
		if( $n <= 0 )
			return "";
		do {
			$buf_len = strlen($this->buf);
			$available = $buf_len - $this->tail_length;
			if( $available <= 0 ){
				$more = $this->in->readBytes($n - $buf_len + $this->tail_length); // FIXME: check overflow
				if( $more === NULL ){
					$this->eof = TRUE;
					return NULL;
				}
				$this->buf .= $more;
			} else {
				$res_len = (int) min($n, $available);
				if( $res_len == $buf_len ){
					$res = $this->buf;
					$this->buf = "";
					return $res;
				} else {
					$res = substr($this->buf, 0, $res_len);
					$this->buf = substr($this->buf, $res_len);
					return $res;
				}
			}
		} while(TRUE);
	}
	
	
	/**
	 * @return int
	 * @throws IOException
	 */
	public function readByte()
	{
		$b = $this->readBytes(1);
		if( $b === NULL )
			return -1;
		else
			return ord($b);
	}
	
	
	/**
	 * Returns the tail bytes. Obviously, this method can return a meaningful
	 * value only after the end of the stream has been reached.
	 * @return string Tail bytes, possibly fewer than expected if the stream is
	 * short.
	 * @throws RuntimeException Method invoked before the end of the filtered
	 * input stream.
	 */
	public function getTail()
	{
		if( ! $this->eof )
			throw new RuntimeException("not at the end of the file");
		return $this->buf;
	}
	
	
	/**
	 * Closes the input stream.
	 * @return void
	 * @throws IOException
	 */
	public function close()
	{
		if( $this->in == NULL )
			return;
		$this->in->close();
		$this->in = NULL;
	}
	
}