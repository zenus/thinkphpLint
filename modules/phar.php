<?php

/*. require_module 'spl'; .*/

class PharException extends Exception {}


/*. forward class PharData {} .*/

class Phar extends RecursiveDirectoryIterator implements Countable, ArrayAccess {

const
	NONE = 0x00000000,
	COMPRESSED = 0x0000F000,
	GZ = 0x00001000,
	BZ2 = 0x00002000,
	SAME = 0,
	PHAR = 1,
	TAR = 2,
	ZIP = 3,
	MD5 = 0x0001,
	SHA1 = 0x0002,
	SHA256 = 0x0003,
	SHA512 = 0x0004,
	OPENSSL = 0x0010,
	PHP = 1,
	PHPS = 2;

	/*. void .*/ function __construct(/*. string .*/ $fname, $flags = 0, /*. string .*/ $alias = NULL)
		/*. throws BadMethodCallException, UnexpectedValueException .*/
	{
		parent::__construct($fname);
	}
	/*. void .*/ function addEmptyDir(/*. string .*/ $dirname){}
	/*. void .*/ function addFile(/*. string .*/ $file, /*. string .*/ $localname = NULL)
		/*. throws Exception .*/{}
	/*. void .*/ function addFromString(/*. string .*/ $localname, /*. string .*/ $contents)
		/*. throws Exception .*/{}
	/*. string .*/ function apiVersion(){}
	/*. string[string] .*/ function buildFromDirectory(/*. string .*/ $base_dir, /*. string .*/ $regex = NULL)
		/*. throws BadMethodCallException, PharException .*/{}
	/*. string[string] .*/ function buildFromIterator(Iterator $iter, /*. string .*/ $base_directory = NULL)
		/*. throws UnexpectedValueException, BadMethodCallException, PharException .*/ {}
	/*. bool .*/ function canCompress($type = 0){}
	/*. bool .*/ function canWrite(){}
	/*. Phar .*/ function compress(/*. int .*/ $compression, /*. string .*/ $extension = NULL)
		/*. throws BadMethodCallException .*/ {}
	/*. bool .*/ function compressAllFilesBZIP2()
		/*. throws BadMethodCallException .*/ {}
	/*. bool .*/ function compressAllFilesGZ()
		/*. throws BadMethodCallException .*/ {}
	/*. void .*/ function compressFiles(/*. int .*/ $compression)
		/*. throws BadMethodCallException .*/ {}
	/*. PharData .*/ function convertToData($format = 9021976, $compression = 9021976, /*. string .*/ $extension = NULL)
		/*. throws BadMethodCallException, PharException .*/ {}
	/*. Phar .*/ function convertToExecutable($format = 9021976, $compression = 9021976, /*. string .*/ $extension = NULL)
		/*. throws BadMethodCallException, UnexpectedValueException, PharException .*/ {}
	/*. bool .*/ function copy(/*. string .*/ $oldfile, /*. string .*/ $newfile)
		/*. throws BadMethodCallException, PharException .*/ {}
	/*. int .*/ function count(){}
	/*. string .*/ function createDefaultStub(/*. string .*/ $indexfile = NULL, /*. string .*/ $webindexfile = NULL)
		/*. throws UnexpectedValueException .*/ {}
	/*. Phar .*/ function decompress(/*. string .*/ $extension = NULL)
		/*. throws BadMethodCallException .*/ {}
	/*. bool .*/ function decompressFiles()
		/*. throws BadMethodCallException .*/ {}
	/*. bool .*/ function delMetadata()
		/*. throws PharException .*/ {}
	/*. bool .*/ function delete(/*. string .*/ $entry)
		/*. throws PharException .*/ {}
	/*. bool .*/ function extractTo(/*. string .*/ $pathto, /*. mixed .*/ $files_or_dir, $overwrite = false)
		/*. throws PharException .*/ {}
	/*. mixed .*/ function getMetadata(){}
	/*. bool .*/ function getModified(){}
	/*. string[string] .*/ function getSignature(){}
	/*. string .*/ function getStub()
		/*. throws RuntimeException .*/ {}
	/*. string[int] .*/ function getSupportedCompression(){}
	/*. string[int] .*/ function getSupportedSignatures(){}
	/*. string .*/ function getVersion(){}
	/*. bool .*/ function hasMetadata(){}
	/*. void .*/ function interceptFileFuncs(){}
	/*. bool .*/ function isBuffering(){}
	/*. mixed .*/ function isCompressed(){}
	/*. bool .*/ function isFileFormat(/*. int .*/ $format)
		/*. throws PharException .*/ {}
	/*. bool .*/ function isValidPharFilename(/*. string .*/ $filename, $executable = true){}
	/*. bool .*/ function isWritable(){}
	/*. bool .*/ function loadPhar(/*. string .*/ $filename, /*. string .*/ $alias = NULL)
		/*. throws PharException .*/ {}
	/*. bool .*/ function mapPhar(/*. string .*/ $alias = NULL, $dataoffset = 0)
		/*. throws PharException .*/ {}
	/*. void .*/ function mount(/*. string .*/ $pharpath, /*. string .*/ $externalpath)
		/*. throws PharException .*/ {}
	/*. void .*/ function mungServer(array $munglist)
		/*. throws UnexpectedValueException .*/ {}
	/*. bool .*/ function offsetExists(/*. mixed .*/ $offset){}
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $offset){}
	/*. void .*/ function offsetSet(/*. mixed .*/ $offset, /*. mixed .*/ $value)
		/*. throws BadMethodCallException .*/ {}
	/*. void .*/ function offsetUnset(/*. mixed .*/ $offset)
		/*. throws BadMethodCallException .*/ {}
	/*. string .*/ function running($retphar = true){}
	/*. bool .*/ function setAlias(/*. string .*/ $alias)
		/*. throws UnexpectedValueException .*/ {}
	/*. bool .*/ function setDefaultStub(/*. string .*/ $index = NULL, /*. string .*/ $webindex = NULL)
		/*. throws UnexpectedValueException, PharException .*/ {}
	/*. void .*/ function setMetadata(/*. mixed .*/ $metadata){}
	/*. void .*/ function setSignatureAlgorithm(/*. int .*/ $sigtype, /*. string .*/ $privatekey = NULL)
		/*. throws UnexpectedValueException .*/ {}
	/*. bool .*/ function setStub(/*. string .*/ $stub)
		/*. throws UnexpectedValueException, PharException .*/ {}
	/*. void .*/ function startBuffering(){}
	/*. void .*/ function stopBuffering()
		/*. throws PharException .*/ {}
	/*. bool .*/ function uncompressAllFiles()
		/*. throws BadMethodCallException .*/ {}
	/*. bool .*/ function unlinkArchive(/*. string .*/ $archive)
		/*. throws PharException .*/ {}
	/*. void .*/ function webPhar(/*. string .*/ $alias = NULL, $index = "index.php", /*. string .*/ $f404 = NULL, $mimetypes = /*. (string[string]) .*/ array(), $rewrites = /*. (string[string]) .*/ array())
		/*. throws PharException, UnexpectedValueException .*/ {}

}


