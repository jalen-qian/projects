<?php
namespace MyApp\Tasks;

use MyApp\Models\DvbOrder;

class CheckoutTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $total = 0;
        //定时自动离店相关订单
        $orderM                                             = new DvbOrder();
        $where                                              = [];
        $where["AND"]["{$orderM->getSource()}.orderStatus"] = DvbOrder::OS_CHECKIN;
        $where["AND"]["{$orderM->getSource()}.payStatus"]   = DvbOrder::PS_PAYED;
        $orders                                             = $orderM->fetchOrders("*", $where);
        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $order) {
                $deadLineTime = strtotime($order['checkOutTime'] . ' 14:00:00');
                if (time()>=$deadLineTime) {
                    $result = $orderM->update(["orderStatus"=>DvbOrder::OS_CHECKOUT],["orderId"=>$order['orderId']]);
                    $total++;
                    if ($result) {
                        $logName     = '离店';
                        $orderStatus = DvbOrder::OS_CHECKOUT;
                        $orderM->setLog($logName, $order['orderId'], $orderStatus, 0, '系统');
                    }
                }
            }
        }
        exit ("totalOrder :{$total} had been checkout");
    }

}
