<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 11/27/14
 * Time: 4:08 PM
 */

namespace ZfcUserAdmin\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form;

class RoleController extends AbstractActionController
{

    /**
     * @return \BjyAuthorize\Service\RoleDbTableGateway
     */
    protected function getRoleTable()
    {
        return $this->serviceLocator->get('BjyAuthorize\Service\RoleDbTableGateway');
    }

    function indexAction()
    {
        $roles = $this->getRoleTable();
        $roleSet = $roles->select()->toArray();


        $form = new Form\Form();
        $form->setAttribute('action', '');
        $name = new Form\Element\Text('name');
        $name->setLabel('Role name');
        $name->setAttribute('required', true);
        $form->add($name);

        $isDefault = new Form\Element\Checkbox('is_default');
        $isDefault->setLabel('Is default?');
        $form->add($isDefault);


        $parentRole = new Form\Element\Select('parent_id');
        $parentRole->setLabel('Parent role');
        $options = array(null=>'none');
        $roleDic = array();
        foreach($roleSet as $role){
            $roleDic[$role['id']] = $role['role_id'];
        }
        $options = $options + $roleDic;

        $parentRole->setValueOptions($options);



        $form->add($parentRole);

        $go = new Form\Element\Submit('go');
        $go->setValue('Go');
        $form->add($go);



        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            $form->setData($data);
            $role_id = $data['name'];
            $is_default = isset($data['is_default']);
            $parent_id = $data['parent_id'];
            if (!$parent_id){
                $parent_id = null;
            }
            $roles->insert(compact('role_id', 'is_default', 'parent_id'));
            $this->redirect()->toRoute('zfcadmin/zfcroleadmin');
        }

        return array('roles'=>$roleSet, 'form'=>$form, 'roleDic'=>$roleDic);
    }



    function removeAction()
    {
        $roles = $this->getRoleTable();
        $id = (int)$this->params()->fromRoute('id');
        if (!$id){
            throw new \Exception("ID is undefined");
        }
        $roles->delete("id = $id");

        $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $linker = new \Zend\Db\TableGateway\TableGateway('user_role_linker', $db);
        $linker->delete(array('role_id'=>$id));
        $this->redirect()->toRoute('zfcadmin/zfcroleadmin');
    }
} 