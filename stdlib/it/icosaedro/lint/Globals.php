<?php

namespace it\icosaedro\lint;

require_once __DIR__ . "/../../../all.php";

use it\icosaedro\io\File;
use it\icosaedro\lint\types\Type;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\ArrayType;
use it\icosaedro\lint\types\BooleanType;
use it\icosaedro\lint\types\FloatType;
use it\icosaedro\lint\types\IntType;
use it\icosaedro\lint\types\MixedType;
use it\icosaedro\lint\types\NullType;
use it\icosaedro\lint\types\ResourceType;
use it\icosaedro\lint\types\StringType;
use it\icosaedro\lint\types\UnknownType;
use it\icosaedro\lint\types\VoidType;
use it\icosaedro\lint\types\ClassConstant;
use it\icosaedro\lint\types\ClassProperty;
use it\icosaedro\lint\types\ClassMethod;
use it\icosaedro\containers\HashMap;
use it\icosaedro\io\IOException;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\ScannerException;


/**
 * Global context of the parser. Contains the parsing options, current state
 * and collected items from all the packages.
 * Also see the <code>Package</code> class that keeps the per-package
 * state of the parser.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/03/03 16:25:14 $
 */
abstract class Globals implements ClassResolver {
	
	/**
	 * Total size of the source parsed (bytes).
	 */
	public $total_source_length = 0;
	
	/**
	 * Objects that writes the report, keeps error counters and stores some
	 * report format options.
	 * @var Logger 
	 */
	public $logger;

	/**
	 * Singleton instances of the immutable types.
	 * @var Type 
	 */
	public static $null_type, $void_type, $boolean_type, $int_type,
		$float_type, $string_type, $mixed_type, $resource_type, $unknown_type;
	
	/**
	 * Singleton instance of the <code>object</code> base class.
	 * @var ClassType 
	 */
	public static $object_type;

	/**
	 * Follows require_module and require_once directives in the first parsed
	 * package. If false and $recursion_level == 1, require* not allowed.
	 * Serves to implement a security feature in PHPLint-on-line WEB version.
	 * @var boolean
	 */
	public $recursive_parsing = FALSE;
	
	/**
	 * Counts the require* deep level: if we parse a package A and, in turn,
	 * A requires B, then this variable will contain 1 while parsing A and will
	 * contain 2 while parsing B.
	 * Serves to implement a security feature in PHPLint-on-line WEB version
	 * (prevents loading of arbitrary files from server).
	 * @var int 
	 */
	public $recursion_level = 0;

	/**
	 * If to report entities declared but not used.
	 * @var boolean 
	 */
	public $report_unused = FALSE;

	/**
	 * @var PhpVersion 
	 */
	public $php_ver;

	/**
	 * @var boolean
	 */
	public $parse_phpdoc = FALSE;

	/**
	 * Directories where to look for modules. Usually this array contains only
	 * one entry, that is the <code>modules/</code> directory of the PHPLint
	 * distributed program.
	 * @var File[int] 
	 */
	public $modules_dirs = /*.(File[int]).*/ array();

	/**
	 * Directories where to look for project root. Usually this array contains only
	 * one entry, that is the <code>modules/</code> directory of the PHPLint
	 * distributed program.
	 * @var File[int]
	 */
	public $project_root = /*.(File).*/ NULL;
	/**
	 * Read-only FQN of the autoload function "__autoload".
	 * @var FullyQualifiedName 
	 */
	public static $AUTOLOAD_FQN;
	
	/**
	 * Read-only FQN of the cast() function "__cast".
	 * @var FullyQualifiedName 
	 */
	public static $CAST_FQN;
	

	/*
	  Autoload pragma:
	 */

	/**
	 * The __autoload() function; NULL=still not found.
	 * @var Function_
	 */
	public $autoload_function;

	/**
	 * Argument of the /&#42;. pragma 'autoload' ... .&#42;/ meta-code.
	 * @var File
	 */
	public $autoload_prepend;

	/**
	 * Arguments of the /&#42;. pragma 'autoload' ... .&#42;/ meta-code.
	 * @var string
	 */
	public $autoload_separator, $autoload_append;
	
	/**
	 * Exception to thrown when errors-to-exception mapping is set by the
	 * <code>pragma error_throws_exception 'EXCEPTION';</code> statement.
	 * Normally NULL, meaning errors are handled the normal way.
	 * @var ClassType 
	 */
	public $error_throws_exception;
	
	/**
	 * Location of the first unhandled error. If not NULL, it's to late to set
	 * an error-to-exception remapping with the <code>pragma
	 * error_throws_exception 'EXCEPTION';</code> statement. Safeguard that
	 * prevents mixing code that use errors with code that uses only exceptions.
	 * @var Where 
	 */
	public $first_error_source_found;

	/**
	 * Maps constant names into <code>Constant</code> objects.
	 * @var HashMap
	 */
	public $constants = NULL;

	/**
	 * Index to the next available entry in vars, much like a "stack pointer".
	 * @var int
	 */
	public $vars_n = 0;

