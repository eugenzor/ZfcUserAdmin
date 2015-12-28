<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ZfcUserAdmin\Service;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Db\TableGateway\TableGateway;

/**
 * Description of Roles
 *
 * @author eugene
 */
class Roles  implements ServiceLocatorAwareInterface 
{
    use ServiceLocatorAwareTrait;
    
    
    function getAvailableRolesDictionary()
    {
        $options = array();
        $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $rolesTable = new TableGateway('user_role', $db);
        $allRoles2 = $rolesTable->select();
        foreach($allRoles2 as $role){
            $options[$role->id] = $role->role_id;
        }
        return $options;
    }
    
    function updateUserRoles($userId, $newRoles)
    {
        if (is_object($userId)){
            $userId = $userId->getId();
        }
        $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $linker = new TableGateway('user_role_linker', $db);
        $linker->delete(array('user_id'=>$userId));


        if ($newRoles){
            foreach($newRoles as $role){
                $linker->insert(array('user_id'=>$userId, 'role_id'=>$role));
            }
        }
        
    }
    
}
