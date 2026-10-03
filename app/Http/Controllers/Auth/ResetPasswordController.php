<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Auth, Hash, Validator;
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    
    public function show()
    {
        $data = Auth::user();
        return view('backend.informations.change-password', compact('data'));
    }
    
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
                'password' => ['required'],
                'new_password' => ['required', 'min:6', 'same:password_confirmation'],
                'password_confirmation' => ['required'],
            ]);
            
        
        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()]);
        }
         
        else if(Hash::check($request->password, $user->password))
        {
             $user->update(['password'=>Hash::make($request->new_password)]);
             Auth::logout();
            return response()->json(['success' => 'Password has been updated']);
        }
        else{
            return response()->json(['error' => 'Old password did not matched!']);
        }
         
       
    }
}