	/**
	 * Stack of variables: super-globals, gloabals, locals to function and
	 * method. Each entry of this array is a variable $v with name $v-&gt;name,
	 * scope $v-&gt;scope (-1 = superglobal, 0 = global, 1 = local, 2+ =
	 * nested function forbidden by PHPLint), and belongs to the package
	 * $v-&gt;decl_in-&gt;getFile(). Normally, the first entries are the
	 * superglobals, then the globals and finally the local variables of the
	 * function or method currently being parsed. Class autoloading complicates
	 * things: while parsing a function or method, class autoloading may
	 * trigger the loading of another package, where new global variables and
	 * new local variables may be added. Once the parsing of a function or
	 * method terminates, the {@link self::cleanCurrentScope()} method
	 * removes the local variables and decrementes vars_n, but this may leave
	 * "holes" between global variables that are set to NULL. These holes
	 * cannot be recovered because statement\AssignedVars assumes the index
	 * of a given variable never changes. That is why
	 * only the entries in the range [0,$vars_n-1] are currently existing vars,
	 * but some entries can be NULL. This solution in quite a mess, and might
	 * change in future.
	 * @var Variable[int]
	 */
	public $vars = /*. (Variable[int]) .*/ array();

	/**
	 * Maps the FQN of functions into <code>Function_</code> objects.
	 * @var HashMap
	 */
	public $functions = /*. (HashMap) .*/ NULL;
	
	/**
	 * Maps the FQN of classes and interfaces into <code>ClassType</code>
	 * objects.
	 * @var HashMap
	 */
	public $classes = /*. (HashMap) .*/ NULL;

	/**
	 * Package currently being parsed.
	 * @var Package 
	 */
	public $curr_pkg;

	/**
	 * Maps <code>File</code> objects that represents package names into
	 * <code>Package</code> objects.
	 * @var HashMap
	 */
	public $packages;
	
	/**
	 *
	 * @var BuiltinClasses 
	 */
	public $builtin;
	
	
	/**
	 * Convenience method that tells the PHP version we are parsing now.
	 * @param int $ver Either 4 or 5.
	 * @return boolean True is the current PHP version matches the argument.
	 * @throws \InvalidArgumentException Invalid argument.
	 */
	public function isPHP($ver){
		if( $ver == 4 )
			return $this->php_ver === PhpVersion::$php4;
		else if( $ver == 5 )
			return $this->php_ver === PhpVersion::$php5;
		else
			throw new \InvalidArgumentException("$ver");
	}


	/**
	 * Compare the current symbol from the scanner of the current package with
	 * the expected one. If they differs, logs error and throws exception.
	 * @param Symbol $expected Expected symbol.
	 * @param string $err Description of the context where the symbol was
	 * expected.
	 * @return void
	 * @throws ParseException
	 */
	public function expect($expected, $err){
		$scanner = $this->curr_pkg->scanner;
		if( $scanner->sym === $expected )
			return;
		throw new ParseException($scanner->here(), $err . ", found symbol " . $scanner->sym);
	}

	/**
	 * Retrieves a constant.
	 * @param FullyQualifiedName $name
	 * @return Constant 
	 */
	public function getConstant($name){
		return cast(Constant::NAME, $this->constants->get($name));
	}
	
	
	/**
	 * Retrieves a variable.
	 * @param string $name Name of the variable, whitout leading dollar sign.
	 * @return Variable 
	 */
	public function getVariable($name){
		$v = $this->vars;
		for($i = $this->vars_n - 1; $i >= 0; $i--)
			if( $v[$i]->name === $name )
				return $v[$i];
		return NULL;
	}
	
	
	/**
	 * Retrieces a function given its FQN.
	 * @param FullyQualifiedName $name
	 * @return Function_ Found function, or NULL if not found.
	 */
	public function getFunc($name){
		return cast(Function_::NAME, $this->functions->get($name));
	}
	
	
	/**
	 * Retrieves a class.
	 * @param FullyQualifiedName $name 
	 * @return ClassType
	 */
	public function getClass($name){
		return cast(ClassType::NAME, $this->classes->get($name));
	}

	
	/**
	 * Retrieves a parsed package.
	 * @param File $fn File name of the package.
	 * @return Package The package, or NULL if not found.
	 */
	public function getPackage($fn) {
		return cast(Package::NAME, $this->packages->get($fn));
	}


	/**
	 * Expands the Symbol::$sym_namespace operator and the following path into
	 * a single Symbol::$sym_identifier containing the absolute name of the
	 * identifier. The result is returned in the "s" property of the scanner.
	 * @return void
	 */
	public function resolveNamespaceOperator()
	{
		$scanner = $this->curr_pkg->scanner;
		if( $this->isPHP(4) )
			throw new ParseException($scanner->here(), "using reserved keyword `namespace' (PHP 5)");
		$scanner->readSym();
		$this->expect(Symbol::$sym_identifier, "expected identifier");
		if( $scanner->s[0] !== "\\" )
			throw new ParseException($scanner->here(), "expected path after namespace operator");
		if( $this->curr_pkg->resolver->name !== "" )
			$scanner->s = "\\" . $this->curr_pkg->resolver->name . $scanner->s;
	}


	/**
	 * Expands the Symbol::$sym_x_namespace operator and the following path into
	 * a single Symbol::$sym_x_identifier containing the absolute name of the
	 * identifier. The result is returned in the "s" property of the scanner.
	 * @return void
	 */
	public function resolveNamespaceOperatorInMetaCode()
	{
		$scanner = $this->curr_pkg->scanner;
		if( $this->isPHP(4) )
			throw new ParseException($scanner->here(), "using reserved keyword `namespace' (PHP 5)");
		$scanner->readSym();
		$this->expect(Symbol::$sym_x_identifier, "expected meta-code identifier");
		if( $scanner->s[0] !== "\\" )
			throw new ParseException($scanner->here(), "expected path after namespace operator");
		if( $this->curr_pkg->resolver->name !== "" )
			$scanner->s = "\\" . $this->curr_pkg->resolver->name . "\\" . $scanner->s;
	}
	

