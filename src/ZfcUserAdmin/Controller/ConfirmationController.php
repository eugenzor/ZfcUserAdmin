<?php

namespace ZfcUserAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ConfirmationController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function sentAction()
    {
        if($this->getRequest()->isPost()){
            try{
                $user = $this->zfcUserAuthentication()->getIdentity();
                $mailer = $this->getServiceLocator('zfcuseradmin_mailer');
                $mailer->sendConfirmationMessage($user);
                $this->getFlashMessenger()->addMessage($e->getMessage());
            }catch(\Exception $e){
                $this->getFlashMessenger()->addMessage($e->getMessage());
            }
            $this->redirect()->refresh();
        }
        if ($this->isAllowed('user-interface', 'use')){
            $this->redirect()->toRoute('zfcuseradmin-welcome');
        }
    }

    public function checkAction()
    {
        $id = $this->params()->fromRoute('id');
        $key = $this->params()->fromRoute('key');
        $user = $this->getServiceLocator()->get('zfcuseradmin_user_mapper')->findById($id);
        $config = $this->getServiceLocator()->get('config');
        $translator = $this->getServiceLocator()->get('translator');
        $arrowManager = $this->getServiceLocator()->get('zfcuseradmin_arrow_manager');
  
        
        try{
            if (isset($config['zfcuseradmin']['confirmation']['salt'])){
                $user->setSalt($config['zfcuseradmin']['confirmation']['salt']);
            }
            if (!$user->isConfirmationKeyCorrect($key)){
                throw new \Exception(
                    $translator->translate('zfcuseradmin.activation_key_is_wrong')
                );
                //Activation link is wrong. Check your email and ask new activation link
            }
            
            if (!isset($config['zfcuseradmin']['confirmed_role'])){
                throw new \Exception("Confirmation role doesn't defined in config");
            }
            
            $newRoles = array($config['zfcuseradmin']['confirmed_role']);
            
            /* @var $roles \ZfcUserAdmin\Service\Roles */
            $roles = $this->getServiceLocator()->get('zfcuseradmin_roles');
            $roles->updateUserRoles($user, $newRoles);
            $this->flashMessenger()->addMessage(
                $translator->translate('zfcuseradmin.activation_key_succesfull')
                    //Your account was succesfully activated
            );
            $this->redirect()->toRoute('zfcuseradmin-welcome');
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            $this->flashMessenger()->addMessage($ex->getMessage());
//            $this->redirect()->toRoute('zfcuseradmin-confirmation-sent');
        }

    }


}

