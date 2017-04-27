<?php
namespace MyApp\Tasks;

use MyApp\Models\DvbAutoConfirm;
use MyApp\Models\DvbCity;
use MyApp\Models\DvbHotel;
use MyApp\Models\DvbOrder;
use MyApp\Models\DvbProvince;
use MyApp\Services\MessageService;

class AutoconfirmTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $total = 0;
        //自动接单功能
        $orderM         = new DvbOrder();
        $hotelM         = new DvbHotel();
        $autoConfirmM   = new DvbAutoConfirm();
        $provinceM      = new DvbProvince();
        $cityM          = new DvbCity();
        $messageService = new MessageService();
        $hotels         = $hotelM->select(["id", "name", "province", "city", "address", "telphone"], ["isAutoOrder" => 1]);
        if (!empty($hotels)) {
            foreach ($hotels as $k => $hotel) {
                $hotel['provinceName'] = $provinceM->get("ProvinceName", ["ProvinceCode" => $hotel["province"]]);
                $hotel['cityName']     = $cityM->get("CityName", ["CityCode" => $hotel["city"]]);
                $autoConfirms          = $autoConfirmM->select(["hotelId", "roomStyleId", "autoStartTime", "autoEndTime"], ["AND" => ["hotelId" => $hotel["id"], "status" => 1]]);
                if (!empty($autoConfirms)) {
                    foreach ($autoConfirms as $autoConfirm) {
                        //查询酒店自动接单列表中对应房型的待处理订单
                        $where                                                       = [];
                        $where["AND"]["{$orderM->getOrderRoomSource()}.hotelId"]     = $autoConfirm["hotelId"];
                        $where["AND"]["{$orderM->getOrderRoomSource()}.roomStyleId"] = $autoConfirm["roomStyleId"];
                        $where["AND"]["{$orderM->getSource()}.orderStatus"]          = DvbOrder::OS_UNCONFIRMED;//条件：待确认
                        $where["AND"]["{$orderM->getSource()}.payStatus"]            = DvbOrder::PS_PAYED;//条件：已付款
                        $where["AND"]["{$orderM->getSource()}.payTime[>=]"]          = $autoConfirm["autoStartTime"];
                        $where["AND"]["{$orderM->getSource()}.payTime[<=]"]          = $autoConfirm["autoEndTime"];
                        $orders                                                      = $orderM->fetchOrders("*", $where);
                        if (!empty($orders)) {
                            foreach ($orders as $order) {
                                if ($order["orderStatus"] == DvbOrder::OS_UNCONFIRMED) {
                                    $affect = $orderM->update(['orderStatus' => DvbOrder::OS_CONFIRMED, 'updateTime' => time(), 'updateUserId' => 0], ['orderId' => $order['orderId']]);
                                    if ($affect) {
                                        $data = ["hotelName"    => $hotel['name'], "userName" => $order["consigneesText"], "inTime" => $order['checkInTime'] . "至" . $order['checkOutTime'], "roomStyleName" => $order['roomStyleName'], "roomsNumber" => $order['roomsNumber'], "nightNumber" => $order['stayDays'],
                                                 "hotelAddress" => $hotel['provinceName'] . $hotel['cityName'] . $hotel['address'], "hotelPhone" => $hotel['telphone']];
                                        $messageService->sendMessage(5, $order['telephone'], $data, $order['orderNo'], $hotel['id']);
                                        $total++;
                                        $logName     = '系统确认接单';
                                        $orderStatus = DvbOrder::OS_CONFIRMED;
                                        $orderM->setLog($logName, $order['orderId'], $orderStatus, 0, '系统');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        exit ("totalOrder :{$total} had been autoConfirm");
    }

}
