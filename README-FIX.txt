FIX BUNDLE (2026-09-07) — Utility Box
======================================
Ei zip-er file gulo project root-e same path-e copy/replace korun.
Ekta notun migration file ache (database/migrations/...) — segulo copy korar
por live server-e SSH thakle:  php artisan migrate
SSH na thakle: sathe deওয়া ut-fix-2026-09-07.sql file-ta phpMyAdmin diye run
korun (একই কাজ, শুধু raw SQL দিয়ে করা)।

Copy korar por: php artisan view:clear   (optional, cache thakle)
Browser-e hard refresh (Ctrl+F5) diben.


0. [URGENT] Settings page save korte gele crash korto
   "SQLSTATE[23000]: Column 'instagram' cannot be null" — eirokom error die
   pura Settings save fail korto jokhon Facebook/Instagram/YouTube/Phone/
   Email/Address/Copyright/Site Name-er kono ekta faka rakha hoto.
   - app/Http/Controllers/Backend/InformationController.php
     (DB-te ei column gulo NOT NULL, kono default nai. Form-e faka rakhle
     Laravel "" -> NULL banaye dito -> DB crash. Ekhon faka rakhle
     empty string '' bosbe, crash korbe na.)
   *** Eita SABAR AGE copy korun, karon eta live site-e settings save
   completely block kore rekheche. ***

0b. Homepage-er "POPULAR CATEGORY"-er pashe je 2ta floating button dekhen
   (WhatsApp + Back-to-top) — actual site-e OI duita render hoy
   resources/views/frontend/app.blade.php theke ("premium-fab" naam-er
   button), header.blade.php-r "custom-floating-wa"/"custom-back-top" na
   (oigulo app.blade.php-r ekta CSS rule diye hide kora — display:none).
   Age ami bhul kore header.blade.php-r (dead/hidden) version-e color fix
   korechilam, tai change kono effect dekhachilo na. Ekhon thik jaygay
   (app.blade.php) fix kora hoyeche:
   - resources/views/frontend/app.blade.php
     (WhatsApp button ekhon "WhatsApp Button Background/Icon" field theke,
     Back-to-top button ekhon "Back-to-Top Background/Icon" field theke
     ashe — duitai Style page-er "Floating Buttons & Accents" section-e)


0c. Homepage-e "POPULAR CATEGORY" ar protyek category-r (jemon "GADGETS")
   heading text-er color — nijer alada dynamic color
   - database/migrations/2026_09_07_130000_add_category_heading_color_to_informations_table.php
     (notun column: category_heading_color)
   - app/Models/Information.php
   - app/Http/Controllers/Backend/InformationController.php
   - resources/views/backend/informations/style.blade.php
     ("Floating Buttons & Accents" section-e "Category Heading Text" field)
   - resources/views/frontend/home.blade.php
     (age hardcoded blue shimmer gradient silo, ekhon field-er color diye
     shimmer hoy. Dot-er pashe pulse glow ar divider line-o dot color-er
     halka (rgba) version use kore, jate mismatch na hoy)


1. Topbar background + text color dynamic kora hoyeche
   - database/migrations/2026_09_07_100000_add_topbar_colors_to_informations_table.php
     (notun column: topbar_bg_color, topbar_text_color)
   - app/Models/Information.php
   - app/Http/Controllers/Backend/InformationController.php
   - resources/views/backend/informations/index.blade.php
     (Settings > Topbar Announcement section-e 2ta color picker add hoyeche)
   - resources/views/frontend/partials/header.blade.php
     (age topbar bg/text hardcoded silo — kalo bg, shada text)

2. Nav menu-te "KITCHEN GADGETS"-er moto 2-word category name niche
   line-e wrap kore jaoya fix
   - resources/views/frontend/partials/header.blade.php
     (.nav-link-clean e white-space:nowrap add kora hoyeche)

3. Floating icon (back-to-top button) — nijer alada dynamic color
   - database/migrations/2026_09_07_110000_add_floating_and_dot_colors_to_informations_table.php
     (notun column: floating_btn_bg_color, floating_btn_icon_color, popular_dot_color)
   - app/Models/Information.php
   - app/Http/Controllers/Backend/InformationController.php
   - resources/views/backend/informations/style.blade.php
     (Front Page > Style page-e notun section: "Floating Buttons & Accents")
   - resources/views/frontend/partials/header.blade.php
     (age Primary Color/Gradient-er sathe joda silo — ekhon alada, nijer
     own color field theke ashe, Primary Color change korle r ei button
     change hobe na)

