<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="edge">
<title>逗比孩官网-比拍-APP_比拍官网_比拍APP下载_比拍android版_比拍IOS版_比拍视频</title>
<meta name="keywords" content="比拍官网,比拍APP下载,对嘴视频,配音,快乐大本营,小咖秀,秒拍,美拍,短视频" />
<meta name="description" content="儿童对嘴配音视频社区 宝宝小咖秀场 艺术学习比赛直播平台，同时比拍还支持视频同步分享到微博、微信朋友圈、QQ和更多好友分享你的视频" />

<link href="/Public/static/web/uikit.almost-flat.min.css"  rel="stylesheet" type="text/css"/>
<link href="/Public/static/web/custom.css"  rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="/Public/static/jquery-2.0.3.min.js"></script>
<script type="text/javascript" src="/Public/static/web/uikit-2.21.0.min.js" ></script>
<script type="text/javascript" src="/Public/static/web/npage.js" ></script>
<script>
	var agent = "ios";
	var is_ios = "1";
	sUserAgent = navigator.userAgent.toLowerCase();   
	bIsIpad = sUserAgent.match(/ipad/i) == "ipad";     
	bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";    
	bIsAndroid = sUserAgent.match(/android/i) == "android";
	function is_weixin(){
	    var ua = navigator.userAgent.toLowerCase();  
	    if(ua.match(/MicroMessenger/i)=="micromessenger") {  
	        return true;  
	    } else {  
	        return false;  
	    }  
	}
	function download(){
	    if(is_weixin()){
	  		window.location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=com.yixia.xiaokaxiu";
	    }else{
	    		if(bIsAndroid){
	    			downLoadByType(1);
	  		}else{
	  			downLoadByType(0);
	  		}
	    }
	}
	function downLoadByType(type){
		if(type==0){
		     window.location.href='https://itunes.apple.com/cn/app/xiao-ka-xiu/id993071546?l=zh&ls=1&mt=8';
		 }else{
			window.location.href='http://m.xiaokaxiu.com/download/xiaokaxiu.php';
		}
     }
	
	var _hmt = _hmt || [];
	(function() {
	  var hm = document.createElement("script");
	  hm.src = "//hm.baidu.com/hm.js?f59cd30a931d909d523e7269cb297ec7";
	  var s = document.getElementsByTagName("script")[0]; 
	  s.parentNode.insertBefore(hm, s);
	})();
</script>
</head>
<body>
<div class="index_banner">
      <div class="index_main">
        <div class="index_top">
        <div class="index_logo">
          <a class="index_logo_a" href="http://doubihai.com" target="_parent" title="比拍">
          <img src="/Public/static/web/index_doubihai.png" border="0" alt="比拍"/>
          </a>
		  <div class="description">人小鬼大 一拍成名</div>
        </div>
        <div class="index_download">
          <img src="/Public/static/web/index_qrcode.png" width="200px">
          <div class="index_download_a">
            <!--<a href="" class="iphone" target="_blank" title="比拍IOS下载"></a>--> 
            <!-- <a href="javascript:downLoadByType(1);" class="android" title="比拍Android下载"></a>--> 
          </div>
        </div>
        </div>
    </div>
</div>
<!--右侧浮动-->
<!-- <div class="fudong">
	<div class="zcewm">
		<img src="http://xkx.static.xiaoka.tv/img/www/fd_tc.png" usemap="#Map"
			border="0" alt="下载比拍" />
		<map name="Map" id="Map">
			<area shape="rect" coords="39,84,174,127"
				href="javascript:downLoadByType(0);" title="下载比拍IOS" />
			<area shape="rect" coords="38,140,179,182"
				href="javascript:downLoadByType(1);" title="下载比拍Android" />
		</map>
	</div>
	<div class="yctb">
		<div class="backtop">
			<a href="#" title="比拍"></a>
		</div>
		<div class="ewm">
			<img src="http://xkx.static.xiaoka.tv/img/www/fd_ewm.png" alt="下载比拍" />
		</div>
	</div>
</div> -->
<!-- 
<div class="index_main">
	<div class="index_main1">
		<p class="index_main1_1">恶搞无罪 + 搞怪有理</p>
		<p class="index_main1_2">带你飙戏带你飞</p>
	</div>
	<div class="index_main2">
		<p class="index_main2_1">对嘴、合演、原创</p>
		<p class="index_main2_2">发现每个人的精彩，展现你的演技</p>
		<p class="index_main2_3">让恶搞精神传承给全世界</p>
	</div>
	<div class="index_main3">
		<p class="index_main3_1">各种配音、MV</p>
		<p class="index_main3_2">上万声音可以供你玩耍，表现不了你的 逗逼创意？用原创啊</p>
	</div>
	<div class="index_main4">
		<p class="index_main4_1">明星、达人、小逗逼</p>
		<p class="index_main4_2">明星大咖又如何，表现自己的演技</p>
		<p class="index_main4_3">铸造完美小逗逼</p>
	</div>
	<div class="index_main5">
		<p class="index_main5_1">高能高效引爆朋友圈</p>
		<p class="index_main5_2">将你演绎的心情分享给朋友吧，快乐的正能量 会渲染每个人</p>
	</div>
	<div class="index_main6">

	</div>
</div>
 -->
<!--右侧浮动结束-->
<div class="footer">
	浙ICP备16021739-1号 杭州逗比嗨文化创意有限公司Copyright © doubihai All rights reserved.<br />
	<a href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=33010802005557">
	<p style="float: center;  margin: 0px 0px 0px 5px; color: #4f4f67;">浙公网安备33010802005557号</p>
	</a>
</div>
</body>
</html>