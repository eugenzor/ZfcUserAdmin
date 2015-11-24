<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ZfcUserAdmin\Service;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

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
        $allRoles2 = $this->serviceLocator->get('BjyAuthorize\Service\RoleDbTableGateway')->select();
        foreach($allRoles2 as $role){
            $options[$role->id] = $role->role_id;
        }
        return $options;
    }
}
