<?php

namespace MyApp\Controllers\Front;

use Phalcon\Mvc\Controller;

class BaseController extends Controller
{

    //指定跳转目录到views下的front
    public function afterExecuteRoute()
    {
        $this->view->setViewsDir($this->view->getViewsDir() . 'front/');
    }

    //public $openid; //微信用户openid（微信端登录）

    //初始化数据，获取对应的酒店id
    //  public function initialize()
    //    {
    //        //微信授权开关
    //        $isWeixin = 0;
    //        if(isset($this->config['params']['isWeixin'])){
    //            $isWeixin = $this->config['params']['isWeixin'];
    //        }
    //
    //        //判断是否已经授权
    //        $code     = $this->request->get("code");
    //        if ($isWeixin && !$code) {
    //            //微信授权
    //            $dirUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//授权回调url
    //            //$dirUrl = 'http://dvb-test.yaochufa.com'.$_SERVER['REQUEST_URI'];
    //            $this->weixinOauth($dirUrl);
    //        }
    //
    //        $userCacheTimeSet = $this->config['params']['userCacheTimeSet'];
    //        $hotelId = $this->request->get("hotelId", "int")?$this->request->get("hotelId", "int"):$this->cookies->get("hotel_Id");
    //        if(!$hotelId){
    //            $hotelId = 1;
    //        }
    //        $hotelInfo = [];
    //        if ($hotelId) {
    //            $this->cookies->set("hotel_Id", $hotelId, $userCacheTimeSet);
    //            $sessionKey = 'hotelInfo_'.$hotelId;
    //            if(!$this->cookies->has($sessionKey)){
    //                //酒店信息
    //                $hotelService = new \MyApp\Services\HotelService();
    //                $hotelInfo = $hotelService->getHotelInfoById($hotelId, ['name','address']);
    //                //酒店相册模型
    //                $hotelPhotoModel = new \MyApp\Models\DvbHotelPhoto();
    //                $hotelPhotoData = $hotelPhotoModel->getHotelPhoto($hotelId);
    //                //封面图
    //                $hotelInfo['coverPic'] = $hotelPhotoData['coverPic'];
    //
    //                //保存酒店信息至cookies中
    //                $hotelInfo && $this->cookies->set($sessionKey, $hotelInfo, $userCacheTimeSet);
    //            }else{
    //                //获取cookies中的酒店信息
    //                $hotelInfo = $this->cookies->get($sessionKey);
    //            }
    //        }
    //        $staticVersion = '201608101605'; //静态资源版本号
    //
    //        $pathinfo = $_SERVER['REQUEST_URI'];
    //        //特殊页面是否登录判断
    //        if (!$this->cookies->get("securetKey")) {
    //            if (strpos(explode('?', $pathinfo)[0], '/front/pay') !== false || strpos(explode('?', $pathinfo)[0], '/front/userCenter') !== false || strpos(explode('?', $pathinfo)[0], '/front/index/book') !== false) {
    //                $url = $this->url->get($pathinfo);
    //                $url = urlencode($url);
    //                $this->response->redirect("front/user/login?hotelId=$hotelId&referUrl=$url");
    //            }
    //        }else{
    //            $userService = new UserService();
    //            $userData = $userService->checkSecuretKey($this->cookies->get("securetKey"));
    //            //检查securetKey是否正确或者过期
    //            if (isset($userData['code']) && $userData['code'] == 1000) {
    //                if ($userData["content"]["hotelId"] != $hotelId) {
    //                    $this->clearUserCookie();
    //                    $this->response->redirect("/front/user/login?hotelId=$hotelId");
    //                }
    //            }elseif (isset($userData['code']) && $userData['code'] == 1004){
    //                $this->clearUserCookie();
    //                $this->response->redirect("/front/user/login?hotelId=$hotelId");
    //            }
    //        }
    //        $this->view->setVar("hotel_Id", $hotelId);
    //        $this->view->setVar("hotelInfo", $hotelInfo);
    //        $this->view->setVar('staticVersion', $staticVersion);
    //
    //        //加入安全验证header字符串
    //        $safeService = new SafeService();
    //        $APIGEEHEADER2 = $safeService->apiHeaderEncrypt('js');
    //        $this->view->setVar('APIGEEHEADER2', $APIGEEHEADER2);
    //        $this->view->setVar('version', $this->config['params']['mmm_api']['version']);
    //        $this->view->setVar('system', $this->config['params']['mmm_api']['system']);
    //        $this->view->setVar('lang', $this->config['params']['mmm_api']['lang']);
    //   }

    /**
     * Encodes an arbitrary variable into JSON format
     *
     * @param mixed $var any number, boolean, string, array, or object to be encoded.
     * If var is a string, it will be converted to UTF-8 format first before being encoded.
     * @return string JSON string representation of input var
     */
//    protected function echoJson($data, $jsonp = "")
    //    {
    //        header("Content-type: application/json; charset=utf-8");
    //        if (empty($jsonp)) {
    //            echo json_encode($data);
    //        } else {
    //            echo $jsonp . '(' . json_encode($data) . ')';
    //        }
    //        exit();
    //    }

    /**
     * 接口返回结果规范
     * @param string $code 编码
     * @param string $message 消息
     * @param string $content 内容
     * @param string $callback 跨域
     */
//    protected function apiResultStandard($code, $message = null, $content = null, $callback = null)
    //    {
    //        //自动识别是否存在callback参数
    //        if (!$callback) {
    //            $callback = $this->request->get('callback', 'string', '');
    //        }
    //        $params = array(
    //            'code'      => $code,
    //            'message'   => $message,
    //            'timeStamp' => time(),
    //            'content'   => $content,
    //        );
    //        return $this->echoJson($params, $callback);
    //    }
    //
    //    /**
    //     * 将用户信息存入cookie
    //     * @param $user
    //     * @param $securetKey
    //     */
    //    public function setUserCookie($user, $securetKey)
    //    {
    //        $userCacheTimeSet = $this->config['params']['userCacheTimeSet'];
    //        //$this->cookies->set("user_data", $user, $userCacheTimeSet);
    //        $this->cookies->set("user_Id", $user['userId'], $userCacheTimeSet);
    //        $this->cookies->set("user_hotelId", $user['hotelId'], $userCacheTimeSet);
    //        $this->cookies->set("user_userPhone", $user['userPhone'], $userCacheTimeSet);
    //        $this->cookies->set("securetKey", $securetKey, $userCacheTimeSet);
    //    }

//    /**
    //     * 清除cookie的用户信息
    //     */
    //    public function clearUserCookie()
    //    {
    //        //$this->cookies->clear("user_data");
    //        $this->cookies->clear("user_Id");
    //        $this->cookies->clear("user_hotelId");
    //        $this->cookies->clear("user_userPhone");
    //        $this->cookies->clear("securetKey");
    //        $this->cookies->clear("hotel_Id");
    //    }

//    /**
    //     * 微信授权回调
    //     * @param $url
    //     */
    //    public function weixinOauth($url)
    //    {
    //        $options = array(
    //            'token'     => 'weixin', //填写你设定的key
    //            'appid'     => 'wx99596f906360376f', //填写高级调用功能的app id
    //            'appsecret' => 'd4624c36b6795d1d99dcf0547af5443d', //填写高级调用功能的密钥
    //        );
    //        $weObj = new Wechat($options);
    //        $url   = $weObj->getOauthRedirect($url, '', 'snsapi_base');
    //        $this->response->redirect($url, true);
    //    }
}
