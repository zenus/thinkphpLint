<?php

namespace it\icosaedro\lint\documentator;

require_once __DIR__ . "/../../../../all.php";

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Visibility;
use it\icosaedro\lint\Constant;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\Function_;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\Package;
use it\icosaedro\utils\Strings;
use it\icosaedro\lint\CaseInsensitiveString;
use it\icosaedro\lint\FullyQualifiedName;
use it\icosaedro\lint\Where;

/**
 * Resolves in-line tags in DocBlock text. The allowed in-line tags are:
 * <blockquote><pre>
 * {@}} --&gt; {@}
 * {@}*} --&gt; {@*}
 * {@}img URL}
 * {@}link ITEM} --&gt; &lt;a href='URLTOITEM'&gt;ITEM&lt;/a&gt;
 * {@}link ITEM HTML} --&gt; &lt;a href='URLTOITEM'&gt;HTML&lt;/a&gt;
 * </pre></blockquote>
 * where <code>HTML</code> is the optional text, and <code>ITEM</code> is
 * any referrable PHP item or relative path, for example:
 * <blockquote><pre>
 * M_PI        (constant)
 * $v          (global variable)
 * func()      (function)
 * MyClass     (class)
 * MyClass::CO
 * MyClass::$p
 * MyClass::m()
 * self::xxx   (implicit class to which the DocBlock belongs)
 * parent::xxx (parent of the class to which the DocBlock belongs)
 * ::CO        (implicit class to which the DocBlock belongs)
 * ::$p        (implicit class to which the DocBlock belongs)
 * ::$m        (implicit class to which the DocBlock belongs)
 * ftp://xxx
 * http://xxx
 * https://xxx
 * mailto:xxx
 * ./xxx    (relative path to file)
 * ../xxx   (relative path to file)
 * </pre></blockquote>
 * Functions and classes must be absolute or fully qualified names.
 * Absolute file paths are not recognized.
 * The path separator is always the slash character.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/25 11:24:39 $
 */
class InLineTags {

	/**
	 * Unsupported weel-known in-line tags. Does not complains, but writes
	 * them verbatim.
	 */
	private static $unsupported = array(
		"example" => 0,
		"internal" => 0,
		"inheritdoc" => 0,
		"source" => 0,
		"tutorial" => 0
	);
	
	
	/**
	 *
	 * @var Globals 
	 */
	private $globals;
	
	
	/**
	 * Short links "::xxx" are referred to this contextual class, possibly
	 * NULL if the in-line tag does not belong to a class or class member.
	 * @var ClassType 
	 */
	private $contextual_class;
	
