<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;

class OrderDetails extends Model
{
    use HasFactory;
    // order_details.deleted_at already exists in the DB, but this trait was
    // never turned on — so OrderController::destroy()'s $item->details()->delete()
    // hard-deleted every order line the moment an order was trashed, and
    // restore_order()/forceDel()'s ->withTrashed() calls threw
    // "Call to undefined method" because the model had no such method at all.
    use SoftDeletes;
    protected $guarded=[];

    protected $casts = [
        'is_preorder'              => 'boolean',
        'preorder_stock_allocated' => 'boolean',
        'estimated_delivery_start' => 'date',
        'estimated_delivery_end'   => 'date',
    ];

    /** "Estimated Delivery: 15–20 October" as promised on this line. */
    public function deliveryLabel(): ?string
    {
        return \App\Services\PreOrderService::formatDelivery(
            $this->estimated_delivery_text,
            $this->estimated_delivery_start,
            $this->estimated_delivery_end
        );
    }

    public function product(){

        return $this->belongsTo(Product::class);
    }


    public function order(){

        return $this->belongsTo(Order::class);
    }
    
    public function variation(){

        return $this->belongsTo(Variation::class);
    }

}
