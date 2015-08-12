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

class XIfPhpVer4Parser implements Parser
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
    }
}