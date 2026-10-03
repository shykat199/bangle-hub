<head>
    @php
        $information = getInfo();
    @endphp

    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title', $information->site_name)</title>
    <meta name="robots" content="index, follow">

    <meta name="description"
          content="@yield(
              'meta_description',
              $information->site_name . ' is an online marketplace in Bangladesh offering quality products, fair prices, and reliable service.'
          )">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" type="image/x-icon"
          href="{{ asset('uploads/img/'.$information->fav_icon) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@100;200;300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css">

    <style>
        i.fal.fa-arrow-up,
        i.fas.fa-arrow-up,
        .fal.fa-arrow-up,
        .fas.fa-arrow-up,
        i.fa-arrow-up,
        .fa-arrow-up{
            font-family: "Font Awesome 5 Free" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            color: #ffffff !important;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/flaticon/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/sal.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/vendor/base.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/update.css') }}">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

    @stack('css')

    <!-- General Tracking Script (E.g., GTM Head Code) -->
    {!! $information->tracking_code !!}

    <!-- Google Analytics 4 (GA4) -->
    @if(isset($information->ga4_id) && !empty($information->ga4_id))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $information->ga4_id }}"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '{{ $information->ga4_id }}');
        </script>
    @endif

    <!-- Microsoft Clarity -->
    @if(isset($information->clarity_id) && !empty($information->clarity_id))
        <script type="text/javascript">
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", "{{ $information->clarity_id }}");
        </script>
    @endif

    <!-- Facebook Pixel -->
    @if($information->fb_pixel_id)
        <script>
            !function(f,b,e,v,n,t,s){
                if(f.fbq)return;
                n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;
                n.push=n;
                n.loaded=!0;
                n.version='2.0';
                n.queue=[];
                t=b.createElement(e);t.async=!0;
                t.src=v;
                s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)
            }(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');

            fbq('init', '{{ $information->fb_pixel_id }}');
            @if(!$__hasGtm)
            fbq('track', 'PageView');
            @endif
        </script>

        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ $information->fb_pixel_id }}&ev=PageView&noscript=1">
        </noscript>
    @endif

    <!-- TikTok Pixel — sob frontend page-e load hobe (Pixel Helper detect korar jonno) -->
    @if(!empty($information->tt_pixel_id))
        <script>
        !function (w, d, t) {
            w.TiktokAnalyticsObject = t;
            var ttq = w[t] = w[t] || [];
            ttq.methods = ["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"];
            ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; };
            for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
            ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]); return e; };
            ttq.load = function (e, n) {
                var r = "https://analytics.tiktok.com/i18n/pixel/events.js"; var o = n && n.partner;
                ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = r;
                ttq._t = ttq._t || {}; ttq._t[e] = +new Date;
                ttq._o = ttq._o || {}; ttq._o[e] = n || {};
                var s = document.createElement("script"); s.type = "text/javascript"; s.async = !0;
                s.src = r + "?sdkid=" + e + "&lib=" + t;
                var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x);
            };
            ttq.load('{{ $information->tt_pixel_id }}');
            @if(!$__ttFromGtm)
            ttq.page();
            @endif
        }(window, document, 'ttq');
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll("i.fal.fa-arrow-up").forEach(function (el) {
                el.classList.remove("fal");
                el.classList.add("fas");
            });
        });
    </script>
</head>