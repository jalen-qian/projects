<?php
namespace MyApp\Services;

use MyApp\Models\DvbHotel;
use MyApp\Services\BaseService;

/**
 * 
 * 酒店服务类
 *
 */
class HotelService extends BaseService
{
	/**
	 * 根据酒店id获取酒店信息
	 * 
	 */
	public function getHotelInfoById($hotelId,$select = "*")
	{
		$hotelModel = new DvbHotel;
		$info = $hotelModel->get($select,['id' => $hotelId]);
		return $info;
	}

	/**
	 * 根据地址获取经纬度信息
	 * @param string $address
	 */
	public static function getBaiduMapInfo($address){
		$getUrl = 'http://api.map.baidu.com/geocoder/v2/?address=' .$address .'&output=json&ak=zHAYOsbR179iwocu6KeGeT4j';

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$getUrl);//上传测试环境
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		//curl_setopt($ch,CURLOPT_POST,false);
		curl_setopt($ch,CURLOPT_SAFE_UPLOAD, false);
		//curl_setopt($ch,CURLOPT_POSTFIELDS,$data);

		$result = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($result,true);
		return $result;
	}
}