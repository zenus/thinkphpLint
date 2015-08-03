<?php
/**
 * Created by PhpStorm.
 * User: lb@lansee.net
 * Date: 2015/8/8
 * Time: 21:28
 */

namespace it\icosaedro\lint;
use InvalidArgumentException;
use it\icosaedro\io\File;
use it\icosaedro\io\FileName;
use it\icosaedro\lint\GlobalsImplementation ;

class Thinkphp
{
    private static $ignore_dirs = array('public','runtime','theme','view');
    private static $map = array();
    public static $lineDistance = 0;
    private static $used = array();
    private static $thinkRoot = '';
    private static $functionFiles = array();
    private static $runName = null;
    private static $runCwd = null;
    private static $namespace = null;

    /**
     * @param $globals
     */
    public static function bootstrap($arg,GlobalsImplementation $globals){
        //关联命名空间与目录
        self::$runCwd  = getcwd();
        self::$runName = $arg;
       // $rawPath = self::getRawFilePath();
        $root = $globals->project_root->getBaseName();
        $root = FileName::encode($globals->project_root->getBaseName());
        $root = str_replace('/',DIRECTORY_SEPARATOR,$root);
        self::buildMap($root);
        //self::parseContent();
        //生成分析文件
        return  self::createRunFile();
    }

    public static function destruct(){
        die;
        $path = self::getRunFilePath();
        if(file_exists($path)){
            try{
                unlink($path);
            }catch (\Exception $e){
                exit("can not delete : {$path}");
            }
        }
    }
    private static function getPhplRoot(){
        return substr(__DIR__,0,strpos(__DIR__,'phplint')+7);
    }

    private static  function parseContent($content){
        $content = self::parseDfunction($content);
        $content = self::parseUseDeclared($content);
        return $content;
    }

    private static function parseDfunction($content){
        $pattern = "#D\(['\"]([a-zA-Z,'\"]+)['\"]\)#";
        if(preg_match_all($pattern,$content,$matches) != false){
            if(!empty($matches[1])){
                $models = array_unique($matches[1]);
                $relations = array();
                foreach($models as $model){
                    $model_array = explode(',',$model);
                    $model_string = '';
                    if(count($model_array)>1){
                        $model_string = str_replace("','",'',$model);
                    }else{
                        $model_string = current($model_array).'Model';
                    }
                    $search = "D('".$model."')";
                    $replace = "new ".$model_string."()";
                    $content = str_replace($search,$replace,$content);
                    foreach(self::$map as $k=>$v){
                        if(strpos($k,$model_string) !== false){
                            self::$used[$k]=$v;
                        }
                    }
                }
            }
        }
        return $content;
    }

    public static function parseThinkFunction(){
        //import;load;vendor;D;parse_res_name;
        //controller;A;R;F;load_exit_file;
    }
    public static function attachFunctionImportClass(){

    }
    public static function attachThinkConstants(){

    }
    public static function attachDeveloperConstants(){

    }
    private static function distance(){
        $rawPath = self::getRawFilePath();
        $runPath = self::getRunFilePath();
        if(file_exists($rawPath) && file_exists($runPath)){
            $rawCount = count(file($rawPath));
            $runCount = count(file($runPath));
            if(!empty($rawCount) && !empty($runCount)){
                self::$lineDistance = $runCount - $rawCount;
            }
        }
    }

