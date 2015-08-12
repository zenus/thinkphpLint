<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/../../../errors.php";
use ErrorException;
use it\icosaedro\io\IOException;
use it\icosaedro\io\ResourceOutputStream;

/**
 * Writes a file as a stream of bytes.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/06 10:06:43 $
 */
class FileOutputStream extends ResourceOutputStream {

	/**
	 * Opens the specified file for writing. Overwrites the file if
	 * it already exists.
	 * @param File $name Name of the file.
	 * @param bool $append If the file already exists, append new data.
	 * @return void
	 * @throws FileNotFoundException One of the components of the path does not
	 * exist.
	 * @throws FilePermissionException Access permission denied to at least one
	 * component of the path.
	 * @throws IOException Invalid file name. File name or its path contains
	 * characters that cannot be mapped to the current system locale. Access
	 * denied to the file or to some part of the path.
	 */
	function __construct($name, $append = FALSE) {
		try {
			$f = fopen($name->getLocaleEncoded(), $append? "ab" : "wb");
		}
		catch(ErrorException $e){
			$m = $e->getMessage();
			if( strpos($m, "No such file or directory in ") !== FALSE )
				throw new FileNotFoundException($name,
					"file not found: " . $name->toUString()->toASCII());
			else if( strpos($m, "Permission denied in ") !== FALSE )
				throw new FilePermissionException($name,
					"access denied: " . $name->toUString()->toASCII());
			else
				throw new IOException($m);
		}
		parent::__construct($f);
	}

}
