@php
    use App\Models\Information;
    use App\Models\AdminText;

    $info_nav = Information::orderBy('id','desc')->first();
    $invoiceType = (int)($info_nav->invoice_type ?? 1);

    if (!function_exists('adm_text')) {
        function adm_text($key, $fallback='') {
            try{
                $t = \App\Models\AdminText::first();
                if($t && isset($t->$key) && $t->$key) return $t->$key;
            }catch(\Throwable $e){}
            return $fallback;
        }
    }

    if (!function_exists('order_status_icon')) {
        function order_status_icon($label){
            $label = strtolower(trim((string)$label));
            
            if (str_contains($label, 'courier complete')) return 'mdi mdi-check-all';
            if (str_contains($label, 'printing pending')) return 'mdi mdi-printer-pos'; 
            if (str_contains($label, 'confirmed'))        return 'mdi mdi-check-decagram'; 
            
            if (str_contains($label, 'courier'))          return 'mdi mdi-truck-delivery';
            if (str_contains($label, 'all'))              return 'mdi mdi-format-list-bulleted';
            if (str_contains($label, 'pending'))          return 'mdi mdi-clock-outline';
            if (str_contains($label, 'processing'))       return 'mdi mdi-cached';
            if (str_contains($label, 'on hold'))          return 'mdi mdi-pause-circle-outline';
            if (str_contains($label, 'incomplete'))       return 'mdi mdi-alert-circle-outline';
            if (str_contains($label, 'complete'))         return 'mdi mdi-check-circle-outline';
            if (str_contains($label, 'cancel'))           return 'mdi mdi-close-circle-outline';
            if (str_contains($label, 'return') || str_contains($label, 'returned') || str_contains($label, 'refund')) {
                return 'mdi mdi-keyboard-backspace';
            }
            
            return 'mdi mdi-package-variant';
        }
    }

    $isWorker = auth()->check() && method_exists(auth()->user(), 'hasRole')
        ? auth()->user()->hasRole('worker')
        : false;

    if (!function_exists('nav_active')) {
        function nav_active($patterns = []) {
            foreach ((array)$patterns as $p) {
                if (request()->routeIs($p) || request()->is($p)) return 'active';
            }
            return '';
        }
    }
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

