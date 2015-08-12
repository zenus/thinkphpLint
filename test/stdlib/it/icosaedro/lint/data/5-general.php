<?php

/**
 * PHPLint Test Program
 * @package PHPLintTest
 * @author Umberto Salsi
 * @copyright 2005 Umberto Salsi
 * @license http://www.icosaedro.it/phplint/manual.html?p=license BSD-style
 */


/*.
	require_module 'standard';
.*/

/* literals: */

define("BOOL1",   false || true && (1&2^3|4) == 0);
define("INT1",     123*4.3);
define("INT2",    +123);
define("INT3",    -123);
define("INT4",    012);
define("INT5",    0x1Ff120);
define("FLOAT1",  1.234 + 12.34 * (-6e9) / (0.5e-34));
define("STRING1", "abc" . 'abc' . "a\000b\0x85");
define("STRING2", <<< EOT
EOT
. <<<EOT
Just a line.
EOT
. <<< EOT
Just two
lines.
EOT
);
define("STRING3", /*.(string).*/ NULL);

/* arrays: */
$arr0 = array();
$arr1 = array(1, 2, 3);
$arr2 = array("a", "b");
$arr3 = array(1=>111, 222, 3=>333);
$arr4 = array("a"=>"x", "b"=>"x"); # warn
$arr1[] = 4;
$arr1[4] = 4;
$arr2[4] = "d";
$arr3[4] = 4;
$arr4["4"] = "z";
$undef_arr1[1]["sss"] = 111;
$undef_arr2[1][123] = "aaa";
$i = $arr1[1]++ + ++$arr1[1];


/* operators: */
$i = 0;
$i++;
++$i;
$arr1[0]++;
++$arr1[0];


/* control structures: */

if( true );
if( true ) ; else ;
if( true ) ; elseif( false ) ;
if( true ) ; elseif( false ) ; else ;
while( false ) ;
do break; while( false ) ;
for(;;) break;
for($i = 0; $i < 10; $i++) {break;}
for($i = 0, $j = 1;  $k = 2, $i < 10;  $i++) ;
foreach($arr2 as $v) continue;
foreach($arr2 as $k => $v) continue;
switch(1){
case 1: break;
case 2:
case 3: echo "hello";  break;
default:
}

switch(1){
	case 1:
	case 2:
	case 1:
		echo 1;
		break;
	case "1":
		echo 1;
}

# Test double quoted strings with embedded variables:
$s = "" . "$i" . "@$i" . "$i@" . "$i$i$i" . "$i@$i@$i";

# Test here-doc with embedded vars:
$s = <<< XXX
$i
XXX
. <<< XXX
   $i
XXX
. <<< ZZZ
$i $i
text
ZZZ;

# Test here-doc with double-quoted label:
$s = <<< "XXX"
$i
XXX;

# Test now-doc:
$s = <<< 'XXX'
?> ?> <? <?
XXX;
$s = <<< 'XXX'
XXX;
$s = <<< 'XXX'
XXX
;


/*. int .*/ function size_of_int()
{
	$n = 1;
	$x = 1;
	while( is_int($x) ){ $n++; $x*=2; }
	return $n;
}
echo "size of int = ", size_of_int(), " bits\n";

if(true){
	exit;
	exit();
	exit(0);
	exit("xyz");
	die;
	die();
	die(0);
	die("xyz");
}


/* Passing arguments by ref.: */

/*. void .*/ function set_string(/*. string .*/ &$s)
{
	$s = "hello";
	?>hello, world, today is <?= date("c") ?>

<?
}

$s = "";
set_string($s);
set_string($undef_string);
set_string($undef_array[2]);

class A extends stdClass
{
	public $x = 0;
	public static $y = 0;

	public /*. void .*/ function by_ref(/*. int .*/ & $i)
	{  $i=123; }

