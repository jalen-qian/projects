<?php
namespace Vendor\WechatPayment;
/**
 * @file WechatPayment.class.php
 * @description 微信支付类
 *
 * @author pysnow530, pysnow530@163.com
 * @date 2014年12月23日 17:24:47
 */
class WechatPayment
{

    /**
     * 微信支付配置数组
     * appid  公众账号
     * mch_id 商户号
     * key    加密key
     */
    private $_config;
    protected $appid;
    protected $mchid;
    protected $key;
    protected $appsecret;

//=======【curl代理设置】===================================
    /**
     * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
     * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
     * @var unknown_type
     */
    const CURL_PROXY_HOST = "0.0.0.0"; //"10.152.18.220";
    const CURL_PROXY_PORT = 0; //8080;
    /**
     * 错误信息
     */
    public $error = null;

    const PREPAY_GATEWAY = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    const QUERY_GATEWAY = 'https://api.mch.weixin.qq.com/pay/orderquery';
    const PAY_REFUND = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
    //const  LOCAL_URL = 'dvb-test.yaochufa.com/api/Payment/pay';
    //const LOCAL_URL = "http://api.yaochufa.com/v2/WeiXin/CallBackForMAuthorize?company=1&paySite=4&redirectUrl=http%3A%2F%2Fdvb-test.yaochufa.com%2Fapi%2FPayment%2Fpay%3ForderNo%3DAB123456789123";
    //const LOCAL_URL = "http://api.yaochufa.com/v2/WeiXin/CallBackForMAuthorize?company=1&paySite=4&redirectUrl=";//要出发
    const LOCAL_URL = "http://payapi.yaochufa.com/v2/Payment/CallBackForMAuthorize?company=2&paySite=3&redirectUrl=";//金游
    //证书路径
    const SSLCERT_PATH = APP_PATH . "/app/config/certificate/apiclient_cert.pem";
    const SSLKEY_PATH = APP_PATH . "/app/config/certificate/apiclient_key.pem";

    /**
     * @param $config 微信支付配置数组
     */
    public function __construct($appid, $mchid, $key, $appsecret)
    {
        //$this->_config = $config;
        $this->appid     = $appid;
        $this->mchid     = "$mchid";
        $this->key       = $key;
        $this->appsecret = $appsecret;
    }

    /**
     * 获取预支付ID
     * @param $body         商品描述
     * @param $out_trade_no 商户订单号
     * @param $total_fee    总金额(单位分)
     * @param $notify_url   通知地址
     * @param $trade_type   交易类型
     */
    public function getPrepayId($body, $out_trade_no, $total_fee,
        $notify_url, $openId, $trade_type = 'JSAPI'
    )
    {
        $data                     = array();
        $data['appid']            = $this->appid;
        $data['mch_id']           = $this->mchid;
        $data['nonce_str']        = $this->getNonceString();
        $data['body']             = $body;
        $data['out_trade_no']     = $out_trade_no;
        $data['total_fee']        = $total_fee;
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['notify_url']       = $notify_url;
        $data['trade_type']       = $trade_type;
        $data['openid']           = $openId;

        $this->log("post:" . json_encode($data), 'pay');
        $result = $this->post(self::PREPAY_GATEWAY, $data,false);
        $this->log("result:" . json_encode($result), 'pay');
        if ($result['return_code'] == 'SUCCESS') {
            return $result['prepay_id'];
        } else {
            $this->error = $result['return_msg'];
            return null;
        }
    }

    /**
     * 获取js支付使用的第二个参数
     */
    public function getPackage($prepay_id)
    {
        $data              = array();
        $nowTime           = time();
        $data['appId']     = $this->appid;
        $data['timeStamp'] = "$nowTime";
        $data['nonceStr']  = $this->getNonceString();
        $data['package']   = 'prepay_id=' . $prepay_id;
        $data['signType']  = 'MD5';
        $data['paySign']   = $this->sign($data);

        return $data;
    }

