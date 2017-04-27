<?php
namespace MyApp\Services;

use MyApp\Models\DvbOrder;
use MyApp\Models\DvbOrderRoomChoice;
use MyApp\Models\DvbOrderRoomDetail;
use MyApp\Models\DvbRoomFloor;
use MyApp\Models\DvbRoomNumber;
use MyApp\Models\DvbRoomStyle;
use MyApp\Services\BaseService;
use MyApp\Services\OrderService;

/**
 * 选房服务类
 * @author chenym
 *
 */
class SelectRoomService extends BaseService
{
    public $jsonRes        = ['code' => 1001, 'message' => '操作失败', 'content' => []];
    private $roomOperation = ["addRoom" => "安排房间", "updateRoom" => "修改房间安排", "cancelRoom" => "取消房间安排"];
    /**
     * 获取房间交集、差集
     * @param  [type] $roomNumNameArr [房间编号数组]
     * @param  [int] $hotelId   [酒店id]
     * @param  [int] $roomStyleId     [房型id]
     * @param  [int] $floorId     [楼层id]
     * @return [type] $result     [数组]
     */

    public function getRoomCollections($roomNumNameArr, $list)
    {
        $result = ['addArr' => [], 'deleteArr' => []];
        if (empty($roomNumNameArr)) {
            return $result;
        }
        $roomNumberModel = new DvbRoomNumber;

        if (empty($list)) {
            $result['addArr'] = $roomNumNameArr;
            return $result;
        }

        $roomNumArr = [];
        foreach ($list as $key => $value) {
            $roomNumArr[] = $value['roomNumName'];
        }
        $addArr    = $deleteArr    = [];
        $addArr    = array_diff($roomNumNameArr, $roomNumArr);
        $deleteArr = array_diff($roomNumArr, $roomNumNameArr);

        $result['addArr']    = $addArr;
        $result['deleteArr'] = $deleteArr;

        return $result;
    }
    /**
     * 获取房间号列表
     * @param  [int] $hotelId   [酒店id]
     * @param  [int] $floorId     [楼层id]
     * @param  [array] $styleList [房型列表数组]
     * @return [type] $floorListArr     [数组]
     */
    public function getRoomNumberList($hotelId, $floorId, $styleList)
    {

        $dvbRoomNumber           = new DvbRoomNumber;
        $where                   = $floorListArr                   = [];
        $where['AND']['hotelId'] = $hotelId;
        $where['AND']['floorId'] = $floorId;
        $where['AND']['isDelete'] = 0;

        $flist     = $dvbRoomNumber->select('*', $where);
        $floorList = [];
        if ($flist) {
            foreach ($flist as $key => $value) {
                $floorList[$value['roomStyleId']][] = $value;
            }
        }

        if ($floorList) {
            foreach ($floorList as $key1 => $val1) {
                $roomNumNames = $roomNumId = [];
                foreach ($val1 as $key2 => $val2) {
                    $roomNumNames[] = $val2['roomNumName'];
                    $roomNumId[]    = $val2['roomNumId'];
                    if ($key2 == 0) {
                        $floorListArr[$key1] = ['hotelId' => $val2['hotelId'], 'roomStyleId' => $val2['roomStyleId'], 'floorId' => $val2['floorId'], 'roomStyleName' => $styleList[$val2['roomStyleId']]['roomStyleName']];
                    }
                }
                $floorListArr[$key1]['roomNumNameArr'] = $roomNumNames;
                $floorListArr[$key1]['roomNumNames']   = $roomNumNames ? implode(';', $roomNumNames) : '';
                $floorListArr[$key1]['roomNumIdArr']   = $roomNumNames;
            }
        }

        return $floorListArr;
    }

