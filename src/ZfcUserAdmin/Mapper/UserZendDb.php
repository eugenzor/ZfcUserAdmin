<?php

namespace ZfcUserAdmin\Mapper;

use ZfcUser\Mapper\User as ZfcUserMapper;
use Zend\Db\ResultSet\HydratingResultSet;

class UserZendDb extends ZfcUserMapper
{
    public function findAll() 
    {
        $select = $this->getSelect($this->tableName);
        $select->order(array('username ASC', 'display_name ASC', 'email ASC'));
        //$resultSet = $this->select($select);

        $linker = new \Zend\Db\TableGateway\TableGateway('user_role_linker', $this->getDbSlaveAdapter());
        $links = $linker->select();
        $roleMap = array();
        foreach($links as $link){
            if (isset($roleMap[$link->user_id])){
                $roleMap[$link->user_id][]=$link->role_id;
            }else{
                $roleMap[$link->user_id] = array($link->role_id);
            }
        }

        $entity = $this->getEntityPrototype();
        $entity->setRoleMap($roleMap);
        $resultSet = new HydratingResultSet($this->getHydrator(), $entity);
        $adapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->getSlaveSql(), $resultSet);
        $paginator = new \Zend\Paginator\Paginator($adapter);

        return $paginator;
    }

    
    public function remove($entity)
    {
        $id = $entity->getId();
        $this->delete(array('user_id' => $id));        
    }
}
