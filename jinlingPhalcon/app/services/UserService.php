<?php

namespace MyApp\Services;

use MyApp\Library\Crypt_RSA;
use MyApp\Library\STD3Des;
use MyApp\Library\XHttp;
use MyApp\Services\BaseService;

class UserService extends BaseService
{
    /**
     * 根据私密生成公密
     * @param string $privatekey
     */
    public static function publicKeyToHex($privatekey)
    {
        $rsa = new Crypt_RSA();
        $rsa->loadKey($privatekey);
        $raw = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW);
        return $raw['n']->toHex();
    }

    /**
     * 用私密解密
     * @param string $privatekey
     * @param string $encrypted
     * @return Ambigous <string, boolean, unknown>
     */
    public static function decrypt($privatekey, $encrypted)
    {
        $rsa       = new Crypt_RSA();
        $encrypted = @pack('H*', $encrypted);
        $rsa->loadKey($privatekey);
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        return $rsa->decrypt($encrypted);
    }

    /**
     * 生成用户$securetKey 时间期限为15天
     * @param $telphone
     * @param $hotelId
     * @param $userId
     * @return bool|string
     */
    public function genSecuretKey($telphone, $hotelId, $userId, $system, $userName)
    {
        if ($telphone && $hotelId && $userId) {
            $datetime   = time();
            $key        = $this->config['params']['apiDesc']['userLoginDesc']['key'];
            $iv         = $this->config['params']['apiDesc']['userLoginDesc']['iv'];
            $mdkey      = $this->config['params']['apiDesc']['userMd5Key'];
            $des        = new STD3Des(base64_encode($key), base64_encode($iv));
            $securetKey = $des->encrypt($hotelId . '###' . $telphone . '###' . $userId . '###' . $userName . '###' . $system . '###' . $datetime . '###' . md5($hotelId . '###' . $telphone . '###' . $userId . '###' . $userName . '###' . $system . '###' . $datetime . '###' . $mdkey));
            return $securetKey;
        } else {
            return false;
        }
    }

    /**
     * $securetKey验证 时间期限为15天
     * @param $securetKey
     * @return array|bool
     */
    public function checkSecuretKey($securetKey)
    {
        if ($securetKey) {
            $userCacheTimeSet = $this->config['params']['userCacheTimeSet'];
            $key              = $this->config['params']['apiDesc']['userLoginDesc']['key'];
            $iv               = $this->config['params']['apiDesc']['userLoginDesc']['iv'];
            $mdkey            = $this->config['params']['apiDesc']['userMd5Key'];
            $des              = new STD3Des(base64_encode($key), base64_encode($iv));
            $data             = $des->decrypt($securetKey);
            $returnData       = ['code' => 1000, 'message' => '解密正常'];
            if ($data) {
                list($hotelId, $telphone, $userId, $userName, $system, $datetime, $md5str) = explode('###', $data);
                if (md5($hotelId . '###' . $telphone . '###' . $userId . '###' . $userName . '###' . $system . '###' . $datetime . '###' . $mdkey) != $md5str) {
                    $returnData["code"]    = 1005;
                    $returnData["message"] = "解密失败";
                }
                if (time() - $datetime >= $userCacheTimeSet) {
                    $returnData["code"]    = 1004;
                    $returnData["message"] = "securetKey过期";
                }
                if ($returnData["code"] == 1000) {
                    $returnData["content"] = ["hotelId" => $hotelId, "telPhone" => $telphone, "userId" => $userId, "userName" => $userName];
                }
                return $returnData;
            } else {
                $returnData["code"]    = 1005;
                $returnData["message"] = "securetKey错误，解密失败";
                $returnData["content"] = ["result" => false];
                return $returnData;
            }

        } else {
            $returnData["code"]    = 1003;
            $returnData["message"] = "参数错误";
            $returnData["content"] = ["result" => false];
            return $returnData;
        }
    }

    /**
     * 用户密码md5加密
     * @param $userPhone
     * @param $password
     * @return string
     */
    public function encryUserPwd($userPhone, $password)
    {
        $mdkey = $this->config['params']['apiDesc']['userMd5Key'];
        $mdPwd = md5($userPhone . $password . $mdkey);
        return $mdPwd;
    }

    /**
     *  检测当天发送验证码的次数是否已达最大值
     * @param $type 0、注册 1、登录 2、找回密码 3、修改密码
     * @param $hotelId
     * @param $telPhone
     */
    public function checkCountOfMsgCode($type, $hotelId, $telPhone)
    {
        //当前类型验证码发送最大值缓存key
        /*$cacheTypeId = "dvb_msgType_" . $type;
        $count       = $this->cache->get($cacheTypeId);*/
        $count = '';
        if (!$count) {
            $count = $this->db->get("dvb_message_type", ["sendCount"], ["msgType" => $type])['sendCount'];
            //$this->cache->save($cacheTypeId, $count, 3600 * 24 * 7);
        }
        //当天当前验证码类型发送的次数
        /*$cacheSumId = "dvb_sumType_" . $type . $hotelId . $telPhone;
        $num        = $this->cache->get($cacheSumId);*/
        $num = 0;
        if (!$num) {
            $nowTime  = strtotime(date('Y-m-d'));
            $dawnTime = strtotime(date('Y-m-d', strtotime('+1 day')));
            $num      = $this->db->count("dvb_send_message", ["AND" => ["telphone" => $telPhone, "hotelId" => $hotelId, "sendType" => $type, "sendStatus" => 1, "createTime[>]" => $nowTime, "createTime[<]" => $dawnTime]]);
            //$this->cache->save($cacheSumId, $num, 3600);
        }
        if ($num < $count) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证上一次发验证码距离现在的时间,时间至少为60s
     * @param $type
     * @param $hotelId
     * @param $telPhone
     * @return bool
     */
    public function checkLastSendTime($type, $hotelId, $telPhone)
    {
        /*$cacheLsId = "dvb_msgLastTime_" . $type . $hotelId . $telPhone;
        $lastTime  = $this->cache->get($cacheLsId);
        if (!$lastTime) {
            $message  = $this->db->get("dvb_send_message", ["createTime"], ["telphone" => $telPhone, "hotelId" => $hotelId, "sendType" => $type, "sendStatus" => 1, "isActive" => 0]);
            if($message){
                $lastTime = $message['createTime'];
                $this->cache->save($cacheLsId, $lastTime, 3600);
            }else{
                return true;
            }
        }*/
        $message = $this->db->get("dvb_send_message", ["createTime", "sendTime", "msgCode"], ["AND" => ["telphone" => $telPhone, "hotelId" => $hotelId, "sendType" => $type, "sendStatus" => 1, "isActive" => 0]]);
        if ($message) {
            $lastTime = $message['sendTime'];
        } else {
            return true;
        }
        $nowtime = time();
        if ($nowtime - $lastTime > 60) {
            return $message;
        } else {
            return false;
        }
    }

    /**
     * 返回验证码短信相关内容
     * @param $type
     * @return array
     */
    public function getMsgContent($type, $name, $msgCode)
    {
        $content = [];
        if (!$msgCode) {
            $msgCode = $this->generateCode(6);
        }
        switch ($type) {
            case 0:
                //短信标题
                $content['title'] = "注册验证码";
                //短信内容
                $content['content'] = "【". $name . "】" . $msgCode . "（".$name." 会员注册验证码，请完成验证）如非本人操作，请忽略本短信";
                //验证码
                $content['msgCode'] = $msgCode;
                break;
            case 1:
                //短信标题
                $content['title'] = "登录验证码";
                //短信内容
                $content['content'] = "【". $name . "】" . $msgCode . "（".$name ."会员登录验证码，请完成验证）如非本人操作，请忽略本短信。";
                //验证码
                $content['msgCode'] = $msgCode;
                break;
            case 2:
                //短信标题
                $content['title'] = "找回密码验证码";
                //短信内容
                $content['content'] = "【". $name . "】" . $msgCode . "（您正在操作找回密码）请勿向任何人提供您的短信验证码。";
                //验证码
                $content['msgCode'] = $msgCode;
                break;
            case 3:
                //短信标题
                $content['title'] = "修改密码验证码";
                //短信内容
                $content['content'] = "【". $name . "】" . $msgCode . "（您正在操作修改密码）请勿向任何人提供您的短信验证码。";
                //验证码
                $content['msgCode'] = $msgCode;
                break;
            default:
                //短信标题
                $content['title'] = "验证码";
                //短信内容
                $content['content'] = "验证码为:" . $msgCode;
                //验证码
                $content['msgCode'] = $msgCode;
        }
        return $content;
    }

    /**
     * 生成验证码 默认6位
     * @param int $length
     * @return int
     */
    function generateCode($length = 6)
    {
        return rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }

    /**
     * 校验密码
     * @return boolean
     */
    public function passwordValidator($password)
    {
        if (!preg_match("/^\S{6,}$/", $password)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 验证手机号码
     * @param $str
     * @return bool|int
     */
    public function mobile($str)
    {
        return preg_match('#^1[3-9][\d]{9}$#', $str);
    }

    /**
     * 验证码短信成功发送后
     * @param $data
     * @param $hotelId
     * @param $telPhone
     * @param $type
     * @return mixed
     */
    public function afterSendMsg($data, $hotelId, $telPhone, $type)
    {
        return $this->db->action(function ($database) use ($data, $hotelId, $telPhone, $type) {
            $res1 = $this->db->update("dvb_send_message", ["isActive" => 1], ["AND" => ["sendType" => $type, "isActive" => 0, "telphone" => $telPhone, "hotelId" => $hotelId]]);
            $res  = $this->db->insert("dvb_send_message", $data);
            if ($res1 === false || $res === false) {
                return false;
            }
            /*$cacheLsId = "dvb_msgLastTime_" . $type . $hotelId . $telPhone;
            $this->cache->save($cacheLsId, time(), 3600);
            $cacheSumId = "dvb_sumType_" . $type . $hotelId . $telPhone;
            $num = $this->cache->get($cacheSumId);
            $this->cache->save($cacheSumId, ($num+1), 3600);*/
            return true;
        });
    }

    /**
     * 随机生成字符串
     * @param $len
     * @return string
     */
    public function genRandomString($len = 6)
    {
        $chars    = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars); // 将数组打乱

        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

}
