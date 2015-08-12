<?php
/** Standard PHP Library (SPL) Functions.

	This module is always available in PHP 5, but PHPLint holds it in a
	separate module for performace reasons since it is still seldom used.

	See: {@link http://www.php.net/manual/en/book.spl.php}
	@package spl
*/

/*. if_php_ver_4 .*/

	The_spl_module_is_only_for_PHP_5;

/*. end_if_php_ver .*/

/*. require_module 'standard'; .*/

# This module is deprecated by PHPLint:
/*     . require_module 'simplexml'; .    */

/** @deprecated Use the proper class constant. */
define('RIT_LEAVES_ONLY', 1);
/** @deprecated Use the proper class constant. */
define('RIT_SELF_FIRST', 1);
/** @deprecated Use the proper class constant. */
define('RIT_CHILD_FIRST', 1);
/** @deprecated Use the proper class constant. */
define('CIT_CALL_TOSTRING', 1);
/** @deprecated Use the proper class constant. */
define('CIT_CATCH_GET_CHILD', 1);


/*. unchecked .*/ class LogicException extends Exception {}
/*. unchecked .*/ class BadFunctionCallException extends LogicException {}
/*. unchecked .*/ class DomainException extends LogicException {}
/*. unchecked .*/ class InvalidArgumentException extends LogicException {}
/*. unchecked .*/ class LengthException extends LogicException {}

/*. unchecked .*/ class BadMethodCallException extends BadFunctionCallException {}

/*. unchecked .*/ class RuntimeException extends Exception {}
/*. unchecked .*/ class OutOfBoundsException extends RuntimeException {}
/*. unchecked .*/ class OutOfRangeException extends RuntimeException {}
/*. unchecked .*/ class OverflowException extends RuntimeException {}
/*. unchecked .*/ class RangeException extends RuntimeException {}
/*. unchecked .*/ class UnderflowException extends RuntimeException {}
/*. unchecked .*/ class UnexpectedValueException extends RuntimeException {}


interface Traversable
{
}

interface IteratorAggregate extends Traversable
{
	/*. Traversable .*/ function getIterator()
		/*. throws Exception .*/;
}


interface Countable
{
	/*. int .*/ function count();
}


/**
	Allows to scan the (key,element) pairs of a collection, one by one. Objects
	that implement this interface can also be used in the foreach() statement:
	<pre>
	class MyCollection implements Iterator { ... }
	$c = new MyCollection();
	foreach($c as $k =&gt; $e){ ... }
	</pre>
	The iterator keeps an internal cursor which is initially positioned over the
	first pair, if it exists. Use valid() to check if the cursor i currently over a
	pair, then use next() to move to the next pair. Example:
	<pre>
	$c-&gt;rewind();
	while( $c-&gt;valid() ){
		$key = $c-&gt;key();
		$element = $c-&gt;current();
		// ...do something with $key, $element
		$c-&gt;next();
	}
	</pre>
	The behaviour of this interface when the underlying collection gets changed
	in between the scanning with the iterator is undefined; results may depend
	on the implementation.
*/
interface Iterator extends Traversable
{
	/**
		Moves the cursor to the first (key,element) pair.
		@return void
	*/
	function rewind();

	/**
		Check if the cursor is on a valid element.
		@return bool
	*/
	function valid();

	/**
		Returns the key of the current (key,element) pair under the cursor.
		Prerequisite: the cursor must be over a valid element, or the behaviour
		is unspecified; implementations may throw an unchecked exception to
		help debugging programs.
		@return mixed
	*/
	function key();

	/**
		Returns the element of the current (key,element) pair under the cursor.
		Prerequisite: the cursor must be over a valid element, or the behaviour
		is unspecified; implementations may throw an unchecked exception to
		help debugging programs.
		@return mixed
	*/
	function current();

	/**
		Move the cursor to the next (key,element) pair, if any. If the cursor
		is already beyond the last pair, does nothing.
		@return void
	*/
	function next();
}


interface OuterIterator extends Iterator
{
	/*. Iterator .*/ function getInnerIterator();
}

interface RecursiveIterator extends Iterator
{
	/*. RecursiveIterator .*/ function getChildren();
	/*. bool  .*/ function hasChildren();
}

