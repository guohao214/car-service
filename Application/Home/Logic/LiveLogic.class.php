<?php
namespace Home\Logic;
use Exception;
use Common\Api\LiveApi;
use User\Api\UserApi;
define(QINIU_PTH, 'QiniuPic/autoload.php');
require_once(VENDOR_PATH . QINIU_PTH);
// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;
/**
 * Class LiveLogic
 * @name 直播管理的逻辑处理类*
 * @author sufyan
 */
class LiveLogic
{
    private $liveApi = null;
    private $uid = 0;
    const COINS_JEWEL = 2.5;//10个淘金币=4个钻；

    public function __construct(){
            $this->liveApi = new LiveApi();
    }
    public function live_config($app_version){
        $config = M('configuration')->where('id=1')->find();
        $config['is_update'] = 0;
        if($app_version == $config['check_version']){
            $config['is_normal_version'] = 0;
        }elseif($app_version != $config['version']){
            $config['is_update'] = 1;
        }              
        return $config;
    }
    public function live_to_video($live_info){
        $liveApi = new liveApi();
        $diff = $live_info['end_time'] - $live_info['start_time'];
        if($diff>15){
            $history = $liveApi->getStream($live_info['stream_key'],$live_info['start_time'],$live_info['end_time']);//获取这段时间开播的起始时间
            if($history){
                log_new($history, 'updateLiveStatus');  
                $start = $history['items'][0]['start'];
                $end = $history['items'][0]['end'];
                $try = 0;
                do{
                    if($try == 5) {
                        break;
                    }
                    $ret = $liveApi->saveLive($live_info['stream_key'], $start, $end);
                    $try ++;
                }while(!$ret['fname']);
                log_new($ret, 'updateLiveStatus');  
                if($ret['fname']) {
                    $play_url = C('QINIU.live_storage').'/'.$ret['fname'];
                    $live_recorded = array(
                        'live_id' => $live_info['id'],
                        'uid' => $live_info['uid'],
                        'group_id' => $live_info['group_id'],
                        'cover_url' => $live_info['cover_url'],
                        'start_time' => $start,
                        'end_time' => $end,
                        'title' => $live_info['title'],
                        'source_flag' => $live_info['source_flag'],
                        'org_id' => $live_info['org_id'],
                        'play' => $play_url,
                        'add_time' => time()
                    );
                    $l_r = M('live_recorded')->add($live_recorded);
                    log_new($l_r, 'updateLiveStatuslive_recorded');  
                    if(!$l_r){
                        log_new(json_encode($live_recorded),'live_recorded add fail');
                    }
                }else{
                    log_new(json_encode($ret),'live_to_video');
                }
            }else{
                log_new(json_encode($history),'get_stream_start_end');
            }
        }
    }
    public function view_gains(){
        $income = $this->get_total_income();//获得总的钻石
        $config = $this->live_config();
        $data['income'] = $income/$config['coin_jewel_scale'];//获得总的钻石
        $data['cash'] = $data['income']*$config['cash_jewel_scale'];//现金（总的）
        $data['able_cash'] = intval($data['cash'] - $data['cash']%$config['lowest_withdraw']);//可提的金额数
        return $data;
    }