	/**
	 * Static initializer of this class, do not use.
	 * @return void
	 */
	public static function static_init() {
		self::$null_type = NullType::getInstance();
		self::$void_type = VoidType::getInstance();
		self::$boolean_type = BooleanType::getInstance();
		self::$int_type = IntType::getInstance();
		self::$float_type = FloatType::getInstance();
		self::$string_type = StringType::getInstance();
		self::$mixed_type = MixedType::getInstance();
		self::$resource_type = ResourceType::getInstance();
		self::$unknown_type = UnknownType::getInstance();
		self::$object_type = ClassType::getObject();
		self::$AUTOLOAD_FQN = new FullyQualifiedName("__autoload", FALSE);
		self::$CAST_FQN = new FullyQualifiedName("cast", FALSE);
	}
	

	/**
	 * Adds a PHP built-in constant.
	 * @param string $name
	 * @param Type $type 
	 * @param boolean $is_magic Magic constants actually are variables whose
	 * value is determined by PHP (and PHPLint too) at parse time.
	 * @return void
	 */
	private function addBuiltIntConstant($name, $type, $is_magic) {
		$fqn = new FullyQualifiedName($name, TRUE);
		$c = new Constant($fqn);
		$c->decl_in = Where::getSomewhere();
		$c->used = 100;
		$c->is_magic = $is_magic;
		$c->value = Result::factory($type, NULL);
		$this->constants->put($fqn, $c);
	}
	

	/**
	 * Adds a superglobal variable.
	 * @param string $name
	 * @param Type $type 
	 * @return void
	 */
	private function addSuperGlobalVar($name, $type) {
		$v = new Variable($name, FALSE, Where::getSomewhere(), -1);
		$v->used = 100;
		$v->assigned = TRUE;
		$v->assigned_once = TRUE;
		$v->type = $type;
		$this->vars[$this->vars_n++] = $v;
	}
	
	
	/**
	 * Initializes the parsing context and defines some PHP built-in items.
	 * @param Logger $logger Writer of the report.
	 * @return void
	 */
	public function __construct($logger){
		
		$this->logger = $logger;
		
		/*
		 * Constants.
		 */
		$this->constants = new HashMap();

		/*
		 * Magic constants that cannot be defined in regular PHP code
		 * because their type is "resource" and not scalar, which is
		 * forbidden in user programs:
		 */
		self::addBuiltIntConstant("STDIN", ResourceType::getInstance(), FALSE);
		self::addBuiltIntConstant("STDOUT", ResourceType::getInstance(), FALSE);
		self::addBuiltIntConstant("STDERR", ResourceType::getInstance(), FALSE);

		/*
		 * Magic constants __XXX__ whose value must be resolved at parse time:
		 */
		// FIXME: PHP 5 only - remove error msg from MagicConstant:
		self::addBuiltIntConstant("__DIR__", StringType::getInstance(), TRUE);
		self::addBuiltIntConstant("__CLASS__", StringType::getInstance(), TRUE);
		self::addBuiltIntConstant("__FILE__", StringType::getInstance(), TRUE);
		self::addBuiltIntConstant("__FUNCTION__", StringType::getInstance(), TRUE);
		self::addBuiltIntConstant("__LINE__", StringType::getInstance(), TRUE);
		// FIXME: PHP 5 only - remove error msg from MagicConstant:
		self::addBuiltIntConstant("__METHOD__", StringType::getInstance(), TRUE);
		// FIXME: PHP 5 only - remove error msg from MagicConstant:
		self::addBuiltIntConstant("__NAMESPACE__", StringType::getInstance(), TRUE);
		
		/*
		 * Variables.
		 */
		
		$this->vars = /*.(Variable[int]).*/ array();
		// Superglobals:
		$asm = ArrayType::factory(self::$string_type, self::$mixed_type);
		$ass = ArrayType::factory(self::$string_type, self::$string_type);
		$this->addSuperGlobalVar("GLOBALS", $asm);
		$this->addSuperGlobalVar("_SERVER", $ass);
		$this->addSuperGlobalVar("_GET", $asm);
		$this->addSuperGlobalVar("_POST", $asm);
		$this->addSuperGlobalVar("_COOKIE", $asm);
		$this->addSuperGlobalVar("_REQUEST", $asm);
		$this->addSuperGlobalVar("_FILES", ArrayType::factory(self::$string_type, $asm));
		$this->addSuperGlobalVar("_ENV", $ass);
		$this->addSuperGlobalVar("_SESSION", $asm);
		$this->addSuperGlobalVar("php_errormsg", self::$string_type);
		# FIXME: actually $php_errormsg is a variable dynamically created
		# into the current scope, and not really a super-global variable.
		
		
		$this->functions = new HashMap();
		
		$this->classes = new HashMap();
		$this->classes->put(self::$object_type->name, self::$object_type);
		
		$this->packages = new HashMap();
		
		// FIXME: globals mut be initialized with the PHP version
		//$this->builtin = new BuiltinClasses($this->isPHP(5));
		$this->builtin = new BuiltinClasses(TRUE);
	}
	
	
	/**
	 * Checks found constant name against the original declared name and
	 * shows differences in spelling of uppercase and lowercase letters.
	 * @param Constant $co Resolved constant, or NULL if not resolved.
	 * @param FullyQualifiedName $found Resolved FQN as found in the source.
	 * @return void
	 */
	private function checkSpellConstant($co, $found)
	{
		if( $co === NULL )
			return;
		if( ! $co->name->equalsCaseSensitive($found) )
			$this->logger->error($this->curr_pkg->scanner->here(),
			"constant\n"
			. "\t$found\n"
			. "was declared as\n"
			. "\t$co\n"
			. "that differs by upper/lower-case letters only");
	}


