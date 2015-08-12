<?php
/** Zip File Functions.

See: {@link http://www.php.net/manual/en/class.ziparchive.php}
@package zip
*/

/*. resource .*/ function zip_open(/*. string .*/ $filename){}
/*. void .*/ function zip_close(/*. resource .*/ $zip){}
/*. resource .*/ function zip_read(/*. resource .*/ $zip){}
/*. bool .*/ function zip_entry_open(/*. resource .*/ $zip_dp, /*. resource .*/ $zip_entry  /*. , args .*/){}
/*. void .*/ function zip_entry_close(/*. resource .*/ $zip_ent){}
/*. mixed .*/ function zip_entry_read(/*. resource .*/ $zip_entry  /*. , args .*/){}
/*. string .*/ function zip_entry_name(/*. resource .*/ $zip_entry){}
/*. int .*/ function zip_entry_compressedsize(/*. resource .*/ $zip_entry){}
/*. int .*/ function zip_entry_filesize(/*. resource .*/ $zip_entry){}
/*. string .*/ function zip_entry_compressionmethod(/*. resource .*/ $zip_entry){}

/*. if_php_ver_5 .*/

/**
 * A file archive, compressed with Zip. Apparently, from the documentation and
 * from may tests, the methods of this class never trigger errors nor throw
 * exceptions; instead, some return FALSE to indicate an error, but whithout
 * further details. All methods, except the default constructor and
 * <code>open()</code> trigger E_WARNING if the archive has not been initialized
 * with <code>open()</code>, but this error is not indicated here, assuming it
 * is a bug in the program the program itself should not try to manage by
 * itself.
 * 
 * <p><b>WARNING:</b> several methods may return FALSE on error rather than
 * the specific type listed here, so check the documentation and always test
 * the actual value returned with a code like this:
 * <pre>
 * $res = $zip-&gt;someMethod(...);
 * if( $res === FALSE ){
 *	die("ZipArchive::someMethod(): error");
 * }
 * </pre>
 */
class ZipArchive
{

	const
		CHECKCONS = 4,
		CM_BZIP2 = 12,
		CM_DEFAULT = -1,
		CM_DEFLATE = 8,
		CM_DEFLATE64 = 9,
		CM_IMPLODE = 6,
		CM_LZ77 = 19,
		CM_LZMA = 14,
		CM_PKWARE_IMPLODE = 10,
		CM_PPMD = 98,
		CM_REDUCE_1 = 2,
		CM_REDUCE_2 = 3,
		CM_REDUCE_3 = 4,
		CM_REDUCE_4 = 5,
		CM_SHRINK = 1,
		CM_STORE = 0,
		CM_TERSE = 18,
		CM_WAVPACK = 97,
		CREATE = 1,
		ER_CHANGED = 15,
		ER_CLOSE = 3,
		ER_COMPNOTSUPP = 16,
		ER_CRC = 7,
		ER_DELETED = 23,
		ER_EOF = 17,
		ER_EXISTS = 10,
		ER_INCONS = 21,
		ER_INTERNAL = 20,
		ER_INVAL = 18,
		ER_MEMORY = 14,
		ER_MULTIDISK = 1,
		ER_NOENT = 9,
		ER_NOZIP = 19,
		ER_OK = 0,
		ER_OPEN = 11,
		ER_READ = 5,
		ER_REMOVE = 22,
		ER_RENAME = 2,
		ER_SEEK = 4,
		ER_TMPOPEN = 12,
		ER_WRITE = 6,
		ER_ZIPCLOSED = 8,
		ER_ZLIB = 13,
		EXCL = 2,
		FL_COMPRESSED = 4,
		FL_NOCASE = 1,
		FL_NODIR = 2,
		FL_UNCHANGED = 8,
		OPSYS_ACORN_RISC = 13,
		OPSYS_ALTERNATE_MVS = 15,
		OPSYS_AMIGA = 1,
		OPSYS_ATARI_ST = 5,
		OPSYS_BEOS = 16,
		OPSYS_DEFAULT = 3,
		OPSYS_DOS = 0,
		OPSYS_MACINTOSH = 7,
		OPSYS_MVS = 11,
		OPSYS_OPENVMS = 2,
		OPSYS_OS_2 = 6,
		OPSYS_OS_400 = 18,
		OPSYS_OS_X = 19,
		OPSYS_TANDEM = 17,
		OPSYS_UNIX = 3,
		OPSYS_VFAT = 14,
		OPSYS_VM_CMS = 4,
		OPSYS_VSE = 12,
		OPSYS_WINDOWS_NTFS = 10,
		OPSYS_Z_CPM = 9,
		OPSYS_Z_SYSTEM = 8,
		OVERWRITE = 8;

