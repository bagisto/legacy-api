<?php

return [
    'menu'  => [
        'push-notification' => 'Push Notification',
    ],

    'acl'  => [
        'push-notification' => 'Push Notification',
        'send'              => 'Send',
    ],

    'system' => [
        'push-notification-configuration'   => 'FCM Push Notification Configuration',
        'server-key'                        => 'Server Key',
        'info-get-server-key'               => 'Info: To get fcm API credentials: <a href="https://console.firebase.google.com/" target="_blank">Click here</a>',
        'android-topic'                     => 'Android Topic',
        'ios-topic'                         => 'IOS Topic',
    ],

    'notification'  => [
        'title'                 => 'Push Notification',
        'add-title'             => 'Add Notification',
        'general'               => 'General',

        'id'                    => 'Id',
        'image'                 => 'Image',
        'text-title'            => 'Title',
        'edit-notification'     => 'Edit Notification',
        'manage'                => 'Notifications',
        'new-notification'      => 'New Notification',
        'create-btn-title'      => 'Save Notification',
        'notification-image'    => 'Notification Image',
        'notification-title'    => 'Notification Title',
        'notification-content'  => 'Notification Content',
        'notification-type'     => 'Notification Type',
        'product-cat-id'        => 'Product/Category Id',
        'store-view'            => 'Channels',
        'notification-status'   => 'Notification Status',
        'created'               => 'Created',
        'modified'              => 'Modified',
        'collection-autocomplete'   => 'Custom Collection - (Autocomplete)',
        'no-collection-found'       => 'Collections not found with same name.',
        'collection-search-hint'    => 'Start typing collection name',
        
        'Action'    => [
            'edit'      => 'Edit',
        ],

        'status'    => [
            'enabled'   => 'Enabled',
            'disabled'  => 'Disabled',
        ],

        'notification-type-option'  => [
            'select'            => '-- Select --',
            'simple'            => 'Simple Type',
            'product'           => 'Product Based',
            'category'          => 'Category Based',
        ],
    ],

    'alert' => [
        'create-success'        => ':name created successfully',
        'update-success'        => ':name updated successfully',
        'delete-success'        => ':name deleted successfully',
        'delete-failed'         => ':name deleted failed',
        'sended-successfully'   => ':name pushed successfully for android and iOS.',
        'no-value-selected'     => 'there are no existing value',
    ],
];
