<?php
header("Content-type: text/html; charset=utf-8");
define('PATH', str_replace('\\', DIRECTORY_SEPARATOR, dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('WECHAT_DOMAIN','http://devinterface.rrkd.cn/WeChat/test_wx/index.php');
//define('WECHAT_DOMAIN','http://devinterface.rrkd.cn/WeChat/');
//include_once("conf/WxPay.pub.config.php");
include_once("lib/WxPay.Config.php");
include_once("lib/JsSdk.php");
include_once("lib/CommonUtilPub.php");
include_once("lib/SDKRuntimeException.php");
include_once("lib/WxpayClientPub.php");
include_once("lib/UnifiedOrderPub.php");
error_reporting(E_ALL);
// 获取微信用户的openId，相信在接微信支付的时候，已经能够获取到openId了
//$openId = "oczZouEKDQvL-tX1VIfN3-FK9RDw";
$appId = WxPayConfig::APPID;
$appSecret = WxPayConfig::APPSECRET;
// 获取jssdk相关参数
$jssdk = new JsSdk($appId, $appSecret);
$signPackage = $jssdk->GetSignPackage();
$timeStamp = $signPackage['timestamp'];
$nonceStr = $signPackage['nonceStr'];
$out_trade_no = $appId.$timeStamp;
// 获取prepay_id
// 具体参数设置可以看文档http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_1
$unifiedOrder = new UnifiedOrderPub();
//$unifiedOrder->setParameter("openid",$openId);//用户openId
$unifiedOrder->setParameter("body", "贡献一分钱");//商品描述，文档里写着不能超过32个字符，否则会报错，经过实际测试，临界点大概在128左右，稳妥点最好按照文档，不要超过32个字符
$unifiedOrder->setParameter("out_trade_no", $out_trade_no);//商户订单号
$unifiedOrder->setParameter("total_fee", "1");//总金额,单位为分
$unifiedOrder->setParameter("notify_url",WxPayConfig::NOTIFY_URL);//通知地址
$unifiedOrder->setParameter("trade_type","APP");//交易类型
$unifiedOrder->setParameter("nonce_str", $nonceStr);//随机字符串
//非必填参数，商户可根据实际情况选填
//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
//$unifiedOrder->setParameter("device_info","XXXX");//设备号
//$unifiedOrder->setParameter("attach","XXXX");//附加数据
//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
//$unifiedOrder->setParameter("openid","XXXX");//用户标识
//$unifiedOrder->setParameter("product_id","XXXX");//商品ID
$prepayId = $unifiedOrder->getPrepayId();


// echo "<br>prepayId===".$prepayId."</br>";
// 计算paySign
$payPackage = array(
    "appId" => WxPayConfig::APPID,
    "nonceStr" => $nonceStr,
    "package" => "prepay_id=" . $prepayId,
    "signType" => "MD5",
    "timestamp" => $timeStamp
);
$paySign = $unifiedOrder->getSign($payPackage);
$payPackage['paySign'] = $paySign;
 echo "<pre>";
 print_R($signPackage);


?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
      <meta content="yes" name="apple-mobile-web-app-capable" />
      <meta content="telephone=no,email=no" name="format-detection" />
    <title>微信支付接入</title>
    <link rel="stylesheet" href="http://biang.io/dist/css/bootstrap.css" />
  </head>
  <body>
    <div>
      <h3 id="menu-pay">微信支付接口</h3>
      <span class="desc">发起一个微信支付请求</span>
      <button class="btn btn_primary" id="chooseWXPay">chooseWXPay</button>
    </div>

  </body>
  <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script>
  alert(location.href.split('#')[0]);
  wx.config({
      debug: true, // 调试开关
      appId: '<?php echo $signPackage["appId"];?>',
      timestamp: <?php echo $signPackage["timestamp"];?>,
      nonceStr: '<?php echo $signPackage["nonceStr"];?>',
      signature: '<?php echo $signPackage["signature"];?>',
      jsApiList: [
        'checkJsApi',
        'chooseWXPay'
      ]
  });
  
  wx.ready(function () {
    document.querySelector('#chooseWXPay').onclick = function () {
      wx.chooseWXPay({
          timestamp: <?php echo $payPackage["timestamp"];?>,
          nonceStr: '<?php echo $payPackage["nonceStr"];?>',
          package: '<?php echo  $payPackage['package'];?>',
          signType: '<?php echo $payPackage["signType"];?>', // 注意：新版支付接口使用 MD5 加密
          paySign: '<?php echo $payPackage["paySign"];?>',
          success: function () {
            alert('支付成功');
            // Add Your Code Here If You Need
          }
      });
    };
  });
  wx.error(function (res) {
    console.log(res);
    alert('验证失败:' + res.errMsg);
    // Add Your Code Here If You Need
  });
  </script>
</html>
