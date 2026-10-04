<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Information;
use App\Models\AdminText;
use App\Models\Page;
use App\Models\SocialIcon;
use App\Models\Career;
use App\Models\AboutUs;

class T12FeatureTest extends TestCase
{
    private $ok = 0;
    private $fail = 0;
    private $createdPageIds = [];
    private $createdSocialIds = [];
    private $createdCareerIds = [];
    private $createdFiles = [];

    private function ok($m){ $this->ok++; fwrite(STDOUT, "OK   $m\n"); }
    private function bad($m){ $this->fail++; fwrite(STDOUT, "FAIL $m\n"); }
    private function chk($cond, $m){ $cond ? $this->ok($m) : $this->bad($m); }
    private function note($m){ fwrite(STDOUT, "  ~  $m\n"); }

    private function info(){ return DB::table('informations')->where('id',1)->first(); }

    public function test_settings_features()
    {
        $admin = User::find(1);
        $this->assertNotNull($admin, 'admin user 1 missing');

        // ---------- SNAPSHOTS (restored in the finally block) ----------
        $origInfo   = (array) DB::table('informations')->where('id', 1)->first();
        $origText   = (array) DB::table('admin_texts')->where('id', 1)->first();
        $origAbout  = (array) (DB::table('about_us')->first() ?? new \stdClass);

        try {
            $this->section('A0. Isolated topbar probe (direct DB writes, no controller)');
            foreach ([0,1,0,1] as $n => $flag) {
                DB::table('informations')->where('id',1)
                  ->update(['topbar_active'=>$flag,'topbar_notice'=>'T12 MICRO NOTICE']);
                $resp = $this->get('/');
                $h = $resp->getContent();
                $this->note("probe#$n topbar_active=$flag -> http ".$resp->status()." len ".strlen($h)
                    ." marker=".(str_contains($h,'T12 MICRO NOTICE')?'YES':'no')
                    ." sitename=".(str_contains($h,'Biz Care')?'yes':'NO'));
            }
            DB::table('informations')->where('id',1)->update(['topbar_active'=>0]);

            $this->section('A. Information settings pages render');
            $this->actingAs($admin);

            $r = $this->get('/admin/settings');
            $this->chk($r->status()===200 && str_contains($r->getContent(), e($origInfo['site_name'])),
                'GET /admin/settings renders and shows current site_name');

            $r = $this->get('/admin/payment-settings');
            $this->chk($r->status()===200 && str_contains($r->getContent(), 'cod_active'),
                'GET /admin/payment-settings renders COD switch');

            $r = $this->get('/admin/styles');
            $this->chk($r->status()===200 && str_contains($r->getContent(), 'primary_color'),
                'GET /admin/styles renders colour form');

            $r = $this->get('/admin/invoice-design');
            $this->chk($r->status()===200, 'GET /admin/invoice-design renders');

            $r = $this->get('/admin/tracking-guide');
            $this->chk($r->status()===200, 'GET /admin/tracking-guide renders');

            $r = $this->get('/admin/profile');
            $this->chk($r->status()===200 && str_contains($r->getContent(), e($admin->first_name ?? '')),
                'GET /admin/profile renders the logged-in user');

            // =====================================================================
            $this->section('B. General settings save -> DB -> frontend');

            $general = $this->generalPayload($origInfo, [
                'site_name'     => 'T12-Site-Name',
                'topbar_active' => '1',
                'topbar_notice' => 'T12 TOPBAR MARKER',
                'currency'      => 'Dollar',
            ]);
            $r = $this->from('http://puregadget.local/admin/settings')
                      ->put('/admin/settings/1', $general);
            $i = $this->info();
            $this->chk($i->site_name==='T12-Site-Name' && $i->topbar_notice==='T12 TOPBAR MARKER'
                       && (int)$i->topbar_active===1 && $i->currency==='Dollar',
                'PUT /admin/settings/1 persists site_name / topbar / currency (status '.$r->status().')');

            $home = $this->get('/');
            $this->chk($home->status()===200 && str_contains($home->getContent(),'T12 TOPBAR MARKER'),
                'Frontend header shows topbar notice when topbar_active=1');

            $this->chk(str_contains($home->getContent(),'<span class="current-price">')
                       && preg_match('/current-price">\s*\$/', $home->getContent())===1,
                'currency=Dollar makes product cards price in $');

            // toggle topbar off -> notice disappears
            $general['topbar_active'] = '0';
            $general['currency']      = $origInfo['currency'];
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $general);
            $home2 = $this->get('/');
            $hc = $home2->getContent();
            $pos = strpos($hc, 'T12 TOPBAR MARKER');
            $this->note('after topbar off: db topbar_active='.$this->info()->topbar_active
                        .' marker-on-page='.($pos===false?'no':'yes at '.$pos));
            if ($pos !== false) {
                $this->note('context: ...'.preg_replace('/\s+/',' ', substr($hc, max(0,$pos-220), 420)).'...');
            }
            $this->chk((int)$this->info()->topbar_active===0 && !str_contains($home2->getContent(),'T12 TOPBAR MARKER'),
                'topbar_active=0 removes the notice from the storefront');

            // site_name in the <title>/logo alt of the storefront
            $this->chk(str_contains($home2->getContent(),'T12-Site-Name'),
                'Frontend uses the configured site_name');

            // ---- order limits ----
            $this->section('C. Order-limit settings enforced at checkout');
            $prod = DB::table('products')->where('status',1)->orderBy('id')->first();
            $this->assertNotNull($prod, 'need at least one live product');

            $general['max_order_qty']    = '1';
            $general['max_order_amount'] = '999999';
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $general);
            $this->chk((int)$this->info()->max_order_qty===1, 'max_order_qty saved as 1');

