<?php
return [
    'zf_permission' => [
        'backend' => [
            'user_key' => 'admin_groupcode',
            'group' => [
                'SUPPER_ADMIN', 'MANAGER', 'STAFF', 'SUPPORT', 'SALE'
            ],
            'prevent_routes' => [
                'access-deny' => true, 'login' => true, 'cronjob' => true,
                'logout' => true, 'login-deny' => true, 'reset-password' => true, 
                'message' => true, 'db' => true
            ],
            'use_subfolder' => true
        ],
    ],
];