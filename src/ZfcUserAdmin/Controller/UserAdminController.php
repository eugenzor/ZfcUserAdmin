<?php

namespace ZfcUserAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator;
use Zend\Stdlib\Hydrator\ClassMethods;
use ZfcUser\Mapper\UserInterface;
use ZfcUser\Options\ModuleOptions as ZfcUserModuleOptions;
use ZfcUserAdmin\Options\ModuleOptions;
use Zend\Form;
use Zend\Db\Sql\Sql;




class UserAdminController extends AbstractActionController
{
    protected $options, $userMapper;
    protected $zfcUserOptions;
    /**
     * @var \ZfcUserAdmin\Service\User
     */
    protected $adminUserService;

    public function listAction()
    {
        $userMapper = $this->getUserMapper();
        $users = $userMapper->findAll();
        if (is_array($users)) {
            $paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter($users));
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
        /** @var $form \ZfcUserAdmin\Form\CreateUser */
        $form = $this->getServiceLocator()->get('zfcuseradmin_createuser_form');
        $request = $this->getRequest();

        /** @var $request \Zend\Http\Request */
        if ($request->isPost()) {
            $zfcUserOptions = $this->getZfcUserOptions();
            $class = $zfcUserOptions->getUserEntityClass();
            $user = new $class();
            $form->setHydrator(new ClassMethods());
            $form->bind($user);
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user = $this->getAdminUserService()->create($form, (array)$request->getPost());
                if ($user) {
                    $this->flashMessenger()->addSuccessMessage('The user was created');
                    return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/access', array('userId'=>$user->getId()));
                }
            }
        }


        $this->flashMessenger()->setNamespace('zfcuseradmin')->addMessage('The user was created');
        return array('createUserForm'=>$form);

    }

    public function editAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserMapper()->findById($userId);

        /** @var $form \ZfcUserAdmin\Form\EditUser */
        $form = $this->getServiceLocator()->get('zfcuseradmin_edituser_form');
        $form->setUser($user);


        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $user = $this->getAdminUserService()->edit($form, (array)$request->getPost(), $user);
                if ($user) {
                    $this->flashMessenger()->addSuccessMessage('The user was edited');
                    return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
                }
            }
        } else {
            $form->populateFromUser($user);
        }

        return array(
            'editUserForm' => $form,
            'userId' => $userId
        );
    }

    public function removeAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');

        /** @var $identity \ZfcUser\Entity\UserInterface */
        $identity = $this->zfcUserAuthentication()->getIdentity();
        if ($identity && $identity->getId() == $userId) {
            $this->flashMessenger()->addErrorMessage('You can not delete yourself');
        } else {
            $user = $this->getUserMapper()->findById($userId);
            if ($user) {
                $this->getUserMapper()->remove($user);
                $this->flashMessenger()->addSuccessMessage('The user was deleted');
            }
        }

        return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
    }

    public function accessAction()
    {
        $userId = $this->params()->fromRoute('userId');


        $roleMap = $this->getUserMapper()->getRoleMap();
        $checked = isset($roleMap[$userId])?array_keys($roleMap[$userId]):array();


        //Get available roles      
        $options = $this->getServiceLocator()->get('zfcuseradmin_roles')->getAvailableRolesDictionary();

        //Create form
        $form = new Form\Form();
        $form->setAttribute('action', $this->url()->fromRoute('zfcadmin/zfcuseradmin/access', array('userId'=>$userId)));
        $checkboxes = new Form\Element\MultiCheckbox('roles');
        $checkboxes->setLabel('Roles');
//        var_dump($options);

        $checkboxes->setValueOptions($options);


        $form->add($checkboxes);

//        var_dump($checkboxes->getValue());

        $submit = new Form\Element\Submit('go');
        $submit->setValue('Save');
        $form->add($submit);

        if ($this->getRequest()->isPost()){
            //Get checked roles
            $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
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
            // Can't rewrite zfcuser_user_mapper, use new service zfcuseradmin_user_mapper
            $this->userMapper = $this->getServiceLocator()->get('zfcuseradmin_user_mapper');
        }
        return $this->userMapper;
    }

    public function setUserMapper(UserInterface $userMapper)
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

    public function setZfcUserOptions(ZfcUserModuleOptions $options)
    {
        $this->zfcUserOptions = $options;
        return $this;
    }

    /**
     * @return \ZfcUser\Options\ModuleOptions
     */
    public function getZfcUserOptions()
    {
        if (!$this->zfcUserOptions instanceof ZfcUserModuleOptions) {
            $this->setZfcUserOptions($this->getServiceLocator()->get('zfcuser_module_options'));
        }
        return $this->zfcUserOptions;
    }
}
