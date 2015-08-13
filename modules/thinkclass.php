<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
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
/**
 * ThinkPHP REST控制器类
 */
class RestController extends Controller {
    // 当前请求类型
    protected /*.string.*/  $_method        =   '';
    // 当前请求的资源类型
    protected  /*.string.*/ $_type          =   '';
    // REST允许的请求类型列表
    protected /*.string[int].*/  $allowMethod    =   array('get','post','put','delete');
    // REST默认请求类型
    protected /*.string.*/  $defaultMethod  =   'get';
    // REST允许请求的资源类型列表
    protected /*string[int]*/  $allowType      =   array('html','xml','json','rss');
    // 默认的资源类型
    protected  /*.string.*/ $defaultType    =   'html';
    // REST允许输出的资源类型列表
    protected  /*string[string]*/ $allowOutputType=   array(
        'xml' => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    );


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
     * 获取当前请求的Accept头信息
     * @return string
     */
    protected function getAcceptType(){
    }

    // 发送Http状态信息
    /**
     * @param string $code
     * @return void
     */
    protected function sendHttpStatus($code) {
    }

    /**
     * 编码数据
     * 
     * @param mixed $data 要返回的数据
     * @param string $type 返回类型 JSON XML
     * @return string
     */
    protected function encodeData($data,$type='') {
    }

    /**
     * 设置页面输出的CONTENT_TYPE和编码
     * 
     * @param string $type content_type 类型对应的扩展名
     * @param string $charset 页面输出编码
     * @return void
     */
    public function setContentType($type, $charset=''){
    }

    /**
     * 输出返回数据
     * 
     * @param mixed $data 要返回的数据
     * @param string $type 返回类型 JSON XML
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data,$type='',$code=200) {
    }
}

class Model
{

    // 操作状态
    const MODEL_INSERT          =   1;      //  插入模型数据
    const MODEL_UPDATE          =   2;      //  更新模型数据
    const MODEL_BOTH            =   3;      //  包含上面两种方式
    const MUST_VALIDATE         =   1;      // 必须验证
    const EXISTS_VALIDATE       =   0;      // 表单存在字段则验证
    const VALUE_VALIDATE        =   2;      // 表单值不为空则验证

    // 当前数据库操作对象
    protected /*.object.*/$db               =   null;
    // 数据库对象池
    private  /*.resource[int].*/ $_db				=	array();
    // 主键名称
    protected /*.string.*/$pk               =   'id';
    // 主键是否自动增长
    protected /*.bool.*/$autoinc          =   false;
    // 数据表前缀
    protected /*.mixed.*/$tablePrefix      =   null;
    // 模型名称
    protected /*.string.*/$name             =   '';
    // 数据库名称
    protected /*.string.*/$dbName           =   '';
    //数据库配置
    protected /*.string.*/$connection       =   '';
    // 数据表名（不包含表前缀）
    protected /*.string.*/$tableName        =   '';
    // 实际数据表名（包含表前缀）
    protected /*.string.*/$trueTableName    =   '';
    // 最近错误信息
    protected /*.string.*/$error            =   '';
    // 字段信息
    protected /*.string[int].*/$fields           =   array();
    // 数据信息
    protected /*.string[string].*/$data             =   array();
    // 查询表达式参数
    protected /*.string[string].*/$options          =   array();
    protected /*.string[string].*/$_validate        =   array();  // 自动验证定义
    protected /*.string[string].*/$_auto            =   array();  // 自动完成定义
    protected /*.string[string].*/$_map             =   array();  // 字段映射定义
    protected /*.string[int].*/$_scope           =   array();  // 命名范围定义
    // 是否自动检测数据表字段信息
    protected /*.bool.*/$autoCheckFields  =   true;
    // 是否批处理验证
    protected /*.bool.*/$patchValidate    =   false;
    // 链操作方法列表
    protected /*.string[int].*/$methods          =   array('strict','order','alias','having','group','lock','distinct','auto','filter','validate','result','token','index','force');
    /**
     * 架构函数
     * 取得DB类的实例对象 字段检查
     *
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '')
    {
    }

    /**
     * 设置数据对象的值
     *
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name, $value)
    {
    }

    /**
     * 获取数据对象的值
     *
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name)
    {
    }

    /**
     * 检测数据对象的值
     *
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name)
    {
    }

    /**
     * 销毁数据对象的值
     *
     * @param string $name 名称
     * @return void
     */
    public function __unset($name)
    {
    }


