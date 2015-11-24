<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZfcUserAdmin;

use Zend\Captcha;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;


class Module implements ServiceProviderInterface, 
                        ConsoleUsageProviderInterface,
                        ConsoleBannerProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return include __DIR__ . '/config/services.config.php';

    }

    public function onBootstrap($e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $config = $sm->get('config');

        if (!empty($config['zfcuser']['use_registration_form_captcha'])){
            $events = $e->getApplication()->getEventManager()->getSharedManager();
            $events->attach('ZfcUser\Form\Register','init', function($e) use($config) {
                $form = $e->getTarget();
                
                $label = 'Type the text';
                if (!empty($config['zfcuser']['form_captcha_options']['options']['use_numbers'])){
                    $numbers = array(0, 1, 3, 4, 5, 6, 7, 8, 9);
                    Captcha\AbstractWord::$V = $numbers;
                    Captcha\AbstractWord::$VN = $numbers;
                    Captcha\AbstractWord::$C = $numbers;
                    Captcha\AbstractWord::$CN = $numbers;
                    $label = 'Type the number';
                }

                $form->add(array(
                        'type' => 'Zend\Form\Element\Captcha',
                        'name' => 'captcha',
                        'options' => array(
                            'label' => 'Input number',
                            'captcha' => Captcha\Factory::factory($config['zfcuser']['form_captcha_options']),
                        ),

                    )
                );

                // Do what you please with the form instance ($form)
            });
//            $events->attach('ZfcUser\Form\RegisterFilter','init', function($e) {
//                $filter = $e->getTarget();
//                // Do what you please with the filter instance ($filter)
//            });
        }

        if (!empty($config['zfcadmin']['registered_role'])){
            $zfcServiceEvents = $sm->get('zfcuser_user_service')->getEventManager();
            $zfcServiceEvents->attach('register.post', function($e) use ($sm) {
                $arrowManager = $sm->get('zfcuseradmin_arrow_manager');
                $user = $e->getParam('user');
                $userId = $user->getId();

                $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                $table = new \Zend\Db\TableGateway\TableGateway('user_role_linker', $adapter);
                $table->insert(array('user_id'=>$userId, 'role_id'=>$arrowManager->get('registered_role')));
            });
        }


    }
    
    
    public function getConsoleBanner(Console $console){
        return 'ZfcUserAdmin commands';
    }

    public function getConsoleUsage(Console $console){
        //description command
        return array(
            'zfcuseradmin install [--adduser|-u]' => 'Install zfcuser database tables (optional with user)',
            'zfcuseradmin adduser' => 'Add new user into the database'
        );
    }
}
