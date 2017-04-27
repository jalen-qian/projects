<?php

namespace MyApp\Controllers\Api;

use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    protected function initialize()
    {
        //        $openId = "";
        //
        //        //$this->tag->prependTitle('Ycf | ');
        //        //$this->view->setTemplateAfter('main');
        //        $safeService = new SafeService();
        //        if(isset($_REQUEST['APIGEEHEADER2'])){
        //            $safeService->checkHttpHeaders();
        //        }
        //        $this->view->disable();
    }

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
    //
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
    //        if (!$callback){
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

    // protected function forward($uri)
    // {
    //     $uriParts = explode('/', $uri);
    //     $params   = array_slice($uriParts, 2);
    //     return $this->dispatcher->forward(
    //         array(
    //             'controller' => $uriParts[0],
    //             'action'     => $uriParts[1],
    //             'params'     => $params,
    //         )
    //     );
    // }
}
