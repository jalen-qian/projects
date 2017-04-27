<?php
namespace MyApp\Services;

use MyApp\Library\Utilities;
use MyApp\Models\DvbOrder;
use MyApp\Models\DvbPay;
use MyApp\Models\DvbPayLog;
use MyApp\Services\BaseService;
use MyApp\Services\OrderService;
use Vendor\WechatPayment\WechatPayment;

/**
 * 支付服务类
 * @author chenym
 * @since 2016-07-21
 */
class PaymentService extends BaseService
{

    public $jsonRes = ['code' => 1001, 'message' => '操作失败', 'content' => []];

    /**
     * M版微信支付
     */
    public function weixinPayM($payId, $orderNo, $payPrice, $userId)
    {
        //订单检查
        $orderService = new OrderService;
        $orderM       = new DvbOrder();

        $orderInfo = $orderService->getOrderRoomInfoByOrderNo($orderNo);
        if ($orderInfo["payStatus"] == 1) {
            $payFailedUrl = $this->config['params']['webUrl']['front'] . 'front/pay/paySuccess?hotelId=' . $orderInfo["hotelId"] . '&orderNo=' . $orderNo;
            Header("Location: $payFailedUrl");
        }
        $orderRes = $this->checkOrderInfo($orderInfo, $orderInfo["orderAmount"], $orderInfo["userId"], $orderInfo["payId"]);
        if ($orderRes['code'] != 1000) {

            $payFailedUrl = $this->config['params']['webUrl']['front'] . 'front/pay/payFailed?hotelId=' . $orderInfo["hotelId"] . '&orderNo=' . $orderNo;
            Header("Location: $payFailedUrl");
            exit();
        }

        //$payConfig = ['appid' => 'wx5eb372d8827ddfc7', 'mchid' => (string) "1218237701", 'key' => '982ba6c963a144fed939a1883c1c7f0c', 'appsecret' => 'c1de582f026353990a970a50f6824023']; //fuwuhao

        $payConfig = $this->getPayInfo($orderInfo['hotelId'], $orderInfo['payId']);
        $payment   = new WechatPayment($payConfig['appId'], $payConfig['mchId'], $payConfig['appKey'], $payConfig['appSecret']);

        //获取openId
        //$openId = $payment->getOpenId();
        $openId    = $payment->getOpenidNet($this->config["params"]["webUrl"]["api"] . "api/Payment/pay?hotelId=" . $orderInfo["hotelId"] . "&orderNo=" . $orderNo);
        $utilities = new Utilities;
        //$openId = '9IM5GqZHnmQJhhKmwHKTcxt5MC7hNTBzjwttvXEce2U=';
        $openId = $utilities->apiSTD3Decrypt($openId, 'cookieDesc');

        $this->logger->info('weixin openId: ' . $openId);
        $this->logger->info('weixin orderInfo: ' . json_encode($orderInfo));

        $orderNoNew = $orderNo . "HH" . time();
        $payBody    = mb_substr($orderInfo["roomStyleName"], 0, 30, 'UTF-8') . " " . $orderInfo["roomsNumber"] . "间" . " " . $orderInfo["stayDays"] . "晚";
        $notifyUrl  = $this->config['params']['webUrl']['api'] . 'api/Payment/weixinNotify';
        $prepayId   = $payment->getPrepayId($payBody, $orderNoNew, $orderInfo['orderAmount'] * 100, $notifyUrl, $openId);
        if (!$prepayId) {
            $payFailedUrl = $this->config['params']['webUrl']['front'] . 'front/pay/payFailed?hotelId=' . $orderInfo["hotelId"] . '&orderNo=' . $orderNo . '&from=php';
            Header("Location: $payFailedUrl");
            exit();
        }
        $orderM->update(["outTradeNo" => $orderNoNew], ["orderId" => $orderInfo['orderId']]);
        $this->logger->info('weixin prepay_id: ' . $prepayId);
        $package = json_encode($payment->getPackage($prepayId));
        $this->logger->info('weixin package: ' . $package);

        return $this->payReturnHtml($package, $orderNo, $orderInfo["hotelId"]);

    }

