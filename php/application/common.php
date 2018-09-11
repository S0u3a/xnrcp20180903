<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件，主要定义系统公共函数库

if (!function_exists('is_login'))
{	
	/**
	 * 检测用户是否登录
	 * @return integer 0-未登录，大于0-当前登录用户ID
	 */
	function is_login()
	{
		$user = session('user_auth');
		if (empty($user)) return 0;
		return session('user_auth_sign') == data_auth_sign($user) ? intval($user['uid']) : 0;
	}
}

if (!function_exists('data_auth_sign'))
{
	/**
	 * 数据签名认证
	 * @param  array  $data 被认证的数据
	 * @param  string $signType 签名加密方式[暂时不用]
	 * @return string       签名
	 */
	function data_auth_sign($data,$signType = 'SHA1') {
		//数据类型检测
		if(!is_array($data)) return "";
		ksort($data);//排序
		$code = http_build_query($data);//url编码并生成query字符串
		$sign = sha1($code);//生成签名
		return $sign;
	}
}

if (!function_exists('string_safe_filter'))
{
	/**
	 * 字符串安全过滤
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	function string_safe_filter($string = '')
	{
		if(is_array($string)){
			$string=implode('，',$string);
			$string=htmlspecialchars(str_shuffle($string));
		} else{
			$string=htmlspecialchars($string);
		}
		
		$string = str_replace('%20','',$string);
		$string = str_replace('%27','',$string);
		$string = str_replace('%2527','',$string);
		$string = str_replace('*','',$string);
		$string = str_replace('"','&quot;',$string);
		$string = str_replace("'",'',$string);
		$string = str_replace('"','',$string);
		$string = str_replace(';','',$string);
		$string = str_replace('<','&lt;',$string);
		$string = str_replace('>','&gt;',$string);
		$string = str_replace("{",'',$string);
		$string = str_replace('}','',$string);
		return $string;
	}
}

if (!function_exists('string_encryption_decrypt'))
{
	/**
	 * 字符串加密解密
	 * @param  string $string    需要加密或解密的字符串
	 * @param  string $operation 加密或解密选项[ENCODE解密,DECODE加密]
	 * @param  string $keys 	 字符串加密秘钥
	 * @return string            加密或解密后的字符串
	 */
	function string_encryption_decrypt($string,$operation = 'ENCODE',$keys = '')
	{
		$string 		= isset($string) ? $string : '';
		$operation 		= !empty($operation) ? strtoupper($operation) : 'ENCODE';
		$keys 			= !empty($keys) ? $keys : config('extends.uc_auth_key');
		$ckey_length	= 4;
		$keya			= md5(substr($keys,0,16));
		$keyb 			= md5(substr($keys,16,16));
		$keyc 			= $ckey_length?($operation=='DECODE'?substr($string,0,$ckey_length):substr(md5(microtime()),-$ckey_length)):'';
		$cryptkey 		= $keya.md5($keya.$keyc);
		$key_length 	= strlen($cryptkey);
		$string 		= $operation=='DECODE'?base64_decode(substr($string,$ckey_length)):sprintf('%010d',0).substr(md5($string.$keyb),0,16).$string;
		$string_length 	= strlen($string);
		$result 		= '';
		$box 			= range(0,255);
		$rndkey 		= array();
		for($i=0;$i<=255;$i++){
			$rndkey[$i]=ord($cryptkey[$i%$key_length]);
		}
		for($j=$i=0;$i<256;$i++){
			$j=($j+$box[$i]+$rndkey[$i])%256;
			$tmp=$box[$i];
			$box[$i]=$box[$j];
			$box[$j]=$tmp;
		}
		for($a=$j=$i=0;$i<$string_length;$i++){
			$a=($a+1)%256;
			$j=($j+$box[$a])%256;
			$tmp=$box[$a];
			$box[$a]=$box[$j];
			$box[$j]=$tmp;
			$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
		}
		if($operation=='DECODE'){
			if((substr($result,0,10)==0||substr($result,0,10)>0)&&substr($result,10,16)==substr(md5(substr($result,26).$keyb),0,16)){
				return substr($result,26);
			}else{
				return '';
			}
		}else{
			return $keyc.str_replace('=','',base64_encode($result));
		}
	}
}

if(!function_exists('formatMenuByPidAndPos'))
{
	/**
	 * 通过菜单上级ID获取指定位置的菜单
	 * @param  integer $pid  菜单上级ID
	 * @param  integer $pos  菜单位置ID
	 * @param  [type]  $menu 需要格式化的菜单数据
	 * @return [type]        已格式化后的菜单数据
	 */
	function formatMenuByPidAndPos($pid=0,$pos=0,$menu=[]){
		$arr  = [];

		if (!empty($menu)) {
			foreach ($menu as $key => $value) {

				if ($value['pid'] == $pid && $value['pos'] > 0 && $value['pos'] == $pos && $value['status'] == 1) {

	                $arr[]		= $value;
				}
			}
		}

		return $arr;
	}
}

if (!function_exists('threePartyplugLoadNum'))
{
	function threePartyplugLoadNum($type = '',$threePartyplug = [])
	{
		if (empty($threePartyplug))  return [];

		foreach($threePartyplug as $key=>$val)
		{	
			if(in_array($type,['image','images','file'])){
				$threePartyplug['uploads'] = (isset($threePartyplug['uploads'])?$threePartyplug['uploads']:0) + 1;
				break;
			}else{
				$threePartyplug[$key] = $type == $key ? $threePartyplug[$key] + 1 : $threePartyplug[$key];
			}
		}

		return $threePartyplug;
	}
}

