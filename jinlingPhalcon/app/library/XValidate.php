<?php

namespace MyApp\Library;

/**
 * 验证类
 *
 *
 */
class XValidate
{
    /**
     * 需要验证的数据
     *
     * @var
     */
    protected $field;

    /**
     * 错误信息
     *
     * @var
     */
    protected $message = [];

    /**
     * 设置要验证的数据
     *
     * @param $data
     */
    public function field($data)
    {
        $this->field = $data;
        return $this;
    }

    /**
     * 不能为空
     *
     * @return $this
     */
    public function required($msg = '')
    {
        if (empty($this->field)) {
            $this->message[] = empty($msg) ? $this->field . "不能为空" : $msg;
        }

        return $this;
    }

    /**
     * 邮箱验证
     *
     * @return $this
     */
    public function email($msg = '')
    {
        if (filter_var($this->field, FILTER_VALIDATE_EMAIL) === false) {
            $this->message[] = empty($msg) ? "邮箱格式不正确" : $msg;
        }

        return $this;
    }

    /**
     * 最大长度验证
     *
     * @param $len
     * @return $this
     */
    public function maxlen($len, $msg = '')
    {
        if (mb_strlen($this->field, 'utf-8') > $len) {
            $this->message[] = empty($msg) ? $this->field . "最大长度为{$len}" : $msg;
        }

        return $this;
    }

    /**
     * 最小长度验证
     *
     * @param $len
     */
    public function minlen($len, $msg = '')
    {
        if (mb_strlen($this->field, 'utf-8') < $len) {
            $this->message[] = empty($msg) ? $this->field . "最小长度为{$len}" : $msg;
        }

        return $this;
    }

    /**
     * 验证网址
     *
     * @param string $msg
     * @return $this
     */
    public function http($msg = '')
    {
        if (filter_var($this->field, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) === false) {
            $this->message[] = empty($msg) ? "网址格式错误" : $msg;
        }

        return $this;
    }

    /**
     * 验证手机号码
     *
     * @param string $msg
     * @return $this
     */
    public function phone($msg = '')
    {
        $preg = "/^\d{11}$/";
        if (!preg_match($preg, $this->field)) {
            $this->message[] = empty($msg) ? "手机格式错误" : $msg;
        }

        return $this;
    }

    /**
     * 身份证验证
     *
     * @param string $msg
     */
    public function identity($msg = '')
    {
        $preg = "/^(\d{15}|\d{18})$/";
        if (!preg_match($preg, $this->field)) {
            $this->message[] = empty($msg) ? "身份证格式错误" : $msg;
        }

        return $this;
    }

    /**
     * 验证数字范围
     *
     * @param $min
     * @param $max
     * @param string $msg
     * @return $this
     */
    public function num($min, $max, $msg = '')
    {
        if (!($this->field >= $min && $this->field <= $max)) {
            $this->message[] = empty($msg) ? "数字范围错误" : $msg;
        }

        return $this;
    }

    /**
     * 正则验证
     *
     * @param $preg
     * @param string $msg
     * @return $this
     */
    public function regexp($preg, $msg = '')
    {
        if (!preg_match($preg, $this->field)) {
            $this->message[] = empty($msg) ? "正则验证失败" : $msg;
        }

        return $this;
    }

    /**
     * 两次输入对比
     *
     * @param $value
     * @param string $msg
     * @return $this
     */
    public function confirm($value, $msg = '')
    {
        if ($value != $this->field) {
            $this->message[] = empty($msg) ? "两次输入不一致" : $msg;
        }

        return $this;
    }

    /**
     * 中文验证
     *
     * @param string $msg
     * @return $this
     */
    public function china($msg = '')
    {
        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-z0-9]+$/ui', $this->field)) {
            $this->message[] = empty($msg) ? "中文验证失败" : $msg;
        }

        return $this;
    }

    /**
     * 获得验证结果
     *
     * @return array|bool
     */
    public function get()
    {
        if (empty($this->message)) {
            return true;
        }

        return $this->message;
    }
    /**
     * 数字和字母验证
     *
     * @return bool
     */
    public function numericAndLetter()
    {
        $preg = '/^[0-9A-Za-z]{1,}$/';
        return preg_match( $preg, $this->field);
    }
}
