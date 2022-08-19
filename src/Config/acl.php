<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings\PushNotification
    |--------------------------------------------------------------------------
    |
    | All ACLs related to settings\pushnotification will be placed here.
    |
    */
    [
        'key'   => 'settings.push_notification',
        'name'  => 'api::app.acl.push-notification',
        'route' => 'api.notification.index',
        'sort'  => 9,
    ], [
        'key'   => 'settings.push_notification.create',
        'name'  => 'admin::app.acl.create',
        'route' => 'api.notification.create',
        'sort'  => 1,
    ], [
        'key'   => 'settings.push_notification.edit',
        'name'  => 'admin::app.acl.edit',
        'route' => 'api.notification.edit',
        'sort'  => 2,
    ], [
        'key'   => 'settings.push_notification.delete',
        'name'  => 'admin::app.acl.delete',
        'route' => 'api.notification.delete',
        'sort'  => 3,
    ], [
        'key'   => 'settings.push_notification.massdelete',
        'name'  => 'admin::app.acl.mass-delete',
        'route' => 'api.notification.mass-delete',
        'sort'  => 4,
    ], [
        'key'   => 'settings.push_notification.massupdate',
        'name'  => 'admin::app.acl.mass-update',
        'route' => 'api.notification.mass-update',
        'sort'  => 5,
    ], [
        'key'   => 'settings.push_notification.send',
        'name'  => 'api::app.acl.send',
        'route' => 'api.notification.send-notification',
        'sort'  => 6,
    ], 
];
