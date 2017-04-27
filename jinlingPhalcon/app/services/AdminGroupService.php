<?php

namespace MyApp\Services;

use MyApp\Services\BaseService;

class AdminGroupService extends BaseService
{

    /**
     * 转换表单权限字符串为数组列表
     *
     * @param string $aclStr
     */
    public function parseStrToArray($aclStr = '')
    {
        $aclList    = [];
        $aclStrList = explode(',', $aclStr);
        foreach ($aclStrList as $v) {
            if ($v) {
                // 字符串切割
                $sourceList = explode(':', $v);
                $sourceKey  = $sourceList[0] . '/' . $sourceList[1];
                $sourceVal  = $sourceList[2];
                // 组装资源
                isset($aclList[$sourceKey]) || $aclList[$sourceKey] = array();
                $aclList[$sourceKey][]                              = $sourceVal;
            }
        }
        return $aclList;
    }
}
