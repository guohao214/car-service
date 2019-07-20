<?php
namespace Home\Logic;
use Exception;
use Common\Api\LiveApi;
use User\Api\UserApi;
/**
 * Class UserLogic
 * @name 用户登录的逻辑处理类*
 * @author sufyan add
 */
class UserLogic
{
    private $user_api = null;
    public function __construct(){
        $this->user_api = new UserApi();
        $this->member_model = new \Home\Model\MemberModel();
    }
    /* UC登录 
     * sufyan 2017-05
     */
    public function uc_login($mobile, $password){
        if(empty($mobile)||empty($password)) {
            throw new Exception('请输入完整登录信息',1001);
        }
        $uid = $this->user_api->login($mobile, $password);
        if($uid > 0){
            return $uid;
        }else{
            switch($uid){
                case -1:
                    throw new Exception('用户被禁用',1002);break;
                case -2:
                    throw new Exception('密码错误',1003);break;
                case -3:
                    throw new Exception('用户不存在,请注册',1014);break;
                default :
                    throw new Exception('系统维护中请联系管理员',1004);
            }
        }
    }
    /* member前台用户登录 
     * sufyan 2017-05
     */
    public function member_login($uid){
        $user = $this->member_model->field(true)->find($uid);
        if(!$user){ //未注册
            throw new Exception('用户不存在，请注册',1014);
        } elseif(1 != $user['status']) {
            throw new Exception('用户被禁用',1002);
        }
        $info = $this->user_api->info($uid);
        if($userinfo == -1){
            throw new Exception('用户被禁用',1002);
        }elseif($userinfo == -2){
            throw new Exception('用户不存在，请注册',1014);
        }
        return $info;
    }
    /* 登录类型
     * sufyan 2017-05
     */
    public function login_type($type){
        switch($type){
            case 1:
                $login_type = 'QQ';break;
            case 2:
                $login_type = 'WEIXIN';break;
            case 3:
                $login_type = 'WEIBO';break;
            case 4:
                $login_type = 'APP';break;
            default :
                throw new Exception('暂时不支持您的登录类型',1008);
        }
        return $login_type;
    }
    /* 更新用户TOKEN（也可放到REDIS里） 
     * sufyan 2017-05
     */
    public function update_token($uid,$token_entrypt){
        $data['uid'] = $uid;
        $data['token_entrypt'] = $token_entrypt;
        $result = D('member')->save($data);
        if(!$result){
            throw new Exception('更新TOKEN失败，请联系管理员',1006);
        }
    }
    /* 更新用户TOKEN密码（也可放到REDIS里） 
     * sufyan 2017-05
     */
    public function pwd_check($uid,$post){
        //获取参数
        $oldPwd   =   data_isset($this->post_origin_data['old'],'trim');
        $repassword = data_isset($this->post_origin_data['repassword'],'trim');
        $data['password'] = data_isset($this->post_origin_data['password'],'trim');
        if(empty($oldPwd)){
            throw new Exception('请输入原密码',1015);
        }
        if(empty($data['password'])){
            throw new Exception('请输入新密码',1016);
        }
        if(empty($repassword)){
            throw new Exception('请输入确认密码',1017);
        }
        if($data['password'] !== $repassword){
            throw new Exception('您输入的新密码与确认密码不一致',1018);
        }
        //密码验证
        if(!$this->user_api->checkPwdFormat($data['password'])) {
            throw new Exception('密码长度为6-20个字母或数字',1019);
        }
        $res = $this->user_api->updatePwd($uid, $oldPwd, $data['password']);
        if($res['status'] == false){
            throw new Exception($res['info'],1020);
        }
    }
    /* 检查注册信息
     * sufyan 2017-05
     */
    public function check_register($post){
        if(!C('USER_ALLOW_REGISTER')){
            throw new Exception('注册已关闭',1126);
        }
        $mobile   =   data_isset($post['mobile'],'trim');
        $password = data_isset($post['password'],'trim');
        $repassword   =   data_isset($post['repassword'],'trim');
        $verify = data_isset($post['verify'],'trim');
        $platform   =   data_isset($post['platform'],'trim');
        $device_id = data_isset($post['device_id'],'trim');
        if(empty($mobile)) {
            throw new Exception('手机号码不能为空',11277);
        }
        //手机号唯一验证
        if($this->user_api->checkMobileExist($mobile)) {
            throw new Exception('该手机号已经注册',1127);
        }
        if(!preg_match('/^1(2[0-9]|3[0-9]|4[0-9]|5[0-9]|6[0-9]|7[0-35-9]|8[0-9]|9[025-9])\d{8}$/', $mobile)) {
            throw new Exception('手机号码格式不正确',1128);
        }
        if(empty($password)) {
            throw new Exception('密码不能为空',1129);
        }
        if(!$this->user_api->checkPwdFormat($password)) {
            throw new Exception('密码长度为6-20位的字母或数字',1130);
        }
        if(empty($repassword)) {
            throw new Exception('重复密码不能为空',1131);
        }
        if($password !== $repassword) {
            throw new Exception('两次密码输入不一致',1132);
        }
        if(empty($verify)) {
            throw new Exception('请输入手机获取到的验证码',1133);
        }
    }
    public function verify_sms($mobile, $verify){
        if(!empty($_SESSION[$mobile.'_code'])){
            $code = intval(substr($_SESSION[$mobile.'_code'],0,4));
            if($verify == $code){
                unset($_SESSION[$mobile.'_code']);
                return true;
            }
        }
        return false;
    }
    public function get_sms($mobile,$is_reg,$app_type=2){
        if(!C('USER_ALLOW_REGISTER')){
            throw new Exception('注册已关闭',1126);
        }
        if(empty($mobile)){
            throw new Exception('手机号码不能为空',11277);
        }
        if(!preg_match('/^1(2[0-9]|3[0-9]|4[0-9]|5[0-9]|6[0-9]|7[0-35-9]|8[0-9]|9[025-9])\d{8}$/', $mobile)) {
            throw new Exception('手机号码格式不正确',1128);
        }
        if(!empty($_SESSION[$mobile.'_code'])){
            $last_time = substr($_SESSION[$mobile.'_code'],4);
            $end = time() - $last_time;
            if($end < 120){
                throw new Exception('2分钟内不能多次获取短信验证码',11278);
            }
        }
        $one = M('ucenter_member')->where('mobile='.$mobile)->count();
        if($is_reg == 0){//判断是0注册 1修改密码
            if($one){
                throw new Exception('用户已存在',1014);
            }
        }elseif($is_reg == 1){
            if(!$one){
                throw new Exception('用户不存在，请注册',1014);
            }
        }
        $sms = $this->send_txy_sms($mobile, $app_type);
        return $sms;
    }
    private function send_txy_sms($mobile,$app_type){
        $time = NOW_TIME;
        $sms = rand(1000,9999);
        $appkey = 'fa29ba48d07679a1215ee269fe8bb565';
        $sdkappid = '1400031727';
        $random = rand(100000,99999).rand(100000,99999);
        $sig = hash("sha256", 'appkey='.$appkey.'&random='.$random.'&time='.$time.'&mobile='.$mobile);
        $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid='.$sdkappid.'&random='.$random;
        $post_arr = array('tel'=>array('nationcode'=>'86','mobile'=>$mobile),
           'sign'=>'','tpl_id'=>'21027','params'=>array($sms),'sig'=>$sig,'time'=>$time,'extend'=>'','ext'=>'');
        //echo json_encode($post_arr);exit;
        /*$sig = hash("sha256", 'appkey='.$appkey.'&random='.$random.'&time='.$time);
        $url = 'https://yun.tim.qq.com/v5/tlssmssvr/add_template?sdkappid='.$sdkappid.'&random='.$random;
        $post_arr = array('sig'=>$sig,'time'=>$time,'title'=>'测试','remark'=>'测试','text'=>'你的短信是','type'=>0);*/
        $return = json_decode(curl_data($url, json_encode($post_arr)),true);
        if($return['result'] == 0){
            $_SESSION[$mobile.'_code'] = $sms.$time;
            //$result = array('status'=>1,'info'=>'获取验证码成功','data'=>array($sms));
        }else{
            //$result = array('status'=>$return['result'],'info'=>$return['errmsg']);
           // $result = array('status'=>$return['result'],'info'=>$return['errmsg']);
            throw new Exception('获取验证码失败',$return['result']);
        }
        return $sms;
    }
    public function is_follow_official($uid){
            $map['who_follow'] = $uid;
            $map['follow_who'] = 1789;
            $select = M('follow');
            if($select->where($map)->find()){
                return;
            }else{
                $map['create_time'] = time();
                $map['is_follow'] = 1;
                $select->add($map);
                M('member')->where('uid='.$uid)->setInc('follows');
                return;
            }
    }
}
