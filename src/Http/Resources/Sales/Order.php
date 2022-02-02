<?php

namespace Webkul\API\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\API\Http\Resources\Core\Channel as ChannelResource;
use Webkul\API\Http\Resources\Customer\Customer as CustomerResource;

class Order extends JsonResource
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
            'id'                                 => $this->id,
            'increment_id'                       => $this->increment_id,
            'status'                             => $this->status,
            'status_label'                       => $this->status_label,
            'channel_name'                       => $this->channel_name,
            'is_guest'                           => $this->is_guest,
            'customer_email'                     => $this->customer_email,
            'customer_first_name'                => $this->customer_first_name,
            'customer_last_name'                 => $this->customer_last_name,
            'shipping_method'                    => $this->shipping_method,
            'shipping_title'                     => $this->shipping_title,
            'payment_title'                      => core()->getConfigData('sales.paymentmethods.' . $this->payment->method . '.title'),
            'shipping_description'               => $this->shipping_description,
            'coupon_code'                        => $this->coupon_code,
            'is_gift'                            => $this->is_gift,
            'total_item_count'                   => $this->total_item_count,
            'total_qty_ordered'                  => $this->total_qty_ordered,
            'base_currency_code'                 => $this->base_currency_code,
            'channel_currency_code'              => $this->channel_currency_code,
            'order_currency_code'                => $this->order_currency_code,
            'grand_total'                        => (float) $this->grand_total,
            'formated_grand_total'               => (string) core()->formatPrice($this->grand_total, $this->order_currency_code),
            'base_grand_total'                   => (float) $this->base_grand_total,
            'formated_base_grand_total'          => (string) core()->formatBasePrice($this->base_grand_total),
            'grand_total_invoiced'               => (float) $this->grand_total_invoiced,
            'formated_grand_total_invoiced'      => (string) core()->formatPrice($this->grand_total_invoiced, $this->order_currency_code),
            'base_grand_total_invoiced'          => (float) $this->base_grand_total_invoiced,
            'formated_base_grand_total_invoiced' => (string) core()->formatBasePrice($this->base_grand_total_invoiced),
            'grand_total_refunded'               => (float) $this->grand_total_refunded,
            'formated_grand_total_refunded'      => (string) core()->formatPrice($this->grand_total_refunded, $this->order_currency_code),
            'base_grand_total_refunded'          => (float) $this->base_grand_total_refunded,
            'formated_base_grand_total_refunded' => (string) core()->formatBasePrice($this->base_grand_total_refunded),
            'sub_total'                          => (float) $this->sub_total,
            'formated_sub_total'                 => (string) core()->formatPrice($this->sub_total, $this->order_currency_code),
            'base_sub_total'                     => (float) $this->base_sub_total,
            'formated_base_sub_total'            => (string) core()->formatBasePrice($this->base_sub_total),
            'sub_total_invoiced'                 => (float) $this->sub_total_invoiced,
            'formated_sub_total_invoiced'        => (string) core()->formatPrice($this->sub_total_invoiced, $this->order_currency_code),
            'base_sub_total_invoiced'            => (float) $this->base_sub_total_invoiced,
            'formated_base_sub_total_invoiced'   => (string) core()->formatBasePrice($this->base_sub_total_invoiced),
            'sub_total_refunded'                 => (float) $this->sub_total_refunded,
            'formated_sub_total_refunded'        => (string) core()->formatPrice($this->sub_total_refunded, $this->order_currency_code),
            'discount_percent'                   => $this->discount_percent,
            'discount_amount'                    => (float) $this->discount_amount,
            'formated_discount_amount'           => (string) core()->formatPrice($this->discount_amount, $this->order_currency_code),
            'base_discount_amount'               => (float) $this->base_discount_amount,
            'formated_base_discount_amount'      => (string) core()->formatBasePrice($this->base_discount_amount),
            'discount_invoiced'                  => (float) $this->discount_invoiced,
            'formated_discount_invoiced'         => (string) core()->formatPrice($this->discount_invoiced, $this->order_currency_code),
            'base_discount_invoiced'             => (float) $this->base_discount_invoiced,
            'formated_base_discount_invoiced'    => (string) core()->formatBasePrice($this->base_discount_invoiced),
            'discount_refunded'                  => (float) $this->discount_refunded,
            'formated_discount_refunded'         => (string) core()->formatPrice($this->discount_refunded, $this->order_currency_code),
            'base_discount_refunded'             => (float) $this->base_discount_refunded,
            'formated_base_discount_refunded'    => (string) core()->formatBasePrice($this->base_discount_refunded),
            'tax_amount'                         => (float) $this->tax_amount,
            'formated_tax_amount'                => (string) core()->formatPrice($this->tax_amount, $this->order_currency_code),
            'base_tax_amount'                    => (float) $this->base_tax_amount,
            'formated_base_tax_amount'           => (string) core()->formatBasePrice($this->base_tax_amount),
            'tax_amount_invoiced'                => (float) $this->tax_amount_invoiced,
            'formated_tax_amount_invoiced'       => (string) core()->formatPrice($this->tax_amount_invoiced, $this->order_currency_code),
            'base_tax_amount_invoiced'           => (float) $this->base_tax_amount_invoiced,
            'formated_base_tax_amount_invoiced'  => (string) core()->formatBasePrice($this->base_tax_amount_invoiced),
            'tax_amount_refunded'                => (float) $this->tax_amount_refunded,
            'formated_tax_amount_refunded'       => (string) core()->formatPrice($this->tax_amount_refunded, $this->order_currency_code),
            'base_tax_amount_refunded'           => (float) $this->base_tax_amount_refunded,
            'formated_base_tax_amount_refunded'  => (string) core()->formatBasePrice($this->base_tax_amount_refunded),
            'shipping_amount'                    => (float) $this->shipping_amount,
            'formated_shipping_amount'           => (string) core()->formatPrice($this->shipping_amount, $this->order_currency_code),
            'base_shipping_amount'               => (float) $this->base_shipping_amount,
            'formated_base_shipping_amount'      => (string) core()->formatBasePrice($this->base_shipping_amount),
            'shipping_invoiced'                  => (float) $this->shipping_invoiced,
            'formated_shipping_invoiced'         => (string) core()->formatPrice($this->shipping_invoiced, $this->order_currency_code),
            'base_shipping_invoiced'             => (float) $this->base_shipping_invoiced,
            'formated_base_shipping_invoiced'    => (string) core()->formatBasePrice($this->base_shipping_invoiced),
            'shipping_refunded'                  => (float) $this->shipping_refunded,
            'formated_shipping_refunded'         => core()->formatPrice($this->shipping_refunded, $this->order_currency_code),
            'base_shipping_refunded'             => (float) $this->base_shipping_refunded,
            'formated_base_shipping_refunded'    => (string) core()->formatBasePrice($this->base_shipping_refunded),
            'customer'                           => $this->when($this->customer_id, new CustomerResource($this->customer)),
            'channel'                            => $this->when($this->channel_id, new ChannelResource($this->channel)),
            'shipping_address'                   => new OrderAddress($this->shipping_address),
            'billing_address'                    => new OrderAddress($this->billing_address),
            'items'                              => OrderItem::collection($this->items),
            'invoices'                           => Invoice::collection($this->invoices),
            'shipments'                          => Shipment::collection($this->shipments),
            'updated_at'                         => $this->updated_at->format('Y-m-d'),
            'created_at'                         => $this->created_at->format('Y-m-d'),
        ];
    }
}