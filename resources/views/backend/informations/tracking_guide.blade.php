@extends('backend.app')

@push('css')
<style>
    .tg-wrap { max-width: 1100px; }
    .tg-head { border-bottom: 3px solid #14181f; padding-bottom: 14px; margin-bottom: 26px; }
    .tg-head h4 { font-weight: 700; margin-bottom: 4px; }
    .tg-head p { color: #6b7280; margin-bottom: 0; font-size: 14px; }

    .tg-status { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1px; background: #e5e7eb; border: 1px solid #e5e7eb; margin-bottom: 30px; }
    .tg-status .cell { background: #fff; padding: 14px 16px; }
    .tg-status .cell .name { font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: #6b7280; font-weight: 700; }
    .tg-status .cell .state { font-size: 15px; font-weight: 700; margin-top: 3px; }
    .tg-status .on  { color: #16624a; }
    .tg-status .off { color: #9a5b00; }

    .tg-card { background: #fff; border: 1px solid #e5e7eb; border-top: 3px solid #00276c; margin-bottom: 24px; }
    .tg-card > .hd { padding: 14px 20px; border-bottom: 1px solid #f0f1f4; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .tg-card > .hd h5 { margin: 0; font-weight: 700; font-size: 17px; }
    .tg-card > .hd .badge-need { font-size: 11px; letter-spacing: .06em; text-transform: uppercase; padding: 3px 9px; border-radius: 2px; }
    .tg-card > .bd { padding: 18px 20px; font-size: 14.5px; }
    .tg-card.fb  { border-top-color: #1877f2; }
    .tg-card.tt  { border-top-color: #000; }
    .tg-card.ga  { border-top-color: #e37400; }
    .tg-card.cl  { border-top-color: #4b3fbb; }
    .tg-card.gtm { border-top-color: #8ab4f8; }

    .tg-steps { counter-reset: s; list-style: none; padding: 0; margin: 0 0 6px; }
    .tg-steps li { counter-increment: s; position: relative; padding: 0 0 12px 34px; }
    .tg-steps li::before { content: counter(s); position: absolute; left: 0; top: 0; width: 23px; height: 23px; border-radius: 50%; background: #eef2f9; color: #00276c; font-weight: 700; font-size: 12.5px; display: flex; align-items: center; justify-content: center; }
    .tg-steps li b { color: #14181f; }

    .tg-field { background: #f7f8fa; border: 1px solid #e5e7eb; border-radius: 3px; padding: 2px 8px; font-family: Consolas, monospace; font-size: 12.5px; color: #00276c; white-space: nowrap; }
    .tg-note { border-left: 3px solid #d1d5db; background: #fafbfc; padding: 10px 14px; font-size: 13.5px; color: #4a5260; margin-top: 12px; }
    .tg-note.warn { border-left-color: #9a5b00; background: #fdf8ef; color: #7a4a00; }
    .tg-note.good { border-left-color: #16624a; background: #f0f7f4; color: #14503c; }
    .tg-table { width: 100%; font-size: 13.5px; }
    .tg-table th { background: #f7f8fa; font-weight: 700; padding: 8px 10px; border: 1px solid #e5e7eb; }
    .tg-table td { padding: 8px 10px; border: 1px solid #e5e7eb; vertical-align: top; }
</style>
@endpush

@section('content')
@php
    $has = fn ($v) => !empty(trim((string) $v));
    $fbOn = $has($information->fb_pixel_id ?? null);
    $ttOn = $has($information->tt_pixel_id ?? null);
    $gaOn = $has($information->ga4_id ?? null);
    $clOn = $has($information->clarity_id ?? null);
    $gtmOn = $has($information->tracking_code ?? null);
    $source = $information->fb_event_source ?? 'theme';
    $sourceLabel = ['theme' => 'Website (Theme)', 'gtm' => 'GTM + Server', 'gtm_only' => 'GTM only'][$source] ?? 'Website (Theme)';
@endphp

<div class="tg-wrap">

    <div class="tg-head">
        <h4>Tracking Setup Guide</h4>
        <p>Facebook Pixel, TikTok Pixel, Google Analytics, Microsoft Clarity এবং Google Tag Manager — কোনটা কোথায় বসাতে হবে, ধাপে ধাপে।</p>
    </div>

    <div class="tg-status">
        <div class="cell"><div class="name">Facebook Pixel</div><div class="state {{ $fbOn ? 'on' : 'off' }}">{{ $fbOn ? 'চালু আছে' : 'বসানো হয়নি' }}</div></div>
        <div class="cell"><div class="name">TikTok Pixel</div><div class="state {{ $ttOn ? 'on' : 'off' }}">{{ $ttOn ? 'চালু আছে' : 'বসানো হয়নি' }}</div></div>
        <div class="cell"><div class="name">Google Analytics</div><div class="state {{ $gaOn ? 'on' : 'off' }}">{{ $gaOn ? 'চালু আছে' : 'বসানো হয়নি' }}</div></div>
        <div class="cell"><div class="name">Clarity</div><div class="state {{ $clOn ? 'on' : 'off' }}">{{ $clOn ? 'চালু আছে' : 'বসানো হয়নি' }}</div></div>
        <div class="cell"><div class="name">Tag Manager</div><div class="state {{ $gtmOn ? 'on' : 'off' }}">{{ $gtmOn ? 'কোড বসানো আছে' : 'ব্যবহার হচ্ছে না' }}</div></div>
        <div class="cell"><div class="name">Event Source</div><div class="state on">{{ $sourceLabel }}</div></div>
    </div>

    <div class="tg-note good mb-4">
        <b>শুরুতেই জেনে রাখুন:</b> এই সাইট নিজেই Facebook ও TikTok-এ ইভেন্ট পাঠাতে পারে — ব্রাউজার থেকেও, সার্ভার থেকেও।
        তাই <b>Google Tag Manager ব্যবহার করা বাধ্যতামূলক নয়</b>। শুধু Pixel ID আর Access Token বসিয়ে দিলেই সব চলবে।
    </div>

    {{-- FACEBOOK --}}
    <div class="tg-card fb">
        <div class="hd">
            <h5>Facebook Pixel (Meta)</h5>
            <span class="badge-need" style="background:#e8f0fe;color:#1877f2;">অবশ্যই দরকার</span>
        </div>
        <div class="bd">
            <p class="mb-3">বিজ্ঞাপনের ফল মাপতে ও রিটার্গেটিং করতে এটাই সবচেয়ে জরুরি।</p>
            <ol class="tg-steps">
                <li><b>Pixel ID নিন:</b> Meta Events Manager → আপনার পিক্সেল → Settings। আইডিটা ১৫–১৬ ডিজিটের সংখ্যা।</li>
                <li><b>Access Token নিন:</b> একই পেজে নিচে <b>Conversions API → Generate Access Token</b>। এটা লম্বা একটা টেক্সট, <span class="tg-field">EAA…</span> দিয়ে শুরু হয়।</li>
                <li><b>বসান:</b> Settings → Tracking &amp; Analytics → Facebook Pixel সেকশনে <span class="tg-field">Pixel ID</span> ও <span class="tg-field">Conversion API Access Token</span> ঘরে বসিয়ে Save করুন।</li>
                <li><b>যাচাই করুন:</b> Events Manager → Test Events-এ গিয়ে সাইটে একটা প্রোডাক্ট খুলুন। ইভেন্ট দেখা গেলেই কাজ শেষ।</li>
            </ol>
            <div class="tg-note">
                <b>ইভেন্ট কে পাঠাবে?</b> ঘরটা <b>"এই ওয়েবসাইট (Theme)"</b> রাখুন — এটাই সুপারিশকৃত। তখন ব্রাউজার ও সার্ভার দুই দিক থেকেই ইভেন্ট যাবে,
                আর একই Event ID ব্যবহার হওয়ায় ফেসবুক ডাবল কাউন্ট করবে না।
            </div>
            <div class="tg-note warn">
                <b>Access Token খালি রাখলে কী হয়?</b> শুধু ব্রাউজার থেকে ইভেন্ট যাবে। iPhone/Safari বা অ্যাড-ব্লকার আছে এমন কাস্টমারের বিক্রি ফেসবুক ধরতে পারবে না —
                সাধারণত ২০–৩০% বিক্রি হিসাবের বাইরে চলে যায়।
            </div>
        </div>
    </div>

    {{-- TIKTOK --}}
    <div class="tg-card tt">
        <div class="hd">
            <h5>TikTok Pixel</h5>
            <span class="badge-need" style="background:#f1f1f1;color:#111;">TikTok Ads চালালে দরকার</span>
        </div>
        <div class="bd">
            <ol class="tg-steps">
                <li><b>Pixel ID নিন:</b> TikTok Ads Manager → Tools → Events → Web Events → আপনার পিক্সেল। আইডিটা ২০ অক্ষরের, যেমন <span class="tg-field">D5UO9NBC77U10VTVTO6G</span>।</li>
                <li><b>Access Token নিন:</b> একই জায়গায় <b>Events API → Generate Access Token</b>। এটা ৪০ অক্ষরের একটা কোড।</li>
                <li><b>বসান:</b> Settings → Tracking &amp; Analytics → TikTok Pixel সেকশনে দুটোই বসিয়ে Save করুন।</li>
                <li><b>যাচাই করুন:</b> TikTok Events Manager → Test Event-এ সাইটে ঘুরে দেখুন ইভেন্ট আসছে কি না।</li>
            </ol>
            <div class="tg-note">
                Facebook-এর মতোই TikTok-ও ব্রাউজার ও সার্ভার দুই দিক থেকে ইভেন্ট পায়, একই Event ID দিয়ে। TikTok Ads না চালালে এটা খালি রাখতে পারেন।
            </div>
        </div>
    </div>

    {{-- GOOGLE ANALYTICS --}}
    <div class="tg-card ga">
        <div class="hd">
            <h5>Google Analytics 4</h5>
            <span class="badge-need" style="background:#fdf1e3;color:#a35c00;">ঐচ্ছিক</span>
        </div>
        <div class="bd">
            <p class="mb-3">কত মানুষ আসছে, কোন পেজ কত দেখা হচ্ছে, কোথা থেকে ট্রাফিক আসছে — এসব জানার জন্য।</p>
            <ol class="tg-steps">
                <li><b>Measurement ID নিন:</b> analytics.google.com → Admin → Data Streams → আপনার ওয়েব স্ট্রিম। আইডিটা <span class="tg-field">G-XXXXXXXXXX</span> এভাবে শুরু হয়।</li>
                <li><b>বসান:</b> Settings → Tracking &amp; Analytics → <span class="tg-field">Google Analytics 4 ID</span> ঘরে বসিয়ে Save করুন।</li>
                <li><b>যাচাই করুন:</b> Analytics → Reports → Realtime-এ নিজের ভিজিট দেখা গেলে হয়ে গেছে।</li>
            </ol>
            <div class="tg-note">
                এটা বিজ্ঞাপনের জন্য নয়, শুধু হিসাব দেখার জন্য। Facebook/TikTok-এর সাথে এর কোনো সম্পর্ক নেই — একটার জন্য আরেকটা লাগে না।
            </div>
        </div>
    </div>

    {{-- CLARITY --}}
    <div class="tg-card cl">
        <div class="hd">
            <h5>Microsoft Clarity</h5>
            <span class="badge-need" style="background:#efedfa;color:#4b3fbb;">ঐচ্ছিক · ফ্রি</span>
        </div>
        <div class="bd">
            <p class="mb-3">কাস্টমার সাইটে কী করছে তার রেকর্ডিং ও হিটম্যাপ দেখা যায় — কোথায় ক্লিক করছে, কোথায় আটকে যাচ্ছে। পুরোপুরি ফ্রি।</p>
            <ol class="tg-steps">
                <li><b>Project ID নিন:</b> clarity.microsoft.com-এ প্রজেক্ট বানান → Settings → Setup। আইডিটা ছোট, যেমন <span class="tg-field">abcdef1234</span>।</li>
                <li><b>বসান:</b> Settings → Tracking &amp; Analytics → <span class="tg-field">Clarity ID</span> ঘরে বসিয়ে Save করুন।</li>
                <li><b>যাচাই করুন:</b> কয়েক ঘণ্টা পর Clarity-তে Recordings দেখা যাবে।</li>
            </ol>
            <div class="tg-note">
                অর্ডার কম হলে বা চেকআউটে কাস্টমার আটকে গেলে কারণ খুঁজতে এটা খুব কাজে দেয়।
            </div>
        </div>
    </div>

    {{-- GTM --}}
    <div class="tg-card gtm">
        <div class="hd">
            <h5>Google Tag Manager</h5>
            <span class="badge-need" style="background:#fdf8ef;color:#9a5b00;">দরকার নেই — শুধু বিশেষ ক্ষেত্রে</span>
        </div>
        <div class="bd">
            <div class="tg-note warn" style="margin-top:0;margin-bottom:16px;">
                <b>বেশিরভাগ দোকানের এটা লাগে না।</b> এই সাইট নিজেই Facebook ও TikTok সামলায়। GTM ব্যবহার করলে সাইট নিজের ইভেন্ট পাঠানো
                <b>বন্ধ করে দেয়</b> — তখন সব ট্যাগ আপনাকে GTM-এর ভেতরে নিজে বানাতে হবে। না বানালে ট্র্যাকিং চুপচাপ বন্ধ হয়ে যাবে, কোনো error দেখাবে না।
            </div>

            <p class="mb-2"><b>তবু ব্যবহার করতে চাইলে:</b></p>
            <ol class="tg-steps">
                <li><b>Web container বানান:</b> tagmanager.google.com → Create Container → টাইপ অবশ্যই <b>Web</b> (Server নয়)। আইডি পাবেন <span class="tg-field">GTM-XXXXXXX</span>।</li>
                <li><b>কোড বসান:</b> GTM-এর <b>&lt;head&gt;</b> কোডটুকু (script অংশ) Settings → Tracking &amp; Analytics → <span class="tg-field">Tracking Code</span> ঘরে পেস্ট করুন। <b>noscript/body অংশটা লাগবে না।</b></li>
                <li><b>মোড বদলান:</b> Facebook Pixel সেকশনে "ইভেন্ট কে পাঠাবে?" → <b>GTM + সার্ভার ইভেন্ট</b> অথবা <b>শুধু GTM</b>।</li>
                <li><b>ট্রিগার বানান:</b> GTM → Triggers → New → Custom Event। নিচের টেবিলের নামগুলো হুবহু লিখুন।</li>
                <li><b>ট্যাগ বানান:</b> প্রতিটা ট্রিগারের জন্য Meta/TikTok ট্যাগ, আর <b>Event ID</b> ঘরে <span class="tg-field">event_id</span> ভেরিয়েবল বসান।</li>
            </ol>

            <table class="tg-table mt-3">
                <thead>
                    <tr>
                        <th style="width:26%">GTM-এ Event name</th>
                        <th style="width:24%">কখন হয়</th>
                        <th style="width:25%">Facebook ট্যাগ</th>
                        <th style="width:25%">TikTok ট্যাগ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><span class="tg-field">view_item</span></td><td>প্রোডাক্ট / ল্যান্ডিং পেজ</td><td>ViewContent</td><td>ViewContent</td></tr>
                    <tr><td><span class="tg-field">add_to_cart</span></td><td>কার্টে যোগ</td><td>AddToCart</td><td>AddToCart</td></tr>
                    <tr><td><span class="tg-field">begin_checkout</span></td><td>চেকআউট পেজ</td><td>InitiateCheckout</td><td>InitiateCheckout</td></tr>
                    <tr><td><span class="tg-field">purchase</span></td><td>অর্ডার সফল</td><td>Purchase</td><td>CompletePayment</td></tr>
                </tbody>
            </table>

            <div class="tg-note warn">
                <b>Event ID না বসালে:</b> সার্ভার আর ব্রাউজারের ইভেন্ট আলাদা গোনা হবে — প্রতিটা বিক্রি <b>দুইবার</b> দেখাবে, ROAS দ্বিগুণ দেখাবে।
                ভুল করে বাজেট বাড়ালে আসলে লোকসান হবে। GTM-এ ভেরিয়েবল বানানোর নিয়ম: Variables → New → Data Layer Variable → নাম <span class="tg-field">event_id</span>।
            </div>
            <div class="tg-note">
                <b>Stape.io বা server-side GTM লাগবে?</b> না। সার্ভার থেকে ইভেন্ট পাঠানোর ব্যবস্থা এই সাইটে আগে থেকেই আছে, তাই বাড়তি সার্ভিসের খরচ করার দরকার নেই।
            </div>
        </div>
    </div>

    <div class="tg-note good">
        <b>সংক্ষেপে কী করবেন:</b> Facebook-এর Pixel ID ও Access Token বসান, TikTok Ads চালালে TikTok-এরটাও বসান, চাইলে Analytics ও Clarity যোগ করুন —
        আর "ইভেন্ট কে পাঠাবে?" ঘরটা <b>"এই ওয়েবসাইট (Theme)"</b> রেখে দিন। GTM ছাড়াই সব নিখুঁতভাবে চলবে।
    </div>

    <div class="mt-4 mb-5">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-primary fw-bold">
            <i class="mdi mdi-cog-outline"></i> Go to Tracking Settings
        </a>
    </div>

</div>
@endsection
