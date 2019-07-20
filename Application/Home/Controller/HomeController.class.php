<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {
    protected $post_origin_data = null;//没有加密就是post_data, 有加密就是解过密的post_data。
    protected $user_uid = 0;//只有新接口才有用户ID
    protected $is_entrypt = 0;//老接口也有不加密的情况
    protected $is_old = 0; //老接口 加密但没有传is_entrypt字段
    /* 初始化操作 
     * sufyan 2017-05
     */
    public function __construct() {
        parent::__construct();
        $this->is_entrypt = I('is_entrypt',0,'intval');
        //echo decrypt('aTsKXP+IHZA8DERWdKqffmX9Mq2f4C9aIoI594EkzpfNpI77lZi8ljGqgZnhSIv6rWkHchpk/r5a5cOPz1YASdpJ/wIHiSi6vjDHXo2JcD5GdvzTbMyZIoYuYkX8XbsJG2Vf2MEIlSa97vPmPiVPIe90YfOh8yzwORMkMDxchI4bmBprEPc6rXD51aSSdqsMLGvmyZBNUV5K2HTq4AOm9fvYDEnxuRVpvj8hJ0vmlL44XMVWWuJ7K2hVgrRtF5jT');exit;
        $this->post_origin_data = $this->get_post();
        $this->user_uid = data_isset($this->post_origin_data['user_uid'],'intval');
        if($this->is_entrypt == 1){//判断TOKEN：异地登录 调用接口;如果加密就要传TOKEN值
            $token = data_isset($this->post_origin_data['token_entrypt'],'trim');
            if($token){
                $is_true = $this->get_token($this->user_uid, $token);
                if($is_true == false){
                    $result = array('status'=>1007,'info'=>'您的账户已在异地登录');
                    $this->put_post($result);
                }
            }
        }
    }
    /* 判断登录，获取uid 
     * sufyan 2017-05
     */
    protected function check_login(){
        if($this->user_uid > 0){
            $uid = $this->user_uid;
        }else{
            $uid = is_login();
        }
	//log_new($uid,'sss');
        if($uid < 0 || $uid == 0) {
            $result = array('status'=>-1,'info'=>'请先登录');
            $this->put_post($result);
        }else{
            return $uid;
        }
    }
    /* 数据统一入口 
     * sufyan 2017-05
     */
    protected function get_post(){
        //判断是否加密，有加密就需要解密（新接口也有不加密的）
        $this->is_entrypt = I('is_entrypt',0,'intval');
        if($this->is_entrypt == 0){
            $post = I('request.');
            if(isset($post['entrypt_data'])){
                $json = decrypt($post['entrypt_data']);
                $post = json_decode($json,true);
                $this->is_old = 1;
            }
        }elseif($this->is_entrypt == 1){
            $encrypted = I('entrypt_data','','trim');
            $json = decrypt($encrypted);
            $post = json_decode($json,true);
        }
        
        return $post;
    }
    /* 数据统一出口 
     * sufyan 2017-05
     */
    protected function put_post($result){
        //判断是否加密，有加密就需要解密（新接口也有不加密的）
        if($this->is_old == 1){
            echo json_encode(array('entrypt_data'=>encrypt(json_encode($result))));
//            log_new(json_encode(array('entrypt_data'=>encrypt(json_encode($result)))),'create_function');
            exit;
        }
        if($this->is_entrypt == 0){
            if($result['status'] == 1){
                if(!empty($result['data'])){
                    $this->renderSuccess($result['info'], $result['data']);
                }else{
                    $this->renderSuccess($result['info']);
                }
            }else{
                $this->renderFailed($result['info'], $result['status']);
            }
        }elseif($this->is_entrypt == 1){
            echo json_encode(array('entrypt_data'=>encrypt(json_encode($result))));
            exit;
        }
    }
    /* 登录时获取TOKEN 
     * sufyan 2017-05
     */
    protected function get_token($uid,$token_entrypt){
        $member_model = new \Home\Model\MemberModel();
        $user = $member_model->field('token_entrypt')->where('uid='.$uid)->find();
        if($user){
            if($user['token_entrypt'] == $token_entrypt){
                return ture;;
            }
        }
        return false;
    }
    /* 生成TOKEN 
     * sufyan 2017-05
     */
    public function create_token($uid){
        $token_str = 'key=doubihai'.'uid='.$uid.'time='.time();
        $token = md5($token_str);
        return $token;
    }
    
    /* 腾讯云通信签名 
     * sufyan 2017-05
     */
    public function get_tx_signature($uid){
        vendor('Tengxunyun/TLSSig');
        $api = new \TLSSigAPI();
        $sdkappid = C('TX_YUNTONGXIN.APPID');
        $identifier = $uid;
        $private_key_path = C('TX_YUNTONGXIN.PRIVATEKEY_PATH');
        $signature = $api->signature($identifier, $sdkappid, $private_key_path);
        if(!$signature) {
           $sig = '';
        }else{
           $sig = $signature[0]; 
        }
        return $sig;
    }
    /* 格式化数据 
     * sufyan 2017-05
     */
    protected function format_data($data){
        if(empty($data)){
            return array();
        }
        foreach ($data as $k=>&$v){
            if($v === null){
                $v = '';
            }
            if(is_array($v)){
                $this->format_data($v);
            }
        }
        return $data;
    }
    
    /* 空操作，用于输出404页面 */
    public function _empty(){
            $this->redirect('Index/index');
    }
    protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
    }
	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}
        
	protected function is_post_commit()
	{
		if(!IS_POST)
		{
			$this->renderFailed('请使用POST方式提交数据,谢谢.');
		}
	}

    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     */
	protected function is_login()
	{
        $user = session('user_auth');

        $uid = empty($user) ? 0 : (session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0);

        if(empty($uid))
        {
        	$this->renderFailed('您未登录,请先登录.',-1);
        }

        return $uid;
	}

}
