<?php
namespace Home\Logic;
use Exception;
/**
 * Class UserLogic
 * @name 首页 逻辑处理类*
 * @author sufyan add
 */
class CallLogic
{
    
    public function check_authentication($post){
        $name = data_isset($post['name'],'trim');
        if(empty($name)) {
            throw new Exception('姓名不能空',1023);
        }
        $mobile = data_isset($post['mobile'],'trim');
        $title_len = mb_strlen($mobile, 'utf-8');
        if(empty($mobile)){
            throw new Exception('手机号为空',1024);
        }
        if($title_len>11 || $title_len<11) {
            throw new Exception('请输入正确的手机号',1025);
        }
        $number = data_isset($post['number'],'trim');
        $title_len1 = mb_strlen($number, 'utf-8');
        if(empty($number)){
            throw new Exception('身份证不能为空',1026);
        }
        if($title_len1>18 || $title_len1<18) {
            throw new Exception('请输入正确的身份证号',1027);
        }
        // if(preg_match('^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$', $number)){
        //     log_new(222244);
        // }else{
        //     log_new(111144);
        // }
        $data = array(
            'name'          =>  $name,
            'mobile'        =>  $mobile,
            'number'        =>  $number,
            'anchor_status' => 1
        );
        return $data;
    }
    // public  function isPersonalCard($number) {
    //     if (!$number) {
    //         return false;
    //     }
    //     return preg_match('^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$', $number) ? true : false;
    // }
    /**
    * 过滤emoji表情
    * @param string   $str 字符串 
    * @return string  lisk 2017年6月8日15:21:20
    */
    public  function filterEmoji($str){

             if(empty($str)) return null;
              $str = preg_replace_callback(
               '/[\xf0-\xf7].{3}/', 
                function($r){ 
                 return '@E' . base64_encode($r[0]);
                },$str);
              $countt=substr_count($str,"@");
             for ($i=0; $i < $countt; $i++) {
                 $c = stripos($str,"@");
                 $str=substr($str,0,$c).substr($str,$c+10,strlen($str)-1);
             }
             $str = preg_replace_callback(
               '/@E(.{6}==)/', 
               function($r){
                 return base64_decode($r[1]);
               }, $str);
              return $str;
    }
}
