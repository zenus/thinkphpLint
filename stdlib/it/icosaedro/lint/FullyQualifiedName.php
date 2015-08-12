<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";
use it\icosaedro\containers\Printable;
use it\icosaedro\containers\Comparable;
use it\icosaedro\containers\Sortable;
use it\icosaedro\containers\Hashable;
use it\icosaedro\containers\Hash;
use RuntimeException;
use CastException;

/**
 * Holds a fully qualified name of a constant, a function or a class. Also
 * provides methods to compare and sort these names that are specific of each
 * entity. In fact, constant names are case-insensitive only in their
 * base name part, while functions and classes are fully case-insensitive.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/03/04 12:01:38 $
 */
class FullyQualifiedName implements Sortable, Printable, Hashable {
	
	/**
	 * Namespace to which this item belongs, saved here in its original form.
	 * @var string 
	 */
	private $ns;
	
	/**
	 * Base name of the item that, joined with the NS, gives the FQN; saved
	 * here in its original form.
	 * @var string 
	 */
	private $name;
	
	/**
	 * Fully qualified name of the item, in its original form.
	 * @var string 
	 */
	private $fqn;
	
	/**
	 * Normalized name. For constants, the NS part is made lowercase, the name
	 * part is left untouched. For functions and classes, is all lowercase
	 * letters.
	 * @var string 
	 */
	private $fqn_normalized;
	
	/**
	 * @var int
	 */
	private $hash = 0;
	
	
	/**
	 *
	 * @param string $fqn
	 * @param boolean $is_constant Is it a constant? From this flag depends the
	 * internal normalization of the name, because the name part of a constant
	 * is case-sensitive; for functions and classes the whole name is
	 * case-insensitive.
	 * @return void
	 * @throws RuntimeException Does not looks like a FQN.
	 */
	public function __construct($fqn, $is_constant){
		if( strlen($fqn) < 1 )
			throw new RuntimeException("not a FQN: empty string");
		if( strlen($fqn) >= 2 && $fqn[0] === "\\" )
			$fqn = substr($fqn, 1);
		$back_slash = strrpos($fqn, "\\");
		if( $back_slash === FALSE ){
			$this->ns = "";
			$this->name = $fqn;
			$this->fqn = $fqn;
			if( $is_constant )
				$this->fqn_normalized = $fqn;
			else
				$this->fqn_normalized = strtolower($fqn);
		} else {
			$this->ns = substr($fqn, 0, $back_slash);
			$this->name = substr($fqn, $back_slash + 1);
			$this->fqn = $fqn;
			if( $is_constant )
				$this->fqn_normalized = strtolower($this->ns) ."\\". $this->name;
			else
				$this->fqn_normalized = strtolower($fqn);
		}
	}
	
	
	/**
	 * Returns the namespace part of the item as originally defined.
	 * @return string 
	 */
	public function getNamespace(){
		return $this->ns;
	}
	
	
	/**
	 * Returns the FQN of the item as originally defined in the source.
	 * @return string 
	 */
	public function getName(){
		return $this->name;
	}
	
	
	/**
	 * Returns the FQN of the item. For constants, the namespace part of the
	 * FQN is all lowercase letters. For functions and classes it is all
	 * lowercase letters.
	 * @return string 
	 */
	public function getFullyQualifiedName(){
		return $this->fqn;
	}
	
	
	/**
	 * Returns the FQN of the item. For constants, the namespace part of the
	 * FQN is all lowercase letters. For functions and classes it is all
	 * lowercase letters.
	 * @return string 
	 */
	public function __toString(){
		return $this->fqn;
	}
	
	
	/**
	 * Compare this name with another for strict equality, case-sensitive.
	 * This method allows to detect names that looks equals to PHP but are not
	 * exactly the same; detecting such typos is not only a question programming
	 * style, but also a safety check when namespaces are mapped into
	 * case-sensitive file systems.
	 * @param FullyQualifiedName $other
	 * @return boolean True if $other is exactly equals to this.
	 */
	public function equalsCaseSensitive($other){
		return $this === $other
		|| $this->fqn === $other->fqn;
	}
	
	
	/**
	 * Case-insensitive comparison for equality. For constants, the name part
	 * is still case-sensitive.
	 * @param object $other
	 * @return boolean 
	 */
	public function equals($other){
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
		
		# Here, comparison specific of the class, field by field.
		# See also the Equality::areEqual($a,$b) method. Example:

		return $this->fqn_normalized === $other2->fqn_normalized;
	}
	
	
	/**
	 * Case-insensitive comparison.
	 * @param object $other
	 * @return int
	 * @throws CastException 
	 */
	function compareTo($other){
		if( $other === NULL )
			throw new CastException("NULL");
		if( get_class($other) !== __CLASS__ )
			throw new CastException("expected " . __CLASS__
			. " but got " . get_class($other));
		$other2 = cast(__CLASS__, $other);
		return strcmp($this->fqn_normalized, $other2->fqn_normalized);
	}
	
	
	/**
	 * Returns the hash of this normalized FQN.
	 * @return int Hash of this normalized FQN.
	 */
	public function getHash(){
		if( $this->hash == 0 )
			$this->hash = Hash::hashOfString($this->fqn_normalized);
		return $this->hash;
	}
	
	
}
