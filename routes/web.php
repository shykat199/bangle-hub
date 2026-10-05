<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Frontend\AuthController as UserController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\BkashController;
use App\Http\Controllers\Frontend\EPSController;
use App\Http\Controllers\Frontend\NagadController;
use App\Http\Controllers\Frontend\UddoktapayController;
use App\Http\Controllers\Frontend\ProductController as FrontProduct;
use App\Http\Controllers\Frontend\DashboardController as UserDashboard;
use App\Http\Controllers\Frontend\UserOrderController;
use App\Http\Controllers\Frontend\UserAccountDetailsController;
use App\Http\Controllers\Frontend\UserWishlistController;
use App\Http\Controllers\Frontend\ProductReviewController;

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ExpenseController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\SliderController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\CashSaleController; 
use App\Http\Controllers\Backend\UsersController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\TypeController;
use App\Http\Controllers\Backend\SizeController;
use App\Http\Controllers\Backend\HomeSectionImageController;
use App\Http\Controllers\Backend\ProductDiscountController;
use App\Http\Controllers\Backend\PurchaseController;
use App\Http\Controllers\Backend\AboutUsController;
use App\Http\Controllers\Backend\CareerController;
use App\Http\Controllers\Backend\SocialIconController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\ComboController;
use App\Http\Controllers\Backend\ColorController;
use App\Http\Controllers\Backend\DeliveryChargeController;
use App\Http\Controllers\Backend\OrderPaymentController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\LandingPageController;
use App\Http\Controllers\Backend\CouponCodeController;
use App\Http\Controllers\Backend\CourierController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\InformationController;
use App\Http\Controllers\Backend\IPBlockController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\Backend\AdminTextController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\FacebookFeedController;
use App\Http\Controllers\Backend\ActivityLogController;
use App\Http\Controllers\Backend\ManualPaymentController;

use App\Http\Controllers\UpdateController;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Http\Controllers\Backend\AddonController;

Route::get('/facebook-product-feed.xml', [FacebookFeedController::class, 'index']);
Route::post('/lp/track/initiate-checkout', [\App\Http\Controllers\LandingPixelController::class, 'initiateCheckout'])->name('lp.track.initiate_checkout');

Route::get('/product-popup/{id}', [App\Http\Controllers\Frontend\ProductController::class, 'popup'])->name('front.product.popup');

// Backend endpoints that used to sit outside every middleware group — anyone
// could read the sales export (customer PII + profit) or rewrite the site's
// styling without logging in. They keep their original URLs and names so no
// blade/JS reference breaks; only the guard in front of them is new.
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/facebook-feed', [FacebookFeedController::class, 'settings']);
    Route::post('/facebook-feed/toggle', [FacebookFeedController::class, 'toggle'])->name('facebook_feed.toggle');

    Route::get('/admin/size-image', [\App\Http\Controllers\Backend\ProductController::class, 'getSizeImage'])->name('admin.size.image');
    Route::get('/admin/styles', [\App\Http\Controllers\Backend\InformationController::class, 'colors'])->name('admin.styles.index');
    Route::put('/admin/style/{id}', [InformationController::class, 'styleUpdate'])->name('admin.style.update');

    Route::get('/admin/products/search', [ProductController::class, 'search'])->name('admin.products.search');
    Route::get('/admin/products/{id}/variation-matrix', [ProductController::class, 'variationMatrix']);
    Route::get('/admin/report/sales/export', [ReportController::class, 'exportSales'])->name('admin.report.sales.export');

    Route::get('/admin/landing-page/color/{id?}', [App\Http\Controllers\Backend\LandingPageController::class, 'colorSettings'])->name('admin.landing_pages.color');
    Route::post('/admin/landing-page/color/update/{id?}', [App\Http\Controllers\Backend\LandingPageController::class, 'updateColor'])->name('admin.landing_pages.color_update');
});

Route::post('/send-otp', [App\Http\Controllers\Frontend\CheckoutController::class, 'sendOtp'])->name('sendOtp');
Route::post('/verify-otp', [App\Http\Controllers\Frontend\CheckoutController::class, 'verifyOtp'])->name('verifyOtp');

Route::get('/example1', [SslCommerzPaymentController::class, 'exampleEasyCheckout']);
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout']);
Route::post('/pay', [SslCommerzPaymentController::class, 'index']);
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);

Route::post('/bkash/create-payment', [BkashController::class, 'createPayment'])->name('bkash.create');
Route::post('/bkash/execute-payment', [BkashController::class, 'executePayment'])->name('bkash.execute');

Route::get('/eps/pay/{order_id}', [EPSController::class, 'pay'])->name('eps.pay');
Route::get('/eps/success', [EPSController::class, 'success'])->name('eps.success');
Route::get('/eps/fail', [EPSController::class, 'fail'])->name('eps.fail');
Route::get('/eps/cancel', [EPSController::class, 'cancel'])->name('eps.cancel');

Route::get('/nagad/pay/{id}', [NagadController::class, 'pay'])->name('nagad.pay');
Route::get('/nagad/callback', [NagadController::class, 'callback'])->name('nagad.callback');

Route::get('/uddoktapay/pay/{id}', [UddoktapayController::class, 'pay'])->name('uddoktapay.pay');
Route::post('/uddoktapay/success', [UddoktapayController::class, 'success'])->name('uddoktapay.success');
Route::get('/uddoktapay/cancel', [UddoktapayController::class, 'cancel'])->name('uddoktapay.cancel');

Route::match(['get', 'post'], '/courier-webhook', [OrderController::class, 'courierWebhook'])->name('courier.webhook');
Route::post('/api/webhook/manydial', [\App\Http\Controllers\Api\CallWebhookController::class, 'handleManyDialWebhook'])->name('webhook.manydial');

