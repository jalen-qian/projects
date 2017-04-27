<?php
namespace MyApp\Library;

use \Phalcon\Mvc\User\Component;

/**
 * Talon\Utilities\Utilities
 * Various utility methods for use in Talon
 */
class Utilities extends Component
{
    public function camelSeparate($string, $separator = ' ')
    {
        return ucwords(preg_replace("/(?=[A-Z])/", "$separator$1", $string));
    }

    /**
     * 打印函数
     * $arr 需要输出的数组
     */
    public function p($arr)
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }

    /**
     * 获取客户端IP地址
     */
    public function getClientIP()
    {
        static $ip = null;
        if ($ip !== null) {
            return $ip;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
        return $ip;
    }

    /**
     * 循环创建目录
     */
    public function mkdir($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }

        if (!mk_dir(dirname($dir), $mode)) {
            return false;
        }

        return @mkdir($dir, $mode);
    }

    /**
     * 格式化单位
     */
    public function byteFormat($size, $dec = 2)
    {
        $a   = array("B", "KB", "MB", "GB", "TB", "PB");
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $a[$pos];
    }

    /**
     * Encodes an arbitrary variable into JSON format
     *
     * @param mixed $var any number, boolean, string, array, or object to be encoded.
     * If var is a string, it will be converted to UTF-8 format first before being encoded.
     * @return string JSON string representation of input var
     */
    public function echoJson($data, $jsonp = "")
    {
        //header("Content-type: application/json; charset=utf-8");
        header("Content-type: text/html; charset=utf-8"); // json结构用html格式传输，以便兼容低版本ie
        $this->view->disable();
        if (empty($jsonp)) {
            echo json_encode($data);
        } else {
            echo $jsonp . '(' . json_encode($data) . ')';
        }

    }

    /**
     * 接口返回结果规范
     * @param string $code 编码
     * @param string $message 消息
     * @param string $content 内容
     * @param string $callback 跨域
     */
    public function apiResultStandard($code, $message = null, $content = null, $callback = null)
    {
        $params = array(
            'code'      => $code,
            'message'   => $message,
            'timeStamp' => time(),
            'content'   => $content,
        );
        exit($this->echoJson($params, $callback));
    }

    /**
     * [geraHash 生成随机长度字符串]
     * @param  [type] $len [长度]
     * @return [type]      [随机字符串]
     */
    public function geraHash($len)
    {
        if ($len < 1) {
            return false;
        }
        return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', $len)), 0, $len);

    }

    /**
     * 使用STD3Des类加密字符串
     * @param string $str 加密字符串
     * @param string $keyPrefix 密钥类型
     * @return string
     */
    public function apiSTD3Encrypt($str, $keyPrefix = 'apiDesc')
    {

        $key         = $this->config['params']['apiDesc'][$keyPrefix]['key'];
        $iv          = $this->config['params']['apiDesc'][$keyPrefix]['iv'];
        $des         = new \MyApp\Library\STD3Des(base64_encode($key), base64_encode($iv));
        $securityStr = urlencode($des->encrypt($str));
        return $securityStr;
    }

    /**
     * 解密STD3Des类加密字符串
     * @param string $securityStr 加密字符串
     * @param string $keyPrefix 密钥类型
     * @return string
     */
    public function apiSTD3Decrypt($securityStr, $keyPrefix = 'apiDesc')
    {

        //$securityStr = urldecode($securityStr);
        if (base64_decode($securityStr)) {
            $key      = $this->config['params']['apiDesc'][$keyPrefix]['key'];
            $iv       = $this->config['params']['apiDesc'][$keyPrefix]['iv'];
            $des      = new \MyApp\Library\STD3Des(base64_encode($key), base64_encode($iv));
            $realyStr = $des->decrypt($securityStr);
            return $realyStr;
        } else {
            return '';
        }
    }

    /**
     *
     * 多维数组根据指定数组顺序和字段排序
     * @param array $array 待排序数组
     * @param array $sarray 指定排序数组
     * @param string $word 排序字段(唯一性)
     */
    public function arrSortByArr($array, $sarray, $word)
    {
        if (empty($array) || empty($sarray) || empty($word)) {
            return $array;
        }
        $tarr = array();
        foreach ((array)$array as $row) {
            $tarr[$row[$word]] = $row;
        }
        $array = array();
        foreach ((array)$sarray as $val) {
            $array[] = $tarr[$val];
        }
        unset($tarr);
        $array = array_filter($array);
        return $array;
    }

    /**
     * 获取数字随机字符串
     * @param $len
     * @return string
     */
    public function genRandomString($len)
    {
        //$chars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $chars    = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
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
     * 验证手机号码
     * @param $str
     * @return bool|int
     */
    public function mobile($str) {
        if (empty($str)) {
            return true;
        }
        return preg_match('#^1[3-9][\d]{9}$#', $str);
    }
}