interface SeekableIterator extends Iterator
{
	/*. void .*/ function seek (/*. int .*/ $index) /*. throws OutOfBoundsException .*/;
}

interface ArrayAccess
{
	/*. bool  .*/ function offsetExists(/*. mixed .*/ $offset);
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $offset);
	/*. void  .*/ function offsetSet(/*. mixed .*/ $offset, /*. mixed .*/ $value);
	/*. void  .*/ function offsetUnset(/*. mixed .*/ $offset);
}

/*. forward interface SplSubject{} .*/

interface SplObserver
{
	/*. void .*/ function update(SplSubject $subject);
}

interface SplSubject
{
	/*. void .*/ function attach(SplObserver $observer);
	/*. void .*/ function detach(SplObserver $observer);
	/*. void .*/ function notify();
}


/**
 * Classes that implements this interface provide a custom serialization
 * algorithm.
 */
interface Serializable
{
	/**
	 * Performs custom serialization.
	 * The standard {@link serialize()} function call this method
	 * to retrieve serialized data.
	 * @return string Custom serialized data. You may return any string
	 * here that represents the state of this object and that allows to
	 * restore this state. The format of this string is specific of the
	 * implementation.
	 */
	function serialize();

	/**
	 * Unserializes data from custom serialization.
	 * The standard {@link unserialize()} function detects if a custom
	 * serialization algorithm is used, then creates the object from
	 * its class and calls this method instead of the constructor.
	 * This method must then initialize the object out of the serialized
	 * data generated by the {@link self::serialize()} method.
	 * <p><b>WARNING.</b> Calling this method from user code may destroy
	 * the object overwriting arbitrary data.
	 * @param string $serialized Serialized data as generated by the
	 * {@link self::serialize()} method.
	 * @return void
	 * @throws RuntimeException You may want to throw this exception
	 * if the serialized data passed are invalid. The official manual
	 * does not specify what to do in this case. The standard {@link
	 * unserialize()} function triggers an {@link E_NOTICE}, but
	 * trigger_error("",E_NOTICE) cannot be called here when errors are
	 * remapped into exceptions: in fact, a checked {@link ErrorException}
	 * would be thrown, but this would violate the contract of the interface
	 * as stated in the PHP manual.
	 */
	function unserialize($serialized);
}


class ArrayIterator
implements ArrayAccess, SeekableIterator, Countable, Serializable
{
	const ARRAY_AS_PROPS = 2;
	const STD_PROP_LIST = 1;
	/*. void  .*/ function __construct(/*. array .*/ $arr /*. , args .*/)
		/*. throws InvalidArgumentException .*/{}
	/*. void  .*/ function rewind(){}
	/*. void  .*/ function seek(/*. int .*/ $position)
		/*. throws OutOfBoundsException .*/{}
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function key(){}
	/*. void  .*/ function next(){}
	/*. bool  .*/ function valid(){}

	# FIMXE: adding missing implementations:
	/*. int   .*/ function count(){}
	/*. bool  .*/ function offsetExists(/*. mixed .*/ $offset){}
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $offset){}
	/*. void  .*/ function offsetSet(/*. mixed .*/ $offset, /*. mixed .*/ $value){}
	/*. void  .*/ function offsetUnset(/*. mixed .*/ $offset){}
	/*. void  .*/ function append(/*. mixed .*/ $value){}
	/*. void  .*/ function asort(){}
	/*. void  .*/ function ksort(){}
	/*. void  .*/ function natcasesort(){}
	/*. void  .*/ function natsort(){}
	/*. void  .*/ function uksort(){}
	/*. array .*/ function getArrayCopy(){}
	/*. int   .*/ function getFlags(){}
	/*. string.*/ function serialize(){}
	/*. void  .*/ function unserialize(/*. string .*/ $serialized){}
}

class RecursiveArrayIterator extends ArrayIterator implements RecursiveIterator
{
	public /*. RecursiveArrayIterator .*/ function getChildren(){}
	public /*. bool .*/ function hasChildren(){}
}