    /**
     * 获取酒店楼层列表
     * @param  [int] $hotelId   [酒店id]
     * @return [type] $floorList     [楼层数组]
     */
    public function hotelRoomFloorList($hotelId, $select = '*',$statusIs = 0)
    {
        $roomFloor                   = new DvbRoomFloor;
        $where                       = [];
        $where["AND"]["hotelId"]     = $hotelId;
        $statusIs && $where["AND"]["statusIs"]    = 1;
        $where["ORDER"]["floorName"] = "ASC";
        $list                        = $roomFloor->select($select, $where);
        $floorList                   = [];
        foreach ($list as $key => $value) {
            $floorList[$value['floorId']] = $value;
        }
        return $floorList;
    }
    /**
     * 获取酒店房型列表
     * @param  [int] $hotelId   [酒店id]
     * @return [type] $styleList     [楼层数组]
     */
    public function hotelRoomStyleList($hotelId, $select = '*',$choiceIs=0)
    {
        $roomStyle = new DvbRoomStyle;
        $where["AND"]["hotelId"]     = $hotelId;
        $choiceIs && $where["AND"]["choiceIs"]    = 1;
        $list      = $roomStyle->select($select, $where);
        $styleList = [];
        foreach ($list as $key => $value) {
            $styleList[$value['roomStyleId']] = $value;
        }
        return $styleList;
    }
    /**
     * 获取酒店选房配置列表
     * @param  [int] $hotelId   [酒店id]
     * @return [type] $roomList     [楼层数组]
     */
    public function hotelSelectRoomList($hotelId)
    {
        $roomList = [];
        if (!$hotelId) {
            return $roomList;
        }
        $floorList = $this->hotelRoomFloorList($hotelId);
        $styleList = $this->hotelRoomStyleList($hotelId);
        foreach ($floorList as $key => $value) {
            $roomNumberList               = $this->getRoomNumberList($hotelId, $value['floorId'], $styleList);
            $floorList[$key]['roomTotal'] = count($roomNumberList);
            if ($roomNumberList) {
                foreach ($roomNumberList as $key1 => $val1) {
                    $temp                               = [];
                    $temp['roomStyleId']                = $val1['roomStyleId'];
                    $temp['roomStyleName']              = $val1['roomStyleName'];
                    $temp['roomNumNames']               = $val1['roomNumNames'];
                    $floorList[$key]['roomStyleList'][] = $temp;
                }
            } else {
                $floorList[$key]['roomStyleList'] = [];
            }
            if (!empty($value['floorPlan']) && $floorList[$key]['roomTotal'] > 0) {
                $floorList[$key]['statusIs'] = 1;
            } else {
                $floorList[$key]['statusIs'] = 0;
            }
        }
        return $floorList;
    }
    //查询选房记录记录
    public function selectHotelRoomList($hotelId, $type, $floorId, $styleId, $roomNumName, $sellDate, $floorList, $styleList, $styleIdArr)
    {
        //查询酒店的房间列表
        $where                                       = [];
        $where['AND']['hotelId']                     = $hotelId;
        $floorId && $where['AND']['floorId']         = $floorId;
        $styleId && $where['AND']['roomStyleId']     = $styleId;
        !$styleId && $where['AND']['roomStyleId']    = $styleIdArr;
        $roomNumName && $where['AND']['roomNumName'] = $roomNumName;
        $where['AND']['isDelete'] = 0;
        $where['ORDER']['floorId']                   = "ASC";

        $columns = ["roomNumId", "roomNumName", "roomStyleId", "floorId"];

        $roomNumber = new DvbRoomNumber;
        $list       = $roomNumber->select($columns, $where);
        //查询选房记录
        $roomChoice = new DvbOrderRoomChoice;

        $where1                               = [];
        $where1['AND']['roomChoice.hotelId']  = $hotelId;
        $where1['AND']['roomChoice.sellDate'] = $sellDate;
        $join1                                = ["[>]dvb_order_room(room)" => ["roomChoice.orderId" => "orderId"]];
        $columns1                             = ["roomChoice.id", "roomChoice.roomNumId", "roomChoice.sellDate", "roomChoice.userName", "roomChoice.createTime", "roomChoice.channelType", "roomChoice.choiceUerId", "roomChoice.choiceUserName", "roomChoice.isOptional", "room.selectType", "room.orderId", "room.checkInTime", "room.checkOutTime"];
        $choiceList                           = $this->db->select("dvb_order_room_choice(roomChoice)", $join1, $columns1, $where1);
        $choiceArr                            = [];
        if ($choiceList) {
            foreach ((array) $choiceList as $key1 => $val1) {
                if ($val1['channelType'] == 2) {
                    $val1["checkInTime"]  = $val1["sellDate"];
                    $val1["checkOutTime"] = date("Y-m-d", (strtotime($val1["sellDate"]) + 86400));
                }
                $choiceArr[$val1['roomNumId']] = $val1;
            }
        }
        $floorNameArr = [];
        //是否已选房处理
        if ($list) {
            foreach ((array) $list as $key => $val) {
                $list[$key]['floorName']                                                                                                                 = $floorList[$val['floorId']]['floorName'];
                $list[$key]['roomStyleName']                                                                                                             = $styleList[$val['roomStyleId']]['roomStyleName'];
                isset($floorList[$val['floorId']]['floorName']) && !in_array($floorList[$val['floorId']]['floorName'], $floorNameArr) && $floorNameArr[] = $floorList[$val['floorId']]['floorName'];
                if (isset($choiceArr[$val['roomNumId']]['roomNumId']) && $choiceArr[$val['roomNumId']]['roomNumId'] == $val['roomNumId']) {
                    $list[$key]['userName']       = $choiceArr[$val['roomNumId']]['userName'];
                    $list[$key]['selectUserName'] = $choiceArr[$val['roomNumId']]['isOptional'] == 1 ? '客人自选' : $choiceArr[$val['roomNumId']]['choiceUserName'];
                    $list[$key]['selectDate']     = date('Y-m-d', $choiceArr[$val['roomNumId']]['createTime']);
                    $list[$key]['selectType']     = $choiceArr[$val['roomNumId']]['selectType'];
                    $list[$key]['orderId']        = $choiceArr[$val['roomNumId']]['orderId'];
                    $list[$key]['channelType']    = $choiceArr[$val['roomNumId']]['channelType'];
                    $list[$key]['checkInTime']    = $choiceArr[$val['roomNumId']]['checkInTime'];
                    $list[$key]['checkOutTime']   = $choiceArr[$val['roomNumId']]['checkOutTime'];
                    $list[$key]['isOptional']     = $choiceArr[$val['roomNumId']]['isOptional'];
                    $list[$key]['selectIs']       = 1;
                } else {
                    $list[$key]['userName']       = '';
                    $list[$key]['selectUserName'] = '';
                    $list[$key]['selectDate']     = '';
                    $list[$key]['selectType']     = 0;
                    $list[$key]['orderId']        = 0;
                    $list[$key]['channelType']    = 0;
                    $list[$key]['checkInTime']    = '';
                    $list[$key]['checkOutTime']   = '';
                    $list[$key]['isOptional']     = 0;
                    $list[$key]['selectIs']       = 0;
                }
                if (isset($choiceArr[$val['roomNumId']]['roomNumId']) && $choiceArr[$val['roomNumId']]['roomNumId'] == $val['roomNumId'] && $type == 2) {
                    unset($list[$key]);
                }
            }
            $list = array_values($list);
        }

        $floorNameArr && asort($floorNameArr);
        $resList = [];
        if ($floorNameArr && $list) {
            foreach ($floorNameArr as $key => $value) {
                foreach ($list as $key1 => $val1) {
                    $value == $val1['floorName'] && $resList[] = $val1;
                }
            }
        } else {
            $resList = $list;
        }

        return $resList;
    }

