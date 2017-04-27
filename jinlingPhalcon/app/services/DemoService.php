<?php

namespace MyApp\Services;

use MyApp\Models\Counters;
use MyApp\Services\BaseService;

class DemoService extends BaseService
{

    public function demo()
    {

        $this->logger->critical("service demo! ");
        //$counters = new Counters();
        $result = $this->getByQuery();

        foreach ($result as $value) {
            var_dump($value->id);
            var_dump($value->name);
            var_dump($value->value);
        }

        $result = $this->getByQueryBuilder();
        foreach ($result as $value) {
            var_dump($value->id);
            var_dump($value->name);
            var_dump($value->value);
        }
        $this->findOrm();

    }

    public function getData()
    {
        $cacheKey = 'my.cache';

        $data = $this->cache->get($cacheKey);
        if ($data === null) {
            $data = array('kcloze', 'hello');
            $this->cache->save($cacheKey, $data, 3600);
        }
        var_dump($this->cache->get($cacheKey));
    }

    //手写sql
    public function getByQuery()
    {
        $sql      = "SELECT * FROM MyApp\Models\Counters";
        $counters = $this->modelsManager->executeQuery($sql);
        //var_dump($this->modelsManager, 123);
        return $counters;
    }
    //mysql链式操作
    public function getByQueryBuilder()
    {
        $counters = $this->modelsManager->createBuilder()
            ->from('MyApp\Models\Counters')
            ->getQuery()
            ->execute();
        return $counters;
    }
    //orm 操作
    public function findOrm()
    {
        //简单查询
        $row = Counters::findFirst(1);
        if ($row) {
            var_dump($row->name);
        }

        //绑定参数
        // Query robots binding parameters with string placeholders
        $conditions = "name = :name: AND value = :value:";
        // Parameters whose keys are the same as placeholders
        $parameters = array(
            "name"  => "kcloze",
            "value" => "101",
        );
        // Perform the query
        $row = Counters::findFirst(
            array(
                $conditions,
                "bind" => $parameters,
            )
        );
        if ($row) {
            var_dump($row->name);
        } else {
            echo 'not find';
        }
    }

}
