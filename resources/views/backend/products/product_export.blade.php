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
                <th>Product</th>
                <th>Type</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Purchase Price</th>
                <th>Sell Price</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td> {{$item->name}}</td>
                <td>{{$item->type}}</td>
                <td>{{ $item->category?$item->category->name:''}}</td>
                <td>{{ $item->brand?$item->brand->name:''}}</td>
                <td>{{$item->purchase_price}}</td>
                <td>{{$item->sell_price}}</td>
                <td>{{ $item->stocks->sum('quantity')}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>