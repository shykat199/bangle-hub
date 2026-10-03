<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LandingPage;
use App\Models\LandingPageSlider;
use App\Models\reviewProductImage;
use App\Models\DeliveryCharge;
use App\Models\Product;
use App\Models\Variation;
use App\Models\User;
use App\Models\Order;
use App\Models\LandingPagePackage;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Facades\FacebookConversion;

class LandingPageController extends Controller
{
    public function index()
    {
        $items = LandingPage::where('page_type', '1')->latest()->paginate(20);
        return view('backend.landing_pages.index', compact('items'));
    }

    public function landing_page_two()
    {
        $items = LandingPage::where('page_type', '2')->latest()->paginate(20);
        return view('backend.landing_pages.index_two', compact('items'));
    }

    public function index_three()
    {
        $items = LandingPage::where('page_type', '3')->latest()->paginate(20);
        return view('backend.landing_pages.index_three', compact('items'));
    }

    public function index_four()
    {
        $items = LandingPage::where('page_type', '4')->latest()->paginate(20);
        return view('backend.landing_pages.index_four', compact('items'));
    }

    public function index_five()
    {
        $items = LandingPage::where('page_type', '5')->latest()->paginate(20);
        return view('backend.landing_pages.index_five', compact('items'));
    }

    public function index_six()
    {
        $items = LandingPage::where('page_type', '6')->latest()->paginate(20);
        return view('backend.landing_pages.index_six', compact('items'));
    }

    public function index_seven()
    {
        $items = LandingPage::where('page_type', '7')->latest()->paginate(20);
        return view('backend.landing_pages.index_seven', compact('items'));
    }

    public function index_eight()
    {
        $items = LandingPage::where('page_type', '8')->latest()->paginate(20);
        return view('backend.landing_pages.index_eight', compact('items'));
    }

    public function index_nine()
    {
        $items = LandingPage::where('page_type', '9')->latest()->paginate(20);
        return view('backend.landing_pages.index_nine', compact('items'));
    }

    public function index_ten()
    {
        $items = LandingPage::where('page_type', '10')->latest()->paginate(20);
        return view('backend.landing_pages.index_ten', compact('items'));
    }

    public function index_eleven()
    {
        $items = LandingPage::where('page_type', '11')->latest()->paginate(20);
        return view('backend.landing_pages.index_eleven', compact('items'));
    }

    public function index_twelve()
    {
        $items = LandingPage::where('page_type', '12')->latest()->paginate(20);
        return view('backend.landing_pages.index_twelve', compact('items'));
    }

    public function index_thirteen()
    {
        $items = LandingPage::where('page_type', '13')->latest()->paginate(20);
        return view('backend.landing_pages.index_thirteen', compact('items'));
    }

    public function index_fourteen()
    {
        $items = LandingPage::where('page_type', '14')->latest()->paginate(20);
        return view('backend.landing_pages.index_fourteen', compact('items'));
    }

    public function index_fifteen()
    {
        $items = LandingPage::where('page_type', '15')->latest()->paginate(20);
        return view('backend.landing_pages.index_fifteen', compact('items'));
    }

    public function index_sixteen()
    {
        $items = LandingPage::where('page_type', '16')->latest()->paginate(20);
        return view('backend.landing_pages.index_sixteen', compact('items'));
    }

    public function create()
    {
        return view('backend.landing_pages.create');
    }

    public function create_landing_page_two()
    {
        return view('backend.landing_pages.create_two');
    }

    public function create_three()
    {
        return view('backend.landing_pages.create_three');
    }

    public function create_four()
    {
        return view('backend.landing_pages.create_four');
    }

    public function create_five()
    {
        return view('backend.landing_pages.create_five');
    }

    public function create_six()
    {
        return view('backend.landing_pages.create_six');
    }

    public function create_seven()
    {
        return view('backend.landing_pages.create_seven');
    }

    public function create_eight()
    {
        return view('backend.landing_pages.create_eight');
    }

    public function create_nine()
    {
        return view('backend.landing_pages.create_nine');
    }

    public function create_ten()       { return view('backend.landing_pages.create_ten'); }
    public function create_eleven()    { return view('backend.landing_pages.create_eleven'); }
    public function create_twelve()    { return view('backend.landing_pages.create_twelve'); }
    public function create_thirteen() { return view('backend.landing_pages.create_thirteen'); }
    public function create_fourteen() { return view('backend.landing_pages.create_fourteen'); }
    public function create_fifteen()  { return view('backend.landing_pages.create_fifteen'); }
    public function create_sixteen()  { return view('backend.landing_pages.create_sixteen'); }

    public function getOrderProduct2(Request $request)
    {
        $search = trim((string) $request->search);
        if ($search === '') {
            return response()->json([]);
        }

        // Multi-word search — প্রতিটি শব্দ আলাদা ভাবে match হলে product দেখাবে
        $terms = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY);

        $products = Product::query()
            ->where(function ($outer) use ($terms) {
                foreach ($terms as $term) {
                    $outer->where(function ($q) use ($term) {
                        $like = "%{$term}%";
                        $q->where('name', 'LIKE', $like)
                          ->orWhere('sku',  'LIKE', $like)
                          ->orWhere('slug', 'LIKE', $like)
                          ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', $like))
                          ->orWhereHas('brand',    fn($b) => $b->where('name', 'LIKE', $like));
                    });
                }
            })
            ->select('id', 'name', 'sku', 'sell_price', 'image', 'stock_quantity')
            ->orderByRaw("CASE WHEN `name` LIKE ? THEN 0 ELSE 1 END", ["{$search}%"])
            ->orderBy('name', 'asc')
            ->take(50)
            ->get()
            ->map(function ($p) {
                // jQuery UI autocomplete needs `label` + `value` to render reliably
                $p->label = trim((string) $p->name) . ($p->sku ? "  ·  SKU: {$p->sku}" : '');
                $p->value = trim((string) $p->name);
                return $p;
            });