if (!function_exists('get_domain'))
{
	function get_domain(){
		return request()->scheme() .'://' . trim($_SERVER['HTTP_HOST'],'/');
	}
}

if (!function_exists('get_invitation_code'))
{
	function get_invitation_code($uid=0)
	{
		return $uid > 0 ? 100000+$uid : '';
	}
}

if (!function_exists('get_invitation_uid'))
{
	function get_invitation_uid($code='')
	{
		return !empty($code) ? intval($code)-100000 : 0;
	}
}

if (!function_exists('get_cover'))
{
	/**
	 * 获取文档封面图片
	 * @param int $cover_id
	 * @param string $field
	 * @return 完整的数据  或者  指定的$field字段值
	 */
	function get_cover($id = 0,$field=''){
		$path 		= '';
		$id 		= intval($id);

		if ($id <= 0 && empty($field)) return [];
		if ($id <= 0 && !empty($field)) return '';

		$info 				= model('picture')->getOneById($id) ;

		if (!empty($info)) 
		{
			$info            = $info -> toArray() ;
			$info['path']	 = $info['img_type'] == 2 ? $info['path'] : trim(get_domain(),'/') . '/' . trim($info['path'],'/');
		} 

		if (!empty($field)) {
			
			return isset($info[$field]) ? $info[$field] : '';
		}
		return !empty($info) ? $info : [];
	}
}

//手机号格式验证
function Mobile_check($mobile,$type = array())
{
	$res[1]	= preg_match('/^1(3[0-9]|5[0-35-9]|7[0-9]|8[0-9])\\d{8}$/', $mobile);//手机号码 移动|联通|电信
	$res[2]	= preg_match('/^1(34[0-8]|(3[5-9]|5[017-9]|8[0-9])\\d)\\d{7}$/', $mobile);//中国移动
	$res[3]	= preg_match('/^1(3[0-2]|5[256]|8[56])\\d{8}$/', $mobile);//中国联通
	$res[4]	= preg_match('/^1((33|53|8[09])[0-9]|349)\\d{7}$/', $mobile);//中国电信
	$res[5]	= preg_match('/^0(10|2[0-5789]|\\d{3})-\\d{7,8}$/', $mobile);//大陆地区固话及小灵通
	$type	= empty($type) ? array(1,2,3,4,5) : $type;
	$ok 	= false;
	foreach ($type as $key=>$val)
	{
		if ($res[$val]) $ok = true;
		continue;
	}

	return ($mobile && $ok) ? true : false;
}

//邮箱格式验证
function Email_check($email)
{
	return (!preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $email) || !$email) ? false : true;
}

//用户名格式验证
function Username_check($email)
{
	return true;
}

//字段处理
function get_fields_string($fields,$pre=''){
	$arr = array();
	if (!empty($fields))
	{
		foreach ($fields as $key=>$val)
		{
			$arr[$val['field'][0]] = $pre != '' ? $pre.'.'.$val['field'][0] : $val['field'][0];
		}
		return implode(',', $arr);
	}
	return '';
}

if (!function_exists('wr'))
{
	//文件写入，快捷调试
	function wr($data,$file = 'info.txt',$return=true)
	{
		$return == true ? file_put_contents('../runtime/'.$file,var_export($data,true),FILE_APPEND) : file_put_contents('../runtime/'.$file,var_export($data,true));
	}
}
if (!function_exists('dblog'))
{
	//数据库写入，快捷调试
	function dblog($data)
	{
		//数据库
		$data	= array($data,date('Y-m-d H:i:s',time()));
		$data	= is_array($data) ? json_encode($data) : $data;
		db('dblog')->insert(array('msg'=>$data,'date'=>date('Y-m-d H:i:s',time())));
	}
}


/**
 * 生成随机数
 * @param number $length 字符串长度
 * @param number $type 字符串类型
 * @return string
 */
