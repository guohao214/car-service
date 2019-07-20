<?php
return array(
	//'配置项'=>'配置值'

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
        'private_key'       => 'MIICXgIBAAKBgQDCJZCzTW44HB23usEloVehBXjEtESRXQrRSQTDinSK4rxrAlyzA2eGwikrE47aT+xEEQMpVmmpxXSW6rpzrAarn8l0XfUZxjFkKfs7AN9J9DwTmYCYhUkz1vchJ1pcexNTdvPlfn7UbmRuBzB7Enx8+srisKMKxJLFvMjHrgAKcQIDAQABAoGAMo8r1rXkbTJNPuqgoBcbRfCx2bAEZ0KurX/xgKbO4NhZLxjnYSsSy9JGfFRUkF/d7H/trdc2HyMv0JmCQttaxOR3nEsKUxPJbaSu5hAOIewJAWzwAnSS0BkcA3ivJ3JMpSxFC8+Wpf2jt/nuiSLpX90KGj25JzJyag+Q53MQAAECQQD0NDMO/7MmRMRFSG8rP7kHPsK+K6m9XoHE+EqPdm48x4QUtg08OHPBo4G4E6PBXMj+Xe7W3cv8Y+GsSjjA+VABAkEAy4ZdLuFE/mKH5GPqpQGemltydPt0Hpxumk+l4Pl7tro3QgyGRqXfQMZrMmFrMToPmRkZl7DcoT/wHAeYa9O6cQJBAIfYleKtGZKbRHeqILV1YE+IeTri/Syr6xKQcKG35wEGmBIRZ/FtEe/RLjMhMcI6BFGEHX4Hqhb/1SVLsKCg0AECQQDGIW/li3fXDaStOnfbWtBEBHJQX9qTUkZ6Ar/BXB6LrIzOx9KQRDoqnP8OdLgdnCBDMqQvgAXJFK1zHBHOJriBAkEAvo/goKh0h7d1ebuMgH4X2feOyb+51uvhyeo0cwJVsHh8c3oP3gyNFZmAQiWTVEcCT9sb2mYILY6vMLHXEKdcpA==',
        // 'key'=>'9t***********ie',           //这里是你在成功申请支付宝接口后获取到的Key

        //支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
        // 'alipay_public_key'=> 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
        'alipay_public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB',

        //异步通知接口
        'service'           => 'mobile.securitypay.pay',
        //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        //签名方式 不需修改
        'sign_type'         => strtoupper('RSA'),
        // 'sign_type'=>strtoupper('MD5'),

        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset'     => strtolower('utf-8'),
        // 'input_charset'=> strtolower('utf-8'),

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        'cacert'            => getcwd().'/ThinkPHP/Library/Vendor/Alipay/cacert.pem',
        // 'cacert'=> getcwd().'\\cacert.pem',

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'transport'         => 'http',
        // 'transport'=> 'http',
      ),
     //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；
    //支付宝配置参数结束
);