    /**
     * 获取符合安排房间的订单列表
     */
    public function noSelectorderRoomList($hotelId, $roomStyleId, $roomNumId, $sellDate, $orderId, $type)
    {

        $result                    = ['code' => 1001, 'message' => '获取数据失败', 'content' => []];
        $where                     = [];
        $where['AND']['hotelId']   = $hotelId;
        $where['AND']['roomNumId'] = $roomNumId;
        $where['AND']['sellDate']  = $sellDate;
        $roomChoice                = new DvbOrderRoomChoice;
        $info                      = $roomChoice->get('*', $where);
        if ($info) {
            $result['message'] = '房间已经选择了!';
            return $result;
        }

        $where                                         = [];
        $join                                          = ["[>]dvb_orders(order)" => ["room.orderId" => "orderId"]];
        $where['AND']['room.roomStyleId']              = $roomStyleId;
        $where['AND']['room.checkInTime[<=]']          = $sellDate;
        $where['AND']['room.checkOutTime[>]']          = $sellDate;
        $where['AND']['order.payStatus']               = 1;
        $where['AND']['order.orderStatus']             = 3;
        $where['AND']['room.selectType'] = $type == 1 ? 2 : [1,2];
        $columns                                       = ["order.orderId", "order.orderNo", "order.userId", "order.userName", "order.consignees", "room.orderRoomId", "room.checkInTime", "room.checkOutTime", "room.stayDays", "room.selectType"];
        $orderList                                     = $this->db->select("dvb_order_room(room)", $join, $columns, $where);
        $orderInfo                                     = [];

        $orderId > 0 && $orderInfo = $this->db->get("dvb_order_room(room)", $join, $columns, ["order.orderId" => $orderId]);

        $orderRoomIdArr = $roomIdArr = $orderIdArr = [];
        foreach ($orderList as $key => $value) {
            $orderRoomIdArr[] = $value['orderRoomId'];
        }
        $orderRoomDetail = new DvbOrderRoomDetail;

        $where                       = [];
        $where['AND']['orderRoomId'] = $orderRoomIdArr;
        $where['AND']['isSelected']  = 0;

        $roomDetailList               = $orderRoomDetail->select('*', $where);
        $roomDetailList && $roomIdArr = array_unique(array_column($roomDetailList, 'orderId'));

        if (count($orderList) > 0) {
            $orderIdArr = [];
            foreach ($orderList as $key => $value) {
                if ($value['consignees']) {
                    $consignees                  = unserialize($value['consignees']);
                    $orderList[$key]['userName'] = implode("、", $consignees);
                }
                $orderList[$key]['checkInTime']  = $value['checkInTime'];
                $orderList[$key]['checkOutTime'] = $value['checkOutTime'];
                if (!in_array($value['orderId'], $roomIdArr) || $sellDate != $value['checkInTime']) {//订单开始日期和筛选日期需要相同
                    unset($orderList[$key]);
                    continue;
                }
                array_push($orderIdArr, $value["orderId"]);
            }
            if($orderInfo && !in_array($orderInfo["orderId"], $orderIdArr) && $orderInfo["consignees"]){
                $consignees                  = unserialize($orderInfo['consignees']);
                $orderInfo['userName'] = implode("、", $consignees);
                array_push($orderList, $orderInfo);
            }
        }
  
        $orderList = $orderList ? array_filter(array_values($orderList)) : []; //过滤空值
        $result    = ['code' => 1000, 'message' => '获取数据成功', 'content' => $orderList];
        return $result;
    }
    /**
     * 根据开始结束数据获取选房列表
     */
    public function getRoomChoiceListByStartEndDate($columns, $checkInTime, $checkOutTime, $roomNumId = 0)
    {
        $roomChoice                              = new DvbOrderRoomChoice;
        $where                                   = [];
        $roomNumId && $where['AND']['roomNumId'] = $roomNumId;
        $where['AND']['sellDate[>=]']            = $checkInTime;
        $where['AND']['sellDate[<]']             = $checkOutTime;
        //$columns                      = ["id", "sellDate"];
        $selectList = $roomChoice->select($columns, $where);
        return $selectList;
    }
    /**
     * 选房，安排房间
     */
    public function electionOrderRoom($roomNumId, $type, $channelType, $orderId, $checkInTime, $checkOutTime, $userName, $oldOrderId,$oldCustomer)
    {
        //判断订单存在\支付情况
        $orderService = new OrderService;
        $orderInfo    = $orderService->getOrderRoomInfoById($orderId);
        if ($channelType == 1 && empty($orderInfo)) {
            $this->jsonRes['message'] = '订单不存在!';
            return $this->jsonRes;
        }
        if ($channelType == 1 && $orderInfo['payStatus'] != 1) {
            $this->jsonRes['message'] = '订单未支付，不能选房!';
            return $this->jsonRes;
        }
        if ($type == 2 && $orderId > 0 && $orderId != $oldOrderId) {
            $checkInTime  = $orderInfo['checkInTime'];
            $checkOutTime = $orderInfo['checkOutTime'];
        }

        if ($channelType != 2 && ($checkInTime < $orderInfo['checkInTime'] || $checkOutTime > $orderInfo['checkOutTime'])) {
            $this->jsonRes['message'] = '选择的日期超出了订单的日期！';
            return $this->jsonRes;
        }
        //查询被选房间列表
        if ($channelType != 2) {
            $selectedList = $this->getRoomChoiceListByStartEndDate("*", $checkInTime, $checkOutTime, $roomNumId);
            if ($selectedList && $type == 1) {
                $this->jsonRes['code']    = 1001;
                $this->jsonRes['message'] = $checkInTime . '-' . $checkOutTime . '的相应房间已被选房';
                return $this->jsonRes;
            }
        }

        if ($type == 1) {
//新增选房
            $userName = $channelType == 1 ? $orderInfo['userName'] : $userName;
            if ($channelType == 1) {
                $res = $this->addOrderRoomChoice($roomNumId, $channelType, $orderId, $checkInTime, $checkOutTime, $userName);
            } else {
                $res = $this->addOrderRoomChoiceOther($roomNumId, $channelType, $orderId, $checkInTime, $checkOutTime, $userName);
            }

        }
        if ($type == 2) {
            //修改选房安排
            $userName = $channelType == 1 ? $orderInfo['userName'] : $userName;
            $res      = $this->updateOrderRoomChoice($roomNumId, $channelType, $orderId, $checkInTime, $checkOutTime, $userName, $oldOrderId,$oldCustomer);
            return $res;
        }

        if ($type == 3) {
//取消安排
            $orderId = $channelType == 2 ? 0 : $orderId;
            $res     = $this->cancelOrderRoomChoice($roomNumId, $channelType, $orderId, $checkInTime, $checkOutTime, $userName);
        }
        if ($res) {
            $this->jsonRes['code']    = 1000;
            $this->jsonRes['message'] = '操作成功!';
        }
        return $this->jsonRes;

    }
    /**
     * 修改已选房安排
     */
    public function updateOrderRoomChoice($roomNumId, $channelType, $orderId, $checkInTime, $checkOutTime, $userName, $oldOrderId,$oldCustomer)
    {
        $orderService = new OrderService;
        $orderInfo    = $orderService->getOrderRoomInfoById($orderId);

        $orderRoomDetail                        = new DvbOrderRoomDetail;
        $roomChoice                             = new DvbOrderRoomChoice;
        $where                                  = [];
        $oldOrderId && $where['AND']['orderId'] = $oldOrderId;
        $where['AND']['roomNumId']              = $roomNumId;
        !$oldOrderId && $where['AND']['sellDate[>=]']           = $checkInTime;
        !$oldOrderId && $where['AND']['sellDate[<]']            = $checkOutTime;
        /*if($oldCustomer){//其它渠道更改其它渠道或直销，一次性取消完相同名称的选房，有危险
            unset($where['AND']['sellDate[>=]'],$where['AND']['sellDate[<]']);
            $where['AND']['userName']           = $oldCustomer;
        }*/
        $where['ORDER']['sellDate']             = "ASC";
        $roomChoiceList                         = $roomChoice->select("*", $where);

        if ($channelType == 2 && !$roomChoiceList) {
            $this->jsonRes['message'] = '需要更改的房间未被选择!';
            return $this->jsonRes;
        }

        $userNameHas = $orderIdHas = []; 
        foreach ($roomChoiceList as $key => $value) {
            !in_array($value["userName"], $userNameHas) && $userNameHas[] = $value["userName"];
            !in_array($value["orderId"], $orderIdHas) && $orderIdHas[] = $value["orderId"];
        }
        if(count($userNameHas) > 1 || count($orderIdHas) > 1){
            $this->jsonRes['message'] = '修改的时间超出了原订单的时间!';
            return $this->jsonRes;
        }
        if ($orderId > 0 && $channelType == 1) {
            $where                      = [];
            $where['AND']['orderId']    = $orderId;
            $where['AND']['isSelected'] = 0;
            $where['ORDER']['useDate']  = "ASC";
            $orderRoomDetailList        = $orderRoomDetail->select("*", $where);
            if ($orderId && !$orderRoomDetailList) {
                $this->jsonRes['message'] = '订单已全部选房!';
                return $this->jsonRes;
            }

            $consignees = unserialize($orderInfo["consignees"]);
            if (count($consignees) > 1) {
                $orderChoicList = $roomChoice->select(["id", "userName"], ["orderId" => $orderId]);
                foreach ($orderChoicList as $value1) {
                    foreach ($consignees as $key2 => $value2) {
                        if ($value1["userName"] == $value2) {
                            unset($consignees[$key2]);
                        }
                    }
                }
                $consignees = array_values($consignees);
            }

        } else {
            $roomNumberModel = new DvbRoomNumber;
            $roomInfo        = $roomNumberModel->get("*", ["roomNumId" => $roomNumId]);
        }

        $addData            = [];
        $addData['hotelId'] = $orderInfo['hotelId'] ? $orderInfo['hotelId'] : (int) $this->session->get('_adminiHotelId'); //1为测试数据，实际不存在为0的酒店
        $addData['roomStyleId'] = $orderId > 0 ? $orderInfo['roomStyleId'] : $roomInfo["roomStyleId"];
        $addData['roomNumId']   = $roomNumId;
        $addData['userId']      = $channelType == 2 ? 0 : $orderInfo['userId'];
        $addData['userName']    = $channelType == 2 ? $userName : $consignees[0];
        $addData['choiceUerId']    = (int) $this->session->get('_adminiUserId');
        $addData['choiceUserName'] = $this->session->get('_adminiRealName');
        $addData['channelType']    = $channelType;
        $addData['orderId']        = $channelType == 2 ? 0 : $orderId;
        $addData['isOptional'] = 2; //1自选房间，2到店选房房间

        $checkInTime = strtotime($checkInTime);
        $checkOutTime = strtotime($checkOutTime);

        if ($orderId > 0 && $channelType == 1) {
            if ($orderId == $oldOrderId) {
                $this->jsonRes['code']    = 1001;
                $this->jsonRes['message'] = '相同订单号不能修改房间!';
                return $this->jsonRes;
            }
            $result = $this->updateOrderRoomChoiceAffairDvb($roomChoiceList, $orderId, $orderRoomDetailList, $checkInTime, $checkOutTime, $addData, $oldOrderId,$orderInfo);
        } else {
            $result = $this->updateOrderRoomChoiceAffairOther($roomChoiceList, $orderId, $orderRoomDetailList, $checkInTime, $checkOutTime, $addData,$orderInfo);
        }
        if ($result) {
            $this->jsonRes['code']    = 1000;
            $this->jsonRes['message'] = '修改选房安排成功!';
        } else {
            $this->jsonRes['message'] = '修改选房安排失败!';
        }
        return $this->jsonRes;
    }
    /**
     * 修改选房,现在选房为直销渠道事务 todo
     */
    public function updateOrderRoomChoiceAffairDvb($roomChoiceList, $orderId, $orderRoomDetailList, $checkInTime, $checkOutTime, $addData, $oldOrderId,$orderInfo)
    {

        //修改选房数据，使用事务
        $result = $this->db->action(function ($database) use ($roomChoiceList, $orderId, $orderRoomDetailList, $checkInTime, $checkOutTime, $addData, $oldOrderId) {
            $selectSellDateArr = [];
            $orderRoomDetail   = new DvbOrderRoomDetail;
            $roomChoice        = new DvbOrderRoomChoice;
            foreach ($roomChoiceList as $key => $value) {
                
                    //需要使用事务
                    $where                   = ['id' => $value['id']];
                    $res1                    = $roomChoice->delete($where);
                    $oldOrderId > 0 && $res2 = $orderRoomDetail->update(['isSelected' => 0], ['ordId' => $value['ordId']]);
                    if (!$res1 || ($oldOrderId > 0 && !$res2)) {
                        return false;
                    }
                
            }

            foreach ($orderRoomDetailList as $key1 => $value1) {
                if ($checkInTime <= strtotime($value1['useDate']) && strtotime($value1['useDate']) < $checkOutTime) {
                    if (!empty($selectSellDateArr) && in_array($value1['useDate'], $selectSellDateArr)) {
                        continue;
                    }
                    //需要使用事务
                    $addData['ordId']      = $value1['ordId'];
                    $addData['sellDate']   = $value1['useDate'];
                    $addData['createTime'] = time();
                    $res3                  = $roomChoice->insert($addData);
                    $res4                  = $orderRoomDetail->update(['isSelected' => 1], ['ordId' => $value1['ordId']]);
                    if (!$res3 || ($orderId > 0 && !$res4)) {
                        return false;
                    }
                    $selectSellDateArr[] = $value1['useDate'];
                }
            }
        });
        
        $roomsNumber = new DvbRoomNumber;
        $roomInfo    = $roomsNumber->get("*", ["roomNumId" => $roomNumId]);

        //操作日志记录
        if($orderId > 0 && $result){
          $this->addOrderOperationLog($this->roomOperation["addRoom"], $orderId, $orderInfo["orderStatus"], $this->session->get("_adminiUserId"), $this->session->get("_adminiRealName"), '酒店选房,安排：' . $checkInTime . "至" . $checkOutTime . ' ' . $roomInfo["roomNumName"] . "房号");
        }
        if($oldOrderId > 0 && $result){
          $this->addOrderOperationLog($this->roomOperation["cancelRoom"], $oldOrderId, $orderInfo["orderStatus"], $this->session->get("_adminiUserId"), $this->session->get("_adminiRealName"), '酒店取消：' . $checkInTime ."至" . $checkOutTime.' ' . $roomInfo["roomNumName"] . "房号的选房安排");
        }
        return $result;
    }
    /**
     * $oldOrderId 原来的订单号
     * 修改选房,现在选房为其它销渠道事务
     */
    public function updateOrderRoomChoiceAffairOther($roomChoiceList, $oldOrderId, $orderRoomDetailList, $checkInTime, $checkOutTime, $addData,$orderInfo)
    {

        $result = $this->db->action(function ($database) use ($roomChoiceList, $oldOrderId, $orderRoomDetailList, $checkInTime, $checkOutTime, $addData) {
            $selectSellDateArr = [];
            $orderRoomDetail   = new DvbOrderRoomDetail;
            $roomChoice        = new DvbOrderRoomChoice;
            foreach ($roomChoiceList as $key => $value) {
                
                    //需要使用事务
                    $where                   = ['id' => $value['id']];
                    $res1                    = $roomChoice->delete($where);
                    $oldOrderId > 0 && $res2 = $orderRoomDetail->update(['isSelected' => 0], ['ordId' => $value['ordId']]);
                    if (!$res1 || ($oldOrderId > 0 && !$res2)) {
                        return false;
                    }
               
            }
            
            $startDate = $checkInTime;
            $endDate   = $checkOutTime;
            $roomDays  = round(($endDate - $startDate) / 86400);
            $tempDate  = $startDate;
            for ($i = 0; $i < $roomDays; $i++) {
                if ($startDate <= $tempDate && $tempDate < $endDate) {
                    $addData['ordId']      = 0;
                    $addData['sellDate']   = date("Y-m-d", $tempDate);
                    $addData['createTime'] = time();
                    $res3                  = $roomChoice->insert($addData);
                    if (!$res3) {
                        return false;
                    }
                    $tempDate += 86400;
                }
            }
            return true;
        });
        //操作日志记录
        if($oldOrderId > 0 && $result){
            $roomsNumber = new DvbRoomNumber;
            $roomInfo    = $roomsNumber->get("*", ["roomNumId" => $roomNumId]);
            $this->addOrderOperationLog($this->roomOperation["cancelRoom"], $oldOrderId, $orderInfo["orderStatus"], $this->session->get("_adminiUserId"), $this->session->get("_adminiRealName"), '酒店取消：' . $checkInTime ."至" . $checkOutTime.' ' . $roomInfo["roomNumName"] . "房号的选房安排");
        }

        return $result;
    }
    /**
     * 新增选房,直销渠道
     */
    public function addOrderRoomChoice($roomNumId, $channelType, $orderId, $checkInTime, $checkOutTime, $userName)
    {
        $orderService = new OrderService;
        $orderInfo    = $orderService->getOrderRoomInfoById($orderId);

        $orderRoomDetail         = new DvbOrderRoomDetail;
        $where                   = [];
        $where['AND']['orderId'] = $orderId;
        //$where['AND']['isSelected'] = 0;
        $where['ORDER']['useDate'] = "ASC";
        $orderRoomDetailList       = $orderRoomDetail->select("*", $where);
        $addData                   = $consigneesArr                   = [];
        $addData['hotelId']        = (int) $orderInfo['hotelId'] ? $orderInfo['hotelId'] : 1; //1为测试数据，实际不存在为0的酒店

        $addData['roomStyleId'] = $orderInfo['roomStyleId'];
        $addData['roomNumId']   = $roomNumId;
        $addData['userId']      = $orderInfo['userId'];
        //$addData['userName']                                                 = $orderInfo['userName'];
        $addData['choiceUerId']                                              = (int) $this->session->get('_adminiUserId');
        $this->session->get('_adminiRealName') && $addData['choiceUserName'] = $this->session->get('_adminiRealName');
        $addData['channelType']                                              = $channelType;
        $addData['orderId']                                                  = $orderId;
        $addData['isOptional']                                               = 2; //1自选房间，2到店选房房间

        if (empty($orderRoomDetailList)) {
            $this->logger->info('orderRoomDetailList: ' . json_encode($orderRoomDetailList));
            return false;
        }
        if ($orderInfo['consignees']) {
            $consignees = unserialize($orderInfo['consignees']);
            for ($i = 0; $i < $orderInfo['stayDays']; $i++) {
                foreach ($consignees as $value) {
                    $consigneesArr[] = $value;
                }
            }
        }
        $roomsNumber = new DvbRoomNumber;
        $roomInfo    = $roomsNumber->get("*", ["roomNumId" => $roomNumId]);

        //插入选房数据，使用事务
        $result = $this->db->action(function ($database) use ($orderRoomDetailList, $checkInTime, $checkOutTime, $consigneesArr, $addData) {
            $selectSellDateArr = [];
            $orderRoomDetail   = new DvbOrderRoomDetail;
            $roomChoice        = new DvbOrderRoomChoice;
            foreach ($orderRoomDetailList as $key => $value) {

                if ($checkInTime <= $value['useDate'] && $value['useDate'] < $checkOutTime) {
                    if ($value["isSelected"] || (!empty($selectSellDateArr) && in_array($value['useDate'], $selectSellDateArr))) {
                        continue;
                    }
                    //需要使用事务
                    $addData['ordId']      = $value['ordId'];
                    $addData['sellDate']   = $value['useDate'];
                    $addData['userName']   = $consigneesArr[$key];
                    $addData['createTime'] = time();
                    $res1                  = $roomChoice->insert($addData);

                    $selectSellDateArr[] = $value['useDate'];
                    $res2                = $orderRoomDetail->update(['isSelected' => 1], ['ordId' => $value['ordId']]);

                    if (!$res1 || !$res2) {
                        return false;
                    } else {
                        $selectSellDateArr[] = $value['useDate'];
                    }
                }
            }

            if (count($selectSellDateArr) > 0) {
                return true;
            } else {
                return false;
            }
        });

        $result && $this->addOrderOperationLog($this->roomOperation["addRoom"], $orderId, $orderInfo["orderStatus"], $this->session->get("_adminiUserId"), $this->session->get("_adminiRealName"), '酒店选房,安排：' . $checkInTime . "至" . $checkOutTime . ' ' . $roomInfo["roomNumName"] . "房号");
        return $result;
    }
    /**
     * 新增选房,其它渠道
     */
    public function addOrderRoomChoiceOther($roomNumId, $channelType, $orderId, $checkInTime, $checkOutTime, $userName)
    {
        $hotelId                      = (int) $this->session->get('_adminiHotelId');
        $orderRoomChoice              = new DvbOrderRoomChoice;
        $where                        = [];
        $where['AND']['roomNumId']    = $roomNumId;
        $where['AND']['hotelId']      = $hotelId;
        $where['AND']['sellDate[>=]'] = $checkInTime;
        $where['AND']['sellDate[<]']  = $checkOutTime;
        $choiceTotal                  = $orderRoomChoice->count($where);
        if ($choiceTotal > 0) {
            return false;
        }
        $roomNumber         = new DvbRoomNumber;
        $roomNumberInfo     = $roomNumber->get("*", ["roomNumId" => $roomNumId]);
        $addData            = [];
        $addData['hotelId'] = $hotelId;

        $addData['roomStyleId']                                              = $roomNumberInfo['roomStyleId'];
        $addData['roomNumId']                                                = $roomNumId;
        $addData['userId']                                                   = 0;
        $addData['userName']                                                 = $userName;
        $addData['choiceUerId']                                              = (int) $this->session->get('_adminiUserId');
        $this->session->get('_adminiRealName') && $addData['choiceUserName'] = $this->session->get('_adminiRealName');
        $addData['channelType']                                              = $channelType;
        $addData['orderId']                                                  = 0;
        $addData['isOptional']                                               = 2; //1自选房间，2到店选房房间

        $startDate = strtotime($checkInTime);
        $endDate   = strtotime($checkOutTime);
        $roomDays  = round(($endDate - $startDate) / 86400);

        //插入选房数据，使用事务
        $result = $this->db->action(function ($database) use ($roomDays, $startDate, $endDate, $addData) {

            $roomChoice = new DvbOrderRoomChoice;
            $tempDate   = $startDate;
            for ($i = 0; $i < $roomDays; $i++) {

                if ($startDate <= $tempDate && $tempDate < $endDate) {

                    $addData['ordId']      = 0;
                    $addData['sellDate']   = date("Y-m-d", $tempDate);
                    $addData['createTime'] = time();
                    $res1                  = $roomChoice->insert($addData);
                    $tempDate              = $tempDate + 86400;

                    if (!$res1) {
                        return false;
                    }
                }
            }
            return true;
        });
        return $result;
    }
    /**
     * 取消安排房间
     */
    public function cancelOrderRoomChoice($roomNumId, $channelType, $orderId, $sellDate)
    {
        
            $orderService = new OrderService;
            $orderInfo    = $orderService->getOrderRoomInfoById($orderId);
        
        $roomChoice                 = new DvbOrderRoomChoice;
        $where                      = [];
        $where['AND']['orderId']    = $orderId;
        $where['AND']['roomNumId']  = $roomNumId;
        !$orderId && $where['AND']['sellDate']                   = $sellDate;
        $where['ORDER']['sellDate'] = "ASC";
        $roomChoiceList             = $roomChoice->select("*", $where);
        if (empty($roomChoiceList)) {
            return false;
        }

        //取消选房安排，事务
        if ($orderId == 0) {//其它渠道

            $result = $this->db->action(function ($database) use ($roomChoiceList) {
                $roomChoice = new DvbOrderRoomChoice;
                foreach ($roomChoiceList as $key => $value) {
                    $where = ['id' => $value['id']];
                    $res1  = $roomChoice->delete($where);
                    if (!$res1) {
                        return false;
                    }
                }
                return true;
            });
        } else {//酒店直销

            $result = $this->db->action(function ($database) use ($roomChoiceList, $sellDate) {
                $orderRoomDetail = new DvbOrderRoomDetail;
                $roomChoice      = new DvbOrderRoomChoice;
                foreach ($roomChoiceList as $key => $value) {
                    if ($sellDate = $value['sellDate']) {
                        $where = ['id' => $value['id']];
                        $res1  = $roomChoice->delete($where);
                        $res2  = $orderRoomDetail->update(['isSelected' => 0], ['ordId' => $value['ordId']]);
                        if (!$res1 || !$res2) {
                            return false;
                        }
                    }
                }
                return true;
            });
            $roomsNumber = new DvbRoomNumber;
            $roomInfo    = $roomsNumber->get("*", ["roomNumId" => $roomNumId]);
            $result && $this->addOrderOperationLog($this->roomOperation["cancelRoom"], $orderId, $orderInfo["orderStatus"], $this->session->get("_adminiUserId"), $this->session->get("_adminiRealName"), '酒店取消：' . $sellDate . ' ' . $roomInfo["roomNumName"] . "房号的选房安排");
        }

        return $result;
    }

