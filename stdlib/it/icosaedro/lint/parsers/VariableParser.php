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
use it\icosaedro\lint\statements\Statement;

class VarParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        $pkg = $globals->curr_pkg;
        $scanner = $pkg->scanner;
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
        Statement::parseStatementTerminator($globals);
        return Flow::NEXT_MASK;

    }
}