        return response()->json($products);
    }

    public function landingProductEntry(Request $request)
    {
        $product = Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($request->id);

        if (!$product) {
            return response()->json(['html' => '', 'pr_id' => null, 'variations' => []]);
        }

        $image = function_exists('getImage')
            ? getImage('products', $product->image)
            : asset('products/' . $product->image);

        $html = '<tr>
                    <td>
                        <img src="' . $image . '" height="50" width="50" class="rounded"/>
                    </td>
                    <td>
                        ' . $product->name . ' <br>
                        <small class="text-muted">SKU: ' . $product->sku . '</small>
                    </td>
                    <td>
                        ' . ($product->sell_price ?? 0) . ' Tk
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-product">
                            <i class="mdi mdi-delete"></i> Remove
                        </button>
                    </td>
                 </tr>';

        if ($product->variations->count() > 0) {
            $variations = $product->variations->map(function ($v) {
                $size  = $v->size->name ?? '';
                $color = $v->color->name ?? '';
                $stock = $v->stocks->sum('quantity');

                return [
                    'id' => $v->id,
                    'size_name' => $size,
                    'color_name' => $color,
                    'after_discount_price' => $v->after_discount_price,
                    'price' => $v->price,
                    'stock' => (int)$stock
                ];
            });
        } else {
            $variations = collect([[
                'id' => '',
                'size_name' => 'Single',
                'color_name' => 'Product',
                'after_discount_price' => $product->after_discount,
                'price' => $product->sell_price,
                'stock' => (int)($product->stock_quantity ?? 0)
            ]]);
        }

        return response()->json([
            'html' => $html,
            'pr_id' => $product->id,
            'variations' => $variations
        ]);
    }

    private function normalizeProductId(Request $request): void
    {
        $pid = $request->input('product_id');
        if (is_array($pid)) $pid = $pid[0] ?? null;
        $pid = is_string($pid) ? trim($pid) : $pid;

        if ($pid === '' || $pid === null) $pid = null;
        elseif (preg_match('/^\d+$/', (string) $pid)) $pid = (int) $pid;
        else $pid = null;

        $npid = $request->input('new_product_id');
        if (is_array($npid)) $npid = $npid[0] ?? null;
        $npid = is_string($npid) ? trim($npid) : $npid;

        if ($npid !== null && $npid !== '' && preg_match('/^\d+$/', (string) $npid)) {
            $request->merge(['new_product_id' => (int) $npid]);
        } else {
            $request->merge(['new_product_id' => null]);
        }

        $request->merge(['product_id' => $pid]);
    }

    private function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson() || $request->expectsJson();
    }

    public function store(Request $request)
    {
        return $this->processStore($request, '1');
    }

    public function store_landing_page_two(Request $request)
    {
        return $this->processStore($request, '2');
    }

    public function store_three(Request $request)
    {
        return $this->processStore($request, '3');
    }

    public function store_four(Request $request)
    {
        return $this->processStore($request, '4');
    }

    public function store_five(Request $request)
    {
        return $this->processStore($request, '5');
    }

    public function store_six(Request $request)
    {
        return $this->processStore($request, '6');
    }

    public function store_seven(Request $request)
    {
        return $this->processStore($request, '7');
    }

    public function store_eight(Request $request)
    {
        return $this->processStore($request, '8');
    }

    public function store_nine(Request $request)     { return $this->processStore($request, '9'); }
    public function store_ten(Request $request)      { return $this->processStore($request, '10'); }
    public function store_eleven(Request $request)   { return $this->processStore($request, '11'); }
    public function store_twelve(Request $request)   { return $this->processStore($request, '12'); }
    public function store_thirteen(Request $request) { return $this->processStore($request, '13'); }
    public function store_fourteen(Request $request) { return $this->processStore($request, '14'); }
    public function store_fifteen(Request $request)  { return $this->processStore($request, '15'); }
    public function store_sixteen(Request $request)  { return $this->processStore($request, '16'); }

    /**
     * URL এ id-র বদলে অ্যাডমিনের দেওয়া slug ব্যবহারের সুযোগ। খালি রাখলে
     * title1 থেকে বানানো হয়। প্রোডাক্টের slug-এ যে বাগ ছিল তার পুনরাবৃত্তি
     * এড়াতে — এই মেথড শুধু তৈরির সময় আর অ্যাডমিন সরাসরি বদলাতে চাইলেই ডাকা
     * হয়; edit ফর্মে খালি রাখলে processUpdate() পুরনো slug-ই রেখে দেয়,
     * প্রতিবার সেভে টাইটেল থেকে আবার বানায় না — নাহলে পুরনো লিংক ভেঙে যেত।
     */
    private function uniqueLandingSlug(?string $preferred, string $fallbackTitle, ?int $excludeId = null): string
    {
        $base = Str::slug($preferred ?: $fallbackTitle);
        if ($base === '') {
            $base = 'landing-page';
        }

        $slug = $base;
        $count = 1;
        while (
            LandingPage::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    private function processStore(Request $request, $pageType)
    {
        $this->normalizeProductId($request);

        $data = $request->validate([
            'title1' => 'required',
            'title2' => 'nullable',
            'slug' => 'nullable|string|max:191|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'video_url' => 'nullable',
            'landing_bg' => 'nullable',
            'feature' => 'nullable',
            'review_top_text' => 'nullable',
            'old_price' => 'nullable',
            'new_price' => 'nullable',
            'phone' => 'nullable',
            'pay_text' => 'nullable',
            'product_id' => 'nullable|integer',
            'variation_id' => 'nullable',
            'regular_price_text' => 'nullable',
            'offer_price_text' => 'nullable',
            'call_text' => 'nullable',
            'left_side_title' => 'nullable',
            'left_side_desc' => 'nullable',
            'right_side_title' => 'nullable',
            'right_side_desc' => 'nullable',
            'top_heading_text' => 'nullable',
            'left_product_details' => 'nullable',
            'theme_primary_col' => 'nullable',
            'theme_gradient_col' => 'nullable',
            'btn_bg_color' => 'nullable',
            'btn_text_color' => 'nullable',
            'btn_text_hero' => 'nullable',
            'btn_text_video' => 'nullable',
            'btn_text_feature' => 'nullable',
            'btn_text_form' => 'nullable',
            'hot_badge_text' => 'nullable',
            'warranty_text' => 'nullable',
            'order_btn_text' => 'nullable',
            'dhamaka_title' => 'nullable',
            'offer_price_label' => 'nullable',
            'currency_text' => 'nullable',
            'call_to_action_text' => 'nullable',
            'feature_title' => 'nullable',
            'feature_list' => 'nullable',
            'details_title' => 'nullable',
            'review_title' => 'nullable',
            'form_title' => 'nullable',
            'form_subtitle' => 'nullable',
            'name_label' => 'nullable',
            'name_placeholder' => 'nullable',
            'phone_label' => 'nullable',
            'address_label' => 'nullable',
            'address_placeholder' => 'nullable',
            'delivery_label' => 'nullable',
            'payment_title' => 'nullable',
            'cod_title' => 'nullable',
            'cod_subtitle' => 'nullable',
            'online_payment_title' => 'nullable',
            'online_payment_subtitle' => 'nullable',
            'order_summary_title' => 'nullable',
            'variation_label' => 'nullable',
            'total_bill_label' => 'nullable',
            'processing_text' => 'nullable',
            'error_msg' => 'nullable',
            'countdown_title' => 'nullable',
            'countdown_bg_color' => 'nullable',
            'countdown_text_color' => 'nullable',
            'hero_btn_bg_color' => 'nullable',
            'hero_btn_text_color' => 'nullable',
            'countdown_hours' => 'nullable|integer',
            'old_price_text' => 'nullable',
            'new_price_text' => 'nullable',
            'promise_badge' => 'nullable', 
            'promise_title' => 'nullable', 
            'promise_img_badge' => 'nullable',
            'identify_badge' => 'nullable', 
            'identify_title' => 'nullable', 
            'identify_subtitle' => 'nullable',
            'trust_sec_title' => 'nullable',
            'trust_1_icon' => 'nullable', 
            'trust_1_title' => 'nullable',
            'trust_2_icon' => 'nullable', 
            'trust_2_title' => 'nullable',
            'trust_3_icon' => 'nullable', 
            'trust_3_title' => 'nullable',
            'trust_4_icon' => 'nullable', 
            'trust_4_title' => 'nullable',
            'promise_1_title' => 'nullable', 
            'promise_1_desc' => 'nullable',
            'promise_2_title' => 'nullable', 
            'promise_2_desc' => 'nullable',
            'promise_3_title' => 'nullable', 
            'promise_3_desc' => 'nullable',
            'negative_title' => 'nullable', 
            'negative_tags' => 'nullable',
            
            // ✅ New 3.5 Section Details (Organic Features)
            'special_feature_title' => 'nullable',
            'sf_1_title' => 'nullable', 'sf_1_desc' => 'nullable',
            'sf_2_title' => 'nullable', 'sf_2_desc' => 'nullable',
            'sf_3_title' => 'nullable', 'sf_3_desc' => 'nullable',
            'sf_4_title' => 'nullable', 'sf_4_desc' => 'nullable',
            'sf_5_title' => 'nullable', 'sf_5_desc' => 'nullable',
            'sf_6_title' => 'nullable', 'sf_6_desc' => 'nullable',
            'sf_7_title' => 'nullable', 'sf_7_desc' => 'nullable',
            'sf_8_title' => 'nullable', 'sf_8_desc' => 'nullable',

            'id_1_icon' => 'nullable', 'id_1_title' => 'nullable', 'id_1_desc' => 'nullable',
            'id_2_icon' => 'nullable', 'id_2_title' => 'nullable', 'id_2_desc' => 'nullable',
            'id_3_icon' => 'nullable', 'id_3_title' => 'nullable', 'id_3_desc' => 'nullable',
            'id_4_icon' => 'nullable', 'id_4_title' => 'nullable', 'id_4_desc' => 'nullable',
            'id_5_icon' => 'nullable', 'id_5_title' => 'nullable', 'id_5_desc' => 'nullable',
            'id_6_icon' => 'nullable', 'id_6_title' => 'nullable', 'id_6_desc' => 'nullable',
            'id_7_icon' => 'nullable', 'id_7_title' => 'nullable', 'id_7_desc' => 'nullable',
            'id_8_icon' => 'nullable', 'id_8_title' => 'nullable', 'id_8_desc' => 'nullable',
            'review_badge' => 'nullable', 
            'review_subtitle' => 'nullable',
            'stat_1_num' => 'nullable', 'stat_1_text' => 'nullable',
            'stat_2_num' => 'nullable', 'stat_2_text' => 'nullable',
            'stat_3_num' => 'nullable', 'stat_3_text' => 'nullable',
            'rev_1_text' => 'nullable', 'rev_1_name' => 'nullable', 'rev_1_loc' => 'nullable',
            'rev_2_text' => 'nullable', 'rev_2_name' => 'nullable', 'rev_2_loc' => 'nullable',
            'rev_3_text' => 'nullable', 'rev_3_name' => 'nullable', 'rev_3_loc' => 'nullable',
            'rev_4_text' => 'nullable', 'rev_4_name' => 'nullable', 'rev_4_loc' => 'nullable',
            'faq_badge' => 'nullable', 'faq_title' => 'nullable',
            'faq_1_q' => 'nullable', 'faq_1_a' => 'nullable',
            'faq_2_q' => 'nullable', 'faq_2_a' => 'nullable',
            'faq_3_q' => 'nullable', 'faq_3_a' => 'nullable',
            'faq_4_q' => 'nullable', 'faq_4_a' => 'nullable',

            // Landing Page 9 fields
            'hero_rating' => 'nullable', 'hero_rating_count' => 'nullable', 'hero_rating_label' => 'nullable',
            'discount_save_text' => 'nullable',
            'spec_title' => 'nullable',
            'spec_1_label' => 'nullable', 'spec_1_value' => 'nullable',
            'spec_2_label' => 'nullable', 'spec_2_value' => 'nullable',
            'spec_3_label' => 'nullable', 'spec_3_value' => 'nullable',
            'spec_4_label' => 'nullable', 'spec_4_value' => 'nullable',
            'spec_5_label' => 'nullable', 'spec_5_value' => 'nullable',
            'spec_6_label' => 'nullable', 'spec_6_value' => 'nullable',
            'spec_7_label' => 'nullable', 'spec_7_value' => 'nullable',
            'stock_count' => 'nullable|integer',
            'stock_text' => 'nullable', 'urgency_title' => 'nullable', 'urgency_subtitle' => 'nullable',
            'final_cta_title' => 'nullable', 'final_cta_subtitle' => 'nullable', 'final_cta_btn_text' => 'nullable',
            'footer_company' => 'nullable', 'footer_email' => 'nullable', 'footer_copyright' => 'nullable',
            'security_badge_text' => 'nullable',

            // ✅ Landing Pages 14 / 15 / 16 — new dynamic text fields
            'brand_sub' => 'nullable', 'spec_badge' => 'nullable',
            'identify_desc' => 'nullable', 'form_desc' => 'nullable',
            'footer_contact_label' => 'nullable', 'footer_address_label' => 'nullable',
            'spec_8_label' => 'nullable', 'spec_8_value' => 'nullable',
            'faq_5_q' => 'nullable', 'faq_5_a' => 'nullable',
            'team_badge' => 'nullable', 'team_pick_btn' => 'nullable',
            'promise_1_badge' => 'nullable', 'promise_2_badge' => 'nullable',
            'gallery_badge' => 'nullable', 'form_badge' => 'nullable',
            'btn_text_top' => 'nullable', 'hero_small_stats' => 'nullable',
            'footer_desc' => 'nullable',
            'whatsapp' => 'nullable',
        ]);

        $data['page_type'] = $pageType;

        if ($request->filled('variation_id') && is_numeric($request->variation_id)) {
            if (!empty($data['product_id'])) {
                $ok = Variation::where('id', $request->variation_id)
                    ->where('product_id', $data['product_id'])
                    ->exists();
                $data['variation_id'] = $ok ? (int) $request->variation_id : null;
            } else {
                $data['variation_id'] = (int) $request->variation_id;
            }
        } else {
            $data['variation_id'] = null;
        }

        if ($request->hasFile('image')) {
            $fileName = time() . 'img.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('landing_pages'), $fileName);
            $data['image'] = $fileName;
        }

        if ($request->hasFile('landing_bg')) {
            $fileName = time() . 'bg.' . $request->file('landing_bg')->getClientOriginalExtension();
            $request->file('landing_bg')->move(public_path('landing_pages'), $fileName);
            $data['landing_bg'] = $fileName;
        }

        if ($request->hasFile('right_product_image')) {
            $fileName = time() . 'rp.' . $request->file('right_product_image')->getClientOriginalExtension();
            $request->file('right_product_image')->move(public_path('landing_pages'), $fileName);
            $data['right_product_image'] = $fileName;
        }

        $data['slug'] = $this->uniqueLandingSlug($data['slug'] ?? null, $data['title1']);

        $landPage = LandingPage::create($data);

        if ($request->has('pkg_qty')) {
            foreach ($request->pkg_qty as $index => $qty) {
                if (!empty($qty) && !empty($request->pkg_price[$index])) {
                    LandingPagePackage::create([
                        'landing_page_id' => $landPage->id,
                        'qty'             => $qty,
                        'price'           => $request->pkg_price[$index],
                        'discount_text'   => $request->pkg_discount_text[$index] ?? null,
                        'is_default'      => ($request->pkg_is_default == $index) ? 1 : 0,
                    ]);
                }
            }
        }

        if ($request->hasFile('sliderimage')) {
            foreach ($request->file('sliderimage') as $key => $image) {
                $fileName = time() . $key . 'sl.' . $image->getClientOriginalExtension();
                $image->move(public_path('landing_sliders'), $fileName);
                LandingPageSlider::create([
                    'landing_page_id' => $landPage->id,
                    'image'           => $fileName,
                ]);
            }
        }

        if ($request->hasFile('review_product_image')) {
            $review_image_data = [];
            foreach ($request->file('review_product_image') as $key => $image) {
                $fileName = time() . $key . 'rv.' . $image->getClientOriginalExtension();
                $image->move(public_path('review_landing_sliders'), $fileName);
                $review_image_data[] = ['review_image' => $fileName];
            }
            if (!empty($review_image_data)) $landPage->review_images()->createMany($review_image_data);
        }

        // page_type '1' ("design one") never got its own admin create/index/edit
        // views built and has no create form linking to it anywhere in the
        // panel — admin.landing_pages.index no longer exists as a route, so
        // this only-ever-used-as-a-fallback default now points somewhere that
        // always exists instead of throwing a RouteNotFoundException.
        $redirectRoute = 'admin.dashboard';
        if ($pageType == '2') $redirectRoute = 'admin.landing_pages_two';
        elseif ($pageType == '3') $redirectRoute = 'admin.landing_pages_three';
        elseif ($pageType == '4') $redirectRoute = 'admin.landing_pages_four';
        elseif ($pageType == '5') $redirectRoute = 'admin.landing_pages_five';
        elseif ($pageType == '6') $redirectRoute = 'admin.landing_pages_six';
        elseif ($pageType == '7') $redirectRoute = 'admin.landing_pages_seven';
        elseif ($pageType == '8') $redirectRoute = 'admin.landing_pages_eight';
        elseif ($pageType == '9') $redirectRoute = 'admin.landing_pages_nine';
        elseif ($pageType == '10') $redirectRoute = 'admin.landing_pages_ten';
        elseif ($pageType == '11') $redirectRoute = 'admin.landing_pages_eleven';
        elseif ($pageType == '12') $redirectRoute = 'admin.landing_pages_twelve';
        elseif ($pageType == '13') $redirectRoute = 'admin.landing_pages_thirteen';
        elseif ($pageType == '14') $redirectRoute = 'admin.landing_pages_fourteen';
        elseif ($pageType == '15') $redirectRoute = 'admin.landing_pages_fifteen';
        elseif ($pageType == '16') $redirectRoute = 'admin.landing_pages_sixteen';

        $redirectUrl = route($redirectRoute);

        if ($this->isAjax($request)) {
            return response()->json([
                'status' => true,
                'msg'    => 'Landing Page Created Successfully!',
                'url'    => $redirectUrl,
            ]);
        }

        return redirect()->route($redirectRoute)->with('success', 'Landing Page Created Successfully!');
    }

    public function edit($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit', compact('item', 'single_product', 'review_images'));
    }

    public function edit_landing_page_two($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', '2')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit_two', compact('item', 'single_product', 'review_images'));
    }

    public function edit_three($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', '3')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit_three', compact('item', 'single_product', 'review_images'));
    }

    public function edit_four($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', '4')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit_four', compact('item', 'single_product', 'review_images'));
    }

    public function edit_five($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', '5')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit_five', compact('item', 'single_product', 'review_images'));
    }

    public function edit_six($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', '6')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit_six', compact('item', 'single_product', 'review_images'));
    }

    public function edit_seven($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', '7')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit_seven', compact('item', 'single_product', 'review_images'));
    }

    public function edit_eight($id)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', '8')->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.edit_eight', compact('item', 'single_product', 'review_images'));
    }

    public function edit_nine($id)     { return $this->editGeneric($id, '9', 'edit_nine'); }
    public function edit_ten($id)      { return $this->editGeneric($id, '10', 'edit_ten'); }
    public function edit_eleven($id)   { return $this->editGeneric($id, '11', 'edit_eleven'); }
    public function edit_twelve($id)   { return $this->editGeneric($id, '12', 'edit_twelve'); }
    public function edit_thirteen($id) { return $this->editGeneric($id, '13', 'edit_thirteen'); }
    public function edit_fourteen($id) { return $this->editGeneric($id, '14', 'edit_fourteen'); }
    public function edit_fifteen($id)  { return $this->editGeneric($id, '15', 'edit_fifteen'); }
    public function edit_sixteen($id)  { return $this->editGeneric($id, '16', 'edit_sixteen'); }

    private function editGeneric($id, $pageType, $viewName)
    {
        $item = LandingPage::with('images', 'review_images', 'packages')->where('page_type', $pageType)->findOrFail($id);
        $review_images = reviewProductImage::where('landing_page_id', $id)->get();
        $single_product = $item->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks'])->find($item->product_id) : null;
        return view('backend.landing_pages.' . $viewName, compact('item', 'single_product', 'review_images'));
    }

    public function update(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '1');
    }

    public function update_landing_page_two(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '2');
    }

    public function update_three(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '3');
    }

    public function update_four(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '4');
    }

    public function update_five(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '5');
    }

    public function update_six(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '6');
    }

    public function update_seven(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '7');
    }

    public function update_eight(Request $request, $id)
    {
        return $this->processUpdate($request, $id, '8');
    }

    public function update_nine(Request $request, $id)     { return $this->processUpdate($request, $id, '9'); }
    public function update_ten(Request $request, $id)      { return $this->processUpdate($request, $id, '10'); }
    public function update_eleven(Request $request, $id)   { return $this->processUpdate($request, $id, '11'); }
    public function update_twelve(Request $request, $id)   { return $this->processUpdate($request, $id, '12'); }
    public function update_thirteen(Request $request, $id) { return $this->processUpdate($request, $id, '13'); }
    public function update_fourteen(Request $request, $id) { return $this->processUpdate($request, $id, '14'); }
    public function update_fifteen(Request $request, $id)  { return $this->processUpdate($request, $id, '15'); }
    public function update_sixteen(Request $request, $id)  { return $this->processUpdate($request, $id, '16'); }

    private function processUpdate(Request $request, $id, $pageType)
    {
        $updatePage = LandingPage::findOrFail($id);
        $this->normalizeProductId($request);

        $data = $request->validate([
            'title1' => 'required',
            'title2' => 'nullable',
            'slug' => 'nullable|string|max:191|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'video_url' => 'nullable',
            'landing_bg' => 'nullable',
            'feature' => 'nullable',
            'review_top_text' => 'nullable',
            'old_price' => 'nullable',
            'new_price' => 'nullable',
            'phone' => 'nullable',
            'pay_text' => 'nullable',
            'product_id' => 'nullable|integer',
            'variation_id' => 'nullable',
            'regular_price_text' => 'nullable',
            'offer_price_text' => 'nullable',
            'call_text' => 'nullable',
            'left_side_title' => 'nullable',
            'left_side_desc' => 'nullable',
            'right_side_title' => 'nullable',
            'right_side_desc' => 'nullable',
            'top_heading_text' => 'nullable',
            'left_product_details' => 'nullable',
            'theme_primary_col' => 'nullable',
            'theme_gradient_col' => 'nullable',
            'btn_bg_color' => 'nullable',
            'btn_text_color' => 'nullable',
            'btn_text_hero' => 'nullable',
            'btn_text_video' => 'nullable',
            'btn_text_feature' => 'nullable',
            'btn_text_form' => 'nullable',
            'hot_badge_text' => 'nullable',
            'warranty_text' => 'nullable',
            'order_btn_text' => 'nullable',
            'dhamaka_title' => 'nullable',
            'offer_price_label' => 'nullable',
            'currency_text' => 'nullable',
            'call_to_action_text' => 'nullable',
            'feature_title' => 'nullable',
            'feature_list' => 'nullable',
            'details_title' => 'nullable',
            'review_title' => 'nullable',
            'form_title' => 'nullable',
            'form_subtitle' => 'nullable',
            'name_label' => 'nullable',
            'name_placeholder' => 'nullable',
            'phone_label' => 'nullable',
            'address_label' => 'nullable',
            'address_placeholder' => 'nullable',
            'delivery_label' => 'nullable',
            'payment_title' => 'nullable',
            'cod_title' => 'nullable',
            'cod_subtitle' => 'nullable',
            'online_payment_title' => 'nullable',
            'online_payment_subtitle' => 'nullable',
            'order_summary_title' => 'nullable',
            'variation_label' => 'nullable',
            'total_bill_label' => 'nullable',
            'processing_text' => 'nullable',
            'error_msg' => 'nullable',
            'countdown_title' => 'nullable',
            'countdown_bg_color' => 'nullable',
            'countdown_text_color' => 'nullable',
            'hero_btn_bg_color' => 'nullable',
            'hero_btn_text_color' => 'nullable',
            'countdown_hours' => 'nullable|integer',
            'old_price_text' => 'nullable',
            'new_price_text' => 'nullable',
            'promise_badge' => 'nullable', 
            'promise_title' => 'nullable', 
            'promise_img_badge' => 'nullable',
            'identify_badge' => 'nullable', 
            'identify_title' => 'nullable', 
            'identify_subtitle' => 'nullable',
            'trust_sec_title' => 'nullable',
            'trust_1_icon' => 'nullable', 
            'trust_1_title' => 'nullable',
            'trust_2_icon' => 'nullable', 
            'trust_2_title' => 'nullable',
            'trust_3_icon' => 'nullable', 
            'trust_3_title' => 'nullable',
            'trust_4_icon' => 'nullable', 
            'trust_4_title' => 'nullable',
            'promise_1_title' => 'nullable', 
            'promise_1_desc' => 'nullable',
            'promise_2_title' => 'nullable', 
            'promise_2_desc' => 'nullable',
            'promise_3_title' => 'nullable', 
            'promise_3_desc' => 'nullable',
            'negative_title' => 'nullable', 
            'negative_tags' => 'nullable',

            // ✅ New 3.5 Section Details (Organic Features)
            'special_feature_title' => 'nullable',
            'sf_1_title' => 'nullable', 'sf_1_desc' => 'nullable',
            'sf_2_title' => 'nullable', 'sf_2_desc' => 'nullable',
            'sf_3_title' => 'nullable', 'sf_3_desc' => 'nullable',
            'sf_4_title' => 'nullable', 'sf_4_desc' => 'nullable',
            'sf_5_title' => 'nullable', 'sf_5_desc' => 'nullable',
            'sf_6_title' => 'nullable', 'sf_6_desc' => 'nullable',
            'sf_7_title' => 'nullable', 'sf_7_desc' => 'nullable',
            'sf_8_title' => 'nullable', 'sf_8_desc' => 'nullable',

            'id_1_icon' => 'nullable', 'id_1_title' => 'nullable', 'id_1_desc' => 'nullable',
            'id_2_icon' => 'nullable', 'id_2_title' => 'nullable', 'id_2_desc' => 'nullable',
            'id_3_icon' => 'nullable', 'id_3_title' => 'nullable', 'id_3_desc' => 'nullable',
            'id_4_icon' => 'nullable', 'id_4_title' => 'nullable', 'id_4_desc' => 'nullable',
            'id_5_icon' => 'nullable', 'id_5_title' => 'nullable', 'id_5_desc' => 'nullable',
            'id_6_icon' => 'nullable', 'id_6_title' => 'nullable', 'id_6_desc' => 'nullable',
            'id_7_icon' => 'nullable', 'id_7_title' => 'nullable', 'id_7_desc' => 'nullable',
            'id_8_icon' => 'nullable', 'id_8_title' => 'nullable', 'id_8_desc' => 'nullable',
            'review_badge' => 'nullable', 'review_subtitle' => 'nullable',
            'stat_1_num' => 'nullable', 'stat_1_text' => 'nullable',
            'stat_2_num' => 'nullable', 'stat_2_text' => 'nullable',
            'stat_3_num' => 'nullable', 'stat_3_text' => 'nullable',
            'rev_1_text' => 'nullable', 'rev_1_name' => 'nullable', 'rev_1_loc' => 'nullable',
            'rev_2_text' => 'nullable', 'rev_2_name' => 'nullable', 'rev_2_loc' => 'nullable',
            'rev_3_text' => 'nullable', 'rev_3_name' => 'nullable', 'rev_3_loc' => 'nullable',
            'rev_4_text' => 'nullable', 'rev_4_name' => 'nullable', 'rev_4_loc' => 'nullable',
            'faq_badge' => 'nullable', 'faq_title' => 'nullable',
            'faq_1_q' => 'nullable', 'faq_1_a' => 'nullable',
            'faq_2_q' => 'nullable', 'faq_2_a' => 'nullable',
            'faq_3_q' => 'nullable', 'faq_3_a' => 'nullable',
            'faq_4_q' => 'nullable', 'faq_4_a' => 'nullable',

            // Landing Page 9 fields
            'hero_rating' => 'nullable', 'hero_rating_count' => 'nullable', 'hero_rating_label' => 'nullable',
            'discount_save_text' => 'nullable',
            'spec_title' => 'nullable',
            'spec_1_label' => 'nullable', 'spec_1_value' => 'nullable',
            'spec_2_label' => 'nullable', 'spec_2_value' => 'nullable',
            'spec_3_label' => 'nullable', 'spec_3_value' => 'nullable',
            'spec_4_label' => 'nullable', 'spec_4_value' => 'nullable',
            'spec_5_label' => 'nullable', 'spec_5_value' => 'nullable',
            'spec_6_label' => 'nullable', 'spec_6_value' => 'nullable',
            'spec_7_label' => 'nullable', 'spec_7_value' => 'nullable',
            'stock_count' => 'nullable|integer',
            'stock_text' => 'nullable', 'urgency_title' => 'nullable', 'urgency_subtitle' => 'nullable',
            'final_cta_title' => 'nullable', 'final_cta_subtitle' => 'nullable', 'final_cta_btn_text' => 'nullable',
            'footer_company' => 'nullable', 'footer_email' => 'nullable', 'footer_copyright' => 'nullable',
            'security_badge_text' => 'nullable',

            // ✅ Landing Pages 14 / 15 / 16 — new dynamic text fields
            'brand_sub' => 'nullable', 'spec_badge' => 'nullable',
            'identify_desc' => 'nullable', 'form_desc' => 'nullable',
            'footer_contact_label' => 'nullable', 'footer_address_label' => 'nullable',
            'spec_8_label' => 'nullable', 'spec_8_value' => 'nullable',
            'faq_5_q' => 'nullable', 'faq_5_a' => 'nullable',
            'team_badge' => 'nullable', 'team_pick_btn' => 'nullable',
            'promise_1_badge' => 'nullable', 'promise_2_badge' => 'nullable',
            'gallery_badge' => 'nullable', 'form_badge' => 'nullable',
            'btn_text_top' => 'nullable', 'hero_small_stats' => 'nullable',
            'footer_desc' => 'nullable',
            'whatsapp' => 'nullable',
        ]);

        if ($request->new_product_id != null) {
            $data['product_id'] = (int) $request->new_product_id;
        }

        if ($request->filled('variation_id')) {
            if (!empty($data['product_id'])) {
                $ok = Variation::where('id', $request->variation_id)->where('product_id', $data['product_id'])->exists();
                $data['variation_id'] = $ok ? (int) $request->variation_id : null;
            } else {
                $data['variation_id'] = (int) $request->variation_id;
            }
        }

        if ($request->hasFile('image')) {
            $fileName = time() . '1up.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('landing_pages'), $fileName);
            $data['image'] = $fileName;
        }
        if ($request->hasFile('landing_bg')) {
            $fileName = time() . '2up.' . $request->file('landing_bg')->getClientOriginalExtension();
            $request->file('landing_bg')->move(public_path('landing_pages'), $fileName);
            $data['landing_bg'] = $fileName;
        }
        if ($request->hasFile('right_product_image')) {
            $fileName = time() . '3up.' . $request->file('right_product_image')->getClientOriginalExtension();
            $request->file('right_product_image')->move(public_path('landing_pages'), $fileName);
            $data['right_product_image'] = $fileName;
        }

        // ফাঁকা রাখলে পুরনো slug-ই থাকে — প্রতি সেভে টাইটেল থেকে নতুন slug বানালে
        // (যেমন প্রোডাক্টে আগে হতো) পুরনো URL ভেঙে যেত। অ্যাডমিন ইচ্ছাকৃতভাবে
        // নতুন slug দিলে তখনই শুধু বদলায়, আর সেটাও ইউনিক করে নেওয়া হয়।
        $requestedSlug = trim((string) ($data['slug'] ?? ''));
        if ($requestedSlug === '') {
            unset($data['slug']);
            if (empty($updatePage->slug)) {
                $data['slug'] = $this->uniqueLandingSlug(null, $data['title1'] ?? $updatePage->title1, $updatePage->id);
            }
        } elseif ($requestedSlug !== $updatePage->slug) {
            $data['slug'] = $this->uniqueLandingSlug($requestedSlug, $data['title1'] ?? $updatePage->title1, $updatePage->id);
        } else {
            unset($data['slug']);
        }

        $updatePage->update($data);

        // ✅ FIXED: Always delete old packages first, then recreate if valid input exists
        LandingPagePackage::where('landing_page_id', $updatePage->id)->delete();
        
        if ($request->has('pkg_qty') && is_array($request->pkg_qty)) {
            foreach ($request->pkg_qty as $index => $qty) {
                if (!empty($qty) && !empty($request->pkg_price[$index] ?? null)) {
                    LandingPagePackage::create([
                        'landing_page_id' => $updatePage->id,
                        'qty'             => $qty,
                        'price'           => $request->pkg_price[$index],
                        'discount_text'   => $request->pkg_discount_text[$index] ?? null,
                        'is_default'      => ($request->pkg_is_default == $index) ? 1 : 0,
                    ]);
                }
            }
        }

        if ($request->hasFile('sliderimage')) {
            foreach ($request->file('sliderimage') as $key => $image) {
                $fileName = time() . $key . 'slup.' . $image->getClientOriginalExtension();
                $image->move(public_path('landing_sliders'), $fileName);
                LandingPageSlider::create([
                    'landing_page_id' => $updatePage->id,
                    'image'           => $fileName,
                ]);
            }
        }

        if ($request->hasFile('review_product_image')) {
            $review_image_data = [];
            foreach ($request->file('review_product_image') as $key => $image) {
                $fileName = time() . $key . 'rvup.' . $image->getClientOriginalExtension();
                $image->move(public_path('review_landing_sliders'), $fileName);
                $review_image_data[] = ['review_image' => $fileName];
            }
            if (!empty($review_image_data)) $updatePage->review_images()->createMany($review_image_data);
        }

        // page_type '1' ("design one") never got its own admin create/index/edit
        // views built and has no create form linking to it anywhere in the
        // panel — admin.landing_pages.index no longer exists as a route, so
        // this only-ever-used-as-a-fallback default now points somewhere that
        // always exists instead of throwing a RouteNotFoundException.
        $redirectRoute = 'admin.dashboard';
        if ($pageType == '2') $redirectRoute = 'admin.landing_pages_two';
        elseif ($pageType == '3') $redirectRoute = 'admin.landing_pages_three';
        elseif ($pageType == '4') $redirectRoute = 'admin.landing_pages_four';
        elseif ($pageType == '5') $redirectRoute = 'admin.landing_pages_five';
        elseif ($pageType == '6') $redirectRoute = 'admin.landing_pages_six';
        elseif ($pageType == '7') $redirectRoute = 'admin.landing_pages_seven';
        elseif ($pageType == '8') $redirectRoute = 'admin.landing_pages_eight';
        elseif ($pageType == '9') $redirectRoute = 'admin.landing_pages_nine';
        elseif ($pageType == '10') $redirectRoute = 'admin.landing_pages_ten';
        elseif ($pageType == '11') $redirectRoute = 'admin.landing_pages_eleven';
        elseif ($pageType == '12') $redirectRoute = 'admin.landing_pages_twelve';
        elseif ($pageType == '13') $redirectRoute = 'admin.landing_pages_thirteen';
        elseif ($pageType == '14') $redirectRoute = 'admin.landing_pages_fourteen';
        elseif ($pageType == '15') $redirectRoute = 'admin.landing_pages_fifteen';
        elseif ($pageType == '16') $redirectRoute = 'admin.landing_pages_sixteen';

        $redirectUrl = route($redirectRoute);

        if ($this->isAjax($request)) {
            return response()->json([
                'status' => true,
                'msg'    => 'Landing Page Updated Successfully!',
                'url'    => $redirectUrl,
            ]);
        }

        return redirect()->route($redirectRoute)->with('success', 'Landing Page Updated Successfully!');
    }

    public function delete_slider($id)
    {
        $item = LandingPageSlider::find($id); 
        if ($item) {
            deleteImage('landing_sliders', $item->image);
            $item->delete();
        }
        return back();
    }

    public function delete_review(Request $request, $id)
    {
        $delete_item = reviewProductImage::find($id);
        if ($delete_item) {
            deleteImage('review_landing_sliders', $delete_item->review_image);
            $delete_item->delete();
        }
        return back();
    }

    public function destroy(Request $request, $id)
    {
        $single_page = LandingPage::with(['images', 'review_images', 'packages'])->find($id);

        if ($single_page) {
            LandingPagePackage::where('landing_page_id', $id)->delete();
            deleteImage('landing_pages', $single_page->image);
            deleteImage('landing_pages', $single_page->landing_bg);
            deleteImage('landing_pages', $single_page->right_product_image);

            foreach ($single_page->images as $slider_image) {
                deleteImage('landing_sliders', $slider_image->image);
            }

            foreach ($single_page->review_images as $rv) {
                deleteImage('review_landing_sliders', $rv->review_image);
            }

            $single_page->delete();
        }

        if ($this->isAjax($request)) {
            return response()->json(['status' => true, 'msg' => 'Deleted Successfully!']);
        }
        return redirect()->back()->with('success', 'Deleted Successfully!');
    }

    public function colorSettings($id = null)
    {
        $all_pages = LandingPage::select('id', 'title1', 'page_type')
            ->whereIn('page_type', ['2', '3', '4', '5', '6', '7', '8'])
            ->orderBy('id', 'desc')
            ->get();

        if ($id) {
            $ln_pg = LandingPage::find($id);
            if (!$ln_pg && $all_pages->isNotEmpty()) {
                $ln_pg = $all_pages->first();
            }
        } elseif ($all_pages->isNotEmpty()) {
            $ln_pg = $all_pages->first();
        } else {
            $ln_pg = null;
        }

        return view('backend.landing_pages.color', compact('ln_pg', 'all_pages'));
    }

    public function updateColor(Request $request, $id)
    {
        $ln_pg = LandingPage::findOrFail($id);

        $data = $request->validate([
            'theme_primary_col' => 'nullable',
            'theme_gradient_col' => 'nullable',
            'btn_bg_color' => 'nullable',
            'btn_text_color' => 'nullable',
            'btn_text_hero' => 'nullable',
            'btn_text_video' => 'nullable',
            'btn_text_feature' => 'nullable',
            'btn_text_form' => 'nullable',
        ]);

        $ln_pg->update($data);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    public function landing_page($id)
    {
        $ln_pg = LandingPage::with(['images', 'review_images', 'packages'])->find($id);
        if(!$ln_pg) abort(404);

        $title = $ln_pg->title1 ?? '';
        $charges = DeliveryCharge::whereNotNull('status')->get();
        $product = $ln_pg->product_id ? Product::with(['variations.size', 'variations.color', 'variations.stocks', 'category'])->find($ln_pg->product_id) : null;

        $lpEventBase = null;
        $lpEventIdVC = null;
        $lpEventIdIC = null;
        if ($product) {
            try {
                $finalPrice = ($product->after_discount && $product->after_discount > 0)
                    ? $product->after_discount
                    : $product->sell_price;

                $base        = "LP_{$product->id}_" . now()->format('YmdHis') . '_' . uniqid();
                $eventIdVC = $base . "_VC";
                $eventIdIC = $base . "_IC";
                $lpEventBase = $base;
                $lpEventIdVC = $eventIdVC;
                $lpEventIdIC = $eventIdIC;

                $userData = [];
                $authUser = auth()->user();

                // ✅ FIXED: Removed extra parenthesis here in the hash function
                if (!empty($authUser?->email)) $userData['em'] = [hash('sha256', strtolower(trim($authUser->email)))];
                if (!empty($authUser?->phone_number)) $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $authUser->phone_number))];
                if (!empty($authUser?->id)) $userData['external_id'] = [hash('sha256', (string)$authUser->id)];

                FacebookConversion::sendViewContent([
                    'product_id'        => $product->id,
                    'product_name'      => $product->name,
                    'value'             => (float)$finalPrice,
                    'currency'          => 'BDT',
                    'content_category' => $product->category->name ?? 'Landing Page',
                    'event_time'        => now()->timestamp,
                    'action_source'     => 'website',
                ], $eventIdVC, $userData);

                FacebookConversion::sendInitiateCheckout([
                    'product_id'    => $product->id,
                    'value'         => (float)$finalPrice,
                    'currency'      => 'BDT',
                    'num_items'     => 1,
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                ], $eventIdIC, $userData);

            } catch (\Throwable $e) {
                Log::error('Facebook CAPI (LP View) Error: ' . $e->getMessage());
            }
        }

        $matrix = [];
        $sizes  = collect();
        $colors = collect();

        if ($product) {
            foreach ($product->variations as $v) {
                $stock = $v->stocks->sum('quantity');
                $key = ($v->size_id ?? 0) . '_' . ($v->color_id ?? 0);
                $price = $v->after_discount_price ?: $v->price;
                
                $matrix[$key] = [
                    'variation_id' => $v->id,
                    'price' => (float)$price,
                    'stock' => (int)$stock
                ];

                if ($v->size) $sizes->push(['id' => $v->size->id, 'name' => $v->size->name]);
                if ($v->color) $colors->push(['id' => $v->color->id, 'name' => $v->color->name, 'code' => $v->color->code]);
            }
        }
        $sizes = $sizes->unique('id')->values();
        $colors = $colors->unique('id')->values();

        return view('backend.landing_pages.land_page', compact('ln_pg', 'charges', 'title', 'product', 'matrix', 'sizes', 'colors', 'lpEventBase', 'lpEventIdVC', 'lpEventIdIC'));
    }

    public function storeData(Request $request)
    {
        $data = $request->validate([
            'mobile' => 'required|digits:11',
            'first_name' => 'required',
            'shipping_address' => 'required',
            'delivery_charge_id' => 'required|numeric',
            'prd_id' => 'required|integer',
            'purchase_event_id' => 'nullable|string',
            'variation_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
            'selected_package_id' => 'nullable',
            'selected_items' => 'nullable|json'
        ]);

        if (empty(auth()->user()?->id)) {
            // আগে প্রতিবার নতুন User তৈরি হতো — একই নম্বরে বারবার অর্ডার করলে
            // ডুপ্লিকেট অ্যাকাউন্ট জমত আর username ফাঁকা থাকত। এখন নম্বর দিয়ে
            // আগে খোঁজা হয়; role-ওয়ালা অ্যাকাউন্ট (admin/worker) কখনো বদলানো হয় না।
            $user = User::where('mobile', $request->mobile)->first();

            if (!$user) {
                $baseUsername = strtolower(str_replace(' ', '', $request->first_name));
                $username     = $baseUsername;
                $counter      = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $user = User::create([
                    'first_name'       => $request->first_name,
                    'mobile'           => $request->mobile,
                    'shipping_address' => $request->shipping_address,
                    'note'             => $request->input('note'),
                    'username'         => $username,
                    'status'           => 1,
                ]);
            } elseif (!$user->roles()->exists()) {
                $user->update(['first_name' => $request->first_name]);
            }

            $data['user_id'] = $user->id;
        } else {
            $data['user_id'] = auth()->user()->id;
        }

        $product = Product::findOrFail($request->prd_id);
        $charge = DeliveryCharge::find($request->delivery_charge_id);
        $chargeAmount = $charge ? (float)$charge->amount : 0;

        // Honor product-level free shipping — the landing page shows the
        // customer "free delivery", so the server must not add the charge.
        if (!empty($product->is_free_shipping) && $product->is_free_shipping == 1) {
            $chargeAmount = 0;
        }

        // Coupon the customer applied on the landing page is kept in the
        // session by getCouponDiscount(). A percentage coupon must be computed
        // against the order subtotal (exactly like the landing page's own JS);
        // a fixed coupon subtracts a flat amount.
        $couponRaw  = (float) (session('coupon_discount') ?? 0);
        $couponType = session('discount_type');
        $couponFor  = function ($subtotal) use ($couponRaw, $couponType) {
            if ($couponRaw <= 0) return 0.0;
            if ($couponType === 'percentage' || $couponType === 'percent') {
                return round(($subtotal * $couponRaw) / 100, 2);
            }
            return $couponRaw;
        };

        $invoiceNo = generateUniqueInvoiceNo();
        $admins = DB::table('model_has_roles')->where('role_id', 8)->pluck('model_id')->toArray();
        $assignUserId = (!empty($admins)) ? $admins[array_rand($admins)] : 1;

        DB::beginTransaction();
        try {
            if($request->filled('selected_items')) {
                
                $selectedItems = json_decode($request->selected_items, true);
                
                if(empty($selectedItems)) {
                    throw new \Exception("Please select at least one item.");
                }

                $totalSubtotal = 0;
                $order = Order::create([
                    'mobile' => $request->mobile,
                    'first_name' => $request->first_name,
                    'shipping_address' => $request->shipping_address,
                    'note' => $request->input('note'),
                    'delivery_charge_id' => $request->delivery_charge_id,
                    'user_id' => $data['user_id'],
                    'assign_user_id' => $assignUserId,
                    'date' => date('Y-m-d'),
                    'invoice_no' => $invoiceNo,
                    'amount' => 0,
                    'shipping_charge' => $chargeAmount,
                    'final_amount' => 0,
                ]);

                foreach ($selectedItems as $item) {
                    $qty = max((int)$item['quantity'], 1);
                    
                    $varId = null;
                    if($item['id'] !== 'default') {
                        $varId = (int)$item['id'];
                    }

                    $unitPrice = (float)$item['price'];
                    $totalSubtotal += ($unitPrice * $qty);

                    $order->details()->create([
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'variation_id' => $varId,
                    ]);
                }

                $couponDiscount = $couponFor($totalSubtotal);
                $finalAmount = max(0, $totalSubtotal + $chargeAmount - $couponDiscount);
                $order->update([
                    'amount' => $totalSubtotal,
                    'discount' => $couponDiscount,
                    'final_amount' => $finalAmount
                ]);

            } else {
                $v_id = $request->variation_id ?: Variation::where('product_id', $product->id)->value('id');
                $selectedVariation = $v_id ? Variation::with('stocks')->find($v_id) : null;
                
                $qty = max((int)$request->quantity, 1);
                $unitPrice = $selectedVariation ? ($selectedVariation->after_discount_price ?: $selectedVariation->price) : ($product->after_discount ?: $product->sell_price);

                if ($request->has('selected_package_id') && $request->selected_package_id != null) {
                    $package = LandingPagePackage::find($request->selected_package_id);
                    if ($package) {
                        $qty = $package->qty;
                        $unitPrice = $package->price / $qty; 
                    }
                }

                $subTotal = $unitPrice * $qty;
                $couponDiscount = $couponFor($subTotal);
                $finalAmount = max(0, $subTotal + $chargeAmount - $couponDiscount);

                $order = Order::create([
                    'mobile' => $request->mobile,
                    'first_name' => $request->first_name,
                    'shipping_address' => $request->shipping_address,
                    'note' => $request->input('note'),
                    'delivery_charge_id' => $request->delivery_charge_id,
                    'user_id' => $data['user_id'],
                    'assign_user_id' => $assignUserId,
                    'date' => date('Y-m-d'),
                    'invoice_no' => $invoiceNo,
                    'amount' => $subTotal,
                    'discount' => $couponDiscount,
                    'shipping_charge' => $chargeAmount,
                    'final_amount' => $finalAmount,
                ]);

                $order->details()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'variation_id' => $selectedVariation?->id,
                ]);
            }

            DB::commit();

            // Coupon is single-use per order — clear it so it doesn't silently
            // apply to the customer's next landing-page order in this session.
            session()->forget(['coupon_discount', 'discount_type', 'applied_coupon_code']);

            rememberPlacedOrder($order->id);

            return response()->json([
                'success' => true,
                'msg' => 'Order Created Successfully!',
                'url' => confirmOrderUrl($order),
                'invoice_no' => $invoiceNo
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Landing Page Order Error: " . $e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Order process failed. Try again.']);
        }
    }
}