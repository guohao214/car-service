<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.thinkphp.cn>
// +----------------------------------------------------------------------

/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */
return array(

    // 预先加载的标签库
    'TAGLIB_PRE_LOAD'     =>    'OT\\TagLib\\Article,OT\\TagLib\\Think',
        
    /* 主题设置 */
    'DEFAULT_THEME' =>  'default',  // 默认模板主题名称

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'onethink_', // 缓存前缀
    'DATA_CACHE_TYPE'   => 'File', // 数据缓存类型
    'URL_MODEL'            => 1, //URL模式

    /* 文件上传相关配置 */
    'DOWNLOAD_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Download/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //下载模型上传配置（文件上传类配置）

    /* 编辑器图片上传相关配置 */
    'EDITOR_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Editor/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
    ),

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'doubihai_home', //session前缀
    'COOKIE_PREFIX'  => 'doubihai_home_', // Cookie前缀 避免冲突

    /**
     * 附件相关配置
     * 附件是规划在插件中的，所以附件的配置暂时写到这里
     * 后期会移动到数据库进行管理
     */
    'ATTACHMENT_DEFAULT' => array(
        'is_upload'     => true,
        'allow_type'    => '0,1,2', //允许的附件类型 (0-目录，1-外链，2-文件)
        'driver'        => 'Local', //上传驱动
        'driver_config' => null, //驱动配置
    ), //附件默认配置

    'ATTACHMENT_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Attachment/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //附件上传配置（文件上传类配置）

    // 服务器路径配置
    'HOST_URL'  =>  'http://www.doubihai.com',

    'wx'  => [
        'url' => 'https://api.weixin.qq.com/sns/jscode2session',
        'appid' => 'wx2c6383b19f336b63',
        'secret' => '8a3dbfd78945c4dc69f5c447b1b78e48',
        'grant_type' => 'authorization_code'
    ],

    //支付宝配置参数
    //支付宝配置参数开始
    /* *
     * 配置文件
     * 版本：1.0
     * 日期：2016-06-06
     * 说明：
     * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
     * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
    */
    'alipay_config'=>array(
 
        // //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        // //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
        // $alipay_config['partner']       = '';

        // //商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
        // $alipay_config['private_key']   = '';

        // //支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
        // $alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';

        // //异步通知接口
        // $alipay_config['service']= 'mobile.securitypay.pay';
        // //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        // //签名方式 不需修改
        // $alipay_config['sign_type']    = strtoupper('RSA');

        // //字符编码格式 目前支持 gbk 或 utf-8
        // $alipay_config['input_charset']= strtolower('utf-8');

        // //ca证书路径地址，用于curl中ssl校验
        // //请保证cacert.pem文件在当前文件夹目录中
        // $alipay_config['cacert']    = getcwd().'/cacert.pem';

        // //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        // $alipay_config['transport']    = 'http';

        //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        // //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
        'partner'           => '2088421728461432',
        // 'partner' =>'2088102169171549',     //这里是你在成功申请支付宝接口后获取到的PID；

        //商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
       // 'private_key'       => 'D:\wamp\www\bipai-web\ThinkPHP\Library\Vendor\Alipay\app_private_key.pem',
       'private_key'       => 'MIICXQIBAAKBgQDLWiibqoJknpTSupbdkCCVdO8uzuAAxSshicUvBqQLwAXXegPwh4YQ7RURZxk8W+DOjnVhfmRUhdwyddPMZa+NEnW/8oCNuuZ2F2o8ASiL4lBC/rJwLEFvG3/CPRLiTwe1VMJNpPh5XPxL6ugTgaGnFR84osUEc8//xlifIS0fwQIDAQABAoGBAJY4AvmDixGDaFMHTX1dBAUEPdBpOGa5QHRlDsn/cN3ROz+DPjfIFYyZZs/VRnolMTvFVwbvVHRv8ktAaXZ7w6L0yXx/QgAr9de0JOho+4J+fU/URygHmgVZXR0huOvmU59wExqeR0Np/+lPcpahH8Yu6xZ9W42S/TQZMhzD1BE1AkEA6WzOwsnzONf3x+vgngir3lqEui5XHL4C71jogBBuqB1Yb+fF7cbphxE9YdTrPJfPKZj+m8/wx668UouOjev8RwJBAN8EzN2XL62XXu1il8kTDK6L6oS3OBSQH8r9mPaT9YqFtydUVknF5dn45LZJT0OCFPg+1AGwv28toEBzIn2fb7cCQCEhln9DVshcrwirTChiJrLaujgK18Z2mcgLIIT80BgAgkrv5MIJF6BaLBI0vGbPTVIkKw9GhnBxNr2onUU7l4UCQQDDKmg94viOaVFhXE6IYGtQtJDe45foJtgrxBIAdysBtlK50ExS8yRaxD0iaLU81rGTLShK8moU2VDMFfWxCb2xAkBqR1AAQ+VvGzYtqc6OXctQHbVBP9FhbSm/P4e3su3GZF2WSAMhTsWLJHoXXik3QQSkxSkI4lMec9FGAYRPHp6a',
       // 
// 'key'=>'9t***********ie',           //这里是你在成功申请支付宝接口后获取到的Key

        //支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
        'alipay_public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB',

        //异步通知接口
        'service'           => 'mobile.securitypay.pay',
        //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        //签名方式 不需修改
        'sign_type'         => 'RSA2',
        // 'sign_type'=>strtoupper('MD5'),

        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset'     => strtolower('utf-8'),
        // 'input_charset'=> strtolower('utf-8'),

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        // 'cacert'            => getcwd().'/cacert.pem',
        'cacert'            => getcwd().'/ThinkPHP/Library/Vendor/Alipay/cacert.pem',
        // 'cacert'=> getcwd().'\\cacert.pem',

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'transport'         => 'http',
        // 'transport'=> 'http',
        'notify_url'        => 'http://www.doubihai.com/inApp.php',
      ),
     //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；
    //支付宝配置参数结束

);
