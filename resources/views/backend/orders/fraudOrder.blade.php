@if($result)
<style>
    /* কম্প্যাক্ট ডিজাইন */
    .fraud-container { font-family: 'Inter', sans-serif; color: #333; }
    .courierSummeryFraud tr td img { max-height: 30px; }
    .courierSummeryFraud tr th { 
        background: #f3f4f6; padding: 5px; text-align: center; 
        font-size: 12px; border: 1px solid #dee2e6;
    }
    .courierSummeryFraud tr td { vertical-align: middle; padding: 5px; text-align: center; font-size: 12px; border: 1px solid #dee2e6; }
    
    /* উইজেট ছোট করা */
    .stat-card { padding: 10px !important; border-radius: 10px; margin-bottom: 10px; }
    .stat-card h3 { font-size: 20px !important; margin: 0; }
    .stat-card p { font-size: 12px; margin: 0; }

    /* অর্ডার হিস্ট্রি টেবিল স্ক্রলযোগ্য করা */
    .scroll-table {
        max-height: 250px; /* টেবিলটি এর বেশি বড় হবে না */
        overflow-y: auto;
        border: 1px solid #eee;
        border-radius: 8px;
    }
    .cusOrderTable { font-size: 12px; margin-bottom: 0; }
    .cusOrderTable th { position: sticky; top: 0; background: #fff; z-index: 1; }
</style>

<div class="fraud-container">
    <div style="text-align:center; margin-bottom: 15px;">
        <h4 style="margin:0; font-weight: bold;">Fraud Tracker Report</h4>
        <small class="text-success" style="font-weight: bold;">[ {{$result->customerPhone}} ]</small>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if($result->total_ratio > 0)
            @php
                $progressColor = $result->total_ratio > 50 ? '#10b981' : ($result->total_ratio > 20 ? '#f59e0b' : '#ef4444');
            @endphp
            <div style="padding: 10px; text-align: center; background: #f9fafb; border-radius: 10px; border: 1px solid #eee; margin-bottom: 15px;">
                <p style="margin-bottom: 5px; font-size: 13px; font-weight: 600;">
                    @if($result->total_ratio > 50) Customer is awesome 
                    @elseif($result->total_ratio > 20) Customer is good 
                    @else Customer is not good @endif
                </p>
                <div class="progress" style="height: 15px; background-color: #fee2e2; border-radius: 10px;">
                  <div class="progress-bar" role="progressbar" style="width: {{$result->total_ratio}}%; background-color: {{$progressColor}}; font-size: 10px; line-height: 15px;" aria-valuenow="{{$result->total_ratio}}" aria-valuemin="0" aria-valuemax="100">
                    Success - {{$result->total_ratio}}%
                  </div>
                </div>
            </div>
            @else
            <div style="padding:15px; text-align: center; background: #fee2e2; color: #b91c1c; border-radius: 10px; margin-bottom: 15px;">
                <span style="font-weight: bold;">No Courier Data Found</span>
            </div>
            @endif
        </div>
    </div>

    @if($result->total_ratio > 0)
    <div class="row gx-2">
        <div class="col-4">
            <div class="stat-card text-center" style="background: #eff6ff;">
                <h3 class="text-primary">{{ $result->total_parcels }}</h3>
                <p>Total</p>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card text-center" style="background: #ecfdf5;">
                <h3 class="text-success">{{ $result->total_delivered }}</h3>
                <p>Delivered</p>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card text-center" style="background: #fff1f2;">
                <h3 class="text-danger">{{ $result->total_canceled }}</h3>
                <p>Returned</p>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered courierSummeryFraud">
            <thead>
                <tr>
                    <th>Courier</th>
                    <th>Total</th>
                    <th>Delivered</th>
                    <th>Returned</th>
                    <th>Ratio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result->purcelsdatas as $courier => $data)
                    <tr>
                        <td>
                            <img src="{{ asset('public/backend/images/' . strtolower(str_replace(' ', '', $courier)) . '.png') }}" alt="{{$courier}}">
                        </td>
                        @php $t = $d = $r = 0; @endphp
                        @foreach($data as $key => $value)
                            @if($key == 'Total Parcels' || $key == 'Total Delivery') @php $t = $value; @endphp
                            @elseif($key == 'Delivered Parcels' || $key == 'Successful Delivery') @php $d = $value; @endphp
                            @elseif($key == 'Canceled Parcels' || $key == 'Canceled Delivery') @php $r = $value; @endphp
                            @endif
                        @endforeach
                        <td>{{ $t }}</td>
                        <td>{{ $d }}</td>
                        <td>{{ $r }}</td>
                        <td style="font-weight: bold;">{{ $t > 0 ? round(($d / $t) * 100) : 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <hr style="margin: 10px 0;">

    <p style="font-weight: bold; text-align: center; font-size: 13px; margin-bottom: 5px;">Internal Order History</p>
    
    <div class="scroll-table">
        @php
            $number = $result->customerPhone ?: '000000000000';
            $oldOrders = App\Models\Order::whereHas('user', function($q) use ($number) {
                $q->where('mobile','like','%' . $number . '%');
            })->orderBy('id','desc')->get();
        @endphp

        <table class="table table-bordered cusOrderTable">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Product & Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($oldOrders as $oldOrder)
                <tr>
                    <td style="vertical-align: top; font-weight: bold; color: #2563eb;">#{{$oldOrder->invoice_no}}</td>
                    <td>
                        @foreach($oldOrder->details()->whereHas('product')->get() as $item)
                            <div style="display: flex; align-items: center; margin-bottom: 3px;">
                                <img src="{{ getImage('products', $item->product->image)}}" style="width:25px; height:25px; border-radius: 4px; margin-right: 8px;">
                                <div style="line-height: 1.2;">
                                    <span style="font-size: 11px;">{{ Str::limit($item->product->name, 40) }}</span><br>
                                    <small class="text-muted">{{number_format($item->unit_price)}} TK x {{ $item->quantity }}</small>
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-1">
                            <span class="badge {{ strtolower($oldOrder->status) == 'delivered' ? 'bg-success' : 'bg-secondary' }}" style="font-size: 9px;">{{$oldOrder->status}}</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">No internal orders</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
    <div class="text-center py-4">
        <h3>No Order Found</h3>
    </div>
@endif