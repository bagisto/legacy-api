<?php

namespace Webkul\API\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\API\Repositories\NotificationRepository;
use Webkul\API\Helpers\SendNotification;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Product\Repositories\ProductRepository;

class NotificationController extends Controller
{
    /**
     * Contains route related configuration.
     *
     * @var array
     */
    protected $_config;

    /**
     * Notification repository instance.
     *
     * @var \Webkul\API\Repositories\NotificationRepository
     */
    protected $notificationRepository;

    /**
     * SendNotification helper instance.
     *
     * @var \Webkul\API\Helpers\SendNotification
     */
    protected $sendNotification;

    /**
     * Channel repository instance.
     *
     * @var \Webkul\Core\Repositories\ChannelRepository
     */
    protected $channelRepository;

    /**
     * Product repository instance.
     *
     * @var \Webkul\Product\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * Category repository instance.
     *
     * @var \Webkul\Category\Repositories\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Webkul\API\Repositories\NotificationRepository  $notificationRepository
     * @param \Webkul\API\Helpers\SendNotification  $sendNotification
     * @param \Webkul\Core\Repositories\ChannelRepository  $channelRepository
     * @param \Webkul\Product\Repositories\ProductRepository  $productRepository
     * @param \Webkul\Category\Repositories\CategoryRepository  $categoryRepository
     */
    public function __construct(
        ChannelRepository $channelRepository,
        NotificationRepository $notificationRepository,
        SendNotification $sendNotification,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    )   {
        $this->_config = request('_config');

        $this->middleware('admin');

        $this->notificationRepository = $notificationRepository;

        $this->sendNotification = $sendNotification;

        $this->channelRepository = $channelRepository;

        $this->productRepository = $productRepository;

        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view($this->_config['view']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $channels = $this->channelRepository->get();

        return view($this->_config['view'], compact('channels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'title'     => 'string|required',
            'content'   => 'string|required',
            'image.*'   => 'mimes:jpeg,jpg,bmp,png',
            'type'      => 'required',
            'channels'  => 'required',
            'status'    => 'required'
        ]);
        
        $data = collect(request()->all())->except('_token')->toArray();

        if ( $data['type'] == 'custom_collection' && isset($data['custom_collection']) ) {
            $data['product_category_id'] = $data['custom_collection'];

            unset($data['custom_collection']);
        }

        $this->notificationsRepository->create($data);

        session()->flash('success', trans('mobikul::app.mobikul.alert.create-success', ['name' => 'Notification']));

        return redirect()->route('mobikul.notification.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $notification = $this->notificationsRepository->findOrFail($id);

        $customCollection = [];
        if ( $notification->type == 'custom_collection' ) {
            $customCollection = app('Webkul\Mobikul\Repositories\CustomCollectionRepository')->findOrFail($notification->product_category_id);
        }

        $channels = $this->channelRepository->get();

        return view($this->_config['view'], compact('notification', 'customCollection','channels'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->validate(request(), [
            'title'     => 'string|required',
            'content'   => 'string|required',
            'image.*'   => 'mimes:jpeg,jpg,bmp,png',
            'type'      => 'required',
            'channels'  => 'required',
            'status'    => 'required'
        ]);

        $data = collect(request()->all())->except('_token')->toArray();

        if ( $data['type'] == 'custom_collection' && isset($data['custom_collection']) ) {
            $data['product_category_id'] = $data['custom_collection'];

            unset($data['custom_collection']);
        }

        $this->notificationsRepository->update($data, $id);

        session()->flash('success', trans('mobikul::app.mobikul.alert.update-success', ['name' => 'Notification']));

        return redirect()->route('mobikul.notification.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $this->notificationsRepository->delete($id);

            session()->flash('success', trans('mobikul::app.mobikul.alert.delete-success', ['name' => 'Notification']));

            return response()->json(['message' => true], 200);
        } catch(\Exception $e) {
            session()->flash('success', trans('mobikul::app.mobikul.alert.delete-failed', ['name' => 'Notification']));
        }

        return response()->json(['message' => false], 400);
    }

    /**
     * To mass update the notification.
     *
     * @return \Illuminate\Http\Response
     */
    public function massUpdate()
    {
        $notificationIds = explode(',', request()->input('indexes'));
        $updateOption = request()->input('update-options');

        foreach ($notificationIds as $notificationId) {
            $notification = $this->notificationsRepository->find($notificationId);

            $notification->update([
                'status' => $updateOption
            ]);
        }

        session()->flash('success', trans('mobikul::app.mobikul.alert.update-success', ['name' => 'Notification']));

        return redirect()->back();
    }

    /**
     * To mass delete the notificaton.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy()
    {
        $notificationIds = explode(',', request()->input('indexes'));

        foreach ($notificationIds as $notificationId) {
            $this->notificationsRepository->deleteWhere([
                'id' => $notificationId
            ]);
        }

        session()->flash('success', trans('mobikul::app.mobikul.alert.delete-success', ['name' => 'Notification']));

        return redirect()->back();
    }

    /**
     * To sent the notification to the device.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendNotification($id)
    {
        $data = $this->notificationsRepository->find($id);

        $notification = $this->sendNotification->sendGCM($data);

        if (isset($notification->message_id)) {
            session()->flash('success', trans('mobikul::app.mobikul.alert.sended-successfully', ['name' => 'Notification']));
        }

        return redirect()->back();
    }

    /**
     * To check resource exist in DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function exist()
    {
        $data = request()->all();

        if ( substr_count($data['givenValue'], ' ') > 0) {
            return response()->json(['value' => false, 'message' => 'Product not exist', 'type' => $data['selectedType']],200);
        }

        //product case
        if ($data['selectedType'] == 'product') {
            if ($product = $this->productRepository->find($data['givenValue'])) {

                if (! isset($product->id) || !isset($product->url_key) || ( isset($product->parent_id) && $product->parent_id) ) {
                    return response()->json(['value' => false, 'message' => 'Product not exist', 'type' => 'product'], 200);
                } else {
                    return response()->json(['value' => true], 200);
                }
            } else {
                return response()->json(['value' => false, 'message' => 'Product not exist', 'type' => 'product'], 200);
            }
        }

        //category case
        if ($data['selectedType'] == 'category') {
            if ($this->categoryRepository->find($data['givenValue'])) {
                return response()->json(['value' => true] ,200);
            } else {
                return response()->json(['value' => false, 'message' => 'Category not exist', 'type' => 'category'] ,200);
            }
        }
    }
}
