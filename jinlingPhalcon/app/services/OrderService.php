<?php
namespace MyApp\Services;

use MyApp\Models\DvbOrder;
use MyApp\Models\DvbOrderRoom;
use MyApp\Models\DvbOrderRoomDetail;
use MyApp\Services\BaseService;

/**
 * 订单服务类
 * 
 *
 */
class OrderService extends BaseService
{
    public  $jsonRes = ['code' => 1001,'message' => '操作失败','content' => []];

	/**
 	 * 根据订单id获取订单详情
 	 * @param [int] $orderId 订单id
 	 * @return [type] $info
  	 */
    public function getOrderInfoById($orderId)
    {	
    	$orderModel = new DvbOrder;
    	$info = $orderModel->get('*',['orderId' => $orderId]);
    	return $info;
    }
    /**
 	 * 根据订单id获取订单详情
 	 * @param [int] $orderId 订单id
 	 * @return [type] $info
  	 */
    public function getOrderRoomInfoById($orderId)
    {	
    	$where = [];
    	$where['AND']['order.orderId'] = $orderId;
    	$join                               = ["[>]dvb_order_room(room)" => ["order.orderId" => "orderId"]];
    	$columns = ["order.orderId","order.orderNo","order.userId","order.userName","order.orderStatus","order.payStatus","order.consignees","room.orderRoomId","room.hotelId","room.roomStyleId","room.roomStyleName","room.roomsNumber","room.checkInTime","room.checkOutTime","room.stayDays","room.selectType"];
		$info = $this->db->get("dvb_orders(order)", $join, $columns, $where);
    	
    	return $info;
    }
    /**
     * 根据订单id获取订单详情
     * @param [int] $orderId 订单id
     * @return [type] $info
     */
    public function getOrderRoomInfoByOrderNo($orderNo)
    {   
        $where = [];
        $where['AND']['order.orderNo'] = $orderNo;
        $join                               = ["[>]dvb_order_room(room)" => ["order.orderId" => "orderId"]];
        $columns = ["order.orderId","order.orderNo","order.outTradeNo","order.userId","order.userName","order.telephone","order.orderStatus","order.payId","order.payStatus","order.consignees","order.orderAmount","order.closeTime","room.orderRoomId","room.hotelId","room.roomStyleId","room.roomStyleName","room.roomsNumber","room.checkInTime","room.checkOutTime","room.stayDays","room.selectType"];
        $info = $this->db->get("dvb_orders(order)", $join, $columns, $where);
        
        return $info;
    }

    /**
     * 根据订单No获取订单详情
     * @param [int] $orderId 订单id
     * @return [type] $info
     */
    public function getOrderInfoByOrderNo($orderNo,$select = "*")
    {
        $orderModel = new DvbOrder;
        $info = $orderModel->get($select,['orderNo' => $orderNo]);
        return $info;
    }
    /**
     * 获取订单房间列表
     */
    public function getOrderRoomList($orderId,$isSelected = -1,$sortKey = 'useDate', $sort = 'ASC')
    {
        $orderRoomDetail = new DvbOrderRoomDetail;
        $where = [];
        $where['AND']['orderId'] = $orderId;
        $isSelected != -1 && $where['AND']['isSelected'] = $isSelected;
        //$where['ORDER'][$sortKey] = $sort;
        $list = $orderRoomDetail->select("*",$where);
        return $list;
    }


    /**
     * 组装插入订单表数据
     * @param $orderNo
     * @param $hotelId
     * @param $userId
     * @param $userName
     * @param $telPhone
     * @param $consignees
     * @param $telephone
     * @param $remark
     * @param $payId
     * @param $payName
     * @param $productAmount
     * @param $ip
     * @param $payCloseTimeSet
     * @return array
     */
    public function getInsertOrderData($orderNo, $hotelId, $userId, $userName, $telPhone, $consignees, $telephone, $remark, $payId, $payName, $productAmount, $ip, $payCloseTimeSet)
    {
        $insertOrderData                  = [];
        $insertOrderData['orderNo']       = $orderNo;
        $insertOrderData['orderType']     = 1;
        $insertOrderData['hotelId']       = $hotelId;
        $insertOrderData['userId']        = $userId;
        $insertOrderData['userName']      = $userName ? $userName : $telPhone;
        $insertOrderData['orderStatus']   = DvbOrder::OS_WAITINGPAY;
        $insertOrderData['payStatus']     = DvbOrder::PS_UNPAYED;
        $insertOrderData['consignees']    = serialize($consignees);
        $insertOrderData['telephone']     = $telephone;
        $insertOrderData['remark']        = $remark ? $remark : '';
        $insertOrderData['payId']         = $payId;
        $insertOrderData['payName']       = $payName;
        $insertOrderData['productAmount'] = $productAmount;
        $insertOrderData['orderAmount']   = $productAmount;
        $insertOrderData['createTime']    = time();
        $insertOrderData['orderIp']       = $ip;
        $insertOrderData['updateTime']    = time();
        $insertOrderData['updateUserId']  = $userId;
        $insertOrderData['closeTime']     = time() + $payCloseTimeSet;
        return $insertOrderData;
    }

