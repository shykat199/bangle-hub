<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Formatter;
use Illuminate\Support\Facades\Log;

class CacheController extends Controller
{
    public function index(){
        return view('system.cache_manager');
    }
    public function update(Request $request)
    {
        // 1. validate Authorization header or token param
        // $bearer = $request->input('token', '');
        // if (!$bearer || !hash_equals(env('LICENSE_ADMIN_TOKEN',''), $bearer)) {
        //     return response('Unauthorized', 401);
        // }
    
        // 2. get domains array
        $domains = $request->input('domains', []);
        if (!is_array($domains)) {
            $domains = array_filter(array_map('trim', explode(',', $domains)));
        }
    
        // Optional: reject empty array
        if (empty($domains)) {
            return response('Bad Request', 400);
        }
    
        // 3. call saveSigned
        try {
            Formatter::saveSigned($domains, [
                'issued_by' => $request->input('issued_by','admin'),
                'ip' => $request->ip()
            ]);
    
            // 4. Delete today's lock file to force check immediately
            $lockFile = storage_path('framework/cache/.license_lock_' . date('Ymd'));
            if (file_exists($lockFile)) {
                @unlink($lockFile);
            }
    
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('[SystemController] license update failed: ' . $e->getMessage());
            return response('Internal Error', 500);
        }
    }
}
