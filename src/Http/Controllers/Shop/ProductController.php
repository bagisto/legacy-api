<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\API\Http\Resources\Catalog\Product as ProductResource;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Product\Repositories\ProductRepository $productRepository
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository)
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);
        
        $this->middleware('validateAPIHeader');
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'data' => ProductResource::collection($this->productRepository->getAll(request()->input('category_id'))),
            'cartCount' => Cart::getCart() ? count(Cart::getCart()->items) : 0,
        ]);
    }

    /**
     * Returns a individual resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        return response()->json([
            'data' => new ProductResource(
                $this->productRepository->findOrFail($id)
            ),
            'cartCount' => Cart::getCart() ? count(Cart::getCart()->items) : 0,
        ]);
    }

    /**
     * Returns product's additional information.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function additionalInformation($id)
    {
        return response()->json([
            'data' => app('Webkul\Product\Helpers\View')->getAdditionalData($this->productRepository->findOrFail($id)),
        ]);
    }

    /**
     * Returns product's additional information.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function configurableConfig($id)
    {
        return response()->json([
            'data' => app('Webkul\Product\Helpers\ConfigurableOption')->getConfigurationConfig($this->productRepository->findOrFail($id)),
        ]);
    }
}
