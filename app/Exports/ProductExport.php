<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Product;

class ProductExport implements FromView
{
    public function view(): View
    {
        return view('backend.products.product_export', ['items' => Product::all()]);
    }

}
