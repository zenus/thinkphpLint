<?php
/**
 * Created by PhpStorm.
 * User: lb@lansee.net
 * Date: 2015/8/7
 * Time: 17:51
 */

namespace it\icosaedro\lint\parsers;


class ParserFactory
{
    /**
     * @param $symbol
     * @return Parser
     */
    public static function  create($symbol)
    {
        $constParser = new XAbstractParser();
        $class = substr($symbol,4);
        $classArray = explode('_',$class);
        $class = '';
        foreach($classArray as $alpha)
        {
            $class .= ucfirst($alpha);
        }
        $class .="Parser";
        $class=  __NAMESPACE__.'\\'.$class;

        if(class_exists($class)){
            $parser = new $class();
            if($parser instanceof Parser){
                return $parser;
            }
        }else{
            return false;
        }

    }

}