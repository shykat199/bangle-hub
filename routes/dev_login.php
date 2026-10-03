<?php
// ⚠️ LOCAL DEV ONLY — এক ক্লিকে admin হিসেবে login (password ছাড়া)।
// শুধু 127.0.0.1/localhost-এ কাজ করে। কাজ শেষে এই file + web.php-র require লাইন মুছে ফেলবেন।
use Illuminate\Support\Facades\Route;

Route::get('/__dev-login', function (\Illuminate\Http\Request $request) {
    $host = strtolower($request->getHost());
    if (!in_array($host, ['127.0.0.1', 'localhost', '::1'])) {
        abort(404);
    }
    $user = \App\Models\User::find(1);
    if (!$user) abort(404, 'admin user (id=1) not found');
    auth()->login($user);
    return redirect('/admin/dashboard');
})->middleware('web');