class PharData extends Phar {
	/*. void .*/ function __construct(/*. string .*/ $fname, $flags = 0, /*. string .*/ $alias = NULL, $format = Phar::TAR){
		parent::__construct($fname);
	}
}


class PharFileInfo extends SplFileInfo {
	/*. void .*/ function __construct(/*. string .*/ $entry)
		/*. throws BadMethodCallException, UnexpectedValueException .*/
	{
		parent::__construct($entry);
	}
	/*. void .*/ function chmod(/*. int .*/ $permissions){}
	/*. bool .*/ function compress(/*. int .*/ $compression){}
	/*. bool .*/ function decompress()
		/*. throws BadMethodCallException .*/ {}
	/*. bool .*/ function delMetadata()
		/*. throws BadMethodCallException, PharException .*/ {}
	/*. int .*/ function getCRC32()
		/*. throws BadMethodCallException .*/ {}
	/*. int .*/ function getCompressedSize(){}
	/*. mixed .*/ function getMetadata(){}
	/*. int .*/ function getPharFlags(){}
	/*. bool .*/ function hasMetadata(){}
	/*. bool .*/ function isCRCChecked(){}
	/*. bool .*/ function isCompressed($compression_type = 9021976){}
	/*. bool .*/ function isCompressedBZIP2(){}
	/*. bool .*/ function isCompressedGZ(){}
	/*. bool .*/ function setCompressedBZIP2()
		/*. throws BadMethodCallException .*/ {}
	/*. bool .*/ function setCompressedGZ()
		/*. throws BadMethodCallException .*/ {}
	/*. void .*/ function setMetadata(/*. mixed .*/ $metadata){}
	/*. bool .*/ function setUncompressed()
		/*. throws BadMethodCallException .*/ {}
}
