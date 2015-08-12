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
    private static $thinkRoot = '';
    private static $functionFiles = array();
    private static $runName = null;
    private static $runCwd = null;
    private static $namespace = null;
    private static $phplRoot = null;

    /**
     * @param $globals
     */
    public static function bootstrap($arg,GlobalsImplementation $globals){
        self::getPhplRoot();
        //关联命名空间与目录
        self::$runCwd  = getcwd();
        self::$runName = $arg;
       // $rawPath = self::getRawFilePath();
        $root = $globals->project_root->getBaseName();
        $root = FileName::encode($globals->project_root->getBaseName());
        $root = str_replace('/',DIRECTORY_SEPARATOR,$root);
        self::buildMap($root);
        //生成分析文件
        return  self::createRunFile();
    }

    public static function destruct(){
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
        die(__DIR__);

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
            $content = self::removeHeaderBlock();
            $help = '';

            //加载phplint模块
            $help .= self::attachLintModules();

            //加载thinkphp函数
            $help .= self::attachThinkFunctions();

            //加载用户自定函数
            //$help .= self::attachDeveloperFunctions();

            //加载要use的文件
            //$help .= self::attachUseClass();
            $header = self::getHeaderBlock();
            $content = $header.$help.$content;
            $path = self::getRunFilePath();
            try{
                file_put_contents($path,$content);
            }catch(\Exception $e){
                exit("can not write run file : {$path} ");
            }
        return self::buildRunName();
    }

    private static function getHeaderBlock(){

        return "<?php\r\n"."namespace\t".self::$namespace.";\r\n";
    }

    private static function removeHeaderBlock(){
        $content = self::getRawContent();
        $content = str_replace("<?php",'',$content);
        $pattern = '#namespace\s+(.*?);#';
        if(preg_match($pattern,$content,$matches) != false){
            self::$namespace = $matches[1];
            $content = preg_replace($pattern,'',$content);
        }
        return $content;

    }
    //use Common\Controller\RestBaseController;
    private static function attachUseClass(){
        $content = self::getRawContent();
        $pattern = "#use\s+(.*)?;#";
        $uses = '';
        if(preg_match_all($pattern,$content,$matches) != false){
            if(!empty($matches[1])){
                foreach($matches[1] as $use){
                    if(isset(self::$map[$use])){
                        $_use = str_replace('\\','/',self::$map[$use]);
                        $uses .= "require_once '".$_use."';\r\n";
                    }
                }
            }
        }
        return $uses;
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

        return '.'.self::$runName;
    }


    private static function getRawFilePath(){

        return  self::$runCwd.DIRECTORY_SEPARATOR.self::$runName;
    }

    private static  function getRunFilePath(){
        return  self::$runCwd.DIRECTORY_SEPARATOR.self::buildRunName();
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
            'xsl','yp','zip','zlib','think','developer');
        $comment ="/*.\r\n";
        foreach($modules as $module){
            $comment .= "\trequire_module '".$module."';\r\n";
        }
        $comment .=".*/\r\n";
        return $comment;
    }

    private static function attachThinkFunctions(){

        return '';
        $thinkFunctionFile = self::$thinkRoot.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.'functions.php';
        try{
            $content = file_get_contents($thinkFunctionFile);
            $pattern ='#(/\*{2}[^/]+/)\s+function\s+(\w+)\([^{]+\)#';
           if(preg_match_all($pattern,$content,$matches)){
               $module = "<?php\r\n";
               foreach($matches[0] as $v){
                   $module .= $v.'{}'."\r\n";
               }
               $file = self::$runCwd.DIRECTORY_SEPARATOR.'think.php';
               file_put_contents($file,$module);
           }
            die;
        }catch(\Exception $e){
            die($e->getMessage());
        }
    }

    private static function attachDeveloperFunctions(){

        $requires = '';
        if(!empty(self::$functionFiles)){
            foreach(self::$functionFiles as $file){
                $file = str_replace('\\','/',$file);
                try{
                    $content = file_get_contents($file);
                    $pattern ='#(/\*{2}[^/]+/)\s+function\s+(\w+)\([^{]+\)#';
                    if(preg_match_all($pattern,$content,$matches)){
                        $module = "<?php\r\n";
                        foreach($matches[0] as $v){
                            $module .= $v.'{}'."\r\n";
                        }
                        $file = self::$runCwd.DIRECTORY_SEPARATOR.'developer.php';
                        file_put_contents($file,$module,FILE_APPEND | LOCK_EX);
                    }
                }catch(\Exception $e){
                    die($e->getMessage());
                }
            }
        }
        return $requires;
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
     * @param $funcName
     */
    public static function searchFunc($funcName,GlobalsImplementation $globals){
        if(isset(self::$functions[$funcName])){
            $pkg = $globals->curr_pkg;
            $scanner = $pkg->scanner;
            try {
                $fn = File::fromLocaleEncoded(self::$functions[$funcName]);
            } catch (InvalidArgumentException $e) {
                $globals->logger->error($scanner->here(),
                    "invalid file \"" . self::$functions[$funcName] . "\": "
                    . $e->getMessage() . "\nHint: expected absolute path (PHPLint safety restriction); under PHP5 the magic constant __DIR__ gives the directory of the current source.");
                $fn = NULL;
            }
            if( $fn != NULL ){
                if($globals->logger->main_file_name == $globals->logger->current_file_name) {
                    $globals->loadPackage($fn, FALSE);
                    $pkg = $globals->getPackage($fn);
                    if ($pkg !== NULL && !$pkg->is_library) {
                        $globals->logger->error($scanner->here(),
                            "package " . $globals->logger->formatFileName($fn)
                            . " is not a library:\n" . $pkg->why_not_library);
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