	/**
	 * Search a constant resolving its name in the current namespace.
	 * Also checks for mispelled upper/lower-case letters that might
	 * occurs in the namespace part of the name.
	 * @param string $name Name of the constant as found in the source, that
	 * might need to be resolved in the current namespace context.
	 * @return Constant Resolved constant, or NULL if not found.
	 */
	public function searchConstant($name)
	{
		if( NamespaceResolver::isAbsolute($name) ){
			$fqn = new FullyQualifiedName(substr($name, 1), TRUE);
			return cast(Constant::NAME, $this->constants->get($fqn));
			
		} else if( NamespaceResolver::isIdentifier($name) ){
			if( $this->curr_pkg->resolver->inNamespace() ){
				// In NS. Search in current NS:
				$fqn = new FullyQualifiedName(
					$this->curr_pkg->resolver->name . "\\" . $name, TRUE);
				$co = cast(Constant::NAME, $this->constants->get($fqn));
				if( $co !== NULL ){
					$this->checkSpellConstant($co, $fqn);
					return $co;
				}
			}
			
			// Search in global NS:
			$fqn = new FullyQualifiedName($name, TRUE);
			$co = cast(Constant::NAME, $this->constants->get($fqn));
			$this->checkSpellConstant($co, $fqn);
			return $co;
			
		} else {
			// Qualified name.
			$lead_id_idx = strpos($name, "\\");
			$lead_id = substr($name, 0, $lead_id_idx);
			foreach($this->curr_pkg->resolver->namespace_use as $nsu){
				// FIXME: comparison case insensitive.
				// But also check spelling.
				if( $nsu->alias === $lead_id ){
					$nsu->used++;
					$fqn = new FullyQualifiedName(
						$nsu->target . substr($name, $lead_id_idx), TRUE);
					$co = cast(Constant::NAME, $this->constants->get($fqn));
					$this->checkSpellConstant($co, $fqn);
					return $co;
				}
			}
			
			// Prepend current NS:
			if( $this->curr_pkg->resolver->inNamespace() )
				$fqn = new FullyQualifiedName(
					$this->curr_pkg->resolver->name . "\\" . $name, TRUE);
			else
				$fqn = new FullyQualifiedName($name, TRUE);
			$co = cast(Constant::NAME, $this->constants->get($fqn));
			$this->checkSpellConstant($co, $fqn);
			return $co;
		}
	}


	/**
	 * Search a variable at the specified scope level.
	 * @param string $name Name of the variable.
	 * @param int $scope Scope level. -1 = superglobal, 0 = global,
	 * 1 = function/method, 2+ = nested function (but PHPLint gives error on
	 * nested functions).
	 * @return Variable Resolved variable, or NULL if not found.
	 */
	public function searchVarInScope($name, $scope)
	{
		for($i = $this->vars_n - 1; $i >= 0; $i--){
			$v = $this->vars[$i];
			if( $v !== NULL ){
//				if( $v->scope < $scope )
//					return NULL;
				if( $v->scope == $scope
				&& $v->name === $name
				&& ($scope <= 0 || $v->decl_in->getFile()->equals($this->curr_pkg->fn)) )
					return $v;
			}
		}
		return NULL;
	}



	/**
	 * Search a variable inside the current scope and then between the
	 * superglobals (scope = -1).
	 * @param string $name Name of the variable.
	 * @return Variable Resolved variable, or NULL if not found.
	 */
	public function searchVar($name)
	{
		/* First, search in the current scope: */
		if( $this->curr_pkg === NULL )
			$scope = 0;
		else
			$scope = $this->curr_pkg->scope;
		$v = $this->searchVarInScope($name, $scope);
		if( $v !== NULL )
			return $v;

		/* Next, search between superglobals: */
		return $this->searchVarInScope($name, -1);
	}
	
	
	/**
	 * Checks found function name against the original declared name and
	 * shows differences in spelling of uppercase and lowercase letters.
	 * @param Function_ $f Resolved function, or NULL if not resolved.
	 * @param FullyQualifiedName $found Resolved FQN as found in the source.
	 * @return void
	 */
	private function checkSpellFunction($f, $found)
	{
		if( $f === NULL )
			return;
		if( ! $f->name->equalsCaseSensitive($found) )
			$this->logger->error($this->curr_pkg->scanner->here(),
			"function\n"
			. "\t$found\n"
			. "was declared as\n"
			. "\t$f\n"
			. "that differs by upper/lower-case letters only");
	}
	
	
	/**
	 * Seach a function, resolving its name in the current namespace.
	 * Also checks for mispelled upper/lower-case letters that might
	 * occurs in the reconstructed name.
	 * @param string $name Name as found in the source, that might need to
	 * be resolved in the current namespace context.
	 * @return Function_ Resolved function, or NULL if not found.
	 * @throws \RuntimeException 
	 */
	public function searchFunc($name)
	{
		if( NamespaceResolver::isAbsolute($name) ){
			$fqn = new FullyQualifiedName(substr($name, 1), FALSE);
			return cast(Function_::NAME, $this->functions->get($fqn));
			
		} else if( NamespaceResolver::isIdentifier($name) ){
			if( $this->curr_pkg->resolver->inNamespace() ){
				// In NS. Search in current NS:
				$fqn = new FullyQualifiedName(
					$this->curr_pkg->resolver->name . "\\" . $name, FALSE);
				$f = cast(Function_::NAME, $this->functions->get($fqn));
				if( $f !== NULL ){
					$this->checkSpellFunction($f, $fqn);
					return $f;
				}
			}
			
			// Search in global NS:
			$fqn = new FullyQualifiedName($name, FALSE);
			$f = cast(Function_::NAME, $this->functions->get($fqn));
			$this->checkSpellFunction($f, $fqn);
			return $f;
			
		} else {
			// Qualified name.
			$lead_id_idx = strpos($name, "\\");
			$lead_id = substr($name, 0, $lead_id_idx);
			foreach($this->curr_pkg->resolver->namespace_use as $nsu){
				// FIXME: comparison case insensitive.
				// But also check spelling.
				if( $nsu->alias === $lead_id ){
					$nsu->used++;
					$fqn = new FullyQualifiedName(
						$nsu->target . substr($name, $lead_id_idx), FALSE);
					$f = cast(Function_::NAME, $this->functions->get($fqn));
					if( $f !== NULL ){
						$this->checkSpellFunction($f, $fqn);
						return $f;
					}
				}
			}
			
			// Prepend current NS:
			if( $this->curr_pkg->resolver->inNamespace() )
				$fqn = new FullyQualifiedName(
					$this->curr_pkg->resolver->name . "\\" . $name, FALSE);
			else
				$fqn = new FullyQualifiedName($name, FALSE);
			$f = cast(Function_::NAME, $this->functions->get($fqn));
			$this->checkSpellFunction($f, $fqn);
			return $f;
		}
	}
	
	
	/**
	 * Checks found class name against the original declared name and
	 * shows differences in spelling of uppercase and lowercase letters.
	 * @param ClassType $c Resolved class, or NULL if not resolved.
	 * @param FullyQualifiedName $found Resolved FQN as found in the source.
	 * @return void
	 */
	private function checkSpellClass($c, $found)
	{
		if( $c === NULL )
			return;
		if( ! $c->name->equalsCaseSensitive($found) )
			$this->logger->error($this->curr_pkg->scanner->here(),
			"class\n"
			. "\t$found\n"
			. "was declared as\n"
			. "\t$c\n"
			. "that differs by upper/lower-case letters only");
	}
	
