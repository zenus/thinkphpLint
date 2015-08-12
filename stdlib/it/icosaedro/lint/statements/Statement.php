<?php

namespace it\icosaedro\lint\statements;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\expressions\Expression;
use it\icosaedro\lint\parsers\ParserFactory;
use it\icosaedro\lint\expressions\UnknownVar;
use it\icosaedro\lint\expressions\UnassignedVar;
use it\icosaedro\lint\expressions\AssignedVar;
use it\icosaedro\lint\TypeDecl;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\ScannerException;

/**
 * Parses a statement, including PHPLint-specific meta-code statements.
 * @author Umberto Salsi <salsi@icosaedro.it>
 * @version $Date: 2014/08/16 05:31:21 $
 */
class Statement {
	
	/*. forward public static int function parse(Globals $globals)
		throws ScannerException, ParseException; .*/
	
	
	/**
	 * Checks statement be terminated by sym_semicolon, sym_close_tag
	 * (aka "?&gt;") or sym_eof. Gives error otherwise. sym_semicolon
	 * gets discarded, the other symbols are retained.
	 * @param Globals $globals
	 * @return void
	 */
	public static function parseStatementTerminator($globals){
		$scanner = $globals->curr_pkg->scanner;
		if( $scanner->sym === Symbol::$sym_semicolon ){
			$scanner->readSym();
		} else if( ($scanner->sym === Symbol::$sym_close_tag) || ($scanner->sym === Symbol::$sym_eof) ){
			# OK
		} else {
			$globals->logger->error($scanner->here(), "missing statement terminator `;' or `?>'");
		}
	}

	
	/**
	 * Parse PHP statement.
	 * @param Globals $globals
	 * @return int Flow mask (see Flow class).
	 * @throws ParseException
	 * @throws ScannerException
	 */
	public static function parse($globals){

//		$pkg = $globals->curr_pkg;
//		$scanner = $pkg->scanner;
//		$symbol = $scanner->sym->__toString();
//		if(($parser = ParserFactory::create($symbol)) != false){
//			return $parser->parse($globals);
//		}else{
//			throw new ParseException($scanner->here(), "unexpected symbol " . $scanner->sym);
//		}



		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
		switch( $scanner->sym->__toString() ){

		case "sym_open_tag":
			$scanner->readSym();
			return Flow::NEXT_MASK;

		case "sym_open_tag_with_echo":
			EchoBlockStatement::parse($globals);
			return Flow::NEXT_MASK;

		case "sym_lbrace":
			return CompoundStatement::parse($globals);

		case "sym_namespace":
			NamespaceStatement::parse($globals);
			return Flow::NEXT_MASK;

		case "sym_use":
			UseStatement::parse($globals);
			return Flow::NEXT_MASK;

		case "sym_x_require_module":
			RequireModuleStatement::parse($globals);
			return Flow::NEXT_MASK;

		case "sym_require_once":
			RequireOnceStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_require":
			if( $pkg->scope > 0 ){
				$globals->logger->warning($scanner->here(), "`require' inside a function. Suggest `require_once' instead");
			}
			IncludeAndRequireStatements::parse($globals, "require");
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_include":
			if( $pkg->scope > 0 ){
				$globals->logger->warning($scanner->here(), "include() inside a function. Suggest include_once() instead");
			}
			IncludeAndRequireStatements::parse($globals, "include");
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_include_once":
			IncludeAndRequireStatements::parse($globals, "include_once");
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_x_docBlock":
			// The main loop of the parser already takes care of the DocBlocks
			// at scope 0, so here what remains to do is:
			if( $globals->parse_phpdoc )
				$globals->logger->error($scanner->here(), "invalid scope for documentation");
			$scanner->readSym();
			return Flow::NEXT_MASK;

		case "sym_x_forward":
			ForwardStatement::parse($globals);
			return Flow::NEXT_MASK;

		case "sym_x_pragma":
			PragmaStatement::parse($globals);
			return Flow::NEXT_MASK;

		case "sym_x_if_php_ver_4":
			if( $globals->isPHP(4) ){
				$pkg->skip_else_php_ver = TRUE;
				$scanner->readSym();
			} else {
				$pkg->skip_else_php_ver = FALSE;
				$done = FALSE;
				while( ! $done ){
					$scanner->readSym();
					switch( $scanner->sym->__toString() ){

					case "sym_x_else":
						$scanner->readSym();
						$done = TRUE;
						break;

					case "sym_x_end_if_php_ver":
						$scanner->readSym();
						$done = TRUE;
						break;

					case "sym_eof":
						throw new ParseException($scanner->here(), "premature end of the file. Expected closing of `if_php_ver_4'.");

					default:
						// FIXME: what?

					}
				}
			}
			return Flow::NEXT_MASK;

		case "sym_x_if_php_ver_5":
			/*
			 * In module definition, starts a section of PHP5-specific code.
			 */
			// FIXME: check is we are really in a module
			if( $globals->isPHP(5) ){
				// PHP5: disable parsing of a possible "else" branch.
				$pkg->skip_else_php_ver = TRUE;
				$scanner->readSym();

			} else {
				// PHP4 - skip this branch, parse possible next "else" branch:
				$pkg->skip_else_php_ver = FALSE;
				$done = FALSE;
				while( ! $done ){
					$scanner->readSym();
					switch( $scanner->sym->__toString() ){

					case "sym_x_else":
						$scanner->readSym();
						$done = TRUE;
						break;

					case "sym_x_end_if_php_ver":
						$scanner->readSym();
						$done = TRUE;
						break;

					case "sym_eof":
						throw new ParseException($scanner->here(), "premature end of the file. Expected closing of `if_php_ver_5'.");

					default:
						// ignore sym
					}
				}
			}
			return Flow::NEXT_MASK;

		case "sym_x_else":
			if( $pkg->skip_else_php_ver ){
				$pkg->skip_else_php_ver = FALSE;
				do {
					$scanner->readSym();
				} while( ! ( $scanner->sym === Symbol::$sym_x_end_if_php_ver
						|| $scanner->sym === Symbol::$sym_eof ) );
				if( $scanner->sym === Symbol::$sym_x_end_if_php_ver ){
					$scanner->readSym();
				} else {
					throw new ParseException($scanner->here(), "missing closing `end_if_php_ver'");
				}
			} else {
				$scanner->readSym();
			}
			return Flow::NEXT_MASK;

		case "sym_x_end_if_php_ver":
			$scanner->readSym();
			return Flow::NEXT_MASK;

		case "sym_define":
			DefineStatement::parse($globals, FALSE);
			return Flow::NEXT_MASK;

		case "sym_const":
			ConstantStatement::parse($globals, FALSE);
			return Flow::NEXT_MASK;

		case "sym_declare":
			return DeclareStatement::parse($globals);

		case "sym_static":
			StaticStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_global":
			GlobalStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_echo":
			EchoStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_trigger_error":
			TriggerErrorStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_identifier":
		case "sym_at":
		case "sym_list":
		case "sym_print":
		case "sym_self":
		case "sym_parent":
		case "sym_lround":
		case "sym_incr":
		case "sym_decr":
		case "sym_new":
		case "sym_isset":
			/* $r = */ Expression::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_exit":
			ExitStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::RETURN_MASK;

		case "sym_variable":
			$dbw = $pkg->curr_docblock;
			$dbw->checkLineTagsForVariable();
			$v = $globals->searchVar($scanner->s);
			if( $v === NULL ){
				/* $r = */ UnknownVar::parse($globals, $dbw->getDocBlock(), $dbw->isPrivate(), $dbw->getVarType());
			} else {
				if( $dbw->getDocBlock() !== NULL )
					$globals->logger->error($scanner->here(), "DocBlock for already known variable $v");
				if( $v->assigned )
					/* $r = */ AssignedVar::parse($globals, $v);
				else
					/* $r = */ UnassignedVar::parse($globals, $v);
			}
			$pkg->curr_docblock->clear();
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_if":
			return IfStatement::parse($globals);

		case "sym_for":
			return ForStatement::parse($globals);

		case "sym_foreach":
			return ForeachStatement::parse($globals);

		case "sym_switch":
			return SwitchStatement::parse($globals);

		case "sym_break":
			BreakStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::BREAK_MASK;

		case "sym_class":
		case "sym_x_abstract":
		case "sym_x_final":
		case "sym_abstract":
		case "sym_final":
		case "sym_x_unchecked":
			ClassStatement::parse($globals, FALSE);
			return Flow::NEXT_MASK;

		case "sym_interface":
			InterfaceStatement::parse($globals, FALSE);
			return Flow::NEXT_MASK;

		case "sym_while":
			return WhileStatement::parse($globals);

		case "sym_do":
			$res = DoWhileStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return $res;

		case "sym_continue":
			ContinueStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::CONTINUE_MASK;

		case "sym_return":
			ReturnStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::RETURN_MASK;

		case "sym_semicolon":
			# Empty statement.
			self::parseStatementTerminator($globals);
			return Flow::NEXT_MASK;

		case "sym_function":
			FunctionStatement::parse($globals, FALSE, NULL);
			return Flow::NEXT_MASK;

		case "sym_x_void":
		case "sym_x_boolean":
		case "sym_x_int":
		case "sym_x_float":
		case "sym_x_string":
		case "sym_x_array":
		case "sym_x_mixed":
		case "sym_x_resource":
		case "sym_x_object":
		case "sym_x_identifier":
		case "sym_x_private":

			$is_private = FALSE;
			if( $scanner->sym === Symbol::$sym_x_private ){
				if( $pkg->scope == 0 )
					$is_private = TRUE;
				else
					$globals->logger->error($scanner->here(), "`private' attribute at local scope");
				$scanner->readSym();
			}

			if( $scanner->sym === Symbol::$sym_define ){
				DefineStatement::parse($globals, $is_private);
				return Flow::NEXT_MASK;

			} else if( $scanner->sym === Symbol::$sym_const ){
				ConstantStatement::parse($globals, $is_private);
				return Flow::NEXT_MASK;

			} else if( $scanner->sym === Symbol::$sym_class
			|| $scanner->sym === Symbol::$sym_abstract
			|| $scanner->sym === Symbol::$sym_final ){
				ClassStatement::parse($globals, $is_private);
				return Flow::NEXT_MASK;

			} else if( $scanner->sym === Symbol::$sym_interface ){
				InterfaceStatement::parse($globals, $is_private);
				return Flow::NEXT_MASK;
			}

			$type = TypeDecl::parse($globals, FALSE);
			if( $scanner->sym === Symbol::$sym_variable ){
				$dbw = $pkg->curr_docblock;
				$dbw->checkLineTagsForVariable();

				$v = $globals->searchVar($scanner->s);
				if( $v === NULL ){
					// Unknown variable.
					// Determines private attribute:
					if( $is_private && $dbw->isPrivate() )
						$globals->logger->error($scanner->here(), "`private' attribute both in DocBlock and PHPLint meta-code");
					$is_private = $is_private || $dbw->isPrivate();
					// Determines type:
					if( $type !== NULL && $dbw->getVarType() !== NULL )
						$globals->logger->error($scanner->here(), "type declaration both in DocBlock and PHPLint meta-code");
					if( $type === NULL )
						$type = $dbw->getVarType();
					/* $r = */ UnknownVar::parse($globals, $dbw->getDocBlock(), $is_private, $type);
				} else {
					// Known variable.
					if( $dbw->getDocBlock() !== NULL )
						$globals->logger->error($scanner->here(), "DocBlock for already known variable $v");
					if( $is_private )
						$globals->logger->error($scanner->here(), "`private' attribute for already known variable $v");
					if( $type !== NULL )
						$globals->logger->error($scanner->here(), "type declared for already known variable $v");
					if( $v->assigned )
						/* $r = */ AssignedVar::parse($globals, $v);
					else
						/* $r = */ UnassignedVar::parse($globals, $v);
				}
				$pkg->curr_docblock->clear();
				self::parseStatementTerminator($globals);
				return Flow::NEXT_MASK;

			} else if( $scanner->sym === Symbol::$sym_function ){
				FunctionStatement::parse($globals, $is_private, $type);
				return Flow::NEXT_MASK;

			} else {
				throw new ParseException($scanner->here(), "expected variable or `function' after type");
			}

		case "sym_throw":
			ThrowStatement::parse($globals);
			self::parseStatementTerminator($globals);
			return Flow::RETURN_MASK;

		case "sym_try":
			return TryStatement::parse($globals);

		case "sym_close_tag":
			TextBlockStatement::parse($globals);
			return Flow::NEXT_MASK;

		case "sym_goto":
			$globals->logger->error($scanner->here(), "goto: unimplemented statement -- trying to continue anyway");
			$scanner->readSym();
			$globals->expect(Symbol::$sym_identifier, "expected identifier after `goto'");
			$scanner->readSym();
			$globals->expect(Symbol::$sym_semicolon, "expected `;'");
			$scanner->readSym();
			return Flow::RETURN_MASK;

		case "sym_text":
			// FIXME: check encoding?
			$scanner->readSym();
			return Flow::NEXT_MASK;

		default:
			throw new ParseException($scanner->here(), "unexpected symbol " . $scanner->sym);
		}

	}
	
}
