<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;

class AuthController extends Controller
{
    public function login()
    {
        if (auth()->user()) {
            return redirect()->route('admin.dashboard');
        }
        return view('backend.auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();
        
        if ($user && $user->status == null) {
            return redirect("admin")->withSuccess('Oppes! Your account is deactivated. Please contact admin.');
        }

        $credentials = $request->only('username', 'password');
        $remember = $request->has('remember') ? true : false;
        
        if (Auth::attempt($credentials, $remember)) {
            
            if(isset($_COOKIE['user'])) {
                setcookie("user", "", time() - 3600, '/');
            }
            if(isset($_COOKIE['pass'])) {
                setcookie("pass", "", time() - 3600, '/');
            }
            
            return redirect()->intended('admin/dashboard')
                        ->withSuccess('You have Successfully loggedin');
        }

        return redirect("admin")->withSuccess('Oppes! You have entered invalid credentials');
    }
}