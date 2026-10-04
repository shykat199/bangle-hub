<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeSectionImage;
use App\Models\HomeCategory;
use App\Models\Slider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Type;
use App\Models\AboutUs;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Order;

class HomeController extends Controller
{
    public function sendSMs(){

    }

    public function home(){
        // ✅ OPTIMIZED: All home page queries with eager loading + minimal queries

        $sliders = Slider::latest()->take(10)->get();
        $brands = Type::whereNotNull('is_top')->take(12)->get();

        $featured_images = HomeSectionImage::first();

        // Only the categories the admin has switched on for the home page.
        $home_categories = HomeCategory::with('category')
                            ->where('status', 1)
                            ->orderBy('serial', 'asc')
                            ->paginate(10);

        $categoryIds = $home_categories->pluck('category_id')->filter()->unique()->values()->toArray();

        // 10 products per category for the home sliders: recommended ones first,
        // then by priority, then newest. One small query per home category.
        $homeProducts = [];
        foreach ($categoryIds as $categoryId) {
            $products = Product::inCategory([$categoryId])
                            ->where('status', 1)
                            // কার্ড এখন resolveStock() ডাকে, তাই stocks-ও eager-load।
                            // getProductInfo() এখন ভ্যারিয়েবল প্রোডাক্টের দাম variation থেকে পড়ে,
                            // তাই price/after_discount_price কলাম না আনলে কার্ডে দাম ০ দেখাতো।
                            ->with(['category:id,name,url', 'variations:id,product_id,price,after_discount_price,stock_quantity', 'variations.stocks', 'images'])
                            ->orderByDesc('is_recommended')
                            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
                            ->latest()
                            ->take(10)
                            ->get();

            if ($products->isNotEmpty()) {
                $homeProducts[$categoryId] = $products;
            }
        }

        // ✅ Pre-fetch HomeCategory cover images (was N+1 inside view)
        $homeCategoryCovers = HomeCategory::whereIn('category_id', $categoryIds)
                                ->pluck('cover_image', 'category_id');

        $popular_products = Product::where('is_popular', 1)
                        ->where('status', 1)
                        ->with(['category:id,name,url', 'variations:id,product_id,price,after_discount_price,stock_quantity', 'variations.stocks', 'images'])
                        ->latest()
                        ->take(12)
                        ->get();

        return view('frontend.home', compact(
            'sliders','brands','featured_images',
            'homeProducts','popular_products','homeCategoryCovers'
        ));
    }

    public function pageName($page){
        $page = Page::where('page', $page)->first();
        return view('frontend.about_us', compact('page'));
    }

    public function aboutUs(){
        $page = Page::where('page','about')->first();
        return view('frontend.about_us', compact('page'));
    }

    public function contactUs(){
        return view('frontend.contact_us');
    }

    public function privacyPolicy(){
        $page = Page::where('page','privacy-policy')->first();
        return view('frontend.privacy_policy', compact('page'));
    }

    public function termCondition(){
        $page = Page::where('page','term')->first();
        return view('frontend.term_and_condition', compact('page'));
    }

    public function faq(){
        return view('frontend.faq');
    }

    public function returnPolicy(){
        $page = Page::where('page','return-policy')->first();
        return view('frontend.return_policy', compact('page'));
    }

    // Exit-intent popup daily counters (shows / code copies); orders are
    // counted separately through orders.coupon_code
    public function exitPopupTrack(Request $request){
        $type = $request->input('type');
        if (!in_array($type, ['show', 'copy'], true)) {
            return response()->json(['ok' => false], 422);
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('exit_popup_stats')) {
            return response()->json(['ok' => true]);
        }

        $today = date('Y-m-d');
        \Illuminate\Support\Facades\DB::table('exit_popup_stats')->updateOrInsert(
            ['stat_date' => $today],
            ['updated_at' => now()]
        );
        \Illuminate\Support\Facades\DB::table('exit_popup_stats')
            ->where('stat_date', $today)
            ->increment($type === 'show' ? 'shows' : 'copies');

        return response()->json(['ok' => true]);
    }

    public function contact(Request $request){
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required|numeric|digits:11|regex:/(01)[0-9]{9}/',
            'email' => '',
            'message' => 'required',
        ]);

        Contact::create($data);

        return response()->json(['success'=>true,'msg'=>'Successfully Created Your Info!']);
    }

    public function orderTrack(Request $request){

        if ($request->ajax()) {
            $mobile = $request->mobile;
            $orders = Order::where('mobile', $mobile)
                            ->orderBy('id', 'desc')
                            ->get();

            if($orders->count() > 0){
                $html = '';
                foreach($orders as $order){

                    $badgeClass = 'bg-secondary';
                    $orderStatus = strtolower(trim($order->status ?? ''));
                    if($orderStatus == 'pending') $badgeClass = 'bg-warning text-dark';
                    elseif($orderStatus == 'processing') $badgeClass = 'bg-info text-white';
                    elseif(in_array($orderStatus, ['courier', 'shipped', 'courier complete'])) $badgeClass = 'bg-primary';
                    elseif(in_array($orderStatus, ['complete', 'delivered'])) $badgeClass = 'bg-success';
                    elseif(in_array($orderStatus, ['cancell', 'cancelled', 'return', 'returning', 'return received'])) $badgeClass = 'bg-danger';

                    $html .= '
                    <div class="order-item p-3 mb-3 bg-white" style="border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <div>
                                <span class="text-muted d-block mb-1" style="font-size: 12px;"><i class="fas fa-file-invoice me-1" style="color: #2563eb;"></i> Invoice & Order ID</span>

                                <span class="fw-bold" style="color: #1e293b; font-size: 15px;">#'.$order->invoice_no.'</span>
                                <span class="badge bg-light text-dark border ms-1" style="font-size: 11px;">ID: '.$order->id.'</span>

                            </div>
                            <div class="text-end">
                                <span class="text-muted d-block mb-1" style="font-size: 12px;"><i class="far fa-calendar-alt me-1" style="color: #2563eb;"></i> Date</span>
                                <span class="fw-bold text-dark" style="font-size: 13px;">'.$order->created_at->format('d M, Y').'</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fw-medium" style="font-size: 13px;"><i class="fas fa-money-bill-wave me-1 text-success"></i> Total Amount:</span>
                            <span class="fw-bold" style="color: #059669; font-size: 15px;">৳ '.number_format($order->final_amount).'</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <span class="text-muted fw-medium" style="font-size: 13px;"><i class="fas fa-info-circle me-1 text-info"></i> Status:</span>
                            <span class="badge rounded-pill px-3 py-1 '.$badgeClass.'" style="font-size: 11px; font-weight: 600; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">'.ucfirst($order->status).'</span>
                        </div>
                    </div>';
                }

                return response()->json(['status' => true, 'html' => $html]);
            } else {
                return response()->json(['status' => false]);
            }
        }

        return view('frontend.order_track');
    }
}