function randomString($length, $type = 0) {
	$arr  = array(
	0 => '123456789',
	1 => 'abcdefghjkmnpqrstuxy',
	2 => 'ABCDEFGHJKMNPQRSTUXY',
	3 => '123456789abcdefghjkmnpqrstuxy',
	4 => '123456789ABCDEFGHJKMNPQRSTUXY',
	5 => 'abcdefghjkmnpqrstuxyABCDEFGHJKMNPQRSTUXY',
	6 => '123456789abcdefghjkmnpqrstuxyABCDEFGHJKMNPQRSTUXY',
	7 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
	);
	$chars = $arr[$type] ? $arr[$type] : $arr[7];
	$hash  = '';
	$max   = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

/**
 * Curl请求
 * @param number $url 请求的URL
 * @param number $body 字符串类型
 * @return string
 */
function CurlHttp($url,$body='',$method='DELETE',$headers=array()){
	$httpinfo=array();
	$ci=curl_init();
	/* Curl settings */
	curl_setopt($ci,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
	curl_setopt($ci,CURLOPT_USERAGENT,'toqi.net');
	curl_setopt($ci,CURLOPT_CONNECTTIMEOUT,30);
	curl_setopt($ci,CURLOPT_TIMEOUT,30);
	curl_setopt($ci,CURLOPT_RETURNTRANSFER,TRUE);
	curl_setopt($ci,CURLOPT_ENCODING,'');
	curl_setopt($ci,CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt($ci,CURLOPT_HEADER,FALSE);
	switch($method){
		case 'POST':
			curl_setopt($ci,CURLOPT_POST,TRUE);
			if(!empty($body)){
				curl_setopt($ci,CURLOPT_POSTFIELDS,$body);
			}
			break;
		case 'DELETE':
			curl_setopt($ci,CURLOPT_CUSTOMREQUEST,'DELETE');
			if(!empty($body)){
				$url=$url.'?'.str_replace('amp;', '', http_build_query($body));
			}
	}
	curl_setopt($ci,CURLOPT_URL,$url);
	curl_setopt($ci,CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ci,CURLINFO_HEADER_OUT,TRUE);
	$response=curl_exec($ci);
	$httpcode=curl_getinfo($ci,CURLINFO_HTTP_CODE);
	$httpinfo=array_merge($httpinfo,curl_getinfo($ci));
	curl_close($ci);
	return $response;
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string 
 */
function data_md5($str, $key = 'XNRCMS'){
	return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 获取客户端IP
 * @return [string] IP地址
 */
function get_client_ip() {
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';

    return $ip;
}

/**
 * [is_json 判断是否是json格式数据]
 * @param  [type]  $string [json字符串]
 * @return boolean
 */
function is_json($string) {
	
	 json_decode($string);
	 
	 return (json_last_error() == JSON_ERROR_NONE);
}

function merge_config(){
	
	//合并系统配置
	$config				=  model('config')->getConfigData();
	if (!empty($config)) {
		
		foreach ($config as $key => $value) config($value,$key);
	}
}

function formatUploadFileName(){

    return date('Y-m-d') . '/' . md5(microtime(true).randomString(10));
}

// 递归删除文件夹
function delFile($path,$delDir = FALSE) {
    if(!is_dir($path))
       return FALSE;		
	$handle = @opendir($path);
	if ($handle) {
		while (false !== ( $item = readdir($handle) )) {
			if ($item != "." && $item != ".." && $item != '.svn')
				is_dir("$path/$item") ? delFile("$path/$item", $delDir) : unlink("$path/$item");
		}
		closedir($handle);
		if ($delDir) return rmdir($path);
	}else {
		if (file_exists($path)) {
			return unlink($path);
		} else {
			return FALSE;
		}
	}
}

/**
 * 将字符串格式化为驼峰命名法
 * @param  string $string  被格式化的字符串
 * @param  string $delimiter 字符串分隔符
 * @return string            格式化后的新字符串
 */
function formatStringToHump($string ='',$delimiter='_')
{
	$str  	= '';

	if (is_string($string) && !empty($string) && !empty($delimiter)) {

		//以下划线分割
        $arr   	= explode($delimiter,$string);

        if (!empty($arr)) {

            foreach ($arr as $v) {

                $str .= ucwords($v);
            }
        }
	}else{

		$str 		= $string;
	}

	return $str;
}

/**
 * 格式化输出无限极分类
 * @param  array  	$category  	需要格式化的数据
 * @param  integer 	$parent_id  父级ID
 * @return array             	格式化后的数据
 */
if (!function_exists('toLevel'))
{
	function toLevel($arr, $parent_id = 0)
	{
	    if (!empty($arr)) {
	    	
	    	foreach ($arr as $v) {
		        if (isset($v[5]) && $v[5] == $parent_id) {

		        	$data[$v[4]] 		= array_merge($v,[[]]);
		        	
		        	if ( in_array($v[1],['array','object'])) {

		        		$data[$v[4]][7] = toLevel($arr,$v[4]);
		        	}
		        }
		    }
	    }

    	return isset($data) ? $data : [];
	}
}

/**
 * 格式时间
 * @param  string  	$value  	需要格式化的数据
 * @param  array 	$parame  	附属参数
 * @param  string 	$extends  	扩展参数
 * @return string             	格式化后的数据
 */
if (!function_exists('format_time'))
{
	function format_time($value,$parame=[],$extends='')
	{
		return !empty($value) ? date((!empty($extends) ? $extends : 'Y-m-d H:i:s'),$value) : '';
	}
}

/**
 * 格式状态
 * @param  string  	$value  	需要格式化的数据
 * @param  array 	$parame  	附属参数
 * @param  string 	$extends  	扩展参数
 * @return string             	格式化后的数据
 */
if (!function_exists('format_status'))
{
	function format_status($value,$parame=[],$extends='')
	{
		$status 				=['未知','启用','禁用'];
		return !empty($value) ? $status[intval($value)] : '未知';
	}
}

/**
 * 格式图片ID
 * @param  string  	$value  	需要格式化的数据
 * @param  array 	$parame  	附属参数
 * @param  string 	$extends  	扩展参数
 * @return string             	格式化后的数据
 */
if (!function_exists('format_imgid'))
{
	function format_imgid($value,$parame=[],$extends='')
	{
		return json_encode([$value,get_cover($value,'path')]);
	}
}

/**
 * 格式空值
 * @param  string  	$value  	需要格式化的数据
 * @param  array 	$parame  	附属参数
 * @param  string 	$extends  	扩展参数
 * @return string             	格式化后的数据
 */
if (!function_exists('format_empty'))
{
	function format_empty($value,$parame=[],$extends='')
	{
		return !empty($value) ? $value : $extends;
	}
}

if (!function_exists('apiReq'))
{
	/**
	 * 接口调用快捷函数
	 * @param  array 	$parame  接口请求参数
	 * @param  string 	$apiName 接口名称
	 * @param  array  	$headers 接口请求头信息
	 * @return array         	 接口数据返回
	 */
	function apiReq($parame,$apiName,$headers=[])
	{
		$api_auth_url 			= config('extend.api_auth_url');
		$api_auth_id 			= config('extend.api_auth_id');
		$api_auth_key 			= config('extend.api_auth_key');

		$apiName 				= !empty($apiName) ? explode('/',trim($apiName,'/')) : [];
		$mName 					= isset($apiName[0]) ? $apiName[0] : '';
		$cName 					= isset($apiName[1]) ? $apiName[1] : '';
		$aName 					= isset($apiName[2]) ? $apiName[2] : '';

		$ApiRequest 			= new \xnrcms\ApiRequest($api_auth_url, $api_auth_id, $api_auth_key);
		if (!empty($api_auth_url))
		{
			$apiName 				= implode('/',[$mName,humpToLine($cName),$aName]);
			//远程接口调用
			$backData 				= $ApiRequest->postData($parame,$apiName,$headers);
			$errorInfo				= $ApiRequest->getError();
		}
		else
		{	
			$parame['time']		= time();
			$parame['apiId']	= $api_auth_id;
			$parame['terminal']	= 1;
			$parame['hash'] 	= $ApiRequest->getSign($parame);

	      	//构造命名空间
	        $namespace       	= '\\'. 'app\\'.strtolower($mName).'\\helper';
	        $models				= '\\'. trim($namespace,'\\') . '\\' . trim($cName,'\\');
	        //实例化操作对象
	        $object            	= new $models($parame,$cName,$aName,$mName);
	      	//获取数据
	        $backData 			= $object->apiRun();
	        $backData 			= $backData->getData();
	        $errorInfo 			= [];
		}

		$backData 	= !empty($backData) ? is_array($backData) ? $backData : json_encode($backData,true) : [];
		$errorInfo 	= !empty($errorInfo) ? is_array($errorInfo) ? $backData : json_encode($errorInfo,true) : [];

		return [$backData,$errorInfo];
	}
}

if(!function_exists('convertUnderline'))
{	
	/**
	 * 下划线转驼峰
	 * @param  string $str 待转字符串
	 * @return string      已转字符串
	 */
	function convertUnderline($str)
	{
	    $str = preg_replace_callback('/([-_]+([a-z]{1}))/i',function($matches){
	        return strtoupper($matches[2]);
	    },$str);
	    return $str;
	}
}

if(!function_exists('humpToLine'))
{
	/**
	 * 驼峰转下划线
	 * @param  string $str 待转字符串
	 * @return string      已转字符串
	 */
	function humpToLine($str)
	{
	    $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
	        return '_'.strtolower($matches[0]);
	    },$str);
	    return !empty($str) ? trim($str,'_') : '';
	}
}

//接口文档授权登录执行
function apidocReq($parame,$apiName,$headers=[])
{

	$ApiUrl					= config('extend.apidoc_url');
	$ApiId 					= config('extend.api_auth_id');
	$ApiKey					= config('extend.api_auth_key');

	$ApiRequest 			= new \xnrcms\ApiRequest($ApiUrl, $ApiId, $ApiKey);

	$backData 				= $ApiRequest->postData($parame,$apiName,$headers);

	return json_decode($backData,true);
}

//url 安全base64加密
if(!function_exists('urlsafe_b64encode'))
{
    function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
}

//url 安全base64解密
if(!function_exists('urlsafe_b64decode'))
{
    function urlsafe_b64decode($string)
    {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) $data .= substr('====', $mod4);
        return base64_decode($data);
    }
}

if(!function_exists('sendJpus'))
{
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/2/2 14:22
	 * @description：极光推送
	 */
	function sendJpus($jpushid=array(), $msg='', $extras=[]){

	    $config = config('jpush.') ;

	    $app_key 				= $config['appkey'];
	    $master_secret 			= $config['appSecret'];
	    //参数
	    //初始化
	    $client = new \JPush\Client($app_key, $master_secret);

	    //如果设备id为空 全局推送
	    if(empty($jpushid)){
	        $client = $client
	            ->push()
	            ->setPlatform(array('ios', 'android'))
	            ->addAllAudience() ;
	    }else{
	        $client = $client
	            ->push()
	            ->setPlatform(array('ios', 'android'))
	            ->addRegistrationId($jpushid) ;
	    }

	    $res = $client
	        ->setNotificationAlert($msg)
	        ->addAndroidNotification($msg,'', 1, $extras)
	        ->addIosNotification($msg, 'iOS sound', \JPush\Config::DISABLE_BADGE, true, 'iOS category', $extras)
	        ->setMessage("msg content", 'msg title', 'type', $extras)
	        ->setOptions(100000, 3600, null, false)
	        ->send();

	    return $res ;
	}
}
if(!function_exists('getSignature'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：双乾支付加密签名
	 */
	function getSignature($MerNo, $BillNo, $Amount, $ReturnURL, $MD5key)
	{
	    $sign_params  = [
	        'MerNo'       => $MerNo,
	        'BillNo'      => $BillNo, 
	        'Amount'      => $Amount,   
	        'ReturnURL'   => $ReturnURL
	    ];
	    $sign_str = "";
	    ksort($sign_params);
	    foreach ($sign_params as $key => $val){
	        $sign_str .= sprintf("%s=%s&", $key, $val);
	    }

	    return strtoupper(md5($sign_str. strtoupper(md5($MD5key))));   
	}
}

if(!function_exists('getUserLevel'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：格式化用户等级
	 */
	function getUserLevel($money=0)
	{
		$config 		= config('system_config.');
		$user_level 	= $config['user_level'];
		$user_level 	= explode("\n",$user_level);
		$level 			= [0,0,0];
		$ll 			= [];
		if (!empty($user_level)) {
			foreach ($user_level as $key => $value) {
				$vv 	= explode('=',$value);
				$ll[] 	= $vv;
			}
		}

		$nn 		= count($ll);
		$ll[$nn] 	= ['L9',1,999999999];
		for ($i=0; $i < $nn; $i++) {
			if ($money*1 >= $ll[$i][2]*1 && $money*1 < $ll[$i+1][2]*1) {
				$level 			= [$i,$ll[$i][1],$ll[$i][2]];
			}
		}

		return $level;
	}
}

if(!function_exists('getDistributionLevel'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：格式化分销比例
	 */
	function getDistributionLevel($money=0)
	{
		$config 		= config('system_config.');
		$rate1 			= $config['fen_first_rate'];
		$rate2 			= $config['fen_second_rate'];
		$rate3 			= $config['fen_third_rate'];
		return [$rate1,$rate2,$rate3];
	}
}

if(!function_exists('getOddsRebate'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：倍率和返利换算
	 */
	function getOddsRebate($rebate=0,$odds=0,$odds_rebate=0)
	{
		if (empty($odds) || $odds <= 0 ) return 0;
		$odds = $rebate >0 ? $odds-(($odds-$odds_rebate)/13)*$rebate : $odds;
		return sprintf("%.3f",$odds);
	}
}

if(!function_exists('getOddsRebates'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：多倍率和返利换算
	 */
	function getOddsRebates($rebate=0,$odds='')
	{
		if (empty($odds) || $odds <= 0 ) return [0];
		$odds 			= explode(',',$odds);
		$arr 			= [];

		foreach ($odds as $key => $value)
		{
			if (strpos($value,'-') === false) {
				$arr[] 		= getOddsRebate($rebate,$value,0);
			}else{
				$vv  		= explode('-',$value);
				$arr[] 		= getOddsRebate($rebate,$vv[0],$vv[1]);
			}
		}

		return $arr;
	}
}

if(!function_exists('repeatNumForNumber'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：检测字符串单个字符在整个字符串中出现的次数
	 */
	function repeatNumForNumber($num='',$n=0)
	{	
		$strlen 		= strlen($num);
		if ($strlen <= 0) return 0;
		for ($i=0; $i < $strlen; $i++) { 
			$n1 	= substr($num,$i,1);
			$n2 	= substr_count('#'.$num, $n1);
			if ( $n2== $n) return $n2;
		}
		return 0;
	}
}

if(!function_exists('sscOddsMoney'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：时时彩赔率金额计算
	 */
	function sscOddsMoney($tag='',$rebate,$lotteryRule,$price,$isWin,$aid=0,$agentOdds=[])
	{	
		$odds_rebate 			= $lotteryRule['odds_rebate'];
		$money 					= 0;
		$winBets 				= isset($isWin[0]) ? intval($isWin[0]) : 0;
		$winCode 				= isset($isWin[1]) ? $isWin[1] : [];

		if ($aid > 0 && !empty($agentOdds) && !empty($agentOdds['odds2'])) {
			$odds 				= !empty($agentOdds['odds2']) ? $agentOdds['odds2'] : $agentOdds['odds1'];
		}else{
			$odds 				= !empty($lotteryRule['odds2']) ? $lotteryRule['odds2'] : $lotteryRule['odds'];
		}

		//赔率个数
		$ocount 				= count(explode(',',$odds));
    	$odds           		= $ocount>=2 ? getOddsRebates(0,$odds) : getOddsRebate(0,$odds,0);
    	$orderOdds 				= [];

        if (in_array($tag,['88-1-3','88-11-3','88-11-10']))
        {
            if (!empty($winCode)) {
	            foreach ($winCode as $kk => $vv)
	            {
	                for ($i=$vv; $i > 0; $i--) {
	                	$oo 			= $odds[(count($odds)-$i)];
	                    $money 			+= $oo*$price*1;
        				$orderOdds[]	= $oo;
	                }
	            }
            }
        }
        /*elseif (in_array($tag,['88-3-1'])) {
        	
        }*/
        elseif (in_array($tag,['88-12-8','88-12-24','88-12-16']))
        {
            if (!empty($winCode)) {
	            foreach ($winCode as $kk => $vv)
	            {
	                $n1     		= repeatNumForNumber($vv,2);
	                $oo 			= $odds[($n1 == 2 ? 0 : 1)];
	                $money 			+= $oo*$price*1;
        			$orderOdds[]	= $oo;
	            }
        	}
        }
        else
        {
        	$money 			+= $odds*$price*$winBets;
        	$orderOdds[]	= $odds;
        }

        return [sprintf("%.3f",$money),(!empty($orderOdds) ? implode(',',$orderOdds) : '')];
	}
}

if(!function_exists('pk10OddsMoney'))
{	
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：PK10赔率金额计算
	 */
	function pk10OddsMoney($tag='',$rebate,$lotteryRule,$price,$isWin,$aid=0,$agentOdds=[])
	{	
		$odds_rebate 			= $lotteryRule['odds_rebate'];
		$money 					= 0;
		$winBets 				= isset($isWin[0]) ? intval($isWin[0]) : 0;
		$winCode 				= isset($isWin[1]) ? $isWin[1] : [];

		if ($aid > 0 && !empty($agentOdds) && !empty($agentOdds['odds2'])) {
			$odds 				= !empty($agentOdds['odds2']) ? $agentOdds['odds2'] : $agentOdds['odds1'];
		}else{
			$odds 				= !empty($lotteryRule['odds2']) ? $lotteryRule['odds2'] : $lotteryRule['odds'];
		}

		//赔率个数
		$ocount 				= count(explode(',',$odds));
    	$odds           		= $ocount>=2 ? getOddsRebates(0,$odds) : getOddsRebate(0,$odds,0);
    	$orderOdds 				= [];

        if (in_array($tag,['96-5-1'])) {//和值
        	
        	if (!empty($winCode)) {
        		foreach ($winCode as $kk => $vv)
	            {
	            	$oo 			= $odds[($vv*1-2)];
	                $money 			+= $oo*$price*1;
	                $orderOdds[]	= $oo;
	            }
        	}
        }elseif (in_array($tag,['96-8-7'])) {//大小单双
            if (!empty($winCode)) {
        		foreach ($winCode as $kk => $vv)
	            {
	            	$oo 			= in_array($vv,['大','小']) ? $odds[0] : $odds[2];
	                $money 			+= $oo*$price*1;
	                $orderOdds[]	= $oo;
	            }
        	}
        }else{
        	$money 			+= $odds*$price*$winBets;
        	$orderOdds[]	= $odds;
        }

        return [sprintf("%.3f",$money),(!empty($orderOdds) ? implode(',',$orderOdds) : '')];
	}
}

if(!function_exists('hk6OddsMoney'))
{
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：六合彩赔率金额计算
	 */
	function hk6OddsMoney($tag='',$rebate,$lotteryRule,$price,$isWin,$aid=0,$agentOdds=[],$orderinfo=[])
	{	
		$odds_rebate 			= $lotteryRule['odds_rebate'];
		$money 					= 0;
		$winBets 				= isset($isWin[0]) ? intval($isWin[0]) : 0;
		$winCode 				= isset($isWin[1]) ? $isWin[1] : [];

		if ($aid > 0 && !empty($agentOdds) && !empty($agentOdds['odds2'])) {
			$odds 				= !empty($agentOdds['odds2']) ? $agentOdds['odds2'] : $agentOdds['odds1'];
		}else{
			$odds 				= !empty($lotteryRule['odds2']) ? $lotteryRule['odds2'] : $lotteryRule['odds'];
		}

		//赔率个数
		$ocount 				= count(explode(',',$odds));
    	$odds           		= $ocount>=2 ? getOddsRebates(0,$odds) : getOddsRebate(0,$odds,0);
    	$orderOdds 				= [];

    	if (in_array($tag,['99-1-1'])) {//两面
    		if (in_array('和局',$winCode)) {
    			$money 		= $orderinfo['money'];
    		}else{
    			$ops  = ['特单','特双','特大','特小','特合单','特合双','特合大','特合小','特尾大','特尾小','特天肖','特地肖','特前肖','特后肖','特家肖','特野肖','总单','总双','总大','总小'];
	        	$ops  = array_flip($ops);
	        	$oo   = 0;
				foreach ($winCode as $key => $value) {
					$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
					$money 			+= $oo*$price*1;
					$orderOdds[]	= $oo;
				}
    		}
        }elseif (in_array($tag,['99-2-1'])) {
        	foreach ($winCode as $key => $value) {
        		$wnum 		= intval($value) - 1;
				$oo 		= isset($odds[$wnum]) ? $odds[$wnum] : 0;
				$orderOdds[]= $oo;
				$money 		+= $oo*$price*1;
			}
        }
        elseif (in_array($tag,['99-5-1','99-5-2','99-5-3','99-5-4','99-5-5','99-5-6'])) {//正码1-6
        	$ops  = ['单','双','大','小','合单','合双','合大','合小','尾大','尾小','红波','绿波','蓝波'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	//判断是否和局
        	if (in_array('和局',$winCode)) {
        		$hnum 		= isset($winCode['和局N']) ? intval($winCode['和局N']) : 0;
    			$money 		= $orderinfo['price'] * $hnum;

    			foreach ($winCode as $key => $value) {
    				if (in_array($value,['红波','绿波','蓝波'])) {
						$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
						$money 			+= $oo*$price*1;
						$orderOdds[]	= $oo;
    				}
				}
    		}else{
    			foreach ($winCode as $key => $value) {
					$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
					$money 			+= $oo*$price*1;
					$orderOdds[]	= $oo;
				}
    		}
        }elseif (in_array($tag,['99-6-1'])) {//大小单双
        	$ops  = ['单','双','大','小','合单','合双','合大','合小','尾大','尾小','红波','绿波','蓝波'];
        	$ops  = array_flip($ops);

        	foreach ($winCode as $kk => $vv) {
        		$win 	= explode(',',$vv);
        		$ooo 	= 1;
        		foreach ($win as $key => $value) {
					$oo 		= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
					$ooo 		*=  $oo;		
				}

				$money 			+= $ooo*$price*1;
				$orderOdds[]	= $ooo;
        	}
        }else if (in_array($tag,['99-8-1','99-8-2','99-8-3','99-8-4'])) {
        	$ops  = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	//对应生肖和赔率
        	foreach ($ops as $key => $value) {
        		$oddsArr[$key] = $odds[$value];
        	}

        	foreach ($winCode as $key => $value)
        	{
        		//分解尾号 格式化对应生肖赔率
        		$winWs 		= explode(',',$value);
        		$winOdds 	= [];
        		foreach ($winWs as $k1 => $v1) {
        			$winOdds[$v1] 	= $oddsArr[$v1];
        		}

        		//找出最小赔率
        		$minOdds 		= min($winOdds);
				$money 			+= $minOdds*$price*1;
				$orderOdds[]	= $minOdds;
			}

        	/*//取第一个生肖赔率
        	foreach ($winCode as $key => $value) {
        		$sx 		= explode(',',$value);
				$oo 		= isset($ops[$sx[0]])?(isset($odds[$ops[$sx[0]]])?$odds[$ops[$sx[0]]]:0):0;
				break;
			}
			$money 			+= $oo*$price*$winBets;*/
        }else if (in_array($tag,['99-8-5','99-8-6','99-8-7','99-8-8'])) {
        	$ops  = ['尾0','尾1','尾2','尾3','尾4','尾5','尾6','尾7','尾8','尾9'];
        	$ops  = array_flip($ops);

        	//对应尾号和赔率
        	foreach ($ops as $key => $value) {
        		$oddsArr[$key] = $odds[$value];
        	}
        	
        	foreach ($winCode as $key => $value)
        	{
        		//分解尾号 格式化对应尾号赔率
        		$winWs 		= explode(',',$value);
        		$winOdds 	= [];
        		foreach ($winWs as $k1 => $v1) {
        			$winOdds[$v1] 	= $oddsArr[$v1];
        		}

        		//找出最小赔率
        		$minOdds 		= min($winOdds);
				$money 			+= $minOdds*$price*1;
				$orderOdds[]	= $minOdds;
			}

        }else if (in_array($tag,['99-10-1','99-10-2','99-10-3'])) {
        	$ops  = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
				$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				$money 			+= ($oo-1)*$price*1+$price;
				$orderOdds[]	= $oo;
			}
        }else if (in_array($tag,['99-10-4'])) {
        	$ops  = ['234肖','5肖','6肖','7肖','总肖单','总肖双'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
				$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				$money 			+= ($oo-1)*$price*1+$price;
				$orderOdds[]	= $oo;
			}
        }else if (in_array($tag,['99-12-1'])) {
        	$ops  = ['红波','蓝波','绿波'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
				$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				$money 			+= $oo*$price*1*$winBets;
				$orderOdds[]	= $oo;
			}
        }else if (in_array($tag,['99-12-2'])) {
        	if (in_array('和局',$winCode)) {
    			$money 		= $orderinfo['money'];
    		}else{
    			$ops  = ['红单','红双','红大','红小','绿单','绿双','绿大','绿小','蓝单','蓝双','蓝大','蓝小'];
	        	$ops  = array_flip($ops);
	        	$oo   = [];

	        	foreach ($winCode as $key => $value) {
					$oo[] 		= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				}

				//取较大赔率为计算赔率
				sort($oo);
				$ooo 			= $oo[count($oo)-1];
				$money 			+= $ooo*$price*1*$winBets;
				$orderOdds[]	= $ooo;
    		}
        }else if (in_array($tag,['99-12-3'])) {
        	$ops  = ['红大单','红大双','红小单','红小双','绿大单','绿大双','绿小单','绿小双','蓝大单','蓝大双','蓝小单','蓝小双'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
        		//退回本金
        		if ($value == '和局') {
        			$money 		= $orderinfo['money'];
        			break;
        		}else{
					$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
					$money 			+= $oo*$price*1*$winBets;
					$orderOdds[]	= $oo;
        		}
			}
        }else if (in_array($tag,['99-12-4'])) {
        	$ops  = ['红波','蓝波','绿波','和局'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
        		if ($value == '和局1') {
        			$money 		= $orderinfo['money'];
        			break;
        		}else{
					$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
					$money 			+= $oo*$price*1*$winBets;
					$orderOdds[]	= $oo;
				}
			}
        }
        else if (in_array($tag,['99-13-1'])) {
        	$ops  = ['头0','头1','头2','头3','头4','尾5','尾6','尾7','尾8','尾9','尾10','尾11','尾12','尾13','尾14'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
        		$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				$money 			+= $oo*$price*1;
				$orderOdds[]	= $oo;
			}
        }
        else if (in_array($tag,['99-13-2'])) {
        	$ops  = ['尾0','尾1','尾2','尾3','尾4','尾5','尾6','尾7','尾8','尾9'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
        		$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				$money 			+= $oo*$price*1;
				$orderOdds[]	= $oo;
			}
        }
        else if (in_array($tag,['99-14-1'])) {
        	$ops  = ['单0双7','单1双6','单2双5','单3双4','单4双3','单5双2','单6双1','单7双0','单5双2','大0小7','大1小6','大2小5','大3小4','大4小3','大5小2','大6小1','大7小0'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
        		$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				$money 			+= $oo*$price*1;
				$orderOdds[]	= $oo;
			}
        }
        else if (in_array($tag,['99-14-2'])) {
        	$ops  = ['金','木','水','火','土'];
        	$ops  = array_flip($ops);
        	$oo   = 0;

        	foreach ($winCode as $key => $value) {
        		$oo 			= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
				$money 			+= $oo*$price*1;
        		$orderOdds[]	= $oo;
			}
        }
        else{
        	$money 			+= $odds*$price*$winBets;
        	$orderOdds[]	= $odds;
        }

        return [sprintf("%.3f",$money),(!empty($orderOdds) ? implode(',',$orderOdds) : '')];
	}
}

if(!function_exists('oneOddsMoney'))
{
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：单赔率金额计算
	 */
	function oneOddsMoney($tag='',$rebate,$lotteryRule,$price,$isWin,$aid=0,$agentOdds=[])
	{	
		$odds_rebate 			= $lotteryRule['odds_rebate'];
		$money 					= 0;
		$winBets 				= isset($isWin[0]) ? intval($isWin[0]) : 0;
		$winCode 				= isset($isWin[1]) ? $isWin[1] : [];

		$orderOdds 				= [];

        $odds 					= $aid > 0 ? $agentOdds : $lotteryRule['odds'];
    	$odds           		= getOddsRebate(0,$odds,0);
    	$money 					+= $odds*$price*$winBets;
        
        $orderOdds[]			= $odds;

        return [sprintf("%.3f",$money),(!empty($orderOdds) ? implode(',',$orderOdds) : '')];
	}
}

if(!function_exists('manyOddsMoney'))
{
	/**
	 * @Author :xnrcms<562909771@qq.com>
	 * @date：2018/6/2 14:22
	 * @description：多赔率金额计算-PC蛋蛋
	 */
	function manyOddsMoneyPC($tag='',$rebate,$lotteryRule,$price,$isWin,$aid=0,$agentOdds=[])
	{	
		$odds_rebate 			= $lotteryRule['odds_rebate'];
		$money 					= 0;
		$winBets 				= isset($isWin[0]) ? intval($isWin[0]) : 0;
		$winCode 				= isset($isWin[1]) ? $isWin[1] : [];

		if ($aid > 0 && !empty($agentOdds) && !empty($agentOdds['odds2'])) {
			$odds 				= !empty($agentOdds['odds2']) ? $agentOdds['odds2'] : $agentOdds['odds1'];
		}else{
			$odds 				= !empty($lotteryRule['odds2']) ? $lotteryRule['odds2'] : $lotteryRule['odds'];
		}

		//赔率个数
		$ocount 				= count(explode(',',$odds));
    	$odds           		= $ocount>=2 ? getOddsRebates(0,$odds) : getOddsRebate(0,$odds,0);

    	$orderOdds 				= [];
    	switch ($tag) {
    		case '115-1-1':
    			foreach ($winCode as $key => $value) {
    				$oo 		= isset($odds[$value]) ? $odds[$value] : 0;
    				$orderOdds[]= $oo;
    				$money 		+= $oo*$price*1;
    			}
    			break;
    		case '115-2-1':
    			$ops  = ['大','单','大单','大双','极大','小','双','小单','小双','极小'];
    			$ops  = array_flip($ops);
    			foreach ($winCode as $key => $value) {
    				$oo 		= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
    				$orderOdds[]= $oo;
    				$money 		+= $oo*$price*1;
    			}
    			break;
    		case '115-3-1':
    			$ops  = ['红波','绿波','蓝波'];
    			$ops  = array_flip($ops);
    			foreach ($winCode as $key => $value) {
    				$oo 		= isset($ops[$value])?(isset($odds[$ops[$value]])?$odds[$ops[$value]]:0):0;
    				$orderOdds[]= $oo;
    				$money 		+= $oo*$price*1;
    			}
    			break;
    		case '115-4-1':
    			$money 			+= $odds*$price*1;
    			$orderOdds[]	= $odds;
    			break;
    		case '115-5-1':
    			$money 			+= $odds*$price*1;
    			$orderOdds[]	= $odds;
    			break;
    		default: $money = 0; break;
    	}

        return [sprintf("%.3f",$money),(!empty($orderOdds) ? implode(',',$orderOdds) : '')];
	}
}

if (!function_exists('msubstr'))
{
	/**
	 * 字符串截取，支持中文和其他编码
	 * @static
	 * @access public
	 * @param string $str 需要转换的字符串
	 * @param string $start 开始位置
	 * @param string $length 截取长度
	 * @param string $charset 编码格式
	 * @param string $suffix 截断显示字符
	 * @return string
	 */
	function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
		if(function_exists("mb_substr"))
		$slice = mb_substr($str, $start, $length, $charset);
		elseif(function_exists('iconv_substr')) {
			$slice = iconv_substr($str,$start,$length,$charset);
			if(false === $slice) {
				$slice = '';
			}
		}else{
			$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("",array_slice($match[0], $start, $length));
		}
		return $suffix ? $slice.'...' : $slice;
	}
}