	/**
	 * @var RelativePathBuilder
	 */
	private $relative_path;
	
	
	/**
	 * @param RelativePathBuilder $relative_path
	 * @param Globals $globals
	 */
	function __construct($globals, $relative_path)
	{
		$this->globals = $globals;
		$this->relative_path = $relative_path;
	}
	
	
	/**
	 * Resolves a reference to class member.
	 * @param ClassType $c
	 * @param string $name
	 * @param string $html Second argument of the link in-line tag. Empty
	 * string if missing.
	 * @return string HTML anchor to the item.
	 * @throws InLineTagException Resolution failed.
	 */
	private function resolveMember($c, $name, $html)
	{
		if( $c === NULL ){
			throw new InLineTagException("no default class outside class context");
			
		} else if( Strings::startsWith($name, "\$") ){
			// Property.
			$p = $c->searchProperty(Strings::substring($name, 1, strlen($name)));
			if( $p === NULL )
				throw new InLineTagException("unresolved property $c::$name");
			$fragment = HtmlUtils::escapeFragment("$c::\$" . $p->name);
			$href = $this->relative_path->build($p->decl_in->getFile()) . "#$fragment";
			if( strlen($html) == 0 )
				$html = $c->name->getName()."::\$".$p->name;
			return "<a href='$href'>$html</a>";
			
		} else if( Strings::endsWith($name, ")") ){
			// Method.
			$i = strpos($name, "(");
			if( $i === FALSE )
				throw new InLineTagException("missing `('");
			$method_name = new CaseInsensitiveString(Strings::substring($name, 0, $i));
			$m = $c->searchMethod($method_name);
			if( $m === NULL )
				throw new InLineTagException("unknown method $c::$name()");
			if( $m->name->__toString() !== $method_name->__toString() )
				throw new InLineTagException(
					"method\n\t$method_name was declared as\n\t"
						. $m->name->__toString()
						. " that differs by upper/lower-case letters only");
			$fragment = HtmlUtils::escapeFragment("$c::" . $m->name . "()");
			// FIXME: copy arguments, is any, in text
			$href = $this->relative_path->build($m->decl_in->getFile()) . "#$fragment";
			if( strlen($html) == 0 )
				// copy also the args: {@link MyClass::m(1,2,3)}
				$html = $c->name->getName()."::".  htmlspecialchars($name);
			return "<a href='$href'>$html</a>";
			
		} else {
			// Class constant.
			$i = strpos($name, "(");
			$co = $c->searchConstant($name);
			if( $co === NULL )
				throw new InLineTagException("unresolved constant $c::$name");
			$fragment = HtmlUtils::escapeFragment("$c::" . $co->name);
			$href = $this->relative_path->build($co->decl_in->getFile()) . "#$fragment";
			if( strlen($html) == 0 )
				$html = $c->name->getName()."::".$co->name;
			return "<a href='$href'>$html</a>";
			
		}
	}
	
	
	/**
	 *
	 * @param string $class_name 
	 * @return ClassType
	 */
	private function resolveClass($class_name)
	{
		// FIXME: this allows only FQN of class:
		// support abs and qualified (?)
		// FIXME: check class name spelling
		if( $class_name === "self" ){
			return $this->contextual_class;
		} else if( $class_name === "parent" ){
			if( $this->contextual_class === NULL )
				return NULL;
			return $this->contextual_class->extended;
		} else {
			$fqn = new FullyQualifiedName($class_name, FALSE);
			return $this->globals->getClass($fqn);
		}
	}
	
	
	/**
	 * Resolves a referred item {@}link ITEM HTML} into an anchor.
	 * @param string $item Item to resolve, first argument of the link in-line
	 * tag.
	 * @param string $html Second argument of the link in-line tag. Empty
	 * string if missing.
	 * @return string HTML anchor to the item.
	 * @throws InLineTagException Resolution failed.
	 */
	private function resolveItem($item, $html)
	{
		if( Strings::startsWith($item, "ftp://")
		|| Strings::startsWith($item, "http://")
		|| Strings::startsWith($item, "https://")
		|| Strings::startsWith($item, "mailto:")
		|| Strings::startsWith($item, "./")
		|| Strings::startsWith($item, "../")
		){
			if( strlen($html) == 0 )
				$html = $item;
			return "<a href='$item'>$html</a>";
			
		} else if( Strings::startsWith($item, "\$") ){
			$v = $this->globals->searchVar(Strings::substring($item, 1, strlen($item)));
			if( $v === NULL )
				throw new InLineTagException("unresolved global variable $item");
			
			$fragment = HtmlUtils::escapeFragment($v->name);
			$href = $this->relative_path->build($v->decl_in->getFile()) . "#$fragment";
			if( strlen($html) == 0 )
				$html = "$v";
			return "<a href='$href'>$html</a>";
		
		} else if( Strings::startsWith($item, "::") ){
			// Class member in current class.
			return $this->resolveMember($this->contextual_class, Strings::substring($item, 2, strlen($item)), $html);
		
		} else if( ($i = strpos($item, "::")) !== FALSE ){
			// Class member.
			$c = $this->resolveClass(Strings::substring($item, 0, $i));
			if( $c === NULL )
				throw new InLineTagException("unresolved class");
			$member_name = Strings::substring($item, $i+2, strlen($item));
			return $this->resolveMember($c, $member_name, $html);
		
		} else if( Strings::endsWith($item, ")") ){
			// Function.
			$i = strpos($item, "(");
			if( $i === FALSE )
				throw new InLineTagException("missing `('");
			$fqn = new FullyQualifiedName(Strings::substring($item, 0, $i), FALSE);
			$f = $this->globals->getFunc($fqn);
			if( $f === NULL )
				throw new InLineTagException("unresolved function $fqn");
			// FIXME: this allows only FQN of func:
			// support abs and qualified (?)
			if( ! $f->name->equalsCaseSensitive($fqn) )
				throw new InLineTagException(
					"function\n\t$fqn was declared as\n\t"
						. $f->name->getFullyQualifiedName()
						. " that differs by upper/lower-case letters only");
			$fragment = HtmlUtils::escapeFragment($f->name . "()");
			$href = $this->relative_path->build($f->decl_in->getFile()) . "#$fragment";
			if( strlen($html) == 0 )
				// copy also the args: {@link f(1,2,3)}
				$html = $f->name->getName() . htmlspecialchars(substr($item, $i));
			return "<a href='$href'>$html</a>";
			
		} else {
			// Constant or class: try both.
			$c = $this->resolveClass($item);
			if( $c !== NULL ){
				$fragment = HtmlUtils::escapeFragment($c->name->__toString());
				$href = $this->relative_path->build($c->decl_in->getFile()) . "#$fragment";
				if( strlen($html) == 0 )
					$html = $c->name->getName();
				return "<a href='$href'>$html</a>";
			}
			$fqn = new FullyQualifiedName($item, TRUE);
			$co = $this->globals->getConstant($fqn);
			if( $co !== NULL ){
				$fragment = HtmlUtils::escapeFragment($co->name->__toString());
				$href = $this->relative_path->build($co->decl_in->getFile()) . "#$fragment";
				if( strlen($html) == 0 )
					$html = $co->name->getName();
				return "<a href='$href'>$html</a>";
			}
			throw new InLineTagException("unresolved $item");
		}
	}
	
	
	/**
	 *
	 * @param string $content Content of the in-line tag, that is, text
	 * between "{@" and "}".
	 * @return string 
	 * @throws InLineTagException Resolution failed.
	 */
	private function parseInLineTag($content)
	{
		// Convert all white-spaces to a single space char:
		$content = (string) str_replace("\t", " ", $content);
		$content = (string) str_replace("\r", " ", $content);
		$content = (string) str_replace("\n", " ", $content);
		do {
			$content2 = (string) str_replace("  ", " ", $content);
			if( strlen($content2) < strlen($content) )
				$content = $content2;
			else
				break;
		} while(TRUE);
		
		$a = explode(" ", $content, 3);
		$tag = $a[0];
		if( $tag === "" ){ // "{@}" --> "{@"
			return "{@";
			
		} else if(array_key_exists($tag, self::$unsupported) ){
			return "{@$content}";
			
		} else if( $tag === "*" ){ // "{@*}" --> "*/"
			return "*/";
			
		} else if( $tag === "img" ){
			if( count($a) != 2 )
				return "{@$content}";
			return "<img src='" . $a[1] . "'>";
			
		} else if( $tag === "link" ){
			if( count($a) < 2 )
				throw new InLineTagException("missing arguments");
			if( count($a) == 2 )
				$text = "";
			else
				$text = $a[2];
			return $this->resolveItem($a[1], $text);
			
		} else {
			throw new InLineTagException("unknown in-line tag");
		}
	}
	

