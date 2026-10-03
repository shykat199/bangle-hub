<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Export Student Data</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <tr>
                  <th>Invoice ID</th>
                  <th>Date Order</th>
                  <th>Customers</th>
                  <th>Address</th>
                  <th>Mobile</th>

                  <th>Status</th>
                  <th>Payment Status</th>
                  <th>Item</th>
                  <th>Amount</th>
                  <th>Discount</th>

                  <th>Paid</th>
                  <th>Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>#{{$item->invoice_no}} </td>
                <td>{{ dateFormate($item->date)}}</td>
                <td>
                  {{$item->first_name.' '.$item->last_name}}
                </td>
                <td>{{$item->shipping_address}}</td>
                <td>{{$item->mobile}}</td>
                <td>{{$item->status}}</td>
                <td>{{$item->payment_status}}</td>
                <td>{{$item->details->count()}}</td>
                <td>{{$item->final_amount}}</td>
                <td>{{$item->discount}}</td>
                <td>{{ $item->payments->sum('amount') }}</td>
                <td>{{ $item->final_amount - $item->payments->sum('amount') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>