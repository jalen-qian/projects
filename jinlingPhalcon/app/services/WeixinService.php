<?php

/**
 * 通用微信方法类
 *
 */
namespace MyApp\Services;

use MyApp\Library\STD3Des;
use MyApp\Library\Wechat;
use MyApp\Library\XHttp;
use MyApp\Models\Users;
use MyApp\Library\Utilities;
use MyApp\Services\BaseService;

class WeixinService extends BaseService
{
    /**
     * @param $url 回调url
     * @param $hotelId 酒店id
     * @return string 授权链接
     */
    public function weixinOauth($url,$hotelId){
        $options = array(
            'token' => 'weixin', //填写你设定的key
            'appid' => 'wx99596f906360376f', //填写高级调用功能的app id
            'appsecret' => 'd4624c36b6795d1d99dcf0547af5443d' //填写高级调用功能的密钥
        );
        $weObj = new Wechat($options);
        return $weObj->getOauthRedirect($url);
    }

    /**
     * 此方法只能适用于微信回调后获取openid等，需在微信回调时调用
     * 根据回调code获取相关信息（openid等）
     */
    public function getWeixinUserInfo(){
        $options = array(
            'token' => 'weixin', //填写你设定的key
            'appid' => 'wx99596f906360376f', //填写高级调用功能的app id
            'appsecret' => 'd4624c36b6795d1d99dcf0547af5443d' //填写高级调用功能的密钥
        );
        $weObj = new Wechat($options);
        $data = $weObj->getOauthAccessToken();
        return $data;
    }
}