	/*. forward private ClassType function autoload(FullyQualifiedName $fqn); .*/
	
	
	/**
	 * Seach a class, resolving its name in the current namespace.
	 * Also checks for mispelled upper/lower-case letters that might
	 * occurs in the reconstructed name.
	 * @param string $name Name as found in the source, that might need to
	 * be resolved in the current namespace context.
	 * @param boolean $is_fqn If true, assumes the name be already fully
	 * qualified or absolute and does not applies the namespace resolution
	 * algorithm. Set to true only to resolve classes in the magic
	 * <code>cast(T,V)</code>, where <code>T</code> must be resolvable
	 * at runtime outside the current namespace context.
	 * @return ClassType Resolved class, or NULL if not found.
	 */
	public function searchClass($name, $is_fqn = FALSE){
		
		// FIXME: "self" should be lowercase (keyword already checked by Scanner in PHP code, but not in DocBlock and string type)
		if( ! $is_fqn ){
			if( $name === "self" )
				return $this->curr_pkg->curr_class;
			else if( $name === "parent" ){
				$c = $this->curr_pkg->curr_class;
				if( $c === NULL )
					return NULL;
				return $c->extended;
			}
		}
		
		if( $is_fqn || NamespaceResolver::isAbsolute($name) ){
			if( NamespaceResolver::isAbsolute($name) )
				$name = substr($name, 1);
			$fqn = new FullyQualifiedName($name, FALSE);
			$c = cast(ClassType::NAME, $this->classes->get($fqn));
			if( $c === NULL )
				$c = $this->autoload($fqn);
			$this->checkSpellClass($c, $fqn);
			return $c;
			
		} else if( NamespaceResolver::isIdentifier($name) ){
			
			foreach($this->curr_pkg->resolver->namespace_use as $nsu){
				// FIXME: comparison case insensitive.
				// But also check spelling.
				if( $nsu->alias === $name ){
					$nsu->used++;
					$fqn = new FullyQualifiedName($nsu->target, FALSE);
					$c = cast(ClassType::NAME, $this->classes->get($fqn));
					if( $c !== NULL ){
						$this->checkSpellClass($c, $fqn);
						return $c;
					}
					$c = $this->autoload($fqn);
					$this->checkSpellClass($c, $fqn);
					return $c;
				}
			}
			
			if( $this->curr_pkg->resolver->inNamespace() ){
				// In NS. Search in current NS:
				$fqn = new FullyQualifiedName(
					$this->curr_pkg->resolver->name . "\\" . $name, FALSE);
				$c = cast(ClassType::NAME, $this->classes->get($fqn));
				if( $c !== NULL ){
					$this->checkSpellClass($c, $fqn);
					return $c;
				}
				$c = $this->autoload($fqn);
				$this->checkSpellClass($c, $fqn);
				return $c;
			} else {
				// Search in global NS:
				$fqn = new FullyQualifiedName($name, FALSE);
				$c = cast(ClassType::NAME, $this->classes->get($fqn));
				if( $c !== NULL ){
					$this->checkSpellClass($c, $fqn);
					return $c;
				}
				$c = $this->autoload($fqn);
				$this->checkSpellClass($c, $fqn);
				return $c;
			}
			
		} else {
			// Qualified name.
			// Apply "use" statements:
			$lead_id_idx = strpos($name, "\\");
			$lead_id = substr($name, 0, $lead_id_idx);
			foreach($this->curr_pkg->resolver->namespace_use as $nsu){
				// FIXME: comparison case insensitive.
				// But also check spelling.
				if( $nsu->alias === $lead_id ){
					$nsu->used++;
					$fqn = new FullyQualifiedName(
						$nsu->target . substr($name, $lead_id_idx), FALSE);
					$c = cast(ClassType::NAME, $this->classes->get($fqn));
					if( $c !== NULL ){
						$this->checkSpellClass($c, $fqn);
						return $c;
					}
					$c = $this->autoload($fqn);
					$this->checkSpellClass($c, $fqn);
					return $c;
				}
			}
			
			// Apply current NS:
			if( $this->curr_pkg->resolver->inNamespace() ){
				// In NS. Search in current NS:
				$fqn = new FullyQualifiedName(
					$this->curr_pkg->resolver->name . "\\" . $name, FALSE);
				$c = cast(ClassType::NAME, $this->classes->get($fqn));
				if( $c !== NULL ){
					$this->checkSpellClass($c, $fqn);
					return $c;
				}
				$c = $this->autoload($fqn);
				$this->checkSpellClass($c, $fqn);
				return $c;
				
			} else {
				// In global NS. Search in global NS:
				$fqn = new FullyQualifiedName($name, FALSE);
				$c = cast(ClassType::NAME, $this->classes->get($fqn));
				if( $c !== NULL ){
					$this->checkSpellClass($c, $fqn);
					return $c;
				}
				$c = $this->autoload($fqn);
				$this->checkSpellClass($c, $fqn);
				return $c;
			}
		}
	}
	
	
	/**
	 * Checks found method name against the original declared name and
	 * shows differences in spelling of uppercase and lowercase letters.
	 * If the two names does not match exactly in case-sensitive way, an
	 * error is displayed at the current scanner position.
	 * @param ClassMethod $m Resolved method that matches those found in the
	 * source.
	 * @param CaseInsensitiveString $found Name of the method as found in the
	 * source.
	 * @return void
	 */
	public function checkSpellMethod($m, $found)
	{
		if( $m->name->__toString() !== $found->__toString() )
			$this->logger->error($this->curr_pkg->scanner->here(),
			"method"
			. "\t$found\n"
			. "was declared as\n"
			. "\t$m\n"
			. "that differs by upper/lower-case letters only");
	}