    //=============================================下面是前台API需要得函数==============================================
    //获取酒店可以选房的列表
    public function getHotelCanSelectRoomListByOrderNo($orderNo)
    {
        $orderService = new OrderService;
        $orderInfo    = $orderService->getOrderRoomInfoByOrderNo($orderNo);
        //检查订单状态
        $returnInfo = $this->checkOrderCanChoiceRoom($orderInfo);
        if ($returnInfo['code'] == 1001) {
            return $returnInfo;
        }
        $roomDetail = new DvbOrderRoomDetail;
        $total      = $roomDetail->count(["AND" => ["orderId" => $orderInfo["orderId"], "isSelected" => 1]]);
        if ($total > 0) {
            $this->jsonRes['message'] = '订单已选房!';
            return $this->jsonRes;
        }

        $floorList   = $this->hotelRoomFloorList($orderInfo['hotelId'],"*",1);
        $styleList   = $this->hotelRoomStyleList($orderInfo['hotelId'], ["roomStyleId", "hotelId", "roomStyleName", "roomStyleAlias"],1);
        $roomList    = $this->getHotelRoomListByHotelId($orderInfo['hotelId'], $orderInfo['roomStyleId']);
        $selectedArr = [];

        if ($roomList) {
            //查询已选房列表
            $where                        = [];
            $where['AND']['hotelId']      = $orderInfo['hotelId'];
            $where['AND']['sellDate[>=]'] = $orderInfo['checkInTime'];
            $where['AND']['sellDate[<]']  = $orderInfo['checkOutTime'];
            $orderRoomChoice              = new DvbOrderRoomChoice;
            $selectedList                 = $orderRoomChoice->select(["id", "roomNumId", "ordId"], $where);
            foreach ($selectedList as $value) {
                !in_array($value['roomNumId'], $selectedArr) && array_push($selectedArr, $value['roomNumId']);
            }
        }

        $roomStyleList = [];
        foreach ($floorList as $key1 => $val1) {
            $temp              = [];
            $temp['floorId']   = $val1['floorId'];
            $temp['floorName'] = $val1['floorName'];
            $temp['floorPlan'] = $val1['floorPlan'];
            $hasRoomStyle      = false;
            foreach ($roomList as $key => $value) {

                if ($val1['floorId'] == $value['floorId']) {
                    $value['roomStyleName'] = $styleList[$value['roomStyleId']]['roomStyleName'];
                    $value['isSold']        = ($selectedArr && in_array($value['roomNumId'], $selectedArr)) ? 1 : 0;
                    $temp['roomList'][]     = $value;
                }
                $value['roomStyleId'] == $orderInfo['roomStyleId'] && $hasRoomStyle = true;
            }

            if (!$hasRoomStyle) {
                continue;
            }
            if (!isset($temp['roomList'])) {
                unset($temp);
            } else {
                $roomStyleList[] = $temp;
            }

        }

        $content                  = ['orderId' => $orderInfo['orderId'], 'roomStyleId' => $orderInfo['roomStyleId'], 'roomAmount' => $orderInfo['roomsNumber'], 'roomStyleList' => $roomStyleList];
        $this->jsonRes['code']    = 1000;
        $this->jsonRes['message'] = '酒店选房列表';
        $this->jsonRes['content'] = $content;
        return $this->jsonRes;
    }
    /**
     * 根据酒店id 获取酒店房间列表
     */
    public function getHotelRoomListByHotelId($hotelId, $roomStyleId = 0)
    {
        if (!$hotelId) {
            return [];
        }
        $roomNumber                                  = new DvbRoomNumber;
        $where                                       = [];
        $where["AND"]["hotelId"]                     = $hotelId;
        $roomStyleId && $where["AND"]["roomStyleId"] = $roomStyleId;
        $where["AND"]["isDelete"]                    = 0;
        $list                                        = $roomNumber->select("*", $where);
        return $list;
    }
    /**
     * 用户在线选择房间
     */
    public function userAddOrderRoomChoice($orderNo, $roomStyleId, $roomNumIds)
    {
        if (!$orderNo || !$roomStyleId || !$roomNumIds) {
            $this->jsonRes["message"] = '参数不完整';
            return $this->jsonRes;
        }
        $roomNumArr   = explode(',', $roomNumIds);
        $orderService = new OrderService;
        $orderInfo    = $orderService->getOrderRoomInfoByOrderNo($orderNo);

        $returnInfo = $this->checkOrderCanChoiceRoom($orderInfo);
        if ($returnInfo['code'] == 1001) {
            return $returnInfo;
        }
        $orderId       = $orderInfo["orderId"];
        $orderRoomList = $orderService->getOrderRoomList($orderId, 0);
        if (count($orderRoomList) == 0) {
            $this->jsonRes['message'] = '订单已选房!';
            return $this->jsonRes;
        }
        $roomList = $this->getRoomChoiceListByStartEndDate(["id"], $orderInfo['checkInTime'], $orderInfo['checkOutTime'], $roomNumArr);

        if ($roomList) {
            $this->jsonRes['message'] = '房间已经被选了';
            return $this->jsonRes;
        }
        $result = $this->frontAddOrderRoomChoiceList($roomNumArr, $orderInfo, $orderRoomList);
        if ($result) {
            $this->jsonRes['code']    = 1000;
            $this->jsonRes['message'] = '选房成功!';
        } else {
            $this->jsonRes['message'] = '选房失败!';
        }
        return $this->jsonRes;
    }
    /**
     * 检查订单是否可以选房
     */
    public function checkOrderCanChoiceRoom($orderInfo)
    {

        if (empty($orderInfo)) {
            $this->jsonRes['message'] = '订单不存在，不能选房!';
            return $this->jsonRes;
        }
        if ($orderInfo['payStatus'] != 1) {
            $this->jsonRes['message'] = '订单未支付，不能选房!';
            return $this->jsonRes;
        }
        if ($orderInfo['selectType'] != 1) {
            $this->jsonRes['message'] = '非在线选房订单，不能操作选房!';
            return $this->jsonRes;
        }
        if ($orderInfo['checkInTime'] < date('Y-m-d')) {
            $this->jsonRes['message'] = '已经超过入住日期，不能选房!';
            return $this->jsonRes;
        }

        return $result = ['code' => 1000];
    }
    /**
     * 新增订单选房
     */
    public function frontAddOrderRoomChoiceList($roomNumArr, $orderInfo, $orderRoomList)
    {

        $orderRoomTotal = count($orderRoomList);
        $roomTotal      = count($roomNumArr);
        $roomIdList     = $consigneesArr     = [];
        $multiple       = $orderRoomTotal / $roomTotal;

        foreach ((array) $roomNumArr as $key => $value) {
            for ($i = 0; $i < $multiple; $i++) {
                $roomIdList[] = $value;
            }
        }
        //房间住宿人名称
        if ($orderInfo['consignees']) {
            $consignees = unserialize($orderInfo['consignees']);
            for ($i = 0; $i < $orderInfo['stayDays']; $i++) {
                foreach ($consignees as $value) {
                    $consigneesArr[] = $value;
                }
            }
        }

        //插入选房数据，使用事务
        $result = $this->db->action(function ($database) use ($orderRoomList, $orderInfo, $roomIdList, $consigneesArr) {
            $selectSellDateArr = [];
            $orderRoomDetail   = new DvbOrderRoomDetail;
            $roomChoice        = new DvbOrderRoomChoice;

            $addData                = [];
            $addData['hotelId']     = (int) $orderInfo['hotelId'] ? $orderInfo['hotelId'] : 1; //1为测试数据，实际不存在为0的酒店
            $addData['roomStyleId'] = $orderInfo['roomStyleId'];
            $addData['userId']      = $orderInfo['userId'];
            $addData['choiceUerId']    = (int) $orderInfo['userId'];
            $addData['choiceUserName'] = $orderInfo['userName'];
            $addData['channelType']    = 1;
            $addData['orderId']        = $orderInfo['orderId'];
            $addData['isOptional']     = 1; //1自选房间，2到店选房房间

            $checkInTime  = $orderInfo['checkInTime'];
            $checkOutTime = $orderInfo['checkOutTime'];
            foreach ((array) $orderRoomList as $key => $value) {

                if ($checkInTime <= $value['useDate'] && $value['useDate'] < $checkOutTime) {

                    $addData['roomNumId']  = $roomIdList[$key];
                    $addData['ordId']      = $value['ordId'];
                    $addData['userName']   = $consigneesArr[$key];
                    $addData['sellDate']   = $value['useDate'];
                    $addData['createTime'] = time();
                    $res1                  = $roomChoice->insert($addData);
                    $res2                  = $orderRoomDetail->update(['isSelected' => 1], ['ordId' => $value['ordId']]);

                    if (!$res1 || !$res2) {
                        return false;
                    }
                }
            }
            return true;
        });

        return $result;
    }
    /**
     * 订单操作日志
     */
    public function addOrderOperationLog($logNameArrange, $orderId, $orderStatus, $userId, $userName, $note)
    {
        $orderM = new DvbOrder();
        $orderM->setLog($logNameArrange, $orderId, $orderStatus, $userId, $userName, '', '', $note);
    }

}
