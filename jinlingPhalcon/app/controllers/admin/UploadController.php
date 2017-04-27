<?php
namespace MyApp\Controllers\Admin;

use MyApp\Controllers\Admin\BaseController;
use MyApp\Library\Utilities;
use MyApp\Library\XHttp;
use Phalcon\Mvc\View;

class UploadController extends BaseController
{
    /**
     * 文件上传
     */
    public function fileAction()
    {
        $tmpname = $_FILES['file']['name'];
        $tmpfile = $_FILES['file']['tmp_name'];
        $tmpType = $_FILES['file']['type'];

        //公用类
        $utilities = new Utilities();

        //异步上传类
        $upload = new XHttp;
        $res    = $upload->upload($tmpname, $tmpfile);
        $result = is_string($res) ? json_decode($res, true) : $res;

        if (isset($result['message']) && $result['message'] == 'success') {
            $resJson['code'] = 1000;
            //拼接图片访问URL
            $imagStaticUrl = YII_ENV == 'local' ? $result['content']['dsfServer'] : $result['content']['dsfCdn'];
            $utilities->apiResultStandard(1000, '上传成功', ['key' => $result['content']['remoteUrl'], 'url' => $imagStaticUrl . $result['content']['remoteUrl']]);
        } else {
            $utilities->apiResultStandard(1001, '上传失败');
        }
        $this->view->disable();
    }
    /**
     * 图片上传
     */
    public function imageAction()
    {
        $tmpname = $_FILES['file']['name'];
        $tmpfile = $_FILES['file']['tmp_name'];
        $tmpType = $_FILES['file']['type'];
        //公用类
        $utilities = new Utilities();
        //图片类型验证
        if (strpos($tmpType, 'image') === false) {
            $message = '请上传正确图片类型';
            $utilities->apiResultStandard(1001, $message);
        }
        //图片的高宽验证
        $imgWight  = (int) $this->request->getPost('imgwight', 'int');
        $imgHeight = (int) $this->request->getPost('imgheight', 'int');
        if ($imgWight && $imgHeight) {
            $fileInfo = getimagesize($tmpfile);
            if ($fileInfo[0] != $imgWight || $fileInfo[1] != $imgHeight) {
                $message = '图片尺寸不是规定大小：' . $imgWight . 'px*' . $imgHeight . 'px';
                $utilities->apiResultStandard(1001, $message);
            }
        }

        //异步上传类
        $upload = new XHttp;
        $res    = $upload->upload($tmpname, $tmpfile);
        $utilities->log("upload-image: " . $res,"upload");
        $result = is_string($res) ? json_decode($res, true) : $res;

        if (isset($result['message']) && $result['message'] == 'success') {
            $resJson['code'] = 1000;
            //拼接图片访问URL
            $imagStaticUrl  = YII_ENV == 'local' ? $result['content']['dsfServer'] : $result['content']['dsfCdn'];
            $content        = $result['content'];
            $content['key'] = $content['remoteUrl'];
            $content['url'] = $imagStaticUrl . $result['content']['remoteUrl'];
            $utilities->apiResultStandard(1000, '上传成功', $content);
        } else {
            $utilities->apiResultStandard(1001, '上传失败');
        }
        $this->view->disable();
    }
}