	public /*. void .*/ function call_by_ref()
	{
		self::by_ref($this->x);
		echo "x=", $this->x, "\n";
		self::by_ref(self::$y);
		echo self::$y;
	}

	public /*. void .*/ function new_self_parent()
	{
		$a = new self;
		$b = new parent;
		$c = new self();
		$d = new parent();
	}

	public /*. void .*/ function self_parent_args(
		/*. self .*/ $o1,
		/*. parent .*/ $o2,
		self $o3,
		parent $o4)
	{}
}

class B
{
	public $x = 0;
}

$a = new A(); $a->x = 123;
$b = new A(); $b->x = 123;
if( $a === $b ) ;
if( $a instanceof A ) ;
if( ! $a instanceof B ) ;
$a->call_by_ref();
$a_clone = clone $a;

/* ****
$fr = pg_connect("dbname=icodb");
$to1 = (boolean) $fr;
$to2 = (int) $fr;
$to3 = (float) $fr;
$to4 = (string) $fr;
#$to5 = (array) $fr;
****/

/* Cmp ops: */

if( 1 < 2 || 1 <= 2 || 1 == 2 || 1 === 2 || 1 >= 2
	|| "a"==="b" || "a" !== "b" ) ;

if( 1 < 2 || 0.5 >= 3.0 && "012" === "890"
	|| 12 != 34 || 12 !== 34 || 1 === 3 and true or false xor true) ;

# All the valid combinations of visibility, static and final attributes:
final class Z {
	public /*.int.*/ $a;
	public static $b = 0;
	static $c = 0;
	static public $d = 0;

	public /*. void .*/ function f(){}
	public static /*. void .*/ function g(){}
	public final /*. void .*/ function h(){}
	static /*. void .*/ function i(){}
	static public /*. void .*/ function j(){}
	static final /*. void .*/ function k(){}
	static public final /*. void .*/ function l(){}
	static final public /*. void .*/ function m(){}
	final /*. void .*/ function n(){}
	final public /*. void .*/ function o(){}
	final static /*. void .*/ function p(){}
	final public static /*. void .*/ function q(){}
	final static public /*. void .*/ function r(){}
}

