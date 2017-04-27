<?php
namespace MyApp\Services;

use MyApp\Models\DvbHotel;
use MyApp\Models\DvbProduct;
use MyApp\Models\DvbProductPrice;
use MyApp\Services\BaseService;
use MyApp\Models\DvbRoomStyle;
use MyApp\Models\DvbFacilities;

/**
 * 产品服务类
 * @author zhaojianhui
 *
 */
class ProductService extends BaseService
{
    /**
     * 保存售价及房态数据
     * @param number $saveType 保存类别(1:设置售价,2:设置房态)
     * @param array $priceData 保存数据，有控制器组织
     */
    public function savePriceData($saveType, $priceData = [])
    {
        return $this->db->action(function ($database) use ($saveType, $priceData) {
            //获取产品ID
            $productId = (new DvbProduct())->initProductId($priceData['roomStyleId'], $priceData['hotelId']);
            if (!$productId) {
                return false;
            }
            //设置房态或售价
            $priceId           = false;
            $productPriceModel = new DvbProductPrice();
            foreach ($priceData['dateList'] as $date) {
                $where = [
                    'hotelId'     => $priceData['hotelId'],
                    'roomStyleId' => $priceData['roomStyleId'],
                    'sellDate'    => $date,
                    'productId'   => $productId,
                ];
                $findPriceData = $productPriceModel->get(['priceId'], ['AND' => $where]);
                if ($saveType == 1) {
                    //1:设置售价
                    $data = [
                        'price'        => $priceData['price'],
                        'currency'     => 1,
                        //'statusIs'     => $priceData['statusIs'],
                        'updateUserId' => (int)$this->session->get("_adminiUserId"),
                        'updateTime'   => time(),
                    ];
                    if ($findPriceData) {
                        $exec = $productPriceModel->update($data, ['priceId' => $findPriceData['priceId']]);
                    } else {
                        $data['createUserId'] = (int)$this->session->get("_adminiUserId");
                        $data['createTime']   = time();
                        $exec                 = $productPriceModel->insert(array_merge($where, $data));
                    }
                } elseif ($saveType == 2) {
                    //2:设置房态
                    $data = [
                        'statusIs'     => $priceData['statusIs'],
                        'updateUserId' => (int)$this->session->get("_adminiUserId"),
                        'updateTime'   => time(),
                    ];
                    //房态设置为开放时才更新库存，关闭时无须更新库存
                    $priceData['statusIs'] == 1 && $data['stockNum'] = $priceData['stockNum']; 
                    if ($findPriceData) {
                        $exec = $productPriceModel->update($data, ['priceId' => $findPriceData['priceId']]);
                    } else {
                        //无设置售价自动跳过房态设置
                        $exec = true;
                    }
                }
                if (!$exec) {
                    return false;
                }
            }
            return true;
        });
    }
    /**
     * 判断是否数据有更改
     */
    public function isChange($saveType, $priceData)
    {
        if (count($priceData['dateList']) > 1){
            return true;
        }
        $productPriceModel = new DvbProductPrice();
        $findPriceData = $productPriceModel->get('*', ['AND'=>[
            'hotelId'=>$priceData['hotelId'],
            'roomStyleId'=>$priceData['roomStyleId'],
            'sellDate'=>$priceData['dateList'][0],
        ],
        ]);
        if (!$findPriceData){
            return true;
        }
        //设置售价
        if ($saveType == 1 && (float)$findPriceData['price'] != $priceData['price']) {
            return true;
        }
        //设置库存
        if ( $saveType == 2 && (int)$findPriceData['statusIs'] != $priceData['statusIs']){
            return true;
        }
        if ($saveType == 2 && (int)$findPriceData['stockNum'] != $priceData['stockNum']){
            return true;
        }
        return false;
    }
    /**
     * 获取房型价格列表
     * @param unknown $hotelId
     * @param unknown $dateList
     */
    public function getStylePriceList($hotelId, $dateList)
    {
        $productPriceModel = new DvbProductPrice();
        //酒店有效价格列表
        $validPriceList = $productPriceModel->getHotelDateValidPriceList($hotelId, $dateList);
        $priceList = [];
        foreach ($validPriceList as $v) {
            isset($priceList[(int) $v['roomStyleId']]) || $priceList[(int) $v['roomStyleId']] = [];
            $priceList[(int) $v['roomStyleId']][] = $v;
        }
        //过滤掉日期列表内有缺失的房型
        foreach ($priceList as $roomStyleId => $v){
            if (count($dateList) != count($v)){
                unset($priceList[$roomStyleId]);
            }
        }
        //房型列表
        $roomStyleModel = new DvbRoomStyle();
        $roomStyleList = $roomStyleModel->select([
            'roomStyleId','roomStyleName','roomStyleAlias','area','bedType','bedSize',
            'breakfastType','hasWindow','floor','maxOccupancy','extraBedRules','coverPic',
            'album','statusIs','choiceIs'
        ], ['AND' =>['roomStyleId'=>array_keys($priceList),'statusIs'=>1] ]);
        //处理房型数据
        foreach ($roomStyleList as $k => $v){
            //包装数据
            $v = $roomStyleModel->packing($v);
            //组装房型标签文字
            $v['roomTag'] = implode('、', [$v['area'], $v['breakfast'], $v['hasWindow']]);
            
            $v['coverPicThumb'] = $v['coverPic'] ? $v['coverPic'] : 'http://cdn7.jinxidao.com/direct_selling/images/photo-list-default.png';
            $v['albumCount'] = count($v['album']);
            //价格列表
            $roomStylePriceList = isset($priceList[$v['roomStyleId']]) ? $priceList[$v['roomStyleId']] : [];
            //房型平均价
            $v['price'] = ceil(array_sum(array_column($roomStylePriceList, 'price'))/count($roomStylePriceList));
            //可定状态
            $statusIs = 0;
            $statusStr = '';
            $choiceIs = 0;//是否可选房
            if ($v['statusIs'] == 1){
                //查看是否还有剩余库存
                if (min(array_column($roomStylePriceList, 'stockNum')) > 0){
                    $statusStr = '预订';
                    $statusIs = 1;
                }else{
                    $statusStr = '订满';
                }
            }else{
                $statusStr = '已关闭';
            }
            $v['statusIs'] = $statusIs;
            $v['statusStr'] = $statusStr;
            $roomStyleList[$k] = $v;
        }
        return $roomStyleList;
    }
    /**
     * 获取房型详细信息
     * @param number $hotelId
     * @param number $roomStyleId
     * @param array $dateList
     */
    public function getStyleInfo($hotelId, $roomStyleId, $dateList)
    {
        //房型列表
        $roomStyleModel = new DvbRoomStyle();
        $roomStyleData = $roomStyleModel->get([
            'roomStyleId','hotelId','roomStyleName','roomStyleAlias','area','bedType','bedSize',
            'breakfastType','hasWindow','floor','maxOccupancy','extraBedRules','roomFacList','coverPic',
            'album','statusIs','choiceIs'
        ], ['AND' =>['roomStyleId'=>$roomStyleId,'hotelId'=>$hotelId,'statusIs'=>1] ]);
        if (!$roomStyleData){
            return $roomStyleData;
        }
        //酒店模型
        $hotelModel = new DvbHotel();
        //酒店名称
        $hotelData = $hotelModel->get(['name'], ['id'=>$roomStyleData['hotelId']]);
        $roomStyleData['hotelName'] = isset($hotelData['name']) ? $hotelData['name'] : '';
        //包装数据
        $roomStyleData = $roomStyleModel->packing($roomStyleData);
        //组装房型标签文字
        $roomStyleData['roomTag'] = implode('、', [$roomStyleData['area'], $roomStyleData['breakfast'], $roomStyleData['hasWindow']]);
        //房型封面缩略图图
        $roomStyleData['coverPicThumb'] = $roomStyleData['coverPic'] ? $roomStyleData['coverPic'] : 'http://cdn7.jinxidao.com/direct_selling/images/photo-list-default.png';
        //房型封面图总数
        $roomStyleData['albumCount'] = count($roomStyleData['album']);
        //实例化价格模型
        $productPriceModel = new DvbProductPrice();
        //酒店指定房型有效价格列表
        $validPriceList = $productPriceModel->getHotelRoomStyleDatePriceList($hotelId, $roomStyleId, $dateList);
        //房型价格
        $roomStyleData['price'] = $validPriceList ? ceil(array_sum(array_column($validPriceList, 'price'))/count($validPriceList)) : 0;
        //日历价格
        $priceCalendar = [];
        foreach ($validPriceList as $v){
            $priceCalendar[$v['sellDate']] = (float)$v['price'];
        }
        $roomStyleData['priceCalendar'] = $priceCalendar;
        //可定状态
        $statusIs = 0;
        $statusStr = '';
        $choiceIs = 0;//是否可选房
        if ($roomStyleData['statusIs'] == 1){
            //查看是否还有剩余库存
            if (min(array_column($validPriceList, 'stockNum')) > 0){
                $statusStr = '预订';
                $statusIs = 1;
            }else{
                $statusStr = '订满';
            }
        }else{
            $statusStr = '已关闭';
        }
        $roomStyleData['statusIs'] = $statusIs;
        $roomStyleData['statusStr'] = $statusStr;
        //最大可输入库存数
        $roomStyleData['maxInputStockNum'] = 0;
        if ($statusIs == 1 && count($dateList) == count($validPriceList) ){
            $roomStyleData['maxInputStockNum'] = max(array_column($validPriceList, 'stockNum'));
        }
        //相册列表
        $albumList = [];
        foreach ($roomStyleData['album'] as $v){
            $albumList[] = [
                'imgUrlThumb' => $v['url'],
                'imgUrl' => $v['url'],
            ];
        }
        $roomStyleData['albumList'] = $albumList;
        //房型设施列表
        //设施模型
        $facilitiesModel = new DvbFacilities();
        $facData = $facilitiesModel->getFacValueList(2, (array)$roomStyleData['roomFacList']);
        //设施简单列表
        $roomStyleData['facListSimple'] = $facData['list'];
        //设施分类列表
        $roomStyleData['facList'] = $facData['catList'];
        //删除多余数据
        unset($roomStyleData['album'],$roomStyleData['roomFacList']);
        
        return $roomStyleData;
    }
}
