<?php

namespace Webkul\API\Http\Controllers\Shop;

use Webkul\Velocity\Repositories\VelocityCustomerCompareProductRepository as CompareRepository;
use Webkul\Product\Repositories\ProductFlatRepository;
use Webkul\Core\Repositories\ChannelRepository;

class CompareController extends Controller
{
    /**
     * CompareRepository object
     *
     * @var \Webkul\Velocity\Repositories\VelocityCustomerCompareProductRepository
     */
    protected $compareRepository;

    /**
     * ProductFlatRepository object
     *
     * @var \Webkul\Product\Repositories\ProductFlatRepository
     */
    protected $productFlatRepository;

    /**
     * ChannelRepository object
     *
     * @var \Webkul\Core\Repositories\ChannelRepository
     */
    protected $channelRepository;

    /**
     * @param  \Webkul\Velocity\Repositories\VelocityCustomerCompareProductRepository  $compareRepository
     * @param  \Webkul\Product\Repositories\ProductFlatRepository   $productFlatRepository
     * @param  \Webkul\Core\Repositories\ChannelRepository   $channelRepository
     */
    public function __construct(
        CompareRepository $compareRepository,
        ProductFlatRepository $productFlatRepository,
        ChannelRepository $channelRepository
    )
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        if (isset($this->_config['authorization_required']) && $this->_config['authorization_required']) {

            auth()->setDefaultDriver($this->guard);

            $this->middleware('auth:' . $this->guard);
        }

        $this->middleware('validateAPIHeader');

        $this->compareRepository = $compareRepository;

        $this->productFlatRepository = $productFlatRepository;

        $this->channelRepository = $channelRepository;
    }

    /**
     * Function to add item to the wishlist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $channel = $this->channelRepository->find(request()->input('channel_id'));

        $locale = request()->input('locale');

        $customer = auth()->guard($this->guard)->user();
        if (! $customer) {
            return response()->json([
                'success'   => false,
                'message'   => trans('admin::app.api.auth.login-required')
            ], 400);
        }
        
        $productFlat = $this->productFlatRepository->findOneWhere([
            'channel'       => $channel->code,
            'locale'        => $locale,
            'product_id'    => $id,
        ]);

        if ( $productFlat ) {
            $compareProduct = $this->compareRepository->findOneByField([
                'customer_id'     => $customer->id,
                'product_flat_id' => $productFlat->id,
            ]);

            if ( $compareProduct ) {
                return response()->json([
                    'success'   => true,
                    'message'   => trans('velocity::app.customer.compare.already_added'),
                ], 200);
            }

            $this->compareRepository->create([
                'customer_id'     => $customer->id,
                'product_flat_id' => $productFlat->id
            ]);

            return response()->json([
                'success'   => true,
                'message'   => trans('velocity::app.customer.compare.added'),
            ], 200);
        } else {
            return response()->json([
                'success'   => true,
                'message'   => trans('admin::app.api.auth.resource-not-found', ['resource' => 'Product']),
            ], 200);
        }
    }
}
