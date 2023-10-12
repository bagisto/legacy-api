<?php

namespace Webkul\API\Repositories;

use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;
use Illuminate\Container\Container as App;

class NotificationRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @param  \Webkul\Attribute\Repositories\AttributeRepository  $attributeRepository
     * @param  \Illuminate\Container\Container  $app
     *
     * @return void
     */
    public function __construct(
        protected NotificationTranslationRepository $notificationTranslationRepository,
        App $app
    ) {
        parent::__construct($app);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\API\Contracts\PushNotification'; 
    }

    /**
     * Create notification.
     *
     * @param  array  $data
     * @return \Webkul\API\Contracts\Notification
     */
    public function create(array $data)
    {
        $notification = $this->model->create($data);
        if (isset($data['channels'])) {
            $model = app()->make($this->model());

            foreach (core()->getAllChannels() as $channel) {
                if (in_array($channel->code, $data['channels'])) {
                    foreach ($channel->locales as $locale) {
                        $param = [];

                        foreach ($model->translatedAttributes as $attribute) {
                            if (isset($data[$attribute])) {
                                $param[$attribute] = $data[$attribute];
                            }
                        }
                        $param['channel'] = $channel->code;
                        $param['locale'] = $locale->code;
                        $param['push_notification_id'] = $notification->id;
                        $param['title'] = $data['title'];
                        $param['content'] = $data['content'];

                        $this->notificationTranslationRepository->create($param);
                    }
                }
            }
        }

        $this->uploadImages($data, $notification);

        //Event::dispatch('api.notification.create.after', $notification);

        return $notification;
    }

    /**
     * Update notification.
     *
     * @param  array  $data
     * @param  int  $id
     * @param  string  $attribute
     * @return \Webkul\API\Contracts\Notification
     */
    public function update(array $data, $id, $attribute = "id")
    {
        Event::dispatch('api.notification.update.before', $id);

        $notification = $this->find($id);
        
        $notification->update($data);

        if (isset($data['channel']) && isset($data['locale'])) {
            $model = app()->make($this->model());
            
            $notificationTranslation = $this->notificationTranslationRepository->findOneWhere([
                'channel'               => $data['channel'],
                'locale'                => $data['locale'],
                'push_notification_id'  => $data['notification_id'],
            ]);
            
            if ($notificationTranslation) {
                foreach ($model->translatedAttributes as $attribute) {
                    if (isset($data[$attribute])) {
                        $notificationTranslation->{$attribute} = $data[$attribute];
                    }
                }
                $notificationTranslation->save();
            }
        }
        
        $this->uploadImages($data, $notification);
        
        Event::dispatch('api.notification.update.after', $notification);

        return $notification;
    }

    /**
     * Upload notification's images.
     *
     * @param  array  $data
     * @param  \Webkul\API\Contracts\Notification  $notification
     * @param  string $type
     * @return void
     */
    public function uploadImages($data, $notification, $type = "image")
    {
        if (isset($data[$type])) {
            $request = request();

            foreach ($data[$type] as $imageId => $image) {
                $file = $type . '.' . $imageId;
                $dir = 'notification/images/' . $notification->id;

                if ($request->hasFile($file)) {
                    if ($notification->{$type}) {
                        Storage::delete($notification->{$type});
                    }

                    $notification->{$type} = $request->file($file)->store($dir);
                    $notification->save();
                }
            }
        } else {
            if ($notification->{$type}) {
                Storage::delete($notification->{$type});
            }

            $notification->{$type} = null;
            $notification->save();
        }
    }
}
