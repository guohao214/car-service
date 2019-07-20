<?php
namespace Home\Logic;
use Exception;
use MultiPay\classes\alipay\Alipay;
/**
 * Class GiftLogic
 * @name 打赏礼物的逻辑处理类*
 * @author sufyan
 */
class GiftLogic
{
	private $uid = 0;
        private $to_uid = 0;
        private $live_id = 0;
        private $gift_id = 0;
        private $ret_id = 0;
        private $combo_id = 0;
        private $key = "";  
        private $iv = "";    
        /** 
         * 构造，传递二个已经进行base64_encode的KEY与IV 
         * 
         * @param string $key 
         * @param string $iv 
         */  
        public function __construct($key='T1a2O3T4o5N6g7S8e9C0R.E0T9K8e7Y6', $iv='01234567')  
        {  
            if (empty($key) || empty($iv)) {  
                exit();  
            }  
            $this->key = $key;  
            $this->iv = $iv;  
        } 
        public function update_order_exchange($finish_data){
            $new_info['trade_no'] = I("trade_no","","trim");
            $new_info['status'] = I("status","","trim");
            $new_info['user_accout'] = I("user_accout","","trim");
            $new_info['pay_time'] = strtotime(I("pay_time","","trim"));
            $new_info['payment_type'] = I("payment_type","","trim");
            $new_info['update_time'] = time();
            $new_info['id'] = $finish_data['id'];
            $flag = M('gift_order')->save($new_info);
            if(!$flag){
                throw new Exception('更新订单失败', -2);
            }else{
                $total_coins = $this->self_coins($finish_data['uid']);
                if($new_info['status'] == 1){
                    $gift_combo = M('gift_combo')->field('coins')->where('id='.$finish_data['combo_id'])->find();
                    $gift_order_data['total_coins'] = $gift_combo['coins'] + $total_coins;
                    $gift_order_data['uid'] = $finish_data['uid'];
                    $gift_order_data['coins'] = $gift_combo['coins'];
                    $gift_order_data['type'] = 2;
                    $gift_order_data['record_id'] = $finish_data['id'];
                    $gift_order_data['add_time'] = time();
                    $gift_exchange = M('gift_exchange')->add($gift_order_data);
                    if($gift_exchange){
                        $coins_t['total_coins'] = $gift_order_data['total_coins'];                        
                    }else{
                        throw new Exception('更新淘气币失败', -4);
                    }
                }else{
                    $coins_t['total_coins'] = $total_coins;
                }
            }
            return $coins_t;
        }
        private function self_coins($uid=0){
            if(!$uid){
                $uid = $this->uid;
            }
            $gift_exchange = M('gift_exchange')->field('total_coins')->where('uid='.$uid.' and (type=0 or type=2) ')->order('id desc')->limit(1)->find();
            if(empty($gift_exchange)){
                $total_coins = 0;
            }else{
                $total_coins = $gift_exchange['total_coins'];
            }
            return $total_coins;
        }

