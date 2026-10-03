<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Report</title>
</head>
<body>
    <table>
      	@php $total = 0; $seenOrders = []; @endphp
        <thead>
            <tr>
                <tr>
                    <th width="12%" style="font-size: 11px;">Invoice No</th>
                    <th width="12%" style="font-size: 11px;">Customer</th>
                    <th width="10%" style="font-size: 11px;">Phone</th>
                    <th width="26%" style="font-size: 11px;">Address</th>
                    <th width="15%" style="font-size: 11px;">Product</th>
                    <th width="15%" style="font-size: 11px;">Quantity</th>
                    <th width="10%" style="font-size: 11px;">Total</th>
            	</tr>
        </thead>
        <tbody>
            @forelse($details as $item)
              @php
                // Delivery charge belongs to the whole order, not each product line.
                // Add it into the Total ONCE per order (on the order's first line) so
                // delivery is included in the Total without being double-counted.
                $oid = optional($item->order)->id;
                $shipping = 0;
                if ($oid && !in_array($oid, $seenOrders)) {
                    $shipping = (float) (optional($item->order)->shipping_charge ?? 0);
                    $seenOrders[] = $oid;
                }
                $row_total = ($item->unit_price * $item->quantity) + $shipping;
                $total += $row_total;
              @endphp
            <tr>
              <td style="font-size: 11px;color: #000;"><a href="{{ route('admin.orders.show',[$item->order->id])}}" target="_blank" class="fw-bold" style="color: #000;">#{{$item->order->invoice_no}}</a> </td>
              <td style="font-size: 11px;color: #000;">{{$item->order->first_name}}</td>
              <td style="font-size: 11px;color: #000;">{{$item->order->mobile}}</td>
              <td style="font-size: 11px;color: #000;">{{$item->order->shipping_address}}</td>
              <td style="font-size: 11px;color: #000;">{{$item->product->name}}</td>
              <td style="font-size: 11px;color: #000;">{{$item->quantity}}</td>
              <td style="font-size: 11px;color: #000;">{{ $row_total }}</td>
            </tr>
            @empty
            <center>
              <h3 class='text-danger'>No data found</h3>
            </center>
            @endforelse
            <tr>
              <td colspan="6" style="font-size: 12px; text-align: right;"><strong>Total Amount (delivery included) :</strong></td>
              <td style="font-size: 12px;color: #000;"><strong>{{ $total }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
