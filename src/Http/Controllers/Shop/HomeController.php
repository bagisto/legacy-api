<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Webkul\API\Http\Resources\Shop\HomePage as HomePageResource;

class HomeController extends Controller
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Controller instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);

        $this->middleware('auth:' . $this->guard)->except(['index']);
        
        $this->middleware('validateAPIHeader');

        $this->_config = request('_config');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): ?JsonResponse
    {
        $data = request()->all();
        
        return response()->json(new HomePageResource(array_merge($data, [
            'customer' => auth($this->guard)->user()
        ])));
    }
}