# Exceptions:
class MyExc extends ErrorException {}
try {
   $error = 'Always throw this error';
   throw new Exception($error, 10);
   throw new MyExc($error);
   throw new MyExc();
}
catch (Exception $e) {
   echo 'Caught exception: ',  $e->getMessage(), "\n";
}
catch (MyExc $e) {
   echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/* Type hinting: */
/*. void .*/ function type_hinting(A $o){}
type_hinting(new A());


/* Typecasting: */

$tc1 = FALSE or (boolean) 1;
$tc2 = 1 + (int) "123";
$tc3 = 0.0 + (float) "3.14";
$tc4 = "abc" . (string) 123;
$tc5 = /*. (array[int]string) .*/ array();  $tc5[1] = "abc";
$tc6 = /*. (resource) .*/ count_chars("abc");
#$tc7 = /*. (object) .*/ new A();
#$tc8 = /*. (A) .*/ new A();


/*. void .*/ function LoginMask(/*. string .*/ $name)
{
	?>
	<html><body>
	<form method=post action="<?= $_SERVER['PHP_SELF'] ?>">
	</form>
	</body></html>
	<?
}


/* Error handling: */

$file = @fopen("text.php", "r");


class TestStaticExpr
{
	const a = FALSE,
		b = NULL,
		c = 123,
		d = -123,
		e = "hello",
		f = self::a,
	#   g = array("one", "two"),
	#      note: array() not allowed in class constants
		h = 3.141592e-4;

	public $a = FALSE,
		$b = NULL,
		$c = 123,
		$d = -123,
		$e = "hello",
		$f = self::a,
		$g = array("one", "two"),
		$h = 3.141592e-4;

	/*. void .*/ function f($a1=FALSE, $a2=NULL, $a3=-123,
		$a5="hello", $a6=self::a, $a7=array("one", "two"), $a8=3.141592e-4)
	{
		static $a = FALSE,
		       $b = NULL,
		       $c = 123;
		static $d = -123;
		static $e = "hello";
		static $f = self::a;
		static $g = array("one", "two");
		static $h = 3.141592e-4;
	}
}

class SpecialMethods {
	/*. void .*/ function __destruct(){}
	public /*. void .*/  function __clone(){}
	static  /*. void .*/  function __set_static(/*. array[string]mixed .*/ $a){}
	public  /*. array[int]string .*/  function __sleep(){}
	public  /*. void .*/  function __wakeup(){}
	public  /*. string.*/ function __toString(){}
	#public  /*. void .*/  function __set(/*.string.*/ $n, /*. mixed .*/ $v){}
	#public  /*. mixed .*/ function __get(/*.string.*/ $n){}
	#public  /*. bool .*/  function __isset(/*.string.*/ $n){}
	#public  /*. void .*/  function __unset(/*.string.*/ $n){}
	#public  /*. mixed .*/  function __call(/*.string.*/ $n, /*. array[]mixed .*/ $a){}
}


$last_exception1 = /*. (Exception) .*/ NULL;
/*. Exception .*/ $last_exception2 = NULL;
/** @var Exception */
$last_exception3 = NULL;


/*
	phpDocumentor DocBlocks
*/

# Empty DocBlocks:
/***/  # <== empty DocBlock
/** */
/**
*/
/** * */
/**
 *
 */

/**
* short short
* long long
* long long
* long long
* long long
*/
$dummy10 = 1;

/**
* short short
* short short.
* long long
* long long
* long long
*/
$dummy11 = 1;

/**
* short short
* short short
* short short.
* long long
* long long
*/
$dummy12 = 1;

/**
* short short
*
* long long
*/
$dummy13 = 1;

/**
* short short
* short short
*
* long long
*/
$dummy14 = 1;

/**
* short short
* short short
* short short
*
* long long
*/
$dummy15 = 1;

/**
* short short
* long long
* long long
* long long
*
* long long
*/
$dummy16 = 1;

/**
* <b>bold</b> in short.
* <b>bold</b> in long.
*/
$dummy17 = 1;

/**
* Testing all the tags:
*
* ATTENTION<br>
* <b>bold</b> <i>italic</i> <code>Code</code> [BR here:]<br>
* [P here:]<p>
* <pre>
* while( $i &gt;= 0 )
*     $i--;
* </pre>
* <ul> <li><b>first bolded</b></li> <li>sublist:<ul><li>second</li></ul></li> <li>...and last</li> </ul>
* <ol> <li>first</li> <li>second</li> <li>...and last</li> </ol>
* <b><i>bold+italic</i></b>
*/
$dummy18 = 1;

/**#@+ FIXME: incomplete support for templates, now parsed but ignored */
/**#@-*/

/**
 * A constant giving the number of days in a week
 */
define("WEEK_DAYS", 7);

/**
 * Short description. This is the long long long description.
 *
 * @var array
 */
$emptyArray = array();

/**
 * Another array
 *
 * @var array[int]string|int|FALSE
 */
$emptyArray2 = array("hello");

/**
 * Another array
 *
 * @var array[int][string]string
 */
$emptyArray3 = array( array("s"=>"s") );

/**
 * Simple test for docBlock
 *
 * This text follows an empty line, so it is moved to the long descr.
 * @param int       $len
 * @param string    $str
 * @param Exception & $obj  bla bla bla
 * @return bool
 * @author Umberto Salsi <phplint@icosaedro.it>
 */
function TestDocBlock($len, $str, &$obj)
{
	return strlen($str) > $len;
}

/**
 * Classic test class
 *
 */
class docBlockCommentedClass {

	/**
	 * The second integer number
	 */
	const TWO = 2;

	/**
	 * @var int
	 */
	public $intProp;

	/**
	 * Useless method
	 *
	 * Use this method to do nothing in a simple, efficient way.
	 *
	 * @param resource $fd
	 * @param string $name
	 * @return bool
	 */
	function aMethod($fd, $name){}

}


/* Abstract classes: */
abstract class Shape
{
	const DEF_SCALE = 1.0;

	public $x = 0.0, $y = 0.0;

	/*. void .*/ function moveTo(/*. float .*/ $x, /*. float .*/ $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

	abstract /*. void .*/ function scale(/*. float .*/ $factor) ;
}


class Circle extends Shape
{
	public $radius = 1.0;

	/*. void .*/ function scale(/*. float .*/ $factor)
	{
		$this->radius *= $factor;
	}
}

class Rectangle extends Shape
{
	public $side_a = 1.0, $side_b = 2.0;

	/*. void .*/ function scale(/*. float .*/ $factor)
	{
		$this->side_a *= $factor;
		$this->side_b *= $factor;
	}
}

$drawing = /*. (array[int]Shape) .*/ array();

/*. void .*/ function scale_shapes(/*. float .*/ $factor)
{
	foreach($GLOBALS['drawing'] as $shape)
		$shape->scale($factor);
}


$drawing[] = new Circle();
$drawing[] = new Circle();
$drawing[] = new Rectangle();
scale_shapes(100.0);



/* Interface test: */

interface DataContainer
{
	/*. void .*/ function set(
		/*. string .*/ $name,
		/*. string .*/ $value);

	/*. string .*/ function get(/*. string .*/ $name);
}


class FileBasedContainer implements DataContainer
{
	private $base_dir = "";

	/*. void .*/ function __construct(/*. string .*/ $base_dir)
	{
		$this->base_dir = $base_dir;
	}

	/*. void .*/ function set(
		/*. string .*/ $name,
		/*. string .*/ $value)
	{
		@file_put_contents($this->base_dir ."/". $name, $value);
	}

	/*. string .*/ function get(/*. string .*/ $name)
	{
		return @file_get_contents($this->base_dir ."/". $name);
	}
}


/*. void .*/ function save_data(/*. array[string]mixed .*/ $arr, /*. DataContainer .*/ $c)
{
	foreach($arr as $k => $v)
		$c->set($k, serialize($v));
}

save_data( array("one"=>1, "two"=>2), new FileBasedContainer("/tmp") );

/**
Check DocBlock Parser.
@param int $a
@param int $b
@return void
*/
function docblock_f1($a, $b){}


/**
Check DocBlock Parser.
@param int $a
@param int $b
@return void
*/
function docblock_f2($a){}


/**
Check DocBlock Parser.
@param int $a
@param int $b
@return void
*/
function docblock_f3($a, $b, $c){}


/**
Check DocBlock Parser.
@param int $a
@param int $b
@param int $c
@return void
*/
function docblock_f4($a, $b){}


/**
Check DocBlock Parser.
@param int $a
@param int $z
@return void
*/
function docblock_f5($a, $b){}


/**
Check DocBlock Parser.
@param int $a
@param int $b
@return void
*/
function docblock_f6($a, $b){ return 1; }


/**
Check DocBlock Parser.
@param int $a
@param int $b
@return void
*/
/*. int .*/ function docblock_f7(/*. float .*/ $a, /*. float .*/ $b){ return 1; }
		
		
// Array short syntax - static expression:
function array_short_syntax($a = [], $b = ["a"], $c = [1, 2]){}

// Array short syntax - non-static expression:
$array_short_syntax_1 = [];  if($array_short_syntax_1);
$array_short_syntax_2 = ["a"];  if($array_short_syntax_2);
$array_short_syntax_3 = [1, 2];  if($array_short_syntax_3);

// ClassName::class special constant:
const X = Exception::class;
echo Exception::class, X, "\n";

?>   <?= "...", "..."  ?> PHP execution terminated.