class ArrayObject
implements IteratorAggregate, ArrayAccess, Countable, Serializable
{
	const
		STD_PROP_LIST = 1,
		ARRAY_AS_PROPS = 2;

	/*. void  .*/ function __construct(/*. args .*/)
		/*. throws InvalidArgumentException .*/{}
	/*. void  .*/ function append(/*. mixed .*/ $newval){}
	/*. int   .*/ function count(){}
	/*. void  .*/ function asort(){}
	/*. void  .*/ function ksort(){}
	/*. void  .*/ function natcasesort(){}
	/*. void  .*/ function natsort(){}
	/*. void  .*/ function uasort(/*. string .*/ $cmp_function){}
	/*. void  .*/ function uksort(/*. string .*/ $cmp_function){}
	/*. array .*/ function exchangeArray(/*. mixed .*/ $input){}
	/*. array .*/ function getArrayCopy(){}
	/*. int   .*/ function getFlags(){}
	/*. void  .*/ function setFlags(/*. int .*/ $flags){}
	/*. ArrayIterator .*/ function getIterator(){}
	/*. string.*/ function getIteratorClass(){}
	/*. void  .*/ function setIteratorClass(
		/*. string .*/ $iterator_class){}
	/*. bool  .*/ function offsetExists(/*. mixed .*/ $index){}
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $index){}
	/*. void  .*/ function offsetSet(/*. mixed .*/ $index, /*. mixed .*/ $newval){}
	/*. void  .*/ function offsetUnset(/*. mixed .*/ $index){}
	/*. string.*/ function serialize(){}
	/*. void  .*/ function unserialize(/*. string .*/ $serialized){}
}

class IteratorIterator
implements OuterIterator
{
	/*. void  .*/ function __construct (Traversable $iterator, /*. string .*/ $classname=null){}
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function key(){}
	/*. void  .*/ function next(){}
	/*. void  .*/ function rewind(){}
	/*. bool  .*/ function valid(){}
	/*. Iterator .*/ function getInnerIterator(){}
}

/**
 * AppendIterator class.
 *
 * FIXME. According to the manual, this class has a constructor with
 * signature:
 * 
 * <p><code>public void function __construct(){}</code>
 * 
 * <p>but then this constructor should call the parent constructor, and
 * this latter in turn requires an argument of type Traversable, but I
 * don't know what this parameter stands for here. So I had to remove the
 * constructor at all because PHPLint requires to call the parent.
 *
 * <p>FIXME. This class also provides a magic __call() method with signature:
 *
 * <p>
 * <code>
 * public mixed function __call(mixed $func, array[int]mixed $params){}
 * </code>
 *
 * <p>which is not supported by PHPLint.
 */
class AppendIterator
extends IteratorIterator
{
	public /*. void .*/ function append ( Iterator $iterator ){}
	public /*. Iterator .*/ function current(){}
	public /*. void .*/ function getArrayIterator(){}
	public /*. Iterator .*/ function getInnerIterator(){}
	public /*. void .*/ function getIteratorIndex(){}
	public /*. mixed .*/ function key(){}
	public /*. void .*/ function next(){}
	public /*. void .*/ function rewind(){}
	public /*. boolean .*/ function valid(){}
}

abstract class FilterIterator
extends IteratorIterator
{
	/*. void .*/ function __construct(/*. Iterator .*/ $it) {parent::__construct($it);}
	/*. Iterator .*/ function getInnerIterator() {}
	/*. bool .*/ function valid(){}
	/*. mixed .*/ function key(){}
	/*. mixed .*/ function current(){}
	/*. void .*/ function rewind(){}
	/*. void .*/ function next(){}
}

abstract class RecursiveFilterIterator
extends FilterIterator
implements RecursiveIterator
{
	/*. RecursiveIterator .*/ function getChildren(){}
	/*. bool  .*/ function hasChildren(){}
}

class ParentIterator
extends RecursiveFilterIterator
{
	/*. void .*/ function rewind(){}
	/*. void .*/ function next(){}
	/*. void .*/ function __construct(/*. RecursiveIterator .*/ $it){parent::__construct($it);}
	/*. bool .*/ function hasChildren(){}
	/*. ParentIterator .*/ function getChildren(){}
	/*. Iterator .*/ function getInnerIterator(){}

	# FIXME: adding missing implementations:
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function key(){}
	/*. bool  .*/ function valid(){}
}

/*. forward class SplFileObject{} .*/