    private static function createRunFile(){

        //$rawPath = self::getRawFilePath();
        //生成分析文件
//        if(file_exists($rawPath)){
//            try{
//                $content = file_get_contents($rawPath);
//            }catch(\Exception $e){
//                exit("can not get {$rawPath}");
//            }

            //替换掉<?php

            $content = self::getRawContent();
            $content = self::removeHeaderBlock($content);
            $content = self::parseContent($content);
            $help = '';

            //加载phplint模块
            $help .= self::attachLintModules();
//            $help .= self::attachThinkClass();
            $help .=self::attachSelfClass();

            //加载thinkphp函数
             //self::attachThinkFunctions();

            //加载用户自定函数
             //self::attachDeveloperFunctions();

            //加载think model
             //self::attachThinkModel();

            //加载要use的文件
            //$help .= self::attachUseClass();
        //
            //$help .= self::attachUseDeclared();
            $header = self::getHeaderBlock();
            $content = $header.$help.$content;
            $path = self::getRunFilePath();
            try{
                file_put_contents($path,$content);
            }catch(\Exception $e){
                exit("can not write run file : {$path} ");
            }
             self::distance( );
        return self::buildRunName();
    }
    private static function attachThinkClass(){
        $modelModule = self::getPhplRoot()
            . DIRECTORY_SEPARATOR
            .'modules'
            .DIRECTORY_SEPARATOR.'thinkmodel.php';
        $controllerModule = self::getPhplRoot()
            .DIRECTORY_SEPARATOR
            .'modules'
            .DIRECTORY_SEPARATOR
            .'thinkcontroller.php';
        $content = str_replace('<?php','',self::getFileContent($modelModule));
        $content .= str_replace('<?php','',self::getFileContent($controllerModule));
        return $content;

    }
    private static function attachSelfClass(){
//        $selfModule = self::getPhplRoot().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'self.php';
//        if(file_exists($selfModule)){
//            try{
//                unlink($selfModule);
//            }catch(\Exception $e){
//               exit("can not delete exits {$selfModule}");
//            }
//        }
        $class = '';
        if(!empty(self::$used)){
            self::$used = array_unique(self::$used);
            foreach(self::$used as $use){
                $class .=self::makeClass($use);
            }
//            $selfModule = self::getPhplRoot().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'self.php';
//            try{
//                $content = "<?php\r\n".$class;
//                file_put_contents($selfModule,$content);
//            }catch (\Exception $e){
//                exit("can not write file : {$selfModule}");
//            }
        }
        return $class;
    }
    private static function makeClass($use){
        $parent = '';
        if(file_exists($use)){
            if(strpos($use,'Controller')){
                $parent .= 'Controller';
            }elseif(strpos($use,'Model')){
                $parent .= 'Model';
            }
            $content = self::getFileContent($use);
            $className = self::getClassName($content);
            if(empty($className)){
                exit("read {$use} class name error");
            }
            $methods  = self::getClassMethods($content);
            if(empty($methods)){
                exit("read {$use} class methods error");
            }
             $extend = '';
            return "class ".$className.$extend."{\r\n".$methods."\r\n}\r\n";
        }
        return '';
    }

    private static function getFileContent($file){

        try {
            $content = file_get_contents($file);
        }catch (\Exception $e){
            exit("can not read {$file}");
        }
        return $content;
    }

    private static function getClassName($content){
        $className = '';
       $pattern = '#class\s+([a-zA-Z0-9]+)\s+(extends|})#';
        if(preg_match($pattern,$content,$matches)!== false){
            if(!empty($matches)){
               $className = $matches[1];
            }
        }
        return $className;
    }
    private static function getClassMethods($content){
        $methods = '';
        $pattern ='#(/\*{2}[^/]+/)?\s+(protected|public)\s+function\s+(\w+)\(([^{]+)?\)#';
        if(preg_match_all($pattern,$content,$matches)){
            if(!empty($matches)){
                //函数参数处理
                $params = array();
                $_methods = $matches[0];
                $_params = $matches[4];
                $_returns = $matches[1];
                foreach($_params as $k=>$v){
                        $args = explode(',',$v);
                        $params[$k]  = $args;
                }
                //函数反回值处理
                $returns = array();
                foreach($_returns as $k=>$v){
                    $pattern ='#return\s+(\w+)#';
                    if(preg_match($pattern,$v,$matches)){
                        $returns[$k] = $matches[1];
                    }else{
                        $returns[$k] = 'void';
                    }
                }
                foreach($_methods as $k=>$v){
                    //$methods .= $v.'{}'."\r\n";
                    $methods .= $v.'{'."\r\n";
                    $params[$k] = array_filter($params[$k]);
                    if(!empty($params[$k])){
                        foreach($params[$k] as $param){
                            if(strpos($param,'array') !== false){
                                $param = str_replace('array','',$param);
                            }
                            if(strpos($param,'=') !== false ){
                                $param = substr($param,0,strpos($param,'='));
                            }
                            $methods .= "\tdump($param);\r\n";
                        }
                    }
                    $returnMap = array(
                        'int'=>1,
                        'mixed'=>1,
                        'string'=>2,
                        'bool'=>'true',
                        'boolean'=>'true',
                        'array'=>'array()',
                        'object'=>' new stdObject()',
                        'string[string]'=>'array()',
                        'string[int]'=>'array()',
                        'int[int]'=>'array()',
                        'mixed[]'=>'array()',
                    );
                   if($returns[$k] != 'void'){
                       $return = $returnMap[$returns[$k]];
                       $methods .= "\treturn $return;\r\n}\r\n";
                   }else{
                       $methods .= "\t}\r\n";
                   }
                }
            }
        }
        return $methods;
    }
    /*
   private static function attachThinkModel(){
       return;
       $thinkModelFile = self::$thinkRoot.DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR.'Think'.DIRECTORY_SEPARATOR.'Model.class.php';
       try{
           $content = file_get_contents($thinkModelFile);
           $pattern ='#(/\*{2}[^/]+/)\s+function\s+(\w+)\([^{]+\)#';
           $pattern ='#(/\*{2}[^/]+/)\s+(protected|public)\s+function\s+(\w+)\([^{]+\)#';
           if(preg_match_all($pattern,$content,$matches)){
               $module = "<?php\r\n";
               foreach($matches[0] as $v){
                   $module .= $v.'{}'."\r\n";
               }
               $modelModule = self::getPhplRoot().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'model.php';
               file_put_contents($modelModule,$module);
           }
       }catch(\Exception $e){
           die($e->getMessage());
       }
   }
   */