<aside id="appSidebar" class="premium-sidebar">
    
    <div class="sidebar-header"></div>

    <div class="sidebar-content" data-simplebar>
        
        <ul class="sidebar-menu">

            <li class="menu-item">
                <a href="{{ route('admin.dashboard')}}" class="menu-link {{ nav_active(['admin.dashboard']) }}">
                    <i class="menu-icon mdi mdi-view-dashboard-outline"></i>
                    <span class="menu-text">{{ adm_text('dashboard','Dashboard') }}</span>
                </a>
            </li>

            @php $ordersOpen = (request()->is('admin/orders*') || request()->routeIs('admin.scan_return.*')) ? 'show' : ''; @endphp
            @if($isWorker || auth()->user()->can('order.view') || auth()->user()->can('permission.view'))
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $ordersOpen ? 'active' : 'collapsed' }}" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#menuOrders" 
                        aria-expanded="{{ $ordersOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-cart-outline"></i>
                    <span class="menu-text">{{ adm_text('orders_manage','Orders Manage') }}</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>

                <div class="collapse menu-dropdown {{ $ordersOpen }}" id="menuOrders">
                    <ul class="sub-menu">
                        
                        @if($isWorker || auth()->user()->can('order.view'))
                            @foreach(getOrderStatus(1) as $key => $item)
                                @php
                                    $isActive = (request('status') === (string)$key) || (request('status') === null && $key === '' && request()->is('admin/orders'));
                                @endphp
                                <li>
                                    <a href="{{ url('admin/orders?q=&status='.$key)}}" 
                                       class="sub-link {{ $isActive ? 'active' : '' }}">
                                        <i class="{{ order_status_icon($item) }}"></i>
                                        <span>{{ $item == 'All' ? 'All Orders' : $item }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @endif

                        @unless($isWorker)
                        <li>
                            <a href="{{ route('admin.scan_return.index') }}"
                               class="sub-link {{ request()->routeIs('admin.scan_return.*') ? 'active' : '' }}">
                                <i class="mdi mdi-barcode-scan"></i>
                                <span>Scan Return (New)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.productPerformance') }}"
                               class="sub-link {{ request()->routeIs('admin.productPerformance') ? 'active' : '' }}">
                                <i class="mdi mdi-chart-bar"></i>
                                <span>Product Performance</span>
                            </a>
                        </li>
                        @endunless

                    </ul>
                </div>
            </li>
            @endif

            @if(auth()->user()->can('order.view') || auth()->user()->can('order.create'))
            <li class="menu-item">
                <a href="{{ route('admin.cash_sales.index') }}" class="menu-link {{ nav_active(['admin.cash_sales.*']) }}">
                    <i class="menu-icon mdi mdi-store-outline"></i>
                    <span class="menu-text">{{ adm_text('cash_sale','Cash Sale') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->can('product.view') || auth()->user()->can('type.view') || auth()->user()->can('category.view') || auth()->user()->can('discount.view'))
            @php
                $productsOpen = request()->is('admin/types*') || request()->is('admin/categories*') || request()->is('admin/products*') || request()->is('admin/free-shipping*') ? 'show' : '';
            @endphp
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $productsOpen ? '' : 'collapsed' }}" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#menuProducts" 
                        aria-expanded="{{ $productsOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-archive-outline"></i>
                    <span class="menu-text">{{ adm_text('products','Products') }}</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>
                <div class="collapse menu-dropdown {{ $productsOpen }}" id="menuProducts">
                    <ul class="sub-menu">
                        @if(auth()->user()->can('type.view'))
                        <li><a href="{{ route('admin.types.index')}}" class="sub-link {{ nav_active(['admin.types.*']) }}"><i class="mdi mdi-label-outline"></i><span>{{ adm_text('brand_manage','Brand Manage') }}</span></a></li>
                        @endif
                        @if(auth()->user()->can('category.view'))
                        <li><a href="{{ route('admin.categories.index')}}" class="sub-link {{ nav_active(['admin.categories.*']) }}"><i class="mdi mdi-shape-outline"></i><span>{{ adm_text('category_manage','Category Manage') }}</span></a></li>
                        @endif
                        @if(auth()->user()->can('product.view'))
                        <li><a href="{{ route('admin.products.index')}}" class="sub-link {{ nav_active(['admin.products.*']) }}"><i class="mdi mdi-barcode"></i><span>{{ adm_text('products_manage','Products Manage') }}</span></a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            @if(auth()->user()->can('size.view') || auth()->user()->can('color.view'))
            @php $variationOpen = request()->is('admin/sizes*') || request()->is('admin/colors*') ? 'show' : ''; @endphp
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $variationOpen ? '' : 'collapsed' }}" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#menuVariation" 
                        aria-expanded="{{ $variationOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-layers-outline"></i>
                    <span class="menu-text">{{ adm_text('variation','Variation') }}</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>
                <div class="collapse menu-dropdown {{ $variationOpen }}" id="menuVariation">
                    <ul class="sub-menu">
                        @if(auth()->user()->can('size.view'))
                        <li><a href="{{ route('admin.sizes.index')}}" class="sub-link {{ nav_active(['admin.sizes.*']) }}"><i class="mdi mdi-ruler"></i><span>{{ adm_text('size_manage','Size Manage') }}</span></a></li>
                        @endif
                        @if(auth()->user()->can('color.view'))
                        <li><a href="{{ route('admin.colors.index')}}" class="sub-link {{ nav_active(['admin.colors.*']) }}"><i class="mdi mdi-palette-outline"></i><span>{{ adm_text('color_manage','Color Manage') }}</span></a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            @if(auth()->user()->can('product.view'))
            <li class="menu-item">
                <a href="{{ route('admin.stock_warning') }}" class="menu-link {{ nav_active(['admin.stock_warning']) }}">
                    <i class="menu-icon mdi mdi-alert-decagram-outline"></i>
                    <span class="menu-text">{{ adm_text('stock_warning','Stock Warning') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->can('product.view'))
            @php $stockNotifyPending = \App\Models\StockNotification::where('status','pending')->count(); @endphp
            <li class="menu-item">
                <a href="{{ route('admin.stock_notifications.index') }}" class="menu-link {{ nav_active(['admin.stock_notifications.*']) }}">
                    <i class="menu-icon mdi mdi-bell-ring-outline"></i>
                    <span class="menu-text">Notify Requests</span>
                    @if($stockNotifyPending > 0)
                        <span class="badge rounded-pill bg-danger ms-auto" style="font-size:10px;">{{ $stockNotifyPending }}</span>
                    @endif
                </a>
            </li>
            @endif

            <li class="menu-item">
                <a href="{{ route('admin.homecat')}}" class="menu-link {{ nav_active(['admin.homecat']) }}">
                    <i class="menu-icon mdi mdi-home-outline"></i>
                    <span class="menu-text">{{ adm_text('home_category','Home Category') }}</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.free_shipping')}}" class="menu-link {{ nav_active(['admin.free_shipping']) }}">
                    <i class="menu-icon mdi mdi-truck-fast-outline"></i>
                    <span class="menu-text">{{ adm_text('free_shipping','Free Shipping') }}</span>
                </a>
            </li>

            @if(auth()->user()->can('discount.view') || auth()->user()->can('product.view'))
            <li class="menu-item">
                <a href="{{ route('admin.coupon_codes.index') }}" class="menu-link {{ nav_active(['admin.coupon_codes.*']) }}">
                    <i class="menu-icon mdi mdi-ticket-percent-outline"></i>
                    <span class="menu-text">{{ adm_text('coupon_code','Coupon Code') }}</span>
                </a>
            </li>
            @endif

            @unless($isWorker)
            <li class="menu-item">
                <a href="{{ route('admin.reviews.index')}}" class="menu-link {{ nav_active(['admin.reviews.*']) }}">
                    <i class="menu-icon mdi mdi-comment-quote-outline"></i>
                    <span class="menu-text">{{ adm_text('reviews','Reviews') }}</span>
                </a>
            </li>
            @endunless

            <!-- ✅ FRONT PAGE SECTION -->
            @if(auth()->user()->can('page.view') || auth()->user()->can('image.view') || auth()->user()->can('slider.view'))
            @php 
                $frontOpen = request()->is('admin/pages*') || request()->is('admin/sliders*') || request()->is('admin/styles*') || request()->is('admin/dynamic-text*') || request()->is('admin/home-section-images*') ? 'show' : ''; 
            @endphp
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $frontOpen ? '' : 'collapsed' }}" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#menuFront" 
                        aria-expanded="{{ $frontOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-monitor-dashboard"></i>
                    <span class="menu-text">{{ adm_text('front_page','Front Page') }}</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>
                <div class="collapse menu-dropdown {{ $frontOpen }}" id="menuFront">
                    <ul class="sub-menu">
                        @if(auth()->user()->can('page.view'))
                        <li><a href="{{ route('admin.pages.index') }}" class="sub-link {{ nav_active(['admin.pages.*']) }}"><i class="mdi mdi-file-document-edit-outline"></i><span>{{ adm_text('manage_page_data','Manage Page Data') }}</span></a></li>
                        @endif
                        <li><a href="{{ route('admin.styles.index') }}" class="sub-link {{ nav_active(['admin.styles.*']) }}"><i class="mdi mdi-palette-swatch-outline"></i><span>{{ adm_text('style','Style') }}</span></a></li>
                        @if(auth()->user()->can('slider.view'))
                        <li><a href="{{ route('admin.sliders.index') }}" class="sub-link {{ nav_active(['admin.sliders.*']) }}"><i class="mdi mdi-view-carousel-outline"></i><span>{{ adm_text('slider_manage','Slider Manage') }}</span></a></li>
                        @endif
                        
                        <!-- 🔥 NEW: Home Section Images Link -->
                        @if(auth()->user()->can('image.view'))
                        <li>
                            <a href="{{ route('admin.home_section_images.index') }}" class="sub-link {{ nav_active(['admin.home_section_images.*']) }}">
                                <i class="mdi mdi-image-multiple-outline"></i>
                                <span>Home Section Images</span>
                            </a>
                        </li>
                        @endif

                        <li><a href="{{ route('admin.dynamic_text.edit') }}" class="sub-link {{ nav_active(['admin.dynamic_text.*']) }}"><i class="mdi mdi-format-text"></i><span>{{ adm_text('dynamic_text','Dynamic Text') }}</span></a></li>
                    </ul>
                </div>
            </li>
            @endif

            @if(auth()->user()->can('delivery_charge.view'))
            <li class="menu-item">
                <a href="{{ route('admin.delivery_charge.index')}}" class="menu-link {{ nav_active(['admin.delivery_charge.*']) }}">
                    <i class="menu-icon mdi mdi-cash-marker"></i>
                    <span class="menu-text">{{ adm_text('delivery_charge','Delivery Charge') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->can('couriers.view'))
            <li class="menu-item">
                <a href="{{ route('admin.couriers.index')}}" class="menu-link {{ nav_active(['admin.couriers.*']) }}">
                    <i class="menu-icon mdi mdi-truck-check-outline"></i>
                    <span class="menu-text">{{ adm_text('courier_manage','Courier Manage') }}</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->can('combo.view') || auth()->user()->can('permission.view') || auth()->user()->can('role.view'))
            @php $usersOpen = request()->is('admin/users*') ? 'show' : ''; @endphp
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $usersOpen ? '' : 'collapsed' }}" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#menuUsers" 
                        aria-expanded="{{ $usersOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-account-group-outline"></i>
                    <span class="menu-text">{{ adm_text('users','Users') }}</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>
                <div class="collapse menu-dropdown {{ $usersOpen }}" id="menuUsers">
                    <ul class="sub-menu">
                        @if(auth()->user()->can('user.view'))
                        <li><a href="{{ route('admin.users.index')}}" class="sub-link {{ nav_active(['admin.users.*']) }}"><i class="mdi mdi-account-cog-outline"></i><span>{{ adm_text('manage_user','Manage User') }}</span></a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            @php $landingOpen = request()->routeIs('admin.landing_pages*') ? 'show' : ''; @endphp
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $landingOpen ? '' : 'collapsed' }}" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#menuLanding" 
                        aria-expanded="{{ $landingOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-layers-triple-outline"></i>
                    <span class="menu-text">{{ adm_text('landing_page','Landing Page') }}</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>
                <div class="collapse menu-dropdown {{ $landingOpen }}" id="menuLanding">
                    <ul class="sub-menu">
                        <li><a href="{{ route('admin.landing_pages_five') }}" class="sub-link {{ nav_active(['admin.landing_pages_five*']) }}"><i class="mdi mdi-star-face"></i><span>Design 1</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_two') }}" class="sub-link {{ nav_active(['admin.landing_pages_two*']) }}"><i class="mdi mdi-brush-outline"></i><span>Design 2</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_three') }}" class="sub-link {{ nav_active(['admin.landing_pages_three*']) }}"><i class="mdi mdi-diamond-stone"></i><span>Design 3</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_four') }}" class="sub-link {{ nav_active(['admin.landing_pages_four*']) }}"><i class="mdi mdi-weather-night"></i><span>Design 4</span></a></li>
                        
                        <li><a href="{{ route('admin.landing_pages_six') }}" class="sub-link {{ nav_active(['admin.landing_pages_six*']) }}"><i class="mdi mdi-rocket-launch-outline"></i><span>Design 5</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_seven') }}" class="sub-link {{ nav_active(['admin.landing_pages_seven*']) }}"><i class="mdi mdi-layers-search-outline"></i><span>Design 6</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_eight') }}" class="sub-link {{ nav_active(['admin.landing_pages_eight*']) }}"><i class="mdi mdi-video-outline"></i><span>Design 7</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_nine') }}" class="sub-link {{ nav_active(['admin.landing_pages_nine*']) }}"><i class="mdi mdi-fan"></i><span>Design 8</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_ten') }}" class="sub-link {{ nav_active(['admin.landing_pages_ten*']) }}"><i class="mdi mdi-blender"></i><span>Design 9</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_eleven') }}" class="sub-link {{ nav_active(['admin.landing_pages_eleven*']) }}"><i class="mdi mdi-bottle-tonic-skull-outline"></i><span>Design 10</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_twelve') }}" class="sub-link {{ nav_active(['admin.landing_pages_twelve*']) }}"><i class="mdi mdi-beehive-outline"></i><span>Design 11</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_thirteen') }}" class="sub-link {{ nav_active(['admin.landing_pages_thirteen*']) }}"><i class="mdi mdi-tshirt-crew-outline"></i><span>Design 12</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_fourteen') }}" class="sub-link {{ nav_active(['admin.landing_pages_fourteen*']) }}"><i class="mdi mdi-music-note-eighth"></i><span>Design 13</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_fifteen') }}" class="sub-link {{ nav_active(['admin.landing_pages_fifteen*']) }}"><i class="mdi mdi-shopping-outline"></i><span>Design 14</span></a></li>
                        <li><a href="{{ route('admin.landing_pages_sixteen') }}" class="sub-link {{ nav_active(['admin.landing_pages_sixteen*']) }}"><i class="mdi mdi-storefront-outline"></i><span>Design 15</span></a></li>

                        <li><a href="{{ route('admin.landing_pages.color') }}" class="sub-link {{ nav_active(['admin.landing_pages.color']) }}"><i class="mdi mdi-palette-outline"></i><span>Color Settings</span></a></li>
                    </ul>
                </div>
            </li>

            @if(auth()->user()->can('page.view'))
                <li class="menu-item"><a href="{{ url('/facebook-feed') }}" class="menu-link {{ nav_active(['facebook-feed']) }}"><i class="menu-icon mdi mdi-rss"></i><span class="menu-text">Facebook Feed</span></a></li>
            @endif

            <li class="menu-header">Analytics & Settings</li>

            @if(auth()->check() && Route::has('admin.profit_calculator.index'))
            @php $pcOpen = request()->is('admin/profit-calculator*') ? 'show' : ''; @endphp
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $pcOpen ? '' : 'collapsed' }}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#menuProfitCalculator"
                        aria-expanded="{{ $pcOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-calculator-variant-outline text-success"></i>
                    <span class="menu-text">Profit Calculator</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>
                <div class="collapse menu-dropdown {{ $pcOpen }}" id="menuProfitCalculator">
                    <ul class="sub-menu">
                        <li>
                            <a href="{{ route('admin.profit_calculator.index') }}"
                               class="sub-link {{ request()->routeIs('admin.profit_calculator.index') ? 'active' : '' }}">
                                <i class="mdi mdi-calculator"></i>
                                <span>Calculator</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.profit_calculator.history') }}"
                               class="sub-link {{ request()->routeIs('admin.profit_calculator.history') ? 'active' : '' }}">
                                <i class="mdi mdi-history"></i>
                                <span>History</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.profit_calculator.compare') }}"
                               class="sub-link {{ request()->routeIs('admin.profit_calculator.compare') ? 'active' : '' }}">
                                <i class="mdi mdi-source-branch"></i>
                                <span>Compare</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @php $reportsOpen = request()->is('admin/report/*') ? 'show' : ''; @endphp
            @if($isWorker || auth()->user()->can('order.view') || auth()->user()->can('permission.view'))
            <li class="menu-item">
                <button class="menu-link has-dropdown {{ $reportsOpen ? '' : 'collapsed' }}" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#menuReports" 
                        aria-expanded="{{ $reportsOpen ? 'true':'false' }}">
                    <i class="menu-icon mdi mdi-chart-box-outline"></i>
                    <span class="menu-text">{{ adm_text('reports','Reports') }}</span>
                    <i class="mdi mdi-chevron-right menu-arrow"></i>
                </button>
                <div class="collapse menu-dropdown {{ $reportsOpen }}" id="menuReports">
                    <ul class="sub-menu">
                        
                        <li><a href="{{ route('admin.report.sales') }}" class="sub-link {{ nav_active(['admin.report.sales']) }}"><i class="mdi mdi-chart-areaspline"></i><span>{{ adm_text('sales_report','Sales Report') }}</span></a></li>

                        @unless($isWorker)
                        <li><a href="{{ route('admin.report.daily_profit') }}" class="sub-link {{ nav_active(['admin.report.daily_profit']) }}"><i class="mdi mdi-finance"></i><span>Daily Profit Report</span></a></li>
                        <li><a href="{{ route('admin.report.monthly_profit') }}" class="sub-link {{ nav_active(['admin.report.monthly_profit']) }}"><i class="mdi mdi-calendar-month-outline"></i><span>Monthly Profit Report</span></a></li>
                        
                        <li><a href="{{ route('admin.report.product_performance') }}" class="sub-link {{ nav_active(['admin.report.product_performance']) }}"><i class="mdi mdi-shopping-search"></i><span>Product Performance</span></a></li>
                        
                        <li><a href="{{ route('admin.report.courier_performance') }}" class="sub-link {{ nav_active(['admin.report.courier_performance']) }}"><i class="mdi mdi-truck-fast"></i><span>Courier Performance</span></a></li>
                        <li><a href="{{ route('admin.report.courier_tracking') }}" class="sub-link {{ nav_active(['admin.report.courier_tracking']) }}"><i class="mdi mdi-barcode-scan"></i><span>Courier Tracking &amp; Payment</span></a></li>
                        @endunless

                        @if($isWorker)
                            <li><a href="{{ route('admin.report.user')}}" class="sub-link {{ nav_active(['admin.report.user']) }}"><i class="mdi mdi-account-star-outline"></i><span>{{ adm_text('user_report','User Report') }}</span></a></li>
                        @else
                            @if(auth()->user()->can('user.view'))
                            <li><a href="{{ route('admin.report.order')}}" class="sub-link {{ nav_active(['admin.report.order']) }}"><i class="mdi mdi-clipboard-text-search-outline"></i><span>{{ adm_text('order_report','Order Report') }}</span></a></li>
                            <li><a href="{{ route('admin.report.user')}}" class="sub-link {{ nav_active(['admin.report.user']) }}"><i class="mdi mdi-account-star-outline"></i><span>{{ adm_text('user_report','User Report') }}</span></a></li>
                            @endif
                        @endif
                        
                    </ul>
                </div>
            </li>
            @endif

            @if(!$isWorker && auth()->user()->can('user.view'))
                <li class="menu-item"><a href="{{ route('admin.expenses.index')}}" class="menu-link {{ nav_active(['admin.expenses.*']) }}"><i class="menu-icon mdi mdi-wallet-outline"></i><span class="menu-text">{{ adm_text('expense','Expense') }}</span></a></li>
                <li class="menu-item"><a href="{{ route('admin.ipblock')}}" class="menu-link {{ nav_active(['admin.ipblock']) }}"><i class="menu-icon mdi mdi-security"></i><span class="menu-text">{{ adm_text('block_ip','Block Ip') }}</span></a></li>
                <li class="menu-item"><a href="{{ route('admin.activity_logs') }}" class="menu-link {{ nav_active(['admin.activity_logs']) }}"><i class="menu-icon mdi mdi-history"></i><span class="menu-text">{{ adm_text('activity_logs','Activity Logs') }}</span></a></li>
                <li class="menu-item"><a href="{{ route('admin.auto_assign.index') }}" class="menu-link {{ nav_active(['admin.auto_assign.index']) }}"><i class="menu-icon mdi mdi-robot-outline"></i><span class="menu-text">Auto Assign Bot</span></a></li>
            @endif

            @if(auth()->user()->can('product.delete'))
            <li class="menu-item">
                <a href="{{ route('admin.invoice_design.index') }}" class="menu-link {{ nav_active(['admin.invoice_design.index']) }}">
                    <i class="menu-icon mdi mdi-receipt-text-outline"></i>
                    <span class="menu-text">{{ adm_text('invoice_design','Invoice Design') }}</span>
                </a>
            </li>
            
            <li class="menu-item mt-2 border-top border-secondary pt-2">
                <a href="{{ route('admin.payment_settings') }}" class="menu-link {{ nav_active(['admin.payment_settings']) }}">
                    <i class="menu-icon mdi mdi-credit-card-multiple-outline text-success"></i>
                    <span class="menu-text">Payment Settings</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="{{ route('admin.settings.index') }}" class="menu-link {{ nav_active(['admin.settings.*']) }}">
                    <i class="menu-icon mdi mdi-cog-outline"></i>
                    <span class="menu-text">{{ adm_text('settings_manage','Settings Manage') }}</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('admin.tracking_guide') }}" class="menu-link {{ nav_active(['admin.tracking_guide']) }}">
                    <i class="menu-icon mdi mdi-book-open-page-variant-outline text-info"></i>
                    <span class="menu-text">Tracking Setup Guide</span>
                </a>
            </li>
            @endif

        </ul>
        
        <div class="sidebar-footer">
            <p>{{ date('Y') }} &copy; BIZ CARE LTD. V-2.5</p>
        </div>

    </div>
