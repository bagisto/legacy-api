<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Webkul\API\Http\Resources\Shop\CmsPage as CmsResource;

class CmsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);
        
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
        return response()->json(new CmsResource(request()->all()));
    }
}