4. "POPULAR CATEGORY" heading-er pashe decorative dot — nijer alada dynamic color
   - resources/views/frontend/home.blade.php
     (age hardcoded blue gradient silo, tarpor Primary Color-er sathe joda
     hoyechilo — ekhon oitao alada, "Floating Buttons & Accents"-er
     "Popular Category Dot" field theke ashe)

4b. WhatsApp floating icon — nijer alada dynamic color
   - database/migrations/2026_09_07_120000_add_whatsapp_and_viewall_colors_to_informations_table.php
     (notun column: whatsapp_btn_bg_color, whatsapp_btn_icon_color,
     view_all_btn_bg_color, view_all_btn_text_color)
   - app/Models/Information.php
   - app/Http/Controllers/Backend/InformationController.php
   - resources/views/backend/informations/style.blade.php
     ("Floating Buttons & Accents" section-e "WhatsApp Button
     Background"/"Icon" field add hoyeche)
   - resources/views/frontend/partials/header.blade.php
     (age Primary Color-er sathe joda silo — ekhon alada. Hover-e age
     hardcoded #25D366-e switch hoto, ekhon shei same custom color-e
     brightness bariye hover effect dey, jate admin je color e din na
     kno hover-e ulta color-e jump na kore)

4c. Homepage-er protyek category section-er "View All" button — nijer alada
   dynamic color
   - resources/views/frontend/home.blade.php
     (age hardcoded blue gradient silo (#0d6efd -> #00276C), ekhon
     "Floating Buttons & Accents" section-er "View All" Button
     Background/Text field theke ashe. Shadow-o age blue-tinted silo,
     ekhon neutral kora hoyeche jate jekono color-e manai)

5. Product detail page-e WhatsApp + Call Now button add kora hoyeche
   - resources/views/frontend/products/show.blade.php
     (Add to Cart / Order Now-er pashe, Settings-e WhatsApp/Phone number
     thakle e show korbe)

6. Popular Category carousel — infinite, continuous motion
   - resources/views/frontend/home.blade.php
     (age Swiper carousel-ta 4.5 second pore-pore thomte thomte cholto,
     ekhon non-stop — ekta card-er transition shesh hoile shathe shathe
     porerta shuru hoye jabe, loop infinite)


7. OTP: 6 digit generate hoto kintu input 4 digit-e limited silo
   - app/Http/Controllers/Frontend/CheckoutController.php
     (random_int(100000,999999) -> random_int(1000,9999), checkout page-er
     maxlength="4" input o "4 digit code" message-er sathe match kora holo)

8. Cart sidebar quantity +/- button-e pura page reload hoye jeto
   - resources/views/frontend/partials/js.blade.php
     (AJAX-e sidebar refresh hoyar por o window.location.reload() call hoto —
     shetake bad deওয়া hoyeche)

9. Cart sidebar prottek page load-e khali dekhato
   - resources/views/frontend/partials/footer.blade.php
     (#cart-dropdown div khali render hoto, khali AJAX click-e bhorto.
     Ekhon server-side e render hoy, tai fresh page load-eo item+Checkout
     button thakbe)

10. "Order Now" fallback bhul route-e jeto (singular /checkout, 404)
    - resources/views/frontend/products/show.blade.php
    - resources/views/frontend/app.blade.php
      (routes/web.php-e route ache plural /checkouts — duitao ekhon thik)

11. Dashboard "Order Value" card sudhu Delivered order gunto
    - app/Http/Controllers/Backend/DashboardController.php
      (tai "Total Order" card-er number-er sathe milto na. Ekhon date range-er
      SOB order (status jai hok) er final_amount theke courier charge
      bad diye hishab hobe)

12. Coupon create/edit hoto na jodi "Minimum Purchase" faka rakha hoto
    - app/Http/Controllers/Backend/CouponCodeController.php
      (minimum_amount / per_customer_limit / total_limit column NOT NULL
      default 0, kintu faka form submit korle Laravel NULL banaye dito ->
      DB error die save fail korto, kono message chara. Ekhon faka rakhle
      0 boshbe.)

13. Admin panel-er form-e 500 error asle kono message na diye silently
    fail korto (coupon bug #12-o eijonyo invisible chilo)
    - public/backend/js/ajax.js
      (ajax_form-er generic error handler crash korto jokhon response-e
      .errors key thakto na — ekhon proper error message dekhabe)
    - resources/views/backend/partials/js.blade.php
      (ajax.js-e cache-bust ?v=filemtime add kora hoyeche, jate purono
      cached JS na thake browser-e)


Note: LandingPageController.php ba landing page blade file-gulo te kono
change lagenai — ei project-e product-remove ar delete-button-er bug
gulo already thik ache (shared processUpdate() method die shothikbhabe
handle kora, class-mismatch-o pawa jai nai).
