<?php

/**
 * api header安全验证类
 *
 */
namespace MyApp\Services;

use MyApp\Models\Users;
use MyApp\Library\Utilities;
use MyApp\Services\BaseService;

class SafeService extends BaseService
{

    const SECCESS_CODE            = 100; //salt正确
    const ERROR_SALT              = -99; //salt验证不通过
    const ERROR_TIMESTAMP_TIMEOUT = -98; //时间戳过期
    const ERROR_PARAM             = -97; //参数错误

    /**
     * [checkHttpHeaders api请求必须带上定制header信息]
     * 只有带HTTP_APIGEEHEADER2的header才需要验证
     * @return [bool] [description]
     */
    public function checkHttpHeaders()
    {

        if (isset($_SERVER['HTTP_APIGEEHEADER2']) || isset($_REQUEST['APIGEEHEADER2'])) {
            $APIGEEHEADER2 = isset($_SERVER['HTTP_APIGEEHEADER2']) ? $_SERVER['HTTP_APIGEEHEADER2'] : '';
            $APIGEEHEADER2 = isset($_REQUEST['APIGEEHEADER2']) ? $_REQUEST['APIGEEHEADER2'] : $APIGEEHEADER2;

            $callback      = isset($_REQUEST["callback"])?$_REQUEST["callback"]:'';
            $lang          = $_REQUEST['lang'];
            //兼容js、server端不传imei
            if ($lang !== 'app') {
                $_REQUEST['imei'] = '';
            }
            $utilities = new Utilities;

            if (!isset($_REQUEST['system']) || !isset($_REQUEST['version']) || !isset($_REQUEST['imei']) || !isset($_REQUEST['lang'])) {
                $return['code']    = self::ERROR_PARAM;
                $return['message'] = '参数错误';
                $return['content'] = '';
                echo $utilities->apiResultStandard($return['code'], $return['message'], $return['content'], $callback);exit;
            }
            $result = self::apiHeaderDecrypt($APIGEEHEADER2, $lang);
            if ($result < 0) {
                $return['code']    = $result;
                $return['message'] = '非法请求';
                $return['content'] = '';
                echo $utilities->apiResultStandard($return['code'], $return['message'], $return['content'], $callback);exit;
            }
        }
    }

