<?php
return array(
    
        'zfcuseradmin' => array(
        'zfcuseradmin_mapper' => 'ZfcUserAdmin\Mapper\UserZendDb',
        'registered_role' => 2,
        'confirmed_role' => 3,
        'send_confirmation_message' => 1
    ),
    
    'zfcuser'=>array(
        'user_entity_class' => 'ZfcUserAdmin\Entity\User'
    ),
    
    
    'view_manager' => array(
        'template_path_stack' => array(
            'zfcuseradmin' => __DIR__ . '/../view',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'zfcuseradmin' => 'ZfcUserAdmin\Controller\UserAdminController',
            'zfcroleadmin' => 'ZfcUserAdmin\Controller\RoleController',
            'zfccli' => 'ZfcUserAdmin\Controller\CliController',
            'zfcconfirmation' => 'ZfcUserAdmin\Controller\ConfirmationController',
            'zfcwelcome' => 'ZfcUserAdmin\Controller\WelcomeController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'zfcuseradmin-confirmation-sent' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/user/confirmation/sent',
                    'defaults' => array(
                        'controller' => 'zfcconfirmation',
                        'action' => 'sent'
                    )
                )
            ),
            
            'zfcuseradmin-confirmation-check' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/user/confirmation/check/:id/:key',
                    'defaults' => array(
                        'controller' => 'zfcconfirmation',
                        'action' => 'check'
                    )
                )
            ),
            
            'zfcuseradmin-welcome' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/user/welcome',
                    'defaults' => array(
                        'controller' => 'zfcwelcome',
                        'action' => 'index'
                    )
                )
            ),
            
            
            'zfcadmin' => array(
                'child_routes' => array(
                    'zfcuseradmin' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/user',
                            'defaults' => array(
                                'controller' => 'zfcuseradmin',
                                'action'     => 'index',
                            ),
                        ),
                        'child_routes' =>array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list[/:p]',
                                    'defaults' => array(
                                        'controller' => 'zfcuseradmin',
                                        'action'     => 'list',
                                    ),
                                ),
                            ),
                            'create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create',
                                    'defaults' => array(
                                        'controller' => 'zfcuseradmin',
                                        'action'     => 'create'
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:userId',
                                    'defaults' => array(
                                        'controller' => 'zfcuseradmin',
                                        'action'     => 'edit',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove/:userId',
                                    'defaults' => array(
                                        'controller' => 'zfcuseradmin',
                                        'action'     => 'remove',
                                        'userId'     => 0
                                    ),
                                ),
                            ),
                            'access' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/access/:userId',
                                    'defaults' => array(
                                        'controller' => 'zfcuseradmin',
                                        'action'     => 'access',
                                        'userId'     => 0
                                    ),
                                ),
                            ),

//                            'roles' => array(
//                                'type' => 'Segment',
//                                'options' => array(
//                                    'route' => '/roles',
//                                    'defaults' => array(
//                                        'controller' => 'zfcuseradminrole',
//                                        'action' => 'index'
//                                    ),
//                                ),
//                            ),
//
//                            'addrole' => array(
//                                'type' => 'Segment',
//                                'options' => array(
//                                    'route' => '/addrole',
//                                    'defaults' => array(
//                                        'controller' => 'zfcuseradminrole',
//                                        'action' => 'index'
//                                    ),
//                                ),
//                            ),


                        ),
                    ),

                    'zfcroleadmin' => array(
                        'type' => 'Segment',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/role[/:action[/:id]]',
                            'defaults' => array(
                                'controller' => 'zfcroleadmin',
                                'action'     => 'index',
                            ),
                        )
                    )
                ),
            ),
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'zfcuseradmin-install' => array(
                    'options' => array(
                        'route'    => 'zfcuseradmin install [--adduser|-u]',
                        'defaults' => array(
                            'controller' => 'zfccli',
                            'action'     => 'install'
                        )
                    )
                ),
                
                'zfcuseradmin-adduser' => array(
                    'options' => array(
                        'route'    => 'zfcuseradmin adduser',
                        'defaults' => array(
                            'controller' => 'zfccli',
                            'action'     => 'adduser'
                        )
                    )
                )
            )
        )
    ),
    
    'navigation' => array(
        'admin' => array(
            'zfcuseradmin' => array(
                'label' => 'Users',
                'route' => 'zfcadmin/zfcuseradmin/list',
                'resource' => 'route/zfcadmin/zfcuseradmin/list',
                'pages' => array(
                    'user-list' => array(
                        'label' => 'User list',
                        'route' => 'zfcadmin/zfcuseradmin/list',
                    ),

                    'user-create' => array(
                        'label' => 'New User',
                        'route' => 'zfcadmin/zfcuseradmin/create',
                    ),


                    'role-list' => array(
                        'label' => 'Roles',
                        'route' => 'zfcadmin/zfcroleadmin',
                    ),
                ),
            ),
        ),
    ),
    
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),

);
