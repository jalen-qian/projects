<?php
namespace MyApp\Tasks;

class MainTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        //$countersService = new CountersService;
        //$countersService->demo();
        echo "Congratulations! You are now flying with Phalcon CLI!";
    }

}