    private  static function makeControllerClass($use){
        $content = self::getFileContent($use);
        $className = self::getClassName($content);
        if(empty($className)){
            exit("read {$use} class name error");
        }
        $methods  = self::getClassMethods($content);
        if(empty($methods)){
            exit("read {$use} class methods error");
        }
        $controllerModule = self::getPhplRoot().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'thinkcontroller.phplint';
        try{
            $controller = file_get_contents($controllerModule);
        }catch(\Exception $e){
            exit("can not read {$controllerModule}");
        }
        return "class ".$className."{\r\n".$methods.$controller."\r\n}\r\n";

    }


    private static function getHeaderBlock(){

        return "<?php\r\n"."namespace\t".self::$namespace.";\r\n";
    }

    private static function removeHeaderBlock($content){
        //$content = self::getRawContent();
        $content = str_replace("<?php",'',$content);
        $pattern = '#namespace\s+(.*?);#';
        if(preg_match($pattern,$content,$matches) != false){
            self::$namespace = $matches[1];
            $content = preg_replace($pattern,'',$content);
        }
        return $content;

    }
    //use Common\Controller\RestBaseController;
    private static function parseUseDeclared($content){
        $pattern = "#use\s+(.*)?;#";
        //加载use声明中的use文件
        if(preg_match_all($pattern,$content,$matches) != false){
            if(!empty($matches[1])){
                foreach($matches[1] as $use){
                    if(isset(self::$map[$use])){
                        self::$used[$use] = self::$map[$use];
                    }
                }
            }
        }
        //除去use标签
        $pattern = "#use\s+(.*)?;#";
        if(($content = preg_replace($pattern,'',$content)) ==null){
            exit('error to remove use tag');
        }
        return $content;
//        //加载隐含用到的文件
//        if(!empty(self::$used)){
//            foreach(self::$used as $v){
//                $v = str_replace('\\','/',$v);
//                $uses .= "require_once '".$v."';\r\n";
//            }
//        }
//        return $uses;
    }
    private static function getRawContent(){
        $content = '';
        try {
            $path = self::getRawFilePath();
            return  file_get_contents($path);
        }catch(\Exception $e){
            exit("can not find {$path}");
        }

    }

    private static function buildRunName(){

        $path = pathinfo(self::$runName);
        if($path['dirname'] == '.'){
            $runPath = '.'.self::$runName;
        }else{
            $fileName = '.'.$path['basename'];
            $runPath =  str_replace($path['basename'],$fileName,self::$runName);
        }
        return $runPath;

    }


