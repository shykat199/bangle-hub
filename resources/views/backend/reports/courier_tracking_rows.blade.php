@forelse($orders as $item)
    @php
        $isPaid = strtolower($item->courier_payment_status ?? '') === 'received';
    @endphp
    <tr>
        <td class="px-1 text-center">
            <input type="checkbox" class="form-check-input ct-check" value="{{ $item->id }}">
        </td>
        <td class="px-1" style="font-size: 12px;">
            {{ $item->courier_sent_at ? \Carbon\Carbon::parse($item->courier_sent_at)->format('d M Y') : '—' }}
            <div class="text-muted" style="font-size: 10px;">
                {{ $item->courier_sent_at ? \Carbon\Carbon::parse($item->courier_sent_at)->format('h:i A') : '' }}
            </div>
        </td>
        <td class="px-1">
            <a href="{{ route('admin.orders.edit', $item->id) }}" target="_blank" class="fw-bold text-primary text-decoration-none" style="font-size: 12px;">#{{ $item->invoice_no }}</a>
            <div class="text-muted" style="font-size: 10px;">ID: {{ $item->id }}</div>
        </td>
        <td class="px-1" style="font-size: 12px;">
            <span class="fw-bold d-block">{{ trim($item->first_name . ' ' . $item->last_name) }}</span>
            <span class="text-muted" style="font-size: 11px;">{{ $item->mobile }}</span>
        </td>
        <td class="px-1" style="font-size: 12px;">
            <span class="badge bg-light text-dark border">{{ optional($item->courier)->name ?? '—' }}</span>
        </td>
        <td class="px-1">
            <span class="fw-bold font-monospace" style="font-size: 12px;">{{ $item->courier_tracking_id }}</span>
            @if($item->courier_tracking_code)
                <div class="text-muted font-monospace" style="font-size: 10px;">{{ $item->courier_tracking_code }}</div>
            @endif
        </td>
        <td class="px-1" style="font-size: 12px;">
            <span class="badge bg-light text-dark border">{{ ucfirst($item->status) }}</span>
        </td>
        <td class="px-1 text-end fw-bold" style="font-size: 12px;">৳{{ number_format($item->final_amount, 0) }}</td>
        <td class="px-1 text-center">
            @if($isPaid)
                <span class="badge bg-success">✔ টাকা পেয়েছি</span>
                <div class="text-muted" style="font-size: 10px;">
                    {{ $item->courier_paid_at ? \Carbon\Carbon::parse($item->courier_paid_at)->format('d M Y') : '' }}
                    @if($item->courier_paid_amount) · ৳{{ number_format($item->courier_paid_amount, 0) }} @endif
                </div>
            @else
                <span class="badge bg-warning text-dark">বাকি</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-4 text-muted" style="font-size: 12px;">
            এই সময়ে কুরিয়ারে পাঠানো কোনো অর্ডার নেই।
        </td>
    </tr>
@endforelse