    public function withdraw_cash_detail($post){
        $view_gains = $this->view_gains();
        $config = $this->live_config();
        $cash = $post['cash'];//提现现金
        $account = $post['account'];//I('account','','trim');//账号
        $account_name = $post['account_name'];//I('account_name','','trim');//账号名称
        if($account == '' || $account_name == ''){
            throw new Exception('参数错误', -2);
        }     
        if($cash > $view_gains['cash'] || $view_gains['cash'] < $config['lowest_withdraw']){
            throw new Exception('未达到提现额度暂时不能提现', -3);
        }
        if(is_int($cash/$config['lowest_withdraw']) == false){
            throw new Exception('提现以'.$config['lowest_withdraw'].'的整数倍', -4);
        }
        //添加提现记录
        $arr['cash'] = $cash;
        $arr['account'] = $account;
        $arr['account_name'] = $account_name;
        $arr['add_time'] = time();
        $result = M('gift_withdraw_cash')->add($arr);
        if(!$result){
            throw new Exception('添加提现记录失败', -5);
        }
        $income = $this->get_total_income();//获得总的受赏淘气数        
        $coins = $cash * $config['coin_cash_scale'];
        $total_coins = $income - $coins;
        $newdata['income'] = $total_coins/$config['coin_jewel_scale'];
        $newdata['cash'] = $view_gains['cash'] - $cash;
        //添加交易记录
        $exchange['coins'] = $coins;
        $exchange['uid'] = $this->uid;
        $exchange['total_coins'] = $total_coins;
        $exchange['type'] = 3;
        $exchange['add_time'] = time();
        $ex = M('gift_exchange')->add($exchange);
        if(!$ex){
            throw new Exception('添加交易记录失败', -6);
        }
        return $newdata;
    }
    private function get_total_income(){//钻是受赏的和提现剩下的
        $condition = 'uid='.$this->uid.' and (type=1 or type=3)';
        $start_coins = M('gift_exchange')->field('total_coins')->where($condition)->order('id desc')->find();
        if(empty($start_coins)){
            $income = 0;
        }else{
            $income = $start_coins['total_coins']; 
        }
        return $income;
    }