</aside>

<style>
:root {
    --nav-w: 252px; 
    --nav-bg: #0f172a;                
    --nav-item-color: #94a3b8;     
    --nav-item-hover: #ffffff;     
    --nav-active-bg: rgba(67, 24, 255, 0.15); 
    --nav-active-border: #4318FF; 
    --nav-accent: #4318FF;         
    --nav-header-h: 70px;
}

.premium-sidebar {
    width: var(--nav-w);
    height: 100vh;
    position: fixed;
    top: 0; left: 0;
    background: var(--nav-bg);
    z-index: 99999;
    display: flex;
    flex-direction: column;
    border-right: 1px solid rgba(255,255,255,0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (min-width: 992px) {
    body.with-sidebar { padding-left: var(--nav-w); }
}

@media (max-width: 991.98px) {
    .premium-sidebar { position: relative; width: 100%; height: auto; border-right: none; }
    body.with-sidebar { padding-left: 0 !important; }
    .sidebar-header { display: none !important; }
}

.sidebar-header { min-height: var(--nav-header-h); width: 100%; }

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 10px 15px 20px 15px;
    display: flex;
    flex-direction: column;
    scrollbar-width: none; 
    -ms-overflow-style: none;
}
.sidebar-content::-webkit-scrollbar { display: none; width: 0; }

.sidebar-menu { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; flex-grow: 1; }

.menu-header {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #475569;
    font-weight: 800;
    margin: 20px 0 10px 15px;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: var(--nav-item-color);
    text-decoration: none !important;
    border-radius: 12px;
    transition: all 0.2s;
    background: transparent;
    border: none;
    width: 100%;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    outline: none !important;
    text-align: left;
}

.menu-link:hover {
    color: var(--nav-item-hover);
    background: rgba(255,255,255,0.05);
    transform: translateX(4px);
}

.menu-link.active {
    background: var(--nav-active-bg);
    color: #fff;
    font-weight: 600;
}

.menu-icon {
    font-size: 20px; 
    margin-right: 12px;
    transition: color 0.2s;
    min-width: 24px;
    text-align: center;
}

.menu-text { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.menu-arrow {
    font-size: 18px;
    color: #64748b;
    transition: transform 0.3s ease;
    margin-left: auto;
}
.menu-link[aria-expanded="true"] .menu-arrow {
    transform: rotate(90deg);
    color: #fff;
}

.sub-menu { list-style: none; padding: 5px 0; margin: 0; position: relative; }

.sub-link {
    display: flex;
    align-items: center;
    padding: 10px 15px 10px 50px; 
    color: #94a3b8;
    font-size: 14px;
    text-decoration: none !important;
    transition: all 0.2s;
    border-radius: 10px;
    margin-bottom: 2px;
}

.sub-link:hover, .sub-link.active {
    color: #fff;
    background: rgba(255,255,255,0.05);
}

.sub-link i { font-size: 16px; margin-right: 10px; color: inherit; }

.sidebar-footer {
    margin-top: auto;
    padding: 20px;
    border-top: 1px solid rgba(255,255,255,0.05);
    text-align: center;
    color: #475569;
    font-size: 11px;
}

.premium-sidebar .menu-link::after { display: none !important; } 
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let retryCount = 0;

    function setupSidebarScroll() {
        const scrollContainer = document.querySelector('.sidebar-content .simplebar-content-wrapper');

        if (scrollContainer) {
            let savedScroll = localStorage.getItem('sidebarScrollPosition');
            if (savedScroll) {
                setTimeout(() => {
                    scrollContainer.scrollTop = savedScroll;
                }, 10);
            }

            scrollContainer.addEventListener('scroll', function() {
                localStorage.setItem('sidebarScrollPosition', scrollContainer.scrollTop);
            });

            document.querySelectorAll('.sidebar-content a, .sidebar-content button').forEach(el => {
                el.addEventListener('click', function() {
                    localStorage.setItem('sidebarScrollPosition', scrollContainer.scrollTop);
                });
            });
        } else {
            if (retryCount < 20) {
                retryCount++;
                setTimeout(setupSidebarScroll, 100);
            }
        }
    }

    setupSidebarScroll();
});
</script>