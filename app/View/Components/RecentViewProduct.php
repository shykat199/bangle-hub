<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Product;
class RecentViewProduct extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $recent_product = session()->get('recent_product', []);

        $items=Product::whereIn('id',$recent_product)->take(4)->get();
        return view('components.recent-view-product', compact('items'));
    }
}