        public function create_sign($post)
	{
            $data['service'] = '"mobile.securitypay.pay"';
            $data['partner'] = '"2088421728461432"';
            $data['_input_charset'] = '"utf-8"';
            $data['notify_url'] = '"http://www.doubihai.com/Czf/notify_url.php"';
            $data['seller_id'] = '"duk@doubihai.com"';
            $data['out_trade_no'] = '"'.$post['order_id'].'"';
            $data['subject'] = '"'.$post['pay_title'].'"';
            $data['body'] = '"'.$post['pay_content'].'"';
            $data['total_fee'] = '"'.$post['discount'].'"';
            $data['payment_type'] = '"'.$post['payment_type'].'"';
            $url = 'http://www.doubihai.com/Czf/signatures_url.php';
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_handle, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl_handle, CURLOPT_HEADER, 0);
            curl_setopt($curl_handle, CURLOPT_POST, true);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($curl_handle); 
            curl_close($ch);
            return $output;
	}
        public function check_order($params){
            $gift_order = M('gift_order')->where($params)->find();
            if(empty($gift_order)){
                throw new Exception('订单信息有误', -1);
            }
            return  $gift_order;
        }
        /**
        *@author sfy 2017-04-06
        *@param  直播预告
        *@return json
        */
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

        public function reward_tim_message($group_id){
            $gift = M('gift')->field('name,coins,pic_url,id')->where('id='.$this->gift_id)->find();
            $anchor = M('member')->field('nickname')->where('uid='.$this->to_uid)->find();
            $member = M('member')->field('nickname,uid,avatar')->where('uid='.$this->uid)->find();
            if(empty($gift) || empty($anchor) || empty($member)){
                throw new Exception('某项信息不存在', -3);
            }
            $msg = $member['nickname'].'送给主播一个'.$gift['name'];
            //礼物名字、礼物图片、礼物id、用户昵称nickname
            $param0 = array(
                'gift_name'=>$gift['name'],
                'gift_pic'=>$gift['pic_url'],
                'gift_id'=>$gift['id'],
                'nick_to'=>$anchor['nickname'],
                'nick_from'=>$member['nickname'],
                'nick_from_avatar'=>$member['avatar'],
                'sendMessageString'=>$msg,
                'messageType'=>9
                );
            foreach ($param0 as $k => $v) {
                if(!$v) $param0[$k] = '';        			
            }
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
        public function create_group($post_data,$operation){
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
            if($output->ErrorCode != 0){
                throw new Exception('发送消息失败', -6);
            }  
        }
        public function validate_apple_pay($receipt_data,$order_id){
            $coins_t = array();
            $gift_apple_buy = M('gift_apple_buy');
            // 验证参数
            if (strlen($receipt_data)<20){
                throw new Exception('参数有误', -1);
            }
            //验证订单 type=0 or type=2 充值淘气币
            $gift_exchange = M('gift_exchange')->field('total_coins')->where('uid='.$this->uid.' and (type=0 or type=2) ')->order('id desc')->limit(1)->find();
            if(empty($gift_exchange)){
                $total_coins = 0;
            }else{
                $total_coins = $gift_exchange['total_coins'];
            } 
            
            $apple_buy_data = $gift_apple_buy->field('combo_id,id,type')->where('order_id="'.$order_id.'" and uid='.$this->uid)->find();
            if(empty($apple_buy_data)){
                $coins_t['total_coins'] = $total_coins;
                return $coins_t;
            }else{
                $type = $apple_buy_data['type'];
            }
            
            $url = 'https://sandbox.itunes.apple.com/verifyReceipt';
            $normal_url = 'https://buy.itunes.apple.com/verifyReceipt';
            // 请求验证
            $data = $this->http_post_data($normal_url,json_encode(array('receipt-data' => $receipt_data)));
            // 判断是否购买成功
            if(strpos($data,'"status":21007') > 0){
                $data = $this->http_post_data($url,json_encode(array('receipt-data' => $receipt_data)));
            }
            $datatest = array('data'=>'ApplePayCallback'.$data,'type'=>'102','add'=>time());                
            M('test')->add($datatest);
            if(strpos($data,'"status":0') > 0){
                $gift_apple_buy->startTrans();
                $new_apple_buy_data['receipt'] = $receipt_data;
                $new_apple_buy_data['id'] = $apple_buy_data['id'];
                $new_apple_buy_data['update_time'] = time(); 
                $update_gift_apple = M('gift_apple_buy')->save($new_apple_buy_data);
                $datatest = array('data'=>'A'.$update_gift_apple.  json_encode($new_apple_buy_data),'type'=>'103','add'=>time());                
                M('test')->add($datatest);
                if($update_gift_apple){
                    if($type == 1 || $type == 0){
                        $gift_combo = M('gift_combo')->field('coins')->where('id='.$apple_buy_data['combo_id'])->find();
                        $gift_order_data['total_coins'] = $gift_combo['coins'] + $total_coins;
                        $gift_order_data['uid'] = $this->uid;
                        $gift_order_data['coins'] = $gift_combo['coins'];
                        $gift_order_data['type'] = 2;
                        $gift_order_data['record_id'] = $apple_buy_data['id'];
                        $gift_order_data['add_time'] = time();
                        $gift_exchange = M('gift_exchange')->add($gift_order_data);
                        if($gift_exchange){
                            $gift_apple_buy->commit();
                            $coins_t['total_coins'] = $gift_order_data['total_coins'];
                            return $coins_t;
                        }else{
                            throw new Exception('更新淘气币失败', -4);
                            $gift_apple_buy->rollback();
                        }
                    }else{
                        $gift_apple_buy->commit();
                        $coins_t['total_coins'] = $total_coins;
                        return $coins_t;
                    }
                }else{
                    throw new Exception('添加凭证失败', -3);
                    $gift_apple_buy->rollback();
                }
            }else{
                $datas = array('data'=>$data,'type'=>101,'add'=>time());                
                M('test')->add($datas);
                throw new Exception('支付验证失败 ', -2);
            }  
           
        }
        public function http_post_data($url, $data_string) {
            $curl_handle=curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_handle, CURLOPT_TIMEOUT, 60);
            curl_setopt($curl_handle, CURLOPT_HEADER, 0);
            curl_setopt($curl_handle, CURLOPT_POST, true);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
            $response_json = curl_exec($curl_handle);
            if ($response_json == false) {
                for ($i = 0;$i < 2;$i++) {
                    $response_json = curl_exec($curl_handle);
                    if ($response_json) {
                        break;
                    }
                }
            }

            if ($response_json == false) {
                return '';
            }
            //parent::saveLog($response_json);
            //$response =json_decode($response_json,true);print_r($response);exit();
            curl_close($curl_handle);
            return $response_json;
        }


        public function check_combo($combo_id,$type=1){
            //$combo_id = I('combo_id',0,'intval');
            if(!$combo_id){
                throw new Exception('参数有误',-1);
            }
            if($type == 1 || $type == 0){
                $combo = M('gift_combo')->where('id='.$combo_id.' and status=1')->find();
            }elseif($type == 2 || $type == 4){
                $combo = M('course')->where('id='.$combo_id.' and is_delete=0')->find();
            }elseif($type == 3){
                $combo = M('commodity')->where('id='.$combo_id)->find();
            }
            if(empty($combo)){
                throw new Exception('所选产品不存在',-2);
            }
            $this->combo_id = $combo_id;
        }
        public function create_order($type=0){
            $order_id = '';
            $is_exist = 0;
            $datetime = time();
            $rand = mt_rand(10000,99999);
            $order_id .= 'dbh'.$datetime.$rand.$this->uid;//$this->uid.$this->combo_id;
            $data['order_id'] = $order_id;
            $data['combo_id'] = $this->combo_id;
            $data['uid'] = $this->uid;
            $data['add_time'] = $datetime;  
            $data['update_time'] = 0;
            $data['type'] = $type;			
            //暂时不做订单验重
            if($type==0 || $type == 4){
                $result = M('gift_apple_buy')->add($data);
            }else{
                $data['trade_no'] = $order_id;
                if($type == 1){
                    $combo = M('gift_combo')->where('id='.$this->combo_id.' and status=1')->find();
                    $price = $combo['money'];
                }elseif($type == 2){
                    $combo = M('course')->where('id='.$this->combo_id.' and is_delete=0')->find();
                    $price = $combo['price'];
                }elseif($type == 3){
                    $combo = M('commodity')->where('id='.$this->combo_id)->find();
                    $price = $combo['price'];
                }
                $data['discount'] = $price;
                $result = M('gift_order')->add($data);
            }
            if(!$result){
                throw new Exception('订单生成失败', -1); 
            }
            return $order_id;
        }
        public function check_param_reward($post){
            $this->uid = data_isset($post['uid'],'intval',0);
            $this->to_uid = data_isset($post['to_uid'],'intval',0);
            $this->live_id = data_isset($post['live_id'],'intval',0);
            $this->gift_id = data_isset($post['gift_id'],'intval',0);
            
            if($this->uid == 0 || $this->to_uid ==0 || $this->live_id == 0 || $this->gift_id == 0){
                throw new Exception('参数有误', -1); 
            }
        }

        public function add_gift_record(){
            $data = array('uid'=>$this->uid,'to_uid'=>$this->to_uid,'live_id'=>$this->live_id,'gift_id'=>$this->gift_id,'reward_time'=>time());
            $this->ret_id = M('gift_record')->add($data);
            if(!$this->ret_id){
                throw new Exception('添加打赏记录失败', -2); 
            }
            return $this->ret_id;
        }

        public function add_gift_exchange(){
            $gift_exchange = M('gift_exchange');
            $gift_record = M('gift')->where('id='.$this->gift_id)->find();
            if(empty($gift_record)){
                throw new Exception('所打赏的礼物不存在', -6); 
            }else{
                $data['coins'] = $gift_record['coins'];
            } 
            $data['type'] = 0;
            $data['uid'] = $this->uid;
            //充值和打赏 用的淘气币;
            $record = $gift_exchange->field('total_coins')->where('uid='.$this->uid.' and (type=0 or type=2)')->order('add_time desc')->find();
            if(!empty($record)){
                if($record['total_coins']<$gift_record['coins']){
                    throw new Exception('您的淘气币不足', -5); 
                }
                $data['total_coins'] = $record['total_coins'] - $gift_record['coins'];         
            }else{
                throw new Exception('您的淘气币不足', -5);
            }
            
            $data['record_id'] = $this->ret_id;
            $data['add_time'] = time();
            $uid_gift_exchange = $gift_exchange->add($data);
            if(!$uid_gift_exchange){
                throw new Exception('打赏失败', -3); 
            }
            $data['type'] = 1;
            $data['uid'] = $this->to_uid;
            //受赏的是钻
            $record_to = $gift_exchange->field('total_coins')->where('uid='.$this->to_uid.' and type=1')->order('add_time desc')->find();
            if(!empty($record_to)){
                $data['total_coins'] = $record_to['total_coins'] + $gift_record['coins'];         
            }else{
                $data['total_coins'] = $gift_record['coins'];
            }

            $uid_gift_exchange = $gift_exchange->add($data);
            if(!$uid_gift_exchange){
                throw new Exception('受赏失败', -4); 
            }

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
        //微信预付款
        public function wx_unified_Order($post){
            //require_once('/mnt/xvdb1/virtualhost/doubihai/Czf/lib/WxPayApi.class.php');
            require_once('/var/www/html/taotong/Czf/lib/WxPayApi.class.php');
            $json = array();
            //生成预支付交易单的必选参数:
            $newPara = array();
            $wx_pay_unoder = new \WxPayUnifiedOrder();
            $wx_pay_unoder->SetAppid(\WxPayConfig::APPID);
            $wx_pay_unoder->SetMch_id(\WxPayConfig::MCHID);
            $wx_pay_unoder->SetDevice_info('WEB');
            $token_str = 'key=doubihaitime='.time();
            $token = md5($token_str);
            $nonce_str = $token;//\Home\Controller\HomeController::create_token(());
            $wx_pay_unoder->SetNonce_str($nonce_str);
            $wx_pay_unoder->SetBody($post['pay_content']);
            $out_trade_no = $post['order_id'];
            $wx_pay_unoder->SetOut_trade_no($out_trade_no);
            $ip = '122.233.203.17';//$_SERVER["REMOTE_ADDR"]
            $wx_pay_unoder->SetSpbill_create_ip($ip);
            $notify_url = 'http://192.168.1.139:8080/Czf/notify_url.php';
            $wx_pay_unoder->SetNotify_url($notify_url);
            $wx_pay_unoder->SetTrade_type('APP');
            $total_fee = intval($post['discount']);
            $wx_pay_unoder->SetTotal_fee($total_fee);
            $wx_pay_unoder->SetSign();
            //应用ID
            $newPara["appid"] = $wx_pay_unoder->GetAppid();        
            //商户号
            $newPara["mch_id"] = $wx_pay_unoder->GetMch_id();
            //设备号        
            $newPara["device_info"] = $wx_pay_unoder->GetDevice_info();
            //随机字符串,这里推荐使用函数生成
            $newPara["nonce_str"] = $wx_pay_unoder->GetNonce_str();
            //商品描述
            $newPara["body"] = $wx_pay_unoder->GetBody();
            //商户订单号,这里是商户自己的内部的订单号
            $newPara["out_trade_no"] = $wx_pay_unoder->GetOut_trade_no();
            //总金额
            $newPara["total_fee"] = $wx_pay_unoder->GetTotal_fee();
            //终端IP
            $newPara["spbill_create_ip"] = $wx_pay_unoder->GetSpbill_create_ip();
            //通知地址，注意，这里的url里面不要加参数
            $newPara["notify_url"] = $wx_pay_unoder->GetNotify_url();
            //交易类型
            $newPara["trade_type"] = $wx_pay_unoder->GetTrade_type();
            //第一次签名
            $newPara["sign"] = $wx_pay_unoder->GetSign();
            //把数组转化成xml格式
            $xmlData = $wx_pay_unoder->ToXml();
            //利用PHP的CURL包，将数据传给微信统一下单接口，返回正常的prepay_id
            $get_data = curl_data('https://api.mch.weixin.qq.com/pay/unifiedorder',$xmlData,'xml','weixin');
            //$get_data = $wx_pay_unoder->FromXml($get_data);
            //返回的结果进行判断。
            if($get_data['return_code'] == "SUCCESS" && $get_data['result_code'] == "SUCCESS"){
                //根据微信支付返回的结果进行二次签名
                //二次签名所需的随机字符串
                $token_str = 'key=doubihaiutime='.time();
                $token = md5($token_str);
                $nonce_str = $token;//\Home\Controller\HomeController::create_token(time());
                $newPara["nonce_str"] = $nonce_str;
                //二次签名所需的时间戳
                $newPara['timeStamp'] = time()."";
                //二次签名剩余参数的补充
                $secondSignArray = array(
                    "appid"=>$newPara['appid'],
                    "noncestr"=>$newPara['nonce_str'],
                    "package"=>"Sign=WXPay",
                    "prepayid"=>$get_data['prepay_id'],
                    "partnerid"=>$newPara['mch_id'],
                    "timestamp"=>$newPara['timeStamp'],
                );
                $json['datas'] = $secondSignArray;
                $json['ordersn'] = $newPara["out_trade_no"];
                $string_a = url_params($secondSignArray);
                $string_b = "$string_a&key=".\WxPayConfig::KEY;
                $json['datas']['sign'] = strtoupper(MD5($string_b));
                $json['message'] = "预支付完成";
                //预支付完成,在下方进行自己内部的业务逻辑
                //return json_encode($json);
            }else{
                $json['message'] = $get_data['return_msg'];
                throw new Exception($json['message'], -9); 
            }
            log_new($get_data,1);
            return $json;
            //return json_encode($json);
    }
}