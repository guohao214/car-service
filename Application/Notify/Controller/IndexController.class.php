<?php
namespace Notify\Controller;
use Think\Controller;
class IndexController extends Controller {

	public function _initialize()
    {
		Vendor("MultiPay.multipay");
		Vendor("MultiPay.class.wepay.WePay");
    }

    public function index()
    {
    	$this->alipayNotifyUrl();
    }

    public function wepayNotifyUrl()
    {
		// $postXml = $GLOBALS["HTTP_RAW_POST_DATA"];	//接受通知参数；
		$postXml = file_get_contents("php://input");	//接受通知参数；

		file_put_contents('./filenameTestingwePayNotifyUrl.txt','Hello world');

		if (empty($postXml))
		{
		    echo "FAIL";

			file_put_contents('./filenamewePayNotifyUrl.txt', json_encode($postXml));
			file_put_contents('./filenameTestingwePayNotifyUrl.txt','Hello world');

		    exit;
		}

		$xmlTransfer = new \MultiPay\classes\wepay\XmlTransfer();
		$response = $xmlTransfer->xml2Array($postXml);

		// $this->renderSuccess('测试成功',['data' => $response]);

		if (empty($response))
		{
		    echo "FAIL";

			file_put_contents('./filenamewePayNotifyUrl.txt', json_encode($response));
			file_put_contents('./filenameTestingwePayNotifyUrl.txt','Hello world');

		    exit;
		}
		else
		{
		    if (!empty($response['return_code']))
		    {
		        if ($response['return_code'] == 'FAIL')
		        {
		            echo "FAIL";

					file_put_contents('./filenamewePayNotifyUrl.txt', json_encode($response));
					file_put_contents('./filenameTestingwePayNotifyUrl.txt','Hello world');

		            exit;
		        }
		        $encpt = new \MultiPay\classes\wepay\WeEncryption();
		        $data = array(
		            "appid"				=>	$response["appid"],
		            "mch_id"			=>	$response["mch_id"],
		            "nonce_str"			=>	$response["nonce_str"],
		            "result_code"		=>	$response["result_code"],
		            "openid"			=>	$response["openid"],
		            "trade_type"		=>	$response["trade_type"],
		            "bank_type"			=>	$response["bank_type"],
		            "total_fee"			=>	$response["total_fee"],
		            "cash_fee"			=>	$response["cash_fee"],
		            "transaction_id"	=>	$response["transaction_id"],
		            "out_trade_no"		=>	$response["out_trade_no"],
		            "time_end"			=>	$response["time_end"]
		        );
		        $sign = $encpt->signature($data);
		        if ($sign = $response["sign"]) {
		            $reply = array(
		                "return_code"   =>  "SUCCESS",
		                "return_msg"    =>  "OK"
		            );
		            $reply = $xmlTransfer->array2XML($reply);
		            echo $reply;

					file_put_contents('./filenamewePayNotifyUrl.txt', $reply);
					file_put_contents('./filenameTestingwePayNotifyUrl.txt',json_encode($response));

		            exit;
		        }
		        else
		        {
		            echo "FAIL";

					file_put_contents('./filenamewePayNotifyUrl.txt', json_encode($sign));
					file_put_contents('./filenameTestingwePayNotifyUrl.txt','Hello world');

		            exit;
		        }
		    }


			file_put_contents('./filenamewePayNotifyUrl.txt', json_encode($sign));
			file_put_contents('./filenameTestingwePayNotifyUrl.txt','Hello world');
		}
    }

