<?php
/**
 * Created by PhpStorm.
 * User: Elvis Lee
 * Date: 2016/12/19
 * Time: 13:15
 */
abstract class Encryption
{
    abstract protected function signature($data);

    /**
     * 随机字符串生成函数
     * @return string   生成的随机字符串
     */
    public function nonceStr() {
        $code = "";
        for ($i = 0; $i > 10; $i++) {
            $code .= mt_rand(10000);
        }
        $nonceStrTemp = md5($code);
        $nonce_str = mb_substr($nonceStrTemp, 5, 37);
        return $nonce_str;
    }
}
class AliEncryption extends Encryption
{
    /**
     * 订单信息签名函数
     * @param $data
     * @return string
     */
    public function signature($data)
    {
        //读取私钥文件
        $rsaPriKeyFile = Config::getConf("ALI_PRIVATE_KEY");
        $priKey = file_get_contents($rsaPriKeyFile) or die("密钥文件读取失败！");
        $res = openssl_get_privatekey($priKey);
        ($res) or die("您使用的私钥格式错误，请检查RSA私钥配置");
        // 参数签名
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }
 /**格式化公钥 
    * $pubKey PKCS#1格式的公钥串 
    * return pem格式公钥， 可以保存为.pem文件 
    */  
   private function formatPubKey($pubKey) {  
       $fKey = "-----BEGIN PUBLIC KEY-----\n";  
       $len = strlen($pubKey);  
       for($i = 0; $i < $len; ) {  
           $fKey = $fKey . substr($pubKey, $i, 64) . "\n";  
           $i += 64;  
       }  
       $fKey .= "-----END PUBLIC KEY-----";  
       return $fKey;  
   } 
    /**
     * 验证签名函数
     * @param $beVerify
     * @param $sign
     * @return bool
     */
    public function verify($beVerify, $sign) {
        //读取公钥文件
        //$rsaPubKeyFile = Config::getConf("ALI_ALIPAY_PUBLIC_KEY");
        //$pubKey = file_get_contents($rsaPubKeyFile) or die("读取公钥文件失败！");
        //$res = openssl_get_publickey($pubKey);
        //($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        //调用openssl内置方法验签，返回bool值
        $res = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnWyjX7dnUXTL1zyDwzy0X/W+6DweCj1PjTPpcfj0I2XstKAldY7o1oyv4NAxRe1w0pczgJBk5loWOo2tbM0UmU435ofDrEdE37iU9TPZGZGy5QBrw9TmYZXtYqGIt743yQxOZ5ZCCOHUkczsosMyzg/E5Sl+NKc7IH0vgmTnlWwK4rWytKHxqiCkvCpXWjgdem8C+zpthcX5Hz73+HNUqffpynWpEsl5SMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnWyjX7dnUXTL1zyDwzy0X/W+6DweCj1PjTPpcfj0I2XstKAldY7o1oyv4NAxRe1w0pczgJBk5loWOo2tbM0UmU435ofDrEdE37iU9TPZGZGy5QBrw9TmYZXtYqGIt743yQxOZ5ZCCOHUkczsosMyzg/E5Sl+NKc7IH0vgmTnlWwK4rWytKHxqiCkvCpXWjgdem8C+zpthcX5Hz73+HNUqffpynWpEsl5SnlIA3HuHy+rd2OJ28UkAXI+USvFJJVM1Bt6z7d0cZtre5F+Xf+gprKd7lz6rKoNQJab0pmC5ZrVSFLRn0hDnSY2WRWdVhv3pzkICQg9iXND8srIPvyUewIDAQABnlIA3HuHy+rd2OJ28UkAXI+USvFJJVM1Bt6z7d0cZtre5F+Xf+gprKd7lz6rKoNQJab0pmC5ZrVSFLRn0hDnSY2WRWdVhv3pzkICQg9iXND8srIPvyUewIDAQAB";
        $result = (bool)openssl_verify($beVerify, base64_decode($sign), $res);
        openssl_free_key($res);	//释放资源
        return $result;
        
    }
}
class Config
{
    /**
     * @var array   配置参数数组
     */
    