    /**
     * 获取预支付返回的html
     */
    public function payReturnHtml($package, $orderNo, $hotelId)
    {

        $htmlTemplate = "<html>
                                       <head>
                                            <meta http-equiv='content-type' content='text/html;charset=utf-8'/>
                                            <meta name='viewport' content='width=device-width, initial-scale=1'/>
                                            <title></title>
                                        </head>
                                        <body>
                                        <script type='text/javascript'>
                                                function jsApiCall()
                                                {
                                                    WeixinJSBridge.invoke(
                                                    'getBrandWCPayRequest',
                                                    {jsApiParameters},
                                                    function (res)
                                                    {
                                                        //WeixinJSBridge.log(res.err_msg);
                                                        if(res.err_msg == 'get_brand_wcpay_request:ok'){
                                                            window.location.href='{pay_success_url}';
                                                        }else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                                                                window.location.href='{pay_fail_url}';
                                                        } else if (res.err_msg == 'get_brand_wcpay_request:fail') {
                                                                window.location.href='{pay_fail_url}';
                                                        } else {
                                                            window.location.href='{pay_fail_url}';
                                                        }
                                                    }
                                                    );
                                                }
                                                if (typeof WeixinJSBridge == 'undefined')
                                                {
                                                    if (document.addEventListener)
                                                    {
                                                        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                                                    }
                                                    else if (document.attachEvent)
                                                    {
                                                        document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                                                        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                                                    }
                                                }
                                                else
                                                {
                                                    jsApiCall();
                                                }
                                        </script>
                                        </body>
                                        </html>";

        $paySuccessUrl = $this->config['params']['webUrl']['front'] . "front/pay/paySuccess?hotelId=$hotelId&orderNo=" . $orderNo;
        $payFailedUrl  = $this->config['params']['webUrl']['front'] . "front/pay/payFailed??hotelId=$hotelId&orderNo=" . $orderNo;
        $htmlTemplate  = str_replace("{jsApiParameters}", $package, $htmlTemplate);
        $htmlTemplate  = str_replace("{pay_success_url}", $paySuccessUrl, $htmlTemplate);
        $htmlTemplate  = str_replace("{pay_fail_url}", $payFailedUrl, $htmlTemplate);

