<?php

namespace it\icosaedro\lint;
require_once __DIR__ . "/../../../all.php";

/**
 * Symbols returned by the PHPLint scanner. Every property is a reference to
 * an instance of this class that represents that symbol. Properties whose name
 * starts with <code>$sym_x_</code> represent PHPLint meta-code keywords and
 * symbols that may appear only inside specially formatted multi-line comments
 * <code>/&#42;. .&#42;/</code> and are not actual PHP symbols.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/02/24 20:02:14 $
 */
class Symbol extends Enum {

	/**
	 * End of the source file reached or I/O error. In this latter case, the
	 * Scanner already reported through the logger the specific error message
	 * detected while reading the file.
	 */
	public static /*. Symbol .*/ $sym_eof;
	
	/**
	 * For internal use of the Scanner; never really returned to client code.
	 */
	public static /*. Symbol .*/ $sym_unknown;
		
	/**
	 * Unimplemented PHP keyword: enddeclare, endwhile, endfor, endswitch,
	 * endif, endforeach.
	 */
	public static /*. Symbol .*/ $sym_unimplemented_keyword;

	/**
	 * Text outside PHP code, normally HTML code.
	 */
	public static /*. Symbol .*/ $sym_text;
	
	/**
	 * PHP open tag <code>&lt;?</code> or <code>&lt;php</code>.
	 */
	public static /*. Symbol .*/ $sym_open_tag;
	
	/**
	 * PHP open tag <code>&lt;?=</code>.
	 */
	public static /*. Symbol .*/ $sym_open_tag_with_echo;
	
	/**
	 * PHP close tag <code>?&gt;</code>.
	 */
	public static /*. Symbol .*/ $sym_close_tag;

	public static /*. Symbol .*/ $sym_define, # define
	$sym_function, # function
	$sym_declare, # declare
	$sym_lbrace, # {
	$sym_rbrace, # }
	$sym_lround, # (
	$sym_rround, # )
	$sym_lsquare, # [
	$sym_rsquare, # ]
	$sym_comma, # ,

	$sym_semicolon, # ;
	$sym_colon, # :
	$sym_double_colon, # ::
	$sym_variable, # a variable "$name"; s="name"
	$sym_for, # for
	$sym_foreach, # foreach
	$sym_as, # as
	$sym_while, # while
	$sym_if, # if
	$sym_else, # else
	$sym_elseif, # elseif
	$sym_return, # return
	$sym_identifier, # s="theid"
	$sym_question; # ?

	/**
	 * Assignment <code>=</code> and assignment by reference
	 * <code>=&amp;</code>.
	 */
	public static /*. Symbol .*/
	$sym_assign;
	
	public static /*. Symbol .*/
	$sym_rarrow, # =>
	$sym_single_quoted_string; # s="xyz" or here-doc <<<

	/**
	 * Double quoted string. A sequence of symbols may follow if there are
	 * embedded variables: the string to the left of the variable
	 * is returned as $sym_double_quoted_string, the variable is returned
	 * as $sym_embedded_variable, then the rest of the string to the right
	 * of the variable is returned as $sym_continuing_double_quoted_string.
	 * ONLY SIMPLE VARS SUPPORTED, not array or braces.
	 */
	public static /*. Symbol .*/ $sym_double_quoted_string;
	
	/**
	 * Variable embedded inside a string.
	 */
	public static /*. Symbol .*/ $sym_embedded_variable;
	
	/**
	 * String to the right of the variable embedded in a double-quoted string.
	 */
	public static /*. Symbol .*/ $sym_continuing_double_quoted_string;
	
	public static /*. Symbol .*/ $sym_here_doc, # here-doc <<< ID

	$sym_lit_int, # literal integer number, s="1234"
	$sym_lit_float, # literal float number, s="3.14"
	$sym_inf, # INF (infinity)
	$sym_nan, # NAN (not a number)
	$sym_plus, # +
	$sym_minus, # -
	$sym_times, # *
	$sym_div, # /