    private static $config = array(

        // 微信支付参数配置
        "WECHAT_APPID"              =>      "wx6558ff5813795185",
        "WECHAT_MCHID"              =>      "1435663902",
        "WECHAT_KEY"                =>      "SCBKYwggSiAgEAAoIBAQC0P6ojxMM5gy",
        "WECHAT_TRADE_TYPE"         =>      "APP",
        "WECHAT_NOTIFY_URL"         =>      "http://doubihai.com/notify.php/CoursePay/wePayNotifyUrl",
        // "WECHAT_SSLCERT_PATH"       =>      MULTIPAY_PATH."/certs/wechat/...",
        "WECHAT_SSLCERT_PATH"       =>      "/certs/wechat/...",
        // "WECHAT_SSLKEY_PATH"        =>      MULTIPAY_PATH."/certs/wechat/...",
        "WECHAT_SSLKEY_PATH"        =>      "/certs/wechat/...",

        // 支付宝支付参数配置
        "ALI_APPID"                 =>      "2017021305648938",
        "ALI_PID"                   =>      "2088421728461432",
        // "ALI_PUBLIC_KEY"            =>      MULTIPAY_PATH."/certs/ali/",
        "ALI_PUBLIC_KEY"            =>    '/mnt/xvdb1/virtualhost/doubihai/ThinkPHP/Library/Vendor/Alipay/app_public_key.pem',
        //// "ALI_PRIVATE_KEY"           =>      MULTIPAY_PATH."/certs/ali/",
        "ALI_PRIVATE_KEY"           =>   "/mnt/xvdb1/virtualhost/doubihai/ThinkPHP/Library/Vendor/Alipay/app_private_key.pem",
        //"ALI_PRIVATE_KEY"           =>   "D:\wamp\www\bipai-web/ThinkPHP/Library/Vendor/Alipay/app_private_key.pem",
       // "ALI_ALIPAY_PUBLIC_KEY"     =>     "D:\wamp\www\bipai-web/ThinkPHP/Library/Vendor/Alipay/alipay_public_key.pem",
        "ALI_ALIPAY_PUBLIC_KEY"     =>      '/mnt/xvdb1/virtualhost/doubihai/ThinkPHP/Library/Vendor/Alipay/app_public_key.txt',
        "ALI_NOTIFY_URL"            =>      "http://www.doubihai.com/inApp.php",
    );

    /**
     * 获取参数配置
     * @param $key
     * @return mixed|void
     */
    public static function getConf($key)
    {
        if (is_string($key))
        {
            return self::$config[$key];
        }
        return "";
    }
}
abstract class Pay
{
    protected static $instance;

    abstract protected function request($data);
    abstract protected function serializeParams($data);
}

class Alipay extends Pay
{
    /**
     * 发起支付请求
     * @param $data
     * @return mixed
     */
    public function request($data)
    {
        // 序列化签名数据
        $data = $this->serializeParams($data);
        // 请求参数按照key=value&key=value方式拼接的未签名原始字符串
        $stringToBeSigned = implode("&", $data);//echo $stringToBeSigned;exit;
        // 获取支付参数签名
        $encpt = new AliEncryption();
        $sign = $encpt->signature($stringToBeSigned);
        // 最后对请求字符串的所有一级value（biz_content作为一个value）进行encode
        $formatArr = $this->withUrlEncode($data);
         //按照key=value&key=value方式拼接签名字符串
        //$signStr = implode("&", $formatArr);
        $sign = $formatArr."&sign=".urlencode($sign);
        return $sign;
    }

    /**
     * 序列化原始参数
     * @param $data
     * @return string
     */
    protected function serializeParams($data)
    {
        // 添加必要参数
        $params = array(
            "app_id"        =>  Config::getConf("ALI_APPID"),
            "method"        =>  "alipay.trade.app.pay",
            "sign_type"     =>  "RSA2",
            "version"       =>  "1.0",
            "timestamp"     =>  date("Y-m-d H:i:s"),
            "charset"       =>  "utf-8",
            "notify_url"    =>  Config::getConf("ALI_NOTIFY_URL"),
        );
        $data = array_merge($data, $params);
        ksort($data);   // 将参数按照自然序排列
        $filterArr = array();
        foreach ($data as $k => $v) {
            if (false === empty($v) && "sign" != $k) {
                if ("biz_content" == $k) {
                    $v = json_encode($v);
                }
                // 转换成目标字符集
                $v = mb_convert_encoding($v, 'UTF-8');
                array_push($filterArr, $k."=".urlencode($v));
            }
        }
        unset ($k, $v);
        // 将参数使用&连接符连接为字符串
        return $filterArr;
    }

    /**
     * 对请求字符串的所有一级value（biz_content作为一个value）进行encode
     * @param $data
     * @return array
     */
    private function withUrlEncode($data)
    {
        $arr = array();
        ksort($data);
        $i = 0;
        $stringToBeSigned = '';
        foreach ($data as $k => $v) {
            $postCharset = "UTF-8";
            // 转换成目标字符集
            $v = $this->characet($v, $postCharset);
            if ($i == 0) {
                    $stringToBeSigned .=  "$v";
            } else {
                    $stringToBeSigned .= "&"  . "$v";
            }
            $i++;
                
        }

        return $stringToBeSigned;
    }
/**
	 * 转换字符集编码
	 * @param $data
	 * @param $targetCharset
	 * @return string
	 */
	private function characet($data, $targetCharset) {
		
		if (!empty($data)) {
			$fileType = mb_detect_encoding($str, "UTF-8, GBK") == 'UTF-8' ? 'UTF-8' : 'GBK';;
			if (strcasecmp($fileType, $targetCharset) != 0) {
				$data = mb_convert_encoding($data, $targetCharset, $fileType);
				//				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
			}
		}


		return $data;
	}
    /**
     * 验证签名
     * @param $data
     * @return bool
     */
    public function verify($data) {
        // 获取sign
        $sign = $data["sign"];
        // 剔除sign、sign_type字段
        unset($data["sign"]);
        unset($data["sign_type"]);
        // 处理通知参数
        $data = $this->withUrlEncode($data);
        $beSign = implode("&", $data);
        // 验证签名
        $encpt = new AliEncryption();
        $result = $encpt->verify($beSign, $sign);
        return $result;
    }
}