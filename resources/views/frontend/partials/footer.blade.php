@php
    use App\Models\Information;
    use Illuminate\Support\Facades\DB;

    $info = Information::orderBy('id','desc')->first();

    $ownerPhone = $info->owner_phone ?? '';
    $ownerEmail = $info->owner_email ?? '';

    $whatsNumberRaw = $info->whats_num ?? $ownerPhone ?? '';
    $whatsNumber = preg_replace('/[^0-9]/', '', $whatsNumberRaw);

    if (substr($whatsNumber, 0, 2) === '01') {
        $whatsNumber = '88' . $whatsNumber;
    } elseif (substr($whatsNumber, 0, 3) === '880') {
    } elseif (substr($whatsNumber, 0, 2) === '88') {
    } else {
        if (strlen($whatsNumber) === 11 && substr($whatsNumber, 0, 1) === '1') {
            $whatsNumber = '88' . $whatsNumber;
        }
    }
@endphp

<style>
    :root{
        --footer-bg1: {{ $info->footer_bg1 ?? '#0f172a' }};
        --footer-bg2: {{ $info->footer_bg2 ?? '#020617' }};
        --footer-bg3: {{ $info->footer_bg3 ?? '#000000' }};
        --footer-text: {{ $info->footer_text ?? '#e5e7eb' }};
        --footer-hover: {{ $info->footer_link_hover ?? '#38bdf8' }};
        --footer-subtitle: {{ $info->footer_subtitle ?? '#9ca3af' }};
        --footer-grad1: {{ $info->footer_border_grad1 ?? '#22d3ee' }};
        --footer-grad2: {{ $info->footer_border_grad2 ?? '#2563eb' }};
        --pill-bg: {{ $info->footer_pill_bg ?? '#0f172a' }};
        --pill-border: {{ $info->footer_pill_border ?? '#94a3b8' }};
        --pill-hover-bg: {{ $info->footer_pill_hover_bg ?? '#0ea5e9' }};
        --pill-hover-text: {{ $info->footer_pill_hover_text ?? '#0b1120' }};
        --underline: {{ $info->footer_underline ?? '#38bdf8' }};
        --social-border: {{ $info->footer_social_border ?? '#94a3b8' }};
        --social-bg: {{ $info->footer_social_bg ?? '#0f172a' }};
        --social-hover-bg: {{ $info->footer_social_hover_bg ?? '#0ea5e9' }};
        --social-hover-text: {{ $info->footer_social_hover_text ?? '#020617' }};
        --mnav-bg: {{ $info->mnav_bg ?? '#ffffff' }};
        --mnav-border: {{ $info->mnav_border ?? '#e5e7eb' }};
        --mnav-icon: {{ $info->mnav_icon ?? '#1e65b2' }};
        --mnav-home-bg: {{ $info->mnav_home_bg ?? '#00276C' }};
        --mnav-home-border: {{ $info->mnav_home_border ?? '#ffffff' }};
        --mnav-home-icon: {{ $info->mnav_home_icon ?? '#ffffff' }};
        --ease-out: cubic-bezier(.22,.61,.36,1);
        --ease-bounce: cubic-bezier(.34,1.56,.64,1);
    }

    .footer-modern{
        position: relative;
        overflow: hidden;
        padding-top: 32px !important;
        padding-bottom: 18px !important;
        background:
            radial-gradient(900px 500px at 0% 0%, color-mix(in srgb, var(--footer-grad1) 18%, transparent), transparent 60%),
            radial-gradient(800px 600px at 100% 100%, color-mix(in srgb, var(--footer-grad2) 20%, transparent), transparent 60%),
            radial-gradient(circle at top left, var(--footer-bg1) 0%, var(--footer-bg2) 55%, var(--footer-bg3) 100%) !important;
        color: var(--footer-text);
        font-family: 'Hind Siliguri', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        isolation: isolate;
    }

    .footer-modern::before{
        content:""; position:absolute; left:0; right:0; top:0; height:2px;
        background: linear-gradient(90deg, transparent, var(--footer-grad1), var(--footer-grad2), var(--footer-grad1), transparent);
        background-size: 300% 100%;
        animation: borderShimmer 6s linear infinite;
        z-index: 3;
    }
    @keyframes borderShimmer {
        from { background-position: 0% 0%; }
        to   { background-position: 300% 0%; }
    }

    .footer-modern::after{
        content:""; position:absolute;
        width: 360px; height: 360px;
        border-radius: 50%; pointer-events: none;
        top: -100px; right: -100px;
        background: radial-gradient(circle, color-mix(in srgb, var(--footer-grad2) 22%, transparent), transparent 70%);
        filter: blur(36px);
        animation: orbDrift 14s ease-in-out infinite;
        z-index: 0;
    }
    .footer-modern .footer-orb-2{
        position:absolute;
        width: 280px; height: 280px;
        border-radius: 50%; pointer-events: none;
        bottom: -80px; left: -80px;
        background: radial-gradient(circle, color-mix(in srgb, var(--footer-grad1) 24%, transparent), transparent 70%);
        filter: blur(36px);
        animation: orbDrift2 18s ease-in-out infinite;
        z-index: 0;
    }
    @keyframes orbDrift  { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-30px,24px) scale(1.06);} }
    @keyframes orbDrift2 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(40px,-28px) scale(1.05);} }

    .footer-grid-bg{
        position:absolute; inset:0; pointer-events:none; z-index:0;
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0);
        background-size: 24px 24px;
        mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
    }

    .footer-sparkles{
        position:absolute; inset:0; pointer-events:none; z-index:1;
        overflow: hidden;
    }
    .footer-sparkles span{
        position:absolute;
        width: 4px; height: 4px; border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,.85), rgba(255,255,255,0) 70%);
        opacity: 0;
        animation: sparkleFloat 9s linear infinite;
    }
    .footer-sparkles span:nth-child(1){ left: 12%; animation-delay: 0s;   width: 3px; height: 3px; }
    .footer-sparkles span:nth-child(2){ left: 28%; animation-delay: 1.6s; width: 4px; height: 4px; }
    .footer-sparkles span:nth-child(3){ left: 45%; animation-delay: 3.1s; width: 3px; height: 3px; }
    .footer-sparkles span:nth-child(4){ left: 62%; animation-delay: 0.8s; width: 4px; height: 4px; }
    .footer-sparkles span:nth-child(5){ left: 78%; animation-delay: 4.5s; width: 4px; height: 4px; }
    .footer-sparkles span:nth-child(6){ left: 91%; animation-delay: 2.4s; width: 3px; height: 3px; }
    @keyframes sparkleFloat{
        0%   { bottom: -10px; opacity: 0; transform: translateX(0) scale(.6); }
        15%  { opacity: 1; }
        80%  { opacity: .8; }
        100% { bottom: 110%; opacity: 0; transform: translateX(20px) scale(1.2); }
    }

    .footer-modern > .container{ position: relative; z-index: 2; }

    .footer-modern a{
        color: var(--footer-text);
        text-decoration: none;
        transition: color .25s ease, opacity .25s ease, transform .25s ease;
    }
    .footer-modern a:hover{ color: var(--footer-hover); }

    .footer-brand-banner{
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--footer-grad1) 12%, transparent),
            color-mix(in srgb, var(--footer-grad2) 12%, transparent));
        border: 1px solid color-mix(in srgb, var(--footer-grad1) 25%, transparent);
        border-radius: 18px;
        padding: 14px 18px;
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }
    .footer-brand-banner::before{
        content:""; position:absolute; top:0; left:-130%;
        width: 50%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.10), transparent);
        transform: skewX(-20deg);
        animation: bannerSweep 8s ease-in-out infinite;
        animation-delay: 2s;
    }
    @keyframes bannerSweep{
        0%, 70% { left: -130%; }
        100%    { left: 200%; }
    }

    .brand-stats-row{
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
    }
    .brand-stat{
        flex: 1;
        text-align: center;
        position: relative;
        padding: 2px 6px;
        min-width: 0;
    }
    .brand-stat .stat-num{
        display: block;
        font-size: 1.20rem;
        font-weight: 900;
        letter-spacing: -.3px;
        background: linear-gradient(135deg, var(--footer-grad1), var(--footer-grad2));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.1;
    }
    .brand-stat .stat-label{
        display: block;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--footer-subtitle);
        font-weight: 700;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .brand-stat .stat-icon{
        font-size: 16px;
        margin-bottom: 2px;
        color: var(--footer-grad1);
        display: inline-block;
        transition: transform .35s var(--ease-bounce);
    }
    .brand-stat:hover .stat-icon{ transform: rotate(-12deg) scale(1.18); }
    .brand-stat:hover .stat-num{ animation: numPop .4s var(--ease-bounce); }
    @keyframes numPop{
        0%   { transform: scale(1); }
        50%  { transform: scale(1.12); }
        100% { transform: scale(1); }
    }
    .stat-divider{
        flex: 0 0 1px;
        width: 1px; height: 40px;
        background: linear-gradient(180deg, transparent, rgba(255,255,255,.18), transparent);
    }

    @media (max-width: 991.98px){
        .footer-brand-banner{ padding: 12px 12px; border-radius: 16px; margin-bottom: 18px; }
        .brand-stat .stat-num{ font-size: 1.05rem; }
        .brand-stat .stat-label{ font-size: 9.5px; }
        .brand-stat .stat-icon{ font-size: 14px; }
        .stat-divider{ height: 36px; }
    }
    @media (max-width: 575.98px){
        .footer-brand-banner{ padding: 10px 8px; border-radius: 14px; }
        .brand-stats-row{ gap: 4px; }
        .brand-stat{ padding: 2px 2px; }
        .brand-stat .stat-num{ font-size: .9rem; }
        .brand-stat .stat-label{ font-size: 8.5px; letter-spacing: .3px; }
        .brand-stat .stat-icon{ font-size: 13px; margin-bottom: 1px; }
        .stat-divider{ height: 32px; }
    }
    @media (max-width: 380px){
        .brand-stat .stat-num{ font-size: .82rem; }
        .brand-stat .stat-label{ font-size: 8px; }
    }

    .footer-tagline,
    .footer-tagline i{ color: var(--footer-text) !important; opacity: 1 !important; }
    .footer-tagline{
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 999px;
        font-size: 12.5px;
        transition: background .3s ease, border-color .3s ease, transform .35s var(--ease-out);
    }
    .footer-tagline:hover{
        background: rgba(255,255,255,.08);
        border-color: color-mix(in srgb, var(--footer-grad1) 40%, transparent);
        transform: translateY(-2px);
    }
    .footer-tagline i{ color: var(--footer-grad1) !important; }

    .footer-contact-label{
        color: var(--footer-text) !important;
        opacity: 1 !important;
        margin-right: 4px;
        font-weight: 600;
    }

    .footer-top-row{ align-items: flex-start !important; }
    @media (min-width: 768px){
        .footer-col{ display: flex; flex-direction: column; justify-content: flex-start; }
    }

    .footer-logo-link{
        position: relative;
        display: inline-flex;
        align-items: center; justify-content: center;
        padding: 6px 10px;
        border-radius: 14px;
        overflow: hidden;
        background: rgba(255,255,255,.03);
        border: 1px solid rgba(255,255,255,.06);
        transition: transform .35s var(--ease-out), background .3s ease, border-color .3s ease;
    }
    .footer-logo-link:hover{
        transform: translateY(-2px) scale(1.03);
        background: rgba(255,255,255,.06);
        border-color: color-mix(in srgb, var(--footer-grad1) 40%, transparent);
    }
    .footer-logo-link::before{
        content:""; position:absolute; top:0; left:-120%;
        width: 60%; height:100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.25), transparent);
        transform: skewX(-20deg);
        transition: left .9s var(--ease-out);
    }
    .footer-logo-link:hover::before{ left: 140%; }
    .footer-logo-link{ text-decoration: none !important; }
    .footer-logo-img{
        max-height: 46px; width: auto; object-fit: contain;
        filter: drop-shadow(0 4px 10px rgba(0,0,0,.30));
    }
    @media (max-width: 767.98px){ .footer-logo-img{ max-height: 42px; } }

    .footer-title{
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        position: relative;
        display: inline-block;
        padding-bottom: 6px;
        margin-bottom: 10px !important;
    }
    .footer-title::after{
        content:"";
        position: absolute; left: 0; bottom: 0;
        width: 30px; height: 2px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2));
        border-radius: 2px;
        transition: width .4s var(--ease-out);
    }
    .footer-modern .col-md-3:hover .footer-title::after,
    .footer-modern .col-md-4:hover .footer-title::after,
    .footer-modern .col-md-5:hover .footer-title::after{ width: 60px; }

    .text-md-end .footer-title::after{ left: auto; right: 0; }
    @media (max-width: 767.98px){
        .footer-title{ display: inline-block; }
        .footer-title::after{ left: 50%; transform: translateX(-50%); }
    }

    .footer-title-small{
        font-size: .95rem;
        font-weight: 800;
        letter-spacing: .10em;
        text-transform: uppercase;
        opacity: .95;
        position: relative;
        display: inline-block;
        padding-bottom: 5px;
    }
    .footer-title-small::after{
        content:""; position:absolute; left:0; bottom:0;
        width: 24px; height: 2px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2));
        border-radius: 2px;
    }
    @media (max-width: 767.98px){
        .footer-title-small::after{ left:50%; transform: translateX(-50%); }
    }

    .footer-subtitle{ color: var(--footer-subtitle); font-size: 12.5px; margin-bottom: 10px !important; }

    .footer-links{ row-gap: .45rem; position: relative; z-index: 2; }

    .footer-pill-link{
        position: relative;
        font-size: 12.5px;
        padding: .35rem .8rem;
        border-radius: 999px;
        background: color-mix(in srgb, var(--pill-bg) 55%, transparent);
        border: 1px solid color-mix(in srgb, var(--pill-border) 35%, transparent);
        white-space: nowrap;
        box-shadow: 0 3px 10px rgba(0,0,0,.16);
        display: inline-flex; align-items: center; justify-content: center;
        z-index: 5; pointer-events: auto; cursor: pointer;
        overflow: hidden;
        isolation: isolate;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }
    /* plain hover: the pill fills with the footer accent, no gradient or shine sweep */
    .footer-pill-link:hover{
        background: var(--footer-grad1);
        border-color: var(--footer-grad1) !important;
        color: #fff !important;
    }

    .footer-legal-links{ gap: 8px 16px; }
    .footer-link-underline{
        font-size: 12.5px;
        position: relative;
        padding-bottom: 3px;
        padding-right: 2px;
        opacity: .9;
    }
    .footer-link-underline::after{
        content:""; position:absolute; left:0; bottom:0;
        width: 0; height: 2px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2));
        border-radius: 2px;
        transition: width .35s var(--ease-out);
    }
    .footer-link-underline:hover{ opacity: 1; }
    .footer-link-underline:hover::after{ width: 100%; }

    .footer-contact{ font-size: 13px; margin-bottom: 10px !important; }
    .footer-contact li{
        margin-bottom: 6px;
        transition: transform .25s ease;
    }
    .footer-contact li:hover{ transform: translateX(3px); }
    .footer-contact .footer-contact-label i{
        color: var(--footer-grad1);
        margin-right: 4px;
        transition: transform .3s ease;
    }
    .footer-contact li:hover .footer-contact-label i{ transform: scale(1.18); }

    .footer-social-icon{
        position: relative;
        width: 32px; height: 32px;
        border-radius: 999px;
        border: 1px solid color-mix(in srgb, var(--social-border) 45%, transparent);
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
        background: color-mix(in srgb, var(--social-bg) 70%, transparent);
        box-shadow: 0 3px 10px rgba(0,0,0,.18);
        overflow: hidden;
        isolation: isolate;
        transition:
            transform .35s var(--ease-bounce),
            background .3s ease, border-color .3s ease,
            color .3s ease, box-shadow .35s ease;
    }
    .footer-social-icon::before{
        content:""; position:absolute; inset:0; border-radius:999px;
        background: linear-gradient(135deg, var(--footer-grad1), var(--footer-grad2));
        opacity: 0;
        transition: opacity .35s ease;
        z-index: -1;
    }
    .footer-social-icon::after{
        content:""; position:absolute; inset:-3px;
        border-radius: 999px;
        border: 2px solid color-mix(in srgb, var(--footer-grad1) 60%, transparent);
        opacity: 0;
        animation: socialRing 2.8s var(--ease-out) infinite paused;
        z-index: -1;
    }
    .footer-social-icon:hover{
        transform: translateY(-3px) scale(1.10) rotate(-5deg);
        border-color: transparent;
        color: var(--social-hover-text);
        box-shadow: 0 10px 18px rgba(0,0,0,.28);
    }
    .footer-social-icon:hover::before{ opacity: 1; }
    .footer-social-icon:hover::after{
        opacity: 1;
        animation-play-state: running;
    }
    @keyframes socialRing{
        0%   { opacity: .6; transform: scale(.9); }
        80%  { opacity: 0;  transform: scale(1.35); }
        100% { opacity: 0;  transform: scale(1.35); }
    }
    .footer-social-icon i{ transition: transform .35s var(--ease-bounce); }
    .footer-social-icon:hover i{ transform: rotate(10deg) scale(1.15); }

    .footer-divider{
        position: relative;
        height: 1px; width: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
        margin: 18px 0 14px;
    }
    .footer-divider::before{
        content:""; position:absolute; top:50%; left:50%;
        transform: translate(-50%, -50%);
        width: 8px; height: 8px; border-radius: 50%;
        background: linear-gradient(135deg, var(--footer-grad1), var(--footer-grad2));
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--footer-grad2) 25%, transparent);
        animation: dotPulse 2.4s ease-in-out infinite;
    }
    @keyframes dotPulse{
        0%, 100% { transform: translate(-50%,-50%) scale(1); }
        50%      { transform: translate(-50%,-50%) scale(1.4); }
    }

    .footer-copy{ font-size: 12px; opacity: .9; }
    .footer-copy a{ text-decoration: none; position: relative; }
    .footer-copy a::after{
        content:""; position: absolute;
        left: 0; right: 0; bottom: -2px;
        height: 1.5px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2));
        transform: scaleX(0); transform-origin: left;
        transition: transform .35s ease;
    }
    .footer-copy a:hover::after{ transform: scaleX(1); }

    .premium-payment-box {
        display: inline-block;
        background: #ffffff;
        padding: 7px 14px;
        border-radius: 12px;
        box-shadow:
            0 10px 24px rgba(0, 0, 0, 0.28),
            0 0 0 1px rgba(255,255,255,.06);
        margin-top: 14px;
        position: relative;
        overflow: hidden;
        isolation: isolate;
        max-width: 95%;
        transition: transform .35s var(--ease-out), box-shadow .35s ease;
    }
    .premium-payment-box::before{
        content:""; position:absolute; inset:0;
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--footer-grad1) 25%, transparent),
            color-mix(in srgb, var(--footer-grad2) 25%, transparent));
        opacity: 0;
        transition: opacity .35s ease;
        z-index: -1;
        border-radius: 12px;
    }
    .premium-payment-box::after{
        content:""; position: absolute; top:0; left:-130%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, transparent, color-mix(in srgb, var(--footer-grad1) 20%, transparent), transparent);
        transform: skewX(-20deg);
        transition: left 1s var(--ease-out);
    }
    .premium-payment-box:hover{
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 16px 30px rgba(0,0,0,.38);
    }
    .premium-payment-box:hover::after{ left: 130%; }
    .premium-payment-box img {
        display: block;
        max-width: 100%; height: auto;
        max-height: 40px;
        position: relative;
        z-index: 1;
    }

    .footer-reveal{
        opacity: 0;
        transform: translateY(20px);
        transition: opacity .7s var(--ease-out), transform .7s var(--ease-out);
    }
    .footer-reveal.in-view{ opacity: 1; transform: translateY(0); }
    .footer-reveal.delay-1{ transition-delay: .05s; }
    .footer-reveal.delay-2{ transition-delay: .12s; }
    .footer-reveal.delay-3{ transition-delay: .19s; }
    .footer-reveal.delay-4{ transition-delay: .26s; }
    .footer-reveal.delay-5{ transition-delay: .33s; }
    .footer-reveal.delay-6{ transition-delay: .40s; }

    .brand-stat{
        opacity: 0; transform: translateY(10px);
        transition: opacity .55s ease, transform .55s var(--ease-out);
    }
    .footer-reveal.in-view .brand-stat{ opacity: 1; transform: translateY(0); }
    .footer-reveal.in-view .brand-stat:nth-child(1){ transition-delay: .08s; }
    .footer-reveal.in-view .brand-stat:nth-child(3){ transition-delay: .20s; }
    .footer-reveal.in-view .brand-stat:nth-child(5){ transition-delay: .32s; }

    .footer-links .footer-pill-link{
        opacity: 0; transform: translateY(8px);
        transition: opacity .5s ease, transform .5s var(--ease-out);
    }
    .footer-reveal.in-view .footer-links .footer-pill-link{
        opacity: 1; transform: translateY(0);
    }
    .footer-reveal.in-view .footer-links .footer-pill-link:nth-child(1){ transition-delay: .12s; }
    .footer-reveal.in-view .footer-links .footer-pill-link:nth-child(2){ transition-delay: .18s; }
    .footer-reveal.in-view .footer-links .footer-pill-link:nth-child(3){ transition-delay: .24s; }
    .footer-reveal.in-view .footer-links .footer-pill-link:nth-child(4){ transition-delay: .30s; }
    .footer-reveal.in-view .footer-links .footer-pill-link:nth-child(5){ transition-delay: .36s; }
    .footer-reveal.in-view .footer-links .footer-pill-link:nth-child(6){ transition-delay: .42s; }

    .footer-social .footer-social-icon{
        opacity: 0; transform: translateY(8px) scale(.9);
        transition: opacity .5s ease, transform .5s var(--ease-bounce);
    }
    .footer-reveal.in-view .footer-social .footer-social-icon{
        opacity: 1; transform: translateY(0) scale(1);
    }
    .footer-reveal.in-view .footer-social .footer-social-icon:nth-child(1){ transition-delay: .16s; }
    .footer-reveal.in-view .footer-social .footer-social-icon:nth-child(2){ transition-delay: .22s; }
    .footer-reveal.in-view .footer-social .footer-social-icon:nth-child(3){ transition-delay: .28s; }
    .footer-reveal.in-view .footer-social .footer-social-icon:nth-child(4){ transition-delay: .34s; }
    .footer-reveal.in-view .footer-social .footer-social-icon:nth-child(5){ transition-delay: .40s; }

    .footer-modern .row.footer-top-row{ row-gap: 16px !important; }

    @media (max-width: 991.98px){
        .footer-modern{ padding-top: 24px !important; padding-bottom: 14px !important; }
        .footer-title{ font-size: .95rem; margin-bottom: 8px !important; }
        .footer-subtitle{ font-size: 12px; }
        .footer-pill-link{ font-size: 12px; padding: .32rem .72rem; }
    }

    @media (max-width: 767.98px){
        .footer-modern{
            text-align: center;
            padding-top: 20px !important;
            padding-bottom: 12px !important;
        }
        .footer-modern .footer-title,
        .footer-modern .footer-title-small,
        .footer-modern .footer-copy{ text-align: center; }
        .footer-logo-link{ padding: 5px 8px; }
        .footer-divider{ margin: 14px 0 12px; }
        .footer-legal-links{ gap: 6px 14px; }
        .footer-contact{ font-size: 12.5px; }
        .footer-tagline{ font-size: 12px; padding: 5px 11px; }
        .premium-payment-box{ padding: 6px 12px; margin-top: 10px; }
        .premium-payment-box img{ max-height: 34px; }
    }

    @media (max-width: 575.98px){
        .footer-modern{ padding-top: 18px !important; padding-bottom: 10px !important; }
        .footer-modern .row.footer-top-row{ row-gap: 12px !important; }
        .footer-brand-banner{ margin-bottom: 14px; }
        .footer-divider{ margin: 12px 0 10px; }
        .footer-title{ font-size: .9rem; padding-bottom: 5px; margin-bottom: 6px !important; }
        .footer-subtitle{ font-size: 11.5px; margin-bottom: 8px !important; }
        .footer-pill-link{ font-size: 11.5px; padding: .3rem .68rem; }
        .footer-social-icon{ width: 30px; height: 30px; font-size: 12px; }
        .footer-link-underline{ font-size: 12px; }
        .footer-copy{ font-size: 11.5px; }
    }

    .footer-nav{
        position: fixed;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 20px);
        max-width: 400px;
        background: var(--mnav-bg);
        border: 1px solid color-mix(in srgb, var(--mnav-border) 60%, transparent);
        border-radius: 20px;
        box-shadow:
            0 12px 26px rgba(15,23,42,.16),
            0 4px 10px rgba(15,23,42,.08),
            inset 0 1px 0 rgba(255,255,255,.6);
        z-index: 99999;
        display: none;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        overflow: hidden;
        isolation: isolate;
        animation: navFloatIn .6s var(--ease-bounce) both;
    }
    @keyframes navFloatIn{
        from { opacity: 0; transform: translateX(-50%) translateY(40px) scale(.9); }
        to   { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
    }

    .footer-nav::before{
        content:"";
        position: absolute;
        top: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, transparent, var(--footer-grad1), var(--footer-grad2), var(--footer-grad1), transparent);
        background-size: 300% 100%;
        animation: borderShimmer 6s linear infinite;
        z-index: 5;
    }

    .nav-slider{
        position: absolute;
        bottom: 3px;
        height: 3px; width: 22px;
        background: linear-gradient(90deg, var(--footer-grad1), var(--footer-grad2));
        border-radius: 3px;
        box-shadow: 0 0 12px color-mix(in srgb, var(--footer-grad1) 60%, transparent);
        transition: left .45s var(--ease-bounce), width .45s var(--ease-bounce);
        z-index: 2;
        pointer-events: none;
    }

    .m-nav-main{
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 8px 10px 10px;
        gap: 6px;
        position: relative;
    }

    .button-shop{
        flex: 1;
        text-align: center;
        display: flex;
        justify-content: center;
        position: relative;
    }

    .footerBtn{
        position: relative;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 2px;
        text-decoration: none;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
        padding: 3px 8px;
        border-radius: 12px;
        min-width: 54px;
        transition:
            transform .3s var(--ease-bounce),
            background .3s ease;
    }

    .footerBtn::before{
        content:""; position: absolute; inset: 0;
        border-radius: 12px;
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--footer-grad1) 14%, transparent),
            color-mix(in srgb, var(--footer-grad2) 14%, transparent));
        opacity: 0;
        transition: opacity .35s ease;
        z-index: -1;
    }
    .footerBtn:hover::before,
    .footerBtn.active-nav::before{ opacity: 1; }

    .footerBtn .icon-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform .35s var(--ease-bounce);
    }
    .footerBtn i{
        font-size: 17px;
        color: var(--mnav-icon);
        line-height: 1;
        transition: color .3s ease;
    }
    .footerBtn span{
        font-size: 10.5px;
        font-weight: 700;
        color: var(--mnav-icon);
        line-height: 1.1;
        text-transform: uppercase;
        letter-spacing: .3px;
        transition: color .3s ease, transform .3s ease;
    }

    .footerBtn:hover,
    .footerBtn.active-nav{ transform: translateY(-1px); }

    .footerBtn:hover .icon-wrap,
    .footerBtn.active-nav .icon-wrap{
        transform: translateY(-2px) scale(1.12);
    }
    .footerBtn:hover i,
    .footerBtn.active-nav i{ color: var(--footer-grad2); }

    .footerBtn:hover span,
    .footerBtn.active-nav span{ color: var(--footer-grad2); }

    .footerBtn:active{ transform: scale(.95); }

    .footerBtn.active-nav::after{
        content:"";
        position: absolute;
        top: 2px; left: 50%;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: radial-gradient(circle, color-mix(in srgb, var(--footer-grad1) 30%, transparent), transparent 70%);
        transform: translateX(-50%);
        animation: navActivePulse 2.4s ease-in-out infinite;
        z-index: -1;
    }
    @keyframes navActivePulse{
        0%, 100% { opacity: .5; transform: translateX(-50%) scale(1); }
        50%      { opacity: 1;  transform: translateX(-50%) scale(1.15); }
    }

    .footer-cart-badge {
        position: absolute;
        top: -6px;
        right: -8px;
        background: #fff;
        color: #ffffff;
        font-size: 9px;
        font-weight: 800;
        width: 15px;
        height: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        line-height: 1;
        z-index: 10;
        pointer-events: none;
    }

    @media (max-width: 575.98px){
        .footer-nav{ display: block; }
        body{ padding-bottom: 74px; }
    }

    @media (prefers-reduced-motion: reduce){
        *, *::before, *::after {
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
        }
    }