    private static function getRawFilePath(){

 //       return  self::$runCwd.DIRECTORY_SEPARATOR.self::$runName;
               return  self::$runName;
    }

    private static  function getRunFilePath(){
        //return  self::$runCwd.DIRECTORY_SEPARATOR.self::buildRunName();
        return  self::buildRunName();
    }

    private static function attachLintModules(){

        /*.
   require_module 'standard';
   require_module 'pcre';
   require_module 'mysql';
 .*/

        $modules = array(
            'apache','bcmath','bz2','calendar','com','cpdf','ctype','curl','dba',
            'dbase','dbx','dio','dom','exif','fam','fbsql','fdf','fileinfo','filepro',
            'filter','ftp','gd','gettext','gmp','hash','hwapi','iconv','imap','informix',
            'ingres','interbase','intl','ircg','json','ldap','libxml','mbstring','mcrypt',
            'mcve','mhash','ming','mnogosearch','msession','msql','mssql','mysql','mysqli',
            'ncurses','nsapi','oci','odbc','openssl','oracle','ovrimossql','pcntl','pcre',
            'pdo','pfpro','pgsql','phar','posix','pspell','readline','recode','regex',
            'session','shmop','simplexml','snmp','soap','sockets','spl','sqlite','sqlite3',
            'standard','standard_reflection','streams','sybase','sysvmsg','sysvsem','sysvshm',
            'tidy','tokenizer','variant','wddx','xdebug','xml','xmlreader','xmlrpc','xmlwriter',
            'xsl','yp','zip','zlib','thinkfunctions','developer');
        $modules = array('thinkfunctions');
        $comment ="/*.\r\n";
        foreach($modules as $module){
            $comment .= "\trequire_module '".$module."';\r\n";
        }
        $comment .=".*/\r\n";
        return $comment;
    }

    /*
    private static function attachThinkFunctions(){

        return;
        $thinkFunctionFile = self::$thinkRoot.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.'functions.php';
        try{
            $content = file_get_contents($thinkFunctionFile);
            $pattern ='#(/\*{2}[^/]+/)\s+function\s+(\w+)\([^{]+\)#';
           if(preg_match_all($pattern,$content,$matches)){
               $module = "<?php\r\n";
               foreach($matches[0] as $v){
                   $module .= $v.'{}'."\r\n";
               }
               $file = self::$runCwd.DIRECTORY_SEPARATOR.'thinkfunctions.php';
               file_put_contents($file,$module);
           }
        }catch(\Exception $e){
            die($e->getMessage());
        }
    }
    */
    /*
    private static function attachThinkModel(){
        return;
        $thinkModelFile = self::$thinkRoot.DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR.'Think'.DIRECTORY_SEPARATOR.'Model.class.php';
        try{
            $content = file_get_contents($thinkModelFile);
            $pattern ='#(/\*{2}[^/]+/)\s+function\s+(\w+)\([^{]+\)#';
            $pattern ='#(/\*{2}[^/]+/)\s+(protected|public)\s+function\s+(\w+)\([^{]+\)#';
            if(preg_match_all($pattern,$content,$matches)){
                $module = "<?php\r\n";
                foreach($matches[0] as $v){
                    $module .= $v.'{}'."\r\n";
                }
                $modelModule = self::getPhplRoot().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'model.php';
                file_put_contents($modelModule,$module);
            }
        }catch(\Exception $e){
            die($e->getMessage());
        }
    }
    */

    /*
    private static function attachThinkController(){
        return ;
        $thinkModelFile = self::$thinkRoot.DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR.'Think'.DIRECTORY_SEPARATOR.'Model.class.php';
        try{
            $content = file_get_contents($thinkModelFile);
            $pattern ='#(/\*{2}[^/]+/)\s+function\s+(\w+)\([^{]+\)#';
            $pattern ='#(/\*{2}[^/]+/)\s+(protected|public)\s+function\s+(\w+)\([^{]+\)#';
            if(preg_match_all($pattern,$content,$matches)){
                $module = "<?php\r\n";
                foreach($matches[0] as $v){
                    $module .= $v.'{}'."\r\n";
                }
                $modelModule = self::getPhplRoot().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'model.php';
                file_put_contents($modelModule,$module);
            }
        }catch(\Exception $e){
            die($e->getMessage());
        }
    }
    */

