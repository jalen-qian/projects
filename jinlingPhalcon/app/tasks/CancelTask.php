<?php
namespace MyApp\Tasks;

use MyApp\Models\DvbHotel;
use MyApp\Models\DvbOrder;
use MyApp\Services\MessageService;

class CancelTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $total = 0;
        //定时取消未付款的订单
        $orderM                                             = new DvbOrder();
        $hotelM                                             = new DvbHotel();
        $messageService                                     = new MessageService();
        $where                                              = [];
        $where["AND"]["{$orderM->getSource()}.orderStatus"] = DvbOrder::OS_WAITINGPAY;
        $where["AND"]["{$orderM->getSource()}.payStatus"]   = DvbOrder::PS_UNPAYED;
        $orders                                             = $orderM->fetchOrders("*", $where);
        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $order) {
                if (time() > $order['closeTime'] && $order['closeTime'] > 0) {
                    $result = $orderM->stockRollback($order['orderId']);
                    $total++;
                    if ($result) {
                        $logName     = '取消订单';
                        $orderStatus = DvbOrder::OS_CANCELED;
                        $orderM->setLog($logName, $order['orderId'], $orderStatus, 0, '系统');
                        //发短信通知
                        $hotel = $hotelM->get("*", ["id" => $order["hotelId"]]);
                        $data  = ["hotelName" => $hotel['name'], "orderNum" => $order['orderNo'], "weixinName" => $hotel['weixinName'], "hotelPhone" => $hotel['telphone']];
                        $messageService->sendMessage(9,$order['telephone'], $data, $order['orderNo'], $hotel['id']);
                    }
                }
            }
        }
        exit ("totalOrder :{$total} had been canceled");
    }

}
