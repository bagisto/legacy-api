<?php

namespace Webkul\Mobikul\Repositories;

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
        return \Webkul\Mobikul\Contracts\PushNotificationTranslation::class;
    }
}
