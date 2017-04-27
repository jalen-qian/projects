<?php
namespace MyApp\Library;

use \Phalcon\Mvc\User\Component;

/**
 * 处理字符串（手机号码隐藏中间位数）
 * @author Jacent
 * @date 2016.7.27
 * Class Tool_String
 * @package MyApp\Library
 */
Class ToolString
{
    public static function subStrReplaceCn($string, $repalce = '*', $start = 0, $len = 0)
    {
        $count = mb_strlen($string, 'UTF-8'); //此处传入编码，建议使用utf-8。此处编码要与下面mb_substr()所使用的一致
        if (!$count) {
            return $string;
        }
        if ($len == 0) {
            $end = $count;  //传入0则替换到最后
        } else {
            $end = $start + $len;  //传入指定长度则为开始长度+指定长度
        }
        $i            = 0;
        $returnString = '';
        while ($i < $count) {  //循环该字符串
            $tmpString = mb_substr($string, $i, 1, 'UTF-8'); // 与mb_strlen编码一致
            if ($start <= $i && $i < $end) {
                $returnString .= $repalce;
            } else {
                $returnString .= $tmpString;
            }
            $i++;
        }
        return $returnString;
    }
}