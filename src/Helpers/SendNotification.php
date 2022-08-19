<?php

namespace Webkul\API\Helpers;

use Webkul\Customer\Repositories\WishlistRepository;

class SendNotification
{

    /**
     * Wishlist repository instance.
     *
     * @var \Webkul\Customer\Repositories\WishlistRepository
     */
    protected $wishlistRepository;

    /**
     * Create a new controller instance.
     *
     * @param  Webkul\Customer\Repositories\WishlistRepository  $wishlistRepository
     * @return void
     */
    public function __construct(WishlistRepository $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    /**
     * send push notification in device
     *
     * @return Response
     */
    public function sendGCM($data)
    {
        $notificationTranslations = $data->translations()->where([
            ['channel', '=', core()->getRequestedChannelCode()],
            ['locale', '=', core()->getRequestedLocaleCode()]
        ])->first();
        
        if ( $notificationTranslations ) {
            $data->title = $notificationTranslations->title;
            $data->content = $notificationTranslations->content;
        }

        // for android device
        $url        = "https://fcm.googleapis.com/fcm/send";
        $authKey    = core()->getConfigData('general.api.pushnotification.server_key');
        $androidTopic = core()->getConfigData('general.api.pushnotification.android_topic');
        $iosTopic   = core()->getConfigData('general.api.pushnotification.ios_topic');

        switch ($type = $data->type) {
            case 'product' :
                $product = app('Webkul\Product\Repositories\ProductRepository')->findorfail($data->product_category_id);

                $targeturl = route('shop.productOrCategory.index', $product->url_key);
                
                $fieldData = [
                    'body'              => $data->content,
                    'title'             => $data->title,
                    'click_action'      => $targeturl,
                    'message'           => $data->content,
                    'notificationType'  => $type,
                    'productName'       => $product->name ?? '',
                    'productId'         => $product->id ?? '',
                    'banner_url'        => asset('storage/'.$data->image),
                    'id'                => $data->id,
                    'sound'             => 'default',
                ];
            break;

            case 'category':
                $category = app('Webkul\Category\Repositories\CategoryRepository')->findorfail($data->product_category_id);
                
                $targeturl = route('shop.productOrCategory.index', $category->slug);
                $fieldData = [
                    'categoryName'      => $category->name ?? '',
                    'categoryId'        => $category->id ?? '',
                    'banner_url'        => asset('storage/'.$data->image),
                    'id'                => $data->id,
                    'body'              => $data->content,
                    'sound'             => 'default',
                    'title'             => $data->title,
                    'message'           => $data->content,
                    'notificationType'  => $data->type,
                ];
            break;

            case 'others':
                $targeturl = route('shop.home.index');
                $fieldData = [
                    'banner_url'        => asset('storage/'.$data->image),
                    'id'                => $data->id,
                    'body'              => $data->content,
                    'sound'             => 'default',
                    'title'             => $data->title,
                    'message'           => $data->content,
                    'notificationType'  => $data->type
                ];
            break;
        }

        $fields = array(
            'to'    => '/topics/' . $androidTopic,
            'data'  => $fieldData,
            'notification' =>  [
                'body'  => $data->content,
                'title' => $data->title,
            ],
        );

        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $authKey,
        );

        try {
            // Open connection
            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            // Disabling SSL Certificate support temporarly
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
            // Execute post
            $result = curl_exec( $ch );
            curl_close( $ch );

        } catch (\Exception $e) {
            session()->flash('error', $e);
        }

        return json_decode($result);
    }

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}