	$sym_eq, # ==
	$sym_eeq, # ===
	$sym_ne, # !=
	$sym_nee, # !==
	$sym_gt, # >
	$sym_ge, # >=
	$sym_lt, # <
	$sym_le, # <=
	$sym_incr, # ++
	$sym_decr, # --
	$sym_not, # !
	$sym_or, # ||
	$sym_or2, # or

	$sym_and, # &&
	$sym_and2, # and
	$sym_at, # @
	$sym_period, # . (string concat. op.)
	$sym_arrow, # ->
	$sym_global, # global
	$sym_null, # null NULL
	$sym_boolean, # boolean or bool
	$sym_false, # false
	$sym_true, # true
	$sym_int, # int or integer
	$sym_float, # float or double or real
	$sym_string, # string
	$sym_array, # array
	$sym_object, # object
	$sym_bit_or, # |
	$sym_bit_and, # & (binary bitwise operator and unary reference operator)
	$sym_mod, # %
	$sym_period_assign, # .=
	$sym_plus_assign, # +=
	$sym_minus_assign, # -=
	$sym_times_assign, # *=
	$sym_div_assign, # /=
	$sym_mod_assign, # %=
	$sym_bit_and_assign, # &=
	$sym_bit_or_assign, # |=
	$sym_bit_xor_assign, # ^=
	$sym_lshift_assign, # <<=
	$sym_rshift_assign, # >>=
	$sym_lshift, # <<
	$sym_rshift, # >>
	$sym_bit_xor, # ^
	$sym_xor, # xor

	$sym_bit_not, # ~
	$sym_abstract, # abstract
	$sym_interface, # interface
	$sym_class, # class
	$sym_extends, # extends
	$sym_implements, # implements
	$sym_const, # const
	$sym_var, # var
	$sym_public, # public
	$sym_private, # private
	$sym_protected, # protected
	$sym_static, # static
	$sym_final, # final
	$sym_self, # self
	$sym_parent, # parent
	$sym_new, # new
	$sym_clone, # clone
	$sym_instanceof, # instanceof
	$sym_list, # list
	$sym_switch, # switch
	$sym_case,

	$sym_break, # break
	$sym_default, # default
	$sym_exit, # exit or die
	$sym_echo, # echo
	$sym_print, # print
	$sym_trigger_error, # trigger_error
	$sym_do, # do
	$sym_try, # try
	$sym_catch, # catch
	$sym_finally, # finally
	$sym_throw, # throw

	$sym_continue,
	$sym_isset,
	$sym_include,
	$sym_include_once,
	$sym_require,
	$sym_require_once,

	$sym_namespace, # namespace
	$sym_use, # use
	$sym_goto, # use

	/* PHPLint extended syntax symbols, aka "meta-code".
	   These symbols MUST appear inside /&42;. .&42;/ : */
	$sym_x_require_module, # require_module
	$sym_x_single_quoted_string, # 'abcd'
	$sym_x_semicolon, # ;
	$sym_x_colon, # :
	$sym_x_comma, # ,
	$sym_x_assign, # =

	/* Forward declarations of functions and classes: */
	$sym_x_forward, # forward
	$sym_x_function, # function
	$sym_x_class, # class
	$sym_x_extends, # extends
	$sym_x_implements, # implements
	$sym_x_const, # const
	$sym_x_interface, # interface
	$sym_x_unchecked, # unchecked

	/* x-Types: */
	$sym_x_void, # void
	$sym_x_boolean, # "boolean" or "bool"
	$sym_x_int, # int
	$sym_x_float, # float
	$sym_x_string, # string
	$sym_x_array, # array
	$sym_x_mixed, # mixed
	$sym_x_resource, # resource
	$sym_x_object, # object
	
	$sym_x_identifier, # s="theID"
	$sym_x_variable, # a variable "$name"; s="name"
	$sym_x_bit_and, # "&" pass by reference symbol in func. proto.
	$sym_x_args, # args
	$sym_x_lround, # (
	$sym_x_rround, # )
	$sym_x_lsquare, # [
	$sym_x_rsquare, # ]
	$sym_x_lbrace, # {
	$sym_x_rbrace, # }
	$sym_x_pragma; # pragma
		
