<?php
/**
 *    获取环境变量
 *
 *    @param     string $key
 *    @param     mixed  $val
 *    @return    mixed
 */
function env($key, $val = null){}
/**
 * 为SQL查询创建LIMIT条件
 * @param int $page
 * @param int $limit
 * @return array
 */
function build_limit( $page = 1, $limit = 10 ){}
/**
 * 获得插件对象
 * @param string $name		插件标识符
 * @return Object
 */
function plugin( $name ){}
/**
 * 获得插件后台管理对象
 * @param string $name		插件标识符
 * @return Object
 */
function aplugin( $name ){}
/**
 * 验证手机号
 * @param string $phone		手机号
 * @return bool
 */
function is_mobile_phone($phone){}
/**
 * 分页函数
 *
 * @param string $page			当前页
 * @param string $pagesize		每页条数
 * @param string $total			总记录数
 * @param string $pageurl		分页链接
 * @param string $style			分页样式
 * @param string $leftoffset	左偏移页数
 * @param string $rightoffset	右偏移页数
 * @param string $ajaxfunc		点击链接时执行的js方法
 * @return string
 */
function page($page, $pagesize, $total, $pageurl, $style=3, $leftoffset=5, $rightoffset=5, $ajaxfunc=''){}
/**
 * 验证表单令牌是否合法
 *
 * @param array $data
 * @return bool
 */
function checkFormToken($data, $name=null){}
/**
 * 生成表单令牌
 *
 * @return string
 */
function buildFormToken($tokenName=null){}
/**
 * 将UNIX时间戳转换成本地时间
 * @param string $time
 * @param string $format
 * @return string
 */
function local_date($format = NULL, $time = null){}
/**
 * 取得某月天数,可用于任意月份
 * @param string $month		月份
 * @param string $year		年份
 * @return number
 */
function days( $month, $year ){}
/**
 * 二维数组排序  根据某个值排序
 * @param	array	$arr 数组
 * @param	string	$keys 用来排序的值
 * @param	string	$type 升序和降序
 * @return	array|bool
 *
 */
function array_sort($arr, $keys, $type = 'asc'){}
/**
 * 递归方式的对变量中的特殊字符进行转义
 * @param   mix     $value
 * @return  mix
 */
function addslashes_deep( $value ){}
/**
 * 将对象成员变量或者数组的特殊字符进行转义
 * @param    mix        $obj      对象或者数组
 * @return   mix                  对象或者数组
 */
function addslashes_deep_obj( $obj ){}
/**
 * 递归方式的对变量中的特殊字符去除转义
 * @param   mix     $value
 * @return  mix
 */
function stripslashes_deep( $value ){}
/**
 * 去除危险字符，防止SQL攻击
 * @param	string	$string;
 * @return	string
 */
function safe4search( $string ){}
/**
 *  将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
 *
 * @access  public
 * @param   string       $str         待转换字串
 *
 * @return  string       $str         处理后字串
 */
function make_semiangle($str){}
/**
 * 电话号码或是手机号码 隐藏中间数字，以星号代替  例：133****1111
 * @param unknown $phone
 * @return mixed
 */
function hidden_phone($phone){}
/**
 * 车牌号 隐藏中间数字，以星号代替  例：粤A****1
 * @param unknown $car_no
 * @return mixed
 */
function hidden_carno($car_no){}
/**
 * 获取根域名
 * @param string $host
 * @return string
 */
function get_top_domain($host){}
/**
 * 获取上传文件的url访问地址
 * @param string $filepath
 * @return string
 */
function FixedUploadedFileUrl($fileurl){}
/**
 * 获取缩略图URL
 * @param string $srcfile		源文件
 * @param int $tow			宽度
 * @param int $toh			高度
 * @param int $wforce		是否限制图片宽度，1：限制，0：不限制
 * @param int $hforce		是否限制图片高度，1：限制，0：不限制
 * @return string
 */