</style>

<footer class="footer-modern text-light">
    <div class="footer-orb-2"></div>
    <div class="footer-grid-bg"></div>
    <div class="footer-sparkles">
        <span></span><span></span><span></span>
        <span></span><span></span><span></span>
    </div>

    <div class="container">

        <div class="footer-brand-banner footer-reveal delay-1">
            <div class="brand-stats-row">
                <div class="brand-stat">
                    <i class="fas fa-shipping-fast stat-icon"></i>
                    <span class="stat-num">100%</span>
                    <span class="stat-label">Fast Delivery</span>
                </div>

                <div class="stat-divider"></div>

                <div class="brand-stat">
                    <i class="fas fa-shield-alt stat-icon"></i>
                    <span class="stat-num">Secure</span>
                    <span class="stat-label">Safe Payment</span>
                </div>

                <div class="stat-divider"></div>

                <div class="brand-stat">
                    <i class="fas fa-headset stat-icon"></i>
                    <span class="stat-num">24/7</span>
                    <span class="stat-label">Live Support</span>
                </div>
            </div>
        </div>

        <div class="row footer-top-row">

            <div class="col-md-3 text-center text-md-start footer-reveal delay-2">
                <a href="{{ route('front.home') }}" class="footer-logo-link">
                    <img
                        src="{{ asset('uploads/img/'.(!empty($info->footer_logo) ? $info->footer_logo : ($info->site_logo ?? ''))) }}"
                        alt="Logo"
                        class="footer-logo-img img-fluid"
                    >
                </a>
                @if(!empty($info->address))
                    <div class="mt-2">
                        <span class="footer-tagline">
                            <i class="fa fa-map-marker-alt"></i> {{ $info->address }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="col-md-5 footer-col footer-reveal delay-3">
                <h5 class="footer-title text-center text-md-start">Popular Categories</h5>
                <p class="footer-subtitle text-center text-md-start">Top picks from our best-selling sections</p>

                <nav class="footer-links d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                    @foreach(DB::table('categories')->where('is_popular', 1)->take(6)->get() as $cat)
                        <a href="{{ route('front.category',[$cat->url])}}" class="footer-pill-link">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="col-md-4 text-center text-md-end footer-col footer-reveal delay-4">
                <h5 class="footer-title">Contact & Social</h5>
                <p class="footer-subtitle text-center text-md-end">Need help? We’re just one call away.</p>

                <ul class="list-unstyled footer-contact">
                    @if(!empty($ownerPhone))
                        <li>
                            <span class="footer-contact-label"><i class="fa fa-phone"></i> Call:</span>
                            <a href="tel:{{ $ownerPhone }}">{{ $ownerPhone }}</a>
                        </li>
                    @endif
                    @if(!empty($ownerEmail))
                        <li>
                            <span class="footer-contact-label"><i class="fa fa-envelope"></i> Email:</span>
                            <a href="mailto:{{ $ownerEmail }}">{{ $ownerEmail }}</a>
                        </li>
                    @endif
                </ul>

                <div class="footer-social d-inline-flex flex-wrap justify-content-center justify-content-md-end gap-2">
                    @if(!empty($info->facebook))
                        <a href="{{ $info->facebook }}" target="_blank" class="footer-social-icon"><i class="fab fa-facebook-f"></i></a>
                    @endif
                    @if(!empty($info->youtube))
                        <a href="{{ $info->youtube }}" target="_blank" class="footer-social-icon"><i class="fab fa-youtube"></i></a>
                    @endif
                    @if(!empty($info->instagram))
                        <a href="{{ $info->instagram }}" target="_blank" class="footer-social-icon"><i class="fab fa-instagram"></i></a>
                    @endif
                    @if(!empty($info->tiktok))
                        <a href="{{ $info->tiktok }}" target="_blank" class="footer-social-icon"><i class="fab fa-tiktok"></i></a>
                    @endif
                    @if(!empty($info->twitter))
                        <a href="{{ $info->twitter }}" target="_blank" class="footer-social-icon"><span style="font-family: system-ui;">𝕏</span></a>
                    @endif
                </div>
            </div>
        </div>

        <div class="footer-divider footer-reveal delay-5"></div>

        <div class="row footer-reveal delay-5">
            <div class="col-md-8 mb-2 mb-md-0">
                <h6 class="footer-title-small text-center text-md-start mb-2">Legal Pages</h6>

                <nav class="footer-links footer-legal-links d-flex flex-wrap justify-content-center justify-content-md-start">
                    @foreach(DB::table('pages')->take(6)->get() as $page)
                        <a href="{{ route('front.page.name', $page->page)}}" class="footer-link-underline">
                            {{ $page->title }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="col-md-4 text-center text-md-end">
                <small class="footer-copy d-block mt-2 mt-md-0">
                    {!! $info->copyright ?? '' !!}
                </small>
            </div>
        </div>

        <div class="row footer-reveal delay-6">
            <div class="col-12 text-center">
                <div class="premium-payment-box">
                    <img
                        src="{{ asset('frontend/images/ssl.png') }}"
                        alt="We Accept Secure Payment"
                        class="img-fluid"
                    >
                </div>
            </div>
        </div>

    </div>
</footer>

<div class="footer-nav d-sm-block d-md-none" id="footerNav">
    <span class="nav-slider" id="navSlider"></span>
    <div class="m-nav-main">

        <div class="button-shop">
            <a href="{{ !empty($ownerPhone) ? 'tel:'.$ownerPhone : '#' }}" class="footerBtn" data-nav="call">
                <div class="icon-wrap">
                    <i class="fa fa-phone-volume"></i>
                </div>
                <span>Call</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="{{ route('front.home') }}" class="footerBtn active-nav" data-nav="home" aria-label="Home">
                <div class="icon-wrap">
                    <i class="fa fa-home"></i>
                </div>
                <span>Home</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="{{ route('front.products.index') }}" class="footerBtn" data-nav="shop">
                <div class="icon-wrap">
                    <i class="fa fa-store"></i>
                </div>
                <span>Shop</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="{{ route('front.carts.index')}}?segment={{request()->segment(1)}}" class="footerBtn cart-dropdown-btn" data-nav="cart">
                <div class="icon-wrap">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="footer-cart-badge cart-count">{{ getTotalCart() }}</span>
                </div>
                <span>Cart</span>
            </a>
        </div>

    </div>
</div>

<script>
(function(){
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.10, rootMargin: '0px 0px -40px 0px' });
        document.querySelectorAll('.footer-reveal').forEach(el => io.observe(el));
    } else {
        document.querySelectorAll('.footer-reveal').forEach(el => el.classList.add('in-view'));
    }

    function positionSlider(){
        const slider = document.getElementById('navSlider');
        const active = document.querySelector('#footerNav .footerBtn.active-nav');
        if(!slider || !active) return;
        const nav = document.getElementById('footerNav');
        const btnRect = active.getBoundingClientRect();
        const navRect = nav.getBoundingClientRect();
        const left = (btnRect.left - navRect.left) + (btnRect.width / 2) - 11;
        slider.style.left = left + 'px';
        slider.style.width = '22px';
    }

    function bindNavButtons(){
        const buttons = document.querySelectorAll('#footerNav .footerBtn');
        buttons.forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                buttons.forEach(b => b.classList.remove('active-nav'));
                btn.classList.add('active-nav');
                positionSlider();
            });
            btn.addEventListener('touchstart', () => {
                buttons.forEach(b => b.classList.remove('active-nav'));
                btn.classList.add('active-nav');
                positionSlider();
            }, { passive: true });
        });
    }

    setTimeout(() => {
        bindNavButtons();
        positionSlider();
    }, 80);

    window.addEventListener('resize', positionSlider);
    window.addEventListener('orientationchange', positionSlider);
})();
</script>

