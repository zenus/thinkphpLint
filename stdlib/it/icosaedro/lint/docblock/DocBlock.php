<?php

namespace it\icosaedro\lint\docblock;
require_once __DIR__ . "/../../../../autoload.php";
use it\icosaedro\containers\Printable;
use it\icosaedro\utils\StringBuffer;
use it\icosaedro\lint\Where;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\ClassType;

/**
 * Holds the content of a DocBlock.
 * Missing tag lines keeps their default value, that is NULL or FALSE depending
 * on the type. So, for example, if <code>$package_word !== NULL</code> then
 * this DocBlock is referring to a package.
 * 
 * Supported line tags for which a specific detailed parsing is performed, are:
 * 
 * <blockquote>
 * <code><b>@abstract</b></code><br>
 * <code><b>@access private</b></code><br>
 * <code><b>@access protected</b></code> (PHP 4 only)<br>
 * <code><b>@access public</b></code> (PHP 4 only)<br>
 * <code><b>@deprecated</b> <i>DESCR</i></code><br>
 * <code><b>@final</b></code> (PHP 4 only)<br>
 * <code><b>@package</b> <i>WORD</i></code><br>
 * <code><b>@param</b> <i>TYPE</i> [&amp;] $<i>VAR</i></code><br>
 * <code><b>@static</b></code> (PHP 4 only)<br>
 * <code><b>@throws</b> <i>TYPE</i> <i>DESCR</i></code> (PHP 5 only)<br>
 * <code><b>@triggers</b> <i>WORD</i> <i>DESCR</i></code><br>
 * <code><b>@var</b> <i>TYPE</i></code><br>
 * </blockquote>
 * 
 * Here <code><i>DESCR</i></code> is any descriptive text HTML encoded.
 * HTML elements and their proper nesting is accurately checked and errors
 * signaled.
 * <br>
 * <code><i>TEXT</i></code> is any textual content extendind up to the next
 * line tag or to the end of the DocBlock.
 * <br>
 * <code><i>TYPE</i></code> is a type descriptor as explained in detail
 * in the {@link ../types/TypeDescriptor.html} class.
 * 
 * Others known line tags that are collected but not otherwise handled by
 * this class, are:
 * 
 * <blockquote>
 * <code><b>@author</b> <i>TEXT</i></code><br>
 * <code><b>@copyright</b> <i>TEXT</i></code><br>
 * <code><b>@global</b> <i>TEXT</i></code><br>
 * <code><b>@license</b> <i>TEXT</i></code><br>
 * <code><b>@link</b> <i>TEXT</i></code><br>
 * <code><b>@see</b> <i>TEXT</i></code><br>
 * <code><b>@since</b> <i>TEXT</i></code><br>
 * <code><b>@todo</b> <i>TEXT</i></code><br>
 * <code><b>@version</b> <i>TEXT</i></code><br>
 * </blockquote>
 * 
 * Any other unknown line tag is signaled as error, but still it is collected.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/09/14 10:14:39 $
 */
class DocBlock implements Printable {
	
	/**
	 * Location of the DocBlock, last line.
	 * @var Where 
	 */
	public $decl_in;

	/**
	 * Short description, HTML text
	 * @var string
	 */
	public $short_descr;

	/**
	 * Long description, HTML text.
	 * @var string
	 */
	public $long_descr;
	
	/**
	 * "@deprecated DESCR", HTML text.
	 * @var string 
	 */
	public $deprecated_descr;

	/**
	 * "@package WORD" name. NULL if not a package.
	 * @var string
	 */
	public $package_word;

	/**
	 * "@var TYPE" type.
	 * @var Type
	 */
	public $var_type;

	/**
	 * "@abstract" (PHP 4)
	 * @var boolean
	 */
	public $is_abstract = FALSE;

	/**
	 * "@final" (PHP 4)
	 * @var boolean
	 */
	public $is_final = FALSE;

	/**
	 * "@static" (PHP 4)
	 * @var boolean
	 */
	public $is_static = FALSE;

	/**
	 * "@access private" (PHP 4; PHP 5 only for constant, variable, function,
	 * class, interface and class constant).
	 * @var boolean
	 */
	public $is_private = FALSE;

	/**
	 * "@access protected" (PHP 4)
	 * @var boolean
	 */
	public $is_protected = FALSE;

	/**
	 * "@access public" (PHP 4)
	 * @var boolean
	 */
	public $is_public = FALSE;

	/**
	 * "@param TYPE [&amp;] &amp;VAR DESCR" types (in order of declaration).
	 * @var Type[int]
	 */
	public $params_types;

