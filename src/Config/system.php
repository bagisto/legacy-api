<?php

return [
    [
        'key'  => 'general.api',
        'name' => 'admin::app.api.system.api',
        'sort' => 3,
    ], [
        'key'    => 'general.api.settings',
        'name'   => 'admin::app.api.system.basic-configuration',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'username',
                'title'         => 'admin::app.api.system.username',
                'type'          => 'text',
                'channel_based' => true,
                'validation'    => 'required',
            ], [
                'name'          => 'password',
                'title'         => 'admin::app.api.system.password',
                'type'          => 'password',
                'channel_based' => true,
                'validation'    => 'required',
            ],
        ],
    ], [
        'key'    => 'general.api.customer',
        'name'   => 'admin::app.api.system.customer-configuration',
        'sort'   => 2,
        'fields' => [
            [
                'name'          => 'login_after_register',
                'title'         => 'admin::app.api.system.login-after-register',
                'type'          => 'boolean',
                'info'          => 'admin::app.api.system.info-login',
                'locale_based'  => true,
                'channel_based' => true,
            ],
        ]
    ],  [
        'key'   => 'general.api.pushnotification',
        'name'  => 'api::app.system.push-notification-configuration',
        'sort'  => 3,
        'fields'=> [
            [
                'name'          => 'server_key',
                'title'         => 'api::app.system.server-key',
                'type'          => 'text',
                'validation'    => '',
                'info'          => 'api::app.system.info-get-server-key',
                'channel_based' => true
            ], [
                'name'          => 'android_topic',
                'title'         => 'api::app.system.android-topic',
                'type'          => 'text',
                'validation'    => '',
                'channel_based' => true
            ], [
                'name'          => 'ios_topic',
                'title'         => 'api::app.system.ios-topic',
                'type'          => 'text',
                'validation'    => '',
                'channel_based' => true
            ],
        ]
    ]
];