function thumb($srcfile, $tow = 100, $toh = 100, $wforce = 0, $hforce = 0){}
/**
 * 生成缩略图
 * @param string $srcfile		源文件
 * @param int $tow			宽度
 * @param int $toh			高度
 * @param int $wforce		是否限制图片宽度，1：限制，0：不限制
 * @param int $hforce		是否限制图片高度，1：限制，0：不限制
 * @return string
 */
function make_thumb($srcfile, $tow = 100, $toh = 100, $wforce = 0, $hforce = 0){}
/**
 * 字符编码转换
 *
 * @param string $source_lang		原编码
 * @param string $target_lang		目标编码
 * @param string $source_string		欲转换的字符
 * @return string
 */
function ecs_iconv( $source_lang, $target_lang, $source_string = '' ){}
/**
 * HTML标签闭合函数
 * @param string $html
 * @return string
 * @author Milian Wolff
 */
function closetags( $html ){}
/**
 * 验证输入的邮件地址是否合法
 *
 * @access  public
 * @param   string      $email      需要验证的邮件地址
 *
 * @return bool
 */
function is_email( $user_email ){}
/**
 * 发送邮件
 *
 * @param strin $email		收信人邮件地址
 * @param string $subject	邮件主题
 * @param string $content	邮件内容
 * @param int $type			邮件格式，0：非html、1：html
 * @param bool $immediately 立即发送
 * @return bool
 */
function send_mail($email, $subject, $content, $type = 0, $immediately = false){}
/**
 * 为变量设置一个默认值
 * @param val $param
 * @param string $val
 * @return val
 */
function defaultVal($param, $val = ''){}
/**
 * 加密解密函数
 * 该函数来源于DISCUZ
 * @param string $string		欲加密、解密的字符串
 * @param string $operation		DECODE表示解密,其它表示加密
 * @param string $key			密匙
 * @param number $expiry		密文有效期
 * @return string
 */
function authcode( $string, $operation = 'DECODE', $key = '', $expiry = 0 ){}
/**
 * 加密函数
 *
 * @param string $str 加密前的字符串
 * @param string $key 密钥
 * @return string 加密后的字符串
 */
function encrypt( $str, $key = '' ){}
/**
 * 解密函数
 *
 * @param string $str 加密后的字符串
 * @param string $key 密钥
 * @return string 加密前的字符串
 */
function decrypt( $str, $key = '' ){}
/**
 * 用户密码验证函数
 *
 * @param string $password 要验证的密码明文
 * @param string $salt     加密盐
 * @param string $crypt   密文
 * @return bool
 */
function  valid($password,$salt,$crypt){}
/**
 * 取数组中指定键值的元素
 * @example $ages  = ['lb'=>27,'cnn'=>23,'mm'=>'22','cc'=>21];
 * $filters = array_only($ages,['cnn']);
 * print_r 将会是 ['cnn']
 * @param array $array
 * @param array $keys
 * @return array
 */
function array_only($array, $keys){}
/**
 * 取数组中除去指定键值的元素
 * @example $ages  = ['lb'=>27,'cnn'=>23,'mm'=>'22','cc'=>21];
 * $filters = array_except($ages,['cnn']);
 * print_r 将会是 ['lb','mm','cc']
 * @param array $array
 * @param  array $keys
 * @return array
 */
function array_except($array, $keys){}
/**
 * 取数组中的元素 如果不存在可指定默认值 键支持 ' world.country.province.city'格式
 * @example  $city = array_get($address,'world.contry.province.city','shanghai');
 * @param $array
 * @param $key
 * @param $default
 * @return mixed
 */
function array_get($array, $key, $default = null){}
/**
 * 以curl方式发送数据
 *
 * @param string        $url        接收数据的url
 * @param string        $data       需要发送的数据,如：a=1&b=2&c=3
 * @param bool          $post       是否以post方式提交,false为get 方式
 */