	/**
	 * Reserved meta-code keyword <code>if_php_ver_4</code> that marks the
	 * beginning of a section of code for PHP 4 only. Used in modules that
	 * have to be shared by PHP 4 and 5 but with some minor differences.
	 */
	public static /*. Symbol .*/ $sym_x_if_php_ver_4;
		
	/**
	 * Reserved meta-code keyword <code>if_php_ver_5</code> that marks the
	 * beginning of a section of code for PHP 5 only. Used in modules that
	 * have to be shared by PHP 4 and 5 but with some minor differences.
	 */
	public static /*. Symbol .*/ $sym_x_if_php_ver_5;
	
	/**
	 * Reserved meta-code keyword <code>else</code> that marks the beginning
	 * of an alternative section of code in PHP modules shared between PHP 4
	 * and PHP 5. Example: <code>if_php_ver_4  PHP_4_DECLS_HERE else
	 * PHP_5_DECLS_HERE</code>.
	 */
	public static /*. Symbol .*/ $sym_x_else;
	
	/**
	 * Reserved meta-code keyword <code>end_if_php_ver</code>.
	 */
	public static /*. Symbol .*/ $sym_x_end_if_php_ver;
	
	/**
	 * Meta-code keyword <code>missing_break</code>.
	 */
	public static /*. Symbol .*/ $sym_x_missing_break;
	
	/**
	 * Meta-code keyword <code>missing_default</code>.
	 */
	public static /*. Symbol .*/ $sym_x_missing_default;

	public static /*. Symbol .*/ $sym_x_public, # public (PHP4)
	$sym_x_protected, # protected (PHP4)
	$sym_x_private, # private (PHP4)
	$sym_x_abstract, # abstract (PHP4)
	$sym_x_static, # static (PHP4)
	$sym_x_final, # final (PHP4)
	$sym_x_self, # self
	$sym_x_parent, # parent
	$sym_x_triggers, # triggers
	$sym_x_throws, # throws
	$sym_x_return, # return
	$sym_x_namespace; # namespace

	/** Templating <code>&lt;</code> (NOT IMPLEMENTED YET). */
	public static /*. Symbol .*/ $sym_x_lt; # <
	/** Templating <code>&gt;</code> (NOT IMPLEMENTED YET). */
	public static /*. Symbol .*/ $sym_x_gt; # >

	/**
	 * Old documentation system of PHPLint. The scanner always gives error
	 * and does not return this symbol anymore.
	 */
	public static /*. Symbol .*/ $sym_x_doc;
	
	/**
	 * DocBlock multi-line comment.
	 */
	public static /*. Symbol .*/ $sym_x_docBlock;
	
	private /*. string .*/ $name;
	
	public /*. void .*/ function __construct(/*. string .*/ $name){
		$this->name = $name;
	}
	
	
	public /*. string .*/ function __toString(){
		return $this->name;
	}
	
}


Symbol::$sym_eof = new Symbol("sym_eof");
Symbol::$sym_unknown = new Symbol("sym_unknown");
Symbol::$sym_unimplemented_keyword = new Symbol("sym_unimplemented_keyword");

Symbol::$sym_text = new Symbol("sym_text");
Symbol::$sym_open_tag = new Symbol("sym_open_tag");
Symbol::$sym_open_tag_with_echo = new Symbol("sym_open_tag_with_echo");
Symbol::$sym_close_tag = new Symbol("sym_close_tag");

Symbol::$sym_define = new Symbol("sym_define");
Symbol::$sym_function = new Symbol("sym_function");
Symbol::$sym_declare = new Symbol("sym_declare");
Symbol::$sym_lbrace = new Symbol("sym_lbrace");
Symbol::$sym_rbrace = new Symbol("sym_rbrace");
Symbol::$sym_lround = new Symbol("sym_lround");
Symbol::$sym_rround = new Symbol("sym_rround");
Symbol::$sym_lsquare = new Symbol("sym_lsquare");
Symbol::$sym_rsquare = new Symbol("sym_rsquare");
Symbol::$sym_comma = new Symbol("sym_comma");