    //废弃use 将生成一个大的分析文件里面包括了use到的类，D加载的...等等
    /*
    private static function  attachUseDeclared(){
        $content = '';
        if(!empty(self::$used)){
            foreach(self::$used as $use=>$file){
                $content .= "use ".$use.";\r\n";
            }
        }
        return $content;
    }
    */
    private static function attachDeveloperFunctions(){

        $developerModule = self::getPhplRoot().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'developer.php';
        if(file_exists($developerModule)){
            try{
                unlink($developerModule);
            }catch(\Exception $e){
                exit("can not delete {$developerModule}");
            }
        }
        $module = "<?php\r\n";
        if(!empty(self::$functionFiles)){
            foreach(self::$functionFiles as $file){
                $file = str_replace('\\','/',$file);
                try{
                    $content = file_get_contents($file);
                    $pattern ='#(/\*{2}[^/]+/)\s+function\s+(\w+)\([^{]+\)#';
                    if(preg_match_all($pattern,$content,$matches)){
                        foreach($matches[0] as $v){
                            $module .= $v.'{}'."\r\n";
                        }
                    }
                }catch(\Exception $e){
                    die($e->getMessage());
                }
            }
            try{
                file_put_contents($developerModule,$module,FILE_APPEND | LOCK_EX);
            }catch(\Exception $e){
                exit("can not write {$developerModule}");
            }
        }
    }




    /**
     * @param $namespace
     * @return bool
     */
    public static function searchFile($namespace){
        return isset(self::$map[$namespace]) ? self::$map[$namespace] : NULL;
    }

    private static function dir($root){
        if($handle = opendir($root)){
            while (false !== ($file = readdir($handle))) {
                if($file != '.' && $file !== '..' ) {
                    $path = $root . DIRECTORY_SEPARATOR . $file;
                    if(is_dir($path)){
                        if(!in_array(strtolower($file),self::$ignore_dirs)){
                            if(strtolower($file) == 'thinkphp'){
                                self::$thinkRoot = $path;
                                continue;
                            }
                            self ::dir($path);
                        }
                    }else{
                        if($file == 'functions.php' || $file == 'function.php'){
                            self::$functionFiles[] = $path;
                        }
                        if(substr($file,-9) == 'class.php'){
                            self::MakeFileNameSpaceMap($path);
                        }
                    }
                 }
            }
        }
    }



    /**
     * @param $path
     * @return bool
     */
    private static function parseFunctions($path){

        $content = file_get_contents($path);
        //var_dump($content);
        $pattern = '#function\s(\w+)\([^{]+\)#';
        try{
            if(preg_match_all($pattern,$content,$matches)){
                foreach($matches[1] as $fun){
                    //self::$functions[$fun] = $path;
                }
            }
        }catch (\Exception $e){
            return false;
        }

    }
    private static function MakeFileNameSpaceMap($file){

        $content = file_get_contents($file);
        $pattern = '#namespace\s+(.*)?;#';
        try{
            if(preg_match($pattern,$content,$matches)){
                $fileName = substr(basename($file),0,-10);
                $namespace = $matches[1].'\\'.$fileName;
                self::$map[$namespace] = $file;
                return true;
            }
        }catch (\Exception $e){
            return false;
        }
    }

    private static function  buildMap($root){
 //       $cacheFile = $root.DIRECTORY_SEPARATOR.'.thinklint.php';
//        if(!file_exists($cacheFile)){
//            self::$map = require($cacheFile);
//        }else{
            self ::dir($root);
//            self::generateCacheFile($cacheFile);
            //当用户代码中调用到think函数时，加在think函数文件
            //$thinkFunctionFile = self::$thinkRoot.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.'functions.php';
          //  array_push(self::$functionFiles,$thinkFunctionFile);
//        }
    }

    private static function generateCacheFile($path){

        $mapContent = "<?php\r\nreturn array(\r\n";
        foreach(self::$map as $k=>$v){
            $mapContent .= "\t";
            $mapContent .= "\t\t'".$k."'=>'".$v."',\r\n";
        }
        $mapContent .= ');';
        file_put_contents($path,$mapContent);
    }





}