            $cart = ['t12key' => [
                'name'=>$prod->name,'size'=>'','color'=>'','quantity'=>3,'price'=>100,'discount'=>0,
                'original_price'=>100,'variation_id'=>null,'product_id'=>$prod->id,'category_name'=>'',
                'purchase_price'=>0,'image'=>null,'is_stock'=>1,'is_free_shipping'=>0,
            ]];
            $r = $this->withSession(['cart'=>$cart])
                      ->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/checkouts', [
                          'mobile'=>'01799000012','first_name'=>'T12 Buyer','payment_method'=>'Cash on Delivery',
                          'shipping_address'=>'T12 addr',
                      ]);
            $j = json_decode($r->getContent(), true);
            $this->chk(is_array($j) && ($j['success']??null)===false && str_contains($j['msg']??'', 'maximum of 1 items'),
                'Checkout blocks a 3-item cart when max_order_qty=1 -> "'.substr($j['msg']??'(no msg)',0,60).'"');

            $general['max_order_qty']    = '10';
            $general['max_order_amount'] = '50';
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $general);
            $r = $this->withSession(['cart'=>$cart])
                      ->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/checkouts', [
                          'mobile'=>'01799000012','first_name'=>'T12 Buyer','payment_method'=>'Cash on Delivery',
                          'shipping_address'=>'T12 addr',
                      ]);
            $j = json_decode($r->getContent(), true);
            $this->chk(is_array($j) && ($j['success']??null)===false && str_contains($j['msg']??'', 'cannot exceed'),
                'Checkout blocks an over-value cart when max_order_amount=50 -> "'.substr($j['msg']??'(no msg)',0,60).'"');

            $this->chk(DB::table('orders')->where('mobile','01799000012')->count()===0,
                'No order row was created by the two rejected checkouts');

            // restore limits to the originals before continuing
            $general['max_order_qty']    = $origInfo['max_order_qty'];
            $general['max_order_amount'] = $origInfo['max_order_amount'];
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $general);

            // ---- OTP gate ----
            $this->section('D. OTP setting gates order placement');
            $general['otp_system'] = '1';
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $general);
            $this->chk((int)$this->info()->otp_system===1, 'otp_system=1 saved');
            $r = $this->withSession(['cart'=>$cart])
                      ->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/checkouts', [
                          'mobile'=>'01799000013','first_name'=>'T12 Buyer','payment_method'=>'Cash on Delivery',
                          'shipping_address'=>'T12 addr',
                      ]);
            $j = json_decode($r->getContent(), true);
            $this->chk(is_array($j) && ($j['success']??null)===false && str_contains($j['msg']??'', 'verification'),
                'otp_system=1 refuses an unverified order -> "'.substr($j['msg']??'(no msg)',0,60).'"');
            $general['otp_system'] = '0';
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $general);
            $this->chk((int)$this->info()->otp_system===0, 'otp_system restored to 0');

            // =====================================================================
            $this->section('E. Payment-method toggles drive the checkout page');
            $pay = $this->paymentPayload($origInfo, ['cod_active'=>'0']);
            $r = $this->from('http://puregadget.local/admin/payment-settings')
                      ->put('/admin/settings/1', $pay);
            $this->chk((int)$this->info()->cod_active===0, 'cod_active=0 saved from the payment-settings form');

            $co = $this->withSession(['cart'=>$cart])->get('/checkouts');
            $body = $co->getContent();
            $this->chk($co->status()===200, 'GET /checkouts renders with a cart (status '.$co->status().')');
            $this->chk(!str_contains($body,'id="payment_cod"'),
                'COD off -> checkout no longer offers the Cash on Delivery radio');
            $this->chk(str_contains($body,'কোনো পেমেন্ট মেথড চালু নেই'),
                'COD off with nothing else on -> "no payment method enabled" warning shown');

            $pay = $this->paymentPayload($origInfo, ['cod_active'=>'1']);
            $this->from('http://puregadget.local/admin/payment-settings')->put('/admin/settings/1', $pay);
            $co = $this->withSession(['cart'=>$cart])->get('/checkouts');
            $this->chk((int)$this->info()->cod_active===1 && str_contains($co->getContent(),'id="payment_cod"'),
                'COD back on -> Cash on Delivery offered again');

            $pay = $this->paymentPayload($origInfo, ['cod_active'=>'1','bkash_active'=>'1','bkash_number'=>'01700000000']);
            $this->from('http://puregadget.local/admin/payment-settings')->put('/admin/settings/1', $pay);
            $co = $this->withSession(['cart'=>$cart])->get('/checkouts');
            $this->chk((int)$this->info()->bkash_active===1 && str_contains($co->getContent(),'payment_bkash'),
                'bkash_active=1 -> bKash option appears on checkout');

            // =====================================================================
            $this->section('F. Referer-based branching in InformationController@update');
            // The controller decides which set of checkboxes to zero out purely from
            // url()->previous().  First switch a few harmless general toggles ON so a
            // wipe is visible, then submit the payment form with no Referer.
            $genOn = $this->generalPayload($origInfo, [
                'topbar_active'=>'1','whats_active'=>'1','checkout_reco_active'=>'1','notification_active'=>'1',
                'topbar_notice'=>'T12 TOPBAR MARKER','whats_num'=>'01799000099',
            ]);
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $genOn);
            $before = $this->info();
            $this->note('before: topbar='.$before->topbar_active.' whats='.$before->whats_active
                        .' reco='.$before->checkout_reco_active.' notif='.$before->notification_active);

            $payNoRef = $this->paymentPayload($origInfo, ['cod_active'=>'1']);
            $this->flushSession();
            $this->put('/admin/settings/1', $payNoRef);   // no ->from(), no referer
            $after = $this->info();
            $this->note('after : topbar='.$after->topbar_active.' whats='.$after->whats_active
                        .' reco='.$after->checkout_reco_active.' notif='.$after->notification_active);
            $zeroed = [];
            foreach (['otp_system','is_ip_check','is_mobile_check','whats_active','notification_active',
                      'sms_pending_active','sms_confirmed_active','sms_delivered_active','pathao_status',
                      'redx_status','manydial_status','checkout_reco_active'] as $c) {
                if ((int)($before->$c ?? 0) !== (int)($after->$c ?? 0)) $zeroed[] = $c;
            }
            $this->note('payment form + no Referer changed: '.($zeroed ? implode(', ',$zeroed) : 'nothing'));
            $this->chk(count($zeroed)===0,
                'Saving the payment form without a Referer does NOT clobber unrelated general settings');

            // and the reverse: general form posted while previous URL is payment-settings
            $this->from('http://puregadget.local/admin/settings')
                 ->put('/admin/settings/1', $this->generalPayload($origInfo, []));
            $this->from('http://puregadget.local/admin/payment-settings')
                 ->put('/admin/settings/1', $this->paymentPayload($origInfo, ['cod_active'=>'1']));
            $before = $this->info();
            $gen2 = $this->generalPayload($origInfo, []);
            $this->from('http://puregadget.local/admin/payment-settings')->put('/admin/settings/1', $gen2);
            $after = $this->info();
            $killed = [];
            foreach (['cod_active','bkash_active','ssl_active','eps_active','nagad_active','uddoktapay_active','manual_payments'] as $c) {
                if ((int)($before->$c ?? 0) !== (int)($after->$c ?? 0)) $killed[] = $c;
            }
            $this->note('general form with payment-settings referer changed: '.($killed ? implode(', ',$killed) : 'nothing'));
            $this->chk(count($killed)===0,
                'Saving the general form from a payment-settings referer does NOT switch payment methods off');

            // =====================================================================
            $this->section('G0. Other storefront-facing settings');
            $g = $this->generalPayload($origInfo, [
                'checkout_reco_active'=>'1','checkout_reco_title'=>'T12 RECO TITLE','checkout_reco_limit'=>'3',
                'whats_active'=>'1','whats_num'=>'01799000099',
                'supp_num1'=>'T12HOTLINE2',
                'address'=>'T12 SHOP ADDRESS','copyright'=>'T12 COPYRIGHT LINE','owner_phone'=>'01799000098',
            ]);
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $g);
            $i = $this->info();
            $this->chk($i->checkout_reco_title==='T12 RECO TITLE' && (int)$i->checkout_reco_active===1,
                'checkout recommendation settings saved');
            $co = $this->withSession(['cart'=>$cart])->get('/checkouts');
            $this->chk(str_contains($co->getContent(),'T12 RECO TITLE'),
                'Checkout page renders the configured recommendation title');
            $home = $this->get('/');
            $hb = $home->getContent();
            $this->chk(str_contains($hb,'T12 SHOP ADDRESS'),  'Footer renders the configured address');
            $this->chk(str_contains($hb,'T12 COPYRIGHT LINE'),'Footer renders the configured copyright');
            $this->chk(str_contains($hb,'01799000098'),       'Footer renders the configured owner phone');
            $this->chk(str_contains($hb,'wa.me/+8801799000099'),
                'whats_active=1 + whats_num renders the floating WhatsApp button');
            $prodHtml = $this->get('/product/'.$prod->slug)->getContent();
            $this->chk(str_contains($hb,'T12HOTLINE2') || str_contains($prodHtml,'T12HOTLINE2')
                       || str_contains($co->getContent(),'T12HOTLINE2'),
                'Settings "Hotline 2" (supp_num1) appears somewhere on the storefront');
            $g['whats_active']='0';
            $this->from('http://puregadget.local/admin/settings')->put('/admin/settings/1', $g);
            $hb2 = $this->get('/')->getContent();
            $this->chk(!str_contains($hb2,'wa.me/+8801799000099'),
                'whats_active=0 removes the floating WhatsApp button');
            $this->chk(!str_contains($hb2,'wa.me/8801799000099'),
                'whats_active=0 also removes the footer WhatsApp links');

            // =====================================================================
            $this->section('G. Coupon visibility switch');
            $this->from('http://puregadget.local/admin/coupon-codes')->get('/admin/status-coupon?coupon_visibility=1');
            $co = $this->withSession(['cart'=>$cart])->get('/checkouts');
            $this->chk((int)$this->info()->coupon_visibility===1 && str_contains($co->getContent(),'coupon_code'),
                'coupon_visibility=1 -> coupon box appears on checkout');
            $this->from('http://puregadget.local/admin/coupon-codes')->get('/admin/status-coupon?coupon_visibility=0');
            $co = $this->withSession(['cart'=>$cart])->get('/checkouts');
            $this->chk((int)$this->info()->coupon_visibility===0 && !str_contains($co->getContent(),'id="coupon_code"'),
                'coupon_visibility=0 -> coupon box hidden on checkout');

            // =====================================================================
            $this->section('H. Invoice type + style settings');
            $orig_it = $origInfo['invoice_type'];
            $target  = ((int)$orig_it === 3) ? 4 : 3;
            $this->from('http://puregadget.local/admin/invoice-design')
                 ->post('/admin/update-invoice-type', ['type'=>$target]);
            $this->chk((int)$this->info()->invoice_type===$target, "invoice_type updated to $target");
            $r = $this->from('http://puregadget.local/admin/invoice-design')
                      ->post('/admin/update-invoice-type', ['type'=>9]);
            $this->chk((int)$this->info()->invoice_type===$target && $r->status()===302,
                'invoice_type rejects an out-of-range value (validation in:1,2,3,4)');
            $this->from('http://puregadget.local/admin/invoice-design')
                 ->post('/admin/update-invoice-type', ['type'=>$orig_it]);

            $style = [
                'primary_color'      => '#121212',
                'primary_background' => '#343434',
                'gradient_code'      => 'linear-gradient(90deg, rgba(18,18,18,1) 0%, rgba(52,52,52,1) 100%)',
                'footer_text'        => '#565656',
            ];
            $this->from('http://puregadget.local/admin/styles')->put('/admin/style/1', $style);
            $i = $this->info();
            $this->chk($i->primary_color==='#121212' && $i->footer_text==='#565656', 'PUT /admin/style/1 persists colours');
            $home = $this->get('/');
            $this->chk(str_contains($home->getContent(),'#121212'), 'Storefront CSS renders the saved primary colour');

            // gradient_code is deliberately kept when submitted empty
            $this->from('http://puregadget.local/admin/styles')->put('/admin/style/1', array_merge($style,['gradient_code'=>'']));
            $this->chk($this->info()->gradient_code === $style['gradient_code'],
                'Empty gradient_code in the style form keeps the previous gradient (intentional guard)');

            // =====================================================================
            $this->section('I. Profile form validation (no write)');
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/admin/profile-update', ['first_name'=>'', 'last_name'=>'', 'email'=>'not-an-email']);
            $j = json_decode($r->getContent(), true);
            $this->chk(isset($j['errors']['first_name']) && isset($j['errors']['email']),
                'Profile update returns field errors and writes nothing');
            $this->chk(User::find(1)->first_name === $admin->first_name, 'Admin user row untouched by the failed profile save');

            // =====================================================================
            $this->section('J. ManyDial audio proxy');
            Http::fake(['api.manydial.com/*' => Http::response(['data'=>['md5'=>'T12FAKEMD5']],200)]);
            $wavPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'T12-clip.wav';
            file_put_contents($wavPath, $this->tinyWav());
            $this->createdFiles[] = $wavPath;
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/admin/manydial/convert-audio', [
                          'audio'=>new UploadedFile($wavPath, 'T12-clip.wav', 'audio/wav', null, true)
                      ]);
            $j = json_decode($r->getContent(), true);
            $this->note('manydial ok-path status='.$r->status().' body='.substr($r->getContent(),0,140));
            $this->chk(($j['success']??false)===true && ($j['md5']??'')==='T12FAKEMD5',
                'ManyDial audio convert returns the md5 from the API');
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/admin/manydial/convert-audio', [
                          'audio'=>UploadedFile::fake()->create('T12-bad.txt', 5, 'text/plain')
                      ]);
            $this->note('manydial bad-file status='.$r->status());
            $this->chk(in_array($r->status(), [422,302]) && !str_contains($r->getContent(),'md5'),
                'ManyDial audio convert rejects a non-audio file');

            // =====================================================================
            $this->section('K. Dynamic text (AdminTextController)');
            $r = $this->get('/admin/dynamic-text');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'add_to_cart_text'),
                'GET /admin/dynamic-text renders the form');

            $dtPayload = [
                'popular_category_title' => 'T12 POPULAR CATS',
                'view_all_text'          => 'T12 VIEW ALL',
                'add_to_cart_text'       => 'T12 ADD TO CART',
                'order_now_text'         => 'T12 ORDER NOW',
                'call_btn_text'          => 'T12 CALL',
                'whatsapp_btn_text'      => 'T12 WHATSAPP',
                'submit_review_btn_text' => 'T12 SUBMIT REVIEW',
                'details_tab_text'       => 'T12 DETAILS',
                'reviews_tab_text'       => 'T12 REVIEWS',
                'common_btn_color'       => '#111111',
                'common_btn_text_color'  => '#222222',
                'order_now_btn_color'    => '#333333',
                'order_now_btn_text_color'=> '#444444',
            ];
            $this->from('http://puregadget.local/admin/dynamic-text')->post('/admin/dynamic-text', $dtPayload);
            $t = DB::table('admin_texts')->where('id',1)->first();
            $this->chk($t->add_to_cart_text==='T12 ADD TO CART' && $t->details_tab_text==='T12 DETAILS',
                'Dynamic text saved to admin_texts');
            $i = $this->info();
            $this->chk($i->common_btn_color==='#111111' && $i->order_now_btn_color==='#333333',
                'Dynamic-text form also saves the button colours into informations');

            $home = $this->get('/');
            $this->chk(str_contains($home->getContent(),'T12 POPULAR CATS') || str_contains($home->getContent(),'T12 VIEW ALL'),
                'Home page renders the configured popular-category / view-all labels');

            $pshow = $this->get('/product/'.$prod->slug);
            $pbody = $pshow->getContent();
            $this->chk($pshow->status()===200, 'Product page renders (status '.$pshow->status().')');
            $this->chk(str_contains($pbody,'T12 ORDER NOW'),  'Product page renders order_now_text');
            $this->chk(str_contains($pbody,'T12 DETAILS'),    'Product page renders details_tab_text');
            $this->chk(str_contains($pbody,'T12 REVIEWS'),    'Product page renders reviews_tab_text');
            $this->chk(str_contains($pbody,'T12 SUBMIT REVIEW'),'Product page renders submit_review_btn_text');
            $this->chk(str_contains($pbody,'#111111') || str_contains($pbody,'#333333'),
                'Product page renders the configured button colours');

            $popup = $this->get('/product-popup/'.$prod->id);
            $this->chk($popup->status()===200 && str_contains($popup->getContent(),'T12 ADD TO CART'),
                'Quick-view popup renders add_to_cart_text');

            // ---- three label columns: do they reach the product page at all? ----
            DB::table('admin_texts')->where('id',1)->update([
                'product_code_label'      => 'T12 CODE LABEL',
                'courier_delivery_title'  => 'T12 COURIER TITLE',
                'short_description_title' => 'T12 SHORT DESC',
            ]);
            $pbody2 = $this->get('/product/'.$prod->slug)->getContent();
            $ppop2  = $this->get('/product-popup/'.$prod->id)->getContent();
            $this->chk(str_contains($pbody2,'T12 CODE LABEL') || str_contains($ppop2,'T12 CODE LABEL'),
                'admin_texts.product_code_label reaches the product page');
            $this->chk(str_contains($pbody2,'T12 COURIER TITLE') || str_contains($ppop2,'T12 COURIER TITLE'),
                'admin_texts.courier_delivery_title reaches the product page');
            $this->chk(str_contains($pbody2,'T12 SHORT DESC'),
                'admin_texts.short_description_title reaches the product page');
            $this->note('hard-coded fallbacks still on page: '
                .(str_contains($pbody2,'Product Code :')?'"Product Code :" ':'')
                .(str_contains($pbody2,'Delivery Cost')?'"Delivery Cost" ':'')
                .(str_contains($pbody2,'>Description<')?'"Description"':''));

            // saving the Dynamic Text form again - are those three columns kept?
            $this->from('http://puregadget.local/admin/dynamic-text')->post('/admin/dynamic-text', $dtPayload);
            $t2 = DB::table('admin_texts')->where('id',1)->first();
            $this->note('after another save: product_code_label='.var_export($t2->product_code_label,true)
                        .' courier_delivery_title='.var_export($t2->courier_delivery_title,true)
                        .' short_description_title='.var_export($t2->short_description_title,true));
            $this->chk($t2->product_code_label==='T12 CODE LABEL' && $t2->courier_delivery_title==='T12 COURIER TITLE'
                       && $t2->short_description_title==='T12 SHORT DESC',
                'Saving the Dynamic Text form preserves the three label columns it does not expose');

            // call / whatsapp button labels
            $this->chk(str_contains($pbody,'T12 CALL') || str_contains($popup->getContent(),'T12 CALL'),
                'call_btn_text is rendered on the storefront');
            $this->chk(str_contains($pbody,'T12 WHATSAPP') || str_contains($popup->getContent(),'T12 WHATSAPP'),
                'whatsapp_btn_text is rendered on the storefront');

            // =====================================================================
            $this->section('L. Pages CRUD + public rendering');
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/admin/pages', ['title'=>'T12 Test Page','page'=>'t12-test-page','body'=>'<p>T12 BODY MARKER ONE</p>']);
            $j = json_decode($r->getContent(), true);
            $p = Page::where('page','t12-test-page')->first();
            if ($p) $this->createdPageIds[] = $p->id;
            $this->chk(($j['status']??false)===true && $p && $p->title==='T12 Test Page', 'POST /admin/pages creates the row');

            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])->post('/admin/pages', ['title'=>'T12 no body']);
            $this->note('POST /admin/pages with no body -> status '.$r->status());
            $this->chk(in_array($r->status(), [422,302]) && Page::where('title','T12 no body')->count()===0,
                'POST /admin/pages rejects a missing body/slug and stores nothing');

            $r = $this->get('/admin/pages');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'T12 Test Page'), 'Admin page list shows the new page');

            $r = $this->get('/admin/pages/'.$p->id.'/edit');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'T12 BODY MARKER ONE'), 'Admin page edit form is prefilled');

            $r = $this->get('/page/t12-test-page');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'T12 BODY MARKER ONE')
                       && str_contains($r->getContent(),'T12 Test Page'),
                'Public /page/{slug} renders title + body HTML');

            $this->chk(str_contains($this->get('/')->getContent(),'/page/t12-test-page'),
                'New page is linked from the storefront footer');

            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->put('/admin/pages/'.$p->id, ['title'=>'T12 Test Page v2','page'=>'t12-test-page','body'=>'<p>T12 BODY MARKER TWO</p>']);
            $p->refresh();
            $r2 = $this->get('/page/t12-test-page');
            $this->chk($p->body==='<p>T12 BODY MARKER TWO</p>' && str_contains($r2->getContent(),'T12 BODY MARKER TWO')
                       && !str_contains($r2->getContent(),'T12 BODY MARKER ONE'),
                'PUT /admin/pages/{id} updates DB and the public page');

            // dedicated legacy routes
            $about = $this->get('/about-us');
            $term  = $this->get('/term-condition');
            $priv  = $this->get('/privacy-policy');
            $ret   = $this->get('/return-policy');
            $this->note('slugs present in `pages`: '.implode(', ', Page::pluck('page')->toArray()));
            $this->chk($priv->status()===200 && strlen(strip_tags($priv->getContent()))>0
                       && str_contains($priv->getContent(), e(Page::where('page','privacy-policy')->value('title') ?? '#')),
                '/privacy-policy renders the matching page row');
            $this->chk($ret->status()===200 && str_contains($ret->getContent(), e(Page::where('page','return-policy')->value('title') ?? '#')),
                '/return-policy renders the matching page row');
            $aboutRow = Page::where('page','about-us')->first();
            $this->chk($about->status()===200 && $aboutRow && str_contains($about->getContent(), e($aboutRow->title)),
                '/about-us renders the shop\'s About page');
            $termRow = Page::where('page','terms-condition')->first();
            $this->chk($term->status()===200 && $termRow && str_contains($term->getContent(), e($termRow->title)),
                '/term-condition renders the shop\'s Terms page');

            // duplicate slug guard
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/admin/pages', ['title'=>'T12 Duplicate','page'=>'t12-test-page','body'=>'<p>T12 DUP</p>']);
            $dup = Page::where('page','t12-test-page')->where('title','T12 Duplicate')->first();
            if ($dup) $this->createdPageIds[] = $dup->id;
            $this->chk($dup === null, 'Duplicate page slug is rejected');

            foreach ($this->createdPageIds as $pid) {
                $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])->delete('/admin/pages/'.$pid);
            }
            $this->chk(Page::where('page','t12-test-page')->count()===0, 'DELETE /admin/pages/{id} removes the rows');
            $this->createdPageIds = [];
            $r = $this->get('/page/t12-test-page');
            $this->chk($r->status()===200 && !str_contains($r->getContent(),'T12 BODY MARKER'),
                'Deleted page no longer renders content on the storefront');

            // =====================================================================
            $this->section('M. Social icons CRUD');
            $img = UploadedFile::fake()->image('T12-social.jpg', 120, 120);
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/admin/social-icons', ['title'=>'T12 Social','link'=>'https://example.com/t12','icon'=>$img]);
            $s = SocialIcon::where('title','T12 Social')->first();
            if ($s) { $this->createdSocialIds[] = $s->id; if($s->image) $this->createdFiles[] = public_path('social_icons/'.$s->image); }
            $this->chk($s !== null && $s->link==='https://example.com/t12', 'POST /admin/social-icons creates the row');
            $this->chk($s && $s->image && file_exists(public_path('social_icons/'.$s->image)),
                'Social icon image written to public/social_icons ('.($s->image ?? 'none').')');

            $r = $this->get('/admin/social-icons');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'T12 Social'), 'Social icon list shows the row');
            $r = $this->get('/admin/social-icons/'.$s->id.'/edit');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'https://example.com/t12'), 'Social icon edit form prefilled');

            $oldImg = $s->image;
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->put('/admin/social-icons/'.$s->id, [
                          'title'=>'T12 Social v2','link'=>'https://example.com/t12v2',
                          'icon'=>UploadedFile::fake()->image('T12-social2.jpg',120,120)]);
            $s->refresh();
            if ($s->image) $this->createdFiles[] = public_path('social_icons/'.$s->image);
            $this->chk($s->title==='T12 Social v2' && $s->image!==$oldImg && file_exists(public_path('social_icons/'.$s->image)),
                'PUT social icon updates row + writes the new file');
            $this->chk(!file_exists(public_path('social_icons/'.$oldImg)), 'Old social icon file deleted on replace');

            $homeHtml = $this->get('/')->getContent();
            $this->chk(str_contains($homeHtml, $s->image) || str_contains($homeHtml,'social_icons'),
                'Saved social icon is rendered somewhere on the storefront');

            $newImg = $s->image;
            $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])->delete('/admin/social-icons/'.$s->id);
            $this->chk(SocialIcon::find($s->id)===null && !file_exists(public_path('social_icons/'.$newImg)),
                'DELETE social icon removes row and file');
            $this->createdSocialIds = [];

            // =====================================================================
            $this->section('N. Career CRUD');
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->post('/admin/career', ['title'=>'T12 Career','description'=>'<p>T12 CAREER BODY</p>',
                          'image'=>UploadedFile::fake()->image('T12-career.jpg',300,200)]);
            $c = Career::where('title','T12 Career')->first();
            if ($c) { $this->createdCareerIds[] = $c->id; if($c->image) $this->createdFiles[] = public_path('career/'.$c->image); }
            $this->chk($c !== null, 'POST /admin/career creates the row');
            $this->chk($c && $c->image && file_exists(public_path('career/'.$c->image)),
                'Career image written to public/career ('.($c->image ?? 'none').')');
            $r = $this->get('/admin/career');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'T12 Career'), 'Career list shows the row');
            $r = $this->get('/admin/career/'.$c->id.'/edit');
            $this->chk($r->status()===200 && str_contains($r->getContent(),'T12 CAREER BODY'), 'Career edit form prefilled');
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                      ->put('/admin/career/'.$c->id, ['title'=>'T12 Career v2','description'=>'<p>T12 CAREER BODY 2</p>']);
            $c->refresh();
            $this->chk($c->title==='T12 Career v2', 'PUT career updates the row');
            $r = $this->get('/careers');
            $this->chk($r->status()===200, 'Public /careers page loads (status '.$r->status().')');
            $cImg = $c->image;
            $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])->delete('/admin/career/'.$c->id);
            $this->chk(Career::find($c->id)===null && !file_exists(public_path('career/'.$cImg)),
                'DELETE career removes row and file');
            $this->createdCareerIds = [];

            // =====================================================================
            $this->section('O. About Us screen');
            $r = $this->get('/admin/about_us');
            $this->chk($r->status()===200, 'GET /admin/about_us renders (status '.$r->status().')');
            $r = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])->post('/admin/about_us', [
                'site_name'=>'T12 About Site','site_url'=>'https://example.com/t12',
                'page_title'=>'T12 ABOUT TITLE','sub_title'=>'T12 ABOUT SUB','speech'=>'T12 ABOUT SPEECH',
                'page_desc'=>'T12 ABOUT DESC','title_one'=>'T12 ONE','desc_one'=>'T12 DESC ONE',
            ]);
            $a = AboutUs::first();
            $this->chk($a && $a->page_title==='T12 ABOUT TITLE', 'POST /admin/about_us saves to about_us');
            $r2 = $this->get('/admin/about_us');
            $this->chk(str_contains($r2->getContent(),'T12 ABOUT TITLE'), 'Saved About Us content is shown back in the admin form');
            $front = $this->get('/about-us')->getContent().$this->get('/')->getContent();
            $this->chk(str_contains($front,'T12 ABOUT TITLE') || str_contains($front,'T12 ABOUT SPEECH'),
                'About Us content saved in admin is visible somewhere on the storefront');

            // =====================================================================
            $this->section('P. Worker (user 77) access to site-content screens');
            $worker = User::find(77);
            if ($worker) {
                $this->app['auth']->forgetGuards();
                $this->actingAs($worker);
                $rIdx = $this->get('/admin/pages');
                $rNew = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                             ->post('/admin/pages', ['title'=>'T12 Worker Page','page'=>'t12-worker-page','body'=>'<p>T12 WORKER BODY</p>']);
                $wp = Page::where('page','t12-worker-page')->first();
                if ($wp) $this->createdPageIds[] = $wp->id;
                $this->note('worker GET /admin/pages -> '.$rIdx->status().'; POST /admin/pages -> '.$rNew->status());
                $this->chk($wp === null, 'Worker (product.create only) is blocked from publishing a public page');
                $rSet = $this->get('/admin/settings');
                $this->chk(in_array($rSet->status(), [403,302]), 'Worker is blocked from /admin/settings (status '.$rSet->status().')');
                $rSoc = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                             ->post('/admin/social-icons', ['title'=>'T12 Worker Social','link'=>'https://example.com/w']);
                $ws = SocialIcon::where('title','T12 Worker Social')->first();
                if ($ws) $this->createdSocialIds[] = $ws->id;
                $this->chk($ws === null, 'Worker is blocked from creating social icons');
                $rCar = $this->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])
                             ->post('/admin/career', ['title'=>'T12 Worker Career','description'=>'x']);
                $wc = Career::where('title','T12 Worker Career')->first();
                if ($wc) $this->createdCareerIds[] = $wc->id;
                $this->chk($wc === null, 'Worker is blocked from creating career posts');
                $this->app['auth']->forgetGuards();
                $this->actingAs($admin);
            } else {
                $this->note('user 77 not found - worker checks skipped');
            }

        } finally {
            // ---------------- CLEANUP ----------------
            $this->app['auth']->forgetGuards();
            $this->actingAs(User::find(1));

            foreach ($this->createdPageIds as $pid)   { Page::where('id',$pid)->delete(); }
            foreach ($this->createdSocialIds as $sid) {
                $row = SocialIcon::find($sid);
                if ($row) { if($row->image) @unlink(public_path('social_icons/'.$row->image)); $row->delete(); }
            }
            foreach ($this->createdCareerIds as $cid) {
                $row = Career::find($cid);
                if ($row) { if($row->image) @unlink(public_path('career/'.$row->image)); $row->delete(); }
            }
            foreach ($this->createdFiles as $f) { if (file_exists($f)) @unlink($f); }

            unset($origInfo['id']);
            DB::table('informations')->where('id',1)->update($origInfo);
            unset($origText['id']);
            if (!empty($origText)) DB::table('admin_texts')->where('id',1)->update($origText);
            if (!empty($origAbout) && isset($origAbout['id'])) {
                $aid = $origAbout['id']; unset($origAbout['id']);
                DB::table('about_us')->where('id',$aid)->update($origAbout);
            }

            $i = $this->info();
            fwrite(STDOUT, "\n--- restore check ---\n");
            fwrite(STDOUT, "site_name=".$i->site_name." currency=".$i->currency." cod=".$i->cod_active
                ." bkash=".$i->bkash_active." otp=".$i->otp_system." topbar=".$i->topbar_active
                ." maxqty=".$i->max_order_qty." maxamt=".$i->max_order_amount
                ." coupon=".var_export($i->coupon_visibility,true)." invoice=".$i->invoice_type
                ." primary_color=".$i->primary_color."\n");
            $t = DB::table('admin_texts')->where('id',1)->first();
            fwrite(STDOUT, "admin_text add_to_cart=".var_export($t->add_to_cart_text,true)
                ." order_now=".var_export($t->order_now_text,true)."\n");
            fwrite(STDOUT, "leftovers: pages=".Page::where('title','like','T12%')->count()
                ." social=".SocialIcon::where('title','like','T12%')->count()
                ." career=".Career::where('title','like','T12%')->count()."\n");
            fwrite(STDOUT, "\n==== T12 RESULT: {$this->ok} OK / {$this->fail} FAIL ====\n");
        }

        $this->assertTrue(true);
    }

    private function section($t){ fwrite(STDOUT, "\n=== $t ===\n"); }

    /** Smallest structurally valid PCM wav so the `mimes:wav` rule passes. */
    private function tinyWav()
    {
        $samples = str_repeat("\x00\x00", 400);
        $dataLen = strlen($samples);
        return "RIFF".pack('V', 36+$dataLen)."WAVEfmt ".pack('V',16).pack('v',1).pack('v',1)
             . pack('V',8000).pack('V',16000).pack('v',2).pack('v',16)."data".pack('V',$dataLen).$samples;
    }

    /** Mimic the /admin/settings form: every field it actually posts. */
    private function generalPayload(array $orig, array $over)
    {
        $keys = ['site_name','owner_phone','owner_email','address','copyright','topbar_notice','facebook',
                 'instagram','tiktok','twitter','youtube','tracking_code','ga4_id','clarity_id','recommend_num',
                 'discount_num','newarrival_num','whats_num','supp_num1','supp_num2','supp_num3','currency',
                 'stock_warning_limit','max_order_amount','max_order_qty','time_limit','fb_pixel_id',
                 'tt_pixel_id','sms_api_key','sms_sender_id','admin_phone','admin_email'];
        $p = [];
        foreach ($keys as $k) { if (array_key_exists($k,$orig) && $orig[$k] !== null) $p[$k] = $orig[$k]; }
        // checkbox-style flags the general form always posts
        foreach (['topbar_active','otp_system','notification_active','is_ip_check','is_mobile_check',
                  'pathao_status','redx_status','whats_active','manydial_status','manydial_sms_status',
                  'checkout_reco_active','coupon_visibility',
                  'sms_pending_active','sms_confirmed_active','sms_processing_active','sms_courier_active',
                  'sms_delivered_active','sms_complete_active','sms_on_hold_active','sms_cancell_active',
                  'sms_returning_active','sms_return_received_active','sms_return_missing_active',
                  'sms_incomplete_active','sms_scheduled_active','sms_courier_complete_active'] as $k) {
            $p[$k] = (string)(int)($orig[$k] ?? 0);
        }
        return array_merge($p, $over);
    }

    /** Mimic the /admin/payment-settings form. */
    private function paymentPayload(array $orig, array $over)
    {
        $p = [];
        foreach (['bkash_number','nogod_number','rocket_number','ssl_store_id','ssl_store_password',
                  'eps_username','eps_merchant_id','nagad_merchant_id','uddoktapay_api_key','uddoktapay_base_url'] as $k) {
            if (array_key_exists($k,$orig) && $orig[$k] !== null) $p[$k] = $orig[$k];
        }
        foreach (['cod_active','bkash_active','bkash_sandbox','ssl_active','ssl_sandbox','ssl_terms_active',
                  'eps_active','eps_sandbox','nagad_active','nagad_sandbox','uddoktapay_active','manual_payments'] as $k) {
            $p[$k] = (string)(int)($orig[$k] ?? 0);
        }
        return array_merge($p, $over);
    }
}