    /**
     * 组装插入订单产品表数据
     * @param $hotelId
     * @param $userId
     * @param $roomStyleId
     * @param $roomStyleName
     * @param $roomsTypeAttr
     * @param $roomsNumber
     * @param $productAmount
     * @param $arrivalTime
     * @param $checkInTime
     * @param $checkOutTime
     * @param $stayDays
     * @param $selectType
     * @return array
     */
    public function getInsertOrderRoomData($hotelId, $userId, $roomStyleId, $roomStyleName, $roomsTypeAttr, $roomsNumber, $productAmount, $arrivalTime, $checkInTime, $checkOutTime, $stayDays, $selectType)
    {
        $insertOrderRoomData                  = [];
        $insertOrderRoomData['hotelId']       = $hotelId;
        $insertOrderRoomData['userId']        = $userId;
        $insertOrderRoomData['roomStyleId']   = $roomStyleId;
        $insertOrderRoomData['roomStyleName'] = $roomStyleName;
        if (!empty($roomsTypeAttr)) {
            $insertOrderRoomData['roomsTypeAttr'] = serialize($roomsTypeAttr);
        }
        $insertOrderRoomData['roomsNumber']  = $roomsNumber;
        $insertOrderRoomData['sellPrice']    = $productAmount;
        $insertOrderRoomData['arrivalTime']  = $arrivalTime;
        $insertOrderRoomData['checkInTime']  = $checkInTime;
        $insertOrderRoomData['checkOutTime'] = $checkOutTime;
        $insertOrderRoomData['stayDays']     = $stayDays;
        $insertOrderRoomData['selectType']   = $selectType;
        return $insertOrderRoomData;
    }

    /**
     * 组装插入订单操作记录表数据
     * @param $userId
     * @param $telPhone
     * @return array
     */
    public function getInsertOrderLogData($userId, $telPhone)
    {
        $insertOrderLogData                = [];
        $insertOrderLogData['logName']     = "用户下单";
        $insertOrderLogData['userId']      = $userId;
        $insertOrderLogData['userName']    = $telPhone;//$userName ? $userName : $telPhone 测试说产品需要改成这样，改成电话号码
        $insertOrderLogData['orderStatus'] = DvbOrder::OS_WAITINGPAY;
        $insertOrderLogData['logTime']     = time();
        return $insertOrderLogData;
    }

    /**
     * 组装插入订单详情表数据
     * @param $orderRoomId
     * @param $orderId
     * @param $sellPrice
     * @param $useDate
     * @return array
     */
    public function getInsertOrderRoomDetailData($orderRoomId, $orderId, $sellPrice, $useDate)
    {
        $insertOrderRoomDetailData                = [];
        $insertOrderRoomDetailData['orderRoomId'] = $orderRoomId;
        $insertOrderRoomDetailData['orderId']     = $orderId;
        $insertOrderRoomDetailData['sellPrice']   = $sellPrice;
        $insertOrderRoomDetailData['useDate']     = $useDate;
        $insertOrderRoomDetailData['isSelected']  = 0;
        return $insertOrderRoomDetailData;
    }

    /**
     * 根据订单号查询订单信息和订单产品信息
     * @param $orderNo
     * @return array|bool
     */
    public function getOrderDetailByOrderNo($orderNo)
    {
        if (empty($orderNo)) return false;
        $orderM     = new DvbOrder();
        $orderRoomM = new DvbOrderRoom();
        $order      = $orderM->get("*", ["orderNo" => $orderNo]);
        $orderRoom  = $orderRoomM->get("*", ["orderId" => $order['orderId']]);
        $orderInfo  = array_merge($order, $orderRoom);
        return $orderInfo;
    }

    /**
     * 组装查询订单总数条件的数组
     * @param $userId
     * @param $hotelId
     * @param $orderStatus
     * @return array
     * @author Jacent
     * @date 2016.07.19
     */
    public function countWhere($userId, $hotelId, $orderStatus)
    {
        $where = [
            "AND" => [
                "userId"      => $userId,
                "hotelId"     => $hotelId,
                "orderStatus" => $orderStatus
            ]
        ];
        return $where;
    }

}