class SplFileInfo
{
	/*. void   .*/ function __construct( /*. string .*/ $file_name ){}
	/*. int    .*/ function getATime()
		/*. throws RuntimeException .*/ {}
	/*. string .*/ function getBasename(/*. string .*/ $suffix = ""){}
	/*. int    .*/ function getCTime()
		/*. throws RuntimeException .*/ {}
	/*. self   .*/ function getFileInfo(/*. string .*/ $class_name = NULL){}
	/*. string .*/ function getFilename(){}
	/*. int    .*/ function getGroup()
		/*. throws RuntimeException .*/ {}
	/*. int    .*/ function getInode()
		/*. throws RuntimeException .*/ {}
	/*. string .*/ function getLinkTarget()
		/*. throws RuntimeException .*/ {}
	/*. int    .*/ function getMTime(){}
	/*. int    .*/ function getOwner()
		/*. throws RuntimeException .*/ {}
	/*. string .*/ function getPath(){}
	/*. self   .*/ function getPathInfo(/*. string .*/ $class_name = NULL){}
	/*. string .*/ function getPathname(){}
	/*. int    .*/ function getPerms(){}
	/*. string .*/ function getRealPath(){}
	/*. int    .*/ function getSize(){}
	/*. string .*/ function getType()
		/*. throws RuntimeException .*/ {}
	/*. bool   .*/ function isDir(){}
	/*. bool   .*/ function isExecutable(){}
	/*. bool   .*/ function isFile(){}
	/*. bool   .*/ function isLink(){}
	/*. bool   .*/ function isReadable(){}
	/*. bool   .*/ function isWritable(){}
	/*. SplFileObject .*/ function openFile(
		$open_mode = "r",
		$use_include_path = FALSE,
		/*. resource .*/ $context = NULL)
		/*. throws RuntimeException .*/ {}
	/*. void   .*/ function setFileClass(/*. string .*/ $class_name = NULL){}
	/*. void   .*/ function setInfoClass(/*. string .*/ $class_name = NULL){}
	/*. string .*/ function __toString(){}
}

class SplFileObject
extends SplFileInfo
implements RecursiveIterator, SeekableIterator
{
	
	const
		DROP_NEW_LINE = 1,
		READ_AHEAD = 2,
		SKIP_EMPTY = 6,
		READ_CSV = 8;

	/*. void .*/ function __construct(
		/*. string .*/ $filename,
		/*. string .*/ $open_mode = "r",
		$use_include_path = FALSE,
		/*. resource .*/ $context = NULL)
		/*. throws RuntimeException .*/
		{ parent::__construct($filename /* ... */); }
	/*. SplFileObject .*/ function getChildren(){}
	/*. bool  .*/ function hasChildren(){}
	public /*. void .*/ function seek( /*. int .*/ $line_pos ){}
	public /*. mixed .*/ function current(){}
	public /*. boolean .*/ function eof(){}
	public /*. boolean .*/ function fflush(){}
	public /*. string .*/ function fgetc(){}
	public /*. array .*/ function fgetcsv(
	 	$delimiter = ",",
		$enclosure = "\"",
		$escape = "\\"){}
	public /*. string .*/ function fgets(){}
	public /*. string .*/ function fgetss($allowable_tags = ""){}
	public /*. boolean .*/ function flock(
	 	/*. int .*/ $operation,
		/*. boolean .*/ &$wouldblock = FALSE){}
	public /*. int .*/ function fpassthru(){}
	public /*. mixed .*/ function fscanf(
	 	/*. string .*/ $format,
		/*. mixed .*/ & $input_variable
		/*. , args .*/){}
	public /*. int .*/ function fseek(
	 	/*. int .*/ $offset,
		$whence = SEEK_SET){}
	public /*. array .*/ function fstat(){}
	public /*. int .*/ function ftell(){}
	public /*. boolean .*/ function ftruncate( /*. int .*/ $size ){}
	public /*. int .*/ function fwrite(
	 	/*. string .*/ $str,
		/*. int .*/ $length = -1){}
	public /*. array .*/ function getCsvControl(){}
	public /*. int .*/ function getFlags(){}
	public /*. int .*/ function getMaxLineLen(){}
	public /*. mixed .*/ function key(){}
	public /*. void .*/ function next(){}
	public /*. void .*/ function rewind(){}
	public /*. void .*/ function setCsvControl(
		$delimiter = ",",
		$enclosure = "\"",
		$escape = "\\"){}
	public /*. void .*/ function setFlags( /*. int .*/ $flags ){}
	public /*. void .*/ function setMaxLineLen( /*. int .*/ $max_len ){}
	public /*. boolean .*/ function valid(){}
}


