<?php
namespace MyApp\Models;

use MyApp\Models\Model;

/**
 * 代码临时存放的地方，有些代码删除了又觉得可能会用到，可以复制到这里
 */
class Temp extends Model
{

    //curd增删改查示范
    public function curdAction()
    {
        //select
        //$id && $where = 'id =:id';

        $sql  = 'select * from counters where id = :id and name = :name ';
        $bind = [
            ':id'   => 2,
            ':name' => 'kcloze',
        ];
        $result = $this->db->queryOne($sql, $bind);
        var_dump($result);
        $result = $this->db->getLastSql(false);
        var_dump($result);

        //count
        $result = $this->db->count('counters', 'id > ?', [2]);
        var_dump($result);
        $result = $this->db->getLastSql(false);
        var_dump($result);
        //另外一种占位符号
        $result = $this->db->count('counters', 'id > :id', [':id' => 2]);
        var_dump($result);
        $result = $this->db->getLastSql(false);
        var_dump($result);

        //insert
        $data = [
            'name'  => 'kcloze',
            'value' => time(),
        ];

        $result = $this->db->insert('counters', $data);
        var_dump($result);
        $result = $this->db->getLastSql(false);
        var_dump($result);

        //update
        $data = [
            'name'  => 'kcloze',
            'value' => time(),
        ];
        //如果where为空，自动会加上1=2的逻辑，避免全表数据被更新
        $where  = ['id' => 2];
        $result = $this->db->update('counters', $data, $where);
        var_dump($result);
        $result = $this->db->getLastSql(false);
        var_dump($result);

        //delete
        //如果where为空，自动会加上1=2的逻辑，避免数据库数据被误伤
        $where = [
            'name' => 'kcloze',
            'id'   => 2,
        ];
        $result = $this->db->delete('test.counters', $where); //$where之间的关系为and
        var_dump($result);
        $result = $this->db->getLastSql(false);
        var_dump($result);

    }

    public function easydbAction()
    {

        //搜索表单举例
        $id        = $this->request->get('id', 'int');
        $name      = $this->request->get('name', 'string');
        $timestamp = $this->request->get('timestamp', 'int');

        $where = ' where 1=1 ';
        $bind  = [];
        if (!empty($id)) {
            $where .= 'and id =:id';
            $bind[':id'] = $id;
        }
        if (!empty($name)) {
            $where .= 'and name like %:name%';
            $bind[':name'] = $name;
        }
        if (!empty($timestamp)) {
            $where .= 'and timestamp > :timestamp';
            $bind[':timestamp'] = $timestamp;
        }

        $sql    = 'select count(*) from users ' . $where;
        $result = $this->db2->queryOne($sql, $bind);
        var_dump($result);
        $sql = $this->db2->getLastSql(false);
        var_dump($sql);
        $sql    = 'select * from users ' . $where;
        $result = $this->db2->queryAll($sql, $bind);
        var_dump($result);
        $sql = $this->db2->getLastSql(false);
        var_dump($sql);
        $this->view->disable();

    }

}