    /**
     * 获取发送到通知地址的数据(在通知地址内使用)
     * @return 结果数组，如果不是微信服务器发送的数据返回null
     *          appid
     *          bank_type
     *          cash_fee
     *          fee_type
     *          is_subscribe
     *          mch_id
     *          nonce_str
     *          openid
     *          out_trade_no    商户订单号
     *          result_code
     *          return_code
     *          sign
     *          time_end
     *          total_fee       总金额
     *          trade_type
     *          transaction_id  微信支付订单号
     */
    public function getBackData()
    {
        $xml  = file_get_contents('php://input');
        $data = $this->xml2array($xml);
        if ($this->validate($data)) {
            return $data;
        } else {
            return null;
        }
    }

    /**
     * 响应微信支付后台通知
     * @param $return_code 返回状态码 SUCCESS/FAIL
     * @param $return_msg  返回信息
     */
    public function responseBack($return_code = 'SUCCESS', $return_msg = null)
    {
        $data                = array();
        $data['return_code'] = $return_code;
        if ($return_msg) {
            $data['return_msg'] = $return_msg;
        }
        $xml = $this->array2xml($data);
        $this->log("notify res:" . json_encode($xml));
        print $xml;
    }

    /**
     * 订单查询接口
     * $param out_trade_no 商户订单号
     * $param type 为0时返回交易状态，为1时返回微信返回的数据
     * @return 字符串，交易状态
     *          SUCCESS     支付成功
     *          REFUND      转入退款
     *          NOTPAY      未支付
     *          CLOSED      已关闭
     *          REVOKED     已撤销
     *          USERPAYING  用户支付中
     *          NOPAY       未支付
     *          PAYERROR    支付失败
     *          null        订单不存在或其它错误，错误描述$this->error
     */
    public function queryOrder($out_trade_no,$type=0)
    {
        $data                 = array();
        $data['appid']        = $this->appid;
        $data['mch_id']       = $this->mchid;
        $data['out_trade_no'] = $out_trade_no;
        $data['nonce_str']    = $this->getNonceString();
        $result               = $this->post(self::QUERY_GATEWAY, $data,false);

        $this->log("query order res:" . json_encode($result), 'pay');
        if ($result['result_code'] == 'SUCCESS') {
            if($type) return $result;
            return $result['trade_state'];
        } else {
            $this->error = $result['err_code_des'];
            return null;
        }
    }

    public function array2xmlBak($array)
    {
        $xml = '<xml>' . PHP_EOL;
        foreach ($array as $k => $v) {
            $xml .= '<$k><![CDATA[$v]]></$k>' . PHP_EOL;
        }
        $xml .= '</xml>';

        return $xml;
    }

    public function xml2array($xml)
    {
        $array = array();
        foreach ((array)simplexml_load_string($xml) as $k => $v) {
            $array[$k] = (string)$v;
        }

        return $array;
    }

