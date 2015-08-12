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

class XIfPhpVer5Parser implements Parser
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

    }
}