    public function add_tim_message($group_id,$follow_who,$who_follow){
        if($group_id!=''){
            $follow_who = M('member')->field('nickname')->where('uid='.$follow_who)->find();
            $who_follow = M('member')->field('nickname')->where('uid='.$who_follow)->find();
            $msg = $who_follow['nickname'].'关注了主播';
            $param0 = array("sendMessageString"=>$msg,"messageType"=>7);
            $json_str = str_replace('"','\"',json_encode($param0));
            $post_data = '{
                "GroupId": '.'"'.$group_id.'"'.',
                "Random": '.mt_rand(1000000,9999999).',
                "MsgBody": [
                    {
                        "MsgType": "TIMCustomElem",
                        "MsgContent": {
                            "Data": '.'"'.$json_str.'"'.',
                            "Desc": "notification"
                        }
                    }
                ]
            }';
            $this->create_group($post_data,'send_group_msg');
        }
    }
    public function update_live_count($uid){
        $model = M('member');
        $member = $model->field('live_count,uid')->where('uid='.$uid)->find();
        $member['live_count'] = $member['live_count'] + 1;
        $model->save($member);
        return $member['live_count'];
    }
    public function get_live_count($uid){
        $member = M('member')->field('live_count')->where('uid='.$uid)->find();
        return $member['live_count'];
    }
    public function get_income($live){
        if(!$live['start_time'] || !$live['end_time']){
            return 0;
        }
        //type=1 or type=3 受赏的和提现的是钻
        $start_condition = 'uid='.$this->uid.' and (type=1 or type=3) and ( add_time between '.$live['start_time'].' and '.$live['end_time'].')';
        $start_coins = M('gift_exchange')->field('id,type,coins,total_coins')->where($start_condition)->order('id asc')->find();
        
        if(empty($start_coins)){
            return 0;
        }else{
            $end_coins = M('gift_exchange')->field('id,total_coins')->where($end_condition)->order('id desc')->find();
            if($start_coins['id'] == $end_coins['id']){
                $income = $start_coins['coins']/self::COINS_JEWEL;
            }else{
                if($start_coins['type'] == 1){
                    $income = ($end_coins['total_coins'] - ($start_coins['total_coins'] - $start_coins['coins']))/self::COINS_JEWEL; 
                }else{
                    $income = ($end_coins['total_coins'] - ($start_coins['total_coins'] + $start_coins['coins']))/self::COINS_JEWEL; 
                }                
            }
        }
        return $income;
    }
    public function get_increased_fans($live){
        if($live['start_time']&&$live['end_time']){
            $condition = 'follow_who='.$this->uid.' and is_follow = 1 and create_time >='.$live['start_time'].' and create_time<='.$live['end_time'];
            $follow = M('follow')->field('count(*) as sum')->where($condition)->find();
            return $follow['sum'];
        }else{
            throw new Exception('暂时无法统计', -5);
        }
    }
    public function format_date($time){
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        if($time >= 3600){
            $hours = floor($time/3600);
            $time = ($time%3600);
        }
        if($time >= 60){
            $minutes = floor($time/60);
            $time = ($time%60);
        }
        $seconds = floor($time);
        return $hours.':'.$minutes.':'.$seconds;
    }
    public function add_follow($data){
        $follow = M('follow')->field('id')->where('who_follow='.$data['who_follow'].' and follow_who='.$data['follow_who'])->find();
        if(empty($follow)){
            $result = M('follow')->add($data);
        }else{
            $data['id'] = $follow['id'];
            $result = M('follow')->save($data);
        } 
        if(!$result){
            throw new Exception('关注失败', -2);
        }
    }
    public function cancle_follow($data){
        $follow = M('follow')->field('id')->where($data)->find();
        if(!empty($follow)){
            $data['is_follow'] = 0;
            $data['id'] = $follow['id'];
            $result = M('follow')->save($data);
            if(!$result){
                throw new Exception('取消关注失败', -2);
            }
        }        
    }
    public function count_inline_user($data,$use_count){        
        $result = M('live')->save($data);
        if(!$result){
            throw new Exception('统计在线人数失败', -3);
        }
        $this->inline_tim_message($data['id'],$use_count);        
    }
    private function inline_tim_message($live_id,$use_count){
        $live = M('live')->field('group_id')->where('id='.$live_id)->find();
        $param0 = array("count"=>$use_count,"messageType"=>8);
        $json_str = str_replace('"','\"',json_encode($param0));
        $post_data = '{
                "GroupId": '.'"'.$live['group_id'].'"'.',
                "Random": '.mt_rand(1000000,9999999).',
                "MsgBody": [
                        {
                                "MsgType": "TIMCustomElem",
                                "MsgContent": {
                                        "Data": '.'"'.$json_str.'"'.',
                                        "Desc": "notification"
                                }
                        }
                ]
        }';
        $this->create_group($post_data,'send_group_msg');
    }
    
    public function live_is_overdue($uid,$source_flag,$org_id){
        $result = array();
        $live_info = M('live')->where('uid='.$uid.' and source_flag='.$source_flag.' and org_id='.$org_id)->field('uid,stream_key,publish,play,group_id,create_time,title,cover_url,id')->order('id desc')->find();
        if(empty($live_info)){
            return $result;
        }
        $diff = (time() - $live_info['create_time'])/43200;
        if($diff < 1 && $diff >0){
            return $live_info;
        }else{
            return $result;
        }
    }
    private function check_live_exist($condition=''){        
        $live = M('live')->field('id,online_users,start_time,end_time,group_id')->where($condition)->find();
        if(empty($live)){
            throw new Exception('此直播不存在', -2);
        }
        return $live ;
    }
    public function get_live($live_id,$status=0){ 
        if(!$live_id){
            throw new Exception('参数有误', -4);
        }  
        if($status==0){
            $condition = 'id='.$live_id;
        }else{
            $condition = 'id='.$live_id.' and uid='.$this->uid;
        }
        $live = $this->check_live_exist($condition);
        return $live;
    }
	public function check_login($uid=0){
            if($uid != 0){
                $this->uid = $uid;
            }else{
                $this->uid = is_login();
                //$this->uid = 1584;
                if(!$this->uid) {
                        throw new Exception('无权限', -1);
                }
            }
            return $this->uid;
	}
	/**
    *@author sfy 2017-04-01
    *@param  直播预告
    *@return json
    */
  public function get_live_priview(){
        $order = 'lp.sort desc';
        $filed = 'lp.title, lp.uid, lp.live_time, m.nickname, m.avatar';
        $live_priview = M()->table(array('dbh_live_priview'=>'lp','dbh_member'=>'m'))->field($filed)
                           ->where('lp.is_delete=0 and lp.status=0 and lp.uid=m.uid')->order($order)->limit(5)->select();
        if(!empty($live_priview)){
            $weekarray=array('日','一','二','三','四','五','六'); //先定义一个数组    
            foreach ($live_priview as $key => $value){
                foreach ($value as $k => $v) {
                    if(!$v) $live_priview[$key][$k] = '';                   
                }
                $live_priview[$key]['live_week'] = '星期'.$weekarray[date('w',$value['live_time'])];
                $live_priview[$key]['live_time'] = date('m-d H:i',$value['live_time']);
                $live_info = M('live')->where('uid='.$value['uid'].' and status=10')->field('status,publish,play,group_id,cover_url,id')->order('id desc')->find();
                if(!empty($live_info)){
                    $live_priview[$key]['is_living'] = 1;
                    $live_priview[$key]['publish'] = $live_info['publish'];
                    $live_priview[$key]['group_id'] = $live_info['group_id'];
                    $live_priview[$key]['live_id']=$live_info['id'];
                    $live_priview[$key]['cover_url'] = $live_info['cover_url'];
                }else{
                    $live_recorded = M('live_recorded')->field('play,live_id,group_id,cover_url')->where('uid='.$value['uid'].' and play!=""')->order('id desc')->find();
                    if(!empty($live_recorded)){
                        $live_priview[$key]['group_id'] = $live_recorded['group_id'];
                        $live_priview[$key]['live_id']=$live_recorded['live_id'];
                        $live_priview[$key]['is_living'] = 0;
                        $live_priview[$key]['play'] = $live_recorded['play'];
                        $live_priview[$key]['cover_url'] = $live_recorded['cover_url'];
                    }
                }
            }
        }
        return $live_priview;
    }
    /**
    *@author sfy 2017-03-31
    *@param  获取和设置直播封面和标题
    *@return json
    */
    public function set_live_info($uid,$live_id,$live_info,$parmas){
        if(!$live_id){
            throw new Exception('无效id', -2);
        }
        $old_cover_url = $live_info['cover_url'];
        $newlive_info['title'] = $live_info['title'];
        $newlive_info['cover_url'] = $live_info['cover_url'];     	

        $title = $parmas['title'];
        $cover_pic = $parmas['cover_pic'];
        if($cover_pic!=''){
            $is_base64 = $this->is_base64($cover_pic);
            if($is_base64 == true){
                $newlive_info['cover_url'] = $this->get_cover_url($cover_pic,$uid); 
            }else{
                $newlive_info['cover_url'] = $cover_pic; 
            }
        }
        if($title!=''){
            $newlive_info['title'] = $title;
        }
        $newlive_info['update_time'] = time();
        $newlive_info['start_time'] = $newlive_info['update_time'];
        $newlive_info['end_time'] = 0;
        $return = M('live')->where(array('id' => $live_id))->save($newlive_info);
        if(!$return){
                throw new Exception('封面和标题设置失败', -7);
        }
        //$this->del_cover_url($old_cover_url);	删除老的封面	
        return $newlive_info;
    }
    //判断字符串是否经过编码方法
    private function is_base64($str){
        if($str==base64_encode(base64_decode($str))){
            return true;
        }else{
            return false;
        }
    }   
	//上传封面图片
	private function get_cover_url($cover_pic,$uid){
		// 需要填写你的 Access Key 和 Secret Key
        $accessKey = '-7DRC0sYQay1zfUxrqSbHv55hQX_6YU_-mXkHN85';
        $secretKey = '4XCy0sHUlwQ7zbdeAbUGOQc7yoll_YsPGuvqynH5';
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 要上传的空间
        $bucket = 'doushow';
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        $pic = 'bipai'.$uid.'-'.NOW_TIME.'.jpg';
        //$pic_base64 = explode(',', $cover_pic);
        // 要上传文件的本地路径x
        //$filePath = 'D:\wamp\www\bipai-web\Livepic'."\\".$pic;
        $filePath = '/mnt/xvdb1/virtualhost/doubihai/Livepic'."/".$pic;        
        // 本地存储图片
        file_put_contents($filePath, base64_decode($cover_pic));
        // 上传到七牛后保存的文件名
        $key = $pic;
        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        //echo "\n====> putFile result: \n";print_r($ret);exit;
        if ($err !== null) {
            var_dump($err);
        } else {            
            unlink($filePath);
            return 'http://vod.doushow.com/'.$ret['key'];
        }
	}

	public function get_group_info($uid,$source_flag){
            $member = M("member");
            $info = $member->where(array('uid'=>$uid))->find();
            if($source_flag == 1){
                if($info["anchor_status"] == 0){
                    throw new Exception('无主播权限'.$uid, -4);
                }
                if($info["anchor_status"] == 4){
                    throw new Exception('您因违反比拍主播公约已被禁播，请加群【群号】处理！', -5);
                }
            }
            $post_data = array("Owner_Account" => "".$uid,"Type" => "AVChatRoom","Name"=>"DoubihaiGroup_".$uid);
            $group_id = $this->create_group(json_encode($post_data),'create_group');		
            return $group_id;
	}

	public function create_group($post_data,$operation,$type=0){
            $random = time();
            //腾讯云通信签名
            $identifier = C('TX_YUNTONGXIN.IDENTIFIER');
            $sdkappid = C('TX_YUNTONGXIN.APPID');
            $sig = \Home\Controller\HomeController::get_tx_signature($identifier);
            //请求腾讯创建群组接口
            $base_url = "https://console.tim.qq.com/v4/";
            $url =  $base_url."group_open_http_svc/".$operation."?usersig=".$sig."&identifier=".$identifier."&sdkappid=".$sdkappid."&apn=1&contenttype=json";
            $output = curl_data($url, $post_data, '', 'txyun');
            $output = json_decode($output);
            $data = array('data'=>$post_data,'type'=>$output->ErrorCode,'add'=>time());                
            M('test')->add($data);
            log_new($output->ErrorCode,'send_group_msg');
            if($operation == 'create_group'){
                if($output->ErrorCode != 0){
					if($type == 0)
                        throw new Exception('创建聊天室失败', -6);
                    else
                        return 0;
                    
                }else{
                    return $output->GroupId;
                }
            }elseif($operation == 'send_group_msg'){
                if($output->ErrorCode != 0){
                    throw new Exception('发送消息失败', -6);
                }  
            }		     
	}
	public function create_live($stream_key,$group_id,$uid,$source_flag,$org_id){		
            $stream_info = null;
            $liveApi = $this->liveApi;

            if($liveApi->getStreamStatus($stream_key)){
                    throw new Exception('用户已经在直播了', -2);
            }else{
                    $stream_info = $liveApi->createStream($stream_key);
            }

            $data = Array();
            $data['stream_key'] = $stream_key;
            $data['publish'] = $stream_info['publish'];
            $data['play'] = $stream_info['play'];
            $data['uid'] = $uid;
            $data['source_flag'] = $source_flag;
            $data['org_id'] = $org_id;
            //$data['cover_url'] = $stream_info['cover_url'];
            $data['group_id'] = $group_id;
            $data['create_time'] = NOW_TIME;

            $liveModel = M('live');
            $live_id = $liveModel->add($data);
            if(!$live_id) {
                    throw new Exception('直播创建失败', -3);
            }
            $data['id'] = $live_id;

            return $data;
	}
        public function encrypt($input){
		$size = mcrypt_get_block_size(MCRYPT_3DES,MCRYPT_MODE_CBC);
		$input = $this->pkcs5_pad($input, $size);
		$key = str_pad($this->key,24,'0');
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
		if( $this->iv == '' )
		{
			$iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		}
		else
		{
			$iv = $this->iv;
		}
		@mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);
		return $data;
	}
	public function decrypt($encrypted){
		$encrypted = base64_decode($encrypted);
		$key = str_pad($this->key,24,'0');
		$td = mcrypt_module_open(MCRYPT_3DES,'',MCRYPT_MODE_CBC,'');
		if( $this->iv == '' )
		{
                    $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		}
		else
		{
                    $iv = $this->iv;
		}
		$ks = mcrypt_enc_get_key_size($td);
		@mcrypt_generic_init($td, $key, $iv);
		$decrypted = mdecrypt_generic($td, $encrypted);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$y=$this->pkcs5_unpad($decrypted);
		return $y;
	}
	private function pkcs5_pad ($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
	private function pkcs5_unpad($text){
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) {
			return false;
		}
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
			return false;
		}
		return substr($text, 0, -1 * $pad);
	}
	private function PaddingPKCS7($data) {
		$block_size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
		$padding_char = $block_size - (strlen($data) % $block_size);
		$data .= str_repeat(chr($padding_char),$padding_char);
		return $data;
	}
}
