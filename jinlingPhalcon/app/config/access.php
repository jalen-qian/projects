<?php
$aclList = [
    ['name' => '后台管理', 'model' => 'admin', 'child' => [
        /* ['name' => '公共', 'controller' => 'public', 'child' => [
            ['name' => '登陆', 'action' => 'login'],
            ['name' => '退出', 'action' => 'logout'],
        ]], */
        ['name' => '管理员', 'controller' => 'admin', 'child' => [
            ['name' => '用户列表', 'action' => 'adminList'],
            ['name' => '用户添加/编辑', 'action' => 'index'],
            ['name' => '用户删除', 'action' => 'adminDelete'],
            ['name' => '平台酒店列表', 'action' => 'hotelList'],
            ['name' => '平台酒店添加/编辑', 'action' => 'hotelIndex'],
            ['name' => '平台酒店删除', 'action' => 'hotelDel'],
        ]],
        ['name' => '管理员组管理', 'controller' => 'admingroup', 'child' => [
            ['name' => '用户组列表', 'action' => 'index'],
            ['name' => '用户组添加/编辑', 'action' => 'editAcl'],
            ['name' => '用户组删除', 'action' => 'deleteGroup'],
        ]],
        ['name' => '酒店介绍', 'controller' => 'hotel', 'child' => [
            ['name' => '酒店基础信息', 'action' => 'hotelIntro'],
            ['name' => '酒店相册编辑', 'action' => 'hotelPhoto'],
        ]],
        ['name' => '房型管理', 'controller' => 'roomstyle', 'child' => [
            ['name' => '房型列表', 'action' => 'index'],
            ['name' => '房型添加/编辑', 'action' => 'roomStyleAE'],
            ['name' => '房型排序', 'action' => 'roomStyleSort'],
        ]],
        ['name' => '订单管理', 'controller'=>'order', 'child'=>[
            ['name' => '待处理订单列表', 'action' => 'index'],
            ['name' => '订单详情', 'action' => 'info'],
            ['name' => '接单/拒单', 'action' => 'operate'],
            ['name' => '确认退款', 'action' => 'refund'],
            ['name' => '今日入住订单列表', 'action' => 'checkInToday'],
            ['name' => '退款订单列表', 'action' => 'refunding'],
            ['name' => '订单查询', 'action' => 'list'],
            ['name' => '确认入住/安排房间', 'action' => 'handle'],
            ['name' => '获取房型对应的楼层及房间', 'action' => 'getFloorRoom'],
            ['name' => '导出订单', 'action' => 'export'],
            ['name' => '自动接单列表', 'action' => 'auto'],
            ['name' => '自动接单开启/关闭', 'action' => 'changeAuto'],
            ['name' => '自动接单保存', 'action' => 'saveAuto'],
        ]],
        ['name' => '售价/房态管理', 'controller' => 'product', 'child' => [
            ['name' => '售价&房态列表', 'action' => 'index'],
            ['name' => '售价&房态设置', 'action' => 'savePrice'],
            ['name' => '维护历史', 'action' => 'history'],
        ]],
        ['name' => '选房管理', 'controller' => 'selectroom', 'child' => [
            ['name' => '选房管理', 'action' => 'index'],
            ['name' => '待选订单房间列表接口', 'action' => 'canSelectRoomOrderList'],
            ['name' => '检查房间日期段内的状态接口', 'action' => 'checkRoomDateStatus'],
            ['name' => '选房\安排房间', 'action' => 'electionOrderRoom'],
            ['name' => '选房配置列表', 'action' => 'configlist'],
            ['name' => '选房配置编辑', 'action' => 'configEdit'],
            ['name' => '选房配置保存', 'action' => 'configSave'],
            ['name' => '选房配置删除', 'action' => 'configDelete'],
            ['name' => '楼层布局图保存', 'action' => 'floorPlanSave'],
            ['name' => '选房配置新增楼层', 'action' => 'saveFloor'],
            ['name' => '选房配置删除楼层', 'action' => 'configDeleteFloor'],
            ['name' => '操作选房状态', 'action' => 'operateSelectStatus'],
        ]],
        ['name' => '支付配置', 'controller' => 'pay', 'child' => [
            ['name' => '商户支付管理', 'action' => 'index'],
            ['name' => '商户支付新增/编辑', 'action' => 'edit'],
            ['name' => '商户支付删除', 'action' => 'delete'],
            ['name' => '支付类型管理', 'action' => 'typelist'],
            ['name' => '支付类型新增/编辑', 'action' => 'typeEdit'],
            ['name' => '支付类型删除', 'action' => 'typeDelete'],
        ]],
        ['name' => '设施管理', 'controller' => 'facilities', 'child' => [
            ['name' => '酒店设施列表', 'action' => 'index1'],
            ['name' => '房型设施列表', 'action' => 'index2'],
            ['name' => '设施新增/编辑', 'action' => 'add'],
            ['name' => '设施删除', 'action' => 'del'],
        ]],
    ]],
    /* ['name' => '接口', 'model' => 'api', 'child' => [

     ]],
     ['name' => '前台', 'model' => 'front', 'child' => [

     ]],*/
];
