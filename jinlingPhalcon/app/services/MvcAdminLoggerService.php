<?php

namespace MyApp\Services;

use MyApp\Services\BaseService;

class MvcAdminLoggerService extends BaseService
{
    /**
     * 根据类型转换成中文描述
     * @param $catalog
     * @return string
     */
    public function transForm($catalog)
    {
        switch ($catalog) {
            case 'login':
                return '登录';
                break;
            case 'logout':
                return '退出';
                break;
            case 'create':
                return '录入';
                break;
            case 'delete':
                return '删除';
                break;
            case 'update':
                return '编辑';
                break;
            case 'other':
                return '其他';
                break;
            default:
                return '未知';
                break;
        }
    }

    /**
     * 联表查询管理员日志
     * @param array $columns
     * @param string $conditions
     * @param array $bind
     * @param string $order
     * @param int $limit
     * @return mixed
     */
    public function fetchLoggerList($columns = array(), $conditions = '', $bind = array(), $order = '\MyApp\Models\MvcAdminLogger.id desc', $limit = 0)
    {
        $dbStr   = 'MyApp\Models\MvcAdminLogger';
        $linkStr = 'MyApp\Models\MvcAdmin';
        $sql     = $dbStr::query();
        if (!empty($columns)) {
            $sql->columns($columns);
        }
        $sql->leftJoin($linkStr, 'MyApp\Models\MvcAdmin.id = MyApp\Models\MvcAdminLogger.user_id');
        if (!empty($conditions)) {
            $sql->where($conditions);
        }
        if (!empty($bind)) {
            $sql->bind($bind);
        }
        if ($order) {
            $sql->order($order);
        }
        if ($limit) {
            $sql->limit($limit);
        }
        $dataList = $sql->execute()->toArray();
        return $dataList;
    }

    /**
     * 删除数据
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $selectResult = $this->getOneData($id);
        if (empty($selectResult)) {
            $return['code']    = 1001;
            $return['message'] = '找不到相关数据';
        } else {
            $result = $this->deteleData($id);
            if ($result) {
                $return['code']    = 1000;
                $return['message'] = '删除成功！';
            } else {
                $return['code']    = 1001;
                $return['message'] = '删除失败！';
            }
        }
        return $return;

    }

}