	/**
	 * "@param TYPE [&amp;] &amp;VAR DESCR" by-reference flag (in order of
	 * declaration).
	 * @var boolean[int]
	 */
	public $params_byref;

	/**
	 * "@param TYPE NAME" [&amp;] &amp;VAR DESCR (in order of declaration, no
	 * repetitions). The dollar sign is removed.
	 * @var string[int]
	 */
	public $params_names;

	/**
	 * "@param TYPE NAME" descriptions in order of declaration, HTML text.
	 * @var string[int]
	 */
	public $params_descrs;

	/**
	 * "@return TYPE DESCR" type.
	 * @var Type
	 */
	public $return_type;

	/**
	 * "@return TYPE DESCR" description, HTML text.
	 * @var string
	 */
	public $return_descr;
	
	/**
	 * "@triggers ERR DESCR" error names.
	 * @var string[int]
	 */
	public $triggers_names;
	
	/**
	 * "@triggers ERR DESCR" error descriptions.
	 * @var string[int]
	 */
	public $triggers_descrs;

	/**
	 * "@throws TYPE DESCR" exceptions. Contains only valid exception classes
	 * without duplicates.
	 * @var ClassType[int]
	 */
	public $throws_exceptions;

	/**
	 * "@throws TYPE DESCR" descriptions, HTML text.
	 * @var string[int]
	 */
	public $throws_descrs;
	
	/**
	 * Other and unknown line tags here. For every line tag two entries
	 * are added to the array: the first one is the line tag name (example:
	 * "@since"), the second one is the content (example: "1.1.0"), so that
	 * even entries (0, 2, 4, ...) are the line tag names, the odd entries
	 * are the descriptions.
	 * string[int]
	 */
	public $others = /*. (string[int]) .*/ array();
	
	
	/**
	 *
	 * @param string $s
	 * @return string 
	 */
	private static function wrap($s){
		return (string) str_replace("\n", "\n * ", $s);
	}
	
	
	/**
	 * Returns a restored DocBlock, aligned to the left.
	 * Line endings are "\n".
	 * @return string Restored DocBlock encoded as ASCII source code.
	 */
	public function __toString(){
		$r = new StringBuffer();
		$r->append("/**\n");
		
		if( $this->short_descr !== NULL )
			$r->append(" * " . self::wrap($this->short_descr) . "\n");
		
		if( $this->long_descr !== NULL )
			$r->append(" * " . self::wrap($this->long_descr) . "\n");
		
		if( $this->deprecated_descr !== NULL )
			$r->append(" * @deprecated " . self::wrap($this->deprecated_descr) . "\n");
		
		if( $this->package_word !== NULL )
			$r->append(" * @package " . $this->package_word . "\n");
		
		if( $this->is_abstract )
			$r->append(" * @abstract\n");
		
		if( $this->is_final )
			$r->append(" * @final\n");
		
		if( $this->is_static )
			$r->append(" * @static\n");
		
		if( $this->is_private )
			$r->append(" * @access private\n");
		
		if( $this->is_protected )
			$r->append(" * @access protected\n");
		
		if( $this->is_public )
			$r->append(" * @access public\n");
		
		for($i = 0; $i < count($this->params_types); $i++){
			$t = $this->params_types[$i];
			$byref = $this->params_byref[$i]? "& " : "";
			$n = $this->params_names[$i];
			$d = $this->params_descrs[$i];
			$r->append(" * @param $t $byref\$$n " . self::wrap($d) . "\n");
		}
		
		if( $this->return_type !== NULL )
			$r->append(" * @return " . $this->return_type . " "
				. self::wrap($this->return_descr) . "\n");
		
		for($i = 0; $i < count($this->triggers_names); $i++){
			$n = $this->triggers_names[$i];
			$d = $this->triggers_descrs[$i];
			$r->append(" * @triggers $n " . self::wrap($d) . "\n");
		}
		
		for($i = 0; $i < count($this->throws_exceptions); $i++){
			$t = $this->throws_exceptions[$i];
			$d = $this->throws_descrs[$i];
			$r->append(" * @throws $t " . self::wrap($d) . "\n");
		
		if( $this->var_type !== NULL )
			$r->append(" * @var " . $this->var_type . "\n");
		}
		
		for($i = 0; $i < count($this->others); $i += 2){
			$r->append(" * " . $this->others[$i] ." "
				. self::wrap($this->others[$i + 1]) ."\n");
		}
		
		$r->append(" */\n");
		return $r->__toString();
	}
	
}