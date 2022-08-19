<?php

namespace Webkul\API\Repositories;

use Webkul\Core\Eloquent\Repository;

class NotificationTranslationRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return \Webkul\API\Contracts\PushNotificationTranslation::class;
    }
}
