<?php

namespace MyApp\Controllers;

use MyApp\Controllers\BaseController;

class TestController extends BaseController
{

    public function indexAction()
    {

        $this->view->setVar("title", "phalcon template");

    }

}