Symbol::$sym_semicolon = new Symbol("sym_semicolon");
Symbol::$sym_colon = new Symbol("sym_colon");
Symbol::$sym_double_colon = new Symbol("sym_double_colon");
Symbol::$sym_variable = new Symbol("sym_variable");
Symbol::$sym_for = new Symbol("sym_for");
Symbol::$sym_foreach = new Symbol("sym_foreach");
Symbol::$sym_as = new Symbol("sym_as");
Symbol::$sym_while = new Symbol("sym_while");
Symbol::$sym_if = new Symbol("sym_if");
Symbol::$sym_else = new Symbol("sym_else");
Symbol::$sym_elseif = new Symbol("sym_elseif");
Symbol::$sym_return = new Symbol("sym_return");
Symbol::$sym_identifier = new Symbol("sym_identifier");
Symbol::$sym_question = new Symbol("sym_question");

Symbol::$sym_assign = new Symbol("sym_assign");
Symbol::$sym_rarrow = new Symbol("sym_rarrow");
Symbol::$sym_single_quoted_string = new Symbol("sym_single_quoted_string");

/*
	DOUBLE QUOTED STRINGS can produce a sequence of symbols if they
	contain embedded variables: the string to the left of the var.
	is returned as Symbol::$sym_double_quoted_string = new Symbol("sym_double_quoted_string");
	as Symbol::$sym_embedded_variable = new Symbol("sym_embedded_variable");
	of the variable is returned as $sym_continuing_double_quoted_string.
*/
Symbol::$sym_double_quoted_string = new Symbol("sym_double_quoted_string");
Symbol::$sym_embedded_variable = new Symbol("sym_embedded_variable");
Symbol::$sym_continuing_double_quoted_string = new Symbol("sym_continuing_double_quoted_string");
Symbol::$sym_here_doc = new Symbol("sym_here_doc");

Symbol::$sym_lit_int = new Symbol("sym_lit_int");
Symbol::$sym_lit_float = new Symbol("sym_lit_float");
Symbol::$sym_inf = new Symbol("sym_inf");
Symbol::$sym_nan = new Symbol("sym_nan");
Symbol::$sym_plus = new Symbol("sym_plus");
Symbol::$sym_minus = new Symbol("sym_minus");
Symbol::$sym_times = new Symbol("sym_times");
Symbol::$sym_div = new Symbol("sym_div");

Symbol::$sym_eq = new Symbol("sym_eq");
Symbol::$sym_eeq = new Symbol("sym_eeq");
Symbol::$sym_ne = new Symbol("sym_ne");
Symbol::$sym_nee = new Symbol("sym_nee");
Symbol::$sym_gt = new Symbol("sym_gt");
Symbol::$sym_ge = new Symbol("sym_ge");
Symbol::$sym_lt = new Symbol("sym_lt");
Symbol::$sym_le = new Symbol("sym_le");
Symbol::$sym_incr = new Symbol("sym_incr");
Symbol::$sym_decr = new Symbol("sym_decr");
Symbol::$sym_not = new Symbol("sym_not");
Symbol::$sym_or = new Symbol("sym_or");
Symbol::$sym_or2 = new Symbol("sym_or2");