	/**
	 * Accounts for usage of a package and checks deprecation.
	 * @param File $fn Package (that is, file) to be accounted.
	 * @return void
	 */
	private function accountPackage($fn)
	{
		$pkg = $this->getPackage($fn);
		if( $pkg === NULL ){
			// FIXME: create a dummy PHPLint built-in package.
			// PHPLint's built-in items are defined in a package that does not
			// exit.
			return;
		}
		if( $pkg !== $this->curr_pkg ){
			$pkg->used++;
			$db = $pkg->docblock;
			if( $db !== NULL && $db->deprecated_descr !== NULL )
				$this->logger->warning($this->curr_pkg->scanner->here(),
				"using deprecated package $fn:\n" . $db->deprecated_descr);
		}
		
	}
	
	
	/**
	 * Adds a new constant.
	 * @param Constant $c 
	 * @return void
	 */
	public function addConstant($c)
	{
		$this->constants->put($c->name, $c);
	}


	/**
	 * Accounts constant usage, checks visibility context and deprecation.
	 * @param Constant $c 
	 * @return void
	 */
	function accountConstant($c)
	{
		$pkg = $this->curr_pkg;
		$c->used++;
		
		if( ! $c->decl_in->getFile()->equals($pkg->fn) ){
			// Access from another pkg.
			$this->accountPackage($c->decl_in->getFile());
			
			if( $c->is_private ){
				$here = $pkg->scanner->here();
				$this->logger->error($here,
				"access forbidden to private constant $c declared in "
				. $this->logger->reference($here, $c->decl_in));
			}
		
			$db = $c->docblock;
			if( $db !== NULL && $db->deprecated_descr !== NULL ){
				$this->logger->warning($pkg->scanner->here(),
				"using deprecated constant $c:\n" . $db->deprecated_descr);
			}
			
		}
	}
	
	
	/**
	 *
	 * @param Variable $v 
	 * @return void
	 */
	public function addVar($v)
	{
		$this->vars[$this->vars_n++] = $v;

		// Check "$this" usage:
		if( $v->name === "this" ){
			$pkg = $this->curr_pkg;
			if( $pkg->curr_method === NULL ){
				$this->logger->error($pkg->scanner->here(),
				"using variable \$this outside of any class method");
			} else if( $pkg->curr_method->is_static ){
				$this->logger->error($pkg->scanner->here(),
				"using variable \$this inside static method "
				. $pkg->curr_method);
			}
		}
	}


	/**
	 * Accounts variable assignment, checks visibility context and deprecation.
	 * @param Variable $v
	 * @return void 
	 */
	function accountVarLHS($v)
	{
		$v->assigned = TRUE;
		$v->assigned_once = TRUE;

		// If declared in "global $v;" statement, update referred global
		// variable too:
		if( $v->is_global ){
			$g = $this->searchVarInScope($v->name, 0);
			$this->accountVarLHS($g);
		}
		
		// If local variable, stop here:
		if( $v->scope >= 1 )
			return;
		
		$pkg = $this->curr_pkg;
		
		// Var in scope <= 0; check visibility and accounts:
		if( ! $v->decl_in->getFile()->equals($pkg->fn) ){
			// Access from another package.
			if( $v->is_private ){
				$here = $pkg->scanner->here();
				$this->logger->error($here,
				"access forbidden to private variable $v declared in "
				. $this->logger->reference($here, $v->decl_in) );
			}

			$this->accountPackage($v->decl_in->getFile());

			// Check deprecation:
			$db = $v->docblock;
			if( $db !== NULL && $db->deprecated_descr !== NULL )
				$this->logger->warning($pkg->scanner->here(),
				"using deprecated variable $v:\n" . $db->deprecated_descr);
		}
	}