    public function alipayNotifyUrl()
    {
		/* *
		 * 功能：支付宝服务器异步通知页面
		 * 版本：1.0
		 * 日期：2016-06-06
		 * 说明：
		 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
		 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


		 *************************页面功能说明*************************
		 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
		 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
		 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
		 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
				 */

		// require_once("alipay.config.php");
		$alipay_config = C('alipay_config');
		// require_once("lib/alipay_notify.class.php");
		Vendor('Alipay.Notify');
		// require_once("lib/alipay_rsa.function.php");
		Vendor('Alipay.RsaFunction');
		// require_once("lib/alipay_core.function.php");
		Vendor("Alipay.CoreFunction");

		file_put_contents('./filenameAlipay.txt', json_encode($_POST));

		// $notifyData = '{"discount":"0.00","payment_type":"1","subject":"\u821e\u8e48\u521d\u7ea7","trade_no":"2017032321001004260240779820","buyer_email":"15001965903","gmt_create":"2017-03-23 17:18:17","notify_type":"trade_status_sync","quantity":"1","out_trade_no":"MTgjMSMxOCMxNDkwMjYwMzEx","seller_id":"2088421728461432","notify_time":"2017-03-23 17:18:18","body":"\u821e\u8e48\u521d\u7ea7","trade_status":"TRADE_SUCCESS","is_total_fee_adjust":"N","total_fee":"0.01","gmt_payment":"2017-03-23 17:18:18","seller_email":"duk@doubihai.com","price":"0.01","buyer_id":"2088412454503262","notify_id":"8c825171684d60f7aed55b64a97ed6bi0a","use_coupon":"N","sign_type":"RSA","sign":"dWI08IkKWnmXD8srvtoISpzGsx4SHMM0gkiQwCJoM3djZjNETMzUkvmldzajnwegE6p\/hT38srduZz7zyMMPhfLZc3\/LthwiKNMJ5BQ8hC3yaCdjDiulyABoelaCGuAZ4O2wvd1YyD9j312FZT1za949Ja5LaSN\/vu1nvkGrmrg="}';

		// $_POST = json_decode($notifyData,true);

		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		if($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
		{
			if(!$alipayNotify->getSignVeryfy($_POST, $_POST['sign'])) {//使用支付宝公钥验签
				
				//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
	    		//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
				//商户订单号
				$out_trade_no = $_POST['out_trade_no'];

				//支付宝交易号
				$trade_no = $_POST['trade_no'];

				//交易状态
				$trade_status = $_POST['trade_status'];

				file_put_contents('./filenameAlipayS.txt', json_encode($_POST));

	    		if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//如果有做过处理，不执行商户的业务程序
				//注意：
				//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
				//请务必判断请求时的out_trade_no、total_fee、seller_id与通知时获取的out_trade_no、total_fee、seller_id为一致的
	    		}
	    		else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
					//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
					//注意：
					//付款完成后，支付宝系统发送该交易状态通知
					//请务必判断请求时的out_trade_no、total_fee、seller_id与通知时获取的out_trade_no、total_fee、seller_id为一致的

					file_put_contents('./filenameAlipayTS.txt', json_encode($_POST));

					$notifyData = $_POST;

					// 订单状态 1 完成 0 未完成
					$orderStatus = $this->checkCoursePayOrderStatus($notifyData);
					// 如果订单不是已完成订单 则更新相关数据
					if(!$orderStatus)
					{
						$this->updateCoursePayOrderData($notifyData);
					}

					// // 测试Coding开始
					// // out_trade_no 由notifyData中获取
					// // $out_trade_no = 'MTMjMSMxOCMxNDkwMjUwMzEw';
					// $out_trade_no = $notifyData['out_trade_no'];
					// $outTradeNoData = $this->parseOutTradeNo($out_trade_no);

					// $OrderCourse = M('OrderCourse');

					// $whereUpdateOrderData = array(
					// 								'id'			=>	$outTradeNoData['id'],
					// 								'uid'			=>	$outTradeNoData['uid'],
					// 								'course_id'		=>	$outTradeNoData['course_id'],
					// 								'create_time'	=>	$outTradeNoData['create_time']
					// 						);

					// $orderCourseData = $OrderCourse->where($whereUpdateOrderData)->find();
					
					// // $this->renderSuccess('解析成功',array('Status' => $orderStatus));
					// $this->renderSuccess('解析成功',$orderCourseData);
					// // 测试Coding结束
				
	    		}
				//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
				echo "success";		//请不要修改或删除
			}
			else //验证签名失败
			{

				file_put_contents('./filenameAlipayF.txt', json_encode($alipay_config));
				echo "sign fail";
			}
		}
		else //验证是否来自支付宝的通知失败
		{
			echo "response fail";
		}
    }

	// 检查订单状态
	private function checkCoursePayOrderStatus($notifyData)
	{
		// out_trade_no 由notifyData中获取
		// $out_trade_no = 'MTMjMSMxOCMxNDkwMjUwMzEw';
		$out_trade_no = $notifyData['out_trade_no'];
		$outTradeNoData = $this->parseOutTradeNo($out_trade_no);

		$OrderCourse = M('OrderCourse');

		$whereUpdateOrderData = array(
										'id'			=>	$outTradeNoData['id'],
										'uid'			=>	$outTradeNoData['uid'],
										'course_id'		=>	$outTradeNoData['course_id'],
										'create_time'	=>	$outTradeNoData['create_time']
								);

		$orderStatus = $OrderCourse->where($whereUpdateOrderData)->getField('status');

		return $orderStatus;
	}

	// 解析 out_trade_no 数据
	private function parseOutTradeNo($out_trade_no = 'MTMjMSMxOCMxNDkwMjUwMzEw')
	{
		$outTradeNoData = array();

		$outTradeNoBase64DeStr = base64_decode($out_trade_no);
		$outTradeNoDataTemp = explode('#',$outTradeNoBase64DeStr);

		$outTradeNoData = array(
								'id'			=>	$outTradeNoDataTemp['0'],
								'uid'			=>	$outTradeNoDataTemp['1'],
								'course_id'		=>	$outTradeNoDataTemp['2'],
								'create_time'	=>	$outTradeNoDataTemp['3'],
							);

		return $outTradeNoData;
	}

	// 更新订单状态和与此订单相关的信息
	private function updateCoursePayOrderData($notifyData)
	{
		// out_trade_no 由notifyData中获取
		// $out_trade_no = 'MTMjMSMxOCMxNDkwMjUwMzEw';
		$out_trade_no = $notifyData['out_trade_no'];

		$outTradeNoData = $this->parseOutTradeNo($out_trade_no);
		$updateOrderData = array(
									'trade_no'		=>	$notifyData['trade_no'],
									'pay_time'		=>	$notifyData['notify_time'],
									'user_account'	=>	$notifyData['buyer_email'],
									'status'		=>	1
							);

		$whereUpdateOrderData = array(
										'id'			=>	$outTradeNoData['id'],
										'uid'			=>	$outTradeNoData['uid'],
										'course_id'		=>	$outTradeNoData['course_id'],
										'create_time'	=>	$outTradeNoData['create_time'],
										'course_price'	=>	$notifyData['total_fee']
								);

		$OrderCourse = M('OrderCourse');

		$updateResult = $OrderCourse->where($whereUpdateOrderData)->save($updateOrderData);

		if($updateResult)
		{
			$Course = M('Course');

			$Course->where(array('id' => $outTradeNoData['course_id']))->setInc('sales');
		}
	}
}