<?php
/**
 * ThinkPHP 控制器基类 抽象类
 */
abstract class Controller {

    protected /*.object.*/ $view     =  null;

    protected /*.string[string].*/$config   =   array();


    /**
     * 模板显示 调用内置的模板引擎显示方法，
     * 
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @param string $prefix 模板缓存前缀
     * @return void
     */
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
    }

    /**
     * 输出内容文本可以包括Html 并支持内容解析
     * 
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */
    protected function show($content,$charset='',$contentType='',$prefix='') {
    }

    /**
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     * 
     * @param string $templateFile 指定要调用的模板文件
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀*
     * @return string
     */
    protected function fetch($templateFile='',$content='',$prefix='') {
    }

    /**
     *  创建静态页面
     * 
     * @param string $htmlfile 生成的静态文件名称
     * @param string $htmlpath 生成的静态文件路径
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @return string
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
    }

    /**
     * 模板主题设置
     * 
     * @param string $theme 模版主题
     * @return object
     */
    protected function theme($theme){
    }

    /**
     * 模板变量赋值
     * 
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return object
     */
    protected function assign($name,$value='') {
    }


    /**
     * 取得模板显示变量的值
     * 
     * @param string $name 模板显示变量
     * @return mixed
     */
    public function get($name='') {
    }


    /**
     * 魔术方法 有不存在的操作的时候执行
     * 
     * @param string $method 方法名
     * @param array $_args 参数
     * @return mixed
     */
    public function __call($method,$_args) {
    }

    /**
     * 操作错误跳转的快捷方法
     * 
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
    }

    /**
     * 操作成功跳转的快捷方法
     * 
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
    }

    /**
     * Ajax方式返回数据到客户端
     * 
     * @param mixed $data 要返回的数据
     * @param string $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data,$type='',$json_option=0) {
    }

    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * 
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
    }
}

