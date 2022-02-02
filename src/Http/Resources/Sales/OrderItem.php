<?php

namespace Webkul\API\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\API\Http\Resources\Catalog\Product as ProductResource;

class OrderItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                                => $this->id,
            'sku'                               => $this->sku,
            'type'                              => $this->type,
            'name'                              => $this->name,
            'product'                           => $this->when($this->product, new ProductResource($this->product)),
            'coupon_code'                       => $this->coupon_code,
            'weight'                            => $this->weight,
            'total_weight'                      => $this->total_weight,
            'qty_ordered'                       => $this->qty_ordered,
            'qty_canceled'                      => $this->qty_canceled,
            'qty_invoiced'                      => $this->qty_invoiced,
            'qty_shipped'                       => $this->qty_shipped,
            'qty_refunded'                      => $this->qty_refunded,
            'price'                             => (float) $this->price,
            'formated_price'                    => (string) core()->formatPrice($this->price, $this->order->order_currency_code),
            'base_price'                        => (float) $this->base_price,
            'formated_base_price'               => (string) core()->formatBasePrice($this->base_price),
            'total'                             => (float) $this->total,
            'formated_total'                    => (string) core()->formatPrice($this->total, $this->order->order_currency_code),
            'base_total'                        => (float) $this->base_total,
            'formated_base_total'               => (string) core()->formatBasePrice($this->base_total),
            'total_invoiced'                    => (float) $this->total_invoiced,
            'formated_total_invoiced'           => (string) core()->formatPrice($this->total_invoiced, $this->order->order_currency_code),
            'base_total_invoiced'               => (float) $this->base_total_invoiced,
            'formated_base_total_invoiced'      => (string) core()->formatBasePrice($this->base_total_invoiced),
            'amount_refunded'                   => (float) $this->amount_refunded,
            'formated_amount_refunded'          => (string) core()->formatPrice($this->amount_refunded, $this->order->order_currency_code),
            'base_amount_refunded'              => (float) $this->base_amount_refunded,
            'formated_base_amount_refunded'     => (string) core()->formatBasePrice($this->base_amount_refunded),
            'discount_percent'                  => $this->discount_percent,
            'discount_amount'                   => (float) $this->discount_amount,
            'formated_discount_amount'          => (string) core()->formatPrice($this->discount_amount, $this->order->order_currency_code),
            'base_discount_amount'              => $this->base_discount_amount,
            'formated_base_discount_amount'     => (string) core()->formatBasePrice($this->base_discount_amount),
            'discount_invoiced'                 => (float) $this->discount_invoiced,
            'formated_discount_invoiced'        => (string) core()->formatPrice($this->discount_invoiced, $this->order->order_currency_code),
            'base_discount_invoiced'            => (float) $this->base_discount_invoiced,
            'formated_base_discount_invoiced'   => (string) core()->formatBasePrice($this->base_discount_invoiced),
            'discount_refunded'                 => (float) $this->discount_refunded,
            'formated_discount_refunded'        => (string) core()->formatPrice($this->discount_refunded, $this->order->order_currency_code),
            'base_discount_refunded'            => (float) $this->base_discount_refunded,
            'formated_base_discount_refunded'   => (string) core()->formatBasePrice($this->base_discount_refunded),
            'tax_percent'                       => $this->tax_percent,
            'tax_amount'                        => (float) $this->tax_amount,
            'formated_tax_amount'               => (string) core()->formatPrice($this->tax_amount, $this->order->order_currency_code),
            'base_tax_amount'                   => (float) $this->base_tax_amount,
            'formated_base_tax_amount'          => (string) core()->formatBasePrice($this->base_tax_amount),
            'tax_amount_invoiced'               => (float) $this->tax_amount_invoiced,
            'formated_tax_amount_invoiced'      => (string) core()->formatPrice($this->tax_amount_invoiced, $this->order->order_currency_code),
            'base_tax_amount_invoiced'          => (float) $this->base_tax_amount_invoiced,
            'formated_base_tax_amount_invoiced' => (string) core()->formatBasePrice($this->base_tax_amount_invoiced),
            'tax_amount_refunded'               => (float) $this->tax_amount_refunded,
            'formated_tax_amount_refunded'      => (string) core()->formatPrice($this->tax_amount_refunded, $this->order->order_currency_code),
            'base_tax_amount_refunded'          => (float) $this->base_tax_amount_refunded,
            'formated_base_tax_amount_refunded' => (string) core()->formatBasePrice($this->base_tax_amount_refunded),
            'grant_total'                       => (float) ($this->total + $this->tax_amount),
            'formated_grant_total'              => (string) core()->formatPrice($this->total + $this->tax_amount, $this->order->order_currency_code),
            'base_grant_total'                  => (float) ($this->base_total + $this->base_tax_amount),
            'formated_base_grant_total'         => (string) core()->formatPrice($this->base_total + $this->base_tax_amount, $this->order->order_currency_code),
            'downloadable_links'                => $this->downloadable_link_purchased,
            'additional'                        => is_array($this->resource->additional)
                                                    ? $this->resource->additional
                                                    : json_decode($this->resource->additional, true),
            'child'                             => new self($this->child),
            'children'                          => Self::collection($this->children)
        ];
    }
}