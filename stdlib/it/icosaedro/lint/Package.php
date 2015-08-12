<?php

namespace it\icosaedro\lint;
use it\icosaedro\io\File;
use it\icosaedro\io\InputStream;
use it\icosaedro\io\IOException;
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\lint\docblock\DocBlock;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use CastException;

require_once __DIR__ . "/../../../all.php";

/**
 * Holds an instance of a package. A package, in
 * the PHPLint terminology, is simply a single PHP source file or a module.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/21 13:08:48 $
 */
class Package implements Printable, Sortable {
	
	const NAME = __CLASS__;

	/**
	 * Scanner for this source file.
	 * @var Scanner 
	 */
	public $scanner;
	
	/**
	 * File of this package.
	 * @var File
	 */
	public $fn;
	
	/**
	 * @var NamespaceResolver
	 */
	public $resolver;

	/**
	 * Is it a PHP extension module? Modules, also known as
	 * extensions in PHP terminology, have their own definition under PHPLint
	 * through a module file that specify the resources provided by that
	 * extension. These module files are quite similar to regular PHP code, but
	 * not exactly valid PHP sources, so PHPLint must be more tolerant while
	 * parsing them. Moreover, modules must be reported differently in the
	 * generated documents.
	 * @var boolean
	 */
	public $is_module = FALSE;

	/**
	 * Is it a library? A library is a package that can be safely required by
	 * other packages; if included with <code>require_once</code>, it is an
	 * error. See the <code>notLibrary()</code> method for more.
	 * @var boolean
	 */
	public $is_library = FALSE;

	/**
	 * If not a library, here is the decription of the cause detected.
	 * See the <code>notLibrary()</code> method for a detailed list of the
	 * conditions that make a package a (non) library.
	 * Simple text, not HTML.
	 * @var string
	 */
	public $why_not_library;

	/**
	 * How many times one or more of the items exported has been
	 * used by other packages.
	 * @var int
	 */
	public $used = 0;

	/**
	 * Documentation block for this package.
	 * @var DocBlock
	 */
	public $docblock;

	/**
	 * Scope is incremented entering a function/method, and decremented on
	 * exit. Functions may be nested, but PHP actually supports only 2 scopes:
	 * global and local. So, if scope=0 we are in the global scope,
	 * else if scope&gt;0 we are in the local scope.
	 * @var int
	 */
	public $scope = 0;

	/**
	 * Nesting level of for(), foreach(), while(), do...while(),
	 * switch(){}. Needed to check 'break' and 'continue' usage.
	 * @var int
	 */
	public $loop_level = 0;
	
	/**
	 * Error silencer operator `@' deep level. This value gets incremented
	 * entering the range of effectiveness of the @ operator and decremented
	 * exiting from it range. If the level is zero, errors are triggered
	 * normally and missing error handling is signaled.
	 * @var int 
	 */
	public $silencer_level = 0;

	/**
	 * Nesting level of the try/catch statement, incremented entering "try{}"
	 * block. If positive, we are inside a try/catch so exceptions are
	 * collected in the $exceptions property and no error is signaled for
	 * unhandled exception.
	 * @var int
	 */
	public $try_block_nesting_level = 0;

	/**
	 * Exceptions thrown inside a "try{}" block are collected here.
	 * @var ExceptionsSet
	 */
	public $exceptions;
	
	/**
	 * Last DocBlock found while scanning current package. NOT the packages's
	 * DocBlock.
	 * @var DocBlockWrapper
	 */
	public $curr_docblock;

	/**
	 * The function we are parsing right now. NULL = not inside a function.
	 * @var Function_
	 */
	public $curr_func;

	/**
	 * The class we are parsing right now. NULL = not inside a class.
	 * @var ClassType
	 */
	public $curr_class;

	/**
	 * The method we are parsing right now. NULL = not inside a method.
	 * @var ClassMethod
	 */
	public $curr_method;
	