	/**
	 * Resolves all the in-line tags <code>{@}xxx}</code> in the given HTML
	 * text, normally part of a DocBlock.
	 * Errors messages might be logged if some in-line tag cannot be resolved;
	 * in this case the line tags is left in the text, unmodified.
	 * @param Where $where Location of the HTML text.
	 * @param string $html
	 * @param ClassType $contextual_class If not null, it is the class or
	 * class member to which the HTML belongs. Short items "::member" forms,
	 * "self" and "parent" are resolved against this class.
	 * @return string Same HTML text, but with in-line tags resolved.
	 */
	public function resolve($where, $html, $contextual_class)
	{
		if( strlen($html) == 0 )
			return $html;
		$this->contextual_class = $contextual_class;
		$i = 0;
		do {
			$i = strpos($html, "{@", $i);
			if( $i === FALSE )
				return $html;
			$j = strpos($html, "}", $i + 2);
			if( $j === FALSE ){
				// err
				return $html;
			}
			// extract content between "{@" and "}":
			$content = Strings::substring($html, $i+2, $j);
			try {
				$resolved = self::parseInLineTag($content);
				$html = Strings::substring($html, 0, $i) . $resolved
					. Strings::substring($html, $j+1, strlen($html));
				$i = $i + strlen($resolved);
			}
			catch(InLineTagException $e){
				$this->globals->logger->error($where,
					"{@$content}: " . $e->getMessage());
				$i = $j + 1;
			}
		} while(TRUE);
	}

}