	/**
	 * Accounts variable usage, checks visibility context and deprecation.
	 * @param Variable $v
	 * @return void
	 */
	function accountVarRHS($v)
	{
		// Note that this func differs from accountVarLHS() in 2 ways:
		// - increments usage counter but does not sets assigned;
		// - accounts referred global var (if any) as RHS rather than LHS.
		
		$v->used++; // <-- first diff

		// If declared in "global $v;" statement, update referred global
		// variable too:
		if( $v->is_global ){
			$g = $this->searchVarInScope($v->name, 0);
			$this->accountVarRHS($g);  // <-- second diff
		}
		
		// If local variable, stop here:
		if( $v->scope >= 1 )
			return;
		
		$pkg = $this->curr_pkg;
		// Var in scope <= 0; check visibility and accounts:
		if( ! $v->decl_in->getFile()->equals($pkg->fn) ){
			// Access from another package.
			if( $v->is_private ){
				$here = $pkg->scanner->here();
				$this->logger->error($here,
				"access forbidden to private variable $v declared in "
				. $this->logger->reference($here, $v->decl_in) );
			}
			
			$this->accountPackage($v->decl_in->getFile());

			// Check deprecation:
			$db = $v->docblock;
			if( $db !== NULL && $db->deprecated_descr !== NULL )
				$this->logger->warning($pkg->scanner->here(),
				"using deprecated variable $v:\n" . $db->deprecated_descr);
		}
	}


	/**
	 * Accounts access to function, checks visibility context and deprecation.
	 * @param Function_ $f
	 * @return void
	 */
	public function accountFunction($f)
	{
		$pkg = $this->curr_pkg;
		
		if( $f !== $pkg->curr_func )
			$f->used++;
		
		if( ! $f->decl_in->getFile()->equals($pkg->fn) ){
			
			$this->accountPackage($f->decl_in->getFile());
			
			if( $f->is_private ){
				$here = $pkg->scanner->here();
				$this->logger->error($here,
				"access forbidden to private function $f declared in "
				. $this->logger->reference($here, $f->decl_in) );
			}
			
			$db = $f->docblock;
			if( $db !== NULL && $db->deprecated_descr !== NULL ){
				$this->logger->warning($pkg->scanner->here(),
				"using deprecated function $f:\n" . $db->deprecated_descr);
			}
		}
	}


	/**
	 * Accounts class usage, checks visibility context and deprecation.
	 * @param ClassType $c
	 * @return void
	 */
	public function accountClass($c)
	{
		$pkg = $this->curr_pkg;
		if( ! $c->equals($pkg->curr_class) ){
			
			$c->used++;
			
			if( ! $c->decl_in->getFile()->equals($pkg->fn) ){
			
				$this->accountPackage($c->decl_in->getFile());
				
				if( $c->is_private ){
					$here = $pkg->scanner->here();
					$this->logger->error($here,
					"access forbidden to private class $c declared in "
					. $this->logger->reference($here, $c->decl_in) );
				}
		
				$db = $c->docblock;
				if( $db !== NULL && $db->deprecated_descr !== NULL )
					$this->logger->warning($pkg->scanner->here(),
					"using deprecated class $c:\n" . $db->deprecated_descr);
			}
		}
	}
	

	/**
	 * Accounts access to class constant, checks visibility context and
	 * deprecation.
	 * @param ClassConstant $co
	 * @return void
	 */
	public function accountClassConstant($co)
	{
		$pkg = $this->curr_pkg;
		$co->used++;
		$this->accountClass($co->class_);
		
		if( $co->visibility === Visibility::$private_ ){
			if( $co->class_ !== $pkg->curr_class )
				$this->logger->error($pkg->scanner->here(),
				"access forbidden to private class constant $co");
			
		} else {
			// Protected or public.
			
			if( $co->visibility === Visibility::$protected_ ){
				if( $pkg->curr_class === NULL
				|| ! $pkg->curr_class->isSubclassOf($co->class_) )
					$this->logger->error($pkg->scanner->here(),
					"access forbidden to protected class constant $co");
			}
		
			if( ! $co->decl_in->getFile()->equals($pkg->fn) ){
				$db = $co->docblock;
				if( $db !== NULL && $db->deprecated_descr !== NULL ){
					$this->logger->warning($pkg->scanner->here(),
					"using deprecated class constant $co:\n"
					. $db->deprecated_descr);
				}
			}
		}
	}


	/**
	 * Accounts access to property, checks visibility context and deprecation.
	 * @param ClassProperty $p 
	 * @return void
	 */
	public function accountProperty($p)
	{
		$pkg = $this->curr_pkg;
		$p->used++;
		
		// If non-static, no need to account again the class because either
		// the object has been created and then the class accounted, or the
		// object comes from a formal definition by type and, again, the class
		// has already been accounted.
		if( $p->is_static )
			$this->accountClass($p->class_);
		
		if( $p->visibility === Visibility::$private_ ){
			if( $p->class_ !== $pkg->curr_class )
				$this->logger->error($pkg->scanner->here(),
				"access forbidden to private property $p");
			
		} else {
			// Protected or public.
			
			if( $p->visibility === Visibility::$protected_ ){
				if( $pkg->curr_class === NULL
				|| ! $pkg->curr_class->isSubclassOf($p->class_) )
					$this->logger->error($pkg->scanner->here(),
					"access forbidden to protected property $p");
			}
		
			if( ! $p->decl_in->getFile()->equals($pkg->fn) ){
				$db = $p->docblock;
				if( $db !== NULL && $db->deprecated_descr !== NULL ){
					$this->logger->warning($pkg->scanner->here(),
					"using deprecated property $p:\n" . $db->deprecated_descr);
				}
			}
		}
	}


