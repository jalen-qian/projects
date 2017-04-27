<?php
/**
 * 公用controller，除了index其他页面不需要登录，切记此controller中的action为非必须登录action
 */
namespace MyApp\Controllers\Admin;

use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    //指定跳转到views下的admin
    public function afterExecuteRoute()
    {
        $this->view->setViewsDir($this->view->getViewsDir() . 'admin/');
    }

//    protected function initialize()
    //    {
    //
    //        $this->checkIsLogining();
    //        $this->setAllServiceToDI(); //注入service类
    //        $this->setDvbUser(); //供页面调用的全局用户信息
    //
    //        //权限认证
    //        $controllerName = $this->dispatcher->getControllerName();
    //        $actionName     = $this->dispatcher->getActionName();
    //        //判断是否有权限
    //        /*if (!$this->access->isAllowed('admin/'.$controllerName, $actionName)) {
    //        $this->dispatcher->forward(array(
    //        'namespace'  => 'MyApp\Controllers\Admin',
    //        'controller' => 'public',
    //        'action'     => 'message',
    //        'params'     => ['warning','禁止访问','admin/public/index','您暂无权限访问此操作，请联系管理员。'],
    //        ));
    //        }*/
    //        //$this->view->disable();
    //        //后台菜单显示
    //        $element          = new Elements();
    //        $this->view->menu = $element->getAdminMenu();
    //   }
    //    protected function _getTranslationMsg()
    //    {
    //        $language = isset($_COOKIE['ycf_user_select_lang']) ? $_COOKIE['ycf_user_select_lang'] : 'zh';
    //        $language = $language ? $language : 'zh';
    //        //Check if we have a translation file for that lang
    //        if (file_exists(APP_PATH . "/app/language/" . $language . "/message.php")) {
    //            require APP_PATH . "/app/language/" . $language . "/message.php";
    //            // require APP_PATH . "/app/language/" . $language . "/view.php";
    //        } else {
    //            // fallback to some default
    //            require APP_PATH . "/app/language/en/message.php";
    //            //require APP_PATH . "/app/language/en/view.php";
    //        }
    //
    //        return new NativeArray(
    //            array(
    //                "content" => $messages,
    //            )
    //        );
    //    }

//    protected function _getTranslationView()
    //    {
    //        $language = isset($_COOKIE['ycf_user_select_lang']) ? $_COOKIE['ycf_user_select_lang'] : 'zh';
    //        $language = $language ? $language : 'zh';
    //        //Check if we have a translation file for that lang
    //        if (file_exists(APP_PATH . "/app/language/" . $language . "/view.php")) {
    //            //require APP_PATH . "/app/language/" . $language . "/message.php";
    //            require APP_PATH . "/app/language/" . $language . "/view.php";
    //        } else {
    //            // fallback to some default
    //            //require APP_PATH . "/app/language/en/message.php";
    //            require APP_PATH . "/app/language/en/view.php";
    //        }
    //        return new NativeArray(
    //            array(
    //                "content" => $view,
    //            )
    //        );
    //    }

    /**
     * 将service类注入到DI
     */

//    public function setAllServiceToDI()
    //    {
    //        $this->di->setShared('mvcAdminService', function () {
    //            return new MvcAdminService();
    //        });
    //        $this->di->setShared('adminGroupService', function () {
    //            return new AdminGroupService();
    //        });
    //        $this->di->setShared('productService', function () {
    //            return new ProductService();
    //        });
    //        $this->di->setShared('access', function () {
    //            return new Access();
    //        });
    //    }

    /**
     * 页面信息提示
     * @param string $type 提示类型
     * @param string $msgTitle 提示标题
     * @param mixed $redirectUrl 跳转地址，内容是复合类型，使用url的get方法生成链接
     * @param string $msgCon 提示附加内容
     * @param number $waitSecond 等待描述
     */
//    public function message($type = 'success', $msgTitle = '操作成功', $redirectUrl = '', $msgCon = '', $waitSeconds = 3)
    //    {
    //        if ($redirectUrl) {
    //            $redirectUrl = $this->url->get($redirectUrl);
    //        } else {
    //            $redirectUrl = $_SERVER['HTTP_REFERER'];
    //        }
    //        switch ($type) {
    //            case 'error':
    //                //提示div样式名
    //                $this->view->boxClass = 'alert-danger';
    //                //提示图标样式
    //                $this->view->iconClass = 'icon-warning-sign';
    //                //$this->flash->error("too bad! the form had errors");
    //                break;
    //            case 'notice':
    //                $this->view->boxClass  = 'alert-info';
    //                $this->view->iconClass = 'icon-info-sign';
    //                //$this->flash->notice("this a very important information");
    //                break;
    //            case 'warning':
    //                $this->view->boxClass  = 'alert-warning';
    //                $this->view->iconClass = 'icon-exclamation-sign';
    //                //$this->flash->warning("best check yo self, you're not looking too good.");
    //                break;
    //            default:
    //                $this->view->boxClass  = 'alert-success';
    //                $this->view->iconClass = 'icon-check-circle';
    //                //$this->flash->success("yes!, everything went very smoothly");
    //        }
    //
    //        $this->view->msgTitle    = $msgTitle;
    //        $this->view->msgCon      = $msgCon;
    //        $this->view->redirectUrl = $redirectUrl;
    //        $this->view->waitSeconds = $waitSeconds;
    //
    //        $this->view->pick("public/message");
    //    }

    /**
     * 判断是否有消息输出
     * @return boolean
     */
//    public function hasMessage()
    //    {
    //        return isset($this->view->msgTitle) ? true : false;
    //    }

    /**
     * 全局登录判断
     */
//    public function checkIsLogining()
    //    {
    //        if ($this->dispatcher->getControllerName() !== "public" || $this->dispatcher->getActionName() == "index") {
    //            if (!$this->session->has('_adminiData')) {
    //                $this->response->redirect("/admin/public/login");
    //            }
    //        } elseif ($this->dispatcher->getControllerName() == "public" && $this->dispatcher->getActionName() == "login") {
    //            if ($this->session->has('_adminiData')) {
    //                $this->response->redirect("/admin/public/index");
    //            }
    //        }
    //    }

    /**
     * 设置用户信息，供全局页面调用
     */
//    public function setDvbUser()
    //    {
    //        if ($this->session->has('_adminiUserId')) {
    //            $userId = $this->session->get('_adminiUserId');
    //            $user   = $this->db->get("dvb_admin", "*", ["id" => $userId]);
    //            if (!empty($user)) {
    //                unset($user['password']);
    //                $this->view->DvbUser = $user;
    //            }
    //        }
    //    }

}
