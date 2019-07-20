<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}

//获取几天几小时几分几秒
function get_short_time($create_time) {
    $dur = NOW_TIME - $create_time;
    if ($dur <= 0) {
        return date("Y-m-d H:i", $create_time);
    } else {
        if ($dur < 60) {
            return $dur . '秒前';
        } else {
            if ($dur < 3600) {
                return floor($dur / 60) . '分钟前';
            } else {
                if ($dur < 86400) {
                    return floor($dur / 3600) . '小时前';
                } else {
                    if ($dur < 432000) { //5天内
                        return floor($dur / 86400) . '天前';
                    } else {
                        return date("Y-m-d H:i", $create_time);
                    }
                }
            }
        }
    }
}
//加密 sufyan 2017
function encrypt($input, $k='T1a2O3T4o5N6g7S8e9C0R.E0T9K8e7Y6', $i='01234567'){
    $size = mcrypt_get_block_size(MCRYPT_3DES,MCRYPT_MODE_CBC);
    $input = pkcs5_pad($input, $size);
    $key = str_pad($k,24,'0');
    $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
    if( $i == '' ){
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    }else{
        $iv = $i;
    }
    @mcrypt_generic_init($td, $key, $iv);
    $data = mcrypt_generic($td, $input);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    $data = base64_encode($data);
    return $data;
}
//解密 sufyan 2017
function decrypt($encrypted, $k='T1a2O3T4o5N6g7S8e9C0R.E0T9K8e7Y6', $i='01234567'){
    $encrypted = base64_decode($encrypted);
    $key = str_pad($k,24,'0');
    $td = mcrypt_module_open(MCRYPT_3DES,'',MCRYPT_MODE_CBC,'');
    if( $i == '' ){
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    }else{
        $iv = $i;
    }
    $ks = mcrypt_enc_get_key_size($td);
    @mcrypt_generic_init($td, $key, $iv);
    $decrypted = mdecrypt_generic($td, $encrypted);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    $y = pkcs5_unpad($decrypted);
    return $y;
}
function pkcs5_pad ($text, $blocksize) {
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}
function pkcs5_unpad($text){
    $pad = ord($text{strlen($text)-1});
    if ($pad > strlen($text)) {
            return false;
    }
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
            return false;
    }
    return substr($text, 0, -1 * $pad);
}
function PaddingPKCS7($data) {
    $block_size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
    $padding_char = $block_size - (strlen($data) % $block_size);
    $data .= str_repeat(chr($padding_char),$padding_char);
    return $data;
}
//日志 sufyan 2017 05
function log_new($msg,$api){
    $time = date("Y-m-d H:i:s");
    if(is_array($msg)){
        $str = $time . " " .$api." ". json_encode($msg);
    }else{
        $str = $time . "  " .$api." ". $msg;
    }
    $str .= "\r\n";
    
     file_put_contents("/mnt/xvdb1/virtualhost/doubihai/login.txt", $str,FILE_APPEND);
    //file_put_contents("/var/www/html/taotong/login.txt", $str,FILE_APPEND);
   //file_put_contents("D:\wamp\www\dat.txt", $str,FILE_APPEND);
    
    
}
//判断是否存在sufyan 2017 05
function data_isset($var,$type = 'intval',$default = ''){
    if(isset($var)){ 
        $return = $var;
    }else{
        if($type == 'intval'){
            $return = 0;
        }elseif($type == 'trim'){
            $return = '';
        }
        if($default != ''){
            $return = $default;
        }
    }
    return $return; 
}
function curl_data($url,$data,$type='',$fun=''){
    if($type == 'xml'){
        $header[] = "Content-type: text/xml";
    }
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl_handle, CURLOPT_HEADER, 0);
    curl_setopt($curl_handle, CURLOPT_POST, true);
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
    if($fun == 'weixin'){
        curl_setopt($curl_handle,URLOPT_SSLCERTTYPE,'PEM');
        //curl_setopt($curl_handle,CURLOPT_SSLCERT, '/mnt/xvdb1/virtualhost/doubihai/ThinkPHP/Library/Vendor/MultiPay/certs/wechat/apiclient_cert.pem');
        //curl_setopt($curl_handle,CURLOPT_SSLCERT, '/mnt/xvdb1/virtualhost/doubihai/ThinkPHP/Library/Vendor/MultiPay/certs/wechat/apiclient_cert.pem');
        curl_setopt($curl_handle,CURLOPT_SSLCERT, '/var/www/html/taotong/ThinkPHP/Library/Vendor/MultiPay/certs/wechat/apiclient_cert.pem');
        curl_setopt($curl_handle,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($curl_handle,CURLOPT_SSLKEY, '/mnt/xvdb1/virtualhost/doubihai/ThinkPHP/Library/Vendor/MultiPay/certs/wechat/apiclient_key.pem');
        //curl_setopt($curl_handle,CURLOPT_SSLKEY, '/var/www/html/taotong/ThinkPHP/Library/Vendor/MultiPay/certs/wechat/apiclient_key.pem');
    }
    $output = curl_exec($curl_handle); 
    curl_close($ch);
    if($type == 'xml'){
        $msg = (array)simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $msg;
    }else{
        return $output;
    }
    
}
    /**
    * 格式化参数格式化成url参数
    */
   function url_params($arr)
   {
        $buff = "";
        foreach ($arr as $k => $v){
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
   }
    /**
    * 二维数组排序  lisk
    * @param $field 要排序的字段
    */
    function  arraySequence($array, $field, $sort = 'SORT_DESC',$id)
    {
        $arrSort = array();
        foreach ($array as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        //lisk 添加$id=1 为机构动态分栏使用
        if($id == 1){
            array_multisort($arrSort[$field], constant($sort));
        }else{
            array_multisort($arrSort[$field], constant($sort), $array);
        }
        return $array;
    }
    /**
    * 把数字1-1亿换成汉字表述，如：123->一百二十三
    * @param [num] $num [数字]
    * @return [string] [string]
    */
    function numToWord($num)
    {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');
        $chiStr = '';
        $num_str = (string)$num;
        $count = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num = null; //临时数字

        $chiStr = '';//拼接结果
        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num]; 
        }else if($count > 2){
            $index = 0;
        for ($i=$count-1; $i >= 0 ; $i--) { 
            $temp_num = $num_str[$i];
            if ($temp_num == 0) {
                if (!$zero_flag && !$last_flag ) {
                    $chiStr = $chiNum[$temp_num]. $chiStr;
                    $last_flag = true;
                }
            }else{
                $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;
                $zero_flag = false;
                $last_flag = false;
            }
            $index ++;
        }
        }else{
            $chiStr = $chiNum[$num_str[0]]; 
        }
        return $chiStr;
    }
 
