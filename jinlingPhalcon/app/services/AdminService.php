<?php

namespace MyApp\Services;

use MyApp\Library\XSmtp;
use MyApp\Models\DvbAdmin;
use MyApp\Models\DvbAdminLogger;
use MyApp\Services\BaseService;


class AdminService extends BaseService
{
    /**
     * [traceLog 记录操作日志]
     * @param array $data
     * @return bool
     */
    public function traceLog($data = [])
    {
        $logger = new DvbAdminLogger();
        $result = $logger->insert($data);
        if ($result == false) {
            echo "日志插入失败 \n";
            foreach ($result->getMessages() as $message) {
                echo $message, "\n";
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * 密码进行加密
     * @param $username
     * @param $password 密码明文
     * @return string
     */
    public function hashPassword($username, $password)
    {
        return md5(md5($username) . md5($password) . $this->config->params['adminKeySalt']);
    }

    /**
     * 检查后台用户是否有效
     * @param $username
     * @param $password
     * @return array
     */
    public function isAdminValid($username, $password)
    {
        $result = array();
        $admin  = new DvbAdmin();
        $res    = $admin->get("*",['userName' => $username]);
        if ($res['id'] > 0 && $res['password'] == $this->hashPassword($username, $password)) {
            $result['isAdmin'] = true;
            $result['info']    = $res;
        } else {
            $result['isAdmin'] = false;
            $result['info']    = array();
        }
        return $result;
    }

    /**
     * 检查用户是否是后台用户
     * @param $username
     * @return mixed
     */
    public function isAdminUser($username)
    {
        $admin = new DvbAdmin();
        $res   = $admin->get("*",array('userName' => $username));
        return $res;
    }

    /**
     * 校验密码
     * @param $password
     * @return int
     */
    public function passwordValidator($password)
    {
        $score = 0;
        if (preg_match("/[0-9]+/", $password)) {
            $score++;
        }
        if (preg_match("/[a-zA-Z]+/", $password)) {
            $score++;
        }
        if (preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/", $password)) {
            $score += 2;
        } else {
            $score = 0;
        }
        if (strlen($password) >= 6) {
            $score++;
        } elseif (strlen($password) < 6) {
            $score = 0;
        }
        return $score;
    }

    /**
     * 验证邮箱
     */
    public function email($str)
    {
        if (empty($str)) {
            return true;
        }

        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if (strpos($str, '@') !== false && strpos($str, '.') !== false) {
            if (preg_match($chars, $str)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 发送邮件方法
     */

    public function sendmailto($mailto, $mailsub, $mailbd, $debug = true)
    {
        //require_once ('email.class.php');
        //##########################################
        $smtpserver     = "smtp.qq.com"; //SMTP服务器
        $smtpserverport = 25; //SMTP服务器端口
        $smtpusermail   = $this->config->params['smtp']['smtpuser']; //SMTP服务器的用户邮箱
        $smtpemailto    = $mailto;
        $smtpuser       = $this->config->params['smtp']['smtpuser']; //SMTP服务器的用户帐号
        $smtppass       = $this->config->params['smtp']['smtppass'];
        $mailsubject    = $mailsub; //邮件主题
        $mailsubject    = "=?UTF-8?B?" . base64_encode($mailsubject) . "?="; //防止乱码
        $mailbody       = $mailbd; //邮件内容
        //$mailbody = "=?UTF-8?B?".base64_encode($mailbody)."?="; //防止乱码
        $mailtype = "HTML"; //"HTML"; //邮件格式（HTML/TXT）,TXT为文本邮件. 139邮箱的短信提醒要设置为HTML才正常
        ##########################################
        //var_dump($smtpserver);exit;
        $smtp        = new XSmtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass); //这里面的一个true是表示使用身份验证,否则不使用身份验证.
        $smtp->debug = $debug; //是否显示发送的调试信息
        return $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
    }

}