Symbol::$sym_and = new Symbol("sym_and");
Symbol::$sym_and2 = new Symbol("sym_and2");
Symbol::$sym_at = new Symbol("sym_at");
Symbol::$sym_period = new Symbol("sym_period");
Symbol::$sym_arrow = new Symbol("sym_arrow");
Symbol::$sym_global = new Symbol("sym_global");
Symbol::$sym_null = new Symbol("sym_null");
Symbol::$sym_boolean = new Symbol("sym_boolean");
Symbol::$sym_false = new Symbol("sym_false");
Symbol::$sym_true = new Symbol("sym_true");
Symbol::$sym_int = new Symbol("sym_int");
Symbol::$sym_float = new Symbol("sym_float");
Symbol::$sym_string = new Symbol("sym_string");
Symbol::$sym_array = new Symbol("sym_array");
Symbol::$sym_object = new Symbol("sym_object");
Symbol::$sym_bit_or = new Symbol("sym_bit_or");
Symbol::$sym_bit_and = new Symbol("sym_bit_and");
Symbol::$sym_mod = new Symbol("sym_mod");
Symbol::$sym_period_assign = new Symbol("sym_period_assign");
Symbol::$sym_plus_assign = new Symbol("sym_plus_assign");
Symbol::$sym_minus_assign = new Symbol("sym_minus_assign");
Symbol::$sym_times_assign = new Symbol("sym_times_assign");
Symbol::$sym_div_assign = new Symbol("sym_div_assign");
Symbol::$sym_mod_assign = new Symbol("sym_mod_assign");
Symbol::$sym_bit_and_assign = new Symbol("sym_bit_and_assign");
Symbol::$sym_bit_or_assign = new Symbol("sym_bit_or_assign");
Symbol::$sym_bit_xor_assign = new Symbol("sym_bit_xor_assign");
Symbol::$sym_lshift_assign = new Symbol("sym_lshift_assign");
Symbol::$sym_rshift_assign = new Symbol("sym_rshift_assign");
Symbol::$sym_lshift = new Symbol("sym_lshift");
Symbol::$sym_rshift = new Symbol("sym_rshift");
Symbol::$sym_bit_xor = new Symbol("sym_bit_xor");
Symbol::$sym_xor = new Symbol("sym_xor");

Symbol::$sym_bit_not = new Symbol("sym_bit_not");
Symbol::$sym_abstract = new Symbol("sym_abstract");
Symbol::$sym_interface = new Symbol("sym_interface");
Symbol::$sym_class = new Symbol("sym_class");
Symbol::$sym_extends = new Symbol("sym_extends");
Symbol::$sym_implements = new Symbol("sym_implements");
Symbol::$sym_const = new Symbol("sym_const");
Symbol::$sym_var = new Symbol("sym_var");
Symbol::$sym_public = new Symbol("sym_public");
Symbol::$sym_private = new Symbol("sym_private");
Symbol::$sym_protected = new Symbol("sym_protected");
Symbol::$sym_static = new Symbol("sym_static");
Symbol::$sym_final = new Symbol("sym_final");
Symbol::$sym_self = new Symbol("sym_self");
Symbol::$sym_parent = new Symbol("sym_parent");
Symbol::$sym_new = new Symbol("sym_new");
Symbol::$sym_clone = new Symbol("sym_clone");
Symbol::$sym_instanceof = new Symbol("sym_instanceof");
Symbol::$sym_list = new Symbol("sym_list");
Symbol::$sym_switch = new Symbol("sym_switch");
Symbol::$sym_case = new Symbol("sym_case");

Symbol::$sym_break = new Symbol("sym_break");
Symbol::$sym_default = new Symbol("sym_default");
Symbol::$sym_exit = new Symbol("sym_exit");
Symbol::$sym_echo = new Symbol("sym_echo");
Symbol::$sym_print = new Symbol("sym_print");
Symbol::$sym_trigger_error = new Symbol("sym_trigger_error");
Symbol::$sym_do = new Symbol("sym_do");
Symbol::$sym_try = new Symbol("sym_try");
Symbol::$sym_catch = new Symbol("sym_catch");
Symbol::$sym_finally = new Symbol("sym_finally");
Symbol::$sym_throw = new Symbol("sym_throw");

Symbol::$sym_continue = new Symbol("sym_continue");
Symbol::$sym_isset = new Symbol("sym_isset");
Symbol::$sym_include = new Symbol("sym_include");
Symbol::$sym_include_once = new Symbol("sym_include_once");
Symbol::$sym_require = new Symbol("sym_require");
Symbol::$sym_require_once = new Symbol("sym_require_once");

Symbol::$sym_namespace = new Symbol("sym_namespace");
Symbol::$sym_use = new Symbol("sym_use");
Symbol::$sym_goto = new Symbol("sym_goto");

/* PHPLint extended syntax symbols, aka "meta-code".
	These symbols MUST appear inside /&42;. .&42;/ : */
Symbol::$sym_x_require_module = new Symbol("sym_x_require_module");
Symbol::$sym_x_single_quoted_string = new Symbol("sym_x_single_quoted_string");
Symbol::$sym_x_semicolon = new Symbol("sym_x_semicolon");
Symbol::$sym_x_colon = new Symbol("sym_x_colon");
Symbol::$sym_x_comma = new Symbol("sym_x_comma");
Symbol::$sym_x_assign = new Symbol("sym_x_assign");

