<?php
return array(
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
        ),
    ),
    'router' => array(
        'routes' => array(
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

    'zfcuseradmin' => array(
        'zfcuseradmin_mapper' => 'ZfcUserAdmin\Mapper\UserZendDb',
    )
);