	public /*. int .*/ $status = 0;
	public /*. int .*/ $statusSys = 0;
	public /*. int .*/ $numFiles = 0;
	public /*. string .*/ $filename = NULL;
	public /*. string .*/ $comment = NULL;

	/*. bool .*/ function addEmptyDir(/*. string .*/ $dirname){}
	/*. bool .*/ function addFile(/*. string .*/ $filepath, /*. string .*/ $localname = NULL, $start = 0, $length = -1){}
	/*. resource .*/ function addFromString(/*. string .*/ $name, /*. string .*/ $content){}
	/*. bool .*/ function addGlob(/*. string .*/ $pattern, $flags = 0, /*. mixed[string] .*/ $options = array()){}
	/*. bool .*/ function addPattern(/*. string .*/ $pattern, $path = '.', /*. mixed[string] .*/ $options = array()){}
	/*. bool .*/ function close(){}
	/*. bool .*/ function deleteIndex(/*. int .*/ $index){}
	/*. bool .*/ function deleteName(/*. string .*/ $name){}
	/*. bool .*/ function extractTo(/*. string .*/ $destination, /*. mixed .*/ $entries){}
	/*. string .*/ function getArchiveComment($flags=0){}
	/*. string .*/ function getCommentIndex(/*. int .*/ $index, $flags=0){}
	/*. string .*/ function getCommentName(/*. string .*/ $name, $flags=0){}
	/*. bool .*/ function getExternalAttributesIndex(/*. int .*/ $index, /*. int .*/ &$opsys, /*. int .*/ &$attr, $flags=0){}
	/*. bool .*/ function getExternalAttributesName(/*. int .*/ $index, /*. int .*/ &$opsys, /*. int .*/ &$attr, $flags=0){}
	/*. string .*/ function getFromIndex(/*. int .*/ $index, $length = 0, $flags = 0){}
	/*. resource .*/ function getFromName(/*. string .*/ $entryname, $length = 0, $flags = 0){}
	/*. string .*/ function getNameIndex(/*. int .*/ $index, $flags = 0){}
	/*. string .*/ function getStatusString(){}
	/*. resource .*/ function getStream(/*. string .*/ $name){}
	/*. int .*/ function locateName(/*. string .*/ $name, $flags = 0){}
	/*. mixed .*/ function open(/*. string .*/ $source, $flags = 0){}
	/*. bool .*/ function renameIndex(/*. int .*/ $index, /*. string .*/ $new_name){}
	/*. bool .*/ function renameName(/*. string .*/ $name, /*. string .*/ $new_name){}
	/*. bool .*/ function setArchiveComment(/*. string .*/ $comment){}
	/*. bool .*/ function setCommentIndex(/*. int .*/ $index, /*. string .*/ $comment){}
	/*. bool .*/ function setCommentName(/*. string .*/ $name, /*. string .*/ $comment){}
	/*. bool .*/ function setExternalAttributesIndex(/*. int .*/ $index, /*. int .*/ $opsys, /*. int .*/ $attr, $flags = 0){}
	/*. bool .*/ function setExternalAttributesName(/*. string .*/ $name, /*. int .*/ $opsys, /*. int .*/ $attr, $flags = 0){}
	/*. mixed[string] .*/ function statIndex(/*. int .*/ $index, $flags = 0){}
	/*. mixed[string] .*/ function statName(/*. string .*/ $filename, $flags = 0){}
	/*. bool .*/ function setPassword(/*. string .*/ $password){}
	/*. bool .*/ function unchangeAll(){}
	/*. bool .*/ function unchangeArchive(){}
	/*. bool .*/ function unchangeIndex(/*. int .*/ $index){}
	/*. bool .*/ function unchangeName(/*. string .*/ $name){}
}

/*. end_if_php_ver .*/