class SplTempFileObject extends SplFileObject
{
	/*. void .*/ function __construct(/*. int .*/ $max_memory = 0)
	{ parent::__construct("", "w"); }
}


class DirectoryIterator
extends SplFileInfo
implements Iterator
{
	const
		CURRENT_MODE_MASK = 240,
		CURRENT_AS_PATHNAME = 32,
		CURRENT_AS_FILEINFO = 0,
		CURRENT_AS_SELF = 16,
		KEY_MODE_MASK = 3840,
		KEY_AS_PATHNAME = 0,
		KEY_AS_FILENAME = 256,
		NEW_CURRENT_AND_KEY = 256;

	/*. void .*/ function __construct(/*. string .*/ $path)
	{ parent::__construct($path); }
	/*. void .*/ function rewind(){}
	/*. string .*/ function key(){}
	/*. DirectoryIterator .*/ function current(){}
	/*. void .*/ function next(){}
	/*. bool .*/ function valid(){}
	/*. string .*/ function getPath(){}
	/*. string .*/ function getFilename(){}
	/*. string .*/ function getPathname(){}
	/*. string .*/ function Recursivekey(){}
	/*. bool .*/ function isDot(){}
	/*. int .*/ function getPerms(){}
	/*. int .*/ function getInode(){}
	/*. int .*/ function getSize(){}
	/*. int .*/ function getOwner(){}
	/*. int .*/ function getGroup(){}
	/*. int .*/ function getATime(){}
	/*. int .*/ function getMTime(){}
	/*. int .*/ function getCTime(){}
	/*. string .*/ function getType(){}
	/*. bool .*/ function isWritable(){}
	/*. bool .*/ function isReadable(){}
	/*. bool .*/ function isExecutable(){}
	/*. bool .*/ function isFile(){}
	/*. bool .*/ function isDir(){}
	/*. bool .*/ function isLink(){}
}

class FilesystemIterator
extends DirectoryIterator
implements SeekableIterator
{
	/*. void .*/ function __construct(/*. string .*/ $path /*. , args .*/)
	{ parent::__construct($path); }
	/*. FilesystemIterator .*/ function current(){}
	/*. int .*/ function getFlags(){}
	/*. string .*/ function key(){}
	/*. void .*/ function next(){}
	/*. void .*/ function rewind(){}
	/*. void .*/ function setFlags(/*. args .*/){}
	/*. void .*/ function seek(/*. int .*/ $position){}
}

class RecursiveDirectoryIterator
extends DirectoryIterator
implements RecursiveIterator
{
	/*. void .*/ function rewind(){}
	/*. void .*/ function next(){}
	/*. bool .*/ function hasChildren(/*. args .*/){}
	/*. RecursiveDirectoryIterator .*/ function getChildren(){}
	/*. string .*/ function key(){}

	# FIXME: adding missing implementations:
	/*. RecursiveDirectoryIterator .*/ function current(){}
	/*. bool  .*/ function valid(){}
}

class RecursiveIteratorIterator
implements OuterIterator
{
	const
		LEAVES_ONLY = 1,
		SELF_FIRST = 1,
		CHILD_FIRST = 1;

	/*. void .*/ function __construct(/*. RecursiveIterator .*/ $it /*., args .*/)
		/*. throws InvalidArgumentException .*/{}
	/*. void  .*/ function setMaxDepth(/*. int .*/ $max_depth)
		/*. throws OutOfRangeException .*/{}
	/*. void  .*/ function rewind(){}
	/*. bool  .*/ function valid(){}
	/*. mixed .*/ function key(){}
	/*. mixed .*/ function current(){}
	/*. void  .*/ function next(){}
	/*. int   .*/ function getDepth(){}
	/*. RecursiveIterator .*/ function getSubIterator(/*. args .*/){}
	/*. Iterator .*/ function getInnerIterator(){}
}

