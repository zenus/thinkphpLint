<?php

namespace it\icosaedro\lint;
use it\icosaedro\io\File;
use it\icosaedro\io\FileInputStream;
use it\icosaedro\io\FileNotFoundException;
use it\icosaedro\io\FilePermissionException;
use it\icosaedro\io\IOException;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\statements\Statement;
use it\icosaedro\lint\statements\EchoBlockStatement;
use it\icosaedro\utils\Strings;
use it\icosaedro\regex\Pattern;
use it\icosaedro\lint\docblock\DocBlockScanner;
use it\icosaedro\lint\docblock\DocBlockWrapper;
use it\icosaedro\lint\ParseException;

require_once __DIR__ . "/../../../all.php";

/**
 * Parses a package..
 * 
 * A <b>package</b> is a single PHP source file.
 * 
 * A <b>library</b> is a package that provide programming tools to other
 * client code. Then, a package <i>is not</i> a library if any of these
 * conditins is detected:
 * contains the initial BOM (byte ordering mark for Unicode encoding);
 * contains text (typically HTML) surrounding the PHP code;
 * contains the <code>return</code> statement at global scope;
 * triggers errors;
 * throws checked exceptions.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/12/09 20:09:53 $
 */
class PackageParser {
	
	/**
	 *
	 * @var Globals 
	 */
	private $globals;
	
	
	/**
	 *
	 * @param Globals $globals 
	 * @return void
	 */
	public function __construct($globals){
		$this->globals = $globals;
	}
	
	
	/**
	 * Checks if the current DocBlock has been consumed, then clears current
	 * DocBlock so it cannot be used again.
	 * @return void
	 */
	private function disposeDocBlock(){
		$pkg = $this->globals->curr_pkg;
		$db = $pkg->curr_docblock->getDocBlock();
		if( $db != NULL ){
			$this->globals->logger->error($db->decl_in, "unused DocBlock");
			$pkg->curr_docblock = new DocBlockWrapper($this->globals->logger);
		}
	}
	
	
	/**
	 * @return void
	 */
	private function setDocBlock(){
		$pkg = $this->globals->curr_pkg;
		$scanner = $pkg->scanner;
		$here = $scanner->here();
		$db = DocBlockScanner::parse($this->globals->logger, $here, $scanner->s, $this->globals);
		$pkg->curr_docblock = new DocBlockWrapper($this->globals->logger, $db, $this->globals->isPHP(4));
		
		// Consume immediately package DocBlock:
		if( $pkg->curr_docblock->isPackage() ){
			
			// Check if this package already has a DocBlock:
			if( $pkg->docblock !== NULL )
				$this->globals->logger->error($here, "multiple @package DocBlocks");
			
			// Assign the pkg DocBlock to this pkg:
			$pkg->curr_docblock->checkLineTagsForPackage();
			$pkg->docblock = $db;
			
			// Reset curr DocBlock:
			$pkg->curr_docblock->clear();
		}
	}
	

