<?php

namespace Webkul\API\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\API\Http\Resources\Catalog\Product as ProductResource;
use Webkul\API\Http\Resources\Customer\Customer as CustomerResource;

class Compare extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @return void
     */
    public function __construct($resource)
    {
        $this->productFlatRepository = app('Webkul\Product\Repositories\ProductFlatRepository');

        $this->customerRepository = app('Webkul\Customer\Repositories\CustomerRepository');
        
        parent::__construct($resource);
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $productFlat = $this->productFlatRepository->findOrFail($this->product_flat_id);

        $customer = $this->customerRepository->findOrFail($this->customer_id);

        return [
            'id'            => $this->id,
            'product'       => new ProductResource($productFlat->product),
            'customer'      => new CustomerResource($customer),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