class LimitIterator
extends IteratorIterator
{
	/*. void .*/ function __construct(/*. Iterator .*/ $it /*., args .*/){parent::__construct($it);}
	/*. void .*/ function rewind(){}
	/*. bool .*/ function valid(){}
	/*. void .*/ function next(){}
	/*. void .*/ function seek(/*. int .*/ $position){}
	/*. int .*/ function getPosition(){}

	# FIXME: adding missing implementations:
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function key(){}
}

class CachingIterator
extends IteratorIterator
implements ArrayAccess, Countable
{
	const
		CALL_TOSTRING = 1,
		CATCH_GET_CHILD = 16,
		TOSTRING_USE_KEY = 2,
		TOSTRING_USE_CURRENT = 4,
		TOSTRING_USE_INNER = 8,
		FULL_CACHE = 256;

	/*. void .*/ function __construct(/*. Iterator .*/ $it /*., args .*/){parent::__construct($it);}
	/*. void .*/ function rewind(){}
	/*. bool .*/ function valid(){}
	/*. void .*/ function next(){}
	/*. bool .*/ function hasNext(){}
	/*. string .*/ function __toString()
		/*. throws BadMethodCallException .*/{}
	/*. mixed .*/ function getCache(){}
	/*. int .*/ function getFlags(){}
	/*. Iterator .*/ function getInnerIterator(){}

	# FIXME: adding missing implementations:
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function key(){}
	/*. int   .*/ function count(){}
	/*. bool  .*/ function offsetExists(/*. mixed .*/ $offset){}
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $offset){}
	/*. void  .*/ function offsetSet(/*. mixed .*/ $offset, /*. mixed .*/ $value){}
	/*. void  .*/ function offsetUnset(/*. mixed .*/ $offset){}
}

class RecursiveCachingIterator
extends CachingIterator
implements RecursiveIterator
{
	/*. bool .*/ function hasChildren(){}
	/*. RecursiveCachingIterator .*/ function getChildren(){}

	# FIXME: adding missing implementations:
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function key(){}
	/*. void  .*/ function next(){}
	/*. void  .*/ function rewind(){}
	/*. bool  .*/ function valid(){}
	/*. int   .*/ function count(){}
	/*. bool  .*/ function offsetExists(/*. mixed .*/ $offset){}
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $offset){}
	/*. void  .*/ function offsetSet(/*. mixed .*/ $offset, /*. mixed .*/ $value){}
	/*. void  .*/ function offsetUnset(/*. mixed .*/ $offset){}
}

class RegexIterator
extends FilterIterator
{
	/*. void .*/ function __construct(/*. Iterator .*/ $it, /*. string .*/ $regex /*. , args .*/){parent::__construct($it);}
	/*. bool .*/ function accept(){}
	/*. bool .*/ function getMode(){}
	/*. bool .*/ function setMode(/*. int .*/ $new_mode)
		/*. throws InvalidArgumentException .*/{}
	/*. bool .*/ function getFlags(){}
	/*. bool .*/ function setFlags(/*. int .*/ $new_flags){}
}

class RecursiveRegexIterator
extends RegexIterator
implements RecursiveIterator
{
	/*. void .*/ function __construct(/*. RecursiveIterator .*/ $it, /*. string .*/ $regex /*. , args .*/){parent::__construct($it, $regex);}
	/*. bool  .*/ function hasChildren(){}
	/*. RecursiveRegexIterator .*/ function getChildren(){}
}

# Commented-out because the 'simplexml' module is deprecated by PHPLint:
//class SimpleXMLIterator
//extends SimpleXMLElement
//implements RecursiveIterator, Countable
//{
//	/*. mixed .*/ function current(){}
//	/*. SimpleXMLIterator .*/ function getChildren(){}
//	/*. bool .*/ function hasChildren(){}
//	/*. mixed .*/ function key(){}
//	/*. void .*/ function next(){}
//	/*. void .*/ function rewind(){}
//	/*. bool .*/ function valid(){}
//
//	# FIXME: adding missing implementations:
//	/*. int   .*/ function count(){}
//}