/* Forward declarations of functions and classes: */
Symbol::$sym_x_forward = new Symbol("sym_x_forward");
Symbol::$sym_x_function = new Symbol("sym_x_function");
Symbol::$sym_x_class = new Symbol("sym_x_class");
Symbol::$sym_x_extends = new Symbol("sym_x_extends");
Symbol::$sym_x_implements = new Symbol("sym_x_implements");
Symbol::$sym_x_const = new Symbol("sym_x_const");
Symbol::$sym_x_interface = new Symbol("sym_x_interface");
Symbol::$sym_x_unchecked = new Symbol("sym_x_unchecked");

/* x-Types: */
Symbol::$sym_x_void = new Symbol("sym_x_void");
Symbol::$sym_x_boolean = new Symbol("sym_x_boolean");
Symbol::$sym_x_int = new Symbol("sym_x_int");
Symbol::$sym_x_float = new Symbol("sym_x_float");
Symbol::$sym_x_string = new Symbol("sym_x_string");
Symbol::$sym_x_array = new Symbol("sym_x_array");
Symbol::$sym_x_mixed = new Symbol("sym_x_mixed");
Symbol::$sym_x_resource = new Symbol("sym_x_resource");
Symbol::$sym_x_object = new Symbol("sym_x_object");

Symbol::$sym_x_identifier = new Symbol("sym_x_identifier");
Symbol::$sym_x_variable = new Symbol("sym_x_variable");
Symbol::$sym_x_bit_and = new Symbol("sym_x_bit_and");
Symbol::$sym_x_args = new Symbol("sym_x_args");
Symbol::$sym_x_lround = new Symbol("sym_x_lround");
Symbol::$sym_x_rround = new Symbol("sym_x_rround");
Symbol::$sym_x_lsquare = new Symbol("sym_x_lsquare");
Symbol::$sym_x_rsquare = new Symbol("sym_x_rsquare");
Symbol::$sym_x_lbrace = new Symbol("sym_x_lbrace");
Symbol::$sym_x_rbrace = new Symbol("sym_x_rbrace");
Symbol::$sym_x_pragma = new Symbol("sym_x_pragma");
Symbol::$sym_x_if_php_ver_4 = new Symbol("sym_x_if_php_ver_4");
Symbol::$sym_x_if_php_ver_5 = new Symbol("sym_x_if_php_ver_5");
Symbol::$sym_x_else = new Symbol("sym_x_else");
Symbol::$sym_x_end_if_php_ver = new Symbol("sym_x_end_if_php_ver");
Symbol::$sym_x_missing_break = new Symbol("sym_x_missing_break");
Symbol::$sym_x_missing_default = new Symbol("sym_x_missing_default");

Symbol::$sym_x_public = new Symbol("sym_x_public");
Symbol::$sym_x_protected = new Symbol("sym_x_protected");
Symbol::$sym_x_private = new Symbol("sym_x_private");
Symbol::$sym_x_abstract = new Symbol("sym_x_abstract");
Symbol::$sym_x_static = new Symbol("sym_x_static");
Symbol::$sym_x_final = new Symbol("sym_x_final");
Symbol::$sym_x_self = new Symbol("sym_x_self");
Symbol::$sym_x_parent = new Symbol("sym_x_parent");
Symbol::$sym_x_triggers = new Symbol("sym_x_triggers");
Symbol::$sym_x_throws = new Symbol("sym_x_throws");
Symbol::$sym_x_return = new Symbol("sym_x_return");
Symbol::$sym_x_namespace = new Symbol("sym_x_namespace");

/* Templating: <K implements Hashable, E> */
Symbol::$sym_x_lt = new Symbol("sym_x_lt");
Symbol::$sym_x_gt = new Symbol("sym_x_gt");

Symbol::$sym_x_doc = new Symbol("sym_x_doc");
Symbol::$sym_x_docBlock = new Symbol("sym_x_docBlock");
