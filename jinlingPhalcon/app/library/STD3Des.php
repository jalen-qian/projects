<?php
namespace MyApp\Library;

class STD3Des
{
    private $key = "";
    private $iv  = "";

    /**
     * 构造，传递二个已经进行base64_encode的KEY与IV
     *
     * @param string $key
     * @param string $iv
     */
    public function __construct($key, $iv)
    {
        if (empty($key) || empty($iv)) {
            echo 'key and iv is not valid';
            exit();
        }
        $this->key = $key;
        $this->iv  = $iv;
    }

    /**
     *加密
     * @param <type> $value
     * @return <type>
     */
    public function encrypt($value)
    {
        $td    = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $iv    = base64_decode($this->iv);
        $value = $this->paddingPKCS7($value);
        $key   = base64_decode($this->key);
        mcrypt_generic_init($td, $key, $iv);
        $ret = base64_encode(mcrypt_generic($td, $value));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }

    /**
     *解密
     * @param <type> $value
     * @return <type>
     */
    public function decrypt($value)
    {
        $td  = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $iv  = base64_decode($this->iv);
        $key = base64_decode($this->key);
        mcrypt_generic_init($td, $key, $iv);
        $ret = trim(mdecrypt_generic($td, base64_decode($value)));
        $ret = $this->unPaddingPKCS7($ret);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }

    private function paddingPKCS7($data)
    {
        $block_size   = mcrypt_get_block_size('tripledes', 'cbc');
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

    private function unPaddingPKCS7($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}

/*使用
include('STD3Des.class.php');
$key='yaochufa';
$iv='';
$msg='1D39111057DDDA9261218F5E048C8BBE';
$des=new STD3Des(base64_encode($key),base64_encode($iv));
$rs1=$des->encrypt($msg);
echo $rs1.'<br />';
$rs2=$des->decrypt($msg);
echo $rs2;*/