	/**
	 * If the next "sym_x_else" mata-code statement must be skipped.
	 * Modules may contain both PHP4 and PHP5 code surrounded by some
	 * special PHPLint meta-code statements "sym_x_if_php_ver_4",
	 * "sym_x_if_php_ver_5", "sym_x_else" and finally "sym_x_end_if_php_ver",
	 * so the same module may declare different things depending on the
	 * current PHP version required.
	 * @var boolean 
	 */
	public $skip_else_php_ver = FALSE;
	
	/**
	 * Context of the parsing.
	 * @var Globals 
	 */
	private $globals;
	
	
	/**
	 * @return string Name of this package as absolute path.
	 */
	public function __toString()
	{
		return $this->fn->__toString();
	}
	
	/**
	 * @param object $other 
	 * @return boolean
	 */
	function equals($other)
	{
		if( $other === NULL )
			return FALSE;
		
		# Fast, easy test:
		if( $this === $other )
			return TRUE;

		# If they belong to different classes, cannot be
		# equal, also if the 2 classes are relatives:
		if( get_class($other) !== __CLASS__ )
			return FALSE;
		
		$other2 = cast(__CLASS__, $other);
		return $this->fn->equals($other2->fn);
	}
	
	
	/**
	 *
	 * @param object $other
	 * @return int
	 * @throws CastException 
	 */
	public function compareTo($other)
	{
		if( $other === NULL )
			throw new CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new CastException("expected " . __CLASS__
			. " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		return $this->fn->compareTo($other2->fn);
	}
	

	/**
	 * Increments the "error silencer" operator deep level.
	 * The parser calls this function when encounters the "@" operator.
	 * @return void
	 */
	public function enteringSilencer()
	{
		$this->silencer_level++;
		if( $this->globals->error_throws_exception !== NULL )
			$this->globals->logger->error($this->scanner->here(),
			"errors mapping into exception is in effect, the silencer operator `@' cannot be used anymore");
	}

	
	/**
	 * Decrements the "error silencer" operator deep level.
	 * The parser calls this function when terminetes the "@" operator.
	 * @return void
	 */
	public function exitingSilencer()
	{
		$this->silencer_level--;
		if( $this->silencer_level < 0 )
			throw new \RuntimeException("unbalanced silencer operator `@' deep level counter");
	}
	

	/**
	 * Marks this package as a library. When the parser detects any of the
	 * conditions that follows, this method is called to account for the reason:
	 * <ul>
	 * <li>file contains leading text</li>
	 * <li><code>&lt;?=</code> at global scope (allowed in local scope, though)</li>
	 * <li>raises error in code at global scope</li>
	 * <li>throws unchecked exception at global scope</li>
	 * <li>other (see usages of this method for a complete list)</li>
	 * </ul>
	 * @param string $descr Describes why this package cannot be a library.
	 * @return void
	 */
	public function notLibrary($descr) {
		$this->is_library = FALSE;
		if( $this->why_not_library === NULL )
			$this->why_not_library = $descr;
		else
			$this->why_not_library .= "\n$descr";
	}
	
	
	/**
	 * Creates a new, empty package.
	 * @param Globals $globals
	 * @param File $fn
	 * @param InputStream $f
	 * @param boolean $is_module Is it a module? Modules are much like empty
	 * prototypes of the PHP's built-in entities and extensions.
	 * @return void
	 * @throws IOException
	 */
	public function __construct($globals, $fn, $f, $is_module){
		$this->globals = $globals;
		$this->fn = $fn;
		$this->is_module = $is_module;
		$this->is_library = TRUE;
		$this->exceptions = new ExceptionsSet();
		$this->resolver = new NamespaceResolver();
		$this->scanner = new Scanner($globals->logger, $fn, $f, $globals->php_ver);
		$this->curr_docblock = new DocBlockWrapper($globals->logger, NULL, FALSE);
	}
	
}
