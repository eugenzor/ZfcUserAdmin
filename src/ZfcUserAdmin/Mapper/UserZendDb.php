<?php

namespace ZfcUserAdmin\Mapper;

use ZfcUser\Mapper\User as ZfcUserMapper;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Paginator;
use Zend\Db\Sql\Select;

class UserZendDb extends ZfcUserMapper
{
    protected $roleMap;

    public function getRoleMap()
    {
        if ($this->roleMap){
            return $this->roleMap;
        }

        $linker = new \Zend\Db\TableGateway\TableGateway('user_role_linker', $this->getDbSlaveAdapter());

        // get roles associated with the logged in user
        $sql = new Select();
        $sql->from('user_role_linker');
        // @todo these fields should eventually be configurable
        $sql->join('user_role', 'user_role.id = user_role_linker.role_id');

        $results = $linker->selectWith($sql);
        foreach($results as $link){
            if (isset($roleMap[$link->user_id])){
                $roleMap[$link->user_id][$link->id]=$link->role_id;
            }else{
                $roleMap[$link->user_id] = array($link->id=>$link->role_id);
            }
        }
        $this->roleMap = $roleMap;
        return $roleMap;
    }

    public function findAll()
    {
        $select = $this->getSelect($this->tableName);
        $select->order(array('username ASC', 'display_name ASC', 'email ASC'));
        //$resultSet = $this->select($select);

        $roleMap = $this->getRoleMap();


        $entity = $this->getEntityPrototype();
        $entity->setRoleMap($roleMap);
        $resultSet = new HydratingResultSet($this->getHydrator(), $entity);
        $adapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->getSlaveSql(), $resultSet);
        $paginator = new \Zend\Paginator\Paginator($adapter);


        return $paginator;
    }

    /**
     * @param \ZfcUser\Entity\UserInterface $entity
     */
    public function remove($entity)
    {
        $id = $entity->getId();
        $this->delete(array('user_id' => $id));
        $this->getEventManager()->trigger('remove', $this, array('entity' => $entity));
    }
}
