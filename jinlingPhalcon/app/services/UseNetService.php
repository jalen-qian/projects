<?php

/**
 * 调用.net接口公共类
 *
 */
namespace MyApp\Services;

use MyApp\Library\STD3Des;
use MyApp\Library\XHttp;
use MyApp\Models\Users;
use MyApp\Library\Utilities;
use MyApp\Services\BaseService;

class UseNetService extends BaseService
{
    /**
     * .net自定义短信发送接口 zhuangzhiwei
     * @param $userPhone 短信接收的用户手机
     * @param $content 短信内容
     * @param string $SendBy 发送人id
     * @param string $userId 收信人
     * @param string $userType
     * @param string $messageTitle 短信标题
     * @param string $templateCode 短信模板
     * @return bool
     */
    public function sendMessageByNet($userPhone, $content, $SendBy = '', $userId = '', $userType = '', $messageTitle = '',$templateCode = 'DVB_MSG'){
        $xhttp = new XHttp();
        $securityService = new SecurityService();
        $postData = array(
            'phone'         => $userPhone,
            'templateCode' => $templateCode,
            'sendBy'        => $SendBy,
            'userId'        => $userId,
            'userType'      => isset($userType) ? $userType : -1,
            'content'  => $content,
            'messageTitle'  => $messageTitle,
        );
        $encodeDate   = $securityService->msgSTD3Encrypt(json_encode($postData));
        $url = $this->config['params']['mmm_api']['index_default'].'Sms/SendSMS';
        $httpResponse = $xhttp->post($url, $encodeDate,false,array('Content-Type' => 'application/json; charset=utf-8'));
        if (!empty($httpResponse)) {
            //解密3DES
            $decodeResponse = $securityService->msgSTD3Decrypt($httpResponse);
            if (!empty($decodeResponse)) {
                $decodeResponse = json_decode($decodeResponse, true);
                $result = (!empty($decodeResponse['data']) && isset($decodeResponse['data']['resultType']) && (0 == $decodeResponse['data']['resultType']));
            }
        }
        $this->log("短信发送result:" . json_encode($result).'#'.date("Y-m-d,H:i:s").'#'.$content, 'application');
        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }
}
