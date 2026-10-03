<!DOCTYPE html>
<html lang="en">
@php
    $info = \App\Models\Information::first();
    $logoUrl = ($info && !empty($info->site_logo))
        ? asset('uploads/img/'.$info->site_logo)
        : asset('backend/img/default-logo.svg'); 
@endphp
<head>
    <meta charset="utf-8" />
    <title>{{ $info->site_name ?? 'Admin' }} Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="Admin Panel" name="description" />
    <meta name="author" content="Coderthemes" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="shortcut icon" href="{{ $logoUrl }}">

    <link href="{{ asset('backend/css/vendor/jquery-jvectormap-1.2.2.css')}}" rel="stylesheet" />
    <link href="{{ asset('backend/css/icons.min.css')}}" rel="stylesheet" />
    <link href="{{ asset('backend/css/app-creative.min.css')}}" rel="stylesheet" id="app-style" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

    <style>
        :root{ 
            --ls-w: 240px;         
            --bg-premium: #0f172a;  
            --bg-footer: #ffffff;   
            --header-h: 70px;       
        } 

        @media print{ .no-print,.no-print *{ display:none !important; } }

        [data-layout-color="light"] .content-page,
        [data-layout-color="light"] .content-page *{ color:#111; }

        @media (min-width: 992px){
            body.with-sidebar{ padding-left: var(--ls-w); }
        }

        .leftside-menu.leftside-menu-detached{
            min-width: var(--ls-w) !important;
            max-width: var(--ls-w) !important;
            background-color: var(--bg-premium) !important;
            padding-top: 0 !important;
        }

        .navbar-custom {
            background-color: var(--bg-premium) !important;
            height: var(--header-h) !important;
            min-height: var(--header-h) !important;
            padding: 0 !important;
            display: flex !important;
            justify-content: space-between !important; 
            align-items: center !important;
            position: fixed !important; 
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999; 
        }

        .wrapper {
            padding-top: var(--header-h) !important;
        }
        
        .topnav-logo {
            width: var(--ls-w); 
            height: var(--header-h);
            background-color: var(--bg-premium);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            flex-shrink: 0; 
            border-right: 1px solid rgba(255,255,255,0.05);
        }

        .brand-logo {
            max-height: 40px; 
            max-width: 80%;
            object-fit: contain;
        }
        
        .navbar-right-content {
            display: flex;
            align-items: center;
            flex-grow: 1;
            padding-left: 15px; 
            padding-right: 20px;
        }

        .button-menu-mobile {
            color: #fff;
            cursor: pointer;
            margin-right: auto; 
            display: flex;
            align-items: center;
            width: auto;
            border: none;
            background: transparent;
        }

        .navbar-custom .nav-link, .navbar-custom .noti-icon { color: #94a3b8 !important; }
        .navbar-custom .nav-link:hover, .navbar-custom .noti-icon:hover { color: #ffffff !important; }

        @media (max-width: 991.98px){
            .topnav-logo {
                display: none !important; 
            }
            
            .leftside-menu.leftside-menu-detached{
                position: relative;
                width: 100% !important; min-width: 100% !important; max-width: 100% !important;
                border-right: 0; box-shadow: none;
            }
            body.with-sidebar{ padding-left:0 !important; }
        }

        .leftbar-user{ 
            text-align:center; padding: 14px 10px; 
            background-color: var(--bg-premium) !important;
        }
        .leftbar-user-name{ color:#fff; font-weight:600; font-size:15px; margin-top:6px; display:block; }
        .leftbar-user img.rounded-circle{ width:42px; height:42px; object-fit:cover; }
        .content-page .content{ padding-top: 14px; }
        .footer{ border-top: 1px solid rgba(0,0,0,.05); background-color: var(--bg-footer) !important; }
    </style>
    @stack('css')
</head>

<body class="loading with-sidebar" data-layout="detached" data-layout-color="light" data-rightbar-onstart="true">

    <div class="navbar-custom topnav-navbar topnav-navbar-dark">

        <a href="{{ route('admin.dashboard') }}" class="topnav-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" class="brand-logo">
            @else
                <h3 class="text-white mb-0">Admin</h3>
            @endif
        </a>

        <div class="navbar-right-content">
            
            <a class="button-menu-mobile disable-btn" aria-label="Toggle Sidebar">
                <div class="lines"><span></span><span></span><span></span></div>
            </a>

            <ul class="list-unstyled topbar-menu mb-0 d-flex align-items-center ms-auto">
                <li class="dropdown notification-list d-xl-none">
                    <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
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
                    <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" id="topbar-userdrop" href="#" role="button" aria-haspopup="true" aria-expanded="false">
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
        </div>
    </div>
    <div class="container-fluid">
        <div class="wrapper">
            <div class="leftside-menu leftside-menu-detached">
                <div class="leftbar-user">
                    <span class="leftbar-user-name mt-2">{{ auth()->user()->name }}</span>
                </div>
                @include('backend.partials.navbar')
                <div class="clearfix"></div>
            </div>

            <div class="content-page">
                <div class="content">
                    @yield('content')
                </div> 
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                {{ date('Y') }} &copy; BIZ CARE LTD. V-2.5
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-end footer-links d-none d-md-block">All rights reserved.</div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div> 
        </div> 
    </div>

    <div class="end-bar">
        <div class="rightbar-title">
            <a href="javascript:void(0);" class="end-bar-toggle float-end"><i class="dripicons-cross noti-icon"></i></a>
            <h5 class="m-0 text-light">Settings</h5>
        </div>
        <div class="rightbar-content h-100" data-simplebar>
            <div class="p-3">
                <h5 class="mt-3">Color Scheme</h5><hr class="mt-1" />
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="color-scheme-mode" value="light" id="light-mode-check" checked />
                    <label class="form-check-label" for="light-mode-check">Light Mode</label>
                </div>
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="color-scheme-mode" value="dark" id="dark-mode-check" />
                    <label class="form-check-label" for="dark-mode-check">Dark Mode</label>
                </div>
                <h5 class="mt-4">Width</h5><hr class="mt-1" />
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

    <script>
        setInterval(function() {
            fetch('/run-pending-calls');
        }, 60000); 
    </script>
    @include('backend.partials.js')
</body>
</html>