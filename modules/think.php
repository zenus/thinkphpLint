<?php
define('CONF_PARSE','1');
define('EXT','2');
define('DATA_PATH','2');
/**
 * 获取和设置配置参数 支持批量定义
 * @param string $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $def 默认值
 * @return mixed
 */
function C($name=null, $value=null,$def=null){}
/**
 * 加载配置文件 支持格式转换 仅支持一级配置
 * @param string $file 配置文件名
 * @param string $parse 配置解析方法 有些格式需要用户自己解析
 * @return array
 */
function load_config($file,$parse=CONF_PARSE){}
/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @throws Think\Exception
 * @return void
 */
function E($msg, $code=0){}
/**
 * 解析yaml文件返回一个数组
 * @param string $file 配置文件名
 * @return array
 */
function yaml_parse_file($file) {}

/**
 * 获取和设置语言定义(不区分大小写)
 * @param string $name 语言变量
 * @param mixed $value 语言值或者变量
 * @return mixed
 */

function L($name=null, $value=null){}
/**
 * 添加和获取页面Trace记录
 * @param string $value 变量
 * @param string $label 标签
 * @param string $level 日志级别
 * @param boolean $record 是否记录日志
 * @return void
 */
function trace($value='[think]',$label='',$level='DEBUG',$record=false){}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param mixed $dec 小数位或者m
 * @return mixed
 */
function G($start,$end='',$dec=4) {}

/**
 * 编译文件
 * @param string $filename 文件名
 * @return string
 */
function compile($filename){}

/**
 * 获取模版文件 格式 资源://模块@主题/控制器/操作
 * @param string $template 模版资源地址
 * @param string $layer 视图层（目录）名称
 * @return string
 */
function T($template='',$layer=''){}

/**
 * @param mixed $filter
 * @param array $data
 * @return array
 */
function array_map_recursive($filter, $data) {}

/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @param boolean $save 是否保存结果
 * @return mixed
 */
function N($key, $step=0,$save=false) {}


/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type=0){}
/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function require_cache($filename){}
/**
 * 区分大小写的文件存在判断
 * @param string $filename 文件地址
 * @return boolean
 */
function file_exists_case($filename){}
/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * @param string $_class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolean
 */
function import($_class, $baseUrl = '', $ext=EXT){}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $_default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @param mixed $datas 要获取的额外数据源
 * @return mixed
 */
function I($name,$_default='',$filter=null,$datas=null) {}
/**
 * 基于命名空间方式导入函数库
 * load('@.Util.Array')
 * @param string $name 函数库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return void
 */
function load($name, $baseUrl='', $ext='.php'){}
/**
 * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
 * @param string $_class 类库
 * @param string $baseUrl 基础目录
 * @param string $ext 类库后缀
 * @return boolean
 */
function vendor($_class, $baseUrl = '', $ext='.php'){}
/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return object
 */
function M($name='', $tablePrefix='',$connection=''){}
/**
 * 解析资源地址并导入类库文件
 * 例如 module/controller addon://module/behavior
 * @param string $name 资源地址 格式：[扩展://][模块/]资源名
 * @param string $layer 分层名称
 * @param integer $level 控制器层次
 * @return string
 */
function parse_res_name($name,$layer,$level=1){}
/**
 * 用于实例化访问控制器
 * @param string $name 控制器名
 * @param string $path 控制器命名空间（路径）
 * @return object
 */
function controller($name,$path=''){}

/**
 * 实例化多层控制器 格式：[资源://][模块/]控制器
 * @param string $name 资源地址
 * @param string $layer 控制层名称
 * @param integer $level 控制器层次
 * @return object
 */
function A($name,$layer='',$level=0) {}

/**
 * 远程调用控制器的操作方法 URL 参数格式 [资源://][模块/]控制器/操作
 * @param string $url 调用地址
 * @param mixed $vars 调用参数 支持字符串和数组
 * @param string $layer 要调用的控制层名称
 * @return mixed
 */
function R($url,$vars=array(),$layer='') {}

/**
 * 处理标签扩展
 * @param string  $tag 标签名称
 * @param mixed & $params 传入参数
 * @return void
 */
function tag($tag, &$params=NULL){}
/**
 * 执行某个行为
 * @param string $name 行为名称
 * @param string $tag 标签名称（行为类无需传入）
 * @param mixed & $params 传入的参数
 * @return void
 */
function B($name, $tag='',&$params=NULL){}
/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content){}
/**
 * 自定义异常处理
 * @param string $msg 异常消息
 * @param string $type 异常类型 默认为Think\Exception
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function throw_exception($msg, $type='Think\\Exception', $code=0){}
/**
 * 浏览器友好的变量输出
 * @param mixed $_var 变量
 * @param boolean $_echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return mixed
 */
function dump($_var, $_echo=true, $label=null, $strict=true){}

/**
 * 实例化模型类 格式 [资源://][模块/]模型
 * @param string $name 资源地址
 * @param string $layer 模型层名称
 * @return object
 */
function D($name='',$layer='') {}
/**
 * 设置当前页面的布局
 * @param mixed $layout 布局名称 为false的时候表示关闭布局
 * @return void
 */
function layout($layout){}

/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param mixed $vars 传入的参数，支持数组和字符串
 * @param mixed $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function U($url='',$vars='',$suffix=true,$domain=false) {}
/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传入的参数
 * @return void
 */
function W($name, $data=array()){}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg=''){}
/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name,$value='',$options=null){}
/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function F($name, $value='', $path=DATA_PATH){}
/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix){}
/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8'){}
/**
 * 数据XML编码
 * @param mixed  $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item='item', $id='id'){}
/**
 * session管理函数
 * @param mixed $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name='',$value=''){}
/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $option cookie参数
 * @return mixed
 */
function cookie($name='', $value='', $option=null){}
/**
 * 加载动态扩展文件
 * @param string $path 文件路径
 * @return void
 */
function load_ext_file($path){}
/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false){}
/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code){}
