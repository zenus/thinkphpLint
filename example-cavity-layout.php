<?php

/*. require_module 'standard'; .*/

/*. private .*/ class CavityNode
{
	public /*. string .*/ $cmp;
	public $dir = "";
	public /*. CavityNode .*/ $p, $q;
	public /*. bool .*/
		$gap_n = true,
		$gap_s = true,
		$gap_w = true,
		$gap_e = true;
}


/**
 *  Layout manager for HTML pages.
 *
 *  An utility class that helps formatting HTML pages.  Istances of this class
 *  are cavities. Every cavity is a rectangular region that contains either
 *  a string of HTML code, or it contain two sub-cavities.  Sub-cavities can
 *  be distributed inside the parent cavity either stacked north/south or
 *  west/east. Cavities can, in turn, contain other cavities and so on.
 *  <p>
 *  <b>Cavity path.</b>
 *  For cavities splitted north/south, the upper cavity is indicated as
 *  "N" and the lower cavity is "S". For cavities splitted west/east, the
 *  leftmost cavity is "W' and the rightmost is "E".
 *  <p>
 *  Cavities and sub-cavities have a path describing their position.  The path
 *  starts from the root (i.e. external) cavity and continues following all
 *  the sub-cavities.  For example, if a cavity gets splitted north/south,
 *  and then the south sub-cavity gets splitted in the west/east direction,
 *  then we end with 3 cavities whose paths are "N", "SW" and "SE".
 *  <p>
 *  New cavities are created simply adding HTML strings and specifying
 *  the desired path of the cavity where this string has to be located.
 *  <p>
 *  <b>Adding contents to the root cavity.</b>
 *  The add() method is the only way to add contents to the cavity.
 *  The following example will produce a result similar to this one
 *  (the border is only for reference and it is not displayed):
 *
 *  <blockquote><pre>
 *  +---------------+
 *  |      Top      |
 *  +-------+-------+
 *  | Left  | Right |
 *  +-------+-------+
 * 
 *
 *  $c = new CavityLayout();
 *  $c-&gt;add("Top", "N");
 *  $c-&gt;add("Left", "SW");
 *  $c-&gt;add("Right", "SE");
 *  echo $c;
 *  </pre></blockquote>
 *
 *  In the example above, the "N" cavity contains the string "Top", the "S"
 *  cavity contains two sub-cavities "SW" and "SE" that in turn contain the
 *  strings "Left" and "Right".
 *  <p>
 *  Intermediate cavities that do not contain directly HTML text but contain
 *  other cavities (as "S" above) are created automatically.
 *  Cavities and sub-cavities can be created in any order.
 *  <p>
 *  There is not limit to the number of nested cavities, so for example a
 *  list of 3 stacked objects can occupy the cavities "N", "SN" and "SSN".
 *  Note that in this case the cavity "SSS" remain empty. Empty cavities
 *  do not produce output.
 *  <p>
 *  <b>Alignment of cavities and textual contents.</b>
 *  Spare space, if available, is distributed evenly both vertically and
 *  horizontally, so that a cavity inside a larger cavity is located
 *  exactly at the center of the available space.
 *  <p>
 *  You can also align the cavity to any of the 4 side of the parent
 *  cavity. To this aim, add any of the letters "nswe" just after the cavity
 *  letter. For example, the path "Nnw" set the alignment of the "N" cavity
 *  to the upper-left corner of the parent cavity.
 *  <p>
 *  Also textual elements can be aligned. To this aim, add a space to their
 *  path then add any combination of the letters "nswe". Cavity alignments
 *  and textual alignment can be combined in a path, for example:
 *  <p>
 *  <code>"NSSneW e"</code>
 *  <p>
 *  has two effects: the NSS cavity is aligned "ne", and the textual component
 *  is aligned to the right edge of the containing cavity. Obviously,
 *  aligning something both "n" and "s", or both "w" and "e" has no meaning;
 *  doing so, "n" prevails over "s", and "w" prevails over "e".
 *  <p>
 *  Very long chunks of text may need to be splitted in several shorter lines,
 *  for example using wordwrap($long_text, 50, "&lt;br&gt;\n").
 *
 *  @package CavityLayout
 *  @author Umberto Salsi <salsi@icosaedro.it>
 *  @version $Date: 2014/02/17 11:50:35 $
 */

class CavityLayout
{
	public $table_border = 0;

	private /*. CavityNode .*/ $root;

	private
		$border = 0,
		$padding = 0;


	/**
	 *  Creates a new empty cavity.
	 *
	 *  @param int $border (pixel) is the space left around this root cavity.
	 *  @param int $padding (pixel) is the space left around textual elements
	 *                      inserted with the add() method.
	 *  @return void
	 */
	public function __construct($border = 0, $padding = 5)
	{
		$this->border = $border;
		$this->padding = $padding;
	}


