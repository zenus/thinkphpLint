<?php

namespace it\icosaedro\lint\documentator;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\containers\HashMap;
use it\icosaedro\io\File;
use it\icosaedro\utils\Strings;
use it\icosaedro\utils\UString;

/**
 * Builds relative path from a file to another. Results are cached; since these
 * paths are relative to one specific package currently being documente, one
 * instance of this class has to be created for every processed package.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/08/17 18:37:21 $
 */
class RelativePathBuilder {
	
	/**
	 * Directory of the current package.
	 * @var File 
	 */
	private $curr_pkg_dir;
	
	/**
	 *
	 * @var string 
	 */
	private $extension;
	
	/**
	 * Reference remapping. There is an even number of entries; for every pair,
	 * the first entry is the target and the second entry is the replacement.
	 * Every target is compared with the beginning of every link generated:
	 * if it match, that beginning of the link is replaced.
	 */
	private $ref_remap = /*. (string[int]) .*/ array();
	
	/**
	 * Maps an external file (File) to a path relative to the current file
	 * (string). The constructor initializes this map to the empty map, then
	 * the <code>relativePathOf()</code> method stored here the calculated
	 * relative paths, so that they are chached once for all.
	 * @var HashMap 
	 */
	private $relative_paths;

	/**
	 * @param File $fn Current package being documented. Relative paths are
	 * calculate against this PHP source file.
	 * @param string $extension HTML file extension, usually ".html".
	 * @param string[int] $ref_remap Remapping pairs applied to the resulting
	 * relative path.
	 */
	public function __construct($fn, $extension, $ref_remap) {
		$this->curr_pkg_dir = $fn->getParentFile();
		$this->extension = $extension;
		$this->ref_remap = $ref_remap;
		$this->relative_paths = new HashMap();
	}
	
	
	/**
	 * Returns a relative path from the HTML document of the current package
	 * being reported to another package. Calculated values are cached for
	 * efficiency.
	 * @param File $other Another package "xxx.php".
	 * @return string Relative path from this HTML document to the other HTML
	 * document (this latter may or may not be already existing).
	 */
	public function build($other)
	{
		$res = cast("string", $this->relative_paths->get($other));
		if( $res !== NULL )
			return $res;
		// Builds the path to the HTML file of the other package:
		$other_html = new File($other->getBaseName()->append(UString::fromASCII($this->extension)));
		// Calculates the relative path to the other HTML file:
		$relative_path = $other_html->relativeTo($this->curr_pkg_dir)->toASCII();
		// Apply remapping:
		for($i = 0; $i < count($this->ref_remap); $i += 2){
			$target = $this->ref_remap[$i];
			if( Strings::startsWith($relative_path, $target) ){
				$relative_path = $this->ref_remap[$i+1]
					. Strings::substring($relative_path, strlen($target), strlen($relative_path));
				break; // stop at the first match found
			}
		}
		// Store in cache:
		$this->relative_paths->put($other, $relative_path);
		return $relative_path;
	}

}
