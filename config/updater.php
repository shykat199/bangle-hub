<?php

return [

    'version' => '2.0',

    'master_server' => null,

    'site_code' => 'finalallproduct',

    /*
    |--------------------------------------------------------------------------
    | Protected Files (relative to project root)
    |--------------------------------------------------------------------------
    |
    | These files contain CLIENT-SIDE customizations (TikTok tracking, order
    | source detection, courier webhook auth, landing pages 14/15/16, FB+TT
    | pixel deduplication) that must SURVIVE master-server updates.
    |
    | During processUpdate(), each listed file is backed up before extracting
    | the new update.zip and restored afterwards — so a master push cannot
    | overwrite them. Add new entries here whenever you customize a CORE file.
    |
    | NOTE: Only list files that ALREADY EXIST on the client. New files
    | created by the master update (or by you) do NOT need to be listed.
    |
    */
    'protected_files' => [
        // ----- Update Guard self-protection (DO NOT REMOVE) -----
        'config/updater.php',
        'app/Http/Controllers/UpdateController.php',
        'vendor/composer/Support/ClassVersionLoader.php',

        // ----- Tracking / Pixel / Source -----
        'app/Models/Information.php',
        'app/Http/Kernel.php',
        'app/Providers/AppServiceProvider.php',
        'app/Http/Controllers/Backend/InformationController.php',
        'app/Http/Controllers/Frontend/CheckoutController.php',
        'app/Http/Controllers/Frontend/ProductController.php',
        'app/Http/Controllers/Backend/OrderController.php',
        'app/Http/Middleware/VerifyCsrfToken.php',

        // ----- Landing pages 14/15/16 + sidebar -----
        'app/Http/Controllers/Backend/LandingPageController.php',
        'routes/web.php',
        'resources/views/backend/partials/navbar.blade.php',

        // ----- Profit Calculator (uses existing classes — no protect needed for new files) -----
        // Service, Model, Controller, Views are NEW files — master won't ship them.

        // ----- Admin form (TikTok pixel + webhook tokens) -----
        'resources/views/backend/informations/index.blade.php',

        // ----- Order index source column -----
        'resources/views/backend/orders/received_order.blade.php',

        // ----- Landing pages: event-ID dedup + incomplete-order auto-save -----
        'resources/views/frontend/landing_pages/landing_page_nine.blade.php',
        'resources/views/frontend/landing_pages/landing_page_ten.blade.php',
        'resources/views/frontend/landing_pages/landing_page_eleven.blade.php',
        'resources/views/frontend/landing_pages/landing_page_twelve.blade.php',
        'resources/views/frontend/landing_pages/landing_page_thirteen.blade.php',
    ],

];
