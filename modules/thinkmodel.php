<?php
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

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
    public function __construct($name = '', $tablePrefix = '', $connection = ''){}

    /**
     * 设置数据对象的值
     *
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name, $value){}

    /**
     * 获取数据对象的值
     *
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name){}

    /**
     * 检测数据对象的值
     *
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name){}

    /**
     * 销毁数据对象的值
     *
     * @param string $name 名称
     * @return void
     */
    public function __unset($name){}


    /**
     * 对保存到数据库的数据进行处理
     *
     * @param mixed $data 要操作的数据
     * @return boolean
     */
    protected function _facade($data){}

    /**
     * 新增数据
     *
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data = '', $options = array(), $replace = false){}

    /**
     * 通过Select方式添加记录
     *
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options 表达式
     * @return boolean
     */
    public function selectAdd($fields = '', $table = '', $options = array()){}

    /**
     * 保存数据
     *
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data = '', $options = array()){}

    /**
     * 删除数据
     *
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options = array()){}

    /**
     * 查询数据集
     *
     * @param array $options 表达式参数
     * @return mixed
     */
    public function select($options = array()){}

    /**
     * 分析表达式
     *
     * @param array $options 表达式参数
     * @return array
     */
    protected function _parseOptions($options = array()){}

    /**
     * 数据类型检测
     *
     * @param mixed & $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data, $key){}

    /**
     * 数据读取后的处理
     *
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_data($data){}

    /**
     * 查询数据
     *
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options = array()){}

    /**
     * 处理字段映射
     *
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * @return array
     */
    public function parseFieldsMap($data, $type = 1){}

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     *
     * @param mixed $field 字段名
     * @param string $value 字段值
     * @return boolean
     */
    public function setField($field, $value = ''){}

    /**
     * 字段值增长
     *
     * @param string $field 字段名
     * @param integer $step 增长值
     * @param integer $lazyTime 延时时间(s)
     * @return boolean
     */
    public function setInc($field, $step = 1, $lazyTime = 0){}

    /**
     * 字段值减少
     *
     * @param string $field 字段名
     * @param integer $step 减少值
     * @param integer $lazyTime 延时时间(s)
     * @return boolean
     */
    public function setDec($field, $step = 1, $lazyTime = 0){}

    /**
     * 延时更新检查 返回false表示需要延时
     * 否则返回实际写入的数值
     *
     * @param string $guid 写入标识
     * @param integer $step 写入步进值
     * @param integer $lazyTime 延时时间(s)
     * @return integer
     */
    protected function lazyWrite($guid, $step, $lazyTime){}

    /**
     * 获取一条记录的某个字段值
     *
     * @param string $field 字段名
     * @param string $spea 字段数据间隔符号 NULL返回数组
     * @return mixed
     */
    public function getField($field, $spea = null){}

    /**
     * 创建数据对象 但不保存到数据库
     *
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
    public function create($data = '', $type = ''){}

    /**
     * 使用正则验证数据
     *
     * @param string $value 要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value, $rule){}

    /**
     * 自动表单验证
     *
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return boolean
     */
    protected function autoValidation($data, $type){}

    /**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     *
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationField($data, $val){}

    /**
     * 根据验证因子验证字段
     *
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationFieldItem($data, $val){}

    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     *
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value, $rule, $type = 'regex'){}

    /**
     * SQL查询
     *
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     * @return mixed
     */
    public function query($sql, $parse = false){}

    /**
     * 执行SQL语句
     *
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     * @return  integer
     */
    public function execute($sql, $parse = false){}

    /**
     *
     * @param string $fields
     * @return int
     */
    public function count($fields = ''){}

    /**
     *
     * @param string $fields
     * @return float
     */
    public function sum($fields = ''){}
    /**
     *
     * @param string $fields
     * @return float
     */
    public function min($fields = ''){}
    /**
     *
     * @param string $fields
     * @return float
     */
    public function max($fields = ''){}

    /**
     *
     * @param string $field
     * @return object
     */
    public function strict($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function filter($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function token($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function result($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function auto($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function alias($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function index($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function distinct($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function lock($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function group($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function having($field = ''){}
    /**
     *
     * @param string $field
     * @return object
     */
    public function order($field = ''){}
    /**
     * 解析SQL语句
     *
     * @param string $sql SQL指令
     * @param boolean $parse 是否需要解析SQL
     * @return string
     */
    protected function parseSql($sql, $parse){}

    /**
     * 切换当前的数据库连接
     *
     * @param string $linkNum 连接序号
     * @param mixed $config 数据库连接信息
     * @param boolean $force 强制重新连接
     * @return object
     */
    public function db($linkNum = '', $config = '', $force = false){}

    /**
     * 设置数据对象值
     *
     * @param mixed $data 数据
     * @return object
     */
    public function data($data = ''){}

    /**
     * 指定当前的数据表
     *
     * @param mixed $table
     * @return object
     */
    public function table($table){}

    /**
     * USING支持 用于多表删除
     *
     * @param mixed $using
     * @return object
     */
    public function using($using){}

    /**
     * 查询SQL组装 join
     *
     * @param mixed $join
     * @param string $type JOIN类型
     * @return object
     */
    public function join($join, $type = 'INNER'){}

    /**
     * 查询SQL组装 union
     *
     * @param mixed $union
     * @param boolean $all
     * @return object
     */
    public function union($union, $all = false){}

    /**
     * 查询缓存
     *
     * @param mixed $key
     * @param integer $expire
     * @param string $type
     * @return object
     */
    public function cache($key = true, $expire = 0, $type = ''){}

    /**
     * 指定查询字段 支持字段排除
     *
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return object
     */
    public function field($field, $except = false){}

    /**
     * 调用命名范围
     *
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $_args 参数
     * @return object
     */
    public function scope($scope = '', $_args = array()){}

    /**
     * 指定查询条件 支持安全过滤
     *
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     * @return object
     */
    public function where($where, $parse = null){}

    /**
     * 指定查询数量
     *
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return object
     */
    public function limit($offset, $length = null){}

    /**
     * 指定分页
     *
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return object
     */
    public function page($page, $listRows = null){}

    /**
     * 查询注释
     *
     * @param string $comment 注释
     * @return object
     */
    public function comment($comment){}

    /**
     * 获取执行的SQL语句
     *
     * @param boolean $fetch 是否返回sql
     * @return object
     */
    public function fetchSql($fetch = true){}

    /**
     * 参数绑定
     *
     * @param string $key 参数名
     * @param mixed $value 绑定的变量及绑定参数
     * @return object
     */
    public function bind($key, $value = false){}

    /**
     * 设置模型的属性值
     *
     * @param string $name 名称
     * @param mixed $value 值
     * @return object
     */
    public function setProperty($name, $value){}
}


