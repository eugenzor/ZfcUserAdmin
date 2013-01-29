<?php

namespace ZfcUserAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcUserAdmin\Options\ModuleOptions;
use Zend\Form;
use Zend\Db\Sql\Sql;

class UserAdminController extends AbstractActionController
{
    protected $options, $userMapper, $adminUserService;

    public function listAction()
    {
        $userMapper = $this->getUserMapper();
        $users = $userMapper->findAll();
        if (is_array($users)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($users));
        } else {
            $paginator = $users;
        }


        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        return array(
            'users' => $paginator,
            'userlistElements' => $this->getOptions()->getUserListElements()
        );
    }

    public function createAction()
    {
        $form = $this->getServiceLocator()->get('zfcuseradmin_createuser_form');
        $request = $this->getRequest();

        $user = false;
        if($request->isPost())
        {
            $user = $this->getAdminUserService()->create((array) $request->getPost());
        }

        if (!$user) {
            return array(
                'createUserForm' => $form
            );
        }

        $this->flashMessenger()->setNamespace('zfcuseradmin')->addMessage('The user was created');
        return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/access');
    }

    public function editAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserMapper()->findById($userId);
        $form = $this->getServiceLocator()->get('zfcuseradmin_edituser_form');
        $form->setUser($user);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $user->setPassword('');
            $form->populateFromUser($user);
            return array(
                'editUserForm' => $form,
                'userId' => $userId
            );
        }

        $this->getAdminUserService()->edit(get_object_vars($request->getPost()), $user);

        $this->flashMessenger()->setNamespace('zfcuseradmin')->addMessage('The user was edited');
        return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
    }

    public function removeAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserMapper()->findById($userId);
        if($user)
        {
            $this->getUserMapper()->remove($user);
            $this->flashMessenger()->setNamespace('zfcuseradmin')->addMessage('The user was deleted');
        }

        return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
    }

    public function accessAction()
    {
        $userId = $this->params()->fromRoute('userId');

        $allRoles = $this->getServiceLocator()->get('BjyAuthorize\Service\Authorize')->getAcl()->getRoles();

        //Get checked roles
        $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $rows = $db->query('SELECT role_id FROM user_role_linker WHERE user_id = ?')->execute(array($userId));
        $checked = array();
        foreach($rows as $row){
            $checked[]=$row['role_id'];
        }


        //Get available roles
        $roles = array();
        $options = array();
        $insertData = array();
        foreach($allRoles as $role){
            if ($role == 'bjyauthorize-identity'){
                continue;
            }else{
                $roles[] = $role;
                $options[$role] = $role;
            }
        }

        //Create form
        $form = new Form\Form();
        $form->setAttribute('action', $this->url()->fromRoute('zfcadmin/zfcuseradmin/access', array('userId'=>$userId)));
        $checkboxes = new Form\Element\MultiCheckbox('roles');
        $checkboxes->setLabel('Roles');
        $checkboxes->setValueOptions($options);

        $form->add($checkboxes);

        $submit = new Form\Element\Submit('go');
        $submit->setValue('Save');
        $form->add($submit);

        if ($this->getRequest()->isPost()){
            $linker = new \Zend\Db\TableGateway\TableGateway('user_role_linker', $db);
            $linker->delete(array('user_id'=>$userId));

            $data = $this->getRequest()->getPost();
            $form->setData($data);
            $newRoles = $this->params()->fromPost('roles');
            if ($newRoles){
                foreach($newRoles as $role){
                    $linker->insert(array('user_id'=>$userId, 'role_id'=>$role));
                }
            }
            return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
        }else{
            $checkboxes->setValue($checked);
        }

        return array('form'=>$form);
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceLocator()->get('zfcuseradmin_module_options'));
        }
        return $this->options;
    }

    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceLocator()->get('zfcuser_user_mapper');
        }
        return $this->userMapper;
    }

    public function setUserMapper(UserMapperInterface $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }

    public function getAdminUserService()
    {
        if (null === $this->adminUserService) {
            $this->adminUserService = $this->getServiceLocator()->get('zfcuseradmin_user_service');
        }
        return $this->adminUserService;
    }

    public function setAdminUserService($service)
    {
        $this->adminUserService = $service;
        return $this;
    }
}
