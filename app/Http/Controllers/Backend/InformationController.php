<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\{Information, User, PopularCategory, Category, ManualPayment};
use Illuminate\Http\Request;
use App\Utils\Util;
use Auth, Validator;

class InformationController extends Controller
{
    public function index()
    {
        $information = Information::orderBy('id', 'desc')->first();
        return view('backend.informations.index', compact('information'));
    }

    public function paymentSettings()
    {
        $information = Information::orderBy('id', 'desc')->first();
        $payments = ManualPayment::all();
        
        return view('backend.informations.payment_settings', compact('information', 'payments'));
    }

    public function colors()
    {
        $information = Information::orderBy('id', 'desc')->first();
        return view('backend.informations.style', compact('information'));
    }

    public function invoiceDesign()
    {
        $info = Information::first();
        return view('backend.informations.invoice_design', compact('info'));
    }

    public function updateInvoiceType(Request $request)
    {
        $request->validate([
            'type' => 'required|integer|in:1,2,3,4'
        ]);

        $info = Information::first();
        if ($info) {
            $info->invoice_type = $request->type;
            $info->save();
            return back()->with('success', 'Invoice Design Updated Successfully!');
        }

        return back()->with('error', 'Information settings not found!');
    }

    public function styleUpdate(Request $request, $id)
    {
        $data = $request->validate([
            'primary_color'       => 'nullable',
            'primary_background'  => 'nullable',
            'primary_background2' => 'nullable',
            'primary_background3' => 'nullable',
            'gradient_code'       => 'nullable',
            'footer_bg1' => 'nullable',
            'footer_bg2' => 'nullable',
            'footer_bg3' => 'nullable',
            'footer_text' => 'nullable',
            'footer_link_hover' => 'nullable',
            'footer_subtitle' => 'nullable',
            'footer_border_grad1' => 'nullable',
            'footer_border_grad2' => 'nullable',
            'footer_pill_bg' => 'nullable',
            'footer_pill_border' => 'nullable',
            'footer_pill_hover_bg' => 'nullable',
            'footer_pill_hover_text' => 'nullable',
            'footer_underline' => 'nullable',
            'footer_social_border' => 'nullable',
            'footer_social_bg' => 'nullable',
            'footer_social_hover_bg' => 'nullable',
            'footer_social_hover_text' => 'nullable',
            'mnav_bg' => 'nullable',
            'mnav_border' => 'nullable',
            'mnav_icon' => 'nullable',
            'mnav_home_bg' => 'nullable',
            'mnav_home_border' => 'nullable',
            'mnav_home_icon' => 'nullable',
            'footer_bg_color'     => 'nullable',
            'footer_text_color'   => 'nullable',
            'footer_accent_color' => 'nullable',
            'floating_btn_bg_color'   => 'nullable',
            'floating_btn_icon_color' => 'nullable',
            'popular_dot_color'       => 'nullable',
            'whatsapp_btn_bg_color'   => 'nullable',
            'whatsapp_btn_icon_color' => 'nullable',
            'view_all_btn_bg_color'   => 'nullable',
            'view_all_btn_text_color' => 'nullable',
            'category_heading_color'  => 'nullable',
        ]);

        $information = Information::orderBy('id', 'desc')->firstOrFail();

        if (empty($data['gradient_code'] ?? null)) unset($data['gradient_code']);

        $information->update($data);

        return back()->with(['msg' => 'Style settings has been updated']);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'site_name'    => 'nullable',
            'site_logo'    => 'max:2048',
            'footer_logo'  => 'max:2048',
            'fav_icon'     => 'max:2048',
            'gemini_api_key' => 'nullable',
            'owner_phone'  => 'nullable',
            'owner_email'  => 'nullable',
            'address'      => 'nullable',
            'copyright'    => 'nullable',
            'topbar_notice' => 'nullable',
            'topbar_active' => 'nullable',
            'topbar_bg_color' => 'nullable',
            'topbar_text_color' => 'nullable',
            'facebook'      => 'nullable',
            'instagram'     => 'nullable',
            'tiktok'        => 'nullable',
            'twitter'       => 'nullable',
            'youtube'       => 'nullable',
            'tracking_code' => 'nullable',
            'ga4_id'        => 'nullable|string',
            'clarity_id'    => 'nullable|string',
            'recommend_num' => 'nullable',
            'discount_num'  => 'nullable',
            'newarrival_num' => 'nullable',
            'bkash' => 'nullable',
            'bkash_number' => 'nullable',
            'bkash_active' => 'nullable',
            'bkash_sandbox' => 'nullable',
            'bkash_app_key' => 'nullable',
            'bkash_app_secret' => 'nullable',
            'bkash_username' => 'nullable',
            'bkash_password' => 'nullable',
            'nogod' => 'nullable',
            'nogod_number' => 'nullable',
            'rocket' => 'nullable',
            'rocket_number' => 'nullable',
            'paypal' => 'nullable',
            'paypal_account' => 'nullable',
            'stripe' => 'nullable',
            'stripe_account' => 'nullable',
            'whats_num' => 'nullable',
            'whats_active' => 'nullable',
            'msngr_chat' => 'nullable',
            'msngr_plugin' => 'nullable',
            'supp_num1' => 'nullable',
            'supp_num2' => 'nullable',
            'supp_num3' => 'nullable',
            'number_visibility' => 'nullable',
            'coupon_visibility' => 'nullable', 
            'checkout_reco_active' => 'nullable',
            'checkout_reco_title'  => 'nullable|string|max:150',
            'checkout_reco_limit'  => 'nullable|integer|min:1|max:12',
            'currency' => 'nullable',
            'redx_api_base_url' => 'nullable',
            'redx_api_access_token' => 'nullable',
            'pathao_api_base_url' => 'nullable',
            'pathao_api_access_token' => 'nullable',
            'pathao_store_id' => 'nullable',
            'steadfast_api_base_url' => 'nullable',
            'steadfast_api_key' => 'nullable',
            'steadfast_secret_key' => 'nullable',
            'carrybee_api_base_url' => 'nullable',
            'carrybee_api_key' => 'nullable',
            'carrybee_client_id' => 'nullable',
            'carrybee_client_secret' => 'nullable',
            'carrybee_client_context' => 'nullable',
            'carrybee_api_token' => 'nullable',
            'carrybee_store_id' => 'nullable',
            'fb_pixel_id' => 'nullable',
            'fb_pixel_test_code' => 'nullable',
            'fb_access_token' => 'nullable',
            'fb_event_source' => 'nullable|in:theme,gtm,gtm_only',
            'tt_pixel_id' => 'nullable',
            'tt_access_token' => 'nullable',
            'tt_test_event_code' => 'nullable',
            'tt_event_source' => 'nullable|in:theme,gtm',
            'steadfast_webhook_token' => 'nullable',
            'pathao_webhook_token' => 'nullable',
            'redx_webhook_token' => 'nullable',
            'carrybee_webhook_token' => 'nullable',
            'fraudApi' => 'nullable',
            'pathao_status' => 'nullable',
            'redx_status' => 'nullable',
            'is_ip_check' => 'nullable',
            'is_mobile_check' => 'nullable',
            'time_limit' => 'nullable',
            'primary_color' => 'nullable',
            'primary_background' => 'nullable',
            'primary_background2' => 'nullable',
            'primary_background3' => 'nullable',
            'gradient_code' => 'nullable',
            'footer_bg1' => 'nullable',
            'footer_bg2' => 'nullable',
            'footer_bg3' => 'nullable',
            'footer_text' => 'nullable',
            'footer_link_hover' => 'nullable',
            'footer_subtitle' => 'nullable',
            'footer_border_grad1' => 'nullable',
            'footer_border_grad2' => 'nullable',
            'footer_pill_bg' => 'nullable',
            'footer_pill_border' => 'nullable',
            'footer_pill_hover_bg' => 'nullable',
            'footer_pill_hover_text' => 'nullable',
            'footer_underline' => 'nullable',
            'footer_social_border' => 'nullable',
            'footer_social_bg' => 'nullable',
            'footer_social_hover_bg' => 'nullable',
            'footer_social_hover_text' => 'nullable',
            'mnav_bg' => 'nullable',
            'mnav_border' => 'nullable',
            'mnav_icon' => 'nullable',
            'mnav_home_bg' => 'nullable',
            'mnav_home_border' => 'nullable',
            'mnav_home_icon' => 'nullable',
            'footer_bg_color' => 'nullable',
            'footer_text_color' => 'nullable',
            'footer_accent_color' => 'nullable',
            'smtp_host' => 'nullable',
            'smtp_port' => 'nullable',
            'smtp_user' => 'nullable',
            'smtp_pass' => 'nullable',
            'sms_api_key' => 'nullable',
            'sms_sender_id' => 'nullable',
            'manydial_api_key' => 'nullable',
            'manydial_caller_id' => 'nullable',
            'manydial_status' => 'nullable',
            'manydial_welcome' => 'nullable|string',
            'manydial_msg_confirm' => 'nullable|string',
            'manydial_msg_cancel' => 'nullable|string',
            'manydial_key_confirm' => 'nullable|digits:1',
            'manydial_key_cancel' => 'nullable|digits:1|different:manydial_key_confirm',
            'manydial_per_call_duration' => 'nullable|integer|min:1|max:60',
            'manydial_repeat' => 'nullable|integer|min:0|max:10',
            'manydial_forward' => 'nullable|string|max:30',
            'manydial_max_attempts' => 'nullable|integer|min:1|max:5',
            'manydial_retry_delay' => 'nullable|integer|min:1|max:720',
            'manydial_voice_type' => 'nullable|in:azure,gemini',
            'manydial_voice_id' => 'nullable|string|max:30',
            'manydial_welcome_audio' => 'nullable|string|max:64',
            'manydial_sms_status' => 'nullable',
            'admin_phone' => 'nullable',
            'admin_email' => 'nullable',
            'sms_new_order_admin' => 'nullable',
            'sms_status_update' => 'nullable',
            
            // ✅ Existing SMS Fields
            'sms_pending'    => 'nullable',
            'sms_processing' => 'nullable',
            'sms_courier'    => 'nullable',
            'sms_complete'   => 'nullable',
            'sms_cancell'    => 'nullable',
            'sms_return'     => 'nullable',
            'sms_on_hold'    => 'nullable',
            'sms_confirmed'              => 'nullable',
            'sms_delivered'              => 'nullable',
            'sms_returning'              => 'nullable',
            'sms_return_received'        => 'nullable',
            'sms_return_missing'         => 'nullable',
            
            // ✅ New SMS Status Fields Added Here
            'sms_incomplete'             => 'nullable',
            'sms_scheduled'              => 'nullable',
            'sms_courier_complete'       => 'nullable',
            'sms_incomplete_active'      => 'nullable',
            'sms_scheduled_active'       => 'nullable',
            'sms_courier_complete_active'=> 'nullable',

            // ✅ Existing Active Toggles
            'sms_pending_active'         => 'nullable',
            'sms_confirmed_active'       => 'nullable',
            'sms_processing_active'      => 'nullable',
            'sms_courier_active'         => 'nullable',
            'sms_delivered_active'       => 'nullable',
            'sms_complete_active'        => 'nullable',
            'sms_on_hold_active'         => 'nullable',
            'sms_cancell_active'         => 'nullable',
            'sms_returning_active'       => 'nullable',
            'sms_return_received_active' => 'nullable',
            'sms_return_missing_active'  => 'nullable',
            'otp_system' => 'nullable',
            'notification_active' => 'nullable',
            'ssl_store_id'       => 'nullable',
            'ssl_store_password' => 'nullable',
            'ssl_sandbox'        => 'nullable',
            'ssl_active'         => 'nullable',
            'ssl_terms_active'   => 'nullable',
            'cod_active'         => 'nullable',
            'ssl_sandbox_store_id'       => 'nullable',
            'ssl_sandbox_store_password' => 'nullable',
            'stock_warning_limit'        => 'nullable|integer', 
            'max_order_amount'           => 'nullable|numeric',
            'max_order_qty'              => 'nullable|integer',
            'eps_active'      => 'nullable',
            'eps_sandbox'     => 'nullable',
            'eps_username'    => 'nullable',
            'eps_password'    => 'nullable',
            'eps_hash_key'    => 'nullable',
            'eps_merchant_id' => 'nullable',
            'eps_store_id'    => 'nullable',
            'eps_sandbox_merchant_id' => 'nullable',
            'eps_sandbox_store_id'    => 'nullable',
            'eps_sandbox_username'    => 'nullable',
            'eps_sandbox_password'    => 'nullable',
            'eps_sandbox_hash_key'    => 'nullable',
            'nagad_active'                  => 'nullable',
            'nagad_sandbox'                 => 'nullable',
            'nagad_merchant_id'             => 'nullable',
            'nagad_merchant_number'         => 'nullable',
            'nagad_public_key'              => 'nullable',
            'nagad_private_key'             => 'nullable',
            'nagad_sandbox_merchant_id'     => 'nullable',
            'nagad_sandbox_merchant_number' => 'nullable',
            'nagad_sandbox_public_key'      => 'nullable',
            'nagad_sandbox_private_key'     => 'nullable',
            'uddoktapay_active'   => 'nullable',
            'uddoktapay_api_key'  => 'nullable',
            'uddoktapay_base_url' => 'nullable',
            'manual_payments' => 'nullable',
        ]);

        $information = Information::orderBy('id', 'desc')->firstOrFail();

        if (str_contains(url()->previous(), 'payment-settings')) {
            $data['ssl_sandbox']           = $request->boolean('ssl_sandbox') ? 1 : 0;
            $data['ssl_active']            = $request->boolean('ssl_active') ? 1 : 0;
            $data['ssl_terms_active']      = $request->boolean('ssl_terms_active') ? 1 : 0;
            $data['cod_active']            = $request->boolean('cod_active') ? 1 : 0;
            $data['bkash_active']          = $request->boolean('bkash_active') ? 1 : 0;
            $data['bkash_sandbox']         = $request->boolean('bkash_sandbox') ? 1 : 0;
            $data['eps_active']            = $request->boolean('eps_active') ? 1 : 0;
            $data['eps_sandbox']           = $request->boolean('eps_sandbox') ? 1 : 0;
            $data['nagad_active']          = $request->boolean('nagad_active') ? 1 : 0;
            $data['nagad_sandbox']         = $request->boolean('nagad_sandbox') ? 1 : 0;
            $data['uddoktapay_active']     = $request->boolean('uddoktapay_active') ? 1 : 0;
            
            // Fixed Line Below: Changed 'manual_payments_active' to 'manual_payments'
            $data['manual_payments']       = $request->boolean('manual_payments') ? 1 : 0;

            if ($request->has('manual_status')) {
                foreach ($request->manual_status as $payment_id => $status) {
                    \App\Models\ManualPayment::where('id', $payment_id)->update(['status' => $status]);
                }
            }
        } 
        else {
            $data['topbar_active']       = $request->boolean('topbar_active') ? 1 : 0;
            $data['otp_system']          = $request->boolean('otp_system') ? 1 : 0;
            $data['notification_active'] = $request->boolean('notification_active') ? 1 : 0;
            $data['is_ip_check']         = $request->boolean('is_ip_check') ? 1 : 0;
            $data['is_mobile_check']     = $request->boolean('is_mobile_check') ? 1 : 0;
            $data['pathao_status']       = $request->boolean('pathao_status') ? 1 : 0;
            $data['redx_status']         = $request->boolean('redx_status') ? 1 : 0;
            $data['whats_active']        = $request->boolean('whats_active') ? 1 : 0;
            $data['manydial_status']     = $request->boolean('manydial_status') ? 1 : 0;
            $data['manydial_sms_status'] = $request->boolean('manydial_sms_status') ? 1 : 0;
            $data['checkout_reco_active'] = $request->boolean('checkout_reco_active') ? 1 : 0;

            $data['sms_pending_active']         = $request->boolean('sms_pending_active') ? 1 : 0;
            $data['sms_confirmed_active']       = $request->boolean('sms_confirmed_active') ? 1 : 0;
            $data['sms_processing_active']      = $request->boolean('sms_processing_active') ? 1 : 0;
            $data['sms_courier_active']         = $request->boolean('sms_courier_active') ? 1 : 0;
            $data['sms_delivered_active']       = $request->boolean('sms_delivered_active') ? 1 : 0;
            $data['sms_complete_active']        = $request->boolean('sms_complete_active') ? 1 : 0;
            $data['sms_on_hold_active']         = $request->boolean('sms_on_hold_active') ? 1 : 0;
            $data['sms_cancell_active']         = $request->boolean('sms_cancell_active') ? 1 : 0;
            $data['sms_returning_active']       = $request->boolean('sms_returning_active') ? 1 : 0;
            $data['sms_return_received_active'] = $request->boolean('sms_return_received_active') ? 1 : 0;
            $data['sms_return_missing_active']  = $request->boolean('sms_return_missing_active') ? 1 : 0;
            
            // ✅ New SMS Toggle Fields Added Here
            $data['sms_incomplete_active']       = $request->boolean('sms_incomplete_active') ? 1 : 0;
            $data['sms_scheduled_active']        = $request->boolean('sms_scheduled_active') ? 1 : 0;
            $data['sms_courier_complete_active'] = $request->boolean('sms_courier_complete_active') ? 1 : 0;
        }

        if (empty($data['gradient_code'] ?? null)) unset($data['gradient_code']);

        if ($request->hasFile('site_logo')) {
            Util::deleteFile($information->site_logo, 'img');
            $data['site_logo'] = Util::uploadFile($request->site_logo, 'img');
        }

        if ($request->hasFile('footer_logo')) {
            Util::deleteFile($information->footer_logo, 'img');
            $data['footer_logo'] = Util::uploadFile($request->footer_logo, 'img');
        }

        if ($request->hasFile('fav_icon')) {
            Util::deleteFile($information->fav_icon, 'img');
            $data['fav_icon'] = Util::uploadFile($request->fav_icon, 'img');
        }

        // Ei column gulo DB-te NOT NULL (kono default nai). Form-e faka rakhle
        // middleware "" -> NULL kore dey, tarpor "Column cannot be null" DB
        // error-e pura Settings page crash kore jeto (jemon Instagram/YouTube
        // faka rakhle just hoyechilo). Faka rakhle empty string '' bosao.
        foreach (['site_name', 'owner_phone', 'owner_email', 'address', 'copyright', 'facebook', 'instagram', 'youtube', 'nogod_number'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] === null) $data[$k] = '';
        }

        $information->update($data);

        return back()->with(['msg' => 'Settings have been updated successfully!']);
    }

    public function statusCoupon(Request $request)
    {
        $information = Information::orderBy('id', 'desc')->first();
        if($information) {
            $information->coupon_visibility = $request->coupon_visibility;
            $information->save();
        }

        return back()->with(['msg' => 'Coupon status updated successfully']);
    }

    public function showProfile()
    {
        $data = Auth::user();
        return view('backend.informations.profile', compact('data'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'username' => ['required', 'unique:users,username,' . $user->id],
            'mobile' => ['required', 'unique:users,mobile,' . $user->id],
            'business_name' => ['required'],
            'image' => ['max:2048'],
        ]);

        $data = $request->only(['first_name', 'last_name', 'email', 'username', 'mobile', 'business_name']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        if ($request->hasFile('image')) {
            Util::deleteFile($user->image, 'img');
            $data['image'] = Util::uploadFile($request->image, 'img');
        }

        $user->update($data);

        return response()->json(['success' => 'Profile has been updated']);
    }

    /**
     * Setup instructions for every tracking platform the shop can switch on.
     * Read-only page — it shows what is configured right now and how to fill
     * in whatever is missing, so the settings form does not have to carry a
     * wall of explanation next to each field.
     */
    public function trackingGuide()
    {
        $information = Information::orderBy('id', 'desc')->first();

        return view('backend.informations.tracking_guide', compact('information'));
    }

    /**
     * Proxies ManyDial's audio/convert API: the admin uploads an mp3 once and
     * gets back the md5 code ManyDial plays instead of TTS when that code is
     * placed in a message field (manydial_welcome_audio).
     */
    public function convertManyDialAudio(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,mpga,wav|max:10240',
        ]);

        $information = Information::orderBy('id', 'desc')->first();

        if (!$information || empty($information->manydial_api_key)) {
            return response()->json(['success' => false, 'message' => 'আগে ManyDial API Key সেভ করুন।'], 422);
        }

        try {
            $file = $request->file('audio');

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'x-api-key' => $information->manydial_api_key,
                ])
                ->attach('audio', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post('https://api.manydial.com/v1/portal/audio/convert');

            $md5 = $response->json('data.md5');

            if ($response->successful() && !empty($md5)) {
                return response()->json(['success' => true, 'md5' => $md5]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json('message') ?: 'ManyDial অডিওটি নেয়নি — ফাইলটি ঠিক আছে কিনা দেখুন।',
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}