// Drains the queue on demand. Left open, repeated anonymous hits pinned PHP
// workers and let anyone burn the shop's call/SMS credit.
Route::get('/run-pending-calls', function () {
    \Illuminate\Support\Facades\Artisan::call('queue:work --stop-when-empty');
    return response()->json(['status' => 'done']);
})->middleware(['auth', 'staff']);

Route::get('/stock-warning', [App\Http\Controllers\Backend\ProductController::class, 'stockWarningIndex'])->name('admin.stock_warning')->middleware(['auth', 'staff']);
Route::get('/track-order', [HomeController::class, 'orderTrack'])->name('front.order.track');
Route::get('/optimize-images', [App\Http\Controllers\Backend\ImageOptimizeController::class, 'run'])->middleware(['auth', 'deny.worker']);
Route::get('/clear-cache', function(){
    \Artisan::call('optimize');
    \Artisan::call('view:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('config:cache');
    \Artisan::call('route:clear');
    dd('ok');
})->middleware(['auth', 'deny.worker']);

// Runs pending migrations — for servers without SSH access
Route::get('/run-migrate', function(){
    \Artisan::call('migrate', ['--force' => true]);
    return '<pre>' . e(\Artisan::output() ?: 'Nothing to migrate.') . '</pre>';
})->middleware(['auth', 'deny.worker']);

Route::group(['as'=>'front.'], function() {
    Route::resource('orders',UserOrderController::class);

    Route::controller(HomeController::class)->group(function(){
        Route::get('/','home')->name('home');
        Route::get('page/{page}', 'pageName')->name('page.name');
        Route::get('/about-us','aboutUs')->name('aboutUs');
        Route::get('/contact-us','contactUs')->name('contactUs');
        Route::get('/careers','career')->name('career');
        Route::get('/privacy-policy','privacyPolicy')->name('privacyPolicy');
        Route::get('/term-condition','termCondition')->name('termCondition');
        Route::get('/return-policy','returnPolicy')->name('returnPolicy');
        Route::get('/faq','faq')->name('faq');
        Route::get('/send-sms','sendSMs')->name('sendSMs');
        Route::post('/contacts','contact')->name('contact');
        Route::post('/exit-popup/track','exitPopupTrack')->name('exit_popup.track')->middleware('throttle:30,1');
    });

    Route::post('/stock-notify', [\App\Http\Controllers\Frontend\StockNotificationController::class, 'store'])
        ->name('stock_notify.store')->middleware('throttle:10,1');

    Route::controller(FrontProduct::class)->group(function(){
        Route::get('/products','index')->name('products.index');
        // Old shop URL — now served at /products (filters and search in the query string are kept).
        Route::get('/products-list', fn (\Illuminate\Http\Request $request) => redirect()->to(
            route('front.products.index') . ($request->getQueryString() ? '?' . $request->getQueryString() : ''), 301
        ));
        Route::get('/live-search','liveSearch')->name('products.liveSearch')->middleware('throttle:60,1');
        Route::get('/category','categories')->name('categories');
        Route::get('/c/{slug}','subCategories')->name('subCategories');
        // Old prefixed category URLs — now served at /{slug} (see end of file).
        Route::get('/cs/{slug}', fn ($slug) => redirect()->route('front.category', $slug, 301))->name('subCategories1');
        Route::get('/s/{slug}', fn ($slug) => redirect()->route('front.category', $slug, 301))->name('subsubCategories');
        Route::get('/brands','brands')->name('brands');
        Route::get('/discount-products','discountProduct')->name('discountProduct');

        Route::get('/product/{product}', [FrontProduct::class, 'show'])->name('products.show');
        // Old product URLs — now served at /product/{slug}.
        Route::get('/product-show/{product}', fn ($product) => redirect()->route('front.products.show', $product, 301));
        Route::get('/relative-product/{product}', [FrontProduct::class, 'relativeProduct'])->name('products.relativeProduct');

        Route::get('/combo-products','comboProducts')->name('combo_products');
        Route::get('/get-trending-products','trendingProduct')->name('trendingProduct');
        Route::get('/get-hotdeal-products','hotdealProduct')->name('hotdealProduct');
        Route::get('/get-recommended-products','recommendedProduct')->name('recommendedProduct');
        
        Route::get('view-landing-page/{id}','landing_page')->name('landing_pages.view_page');
        Route::get('view-landing-page-two/{id}','landing_pages_two')->name('landing_pages_two.view_page');
        Route::get('view-landing-page-three/{id}','landing_page_three')->name('landing_pages_three.view_page');
        Route::get('view-landing-page-four/{id}','landing_page_four')->name('landing_pages_four.view_page');
        Route::get('view-landing-page-five/{id}','landing_page_five')->name('landing_pages_five.view_page');
        Route::get('view-landing-page-six/{id}','landing_page_six')->name('landing_pages_six.view_page');
        Route::get('view-landing-page-seven/{id}', 'landing_page_seven')->name('landing_pages_seven.view_page');
        Route::get('view-landing-page-eight/{id}', 'landing_page_eight')->name('landing_pages_eight.view_page');
        Route::get('view-landing-page-nine/{id}', 'landing_page_nine')->name('landing_pages_nine.view_page');
        Route::get('view-landing-page-ten/{id}', 'landing_page_ten')->name('landing_pages_ten.view_page');
        Route::get('view-landing-page-eleven/{id}', 'landing_page_eleven')->name('landing_pages_eleven.view_page');
        Route::get('view-landing-page-twelve/{id}', 'landing_page_twelve')->name('landing_pages_twelve.view_page');
        Route::get('view-landing-page-thirteen/{id}', 'landing_page_thirteen')->name('landing_pages_thirteen.view_page');
        Route::get('view-landing-page-fourteen/{id}', 'landing_page_fourteen')->name('landing_pages_fourteen.view_page');
        Route::get('view-landing-page-fifteen/{id}', 'landing_page_fifteen')->name('landing_pages_fifteen.view_page');
        Route::get('view-landing-page-sixteen/{id}', 'landing_page_sixteen')->name('landing_pages_sixteen.view_page');

        Route::get('/free-shipping-product', 'free_shipping')->name('free-shipping');
        Route::get('/get-variation_price','get_variation_price')->name('get-variation_price');
    });

    // GET + POST: older landing page designs post to this endpoint.
    Route::match(['get','post'],'/coupon-discount',[CheckoutController::class,'getCouponDiscount'])->name('getCouponDiscount');

    Route::group(['middleware' => 'auth'], function() {
        Route::resource('dashboard',UserDashboard::class);
        Route::resource('account_details',UserAccountDetailsController::class);
    });

    // Wishlist works for guests as well (cookie) — logged-in customers get it saved to their account.
    Route::get('/wishlist', [UserWishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [UserWishlistController::class, 'toggle'])->name('wishlist.toggle')->middleware('throttle:60,1');

    Route::get('/review-login/{product}', [ProductReviewController::class, 'loginFirst'])->name('reviews.login');
    Route::resource('product-reviews',ProductReviewController::class);
    Route::put('product-reviews', [ProductReviewController::class, 'update2'])->name('product.view.update');

    Route::controller(UserDashboard::class)->group(function(){
        Route::get('/confirm-order/{id}','confirmOrder')->name('confirmOrder');
        Route::get('/confirm-order-landing/{id}','confirmOrderlanding')->name('confirmOrderlanding');
    });

    Route::controller(UserController::class)->group(function(){
        Route::post('/user-login','login')->name('login');
        Route::get('/seller-register', function(){ return null; })->name('sellerRegister');
        Route::post('/seller-register-post','sellerRegisterPost')->name('sellerRegisterPost');
        Route::post('/user-register','Register')->name('register');
        Route::get('/get-otp','getOpt')->name('getOpt');
        Route::post('/otp-verify','optVerify')->name('optVerify');
    });

    Route::resource('/carts',CartController::class);
    Route::post('/cart/store', [CartController::class, 'storeCart'])->name('carts.storeCart');
    Route::get('/cart/clear-all', [CartController::class, 'clearAll'])->name('carts.clearAll');

    Route::resource('/checkouts',CheckoutController::class);
    Route::post('store-data',[CheckoutController::class,'storeData'])->name('storeData');
    Route::post('/store/checkout',[CheckoutController::class,'StoreChk'])->name('store.checkout');

    Route::post('/store/landing/data',[CheckoutController::class,'storelandData'])->name('storelandData');
    Route::post('/get-delivery-charge-ajax', [CheckoutController::class, 'getDeliveryChargeAjax'])->name('getDeliveryChargeAjax');
});

Route::post('incomplete/order/store',[CheckoutController::class,'incompleteStore'])->name('incompleteStore');
Route::get('/check-courier-percentage',[CheckoutController::class,'courierPercentage'])->name('courierPercentage');

Route::post('/admin/products/toggle-recommended', [\App\Http\Controllers\Backend\ProductController::class, 'toggleRecommended'])->name('admin.product.toggleRecommended')->middleware('auth');

Auth::routes();

Route::controller(AuthController::class)->group(function(){
    Route::get('/admin','login')->name('admin.login');
    Route::post('/admin-login','postLogin')->name('admin.postLogin');
});

Route::group(['middleware' => ['auth', 'deny.worker']], function () {
    Route::get('/system-update', [UpdateController::class, 'checkUpdate'])->name('update.check');
    Route::post('/system-update/process', [UpdateController::class, 'processUpdate'])->name('update.process');
});

// 'staff' keeps shop customers out: the storefront and the panel share one
// login guard, so 'auth' alone let anyone who registered on the shop open
// these pages. Staff always carry a role; self-registered customers do not.
Route::group(['prefix' => 'admin','middleware' => ['auth','staff'],'as'=>'admin.'], function() {

    // Admin-only management routes — workers are blocked server-side.
    Route::group(['middleware' => 'deny.worker'], function () {
        Route::post('manual-payments', [ManualPaymentController::class, 'store'])->name('manual_payments.store');
        Route::get('manual-payments/toggle/{id}', [ManualPaymentController::class, 'toggle'])->name('manual_payments.toggle');
        Route::delete('manual-payments/{id}', [ManualPaymentController::class, 'destroy'])->name('manual_payments.destroy');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs');
        Route::post('/activity-logs/undo/{id}', [ActivityLogController::class, 'undoActivity'])->name('activity_logs.undo');

        Route::get('/dynamic-text', [AdminTextController::class, 'edit'])->name('dynamic_text.edit');
        Route::post('/dynamic-text', [AdminTextController::class, 'update'])->name('dynamic_text.update');

        Route::get('/Ip-block', [IPBlockController::class, 'index'])->name('ipblock');
        Route::get('/Ip-block/delete/{id}', [IPBlockController::class, 'delete'])->name('ipblock.delete');
        Route::get('/Ip-block/edit/{id}', [IPBlockController::class, 'edit'])->name('ipblock.edit');
        Route::put('/Ip-block/update/{id}', [IPBlockController::class, 'update'])->name('ipblock.update');
        Route::post('/Ip-block-submit', [IPBlockController::class, 'IPBlockSubmit'])->name('ipblock.submit');
    });

    // Per-order activity log — used inside the order drawer, so workers keep it.
    Route::get('/order-history/{id}', [ActivityLogController::class, 'getOrderHistory'])->name('order.history');

    Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('dashboard');
    Route::get('/get-dashboard-data',[DashboardController::class,'getDashboardData'])->name('getDashboardData');
    Route::get('/get-dashboard-data-two',[DashboardController::class,'getDashboardData2'])->name('getDashboardData2');

    Route::resource('reviews', DashboardController::class)->only(['index', 'destroy'])->middleware('deny.worker');
    Route::get('reviews/action', [DashboardController::class, 'reviewAction'])->name('reviews.action')->middleware('deny.worker');

    Route::post('/file-upload',[ProductController::class,'fileUpload'])->name('ckeditor.upload');
    Route::get('/file-delete/{id}',[ProductController::class,'deleteImage'])->name('deleteImage')->middleware('deny.worker');
    Route::post('/product-images/{product}/bulk-delete',[ProductController::class,'bulkDeleteImages'])->name('productImages.bulkDelete')->middleware('deny.worker');
    Route::post('/product-images/{product}/sort',[ProductController::class,'sortImages'])->name('productImages.sort')->middleware('deny.worker');
    Route::get('/get-sub-category',[ProductController::class,'getSubcategory'])->name('getSubcategory');
    Route::get('/product-export',[ProductController::class,'productExport'])->name('productExport');
    Route::post('/update-priority/{id}', [ProductController::class, 'updatePriority']);
    Route::get('/cat-wise-product',[ProductController::class,'cat_wise_product'])->name('cat_wise_product');

    Route::controller(OrderController::class)->group(function(){
        Route::get('/scan-return', 'scanReturnIndex')->name('scan_return.index');
        Route::post('/scan-return/submit', 'scanReturnSubmit')->name('scan_return.submit');
        Route::post('/scan-return/missing', 'markMissingReturns')->name('scan_return.missing');
        Route::post('/scan-return/missing-csv', 'missingCheckerProcess')->name('scan_return.missing_csv');

        Route::get('/auto-assign', 'autoAssignIndex')->name('auto_assign.index');
        Route::post('/save-auto-assign-status', 'saveAutoAssignStatus')->name('saveAutoAssignStatus');
        Route::post('/toggle-auto-assign', 'toggleAutoAssign')->name('toggleAutoAssign');
        
        Route::get('/orders/customer-history', 'customerHistory')->name('orders.customerHistory');
        
        Route::post('/fetch-address-details', 'fetchAddressDetails')->name('fetch.address.details');
        Route::get('order/fraud-check/{id}', 'fraudOrderCheck')->name('fraudOrderCheck');
        Route::get('order/fraudulent-check/{mobileNo}', 'fraudulentCheck')->name('fraudulentCheck');

        Route::get('/order-status/{id}','orderStatus')->name('orderStatus');
        Route::post('/order-status/update/{id}','orderStatusUPdate')->name('orderStatusUPdate');

        Route::get('/orders-details-ajax/{id}', 'getOrderDetailsAjax')->name('orders.details_ajax');
        Route::post('/orders/update-address-ajax', 'updateAddressAjax')->name('orders.updateAddressAjax');

        Route::get('/get-order-product','getOrderProduct')->name('getOrderProduct');
        Route::get('/get-order-product2','getOrderProduct2')->name('getOrderProduct2');
        Route::get('/order-product-entry','orderProductEntry')->name('orderProductEntry');
        Route::get('/landing-product-entry','landingProductEntry')->name('landingProductEntry');
        Route::get('/order-export','orderExport')->name('orderExport');

        Route::get('/assign-user','assignUser')->name('assignUser');
        Route::get('/order-status-opdate','orderStatusUpdateMulti')->name('orderStatusUpdateMulti');
        Route::get('/order-status-direct', 'orderStatusUpdateDirect')->name('orderStatusUpdateDirect');
        
        Route::get('/all-order-delete','deleteAllOrder')->name('deleteAllOrder');
        Route::get('/order-list','orderList')->name('orderList');
        Route::post('/order-list','orderList')->name('orderListPost');
        Route::get('/order-ids-by-filter','orderIdsByFilter')->name('orderIdsByFilter');
        Route::view('/print_multiple','backend.reports.print');

        Route::get('/status-wise-order', 'status_wise_order')->name('status_wise_order');
        Route::get('/search-order', 'searchOrder')->name('searchOrder');

        Route::post('/assign-user-store','assignUserStore')->name('assignUserStore');
        Route::get('/multi-order-status-update-store','multuOrderStatusUpdate')->name('multuOrderStatusUpdate');

        Route::get('/courier-precheck','courierPrecheck')->name('courierPrecheck');
        Route::get('/order-product-performance','productPerformance')->name('productPerformance');
        Route::get('/order-product-performance-data','productPerformanceData')->name('productPerformanceData');
        Route::get('/order-product-performance-orders','productPerformanceOrders')->name('productPerformanceOrders');
        Route::get('/create-redx-parcel','OrderSendToRedx')->name('createRedxParcel');
        Route::get('/zones-by-city/{city}','getPathaoZoneListByCity')->name('zonesByCity');
        Route::get('/areas-by-zone/{zone}','getPathaoAreaListByZone')->name('areasByZone');
        Route::get('/create-pathao-parcel','OrderSendToPathao')->name('createPathaoParcel');
        Route::get('/create-steadfast-parcel', 'OrderSendToSteadfast')->name('createSteadfastParcel');
        Route::get('/create-carrybee-parcel', 'OrderSendToCarrybee')->name('createCarrybeeParcel');
        Route::post('/create-carrybee-parcel', 'OrderSendToCarrybee')->name('orders.send_to_carrybee');

        Route::get('/update-courier-status', [OrderController::class, 'updateCourierStatus'])->name('updateCourierStatus');
        Route::get('generate-access-token', 'viewAccessToken')->name('viewAccessToken');
        Route::post('generate-access-token', 'generatePathaoAccessToken')->name('generatePathaoAccessToken');

        Route::get('/trashed/orders', 'trashed_orders')->name('trashed_orders');
        Route::get('/restore/order', 'restore_order')->name('restore_order');
        Route::get('/force/delete/order/{id}', 'forceDel')->name('forceDel');
    });

    Route::prefix('cash-sales')->name('cash_sales.')->group(function () {
        Route::get('/', [CashSaleController::class, 'index'])->name('index');
        Route::get('/create', [CashSaleController::class, 'create'])->name('create');
        Route::post('/store', [CashSaleController::class, 'store'])->name('store');
    });

    Route::get('/recommended-update',[ProductController::class,'recommendedUpdate'])->name('recommendedUpdate');
    Route::get('/checkout-pick-update',[ProductController::class,'checkoutPickUpdate'])->name('checkoutPickUpdate');
    Route::get('/show-update',[ProductController::class,'showUpdate'])->name('showUpdate');
    Route::get('/product-copy/{id}',[ProductController::class,'productCopy'])->name('productCopy');
    Route::post('/products/{id}/duplicate',[ProductController::class,'duplicate'])->name('products.duplicate');
    Route::get('/products/{id}/quick-stock',[ProductController::class,'quickStock'])->name('products.quickStock');
    Route::post('/products/{id}/quick-stock',[ProductController::class,'quickStockUpdate'])->name('products.quickStockUpdate');
    Route::post('/products/variant-option',[ProductController::class,'storeVariantOption'])->name('products.variantOption');
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::post('/products/toggle-popular', [ProductController::class, 'togglePopular'])->name('product.togglePopular');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulkDelete');

    Route::resource('products',ProductController::class);

    Route::get('/stock-notifications', [\App\Http\Controllers\Backend\StockNotificationController::class, 'index'])->name('stock_notifications.index');
    Route::post('/stock-notifications/{id}/toggle', [\App\Http\Controllers\Backend\StockNotificationController::class, 'toggle'])->name('stock_notifications.toggle');
    Route::delete('/stock-notifications/{id}', [\App\Http\Controllers\Backend\StockNotificationController::class, 'destroy'])->name('stock_notifications.destroy');
    Route::resource('expenses',ExpenseController::class);

    Route::get('/home-category',[CategoryController::class,'homeCatgeory'])->name('homecat');
    Route::post('/home-category',[CategoryController::class,'storehomeCatgeory'])->name('store-homecat');
    Route::delete('/del-home-category/{id}', [CategoryController::class, 'delhomeCatgeory'])->name('del_homecat');
    
    Route::post('/home-category/update/{id}', [CategoryController::class, 'updatehomeCatgeory'])->name('update_homecat');
    Route::post('/home-category/status/{id}', [CategoryController::class, 'homeCategoryStatus'])->name('homecat_status');
    Route::get('/home-category/remove-cover/{id}', [CategoryController::class, 'removeHomeCover'])->name('remove_homecat_cover');

    Route::get('/popular-category',[CategoryController::class,'popularCatgeory'])->name('popularCatgeory');
    Route::resource('categories',CategoryController::class);
    Route::resource('sliders',SliderController::class);
    Route::resource('orders',OrderController::class);
    Route::resource('users',UsersController::class)->middleware('deny.worker');
    Route::resource('roles',RoleController::class)->middleware('deny.worker');
    Route::resource('permissions',PermissionController::class)->middleware('deny.worker');

    Route::get('/top-brand-update',[TypeController::class,'topBrandUpdate'])->name('topBrandUpdate');
    Route::resource('types',TypeController::class);
    Route::resource('sizes',SizeController::class);
    Route::resource('purchase',PurchaseController::class);
    Route::resource('about_us',AboutUsController::class);
    Route::resource('career',CareerController::class);
    Route::resource('suppliers',SupplierController::class);
    Route::resource('combos',ComboController::class);
    Route::resource('colors',ColorController::class);
    Route::resource('pages',PageController::class);
    
    // "Design one" — page_type=1 — never got its own admin create/index/edit
    // views built, has zero rows in the DB, and nothing in the admin menu
    // links to it. Its index/create/edit pages 500'd with "View not found";
    // store/update/destroy worked but were only reachable by hand-crafting a
    // request, never through any UI. All seven resource routes are gone —
    // the shared processStore()/processUpdate() logic they called stays on
    // the controller, still used by every working design two-sixteen.

    Route::get('landing-page/{id}',[PageController::class,'landing_page'])->name('landing_index');
    Route::post('store-data',[LandingPageController::class,'storeData'])->name('landing_pages.storeData');

    // ✅ Landing-page specific product search — products WITHOUT variations o include kore
    // (OrderController-er getOrderProduct2 variations table-er sathe JOIN kore — single product miss kore.
    //  Ei dedicated route LandingPageController use kore products table-e directly query kore.)
    Route::get('/lp-get-product-search',[LandingPageController::class,'getOrderProduct2'])->name('lp.getOrderProduct2');
    Route::get('/lp-product-entry',[LandingPageController::class,'landingProductEntry'])->name('lp.landingProductEntry');
    
    Route::get('landing-page-two',[LandingPageController::class,'landing_page_two'])->name('landing_pages_two');
    Route::get('create-landing-page-two',[LandingPageController::class,'create_landing_page_two'])->name('landing_pages_two.create');
    Route::post('store-landing-page-two',[LandingPageController::class,'store_landing_page_two'])->name('landing_pages_two.store');
    Route::get('edit-landing-page-two/{id}',[LandingPageController::class,'edit_landing_page_two'])->name('landing_pages_two.edit');
    Route::patch('update-landing-page-two/{id}', [LandingPageController::class, 'update_landing_page_two'])->name('landing_pages_two_update');
    // destroy_landing_page_two() never existed on the controller — every
    // other design (three..sixteen) already points its delete route at the
    // one generic destroy() below, which works for any page_type since the
    // row lives in the same landing_pages table regardless of design.
    Route::delete('delete-landing-page-two/{id}', [LandingPageController::class,'destroy'])->name('landing_pages_two.destroy');

    Route::get('landing-page-three', [LandingPageController::class, 'index_three'])->name('landing_pages_three');
    Route::get('create-landing-page-three', [LandingPageController::class, 'create_three'])->name('landing_pages_three.create');
    Route::post('store-landing-page-three', [LandingPageController::class, 'store_three'])->name('landing_pages_three.store');
    Route::get('edit-landing-page-three/{id}', [LandingPageController::class, 'edit_three'])->name('landing_pages_three.edit');
    Route::patch('update-landing-page-three/{id}', [LandingPageController::class, 'update_three'])->name('landing_pages_three.update');
    Route::delete('delete-landing-page-three/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_three.destroy');

    Route::get('landing-page-four', [LandingPageController::class, 'index_four'])->name('landing_pages_four');
    Route::get('create-landing-page-four', [LandingPageController::class, 'create_four'])->name('landing_pages_four.create');
    Route::post('store-landing-page-four', [LandingPageController::class, 'store_four'])->name('landing_pages_four.store');
    Route::get('edit-landing-page-four/{id}', [LandingPageController::class, 'edit_four'])->name('landing_pages_four.edit');
    Route::patch('update-landing-page-four/{id}', [LandingPageController::class, 'update_four'])->name('landing_pages_four.update');
    Route::delete('delete-landing-page-four/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_four.destroy');

    Route::get('landing-page-five', [LandingPageController::class, 'index_five'])->name('landing_pages_five');
    Route::get('create-landing-page-five', [LandingPageController::class, 'create_five'])->name('landing_pages_five.create');
    Route::post('store-landing-page-five', [LandingPageController::class, 'store_five'])->name('landing_pages_five.store');
    Route::get('edit-landing-page-five/{id}', [LandingPageController::class, 'edit_five'])->name('landing_pages_five.edit');
    Route::patch('update-landing-page-five/{id}', [LandingPageController::class, 'update_five'])->name('landing_pages_five.update');
    Route::delete('delete-landing-page-five/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_five.destroy');

    Route::get('landing-page-six', [LandingPageController::class, 'index_six'])->name('landing_pages_six');
    Route::get('create-landing-page-six', [LandingPageController::class, 'create_six'])->name('landing_pages_six.create');
    Route::post('store-landing-page-six', [LandingPageController::class, 'store_six'])->name('landing_pages_six.store');
    Route::get('edit-landing-page-six/{id}', [LandingPageController::class, 'edit_six'])->name('landing_pages_six.edit');
    Route::patch('update-landing-page-six/{id}', [LandingPageController::class, 'update_six'])->name('landing_pages_six.update');
    Route::delete('delete-landing-page-six/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_six.destroy');

    Route::get('landing-page-seven', [LandingPageController::class, 'index_seven'])->name('landing_pages_seven');
    Route::get('create-landing-page-seven', [LandingPageController::class, 'create_seven'])->name('landing_pages_seven.create');
    Route::post('store-landing-page-seven', [LandingPageController::class, 'store_seven'])->name('landing_pages_seven.store');
    Route::get('edit-landing-page-seven/{id}', [LandingPageController::class, 'edit_seven'])->name('landing_pages_seven.edit');
    Route::patch('update-landing-page-seven/{id}', [LandingPageController::class, 'update_seven'])->name('landing_pages_seven.update');
    Route::delete('delete-landing-page-seven/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_seven.destroy');

    Route::get('landing-page-eight', [LandingPageController::class, 'index_eight'])->name('landing_pages_eight');
    Route::get('create-landing-page-eight', [LandingPageController::class, 'create_eight'])->name('landing_pages_eight.create');
    Route::post('store-landing-page-eight', [LandingPageController::class, 'store_eight'])->name('landing_pages_eight.store');
    Route::get('edit-landing-page-eight/{id}', [LandingPageController::class, 'edit_eight'])->name('landing_pages_eight.edit');
    Route::patch('update-landing-page-eight/{id}', [LandingPageController::class, 'update_eight'])->name('landing_pages_eight.update');
    Route::delete('delete-landing-page-eight/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_eight.destroy');

    Route::get('landing-page-nine', [LandingPageController::class, 'index_nine'])->name('landing_pages_nine');
    Route::get('create-landing-page-nine', [LandingPageController::class, 'create_nine'])->name('landing_pages_nine.create');
    Route::post('store-landing-page-nine', [LandingPageController::class, 'store_nine'])->name('landing_pages_nine.store');
    Route::get('edit-landing-page-nine/{id}', [LandingPageController::class, 'edit_nine'])->name('landing_pages_nine.edit');
    Route::patch('update-landing-page-nine/{id}', [LandingPageController::class, 'update_nine'])->name('landing_pages_nine.update');
    Route::delete('delete-landing-page-nine/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_nine.destroy');

    Route::get('landing-page-ten', [LandingPageController::class, 'index_ten'])->name('landing_pages_ten');
    Route::get('create-landing-page-ten', [LandingPageController::class, 'create_ten'])->name('landing_pages_ten.create');
    Route::post('store-landing-page-ten', [LandingPageController::class, 'store_ten'])->name('landing_pages_ten.store');
    Route::get('edit-landing-page-ten/{id}', [LandingPageController::class, 'edit_ten'])->name('landing_pages_ten.edit');
    Route::patch('update-landing-page-ten/{id}', [LandingPageController::class, 'update_ten'])->name('landing_pages_ten.update');
    Route::delete('delete-landing-page-ten/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_ten.destroy');

    Route::get('landing-page-eleven', [LandingPageController::class, 'index_eleven'])->name('landing_pages_eleven');
    Route::get('create-landing-page-eleven', [LandingPageController::class, 'create_eleven'])->name('landing_pages_eleven.create');
    Route::post('store-landing-page-eleven', [LandingPageController::class, 'store_eleven'])->name('landing_pages_eleven.store');
    Route::get('edit-landing-page-eleven/{id}', [LandingPageController::class, 'edit_eleven'])->name('landing_pages_eleven.edit');
    Route::patch('update-landing-page-eleven/{id}', [LandingPageController::class, 'update_eleven'])->name('landing_pages_eleven.update');
    Route::delete('delete-landing-page-eleven/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_eleven.destroy');

    Route::get('landing-page-twelve', [LandingPageController::class, 'index_twelve'])->name('landing_pages_twelve');
    Route::get('create-landing-page-twelve', [LandingPageController::class, 'create_twelve'])->name('landing_pages_twelve.create');
    Route::post('store-landing-page-twelve', [LandingPageController::class, 'store_twelve'])->name('landing_pages_twelve.store');
    Route::get('edit-landing-page-twelve/{id}', [LandingPageController::class, 'edit_twelve'])->name('landing_pages_twelve.edit');
    Route::patch('update-landing-page-twelve/{id}', [LandingPageController::class, 'update_twelve'])->name('landing_pages_twelve.update');
    Route::delete('delete-landing-page-twelve/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_twelve.destroy');

    Route::get('landing-page-thirteen', [LandingPageController::class, 'index_thirteen'])->name('landing_pages_thirteen');
    Route::get('create-landing-page-thirteen', [LandingPageController::class, 'create_thirteen'])->name('landing_pages_thirteen.create');
    Route::post('store-landing-page-thirteen', [LandingPageController::class, 'store_thirteen'])->name('landing_pages_thirteen.store');
    Route::get('edit-landing-page-thirteen/{id}', [LandingPageController::class, 'edit_thirteen'])->name('landing_pages_thirteen.edit');
    Route::patch('update-landing-page-thirteen/{id}', [LandingPageController::class, 'update_thirteen'])->name('landing_pages_thirteen.update');
    Route::delete('delete-landing-page-thirteen/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_thirteen.destroy');

    Route::get('landing-page-fourteen', [LandingPageController::class, 'index_fourteen'])->name('landing_pages_fourteen');
    Route::get('create-landing-page-fourteen', [LandingPageController::class, 'create_fourteen'])->name('landing_pages_fourteen.create');
    Route::post('store-landing-page-fourteen', [LandingPageController::class, 'store_fourteen'])->name('landing_pages_fourteen.store');
    Route::get('edit-landing-page-fourteen/{id}', [LandingPageController::class, 'edit_fourteen'])->name('landing_pages_fourteen.edit');
    Route::patch('update-landing-page-fourteen/{id}', [LandingPageController::class, 'update_fourteen'])->name('landing_pages_fourteen.update');
    Route::delete('delete-landing-page-fourteen/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_fourteen.destroy');

    Route::get('landing-page-fifteen', [LandingPageController::class, 'index_fifteen'])->name('landing_pages_fifteen');
    Route::get('create-landing-page-fifteen', [LandingPageController::class, 'create_fifteen'])->name('landing_pages_fifteen.create');
    Route::post('store-landing-page-fifteen', [LandingPageController::class, 'store_fifteen'])->name('landing_pages_fifteen.store');
    Route::get('edit-landing-page-fifteen/{id}', [LandingPageController::class, 'edit_fifteen'])->name('landing_pages_fifteen.edit');
    Route::patch('update-landing-page-fifteen/{id}', [LandingPageController::class, 'update_fifteen'])->name('landing_pages_fifteen.update');
    Route::delete('delete-landing-page-fifteen/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_fifteen.destroy');

    Route::get('landing-page-sixteen', [LandingPageController::class, 'index_sixteen'])->name('landing_pages_sixteen');
    Route::get('create-landing-page-sixteen', [LandingPageController::class, 'create_sixteen'])->name('landing_pages_sixteen.create');
    Route::post('store-landing-page-sixteen', [LandingPageController::class, 'store_sixteen'])->name('landing_pages_sixteen.store');
    Route::get('edit-landing-page-sixteen/{id}', [LandingPageController::class, 'edit_sixteen'])->name('landing_pages_sixteen.edit');
    Route::patch('update-landing-page-sixteen/{id}', [LandingPageController::class, 'update_sixteen'])->name('landing_pages_sixteen.update');
    Route::delete('delete-landing-page-sixteen/{id}', [LandingPageController::class, 'destroy'])->name('landing_pages_sixteen.destroy');

    Route::get('delete-slider-image/{id}',[LandingPageController::class,'delete_slider'])->name('delete_slider')->middleware('deny.worker');
    Route::get('delete/review/{id}', [LandingPageController::class, 'delete_review'])->name('delete_review')->middleware('deny.worker');

    Route::resource('couriers',CourierController::class)->middleware('deny.worker');
    Route::resource('social-icons',SocialIconController::class,['names'=>'social_icons']);
    Route::resource('order-payments',OrderPaymentController::class,['names'=>'order_payments']);
    
    Route::post('delivery-charges/global-update', [DeliveryChargeController::class, 'globalUpdate'])->name('delivery_charge.global_update');
    Route::resource('delivery-charges',DeliveryChargeController::class,['names'=>'delivery_charge']);
    
    Route::resource('coupon-codes',CouponCodeController::class,['names'=>'coupon_codes'])->middleware('deny.worker');
    Route::post('exit-popup-settings',[CouponCodeController::class,'exitPopupSave'])->name('exit_popup.save')->middleware('deny.worker');

    Route::get('/user-status-update',[UsersController::class,'userStatusUpdate'])->name('userStatusUpdate');
    Route::resource('/home-section-images',HomeSectionImageController::class,['names'=>'home_section_images']);
    Route::resource('/product-discounts',ProductDiscountController::class,['names'=>'product_discounts']);

    Route::get('/free-shipping-product',[ProductDiscountController::class,'free_shipping'])->name('free_shipping');
    Route::get('/create-free-shipping-product',[ProductDiscountController::class,'create_free_shipping'])->name('create_free_shipping');
    Route::post('/store-free-shipping',[ProductDiscountController::class,'store_free_shipping'])->name('store-free-shipping');
    Route::get('/destroy-free-shipping',[ProductDiscountController::class,'fshippingdestroy'])->name('free-shipping.fshippingdestroy');

    Route::get('/get-discount-product',[ProductDiscountController::class,'getDiscountProduct'])->name('getDiscountProduct');
    Route::get('/product-entry',[ProductDiscountController::class,'productEntry'])->name('productEntry');
    Route::get('/free-shipping-product-entry',[ProductDiscountController::class,'productEntry2'])->name('productEntry2');

    Route::get('/get-purchase-product',[PurchaseController::class,'getPurchaseProduct'])->name('getPurchaseProduct');
    Route::get('/purchase-product-entry',[PurchaseController::class,'purchaseProductEntry'])->name('purchaseProductEntry');

    // ======== ADDON MARKETPLACE ========
    Route::get('/addons', [AddonController::class, 'index'])->name('addons.index')->middleware('deny.worker');
    Route::post('/addons/install', [AddonController::class, 'install'])->name('addons.install')->middleware('deny.worker');

    // ======== PROFIT CALCULATOR ========
    Route::prefix('profit-calculator')->name('profit_calculator.')->group(function () {
        Route::get('/',                            [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'index'])->name('index');
        Route::post('/save',                       [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'save'])->name('save');
        Route::post('/live',                       [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'liveCalc'])->name('live');
        Route::get('/history',                     [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'history'])->name('history');
        Route::post('/{id}/toggle-favorite',       [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'toggleFavorite'])->name('toggle_favorite');
        Route::delete('/{id}',                     [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'destroy'])->name('destroy');
        Route::get('/compare',                     [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'compare'])->name('compare');
        Route::get('/{id}/print',                  [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'printPdf'])->name('print');
        Route::get('/export/csv',                  [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'exportCsvAll'])->name('export_csv_all');
        Route::get('/{id}/export/csv',             [\App\Http\Controllers\Backend\ProfitCalculatorController::class, 'exportCsvOne'])->name('export_csv_one');
    });

    Route::group(['as'=> 'report.'], function(){
        Route::controller(ReportController::class)->group(function(){
            Route::get('/order-report', 'orderReport')->name('order');
            Route::get('/product-report', 'productReport')->name('product');
            Route::get('/user-report', 'userReport')->name('user');
            Route::get('/order-search', 'filterOrder')->name('order.search');
            Route::get('/product-search', 'filterProduct')->name('product.search');
            Route::get('/export-order-report', 'exportOrderReport')->name('order.export');
            Route::get('/sales-report', 'salesReport')->name('sales');
            Route::get('/daily-profit', 'dailyProfitReport')->name('daily_profit');
            Route::post('/store-ad-cost', 'storeAdCost')->name('store_ad_cost');
            Route::post('/store-other-expense', 'storeOtherExpense')->name('store_other_expense');
            Route::get('/monthly-profit', 'monthlyProfitReport')->name('monthly_profit');
            Route::get('/product-performance', 'productPerformanceReport')->name('product_performance');
            Route::get('/courier-performance', 'courierPerformanceReport')->name('courier_performance');
        });

        // Courier Tracking & Payment — কোন দিন কোন অর্ডারে ট্র্যাকিং আইডি এলো
        // এবং কোনগুলোর টাকা কুরিয়ার থেকে পাওয়া গেছে
        Route::controller(\App\Http\Controllers\Backend\CourierTrackingReportController::class)->group(function () {
            Route::get('/courier-tracking', 'index')->name('courier_tracking');
            Route::post('/courier-tracking/mark-paid', 'markPaid')->name('courier_tracking.markPaid');
            Route::post('/courier-tracking/mark-pending', 'markPending')->name('courier_tracking.markPending');
        });
    });
    
    Route::post('/update-invoice-type', [InformationController::class, 'updateInvoiceType'])->name('invoice_type.update')->middleware('deny.worker');
    Route::get('/invoice-design', [InformationController::class, 'invoiceDesign'])->name('invoice_design.index')->middleware('deny.worker');
    Route::resource('settings', InformationController::class)->middleware('deny.worker');

    Route::controller(InformationController::class)->group(function(){
        Route::get('/tracking-guide', 'trackingGuide')->name('tracking_guide')->middleware('deny.worker');
        Route::post('/manydial/convert-audio', 'convertManyDialAudio')->name('manydial.convert_audio')->middleware('deny.worker');
        Route::get('/profile', 'showProfile')->name('profile');
        Route::post('/profile-update', 'updateProfile')->name('profile.update');
        Route::get('/status-coupon','statusCoupon')->name('status.coupon');
        
        Route::get('/payment-settings', 'paymentSettings')->name('payment_settings');
    });

    Route::controller(ResetPasswordController::class)->group(function(){
        Route::get('/change-password', 'show')->name('password');
        Route::post('/update-password', 'updatePassword')->name('password.update');
    });

});

// LOCAL DEV ONLY — delete routes/dev_login.php and this block before deploy.
//
// The gate is now AND, not OR: the old version registered the route whenever
// APP_ENV was local *or* the Host header said localhost, and the Host header
// comes from the client — so a production server could be handed
// "Host: localhost" and hand back a password-free admin session. It also
// requires the connection itself to come from the machine running the app.
if (app()->environment('local')
    && in_array(request()->getHost(), ['127.0.0.1', 'localhost', '::1'], true)
    && in_array(request()->ip(), ['127.0.0.1', '::1'], true)) {
    require __DIR__ . '/dev_login.php';
}

// Category / sub-category pages live at the domain root: /{slug}.
// Must stay the last route in this file so it never shadows a real page.
Route::get('/{slug}', [FrontProduct::class, 'categoryPage'])->name('front.category');
