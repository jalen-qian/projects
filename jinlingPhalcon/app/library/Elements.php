<?php
namespace MyApp\Library;

use MyApp\Models\DvbHotel;
use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View;
use MyApp\Library\Access;

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Elements extends Component
{
    /**
     * 后台管理菜单
     * @var [type]
     */
    private $adminMenu = [
        [
            'name' => '订单管理', 'url' => 'admin/order/index', 'child' => [
                ['name' => '待处理订单', 'url' => 'admin/order/index','childUrl'=>[

                ]],
                ['name' => '今日入住', 'url' => 'admin/order/checkInToday','childUrl'=>[

                ]],
                ['name' => '退款处理', 'url' => 'admin/order/refunding','childUrl'=>[

                ]],
                ['name' => '订单查询', 'url' => 'admin/order/list','childUrl'=>[

                ]],
                ['name' => '自动接单', 'url' => 'admin/order/auto'],
            ],
        ],
        [
            'name' => '选房管理', 'url' => 'admin/selectroom/index', 'child' => [
                ['name' => '选房管理', 'url' => 'admin/selectroom/index','childUrl'=>[
                    
                ]],
                ['name' => '选房配置', 'url' => 'admin/selectroom/configlist','childUrl'=>[
                    'admin/selectroom/configedit',
                ]],
            ],
        ],
        [
            'name' => '产品管理', 'url' => 'admin/roomstyle/index', 'child' => [
                ['name' => '房型管理', 'url' => 'admin/roomstyle/index','childUrl'=>[
                    'admin/roomstyle/roomStyleAE','admin/roomstyle/roomStyleSort'
                ]],
            ],
        ],
        [
            'name' => '售价/房态维护', 'url' => 'admin/product/index', 'child' => [
                ['name' => '售价&房态维护', 'url' => 'admin/product/index','childUrl'=>[
                    
                ]],
                ['name' => '维护历史', 'url' => 'admin/product/history','childUrl'=>[
                    
                ]],
            ],
        ],
        [
            'name' => '酒店介绍', 'url' => 'admin/hotel/hotelIntro', 'child' => [
                ['name' => '酒店基础信息', 'url' => 'admin/hotel/hotelIntro','childUrl'=>[
                    
                ]],
                ['name' => '酒店相册', 'url' => 'admin/hotel/hotelPhoto','childUrl'=>[
                    
                ]],
            ],
        ],
        [
            'name' => '支付配置', 'url' => 'admin/pay/index', 'child' => [
                ['name' => '商户支付管理', 'url' => 'admin/pay/index','childUrl'=>[
                        'admin/pay/edit','admin/pay/delete'
                    ]],
                ['name' => '支付类型管理', 'url' => 'admin/pay/typeList','childUrl'=>[
                        'admin/pay/typeEdit','admin/pay/typeDelete'
                    ]],
            ],
        ],
        [
            'name' => '系统配置', 'url' => 'admin/facilities/index1', 'child' => [
                ['name' => '酒店设施配置', 'url' => 'admin/facilities/index1','childUrl'=>[
                    
                ]],
                ['name' => '房间设施配置', 'url' => 'admin/facilities/index2','childUrl'=>[
                    
                ]],
            ],
        ],
        [
            'name' => '用户管理', 'url' => 'admin/admin/adminList', 'child' => [
                ['name' => '用户', 'url' => 'admin/admin/adminList','childUrl'=>[
                    'admin/admin/index','admin/admin/adminDelete',//子菜单url集合
                ]],
                ['name' => '用户组', 'url' => 'admin/admingroup/index','childUrl'=>[
                    'admin/admingroup/editAcl','admin/admingroup/deleteGroup',
                ]],
                ['name' => '平台酒店', 'url' => 'admin/admin/hotelList','childUrl'=>[
                    'admin/admin/hotelIndex','admin/admin/hotelDel',
                ]],
            ],
        ],
    ];
    public function __construct()
    {
        //过滤掉无法访问的URL
        $this->filterMenu();
    }
    /**
     * 获取后台管理菜单
     * @return string
     */
    public function getAdminMenu()
    {
        $controllerName = $this->view->getControllerName();
        $actionName     = $this->view->getActionName();
        
        $fullUrl = $_SERVER['REQUEST_URI'];
        $parseUrl = parse_url($fullUrl);
        $nowNavKey = -1;
        //一级菜单
        $menuStr = '<div class="mainmenu"><div class="pagewraper">';
        foreach ($this->adminMenu as $k => $v){
            //收集子类URL
            $childUrlList = array_column($v['child'], 'url');
            foreach ((array)array_column($v['child'], 'childUrl') as $childUrl){
                $childUrlList = array_merge($childUrlList, $childUrl);
            }
            //判断当前URL所在的navbar
            if (in_array(strtolower(substr($parseUrl['path'],1)), array_map('strtolower', $childUrlList))) {
                $nowNavKey = $k;
            }
            $menuStr .= '<a href="'.$this->url->get($v['url']).'" class="'.($k==$nowNavKey ? 'current' : '').'">'.$v['name'].'</a>';
        }
        $menuStr .= '</div></div>';
        //二级菜单
        $menuStr .= '<div class="secondmenu"><div class="pagewraper">';
        if (isset($this->adminMenu[$nowNavKey]) && $this->adminMenu[$nowNavKey]){
            foreach ((array)$this->adminMenu[$nowNavKey]['child'] as $v){
                //收集子类url
                $childUrlList = [$v['url']];
                isset($v['childUrl']) && $childUrlList = array_merge($childUrlList, $v['childUrl']);
                if($v['name']=='待处理订单'){
                    $menuStr .= '<a href="'.$this->url->get($v['url']).'" class="'.(in_array(strtolower(substr($parseUrl['path'],1)), array_map('strtolower', $childUrlList) ) ? 'current' : '').'">'.$v['name'].'<span  id="index_count" style="display:none;"></span>'.'</a>';
                }elseif($v['name']=='退款处理'){
                    $menuStr .= '<a href="'.$this->url->get($v['url']).'" class="'.(in_array(strtolower(substr($parseUrl['path'],1)), array_map('strtolower', $childUrlList) ) ? 'current' : '').'">'.$v['name'].'<span  id="refund_count" style="display:none;"></span>'.'</a>';
                }elseif($v['name']=='自动接单'){
                    $hotelM = new DvbHotel();
                    $hotelId = $this->session->get('_adminiHotelId');
                    $isAutoOrder = $hotelM->get("isAutoOrder",["id"=>$hotelId]);
                    if($isAutoOrder==1){
                        $menuStr .= '<a href="'.$this->url->get($v['url']).'" class="'.(in_array(strtolower(substr($parseUrl['path'],1)), array_map('strtolower', $childUrlList) ) ? 'current' : '').'">'.$v['name'].'</a>';
                    }
                }else{
                    $menuStr .= '<a href="'.$this->url->get($v['url']).'" class="'.(in_array(strtolower(substr($parseUrl['path'],1)), array_map('strtolower', $childUrlList) ) ? 'current' : '').'">'.$v['name'].'</a>';
                }
            }
        }
        $menuStr .= '</div></div>';
        return $menuStr;
    }
    /**
     * 判断是否有权限
     * @param string $url
     */
    private function isAllowed($url){
        $urlArr = explode('/', $url);
        return $this->access->isAllowed($urlArr[0].'/'.$urlArr[1], $urlArr[2]);
    }
    /**
     * 根据用户角色过滤菜单
     */
    public function filterMenu()
    {
        foreach ($this->adminMenu as $k => $v){
            $validChildUrl = '';
            //判断一级目录是否有权限
            if ($this->isAllowed($v['url'])){
                $validChildUrl = $v['url'];
            }
            foreach ((array)$v['child'] as $kk => $vv){
                //判断自目录是否有权限
                if ($this->isAllowed($vv['url'])){
                    $validChildUrl || $validChildUrl = $vv['url'];                    
                }else{
                    unset($v['child'][$kk]);
                }
            }
            if ($validChildUrl){
                //如果在子集目录中发现有效url，则赋值到一级目录
                $v['url'] = $validChildUrl;
                $this->adminMenu[$k] = $v;                
            }else{
                unset($this->adminMenu[$k]);
            }
        }
    }
}