    /**
     * [apiHeaderDecrypt header解密]
     * @param  [type] $header [header字符串]
     * @param  string $lang   [编程语言]
     * @return [type]         [整数]
     */
    public function apiHeaderDecrypt($header, $lang = 'app')
    {
        if (!in_array($lang, array('php', 'java', 'net', 'app', 'js'))) {
            return self::ERROR_PARAM;
        }
        $fromName = $lang;
        if (in_array($lang, array('php', 'java', 'net'))) {
            $fromName = 'server';
        }
        //由固定和动态前后两部分组成
        $salt     = $this->config['params']['safeSalt']['appCommon'] . $this->config['params']['safeSalt'][$fromName]['salt'];
        $overTime = $this->config['params']['safeSalt'][$fromName]['lastime'];
        //$overTime = 3600 * 24;
        $info = base64_decode($header);
        $time = time();
        if (strpos($info, '#') !== false) {
            $headerInfo = explode('#', $info);
            //验证时间戳
            if ($time - $headerInfo['0'] > $overTime) {
                return self::ERROR_TIMESTAMP_TIMEOUT;
            }
            //验证salt是否正确
            $parmaValue = '';
            if (!empty($headerInfo['1']) && strpos($headerInfo['1'], ',') !== false) {
                $params = explode(',', $headerInfo['1']);
                //sort($params);
                foreach ($params as $pv) {
                    //参数值可以为空，但是必须传这个参数
                    if (isset($_REQUEST[$pv])) {
                        $parmaValue .= $_REQUEST[$pv] . ',';
                    }
                }
                //验证R2MD5R6部分
                if (!empty($headerInfo['2'])) {
                    $r2  = substr($headerInfo['2'], 0, 2);
                    $r6  = substr($headerInfo['2'], -6);
                    $md5 = substr($headerInfo['2'], 2, 32);

                    //md5之前算法：T#Salt#B2#R2#R6
                    $str = $headerInfo['0'] . '#' . $salt . '#' . md5($parmaValue) . '#' . $r2 . '#' . $r6;
                    if ($md5 == md5($str)) {
                        return self::SECCESS_CODE;
                    } else {
                        //记录错误header
                        $this->logger->info('--------header-----------: ' . $info);
                        $this->logger->info('md5 parmaValue befor: ' . $parmaValue);
                        $this->logger->info('md5 parmaValue: ' . md5($parmaValue));
                        $this->logger->info('md5 befor: ' . $str);
                        $this->logger->info('md5 gera: ' . md5($str));
                        $this->logger->info('md5 origin: ' . $md5);
                    }
                }
                return self::ERROR_SALT;
            }

        }
        return self::ERROR_SALT;

    }
    /**
     * [apiHeaderEncrypt header加密]
     * @param  string $lang   [编程语言]
     * @param  array  $params [api需要签名的参数]
     * @return [type]         [字符串]
     */
    public function apiHeaderEncrypt($lang = 'app', $params = array('system', 'version', 'lang', 'imei'))
    {
        if (!in_array($lang, array('php', 'java', 'net', 'app', 'js'))) {
            exit('lang 参数不合法');
        }
        $time     = time();
        $fromName = $lang;
        if (in_array($lang, array('php', 'java', 'net'))) {
            $fromName = 'server';
        }
        //salt由固定部分+各个平台独立配置
        $salt = $this->config['params']['safeSalt']['appCommon'] . $this->config['params']['safeSalt'][$fromName]['salt'];
        $utilities = new Utilities;
        //生成2位和6位的随机数
        $r2 = $utilities->geraHash(2);
        $r6 = $utilities->geraHash(6);
        //sort($params);
        //连接参数key
        $paramString = join(',', $params) . ',';
        //连接参数值
        $parmaValue = '';
        foreach ($params as $pv) {
            $parmaValue .= $this->config['params']['mmm_api'][$pv] . ',';
        }
        //字符串生成算法base64(T#B1#R2MD5R6)
        $md5             = md5($time . '#' . $salt . '#' . md5($parmaValue) . '#' . $r2 . '#' . $r6);
        $headerBase64Pre = $time . '#' . $paramString . '#' . $r2 . $md5 . $r6;
        $header          = base64_encode($headerBase64Pre);

        return $header;
    }

    /**
     * [getSalt 获取salt]
     * @param  [type] $lang    [编程语言]
     * @return [json]          [salt内容]
     */
    public function getSalt($lang)
    {

        if (empty($lang)) {
            $return['code']    = -1;
            $return['message'] = '参数缺失';
            $return['content'] = array();
            return $return;
        }

        if (!in_array($lang, array('php', 'java', 'net', 'app'))) {
            $return['code']    = -2;
            $return['message'] = '参数错误';
            $return['content'] = array();
            return $return;
        }

        $return['code']    = 1000;
        $return['message'] = 'success';

        $utilities = new Utilities;
        //$fromName = $lang;
        
        if (in_array($lang, array('php', 'java', 'net'))) {
        	$keyPrefix = 'safeApiDesc';
            //$fromName           = 'server';
            $salt['asltServer'] = $utilities->apiSTD3Encrypt($this->config['params']['safeSalt']['server']['salt'], $keyPrefix);
            $salt['asltApp']    = $utilities->apiSTD3Encrypt($this->config['params']['safeSalt']['app']['salt'], $keyPrefix);
            $salt['asltJs']     = $utilities->apiSTD3Encrypt($this->config['params']['safeSalt']['js']['salt'], $keyPrefix);

        } else {
        	$keyPrefix = 'safeAppJsDesc';
            $salt = $utilities->apiSTD3Encrypt($this->config['params']['safeSalt'][$lang]['salt'], $keyPrefix);

        }

        $return['content'] = array('aslt' => $salt);
        //$return['content'][] = array('unaslt' => YcfSecurity::getSTD3Decrypt($salt, $lang));
        return $return;

    }

}
