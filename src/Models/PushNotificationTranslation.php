<?php

namespace Webkul\API\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\API\Contracts\PushNotificationTranslation as PushNotificationTranslationContract;

/**
 * Class NotificationTranslation
 *
 * @package Webkul\API\Models
 *
 */
class PushNotificationTranslation extends Model implements PushNotificationTranslationContract
{
    protected $table = 'push_notification_translations';

    public $timestamps = false;
    
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

     /**
     * Fillables.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'locale',
        'channel',
        'push_notification_id'
    ];


    /**
     * Get the notification that owns the attribute value.
     */
    public function notification()
    {
        return $this->belongsTo(PushNotificationProxy::modelClass());
    }
}