function dcurl( $url, $data, $post = true ){}
/**
 *
 * @author				c.k xiao <jihaoju@qq.com>
 * @time				2015-3-31 下午3:17:17
 * @param	string		$url		请求的网址
 * @param	int			$limit		返回值长度
 * @param	string		$post		post数据，有值时将以post方式提交请求，例：a=1&b=2
 * @param	string		$cookie		附加cookie
 * @param	bool		$bysocket
 * @param	string		$ip			主机IP，不为空时header中的host即为$ip，否则为$url解析出的host
 * @param	int			$timeout	超时时间
 * @param	bool		$block		是否为阻塞模式
 * @return	mixed|string
 *
 */
function dfopen( $url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE ){}
/**
 * 生成一定范围内的随机数
 * @param int $b	下限
 * @param int $e	上限
 * @return int
 */
function rnd_number( $b = 1, $e = 100 ){}
/**
 * 将URL转换为短网址
 * @param unknown $url
 * @return Ambigous <>|string
 */
function convert_short_url( $url ){}
/**
 *计算某个经纬度的周围某段距离的正方形的四个点
 *
 *@param lng float 经度
 *@param lat float 纬度
 *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
 *@return array 正方形的四个点的经纬度坐标
 */
function squarePoint( $lng, $lat, $distance = 0.5 ){}
/**
 * 根据地址返回经纬度坐标
 * @param	string	$addr	地址，例：广州市天河区体育西广利路77号东洲大厦B座
 * @param	string	$city	城市，广州
 * @return	array|bool
 *
 */
function geocoder( $addr, $city ){}
/**
 * 根据地球上任意两点的经纬度计算两点间的距离
 * @return	float
 *
 */
function point_distance($lat1, $lng1, $lat2, $lng2){}
/**
 * utf8字符转Unicode字符
 * @param string $char 要转换的单字符
 * @return string
 */
function utf8_to_unicode( $char ){}
/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @param string $depart 分隔,默认为空格为单字
 * @return string
 */
function str_to_unicode_word( $str, $depart = ' ' ){}
/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @return string
 */
function str_to_unicode_string( $str ){}
/**
 * 中文分词
 * @param string 	$text	欲分词的文本
 * @param int 		$num	分词数
 * @return array
 */
function segment( $text, $num = null ){}
/**
 * 修正目录分隔符
 * @param string $path
 * @return string
 */
function dir_path( $path ){}
/**
 * 递归删除目录
 *
 * @param string $dir
 * @return bool
 */
function del_dir( $dir ){}
/**
 * 列出目录的子目录
 * @param string $dir
 */
function list_dir( $dir ){}
/**
 * 列出目录中的文件
 * @param string $dir
 * @param string $pattern
 * @return array
 */
function list_files( $dir, $pattern = '', $ext = '' ){}
/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false){}
/**
 +----------------------------------------------------------
 * 把返回的数据集转换成Tree
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0){}
/**
 * 过滤恶意代码，防止XSS攻击
 *
 * @param string $val
 * @return string
 */
function remove_xss($val){}
/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function rand_string($len=6,$type='',$addChars=''){}
/**
 * 类型配置
 * @return array
 */
function load_inc_config($file, $basedir = ''){}
/**
 * 通知文件服务器更新相应的文件为有效文件
 * @param array|string $file_ids		文件ID，格式：array(1,2)或  1
 * @param int $item_id					关联的实体ID，如商品ID、店铺ID等
 * @return	void
 */
function file_accepted($file_ids, $item_id = 0, $belong = -1){}
/**
 * 通知文件服务器更新相应的文件为有效文件
 * @param array|string $file_ids		文件ID，格式：array(1,2)或  1
 * @return	void
 */
function file_droped($file_ids){}
/**
 * 根据文件ID获取文件信息
 * @param array|string $file_ids		文件ID，格式：array(1,2)或  1
 * @return null|array
 */
