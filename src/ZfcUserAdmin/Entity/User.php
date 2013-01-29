<?php

namespace ZfcUserAdmin\Entity;
use ZfcUser\Entity\User as Zfcuser;
/**
 * Created by JetBrains PhpStorm.
 * User: eugene
 * Date: 1/29/13
 * Time: 2:56 PM
 * To change this template use File | Settings | File Templates.
 */
class User extends Zfcuser
{
    protected $roleMap;

    function setRoleMap($roleMap)
    {
        $this->roleMap = $roleMap;
    }

    function roles()
    {
        $id = $this->getId();
        if (isset($this->roleMap[$id])){
            return $this->roleMap[$id];
        }
        return array();
    }
}
