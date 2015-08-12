<?php

namespace com\tecnick\barcode;

require_once __DIR__ . "/../../../all.php";

use InvalidArgumentException;
use it\icosaedro\containers\Printable;

/**
 * Single bar or space in a barcode.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/26 10:47:48 $
 */
class Bar implements Printable {
	
	/** Type: true = black (mark), false = white (space). */
	public $t = FALSE;
	/** Bar width in units. */
	public $w = 0;
	/** Height in units. */
	public $h = 0;
	/** Bar top position: 0 = top, 1 = middle. */
	public $p = 0;
	
	/**
	 * @param boolean $t
	 * @param int $w
	 * @param int $h
	 * @param int $p
	 * @throws InvalidArgumentException Invalid $p.
	 */
	function __construct($t, $w, $h, $p) {
		if ( ! ($p == 0 || $p == 1) )
			throw new InvalidArgumentException("p=$p");
		$this->t = $t;
		$this->w = $w;
		$this->h = $h;
		$this->p = $p;
	}
	
	
	function __toString() {
		return "[" . ($this->t? "S":"B") . "," . $this->w . "," . $this->h
				. "," . $this->p . "]";
	}
}
