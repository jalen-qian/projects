<?php

namespace MyApp\Library;

use Phalcon\Acl;
use Phalcon\Mvc\User\Component;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use MyApp\Models\DvbAdminGroup;

/**
 * 权限访问类
 * @author zhaojianhui
 *
 */
class Access extends Component
{
    public $acl;
    public function __construct()
    {
        $this->acl = new AclList();
        // 设置默认访问级别为拒绝
        $this->acl->setDefaultAction(\Phalcon\Acl::DENY);
        
        $this->defineAcl();
    }
    /**
     * 定义acl
     */
    public function defineAcl()
    {
        //从数据库查询所有用户组数据（角色数据）
        $adminGroupModel = new DvbAdminGroup();
        $groupList = $adminGroupModel->select(['id','groupName','groupAcl'], ['statusIs'=>1]);
        //将角色列表添加到ACL
        foreach ($groupList as $groupV){
            $role = new Role('role_'.$groupV['id'], $groupV['groupName']);
            $this->acl->addRole($role);
        }
        //添加资源，一个控制器对应一个资源
        include_once APP_PATH . '/app/config/access.php';
        foreach ($aclList as $modelK => $modelV){
            foreach ($modelV['child'] as $conK => $conV){
                //资源名称
                $resourceName = new Resource($modelV['model'].'/'.$conV['controller']);
                $this->acl->addResource(strtolower($resourceName), array_map('strtolower', (array)array_column($conV['child'], 'action')));
            }
        }
        //设置后台公共资源，不在权限设置里面设置
        $publicResourceName = new Resource('admin/public','后台公共资源');
        $this->acl->addResource($publicResourceName, ['login','logout','forget','reset','captcha']);
        $uploadResourceName = new Resource('admin/upload','后台上传资源');
        $this->acl->addResource($uploadResourceName, ['image','file']);
        $localResourceName = new Resource('admin/admin','地区联动');
        $this->acl->addResource($localResourceName, ['citylist']);
        $syncResourceName = new Resource('admin/hotel','地区联动');
        $this->acl->addResource($syncResourceName, ['syncphoto']);
        
        //定义访问控制,根据用户组设置访问权限
        foreach ((array)$groupList as $groupK => $groupV){
            foreach ((array)$groupV['groupAcl'] as $aclK => $aclV){
                $this->acl->allow('role_'.$groupV['id'], strtolower($aclK), array_map('strtolower', $aclV));
            }
        }
        //设置宾客角色
        $roleGuests = new Role("guests");
        $this->acl->addRole($roleGuests);
        //设置公共资源全部用户都可访问
        $this->acl->allow('*', 'admin/public', '*');
        $this->acl->allow('*', 'admin/upload', '*');
        $this->acl->allow('*','admin/admin',['citylist']);
        $this->acl->allow('*','admin/hotel',['syncphoto']);
    }
    /**
     * 判断是否有权限
     * @param string $resourceName 资源名称，"模型名称/控制器名称"，和access配置文件中的model和controller值一一对应的，例如：admin/public
     * @param string $access 资源具体操作
     */
    public function isAllowed($resourceName, $access)
    {
        $groupId = (int)$this->session->get("_adminiGroupId");
        if ($groupId){
            return $this->acl->isAllowed('role_'.$groupId, strtolower($resourceName), strtolower($access) );
        }else{
            return $this->acl->isAllowed('guests', $resourceName, $access);
        }
    }
    /**
     * 获取权限配置列表
     */
    public function getAclConfigList()
    {
        //加载资源固定配置
        require APP_PATH . '/app/config/access.php';
        
        //超级管理员直接返回所有权限
        $groupId = (int)$this->session->get("_adminiGroupId");
        if ($groupId == 1){
            return $aclList;
        }
        
        //超级管理员专属控制器列表
        $supperConList = ['admin/pay','admin/facilities'];
        //超级管理员专属操作列表
        $supperActList = ['admin/admin/hotelList','admin/admin/hotelIndex','admin/admin/hotelDel'];
        
        foreach ($aclList as $modelKey => $modelVal){
            foreach ($modelVal['child'] as $conKey => $conVal){
                $controller = $modelVal['model'].'/'.$conVal['controller'];
                if (in_array($controller, $supperConList)){
                    unset($aclList[$modelKey]['child'][$conKey]);
                }
                foreach ($conVal['child'] as $actKey => $actVal){
                    if (in_array($controller.'/'.$actVal['action'], $supperActList)){
                        unset($aclList[$modelKey]['child'][$conKey]['child'][$actKey]);
                    }
                }
            }
        }
        return $aclList;
    }
}