	/**
	 *  Add a textual content. Intermediate cavities are also created.
	 *
	 *  @param string $cmp is the HTML textual content.
	 *  @param string $path is the destination cavity.
	 *  @return void
	 *
	 *  @throws ErrorException if the path is not valid:
	 *       it is an empty string;
	 *       the cavity already contains text and cannot be splitted;
	 *       invalid cavity specifier or alignment specifier.
	 */
	public function add($cmp, $path)
	{

		if( strlen($path) == 0 )
			throw new ErrorException("empty string not a valid path");
		
		if( $this->root === NULL )
			$this->root = new CavityNode();

		$n = $this->root;
		for( $i = 0; $i < strlen($path); $i++ ){
			$c = $path[$i];

			if( $c === ' ' and $i > 0 )
				break;

			if( $n->cmp !== NULL )
					throw new ErrorException("invalid cavity path `$path' at offset $i: the node cannot be traversed because it is a component, not a cavity");

			if( $n->dir === 'N' && ($c === 'W' || $c === 'E')
			||  $n->dir === 'W' && ($c === 'N' || $c === 'S') )
				throw new ErrorException("invalid cavity path `$path' at offset $i: the cavity is already splitted in the opposite direction");

			if( $c === 'N' || $c === 'W' ){
				$n->dir = ($c === 'N')? 'N':'W';
				if( $n->p === null )
					$n->p = new CavityNode();
				$n = $n->p;
			} else if( $c === 'S' || $c === 'E' ){
				$n->dir = ($c === 'S')? 'N':'W';
				if( $n->q === null )
					$n->q = new CavityNode();
				$n = $n->q;
			} else if( $c === 'n' )
				$n->gap_n = false;
			else if( $c === 's' )
				$n->gap_s = false;
			else if( $c === 'w' )
				$n->gap_w = false;
			else if( $c === 'e' )
				$n->gap_e = false;
			else {
				throw new ErrorException("unknown cavity path specifier `$c' scanning path argument `$path'");
			}
		}

		if( $n->cmp !== null )
			throw new ErrorException("invalid cavity path `$path': node already occupied by another component");
		else if( $n->p != null || $n->q != null )
			throw new ErrorException("invalid cavity path `$path': the node is an intermediate cavity, cannot insert component here");

		$n->cmp = $cmp;

		for( $i = $i+1; $i < strlen($path); $i++ ){
			$c = $path[$i];
			if( $c === 'n' ){
				$n->gap_n = FALSE;
			} else if( $c === 's' ){
				$n->gap_s = FALSE;
			} else if( $c === 'w' ){
				$n->gap_w = FALSE;
			} else if( $c === 'e' ){
				$n->gap_e = FALSE;
			} else
				throw new ErrorException("unknown padding specifier `$c'");
		}
	}


	private /*. string .*/ function toString(
		/*. CavityNode .*/ $n,
		/*. string .*/ $parent_orientation)
	{
		if( $n === NULL )
			return "<td>&nbsp;</td>\n";  # FIXME

		$ta = "width='100%'";   # table alignment attributes
		$ca = "<td";  # cell alignment attributes
		if( ! $n->gap_n )
			$ca .= " valign=top";
		else if( ! $n->gap_s )
			$ca .= " valign=bottom";
		if( ! $n->gap_w ){
			$ta = "";
			$ca .= " align=left";
		} else if( ! $n->gap_e ){
			$ta = "";
			$ca .= " align=right";
		}
		$ca .= ">";

		if( $n->cmp === NULL ){
			if( $n->dir === "N" ){
				if( $parent_orientation === "N" ){
					return $this->toString($n->p, "N")
						. "</tr>\n"
						. $this->toString($n->q, "N")
						. "</tr>\n";

				} else {
					return "$ca\n<table $ta border=". $this->table_border
						." cellspacing=0"
						." cellpadding=0"
						.">\n<tr>\n"
					. $this->toString($n->p, "N")
					. "</tr>\n<tr>\n"
					. $this->toString($n->q, "N")
					. "</tr>\n</table>\n</td>\n";
				}

			} else {
				if( $parent_orientation === "W" ){
					return $this->toString($n->p, "W")
						. $this->toString($n->q, "W");

				} else {
					return "$ca\n<table $ta border=". $this->table_border
						." cellspacing=0"
						." cellpadding=0"
						.">\n<tr>\n"
					. $this->toString($n->p, "W")
					. $this->toString($n->q, "W")
					. "</tr>\n</table>\n";
				}
			}

		} else {
			return "$ca"
				. "<table border=" . $this->table_border
				. " cellspacing=0 cellpadding="
				. $this->padding . "><td>"
				. $n->cmp
				. "</td></table>"
				."</td>\n";
		}

	}


	/**
	 *  Returns the resulting HTML code.
	 *
	 *  @return string
	 */
	function __toString()
	{
		if( $this->root === NULL )
			return "";

		return "<table border=". $this->table_border
			." cellspacing=0"
			." cellpadding=" . $this->border
			.">\n<tr>\n"
			. $this->toString($this->root,
				($this->border == 0)? $this->root->dir : "X")
			. "</tr>\n</table>\n";
	}
}


/**
 * Test function to be removed.
 * @access private
 * @throws ErrorException
 */
function test_code_remove()
{
	header("Content-Type: text/html; charset=UTF-8");

	echo "<html><body bgcolor='#dcdad5'>\n";
	echo "<h1>Testing CavityLayout</h1>\n";
	echo "<form>";
	$c = new CavityLayout(0, 5);
	#$c->table_border = 1;  # un-comment for debugging

	$c->add("<b>Form description here</b>", "N");
	$in = "SN";  # cavity of the input area
	$but = "SSSe";  # cavity for the buttons

	$c->add("First Name:", $in."NW");
	$c->add("<input type=text size=15>", $in."NE e");
	$c->add("Last Name:", $in."SNW");
	$c->add("<input type=text size=15>", $in."SNE e");
	$c->add("Age:", $in . "SSW");
	$c->add("<input type=text size=5>", $in."SSE e");

	$c->add("<input type=submit value=Cancel>", $but."W e");
	$c->add("<input type=submit value=Save>", $but."E e");

	$t = "This very long text servers to illustrate how the wordwrap() function can help to format properly the mask, avoiding too large cells.";
	$c->add( wordwrap($t, 50, "<br>\n"), "SSN");

	echo $c;
	echo "</form>";
	echo "</body></html>";
}

test_code_remove();
