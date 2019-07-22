<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;
use Exception;
use Home\Logic\UserLogic;
use User\Api\WXLoginHelper;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {
     public function wxLogin(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        
        $code = urldecode($data["code"]);
        $rawData = urldecode($data["rawData"]);
        $signature = urldecode($data["signature"]);
        $encryptedData = urldecode($data["encryptedData"]);
        $iv = urldecode($data["iv"]);

        $wxHelper = new WXLoginHelper();
        $data = $wxHelper->checkLogin($code, $rawData, $signature, $encryptedData, $iv);
        echo json_encode($data);
        exit;
    }

    public function getToken(){
        header("Access-Control-Allow-Origin:*");

        $wxHelper = new WXLoginHelper();
        $res = $wxHelper->getToken();

        //$res = json_decode(json_encode($json), true);
        // $access_token = $res['result']['access_token'];
        // echo $access_token;
        $result = $res['result'];
        //$result = json_decode(json_encode($result), true);
        //print_r($result);
        $res = json_encode($result);
        $patten = array("\r\n", "\n", "\r", "\"", "\\");
        $str=str_replace($patten, "", $res);
        $str = str_replace("{access_token:", "", $str);
        $str = preg_replace("/,expires_in.*/", "", $str);
        //echo $str;
        return $str;
    }

    public function appGetPhone(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        
        $sessionKey = urldecode($data["sessionKey"]);
        $encryptedData = urldecode($data["encryptedData"]);
        $iv = urldecode($data["iv"]);
        $open_id = urldecode($data["open_id"]);
        $wxHelper = new WXLoginHelper();
        $data = $wxHelper->decryptData($encryptedData, $iv, $sessionKey);
        $data = json_decode($data,true);
        if($data['phoneNumber']){
            M('user')->where(['open_id'=>$open_id])->save(['phonenumber'=>'']);

            if(M('user')->where(['open_id'=>$open_id])->save(['phonenumber'=>$data['phoneNumber']])){
                echo json_encode(array('status'=>1,'msg'=>'绑定成功'));
                exit;
            }
        }
        echo json_encode(array('status'=>0,'msg'=>'绑定失败！'));
        exit;
    }


    public function getUnlimited(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $scene = $data['scene'];
        $page = $data['page'];
        $openId = $data['openId'];

        $info = M('user')->where(['open_id'=>$openId])->find();
        if(empty($info)){
            echo json_encode(array('status'=>0,'msg'=>'抱歉，登录失败！'));
            exit;
        }

        if(!empty($info['qrcode'])){
            echo json_encode(array('status'=>1,'msg'=>'二维码生成', 'url'=>$info['qrcode']));
            exit;
        }

        $uid = $info['id'];

        if(!isset($scene)){
            $scene = "uid=" . $uid;
        }

        $wxHelper = new WXLoginHelper();
        $access_token = $this->getToken();

        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $param = [
            //"access_token"=>$access_token,
            "scene"=>$scene
        ];


        if(isset($page)){
            $param['page'] = $page;
        }


        $res = $wxHelper->makeRequestJson($url, json_encode($param));
        //echo "<br><br>";
        $filepath = $this->UploadImageQrCode($res);
        $filepath = 'http://www.maixc.cn/qrcode/'.$filepath;
        M('user')->where(['open_id'=>$openId])->save(['qrcode'=>$filepath]);
        //echo "filePath:" . $filepath;
        echo json_encode(array('status'=>1,'msg'=>'二维码生成', 'url'=>$filepath));
    }

    public function UploadImageQrCode($img){        
        $fileimgname = time()."-".rand(1000,9999).".png";
        $filepath = APP_PATH . '/../qrcode/' . $fileimgname;

        // $img=file_get_contents($image);
        $res = file_put_contents($filepath, $img);
        return $fileimgname;
    }

    public function appAddCommission(){
       header("Access-Control-Allow-Origin:*");
       $commission = $this->post_origin_data;
       $where = [
           'parent_id'=>$commission['parent_id'],
           'member_id'=>$commission['member_id'],
           'type'=>$commission['type']
       ];
       $info = M('commission')->where($where)->find();
        file_put_contents('thumb.txt', '------'.json_encode($this->post_origin_data),FILE_APPEND);
        if(empty($info)){
            if(M('commission')->add($commission)){
                echo json_encode(array('status'=>1,'msg'=>'成功！'));
            }else{
                echo json_encode(array('status'=>0,'msg'=>'抱歉，失败！'));
            }
        }else{
            echo json_encode(array('status'=>0,'msg'=>'已领取！'));
        }
       
    }
	/* 用户注册接口 */
	public function appAddUser(){
       header("Access-Control-Allow-Origin:*");
       $user = $this->post_origin_data;
       $openId = $user['openId'];

       $info = M('user')->where(['open_id'=>$openId])->find();

       if(empty($info)){
            $user['open_id'] = $user['openId'];
            unset($user['watermark']);
            unset($user['openId']);

           if(M('user')->add($user)){
                $user['uid'] = M('user')->getLastInsID();
                file_put_contents('thumb.txt', '------'.json_encode($user),FILE_APPEND);
                echo json_encode(array('status'=>1,'msg'=>'登录成功！','data'=>$user));
            }else{
                echo json_encode(array('status'=>0,'msg'=>'抱歉，登录失败！'));
            }
       }else{
            $group = M('group_user')->where(['uid'=>$info['uid']])->find();
            $new = array_merge($info,$group);
            echo json_encode(array('status'=>1,'msg'=>'已登录，不能重复登录', 'data'=>$new));
       }
	} 

    /* 用户接口 */
    public function appUserDetail(){
        header("Access-Control-Allow-Origin:*");
       $user = $this->post_origin_data;
       $openId = $user['open_id'];
       $uid = $user['uid'];
       $where = [];
       if($openId){
            $where['open_id'] = $openId;
       }
       if($uid){
            $where['uid'] = $uid;
       }
       $info = M('user')->where($where)->find();
       echo json_encode(array('status'=>1,'msg'=>'已登录', 'data'=>$info));
    }

    public function appUpdateUser(){
       header("Access-Control-Allow-Origin:*");
       $user = $this->post_origin_data;
       $uid = $user['uid'];
       $parent_id = $user['parent_id'];
       $info = M('user')->field('parent_id')->where(['uid'=>$uid])->find();
       $_info = M('user')->field('uid')->where(['parent_id'=>$uid,'uid'=>$parent_id])->find();
       if($info && !$_info && !$info['parent_id']){
            M('user')->where(['uid'=>$uid])->save(['parent_id'=> $parent_id ]);
       }       
    }
}
