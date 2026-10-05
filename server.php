<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
// Only real files are served as-is. A folder is never a page, and one of them
// (public/products, the product images) has the same name as the shop page
// route /products — so folders go to the application like any other URL.
if ($uri !== '/' && is_file(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