class SplDoublyLinkedList
implements Iterator, ArrayAccess, Countable
{
	/*. void .*/ function __construct(){}
	/*. mixed .*/ function bottom(){}
	/*. int .*/ function count(){}
	/*. mixed .*/ function current(){}
	/*. int .*/ function getIteratorMode(){}
	/*. bool .*/ function isEmpty(){}
	/*. mixed .*/ function key(){}
	/*. void .*/ function next(){}
	/*. bool .*/ function offsetExists(/*. mixed .*/ $index){}
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $index){}
	/*. void .*/ function offsetSet(/*. mixed .*/ $index, /*. mixed .*/ $newval){}
	/*. void .*/ function offsetUnset(/*. mixed .*/ $index){}
	/*. mixed .*/ function pop(){}
	/*. void .*/ function push(/*. mixed .*/ $value){}
	/*. void .*/ function rewind(){}
	/*. void .*/ function setIteratorMode(/*. int .*/ $mode)
		/*. throws RuntimeException .*/ {}
	/*. mixed .*/ function shift(){}
	/*. mixed .*/ function top(){}
	/*. void .*/ function unshift(/*. mixed .*/ $value){}
	/*. bool .*/ function valid(){}
}


class SplStack
extends SplDoublyLinkedList
{
	/*. void .*/ function __construct(){ parent::__construct(); }
	/*. void .*/ function setIteratorMode(/*. int .*/ $mode)
		/*. throws RuntimeException .*/ {}
}


class SplQueue
extends SplDoublyLinkedList
{
	/*. void .*/ function __construct(){ parent::__construct(); }
	/*. mixed .*/ function dequeue(){}
	/*. void .*/ function enqueue(/*. mixed .*/ $value){}
	/*. void .*/ function setIteratorMode(/*. int .*/ $mode)
		/*. throws RuntimeException .*/ {}
}


abstract class SplHeap
implements Iterator, Countable
{
	/*. void .*/ function __construct(){}
	abstract /*. int .*/ function compare(/*. mixed .*/ $value1, /*. mixed .*/ $value2);
	/*. int .*/ function count(){}
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function extract(){}
	/*. void .*/ function insert(/*. mixed .*/ $value){}
	/*. bool .*/ function isEmpty(){}
	/*. mixed .*/ function key(){}
	/*. void .*/ function next(){}
	/*. void .*/ function recoverFromCorruption(){}
	/*. void .*/ function rewind(){}
	/*. mixed .*/ function top(){}
	/*. bool .*/ function valid(){}
}


class SplMaxHeap
extends SplHeap
{
	/*. int .*/ function compare(/*. mixed .*/ $value1, /*. mixed .*/ $value2){}
}


class SplMinHeap
extends SplHeap
{
	/*. int .*/ function compare(/*. mixed .*/ $value1, /*. mixed .*/ $value2){}
}


class SplPriorityQueue
implements Iterator, Countable
{
	/*. void .*/ function __construct(){}
	/*. void .*/ function compare( /*. mixed .*/ $priority1 , /*. mixed .*/ $priority2 ){}
	/*. int .*/ function count(){}
	/*. mixed .*/ function current(){}
	/*. mixed .*/ function extract(){}
	/*. void .*/ function insert( /*. mixed .*/ $value , /*. mixed .*/ $priority ){}
	/*. bool .*/ function isEmpty(){}
	/*. mixed .*/ function key(){}
	/*. void .*/ function next(){}
	/*. void .*/ function recoverFromCorruption(){}
	/*. void .*/ function rewind(){}
	/*. void .*/ function setExtractFlags( /*. int .*/ $flags ){}
	/*. mixed .*/ function top(){}
	/*. bool .*/ function valid(){}
}


