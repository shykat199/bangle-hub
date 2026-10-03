<!DOCTYPE html>
<html lang="en">
@php
    /** Load site info once */
    $info = \App\Models\Information::first();

    /** Build logo URL with safe fallback */
    $logoUrl = ($info && !empty($info->site_logo))
        ? asset('uploads/img/'.$info->site_logo)
        : asset('backend/img/default-logo.svg'); // fallback
@endphp
<head>
    <meta charset="utf-8" />
    <title>{{ $info->site_name ?? 'Admin' }} Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="Admin Panel" name="description" />
    <meta name="author" content="Coderthemes" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ $logoUrl }}">

    <!-- Vendor / Theme CSS -->
    <link href="{{ asset('backend/css/vendor/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet" />
    <link href="{{ asset('backend/css/icons.min.css') }}" rel="stylesheet" />

    {{-- ✅ MUST: Unicons (icons.min.css এর পরে) --}}
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

    {{-- ✅ FORCE: uil class যেন Unicons font ই নেয় --}}
    <style>
        .uil{
            font-family: "unicons" !important;
            font-style: normal !important;
            font-weight: 400 !important;
            speak: none;
            display: inline-block;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>

    <link href="{{ asset('backend/css/app-creative.min.css') }}" rel="stylesheet" id="app-style" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

    <!-- Layout overrides -->
    <style>
        :root{ --ls-w:220px; } /* Desktop sidebar width */

        @media print{ .no-print,.no-print *{ display:none !important; } }

        [data-layout-color="light"] .content-page,
        [data-layout-color="light"] .content-page *{ color:#111; }

        @media (min-width: 992px){
            body.with-sidebar{ padding-left: var(--ls-w); }
        }

        .leftside-menu.leftside-menu-detached{
            min-width: var(--ls-w) !important;
            max-width: var(--ls-w) !important;
        }

        @media (max-width: 991.98px){
            .leftside-menu.leftside-menu-detached{
                position: relative;
                width: 100% !important; min-width: 100% !important; max-width: 100% !important;
                border-right: 0; box-shadow: none;
            }
            body.with-sidebar{ padding-left:0 !important; }
        }

        .topnav-logo{ display: none !important; }

        .leftbar-user{ text-align:center; padding: 14px 10px; }
        .leftbar-user a{ display:block; text-decoration:none; }
        .sidebar-logo{
            height: 64px; width:auto; display:block; margin: 50px auto 8px;
            object-fit:contain;
        }
        .leftbar-user-name{
            color:#fff; font-weight:600; font-size:15px; margin-top:6px;
            display:block;
        }

        .leftbar-user img.rounded-circle{ width:42px; height:42px; object-fit:cover; }

        .content-page .content{ padding-top: 14px; }

        .footer{ border-top: 1px solid rgba(0,0,0,.05); }

        /* ✅ arrow/box remove */
        .leftside-menu .side-nav-link.has-arrow::after,
        .leftside-menu .side-nav-link.has-arrow:after,
        .leftside-menu a.side-nav-link.has-arrow::after,
        .leftside-menu button.side-nav-link.has-arrow::after,
        .leftside-menu .side-nav-link[data-bs-toggle="collapse"]::after,
        .leftside-menu .side-nav-link[data-bs-toggle="collapse"]:after,
        .leftside-menu .side-nav-item > a.has-arrow::after,
        .leftside-menu .side-nav-item > button.has-arrow::after{
            content: none !important;
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            -webkit-mask: none !important;
            mask: none !important;
        }

        .leftside-menu .side-nav-link.has-arrow{
            background-image: none !important;
        }

        .leftside-menu button.side-nav-link,
        .leftside-menu button.side-nav-link:focus,
        .leftside-menu button.side-nav-link:active{
            outline: none !important;
            box-shadow: none !important;
            border: 0 !important;
            appearance: none !important;
            -webkit-appearance: none !important;
        }
    </style>

    {{-- ✅ Per-page CSS --}}
    @stack('css')
</head>

<body class="loading with-sidebar"
      data-layout="detached"
      data-layout-color="light"
      data-rightbar-onstart="true">

    <!-- Topbar Start -->
    <div class="navbar-custom topnav-navbar topnav-navbar-dark">
        <div class="container-fluid">

            <ul class="list-unstyled topbar-menu float-end mb-0">
                <li class="dropdown notification-list d-xl-none">
                    <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                        <i class="dripicons-search noti-icon"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-animated dropdown-lg p-0">
                        <form class="p-3"><input type="text" class="form-control" placeholder="Search ..." aria-label="Search"></form>
                    </div>
                </li>

                <li class="notification-list">
                    <a class="nav-link" href="{{ route('front.home') }}" target="_blank" aria-label="View Site">
                        <i class="dripicons-home noti-icon"></i>
                    </a>
                </li>

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" id="topbar-userdrop" href="#" role="button">
                        <span class="account-user-avatar">
                            <img src="{{ getImage('uploads/img', Auth::user()->image) }}" alt="user-image" class="rounded-circle">
                        </span>
                        <span><span class="account-user-name">{{ auth()->user()->first_name }}</span></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown" aria-labelledby="topbar-userdrop">
                        <div class="dropdown-header noti-title"><h6 class="m-0">Welcome !</h6></div>

                        <a href="{{ route('admin.profile') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-account-circle me-1"></i><span>My Account</span>
                        </a>
                        <a href="{{ route('admin.password') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-shield-lock me-1"></i><span>Change Password</span>
                        </a>

                        @can('product.delete')
                        <a href="{{ route('admin.settings.index') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-cog me-1"></i><span>Settings</span>
                        </a>
                        @endcan

                        <a class="dropdown-item notify-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout me-1"></i><span>Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </div>
                </li>
            </ul>

            <a class="button-menu-mobile disable-btn" aria-label="Toggle Sidebar">
                <div class="lines"><span></span><span></span><span></span></div>
            </a>
        </div>
    </div>
    <!-- end Topbar -->

    <!-- Start Content-->
    <div class="container-fluid">
        <div class="wrapper">

            <!-- ========== Left Sidebar Start ========== -->
            <div class="leftside-menu leftside-menu-detached">
                <div class="leftbar-user">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ $logoUrl }}" class="sidebar-logo" alt="{{ $info->site_name ?? 'Admin' }} Logo">
                    </a>
                    <span class="leftbar-user-name">{{ auth()->user()->name }}</span>
                </div>

                @include('backend.partials.navbar')

                <div class="clearfix"></div>
            </div>
            <!-- Left Sidebar End -->

            <div class="content-page">
                <div class="content">

                    {{-- ✅ Page Content --}}
                    @yield('content')

                </div>

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6"></div>
                        </div>
                    </div>
                </footer>
            </div>

        </div>
    </div>
    <!-- END Container -->

    <div class="end-bar">
        <div class="rightbar-title">
            <a href="javascript:void(0);" class="end-bar-toggle float-end">
                <i class="dripicons-cross noti-icon"></i>
            </a>
            <h5 class="m-0 text-light">Settings</h5>
        </div>

        <div class="rightbar-content h-100" data-simplebar>
            <div class="p-3">
                <h5 class="mt-3">Color Scheme</h5>
                <hr class="mt-1" />
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="color-scheme-mode" value="light" id="light-mode-check" checked />
                    <label class="form-check-label" for="light-mode-check">Light Mode</label>
                </div>
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="color-scheme-mode" value="dark" id="dark-mode-check" />
                    <label class="form-check-label" for="dark-mode-check">Dark Mode</label>
                </div>

                <h5 class="mt-4">Width</h5>
                <hr class="mt-1" />
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="width" value="fluid" id="fluid-check" checked />
                    <label class="form-check-label" for="fluid-check">Fluid</label>
                </div>
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="width" value="boxed" id="boxed-check" />
                    <label class="form-check-label" for="boxed-check">Boxed</label>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="common_modal" tabindex="-1" aria-hidden="true"></div>
    <div class="rightbar-overlay"></div>

    {{-- ✅ Base JS --}}
    @include('backend.partials.js')

    {{-- ✅ Per-page JS (important) --}}
    @stack('js')
</body>
</html>