    /**
     * 数组转为xml字符
     * @throws WxPayException
     **/
    public function array2xml($array)
    {
        if (!is_array($array)
            || count($array) <= 0
        ) {
            exit("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * @param $url
     * @param $data
     * @param $useCert 是否需要证书
     * @return array|int
     * @throws Exception
     */
    public function post($url, $data, $useCert)
    {
        $data['sign'] = $this->sign($data);

        if (!function_exists('curl_init')) {
            throw new \Exception('Please enable php curl module!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->array2xml($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, self::SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, self::SSLKEY_PATH);
        }
        $content = curl_exec($ch);

        if($content){
            curl_close($ch);
            $array = $this->xml2array($content);
        }else{
            $array = curl_errno($ch);
            curl_close($ch);
        }
        return $array;
    }

    public function sign($data)
    {
        ksort($data);
        $string         = $this->toUrlParams($data);
        $stringSignTemp = $string . '&key=' . $this->key;

        $sign = strtoupper(md5($stringSignTemp));

        return $sign;

    }

    /**
     * 验证是否是腾讯服务器推送数据
     * @param $data 数据数组
     * @return 布尔值
     */
    public function validate($data)
    {
        if (!isset($data['sign'])) {
            return false;
        }

        $sign = $data['sign'];
        unset($data['sign']);

        return $this->sign($data) == $sign;
    }

    //返回随机字符串
    public function getNonceString()
    {
        return str_shuffle('pysnow530pysnow530pysnow530');
    }

    /**
     *
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     *
     * @return 用户的openid
     */
    public function getOpenid()
    {
        $this->log("get url:" . json_encode($_GET), 'pay');
        //通过code获得openid
        if (!isset($_GET['code'])) {
            //触发微信返回code码
            //$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING']);
            $baseUrl = urlencode(self::LOCAL_URL);
            $url     = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code   = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }

    public function getOpenidNet($returnUrl)
    {
        $this->log("get url:" . json_encode($_GET), 'pay');
        //通过code获得openid
        if (!isset($_GET['openId'])) {
            //触发微信返回code码
            //$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING']);
            $baseUrl = self::LOCAL_URL . urlencode($returnUrl);
            $baseUrl = urlencode($baseUrl);
            $url     = $this->__CreateOauthUrlForCode($baseUrl);

            Header("Location: $url");
            exit();
        }
        /*$param = $_GET;
        $param = json_decode(json);*/
        return $_GET['openId'];
    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"]         = $this->appid;
        $urlObj["redirect_uri"]  = "$redirectUrl";
        $urlObj["response_type"] = "code";
        //$urlObj["scope"]         = "snsapi_userinfo";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "AB123456789123" . "#wechat_redirect";
        $bizString       = $this->toUrlParams($urlObj);
        $this->log("授权url:" . "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString, 'pay');
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;

    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (WechatPayment::CURL_PROXY_HOST != "0.0.0.0"
            && WechatPayment::CURL_PROXY_PORT != 0
        ) {
            curl_setopt($ch, CURLOPT_PROXY, WechatPayment::CURL_PROXY_HOST);
            curl_setopt($ch, CURLOPT_PROXYPORT, WechatPayment::CURL_PROXY_PORT);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data       = json_decode($res, true);
        $this->data = $data;
        $openid     = $data['openid'];
        return $openid;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code ，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"]      = $this->appid;
        $urlObj["secret"]     = $this->appsecret;
        $urlObj["code"]       = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString            = $this->toUrlParams($urlObj);
        $this->log("access_token url:" . "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString, 'pay');
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function toUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    //打印日志
    public function log($errmsg, $fileName)
    {
        $path     = APP_PATH . '/app/runtime/';
        $filename = $path . $fileName . '.log';
        $fp2      = @fopen($filename, "a");
        fwrite($fp2, date('Y-m-d H:i:s') . '  ' . $errmsg . "\r\n");
        fclose($fp2);
    }

    /**
     * 生成sign
     * @return string
     */
    public function MakeSign($data=[])
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 退款
     * @param $info
     * @return array|null
     * @throws Exception
     */
    public function payRefund($info)
    {
        $data                   = array();
        $data['appid']          = $this->appid;
        $data['mch_id']         = $this->mchid;
        $data['nonce_str']      = $this->getNonceString();
        $data['transaction_id'] = $info['payOrderNo'];
        $data['out_refund_no']  = $info['outRefundNo'];
        $data['total_fee']      = $info['orderAmount']*100;
        $data['refund_fee']     = $info['orderAmount']*100;
        $data['op_user_id']     = $this->mchid;
        //$data['sign']           = $this->MakeSign($data);
        $result                 = $this->post(self::PAY_REFUND, $data,false);
        $this->log("pay refund res:" . json_encode($result), 'pay');
        if ($result['result_code'] == 'SUCCESS') {
            return $result;
        } else {
            $this->error = $result['err_code_des'];
            return $result['err_code_des'];
        }
    }
}
