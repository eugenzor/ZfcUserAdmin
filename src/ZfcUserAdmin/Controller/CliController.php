<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ZfcUserAdmin\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Console;
use Zend\Console\Prompt;
use Zend\Console\ColorInterface;
/**
 * Description of CliController
 *
 * @author eugene
 */
class CliController extends AbstractActionController
{
    function installAction()
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $platformName = get_class($adapter->getPlatform());
        switch ($platformName){
            case 'Zend\Db\Adapter\Platform\Mysql':
                $queries = array(
                    "
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `display_name` varchar(50) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `state` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8",
                    "
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8",
                    "
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8",
                    "
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8",
                    "
CREATE TABLE IF NOT EXISTS `user_role_linker` (
  `user_id` int(11) unsigned NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8",
                    "
INSERT IGNORE INTO `user_role` (`id`, `role_id`, `is_default`, `parent_id`) VALUES
(1, 'guest', 1, null),
(2, 'user_unconformited', 0, null),
(3, 'user_conformited', 0, null),
(4, 'admin', 4, 3)"
                );
                foreach($queries as $query){
                    $statement = $adapter->query($query);
                    $statement->execute();
                }
                Console::getInstance()->writeLine("ZfcUser database tables were successfully created");
                break;
                
            default:
                throw new \RuntimeException("Platform $platformName isn't supported");
        }
        
        if ($this->params()->fromRoute('adduser')||$this->params()->fromRoute('u')){
            $this->adduserAction();
        }
    }
    
    
    
    function adduserAction()
    {

        $console = Console::getInstance();
        $user = array();
        $user['username'] = Prompt\Line::prompt("Enter user login: ", false);
        $user['email'] = Prompt\Line::prompt("Enter email: ", false);
        $user['display_name'] = Prompt\Line::prompt("Enter display name: ", true);
        if (!$user['display_name']){
            $user['display_name'] = $user['username'];
        }
        $user['password'] = Prompt\Password::prompt("Enter password: ", false);
        $user['passwordVerify'] = Prompt\Password::prompt("Confirm password: ", false);
        $rolesDic = $this->getServiceLocator()->get('zfcuseradmin_roles')
                ->getAvailableRolesDictionary();
        $arrowManager = $this->getServiceLocator()->get('zfcuseradmin_arrow_manager');
        $registeredRole = $arrowManager->get('registered_role');
        
        foreach($rolesDic as $id=>$name){
            if ($id == $registeredRole){
                $rolesDic[$id] .= ' [DEFAULT]';
            }
        }
        $role = Prompt\Select::prompt(
                'Select user role: ',
                $rolesDic,
                true,
                true
        );
        if (!$role){
            $role = $registeredRole;
        }
        $arrowManager->set('registered_role', $role);
        $userService = $this->getServiceLocator()->get('zfcuser_user_service');
        /* @var $form \Zend\Form\Form */
        $form = $userService->getRegisterForm();
        if ($form->has('captcha')){
            $form->remove('captcha');
        }
        $result = $userService->register($user);
        if ($result){
            $console->writeLine("User was successfully added", ColorInterface::GREEN);
        }else{
            $messages = $form->getMessages();
            $console->writeLine("User was not added. Errors:", ColorInterface::RED);
            foreach($messages as $field=>$messageList){
                $console->writeLine($field, ColorInterface::BLUE);
                foreach($messageList as $message){
                    $console->writeLine('  - ' . $message, ColorInterface::NORMAL);
                }
            }
        }

        
//        $console->writeLine("User was successfully registered");
    }
}