function file_get_in_file_ids($file_ids){}
/**
 * 根据文件关联ID获取文件信息
 * @param int $item_id
 * @param int $belong
 * @return null|array
 */
function file_get_in_item_id($item_id, $belong){}
/**
 * 在页面显示挂件内容
 * @param string $page			页面
 * @param string $area			挂件区域
 * @param string $type			挂件类型
 * @param string $style_name	样式名
 */
function wg($page, $area, $type = '', $template_name = 'default', $style_name = 'default', $store_id = 0){}
/**
 * 显示店铺挂件
 * @author				c.k xiao <jihaoju@qq.com>
 * @date				2014-12-2
 */
function swg($page, $area){}
/**
 *    获取挂件实例
 *
 *    @param     string $id
 *    @param     string $name
 *    @param     array  $options
 *    @return    Object Widget
 */
function widget($id, $name, $options = array(), $type = ''){}
/**
 *    获取指定风格，指定页面的挂件的配置信息
 *
 *    @param     string $template_name			主题名
 *    @param     string $page					模板页面名称
 *    @param	 string $type					挂件类型，Mall|Store
 *    @package	 string $style_name				样式名
 *    @return    array
 */
function get_widget_config($template_name, $page, $type = 'mall', $style_name = 'default'){}
/**
 * 获取挂件列表
 *
 * @return array
 */
function list_widget($type = ''){}
/**
 * 获取挂件信息
 *
 * @param string $id
 * @return array
 */
function get_widget_info($name, $type = ''){}
/**
 *    获取邮件内容
 *
 *    @author    c.k
 *    @param     string $mail_tpl
 *    @param     array  $var
 *    @return    array
 */
function get_mail($mail_tpl, $var = array()){}
/**
 *    获取短消息内容
 *
 *    @author    c.k
 *    @param     string $msg_tpl
 *    @param     array  $var
 *    @return    string
 */
function get_msg($msg_tpl, $var = array()){}
/**
 *    获取手机短信内容
 *
 *    @author    c.k
 *    @param     string $sms_tpl
 *    @param     array  $var
 *    @return    string
 */
function get_sms($sms_tpl, $var = array()){}
/**
 * 格式化费用：可以输入数字或百分比的地方
 *
 * @param   string      $fee    输入的费用
 */
function format_fee($fee){}
/**
 * 根据总金额和费率计算费用
 *
 * @param     float    $amount    总金额
 * @param     string    $rate    费率（可以是固定费率，也可以是百分比）
 * @param     string    $type    类型：s 保价费 p 支付手续费 i 发票税费
 * @return     float    费用
 */
function compute_fee($amount, $rate, $type){}
/**
 * 获取用户头像地址
 *
 * @author Garbin
 * @param string $portrait
 * @return string
 */
function portrait($user_id, $portrait = '', $size = 'small'){}
/**
 * 获取商品类型对象
 *
 * @param string $type
 * @param array $params
 * @return void
 */
function gt($type, $params = array()){}
/**
 * 获取订单类型对象
 *
 * @param
 *        none
 * @return void
 */
function ot($type, $params = array()){}
/**
 * 获取订单状态相应的文字表述
 *
 * @param int $order_status
 * @return string
 */
function order_status($order_status, $ship_type = ''){}
/**
 * 获取安装订单状态相应的文字表述
 *
 * @param int $order_status
 * @return string
 */
function order_service_status($service_status){}
/**
 * 获取订单冲正状态的文字表述
 * @author				c.k xiao <jihaoju@qq.com>
 * @date				2015-1-28
 */
function order_correction_status($correction_status){}
/**
 * 获取退款状态相应的文字表述
 *
 * @author lizichuan
 * @param int $order_status
 * @return string
 */
function refund_status($refund_status){}
/**
 * 转换订单状态值
 *
 * @param string $order_status_text
 * @return void
 */
function order_status_translator($order_status_text){}
