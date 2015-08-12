<?php

namespace it\icosaedro\lint\docblock;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\Logger;
use it\icosaedro\lint\Where;
use it\icosaedro\lint\docblock\DocBlock;

/**
 * DocBlock wrapper for semplified access to properties from PHPLint.
 * Also handles missing DocBlock and missing line tag, providing accessor
 * methods that return the default value.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/01/30 12:26:30 $
 */
class DocBlockWrapper {
	
	/*. private .*/ const
		ABSTRACT_TAG = 1,
		ACCESS_TAG = 2,
		FINAL_TAG = 4,
		PACKAGE_TAG = 8,
		PARAM_TAG = 16,
		RETURN_TAG = 32,
		STATIC_TAG = 64,
		THROWS_TAG = 128,
		TRIGGERS_TAG = 256,
		VAR_TAG = 512;
	
	/**
	 * @var Logger
	 */
	private $logger;
	
	/**
	 * @var Where 
	 */
	private $where;

	/**
	 * @var DocBlock
	 */
	private $db;
	
	private $is_php4 = FALSE;

	/**
	 * Initializes new DocBlock wrapper.
	 * @param Logger $logger
	 * @param DocBlock $db DocBlock to wrap, or NULL if not available.
	 * @param boolean $is_php4
	 * @return void
	 */
	public function __construct($logger, $db = NULL, $is_php4 = FALSE) {
		$this->logger = $logger;
		if( $db == NULL )
			$this->where = Where::getNowhere();
		else
			$this->where = $db->decl_in;
		$this->db = $db;
		$this->is_php4 = $is_php4;
	}
	
	
	/**
	 * @return void
	 */
	public function clear(){
		$this->where = Where::getNowhere();
		$this->db = NULL;
	}
	
	
	/**
	 * Returns the wrapped DocBlock, possibly NULL if missing.
	 * @return DocBlock 
	 */
	public function getDocBlock()
	{
		return $this->db;
	}
	
	
	/**
	 *
	 * @param string $tag
	 * @param boolean $found
	 * @param boolean $allowed 
	 * @return void
	 */
	private function checkLineTag($tag, $found, $allowed)
	{
		if( $found && ! $allowed )
			$this->logger->error($this->where,
				"unexpected DocBlock line tag `$tag'");
	}
	
	
	/**
	 *
	 * @param int $allowed_set 
	 * @return void
	 */
	private function checkLineTags($allowed_set)
	{
		$db = $this->db;
		if( $db === NULL )
			return;
		
		$this->checkLineTag("@abstract",
			$db->is_abstract,
			($allowed_set & self::ABSTRACT_TAG) != 0);
			
		$this->checkLineTag("@access",
			$db->is_public || $db->is_protected || $db->is_private,
			($allowed_set & self::ACCESS_TAG) != 0);
		
		$this->checkLineTag("@final",
			$db->is_final,
			($allowed_set & self::FINAL_TAG) != 0);
		
		$this->checkLineTag("@package",
			$db->is_final,
			($allowed_set & self::PACKAGE_TAG) != 0);
		
		$this->checkLineTag("@param",
			$db->is_final,
			($allowed_set & self::PARAM_TAG) != 0);
		
		$this->checkLineTag("@return",
			$db->is_final,
			($allowed_set & self::RETURN_TAG) != 0);
		
		$this->checkLineTag("@static",
			$db->is_final,
			($allowed_set & self::STATIC_TAG) != 0);
		
		$this->checkLineTag("@throws",
			$db->is_final,
			($allowed_set & self::THROWS_TAG) != 0);
		
		$this->checkLineTag("@var",
			$db->is_final,
			($allowed_set & self::VAR_TAG) != 0);
		
		$this->checkLineTag("@triggers",
			$db->is_final,
			($allowed_set & self::TRIGGERS_TAG) != 0);
	}
	
	
	/**
	 * Reports error for line tags forbidden in package's DocBlock.
	 * @return void
	 */
	public function checkLineTagsForPackage()
	{
		if( $this->db === NULL )
			return;
		$allowed_set = self::PACKAGE_TAG;
		$this->checkLineTags($allowed_set);
		if( $this->db->package_word === NULL )
			throw new \RuntimeException("missing @package");
	}
	