/**
WARNING: PHPLint is very strict in checking method signature, and the int type
does not match the overridden type mixed, so I had to change int into mixed here
and there. Involved methods are: key, offsetExists, offsetGet, offsetSet,
offsetUnset.
*/
class SplFixedArray
implements Iterator, ArrayAccess, Countable
{
	/*. void .*/ function __construct(/*. int .*/ $size){}
	/*. int .*/ function count(){}
	/*. mixed .*/ function current(){}
	static /*. SplFixedArray .*/ function fromArray(
		/*. array .*/ $array_,
		/*. boolean .*/ $save_indexes = TRUE){}
	/*. int .*/ function getSize(){}
	#/*. int .*/ function key(){}
	/*. mixed .*/ function key(){}
	/*. void .*/ function next(){}
	#/*. bool .*/ function offsetExists(/*. int .*/ $index){}
	/*. bool .*/ function offsetExists(/*. mixed .*/ $index){}
	#/*. mixed .*/ function offsetGet(/*. int .*/ $index){}
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $index){}
	#/*. void .*/ function offsetSet(/*. int .*/ $index, /*. mixed .*/ $newval){}
	/*. void .*/ function offsetSet(/*. mixed .*/ $index, /*. mixed .*/ $newval){}
	#/*. void .*/ function offsetUnset(/*. int .*/ $index){}
	/*. void .*/ function offsetUnset(/*. mixed .*/ $index){}
	/*. void .*/ function rewind(){}
	/*. int .*/ function setSize(/*. int .*/ $size){}
	/*. array[int]mixed .*/ function toArray(){}
	/*. bool .*/ function valid(){}
}


class SplObjectStorage
implements Countable, Iterator, Serializable, ArrayAccess {
	/*. void .*/ function addAll(/*. SplObjectStorage .*/ $storage){}
	/*.bool.*/ function contains(/*. object .*/ $obj){}
	/*.void.*/ function attach(
		/*. object .*/ $obj,
		/*. mixed .*/ $data = NULL){}
	/*.void.*/ function detach(/*. object .*/ $obj){}
	/*.int.*/ function count(){}
	/*. object .*/ function current(){}
	/*. mixed .*/ function key(){}
	/*. void  .*/ function next(){}
	/*. void  .*/ function rewind(){}
	/*. bool  .*/ function valid(){}
	// $object_ MUST be mixed to comply with interface:
	/*. boolean .*/ function offsetExists(/*. mixed .*/ $object_){}
	// $object_ MUST be mixed to comply with interface:
	/*. mixed .*/ function offsetGet(/*. mixed .*/ $object_){}
	// $object_ MUST be mixed to comply with interface:
	/*. void .*/ function offsetSet(/*. mixed .*/ $object_, /*. mixed .*/ $info){}
	// $object_ MUST be mixed to comply with interface:
	/*. void .*/ function offsetUnset(/*. mixed .*/ $object_){}
	/*. void .*/ function removeAll(/*. SplObjectStorage .*/ $storage){}
	/*. string.*/ function serialize(){}
	/*. void .*/ function unserialize(/*. string .*/ $serialized){}
	/*. void .*/ function setInfo (/*. mixed .*/ $data ){}
	/*. mixed .*/ function getInfo(){}
}

class GlobIterator
	extends FilesystemIterator
	implements Countable
{
	/*. void .*/ function __construct(/*. string .*/ $path, $flags = 0)
	{
		parent::__construct($path, $flags);
	}
	/*. int .*/ function count(){}
}


/*. array[int]string .*/ function class_implements(/*. mixed .*/ $class_, $autoload=TRUE){}
/*. array[int]string .*/ function class_parents(/*. mixed .*/ $class_, $autoload=TRUE){}
/*. int .*/ function iterator_apply(Traversable $iterator, /*. mixed .*/ $callback, $params=/*.(array[int]mixed).*/array()){}
/*. int .*/ function iterator_count(Traversable $iterator){}
/*. array .*/ function iterator_to_array(Traversable $iterator, /*. bool .*/ $use_keys=TRUE){}
/*. void .*/ function spl_autoload_call(/*. string .*/ $class_name)
	/*. throws LogicException .*/{}
/*. string .*/ function spl_autoload_extensions(/*. string .*/ $file_extensions=NULL){}
/*. array[int]string .*/ function spl_autoload_functions(){}
/*. bool .*/ function spl_autoload_register(/*. mixed .*/ $autoload_function=NULL, $throw_=TRUE, $prepend=FALSE)
	/*. throws LogicException .*/{}
/*. bool .*/ function spl_autoload_unregister(/*. mixed .*/ $autoload_function ){}
/*. void .*/ function spl_autoload(/*. string .*/ $class_name, /*. string .*/ $file_extensions=NULL){}
/*. array[string]string .*/ function spl_classes(){}
/*. string .*/ function spl_object_hash(/*. object .*/ $obj){}
