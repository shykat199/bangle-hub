<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrderDetails;
use App\Models\OrderPayment;
use App\Models\User;
use App\Models\Courier;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];

    // Every order gets an unguessable token at creation; invoice links carry
    // it as ?t= so guests can reopen them any time from any device, while the
    // serial /confirm-order/{id} ids alone stay un-enumerable.
    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->order_token)) {
                $order->order_token = \Illuminate\Support\Str::random(40);
            }
        });
    }

    // Normalizes status casing on every save ('pending' -> 'Pending') so
    // filters and dropdowns always match. See canonicalOrderStatus().
    public function setStatusAttribute($value){
        $this->attributes['status'] = function_exists('canonicalOrderStatus') ? canonicalOrderStatus($value) : $value;
    }

    public function details(){

        return $this->hasMany(OrderDetails::class);
    }

    public function payments(){

        return $this->hasMany(OrderPayment::class);
    }

    public function user(){

        return $this->belongsTo(User::class);
    }

    public function assign(){

        return $this->belongsTo(User::class,'assign_user_id');
    }

    public function courier(){

        return $this->belongsTo(Courier::class);
    }    
  
  	public function delivery_charge(){

        return $this->belongsTo(DeliveryCharge::class, 'delivery_charge_id');
    }
    
    public function getCourierPercent(){
        $ratio =0;
        if($this->user){
            if(isset($this->user->curier_summery)){
                $data = json_decode($this->user->curier_summery, true);
                $data =  $data['Summaries'] ?? [];
    
                $total_parcels =isset($data['Total Parcels'])?$data['Total Parcels']:0;
                $total_delivered =isset($data['Total Delivered'])?$data['Total Delivered']:0;
    
                $ratio = ($total_parcels > 0) ? round(($total_delivered / $total_parcels) * 100,0) : 0;
            }
        }
        return $ratio;
    }
}
