<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\CaseInsensitiveString;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\types\ClassType;
use it\icosaedro\lint\types\ArrayType;
use it\icosaedro\lint\Variable;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\lint\ParseException;

/**
 * Parses the "foreach" statement.
 * <blockquote><pre>
 * foreach(arrayORobject as [&amp;]$v) {}
 * foreach(arrayORobject as $$k =&gt; [&amp;]$v) {}
 * </pre></blockquote>
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2015/02/23 16:30:07 $
 */
class ForeachStatement {
	
	/**
	 * @var CaseInsensitiveString 
	 */
	private static $KEY_NAME, $CURRENT_NAME, $GET_ITERATOR_NAME;
	 
	/**
	 * Parses the "foreach" statement.
	 * @param Globals $globals
	 * @return int See {@link it\icosaedro\lint\Flow}
	 */
	public static function parse($globals)
	{
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		$scanner->readSym();
		$globals->expect(Symbol::$sym_lround, "expected `(' after `foreach'");
		$scanner->readSym();

		# foreach(EXPR...
		$r = Expression::parse($globals);
		$k_type = Globals::$unknown_type;
		$v_type = Globals::$unknown_type;
		if( $r->isUnknown() ){
			#$globals->logger->warning($scanner->here(), "`foreach': expected array, found unknown type")

		} else if( $r->isArray() ){
			$a = cast(ArrayType::NAME, $r->getType());
			$k_type = $a->getIndex();
			$v_type = $a->getElem();

		} else if( $globals->isPHP(5) && $r->isClass() ){
			$c = cast(ClassType::NAME, $r->getType());

			if( $c->isSubclassOf($globals->builtin->IteratorClass) ){
				// The key type is the return value of the key() method:
				$key = $c->searchMethod(self::$KEY_NAME);
				if( $key === NULL )
					throw new ParseException($scanner->here(),
					"missing method $c::key()");
				$globals->accountMethod($key);
				$k_type = $key->sign->returns;
				// Element type is the return type of the current() method:
				$current = $c->searchMethod(self::$CURRENT_NAME);
				if( $current === NULL )
					throw new ParseException($scanner->here(),
					"missing method $c::current()");
				$globals->accountMethod($current);
				$v_type = $current->sign->returns;
				// FIXME: missing accounting for rewind(), next(), valid()

			} else if( $c->isSubclassOf($globals->builtin->IteratorAggregateClass) ){
				$get_iterator = $c->searchMethod(self::$GET_ITERATOR_NAME);
				if( $get_iterator === NULL )
					throw new ParseException($scanner->here(),
					"missing method $c::getIterator()");
				$globals->accountMethod($get_iterator);
				$iterator = cast(ClassType::NAME, $get_iterator->sign->returns);
				if( $iterator->isSubclassOf($globals->builtin->IteratorClass) ){
					// Key type is the return type of the key() method:
					$key = $iterator->searchMethod(self::$KEY_NAME);
					if( $key === NULL )
						throw new ParseException($scanner->here(),
						"missing method $iterator::key()");
					$globals->accountMethod($key);
					$k_type = $key->sign->returns;
					// Element type is the return type of the current() method:
					$current = $iterator->searchMethod(self::$CURRENT_NAME);
					if( $current === NULL )
						throw new ParseException($scanner->here(),
						"missing method $iterator::current()");
					$globals->accountMethod($current);
					$v_type = $current->sign->returns;
					// FIXME: missing accounting for rewind(), next(), valid()
				} else {
					$globals->logger->error($scanner->here(),
					"method $get_iterator does not return instance of "
					. $globals->builtin->IteratorClass);
				}

			} else {
				// Iterating over the properties of the object:
				$k_type = Globals::$string_type; // name of the property
				$v_type = Globals::$mixed_type; // value
			}

		} else {
			$globals->logger->error($scanner->here(),
			"`foreach(EXPR' requires an array or an object, found "
			. $r->getType());
		}

		$before = new AssignedVars($globals);

		# foreach(... as
		$globals->expect(Symbol::$sym_as, "expected `as'. Hint: check `foreach( ARRAY_EXPRESSION as ...'");
		$scanner->readSym();

		# foreach(... as [&]
		$by_addr = FALSE;
		if( $scanner->sym === Symbol::$sym_bit_and ){
			if( $globals->isPHP(4) ){
				$globals->logger->error($scanner->here(),
				"can't use `&' in `foreach' (PHP 5)");
			}
			$by_addr = TRUE;
			$scanner->readSym();
		}

		# foreach(... as $VARNAME
		$globals->expect(Symbol::$sym_variable,
		"`foreach': expected variable name");
		$v_name = $scanner->s;
		$v_where = $scanner->here();
		$v = $globals->searchVar($v_name);
		
		$scanner->readSym();

		# foreach(... as VARNAME [=> [&] $VARNAME]
		if( $scanner->sym === Symbol::$sym_rarrow ){
			if( $by_addr ){
				$globals->logger->error($scanner->here(),
				"the key cannot be passed by reference");
				$by_addr = FALSE;
			}
			
			$scanner->readSym();
			if( $scanner->sym === Symbol::$sym_bit_and ){
				$by_addr = TRUE;
				$scanner->readSym();
			}
			$globals->expect(Symbol::$sym_variable,
			"`foreach': expected variable name after `=>'");
			
			// Prev. var was the key:
			$k_name = $v_name;
			$k_where = $v_where;
			$k = $v;
			if( $k === NULL ){
				// Create key var:
				$k = new Variable($k_name, FALSE, $k_where, $pkg->scope);
				$k->type = $k_type;
				$globals->addVar($k);
			} else {
				if( ! $k_type->assignableTo($k->type) ){
					$globals->logger->error($scanner->here(),
					"foreach(): variable `$k' of type " . $k->type
					. " does not match the index of type " . $k_type);
				}
			}
			$globals->accountVarLHS($k);
			
			// Curr var is the value:
			$v_name = $scanner->s;
			$v_where = $scanner->here();
			$v = $globals->searchVar($v_name);
			
			$scanner->readSym();
		}
		
		if( $v === NULL ){
			// Create value var:
			$v = new Variable($v_name, FALSE, $v_where, $pkg->scope);
			$v->type = $v_type;
			$globals->addVar($v);
		} else {
			if( ! $v_type->assignableTo($v->type) ){
				$globals->logger->error($scanner->here(),
				"foreach(): variable `$v' of type " . $v->type
				. " does not match the elements of type " . $v_type);
			}
		}
		$globals->accountVarLHS($v);

		$globals->expect(Symbol::$sym_rround, "expected closing `)' of the `foreach' statement");
		$scanner->readSym();

		/* FIXME: should recommend foreach(... &$v)?
		 * Remember that by-ref var should then be unset($v) after foreach()
		 * or subtle errors may happen.
		if( ! by_addr ){
			$globals->logger->notice($scanner->here(), "optimization suggestion: consider to pass the value by reference: `foreach($a as $$k => &$v)...'")
			raise this msg only if the type of $v is a complex data struct:
			array, string(?), ...(?)
		}
		*/

		$pkg->loop_level++;
		$res = CompoundStatement::parse($globals);
		$pkg->loop_level--;
		$before->restore();
		return Flow::NEXT_MASK | $res & Flow::RETURN_MASK;
	}
	
	
	public static function static_init()
	{
		self::$KEY_NAME = new CaseInsensitiveString("key");
		self::$CURRENT_NAME = new CaseInsensitiveString("current");
		self::$GET_ITERATOR_NAME = new CaseInsensitiveString("getIterator");
	}
	
}


ForeachStatement::static_init();
