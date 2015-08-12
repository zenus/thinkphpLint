<?php
/**
 * Created by PhpStorm.
 * User: zenus
 * Date: 2015/8/7
 * Time: 14:53
 */

namespace it\icosaedro\lint\parsers;


use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\ParseException;

class XElseParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     * @throws ParseException
     */
    public function parse(Globals $globals)
    {
        $pkg = $globals->curr_pkg;
        $scanner = $pkg->scanner;
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
    }
}