	/**
	 * Accounts access to method, checks visibility context and deprecation.
	 * @param ClassMethod $m 
	 * @return void
	 */
	public function accountMethod($m)
	{
		$pkg = $this->curr_pkg;
		
		if( $m !== $pkg->curr_method )
			$m->used++;
		
		// If non-static, no need to account again the class because either
		// the object has been created and then the class accounted, or the
		// object comes from a formal definition by type and, again, the class
		// has already been accounted.
		if( $m->is_static )
			$this->accountClass($m->class_);
		
		if( $m->visibility === Visibility::$private_ ){
			if( $m->class_ !== $pkg->curr_class )
				$this->logger->error($pkg->scanner->here(),
				"access forbidden to private method $m");
			
		} else {
			// Protected or public.
			
			if( $m->visibility === Visibility::$protected_ ){
				if( $pkg->curr_class === NULL
				|| ! $pkg->curr_class->isSubclassOf($m->class_) )
					$this->logger->error($pkg->scanner->here(),
					"access forbidden to protected method $m");
			}
		
			if( ! $m->decl_in->getFile()->equals($pkg->fn) ){
				$db = $m->docblock;
				if( $db !== NULL && $db->deprecated_descr !== NULL ){
					$this->logger->warning($pkg->scanner->here(),
					"using deprecated method $m:\n" . $db->deprecated_descr);
				}
			}
		}
	}
	
	
	/**
	 * Returns true if "$this" does exist (that is, we are inside a non-static
	 * method) and it is subclass of the class $c. If this is true, the
	 * non-static resolution operator can be applied also to non-static members.
	 * @param ClassType $c
	 * @return boolean
	 */
	public function isNonStaticContextOf($c)
	{
		$pkg = $this->curr_pkg;
		return $pkg->curr_method !== NULL
			&& ! $pkg->curr_method->is_static
			&& $pkg->curr_class->isSubclassOf($c);
	}
	

	/**
	 * Remove all the vars of the current scope, current package, typically
	 * exiting from a function or method.
	 * @return void
	 */
	public function cleanCurrentScope()
	{
		$scope = $this->curr_pkg->scope;
		if( $scope <= 0 )
			// Globals and superglobals are never deleted.
			return;
		$i = $this->vars_n - 1;
		$fn = $this->curr_pkg->fn;
		$print_notices = $this->logger->print_notices;
		while( $i >= 0 ){
			$v = $this->vars[$i];
			if( $v !== NULL
			&& $v->scope == $scope
			&& $v->decl_in->getFile()->equals($fn) ){
				if( $print_notices ){
					if( $v->used == 0 ){
						if( $v->is_global ){
							if( ! $v->assigned_once ){
								$this->logger->notice($v->decl_in,
								"variable $v declared global never used");
							}
						} else if( $v->is_private || $v->scope > 0 ){
							$this->logger->notice($v->decl_in,
							"variable $v assigned but never used");
						}
					}
				}
				$this->vars[$i] = NULL;
			}
			$i--;
		}
		
		// Remove trailing NULL entries from $this->vars[]:
		$i = $this->vars_n;
		while( $i > 0 && $this->vars[$i-1] === NULL )
			$i--;
		$this->vars_n = $i;
	}
	
	
	/**
	 * Load a package or a module. Exceptions thrown by the scanner and by the
	 * parser are captured here and logged as fatal errors.
	 * @param File $fn File of the package or module.
	 * @param boolean $is_module True if this package is loaded from
	 * <code>require_module</code> or user set the <code>--is-module</code>
	 * command line flag; false if <code>require_once</code>.
	 * @return void 
	 */
	public abstract function loadPackage($fn, $is_module);
	
	
	/**
	 * Attempts to perform class autoloading if the <code>__autoload()</code>
	 * function has been already parsed. Errors are reported as usual.
	 * @param FullyQualifiedName $fqn FQN of the class to load.
	 * @return ClassType Resolved class, or NULL if it fails for any reason.
	 */
	private function autoload($fqn)
	{
		if( $this->autoload_function === NULL )
			return NULL;
		$this->accountFunction($this->autoload_function);
		try {
			$s = $this->autoload_prepend->getLocaleEncoded() . "/"
				. (string) str_replace("\\", $this->autoload_separator,
						$fqn->__toString())
				. $this->autoload_append;
		}
		catch(IOException $e){
			// Invalid encoding resulting from the composition of the
			// path + class name:
			$this->logger->error($this->curr_pkg->scanner->here(),
				$e->getMessage());
			return NULL;
		}
		
		$f = File::fromLocaleEncoded($s);
//		$this->logger->error($this->curr_pkg->scanner->here(),
//		"autoloading class $fqn from package $f");
		$this->loadPackage($f, FALSE);
		$pkg = $this->getPackage($f);
		if( $pkg === NULL )
			// Any attempt to load the pkg failed. Caller will display
			// an "unknown type" error.
			return NULL;
		if( ! $pkg->is_library ){
			$this->logger->error($this->curr_pkg->scanner->here(),
			"autoloaded package " . $this->logger->formatFileName($f)
			." is not a library:\n" . $pkg->why_not_library);
		}
		return $this->getClass($fqn);
	}


}

Globals::static_init();
