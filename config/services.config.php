<?php
/**
 * User: Vladimir Garvardt
 * Date: 3/18/13
 * Time: 6:39 PM
 */
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcUser\Form\RegisterFilter;
use ZfcUser\Mapper\UserHydrator;
use ZfcUser\Validator\NoRecordExists;
use ZfcUserAdmin\Form;
use ZfcUserAdmin\Options;
use ZfcUserAdmin\Validator\NoRecordExistsEdit;
use ZfcUserAdmin\Service\ArrowManager;

return array(
    'invokables' => array(
        'ZfcUserAdmin\Form\EditUser' => 'ZfcUserAdmin\Form\EditUser',
        'zfcuseradmin_user_service' => 'ZfcUserAdmin\Service\User',
        'zfcuseradmin_roles' => 'ZfcUserAdmin\Service\Roles',
        'zfcuseradmin_mailer' => 'ZfcUserAdmin\Service\Mailer',
    ),
    'factories' => array(
        'zfcuseradmin_arrow_manager' => function (ServiceLocatorInterface $sm) {
            $arrowManager = new ArrowManager();
            $config = $sm->get('Config');
            
            if (isset($config['zfcuseradmin']['registered_role'])){
                $arrowManager->set('registered_role', $config['zfcuseradmin']['registered_role']);
            }
            
            if (isset($config['zfcuseradmin']['send_confirmation_message'])){
                $arrowManager->set('send_confirmation_message', $config['zfcuseradmin']['send_confirmation_message']);
            }

            return $arrowManager;
        },

        'zfcuseradmin_module_options' => function (ServiceLocatorInterface $sm) {
            $config = $sm->get('Config');
            return new Options\ModuleOptions(isset($config['zfcuseradmin']) ? $config['zfcuseradmin'] : array());
        },
        'zfcuseradmin_edituser_form' => function (ServiceLocatorInterface $sm) {
            /** @var $zfcUserOptions \ZfcUser\Options\UserServiceOptionsInterface */
            $zfcUserOptions = $sm->get('zfcuser_module_options');
            /** @var $zfcUserAdminOptions \ZfcUserAdmin\Options\ModuleOptions */
            $zfcUserAdminOptions = $sm->get('zfcuseradmin_module_options');
            $form = new Form\EditUser(null, $zfcUserAdminOptions, $zfcUserOptions, $sm);
            $filter = new RegisterFilter(
                new NoRecordExistsEdit(array(
                    'mapper' => $sm->get('zfcuser_user_mapper'),
                    'key' => 'email'
                )),
                new NoRecordExistsEdit(array(
                    'mapper' => $sm->get('zfcuser_user_mapper'),
                    'key' => 'username'
                )),
                $zfcUserOptions
            );
            if (!$zfcUserAdminOptions->getAllowPasswordChange()) {
                $filter->remove('password')->remove('passwordVerify');
            } else {
                $filter->get('password')->setRequired(false);
                $filter->remove('passwordVerify');
            }
            $form->setInputFilter($filter);
            return $form;
        },
        'zfcuseradmin_createuser_form' => function (ServiceLocatorInterface $sm) {
            /** @var $zfcUserOptions \ZfcUser\Options\UserServiceOptionsInterface */
            $zfcUserOptions = $sm->get('zfcuser_module_options');
            /** @var $zfcUserAdminOptions \ZfcUserAdmin\Options\ModuleOptions */
            $zfcUserAdminOptions = $sm->get('zfcuseradmin_module_options');
            $form = new Form\CreateUser(null, $zfcUserAdminOptions, $zfcUserOptions, $sm);
            $filter = new RegisterFilter(
                new NoRecordExists(array(
                    'mapper' => $sm->get('zfcuser_user_mapper'),
                    'key' => 'email'
                )),
                new NoRecordExists(array(
                    'mapper' => $sm->get('zfcuser_user_mapper'),
                    'key' => 'username'
                )),
                $zfcUserOptions
            );
            if ($zfcUserAdminOptions->getCreateUserAutoPassword()) {
                $filter->remove('password')->remove('passwordVerify');
            }
            $form->setInputFilter($filter);
            return $form;
        },
        'zfcuseradmin_user_mapper' => function (ServiceLocatorInterface $sm) {
            /** @var $config \ZfcUserAdmin\Options\ModuleOptions */
            // Can't rewrite zfcuser_user_mapper, use new service zfcuseradmin_user_mapper
            $config = $sm->get('zfcuseradmin_module_options');
            $mapperClass = $config->getUserMapper();
            if (stripos($mapperClass, 'doctrine') !== false) {
                $mapper = new $mapperClass(
                    $sm->get('zfcuser_doctrine_em'),
                    $sm->get('zfcuser_module_options')
                );
            } else {
                /** @var $zfcUserOptions \ZfcUser\Options\UserServiceOptionsInterface */
                $zfcUserOptions = $sm->get('zfcuser_module_options');

                /** @var $mapper \ZfcUserAdmin\Mapper\UserZendDb */
                $mapper = new $mapperClass();
                $mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
                $entityClass = $zfcUserOptions->getUserEntityClass();
                $mapper->setEntityPrototype(new $entityClass);
                $mapper->setHydrator(new ZfcUser\Mapper\UserHydrator());
                $mapper->setTableName($zfcUserOptions->getTableName());
            }

            return $mapper;
        },
    ),
);