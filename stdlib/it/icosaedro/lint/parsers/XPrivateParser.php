<?php
/**
 * Created by PhpStorm.
 * User: zenus
 * Date: 2015/8/7
 * Time: 14:53
 */

namespace it\icosaedro\lint\parsers;


use it\icosaedro\lint\expressions\AssignedVar;
use it\icosaedro\lint\expressions\UnassignedVar;
use it\icosaedro\lint\expressions\UnknownVar;
use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\ParseException;
use it\icosaedro\lint\statements\ClassStatement;
use it\icosaedro\lint\statements\ConstantStatement;
use it\icosaedro\lint\statements\DefineStatement;
use it\icosaedro\lint\statements\FunctionStatement;
use it\icosaedro\lint\statements\InterfaceStatement;
use it\icosaedro\lint\statements\Statement;
use it\icosaedro\lint\Symbol;
use it\icosaedro\lint\TypeDecl;

class XPrivateParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
            $pkg = $globals->curr_pkg;
            $scanner = $pkg->scanner;
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
                Statement::parseStatementTerminator($globals);
                return Flow::NEXT_MASK;

            } else if( $scanner->sym === Symbol::$sym_function ){
                FunctionStatement::parse($globals, $is_private, $type);
                return Flow::NEXT_MASK;

            } else {
                throw new ParseException($scanner->here(), "expected variable or `function' after type");
            }
    }
}