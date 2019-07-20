<?php
/**
 * Created by PhpStorm.
 * User: Elvis Lee
 * Date: 2016/12/18
 * Time: 0:25
 */

namespace MultiPay;


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
        "ALI_APPID"                 =>      "2016081801766911",
        "ALI_PID"                   =>      "2088022393766628",
        // "ALI_PUBLIC_KEY"            =>      MULTIPAY_PATH."/certs/ali/",
        "ALI_PUBLIC_KEY"            =>      "/certs/ali/",
        // "ALI_PRIVATE_KEY"           =>      MULTIPAY_PATH."/certs/ali/",
        "ALI_PRIVATE_KEY"           =>      "/certs/ali/",
        // "ALI_ALIPAY_PUBLIC_KEY"     =>      MULTIPAY_PATH."/certs/ali/",
        "ALI_ALIPAY_PUBLIC_KEY"     =>      "/certs/ali/",
        "ALI_NOTIFY_URL"            =>      "",
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