        return $htmlTemplate;

    }

    /**
     * 检查订单
     */
    public function checkOrderInfo($orderInfo, $payPrice, $userId, $payId)
    {

        if (!$orderInfo) {
            $this->jsonRes['message'] = "订单不存在!";
            return $this->jsonRes;
        }
        if ($orderInfo['payStatus'] != 0) {
            $this->jsonRes['message'] = "订单已付款!";
            return $this->jsonRes;
        }
        $this->logger->info('weixin closeTime: ' . $orderInfo['closeTime'] . '-' . time());
        if ($orderInfo['closeTime'] < time()) {
            $this->jsonRes['message'] = "订单已过期!";
            return $this->jsonRes;
        }
        if ($orderInfo['orderAmount'] != $payPrice) {
            $this->jsonRes['message'] = "付款金额不对!";
            return $this->jsonRes;
        }
        $this->jsonRes['code']    = 1000;
        $this->jsonRes['message'] = "订单校验通过!";
        return $this->jsonRes;
    }

    /**
     * 获取支付信息
     */
    public function getPayInfo($hotelId, $payId)
    {
        $payModel                = new DvbPay;
        $where                   = [];
        $where["AND"]["hotelId"] = $hotelId;
        $where["AND"]["payId"]   = $payId;
        $info                    = $payModel->get('*', $where);
        return $info;
    }

    /**
     * 添加日志记录
     */
    public function addPayLog($payId, $orderId, $orderNo, $payOrderNo, $orderAmount)
    {
        $data                = [];
        $data['payId']       = $payId;
        $data['orderId']     = $orderId;
        $data['orderNo']     = $orderNo;
        $data['payOrderNo']  = $payOrderNo;
        $data['orderAmount'] = $orderAmount;

        $payLog = new MyApp\Models\DvbPayLog;
        $res    = $payLog->insert($data);
        return $res;
    }

    /**
     * 微信支付回调处理
     */
    public function weixinNotify($result)
    {

        $this->logger->info('weixin test: ' . serialize($result));
        $resContent = $this->xml2array($result);

        if ($resContent['result_code'] != "SUCCESS") {
            $this->logger->info('weixin Notify: ' . json_decode($result));
            exit("支付回调非法!");
        }

        $outTradeNo = explode("HH", $resContent["out_trade_no"]);
        //订单检查
        $orderService = new OrderService;
        $orderInfo    = $orderService->getOrderRoomInfoByOrderNo($outTradeNo[0]);

        //获取支付信息
        $payConfig = $this->getPayInfo($orderInfo['hotelId'], $orderInfo['payId']);
        $payment   = new WechatPayment($payConfig['appId'], $payConfig['mchId'], $payConfig['appKey'], $payConfig['appSecret']);

        $payment->log('weixin orderInfo:' . json_encode($orderInfo), 'pay');

        if ($orderInfo['payStatus'] > 0) {
            $payment->log('weixin yizhifu', 'pay');
            $payment->responseBack("SUCCESS", "OK");
            exit();
        }

        $payment->log('weixin res: ' . json_encode($resContent), 'pay');

        //去掉不需要签名的参数
        unset($resContent["result_code"], $resContent["return_code"]);
        if ($payment->validate($resContent)) {
            $payment->log("回调签名不正确", 'pay');
            $payment->responseBack("FAIL", "SIGN INCORRECT");
            exit();
        }
        //查询订单是否支付成功
        if (!$payment->queryOrder($resContent["out_trade_no"])) {
            $payment->log("weixin pay:ORDER NO PAY", 'pay');
            $payment->responseBack("FAIL", "ORDER NO PAY");
            exit();
        }
        //更新订单支付状态

        $result = $this->updateOrderPayStatus($orderInfo, $resContent['transaction_id'], $resContent["out_trade_no"]);
        if ($result) {
            $payment->log("weixin pay:ORDER PAY SUCCESS", 'pay');
            $payment->responseBack("SUCCESS", "OK");
        } else {
            $payment->log("weixin pay:ORDER UPDATE FAIL", 'pay');
            $payment->responseBack("FAIL", "ORDER UPDATE FAIL");
        }
        exit();
    }

    /**
     * 更新订单支付状态
     */
    public function updateOrderPayStatus($orderInfo, $transactionId, $outTradeNo)
    {
        //修改选房数据，使用事务
        $result = $this->db->action(function ($database) use ($orderInfo, $transactionId, $outTradeNo) {
            $orderModel           = new DvbOrder;
            $payLog               = new DvbPayLog;
            $nowTime              = time();
            $data1                = $data2                = [];
            $data1["payStatus"]   = 1;
            $data1["orderStatus"] = 2;
            $data1["payTime"]     = $nowTime;
            $res1                 = $orderModel->update($data1, ["orderId" => $orderInfo["orderId"]]);

            $data2['payId']       = $orderInfo["payId"];
            $data2['orderId']     = $orderInfo["orderId"];
            $data2['orderNo']     = $orderInfo["orderNo"];
            $data2['outTradeNo']  = $outTradeNo;
            $data2['payOrderNo']  = $transactionId;
            $data2['orderAmount'] = $orderInfo["orderAmount"];
            $data2["payTime"]     = $nowTime;
            $data2["isPaid"]      = 1;

            $res2 = $payLog->insert($data2);
            if ($res1 && $res2) {
                return true;
            } else {
                return false;
            }
        });
        return $result;
    }

    public function xml2array($xml)
    {
        $array = array();
        foreach ((array) simplexml_load_string($xml) as $k => $v) {
            $array[$k] = (string) $v;
        }

        return $array;
    }

    public function payRefund($orderInfo)
    {
        $payConfig = $this->getPayInfo($orderInfo['hotelId'], $orderInfo['payId']);
        $payment   = new WechatPayment($payConfig['appId'], $payConfig['mchId'], $payConfig['appKey'], $payConfig['appSecret']);
        $result    = $payment->payRefund($orderInfo);
        return $result;
    }
}