    /**
     * 对保存到数据库的数据进行处理
     *
     * @param mixed $data 要操作的数据
     * @return boolean
     */
    protected function _facade($data)
    {
    }

    /**
     * 新增数据
     *
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data = '', $options = array(), $replace = false)
    {
    }

    /**
     * 通过Select方式添加记录
     *
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options 表达式
     * @return boolean
     */
    public function selectAdd($fields = '', $table = '', $options = array())
    {
    }

    /**
     * 保存数据
     *
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data = '', $options = array())
    {
    }

    /**
     * 删除数据
     *
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options = array())
    {
    }

    /**
     * 查询数据集
     *
     * @param array $options 表达式参数
     * @return mixed
     */
    public function select($options = array())
    {
    }

    /**
     * 分析表达式
     *
     * @param array $options 表达式参数
     * @return array
     */
    protected function _parseOptions($options = array())
    {
    }

    /**
     * 数据类型检测
     *
     * @param mixed & $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data, $key)
    {
    }

    /**
     * 数据读取后的处理
     *
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_data($data)
    {
    }

    /**
     * 查询数据
     *
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options = array())
    {
    }

    /**
     * 处理字段映射
     *
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * @return array
     */
    public function parseFieldsMap($data, $type = 1)
    {
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     *
     * @param mixed $field 字段名
     * @param string $value 字段值
     * @return boolean
     */
    public function setField($field, $value = '')
    {
    }

    /**
     * 字段值增长
     *
     * @param string $field 字段名
     * @param integer $step 增长值
     * @param integer $lazyTime 延时时间(s)
     * @return boolean
     */
    public function setInc($field, $step = 1, $lazyTime = 0)
    {
    }

    /**
     * 字段值减少
     *
     * @param string $field 字段名
     * @param integer $step 减少值
     * @param integer $lazyTime 延时时间(s)
     * @return boolean
     */
    public function setDec($field, $step = 1, $lazyTime = 0)
    {
    }

    /**
     * 延时更新检查 返回false表示需要延时
     * 否则返回实际写入的数值
     *
     * @param string $guid 写入标识
     * @param integer $step 写入步进值
     * @param integer $lazyTime 延时时间(s)
     * @return integer
     */
    protected function lazyWrite($guid, $step, $lazyTime)
    {
    }

    /**
     * 获取一条记录的某个字段值
     *
     * @param string $field 字段名
     * @param string $spea 字段数据间隔符号 NULL返回数组
     * @return mixed
     */
    public function getField($field, $spea = null)
    {
    }

    /**
     * 创建数据对象 但不保存到数据库
     *
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
    public function create($data = '', $type = '')
    {
    }

    /**
     * 使用正则验证数据
     *
     * @param string $value 要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value, $rule)
    {
    }

    /**
     * 自动表单验证
     *
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return boolean
     */
    protected function autoValidation($data, $type)
    {
    }

    /**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     *
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationField($data, $val)
    {
    }

    /**
     * 根据验证因子验证字段
     *
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationFieldItem($data, $val)
    {
    }

    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     *
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value, $rule, $type = 'regex')
    {
    }

    /**
     * SQL查询
     *
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     * @return mixed
     */
    public function query($sql, $parse = false)
    {
    }

    /**
     * 执行SQL语句
     *
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     * @return  integer
     */
    public function execute($sql, $parse = false)
    {
    }

    /**
     *
     * @param string $fields
     * @return int
     */
    public function count($fields = '')
    {
    }

    /**
     *
     * @param string $fields
     * @return float
     */
    public function sum($fields = '')
    {
    }
    /**
     *
     * @param string $fields
     * @return float
     */
    public function min($fields = '')
    {
    }
    /**
     *
     * @param string $fields
     * @return float
     */
    public function max($fields = '')
    {
    }

    /**
     *
     * @param string $field
     * @return object
     */
    public function strict($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function filter($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function token($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function result($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function auto($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function alias($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function index($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function distinct($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function lock($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function group($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function having($field = '')
    {
    }
    /**
     *
     * @param string $field
     * @return object
     */
    public function order($field = '')
    {
    }
    /**
     * 解析SQL语句
     *
     * @param string $sql SQL指令
     * @param boolean $parse 是否需要解析SQL
     * @return string
     */
    protected function parseSql($sql, $parse)
    {
    }

    /**
     * 切换当前的数据库连接
     *
     * @param string $linkNum 连接序号
     * @param mixed $config 数据库连接信息
     * @param boolean $force 强制重新连接
     * @return object
     */
    public function db($linkNum = '', $config = '', $force = false)
    {
    }

    /**
     * 设置数据对象值
     *
     * @param mixed $data 数据
     * @return object
     */
    public function data($data = '')
    {
    }

    /**
     * 指定当前的数据表
     *
     * @param mixed $table
     * @return object
     */
    public function table($table)
    {
    }

    /**
     * USING支持 用于多表删除
     *
     * @param mixed $using
     * @return object
     */
    public function using($using)
    {
    }

    /**
     * 查询SQL组装 join
     *
     * @param mixed $join
     * @param string $type JOIN类型
     * @return object
     */
    public function join($join, $type = 'INNER')
    {
    }

    /**
     * 查询SQL组装 union
     *
     * @param mixed $union
     * @param boolean $all
     * @return object
     */
    public function union($union, $all = false)
    {
    }

    /**
     * 查询缓存
     *
     * @param mixed $key
     * @param integer $expire
     * @param string $type
     * @return object
     */
    public function cache($key = true, $expire = 0, $type = '')
    {
    }

    /**
     * 指定查询字段 支持字段排除
     *
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return object
     */
    public function field($field, $except = false)
    {
    }

    /**
     * 调用命名范围
     *
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $_args 参数
     * @return object
     */
    public function scope($scope = '', $_args = array())
    {
    }

    /**
     * 指定查询条件 支持安全过滤
     *
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     * @return object
     */
    public function where($where, $parse = null)
    {
    }

    /**
     * 指定查询数量
     *
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return object
     */
    public function limit($offset, $length = null)
    {
    }

    /**
     * 指定分页
     *
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return object
     */
    public function page($page, $listRows = null)
    {
    }

    /**
     * 查询注释
     *
     * @param string $comment 注释
     * @return object
     */
    public function comment($comment)
    {
    }

    /**
     * 获取执行的SQL语句
     *
     * @param boolean $fetch 是否返回sql
     * @return object
     */
    public function fetchSql($fetch = true)
    {
    }

    /**
     * 参数绑定
     *
     * @param string $key 参数名
     * @param mixed $value 绑定的变量及绑定参数
     * @return object
     */
    public function bind($key, $value = false)
    {
    }

    /**
     * 设置模型的属性值
     *
     * @param string $name 名称
     * @param mixed $value 值
     * @return object
     */
    public function setProperty($name, $value)
    {
    }
}

/**
 * 高级模型扩展
 */
class AdvModel extends Model {
    protected /*.string.*/$optimLock        =   'lock_version';
    protected /*.string.*/$returnType       =   'array';
    protected /*.string[int].*/$blobFields       =   array();
    protected /*.mixed.*/$blobValues       =   null;
    protected /*.string[int].*/$serializeField   =   array();
    protected /*.string[int].*/$readonlyField    =   array();
    protected /*.string[int].*/$_filter          =   array();
    protected /*.string[int].*/$partition        =   array();


    /**
     * 利用__call方法重载 实现一些特殊的Model方法 （魔术方法）
     *
     * @param string $method 方法名称
     * @param mixed $_args 调用参数
     * @return mixed
     */
    public function __call($method,$_args) {
    }

    /**
     * 对保存到数据库的数据进行处理
     *
     * @param mixed $data 要操作的数据
     * @return boolean
     */
    protected function _facade($data) {
    }

    // 查询成功后的回调方法
    /**
     * @param mixed & $result
     * @param string $options
     * @return void
     */
    protected function _after_find(&$result,$options='') {
    }

    // 查询数据集成功后的回调方法
    /**
     * @param mixed & $resultSet
     * @param string $options
     * @return void
     */
    protected function _after_select(&$resultSet,$options='') {
    }

    // 写入前的回调方法
    /**
     * @param mixed & $data
     * @param string $options
     */
    protected function _before_insert(&$data,$options='') {
    }

    /**
     * @param mixed $data
     * @param string $options
     */
    protected function _after_insert($data,$options) {
    }

    // 更新前的回调方法
    /**
     * @param mixed & $data
     * @param string $options
     * @return bool
     */
    protected function _before_update(&$data,$options='') {
    }

    /**
     * @param mixed $data
     * @param string $options
     */
    protected function _after_update($data,$options) {
    }

    /**
     * @param mixed $data
     * @param string $options
     */
    protected function _after_delete($data,$options) {
    }

    /**
     * 记录乐观锁
     *
     * @param array $data 数据对象
     * @return array
     */
    protected function recordLockVersion($data) {
    }

    /**
     * 缓存乐观锁
     *
     * @param array $data 数据对象
     * @return void
     */
    protected function cacheLockVersion($data) {
    }

    /**
     * 检查乐观锁
     *
     * @param integer $id  当前主键
     * @param array & $data  当前数据
     * @return mixed
     */
    protected function checkLockVersion($id,&$data) {
    }

    /**
     * 查找前N个记录
     *
     * @param integer $count 记录个数
     * @param array $options 查询表达式
     * @return array
     */
    public function topN($count,$options=array()) {
    }

    /**
     * 查询符合条件的第N条记录
     * 0 表示第一条记录 -1 表示最后一条记录
     *
     * @param integer $position 记录位置
     * @param array $options 查询表达式
     * @return mixed
     */
    public function getN($position=0,$options=array()) {
    }

    /**
     * 获取满足条件的第一条记录
     *
     * @param array $options 查询表达式
     * @return mixed
     */
    public function first($options=array()) {
    }

    /**
     * 获取满足条件的最后一条记录
     *
     * @param array $options 查询表达式
     * @return mixed
     */
    public function last($options=array()) {
    }

    /**
     * 返回数据
     *
     * @param array $data 数据
     * @param string $type 返回类型 默认为数组
     * @return mixed
     */
    public function returnResult($data,$type='') {
    }

    /**
     * 获取数据的时候过滤数据字段
     *
     * @param mixed & $result 查询的数据
     * @return array
     */
    protected function getFilterFields(&$result) {
    }

    /**
     * @param mixed & $resultSet
     * @return mixed
     */
    protected function getFilterListFields(&$resultSet) {
    }

    /**
     * 写入数据的时候过滤数据字段
     *
     * @param mixed $data 查询的数据
     * @return array
     */
    protected function setFilterFields($data) {
    }

    /**
     * 返回数据列表
     *
     * @param array & $resultSet 数据
     * @param string $type 返回类型 默认为数组
     * @return void
     */
    protected function returnResultSet(&$resultSet,$type='') {
    }

    /**
     * @param mixed & $data
     * @return mixed
     */
    protected function checkBlobFields(&$data) {
    }

    /**
     * 获取数据集的文本字段
     *
     * @param mixed & $resultSet 查询的数据
     * @param string $field 查询的字段
     * @return void
     */
    protected function getListBlobFields(&$resultSet,$field='') {
    }

    /**
     * 获取数据的文本字段
     *
     * @param mixed & $data 查询的数据
     * @param string $field 查询的字段
     * @return void
     */
    protected function getBlobFields(&$data,$field='') {
    }

    /**
     * 保存File方式的字段
     *
     * @param mixed & $data 保存的数据
     * @return void
     */
    protected function saveBlobFields(&$data) {
    }

    /**
     * 删除File方式的字段
     *
     * @param mixed & $data 保存的数据
     * @param string $field 查询的字段
     * @return void
     */
    protected function delBlobFields(&$data,$field='') {
    }

    /**
     * 检查序列化数据字段
     *
     * @param array & $data 数据
     * @return array
     */
    protected function serializeField(&$data) {
    }

    // 检查返回数据的序列化字段
    /**
     * @param mixed & $result
     * @return mixed
     */
    protected function checkSerializeField(&$result) {
    }

    // 检查数据集的序列化字段
    /**
     * @param mixed & $resultSet
     * @return mixed
     */
    protected function checkListSerializeField(&$resultSet) {
    }

    /**
     * 检查只读字段
     *
     * @param array & $data 数据
     * @return array
     */
    protected function checkReadonlyField(&$data) {
    }

    /**
     * 批处理执行SQL语句
     * 批处理的指令都认为是execute操作
     *
     * @param array $sql  SQL批处理指令
     * @return boolean
     */
    public function patchQuery($sql=array()) {
    }

    /**
     * 得到分表的的数据表名
     *
     * @param array $data 操作的数据
     * @return string
     */
    public function getPartitionTableName($data=array()) {
    }
}

class MergeModel extends Model {

    protected /*string[int]*/ $modelList    =   array();    //  包含的模型列表 第一个必须是主表模型
    protected /*.string.*/$masterModel  =   '';         //  主模型
    protected /*.string.*/$joinType     =   'INNER';    //  聚合模型的查询JOIN类型
    protected /*.string.*/$fk           =   '';         //  外键名 默认为主表名_id
    protected /*.string[string].*/$mapFields    =   array();    //  需要处理的模型映射字段，避免混淆 array( id => 'user.id'  )


    /**
     * 得到完整的数据表名
     *
     * @return string
     */
    public function getTableName() {
    }


    /**
     * 新增聚合数据
     *
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param bool $replace 是否replace
     * @return mixed
     */
    public function add($data='',$options=array(),$replace=false){
    }

    /**
     * 对保存到数据库的数据进行处理
     *
     * @param mixed $data 要操作的数据
     * @return bool
     */
    protected function _facade($data) {
    }

    /**
     * 保存聚合模型数据
     *
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data='',$options=array()){
    }

    /**
     * 删除聚合模型数据
     *
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options=array()){
    }

    /**
     * 表达式过滤方法
     *
     * @param string & $options 表达式
     * @return void
     */
    protected function _options_filter(&$options) {
    }

    /**
     * 检查条件中的聚合字段
     *
     * @param mixed $where 条件表达式
     * @return array
     */
    protected function checkCondition($where) {
    }

    /**
     * 检查Order表达式中的聚合字段
     *
     * @param string $order 字段
     * @return string
     */
    protected function checkOrder($order='') {
    }

    /**
     * 检查Group表达式中的聚合字段
     *
     * @param string $group 字段
     * @return string
     */
    protected function checkGroup($group='') {
    }

    /**
     * 检查fields表达式中的聚合字段
     *
     * @param string $fields 字段
     * @return string
     */
    protected function checkFields($fields='') {
    }

    /**
     * 获取数据表字段信息
     *
     * @return array
     */
    public function getDbFields(){
    }
}
/**
 * ThinkPHP关联模型扩展
 */
class RelationModel extends Model {

    const   HAS_ONE     =   1;
    const   BELONGS_TO  =   2;
    const   HAS_MANY    =   3;
    const   MANY_TO_MANY=   4;

    // 关联定义
    protected /*.resource[int].*/   $_link = array();


    /**
     * 得到关联的数据表名
     *
     * @param object $relation
     * @return string
     */
    public function getRelationTableName($relation) {
    }

    // 查询成功后的回调方法
    /**
     * @param mixed & $result
     * @param mixed $options
     */
    protected function _after_find(&$result,$options) {
    }

    // 查询数据集成功后的回调方法
    /**
     * @param mixed & $result
     * @param mixed[] $options
     */
    protected function _after_select(&$result,$options) {
    }

    // 写入成功后的回调方法
    /**
     * @param mixed $data
     * @param mixed[] $options
     */
    protected function _after_insert($data,$options) {
    }

    // 更新成功后的回调方法
    /**
     * @param mixed $data
     * @param mixed[] $options
     * @return void
     */
    protected function _after_update($data,$options) {
    }

    // 删除成功后的回调方法
    /**
     * @param mixed $data
     * @param mixed[] $options
     * @return void
     */
    protected function _after_delete($data,$options) {
    }

    /**
     * 对保存到数据库的数据进行处理
     *
     * @param mixed $data 要操作的数据
     * @return bool
     */
    protected function _facade($data) {
    }

    /**
     * 获取返回数据集的关联记录
     *
     * @param array & $resultSet  返回数据
     * @param mixed $name  关联名称
     * @return array
     */
    protected function getRelations(&$resultSet,$name='') {
    }

    /**
     * 获取返回数据的关联记录
     *
     * @param mixed & $result  返回数据
     * @param mixed $name  关联名称
     * @param boolean $_return 是否返回关联数据本身
     * @return array
     */
    protected function getRelation(&$result,$name='',$_return=false) {
    }

    /**
     * 操作关联数据
     *
     * @param string $opType  操作方式 ADD SAVE DEL
     * @param mixed $data  数据对象
     * @param string $name 关联名称
     * @return mixed
     */
    protected function opRelation($opType,$data='',$name='') {
    }

    /**
     * 进行关联查询
     *
     * @param mixed $name 关联名称
     * @return object
     */
    public function relation($name) {
    }

    /**
     * 关联数据获取 仅用于查询后
     *
     * @param string $name 关联名称
     * @return array
     */
    public function relationGet($name) {
    }
}

/**
 * ThinkPHP视图模型扩展
 */
class ViewModel extends Model {

    protected /*.string[int].*/ $viewFields = array();


    /**
     * 得到完整的数据表名
     *
     * @return string
     */
    public function getTableName() {
    }

    /**
     * 表达式过滤方法
     *
     * @param string & $options 表达式
     * @return void
     */
    protected function _options_filter(&$options) {
    }


    /**
     * 检查条件中的视图字段
     *
     * @param mixed $where 条件表达式
     * @return array
     */
    protected function checkCondition($where) {
    }

    /**
     * 检查Order表达式中的视图字段
     *
     * @param string $order 字段
     * @return string
     */
    protected function checkOrder($order='') {
    }

    /**
     * 检查Group表达式中的视图字段
     *
     * @param string $group 字段
     * @return string
     */
    protected function checkGroup($group='') {
    }

    /**
     * 检查fields表达式中的视图字段
     *
     * @param string $fields 字段
     * @return string
     */
    protected function checkFields($fields='') {
    }
}

/**
 * 日志处理类
 */
class Log {

    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志信息
    static protected /*.mixed[].*/$log       =  array();

    // 日志存储
    static protected /*.object.*/$storage   =   null;

    // 日志初始化
    /**
     * @param mixed[] $config
     */
    static public function init($config=array()){
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @return void
     */
    static function record($message,$level=self::ERR,$record=false) {
    }

    /**
     * 日志保存
     * @param string $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function save($type='',$destination='') {
    }

    /**
     * 日志直接写入
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param string $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function write($message,$level=self::ERR,$type='',$destination='') {
    }
}