<script>
    $(document).ready(function() {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": false,
                "progressBar": false,
                "timeOut": "1000",
                "extendedTimeOut": "300",
                "showDuration": "200",
                "hideDuration": "200"
            };
        }

        $(document).on('click', '.remove-cart, .cart-delete-btn, .close-item, .delete-cart-item, .remove-item, .btn-remove, .remove', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            let btn = $(this);
            let url = btn.attr('href') || btn.data('url');
            
            if(!url || url === '#') return;

            let originalHtml = btn.html();

            btn.html('<i class="fas fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    if (res && typeof res === 'object') {
                        if (res.view || res.html) {
                            $('#cart-dropdown, .cart-content-wrap').html(res.view || res.html);
                        } else {
                            btn.closest('li, .cart-item, .item, tr').fadeOut(300, function() { $(this).remove(); });
                        }

                        if (res.item !== undefined) $('.cart-count, .cart-item-count').text(res.item);
                        if (res.amount) $('.cart-amount').text('৳ ' + res.amount);
                        
                        if (window.toastr) toastr.success(res.msg || 'Item removed');
                    } else {
                        btn.closest('li, .cart-item, .item, tr').fadeOut(300, function() { $(this).remove(); });
                        if (window.toastr) toastr.success('Item removed');
                    }
                },
                error: function(err) {
                    console.error("Cart Remove Error:", err);
                    btn.closest('li, .cart-item, .item, tr').fadeOut(300, function() { $(this).remove(); });
                    if (window.toastr) toastr.error('Item removed!');
                }
            });
        });
    });
</script>

<div class="cart-dropdown" id="cart-dropdown">
    {{-- Render the sidebar server-side so it is never empty on a fresh page load. --}}
    @include('frontend.partials.cart_sidebar', [
        'cart' => session()->get('cart', []),
        'segm' => request()->segment(1) ?? 'home',
    ])
</div>
@include('frontend.partials.js')