<?php

namespace it\icosaedro\io;
require_once __DIR__ . "/../../../autoload.php";
require_once __DIR__ . "/../../../errors.php";
use ErrorException;
use it\icosaedro\io\IOException;

/**
 * Reads a file as a stream of bytes. The constructor accepts an Unicode file
 * name. Examples:
 * <pre>
 *		$fn = UString::fromUTF8("UTF8_encoded_filename_here.txt");
 *		$f = new FileInputStream( new File($fn) );
 *		while( ($bytes = $f-&gt;readBytes(100)) !== NULL )
 *			echo $bytes;
 *		$f-&gt;close();
 * </pre>
 * See also the LineInputWrapper class to read files line by line.
 *
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:16:02 $
 */
class FileInputStream extends SeekableResourceInputStream {

	/**
	 * Opens the specified file for reading.
	 * @param File $name Name of the file.
	 * @return void
	 * @throws FileNotFoundException The file does not exists. One of the
	 * components of the path does not exist.
	 * @throws FilePermissionException Access permission denied to at least one
	 * component of the path.
	 * @throws IOException Invalid file name. File name or its path contains
	 * characters that cannot be mapped to the current system locale.
	 */
	function __construct($name) {
		try {
			$f = fopen($name->getLocaleEncoded(), "rb");
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
