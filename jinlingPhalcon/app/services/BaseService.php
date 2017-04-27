<?php

namespace MyApp\Services;

use \Phalcon\DI;
use \Phalcon\DI\Injectable;

class BaseService extends Injectable
{
    public function __construct()
    {
        $di = DI::getDefault();
        $this->setDI($di);
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
}