	/**
	 * Reports error for line tags forbidden in constant's DocBlock.
	 * @return void
	 */
	public function checkLineTagsForConstant()
	{
		if( $this->db === NULL )
			return;
		$allowed_set = self::ACCESS_TAG;
		$this->checkLineTags($allowed_set);
		if( $this->db->is_protected )
			$this->logger->error($this->where,
			"invalid line tag `@access protected': only public or private allowed");
	}
	
	
	/**
	 * Reports error for line tags forbidden in variable's DocBlock.
	 * @return void
	 */
	public function checkLineTagsForVariable()
	{
		if( $this->db === NULL )
			return;
		$allowed_set = self::ACCESS_TAG | self::VAR_TAG;
		$this->checkLineTags($allowed_set);
		if( $this->db->is_protected )
			$this->logger->error($this->where,
			"invalid line tag `@access protected': only public or private allowed");
	}
	
	
	/**
	 * Reports error for line tags forbidden in function's DocBlock.
	 * @return void
	 */
	public function checkLineTagsForFunction()
	{
		if( $this->db === NULL )
			return;
		$allowed_set = self::ACCESS_TAG | self::PARAM_TAG | self::RETURN_TAG
			| self::THROWS_TAG | self::TRIGGERS_TAG;
		$this->checkLineTags($allowed_set);
		if( $this->db->is_protected )
			$this->logger->error($this->where,
			"invalid line tag `@access protected': only public or private allowed");
	}
	
	
	/**
	 * Reports error for line tags forbidden in class and interface DocBlock.
	 * @return void
	 */
	public function checkLineTagsForClass()
	{
		if( $this->db === NULL )
			return;
		$allowed_set = self::ACCESS_TAG | self::PARAM_TAG | self::RETURN_TAG
			| self::THROWS_TAG | self::TRIGGERS_TAG;
		if( $this->is_php4 )
			$allowed_set |= self::ABSTRACT_TAG | self::FINAL_TAG;
		$this->checkLineTags($allowed_set);
		if( $this->db->is_protected )
			$this->logger->error($this->where,
			"invalid line tag `@access protected': only public or private allowed");
	}
	
	
	/**
	 * Reports error for line tags forbidden in class constant's DocBlock.
	 * @return void
	 */
	public function checkLineTagsForClassConstant()
	{
		if( $this->db === NULL )
			return;
		$allowed_set = self::ACCESS_TAG;
		$this->checkLineTags($allowed_set);
		if( $this->db->is_protected )
			$this->logger->error($this->where,
			"invalid line tag `@access protected': only public or private allowed");
	}
	
	
	/**
	 * Reports error for line tags forbidden in property's DocBlock.
	 * @return void
	 */
	public function checkLineTagsForProperty()
	{
		$allowed_set = self::VAR_TAG;
		if( $this->is_php4 )
			$allowed_set |= self::ACCESS_TAG;
		$this->checkLineTags($allowed_set);
	}
	
	
	/**
	 * Reports error for line tags forbidden in method's DocBlock.
	 * @return void
	 */
	public function checkLineTagsForMethod()
	{
		$allowed_set = self::PARAM_TAG | self::RETURN_TAG | self::TRIGGERS_TAG;
		if( $this->is_php4 )
			$allowed_set |= self::ABSTRACT_TAG | self::ACCESS_TAG
			| self::FINAL_TAG | self::STATIC_TAG;
		else /* PHP5 */
			$allowed_set |= self::THROWS_TAG;
		$this->checkLineTags($allowed_set);
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isAbstract(){
		return $this->db !== NULL && $this->db->is_abstract;
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isPrivate(){
		return $this->db !== NULL && $this->db->is_private;
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isProtected(){
		return $this->db !== NULL && $this->db->is_protected;
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isPublic(){
		return $this->db !== NULL && $this->db->is_public;
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isDeprecated(){
		return $this->db !== NULL && $this->db->deprecated_descr !== NULL;
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isFinal(){
		return $this->db !== NULL && $this->db->is_final;
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isPackage(){
		return $this->db !== NULL && $this->db->package_word !== NULL;
	}
	
	
	/**
	 * @return boolean 
	 */
	public function isStatic(){
		return $this->db !== NULL && $this->db->is_static;
	}
	
	
	/**
	 *
	 * @return Type 
	 */
	public function getVarType()
	{
		if( $this->db !== NULL )
			return $this->db->var_type;
		else
			return NULL;
	}
	
	
	/**
	 *
	 * @return Type 
	 */
	public function getReturnType()
	{
		if( $this->db !== NULL )
			return $this->db->return_type;
		else
			return NULL;
	}
	
	
	/**
	 * Returns the index or the formal argument with the given name.
	 * @param string $name Name of the formal argument.
	 * @return int Index of the formal argument, or -1 if not found.
	 */
	public function getParamIndex($name)
	{
		if( $this->db === NULL || $this->db->params_names === NULL )
			return -1;
		for($i = count($this->db->params_names) - 1; $i >= 0; $i--)
			if( $this->db->params_names[$i] === $name )
				return $i;
		return -1;
	}

}