	/**
	 * Parse a package, that is a PHP source file.
	 * @param File $fn File name of the PHP source.
	 * @param boolean $is_module True if it is a module.
	 * @return void
	 * @throws FileNotFoundException
	 * @throws FilePermissionException
	 * @throws IOException
	 * @throws ParseException
	 * @throws ScannerException
	 */
	public function parse($fn, $is_module)
	{
		$logger = $this->globals->logger;
		if( $logger->print_source )
			$logger->printVerbatim("BEGIN parsing of "
			. $logger->formatFileName($fn) . "\n");

		$f = new FileInputStream($fn);
		$pkg = new Package($this->globals, $fn, $f, $is_module);
		// FIXME: this should go in Globals::loadPackage()
		$this->globals->packages->put($fn, $pkg);
		$this->globals->curr_pkg = $pkg;

		$pkg->scope = 0;
		$scanner = $pkg->scanner;

		// Report and skip initial text:
		if ($scanner->sym === Symbol::$sym_text) {
			$text = Strings::toLiteral(substr($scanner->s, 0, 20));

			if ($is_module) {
				$pkg->notLibrary("Found leading text in file before opening PHP tag: $text.");
				// FIXME: leading text in modules is not an error because ignored. ok?
			} else if (Pattern::matches("#!{ -\xff}+?\n", $scanner->s)) {
				$pkg->notLibrary("Unix CGI executable script detected: $text.");
			} else {
				$bom = array(
					/* BOM patterns and description: */
					array("\xfe\xff", "UTF-16 BE"),
					array("\xff\xfe", "UTF-16 LE"),
					array("\xef\xbb\xbf", "UTF-8"),
					array("\x00\x00\xfe\xff", "UTF-32 BE"),
					array("\xff\xfe\x00\x00", "UTF-32 LE")
				);

				for ($i = count($bom) - 1; $i >= 0; $i--)
					if (Strings::startsWith($pkg->scanner->s, $bom[$i][0]))
						break;

				if ($i >= 0) {
					$msg = "Unicode " . $bom[$i][1] . " BOM sequence detected: $text.";
					$pkg->notLibrary($msg);
					$logger->error($scanner->here(), "unsupported $msg");
				} else {
					$pkg->notLibrary("Bare textual content detected before PHP opening tag: $text.");
				}
			}
		}

		/*
		 * Main loop of the package parser. Its main tasks are: collect
		 * DocBlocks at scope level 0, check if actual PHP code is present,
		 * and detect end of file.
		 */
		$code_found = FALSE;
		$res = Flow::NEXT_MASK;
		while(TRUE) {
			$sym = $scanner->sym;
			if( $sym === Symbol::$sym_open_tag ){
				$code_found = TRUE;
				$scanner->readSym();
				
			} else if( $sym === Symbol::$sym_open_tag_with_echo ){
				$code_found = TRUE;
				EchoBlockStatement::parse($this->globals);
			
			} else if( $sym === Symbol::$sym_x_docBlock ){
				if( $this->globals->parse_phpdoc ){
					$this->disposeDocBlock();
					$this->setDocBlock();
				}
				$scanner->readSym();
				
			} else if( $sym === Symbol::$sym_eof ){
				break;
			
			} else {
				// Parse next statement.
				if( ($res & Flow::NEXT_MASK) == 0 )
					$logger->error($scanner->here(), "unreachable statement");
			
				$res = Statement::parse($this->globals);
			}
		}

		if (!$code_found)
			$logger->notice($scanner->here(), "no PHP code found at all");


		$pkg->resolver->close($logger);

		// FIXME: todo
//	/*
//		Check undefined function protos
//	*/
//
//	foreach(Function_::funcs as $f){
//		if( $f->is_forward && $f->decl_in->getFile()->equals($fn) ){
//			$f->decl_in->error("missing function " . $f->name
//				. " declared forward");
//		}
//	}
//
//	/*
//		Check undefined class protos
//	*/
//
//	FOR i=0 TO count(classes)-1 DO
//		c = classes[i];
//		if( c[forward] AND (c[decl_in][fn] = abs_pathfile) ){
//			Error2(here(), "missing class `" . c[name]
//				. "' declared forward in " . reference(c[decl_in]));
//		}
//	}
//
		if( ! $pkg->is_library )
			$logger->notice(new Where($fn),
			"this package is not a library:\n" . $pkg->why_not_library);
		
		$pkg->scanner->close();
		$pkg->scanner = NULL;
		
		if( $logger->print_source )
			$logger->printVerbatim("END parsing of "
			. $logger->formatFileName($fn) . "\n");

		if ($pkg->loop_level !== 0) {
			$logger->error(NULL, "phplint: INTERNAL ERROR: loop_level="
			. $pkg->loop_level);
		}

		if( $pkg->scope != 0 ){
			$logger->error(NULL, "phplint: INTERNAL ERROR: scope="
				. $pkg->scope);
		}

		if( $pkg->silencer_level != 0 ){
			$logger->error(NULL, "phplint: INTERNAL ERROR: silencer_level="
				. $pkg->silencer_level);
		}
	}
	
}
