<?php
namespace MyApp\Controllers;

use MyApp\Controllers\BaseController;

class ErrorsController extends BaseController
{
    public function initialize()
    {
        //$this->tag->setTitle('Oops!');
        parent::initialize();
    }

    public function show404Action()
    {

    }

    public function show401Action()
    {

    }

    public function show500Action()
    {

    }
}
