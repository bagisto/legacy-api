<?php

namespace Webkul\API\Http\Resources\Checkout;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\API\Http\Resources\Catalog\Product as ProductResource;

class CartItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $currencyCode = session()->get('currency') ?: core()->getChannelBaseCurrencyCode();

        return [
            'id'                            => $this->id,
            'quantity'                      => $this->quantity,
            'sku'                           => $this->sku,
            'type'                          => $this->type,
            'name'                          => $this->name,
            'coupon_code'                   => $this->coupon_code,
            'weight'                        => $this->weight,
            'total_weight'                  => $this->total_weight,
            'base_total_weight'             => $this->base_total_weight,
            'price'                         => (float) core()->convertPrice($this->base_price, $currencyCode),
            'formated_price'                => (string) core()->formatPrice(core()->convertPrice($this->base_price, $currencyCode), $currencyCode),
            'base_price'                    => (float) $this->base_price,
            'formated_base_price'           => (string) core()->formatBasePrice($this->base_price),
            'custom_price'                  => (float) $this->custom_price,
            'formated_custom_price'         => (string) core()->formatPrice($this->custom_price, $this->cart->cart_currency_code),
            'total'                         => (float) core()->convertPrice($this->base_total, $currencyCode),
            'formated_total'                => (string) core()->formatPrice(core()->convertPrice($this->base_total, $currencyCode), $currencyCode),
            'base_total'                    => (float) $this->base_total,
            'formated_base_total'           => (string) core()->formatBasePrice($this->base_total),
            'tax_percent'                   => $this->tax_percent,
            'tax_amount'                    => (float) core()->convertPrice($this->base_tax_amount, $currencyCode),
            'formated_tax_amount'           => (string) core()->formatPrice(core()->convertPrice($this->base_tax_amount, $currencyCode), $currencyCode),
            'base_tax_amount'               => (float) $this->base_tax_amount,
            'formated_base_tax_amount'      => (string) core()->formatBasePrice($this->base_tax_amount),
            'discount_percent'              => $this->discount_percent,
            'discount_amount'               => (float) core()->convertPrice($this->base_discount_amount, $currencyCode),
            'formated_discount_amount'      => (string) core()->formatPrice(core()->convertPrice($this->base_discount_amount, $currencyCode), $currencyCode),
            'base_discount_amount'          => (float) $this->base_discount_amount,
            'formated_base_discount_amount' => (string) core()->formatBasePrice($this->base_discount_amount),
            'additional'                    => is_array($this->resource->additional)
                                                ? $this->resource->additional
                                                : json_decode($this->resource->additional, true),
            'child'                         => new self($this->child),
            'product'                       => $this->when($this->product_id, new ProductResource($this->product)),
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
        ];
    }
}