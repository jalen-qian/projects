<?php

/**
 * 加密解密公共类
 *
 */
namespace MyApp\Services;

use MyApp\Library\STD3Des;
use MyApp\Models\Users;
use MyApp\Library\Utilities;
use MyApp\Services\BaseService;

class SecurityService extends BaseService
{
    /**
     * 使用STD3Des类加密字符串(短信接口{"param1":"aaaaaaaaaaaa","param2":"bbbbbbbbb"}###20150906220512###73fea7f23d0a91a433方案)
     * @param string $jsonStr json格式串
     * @param string $keyPrefix 密钥配置名
     * @return string $securityStr 加密串
     * @throws CException
     */
    public function msgSTD3Encrypt($jsonStr, $keyPrefix = 'msgDesc')
    {
        $key  = $this->config['params'][$keyPrefix]['key'];
        $iv   = $this->config['params'][$keyPrefix]['iv'];
        $salt = $this->config['params'][$keyPrefix]['salt'];
        $des  = new STD3Des(base64_encode(trim($key)), base64_encode(trim($iv)));
        $time = date('YmdHis');
        //$jsonStr = '{ "templateCode": "HELPDESK-TURNOVER", "phone": "13560401379", "contentParams": "{\"orderType\":1,\"reporterName\":2,\"orderId\":3,\"turnovererName\":4}", "sendBy": "weifangbin", "messageTitle": "服务台-转单提醒111" }';
        $rawVerifyStr = "{$jsonStr}###{$time}###{$salt}";
        $verifyStr    = md5($rawVerifyStr);
        $rawDate      = "{$jsonStr}###{$time}###{$verifyStr}";
        $securityStr  = ($des->encrypt($rawDate));
        return $securityStr;
    }

    /**
     * 解密STD3Des类加密字符串(短信接口{"param1":"aaaaaaaaaaaa","param2":"bbbbbbbbb"}###20150906220512###73fea7f23d0a91a433方案)
     * @param string $securityStr 加密串
     * @param string $keyPrefix 密钥配置名
     * @return string $rawStr 失败为empty
     * @throws CException
     */
    public function msgSTD3Decrypt($securityStr, $keyPrefix = 'msgDesc')
    {
        if (($securityStr)) {
            $key  = $this->config['params'][$keyPrefix]['key'];
            $iv   = $this->config['params'][$keyPrefix]['iv'];
            $salt = $this->config['params'][$keyPrefix]['salt'];
            $des  = new STD3Des(base64_encode($key), base64_encode($iv));
            try {
                $realyStr = $des->decrypt($securityStr);
                if (preg_match('/###[0-9]{14}###[\s\S]{32}$/', $realyStr, $match)) {
                    $arr       = explode('###', $match[0]);
                    $time      = $arr[1];
                    $verifyStr = $arr[2];
                    $rawJson   = str_replace($match[0], '', $realyStr);
                    if (strtolower($verifyStr) == strtolower(md5("{$rawJson}###{$time}###{$salt}"))) {
                        return $rawJson;
                    };
                }
            } catch (Exception $e) {
                return '';
            }
        } else {
            return '';
        }
    }
}
