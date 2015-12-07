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
    protected $salt='OybedVew5';

    function setRoleMap($roleMap)
    {
        $this->roleMap = $roleMap;
    }

    function fetchRoles()
    {
        $id = $this->getId();
        if (isset($this->roleMap[$id])){
            return $this->roleMap[$id];
        }
        return array();
    }

    function fetchRolesString()
    {
        return join(", ", $this->fetchRoles());
    }
    
    function setSalt($salt)
    {
        $this->salt = $salt;
    }
    
    /**
     * Get user confirmation key
     * @return string Confirmation key
     */
    public function obtainConfirmationKey()
    {
        return md5($this->getId() . $this->salt);
        
    }
    
    /**
     * Check if confirmation key correct
     * @param type $checkedKey
     * @return bool result of confiration check
     */
    public function isConfirmationKeyCorrect($checkedKey)
    {
        return ($this->obtainConfirmationKey() == $checkedKey);
    }
}
