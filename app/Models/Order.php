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
    use \Illuminate\Notifications\Notifiable;

    protected $guarded=[];

    protected $casts = [
        'is_preorder'          => 'boolean',
        'due_reminder_sent_at' => 'datetime',
    ];

    // Statuses where the order no longer holds its stock (or pre-order slots).
    // Same list OrderController uses for its stock swing.
    public const INACTIVE_STATUSES = ['cancelled', 'returning', 'return received', 'return missing', 'cancell', 'return'];

    public static function statusHoldsStock($status): bool
    {
        return !in_array(strtolower(trim((string) $status)), self::INACTIVE_STATUSES, true);
    }

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

        // Pre-orders: the due is always "order total minus what has been
        // received", whoever changed either of them (gateway, payment entry,
        // order edit). Marking the order Paid settles it in full.
        static::saving(function ($order) {
            if (empty($order->is_preorder)) {
                return;
            }

            $final = (float) $order->final_amount;
            if (strtolower(trim((string) $order->payment_status)) === 'paid') {
                $order->advance_paid = $final;
            }

            $order->advance_paid = round(min($final, max(0, (float) $order->advance_paid)), 2);
            $order->due_amount   = round(max(0, $final - (float) $order->advance_paid), 2);
        });

        // Every status change of a pre-order — status dropdown, bulk update,
        // edit form, courier push — lands here: stock is allocated once the
        // order leaves "Pre-Order" and the customer is emailed.
        static::updated(function ($order) {
            if (empty($order->is_preorder) || !$order->wasChanged('status')) {
                return;
            }

            app(\App\Services\PreOrderService::class)->handleStatusChanged($order, $order->getOriginal('status'));
        });
    }

    /** Pre-order emails go to the address given at checkout. */
    public function routeNotificationForMail($notification = null)
    {
        return $this->email;
    }

    /**
     * What a payment gateway should charge right now: the advance for an
     * advance pre-order that has not paid it yet, otherwise the order total.
     */
    public function payableNow(): float
    {
        if (empty($this->is_preorder)) {
            return (float) $this->final_amount;
        }

        if ($this->payment_mode === 'advance' && (float) $this->advance_paid < (float) $this->advance_amount) {
            return round((float) $this->advance_amount - (float) $this->advance_paid, 2);
        }

        return round(max(0, (float) $this->final_amount - (float) $this->advance_paid), 2);
    }

    /**
     * The least a gateway callback must report as paid. A pre-order that has
     * already been settled expects nothing more, so a repeated callback
     * (customer refreshes the return page) is not flagged as underpaid.
     */
    public function gatewayMinimum(): float
    {
        if (!empty($this->is_preorder) && (float) $this->advance_paid > 0) {
            return 0.0;
        }

        return $this->payableNow();
    }

    public function isAdvancePreorder(): bool
    {
        return !empty($this->is_preorder) && $this->payment_mode === 'advance';
    }

    /** Has the advance of an advance pre-order arrived yet? */
    public function advanceIsPaid(): bool
    {
        return (float) $this->advance_amount > 0 && (float) $this->advance_paid + 0.01 >= (float) $this->advance_amount;
    }

    /** The estimated delivery promised on this order's pre-order lines. */
    public function preorderDeliveryLabel(): ?string
    {
        $labels = $this->details
            ->where('is_preorder', true)
            ->map(fn ($line) => $line->deliveryLabel())
            ->filter()
            ->unique()
            ->values();

        return $labels->isEmpty() ? null : $labels->implode(' / ');
    }

    public function deliveryLogs()
    {
        return $this->hasMany(PreorderDeliveryLog::class)->latest('id');
    }

    public function scopePreorders($query)
    {
        return $query->where('orders.is_preorder', 1);
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
