<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Hash;
use DB;

class AuthController extends Controller
{
    public function sellerRegister(){
        return view('auth.seller_register');
    }

    public function sellerRegisterPost(Request $request){
        $request->validate([
            'email' => 'nullable|email|unique:users',
            'username' => 'required|min:6|unique:users',
            'first_name' => 'required',
            'business_name' => '',
            'last_name' => 'required',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $data = $request->all();
        $user = $this->create($data);
        $user->is_seller=1;
        $user->status=1;
        $user->save();
        $user->assignRole('vendor');
        $url=session()->get('url');

        if (empty($url)) {
            $url= route('front.home');
        }

        return response()->json(['success'=>true,'msg'=>'Successfully Create Seller Account!', 'url'=>$url]);
    }


    /**
     * ============================
     * LOGIN (Modified)
     * ============================
     * আগের phone + otp বাদ দিয়ে
     * এখন সরাসরি email + password login হবে
     */
    public function login(Request $request){
        // Validate email + password
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Login attempt
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $url = session()->get('url');
            if (!empty($url)) {
                session()->forget('url');
                return redirect($url);
            }

            return redirect()->route('front.home');
        }

        return back()->withErrors(['email' => 'Invalid email or password']);
    }



    /**
     * =======================
     * OLD OTP SYSTEM (kept)
     * =======================
     * তুমি চাইলে পরে delete করবে
     * আমি এখানে একটাও ফাংশন বাদ দিইনি
     */
    public function getOpt(){
        $user_data=session()->get('user_data');
        if(empty($user_data)){
            return redirect()->route('login');
        }
        return view('auth.otp_verify');
    }

    public function optVerify(){
        $user_data=session()->get('user_data');
        date_default_timezone_set("Asia/Dhaka");

        if(empty($user_data)){
            return redirect()->route('login');
        }

        $exp_date = date('Y-m-d H:i:s');

        if(request('button')=='Save'){
            request()->validate([
                'otp_verify' => 'required',
            ]);

            if($user_data['otp_verify'] != request('otp_verify')){
                return back()->with('error_msg', 'PIN Is Not Match. please try again !');
            }

            if($user_data['exp_time']<$exp_date){
                return back()->with('error_msg', 'Time Is Expired!');
            }

            $user=User::where('mobile', $user_data['phone'])->first();
            if($user){
                Auth::loginUsingId($user->id);
                session()->put('user_data',[]);
                if(auth()->user()->type=='1'){
                    session()->put('cart',[]);
                }
                return redirect(url('/checkouts'))->with('success_msg', 'Login Success!');
            }else{
                $user=$this->createUser($user_data);
                if($user){
                    Auth::loginUsingId($user->id);
                    session()->put('user_data',[]);
                    return redirect(url('/checkouts'))->with('success_msg', 'Login Success!');
                }else{
                    return back()->with('error_msg', 'Something Went Wrong . try again !');
                }
            }

        } else if(request('button')=='Resend'){

            $date = date('Y-m-d H:i:s');
            $date = strtotime($date);
            $date = strtotime("+3 minute", $date);
            $new_date=date('Y-m-d H:i:s', $date);
            $otp=rand(100000,999999);
            $user_data['exp_time']=$new_date;
            $user_data['otp_verify']=$otp;
            session()->put('user_data', $user_data);

            $number=$user_data['phone'];
            $msg='Your One-Time PIN is '.$otp.'. It will expire in 3 minutes.Visit softitsecurity.com';

            $success=sendSMS($number ,$msg);
            $res=json_decode($success);

            if (isset($res->Status) && ($res->Status =='0')) {
                return redirect()->route('front.getOpt')->with('status','PIN Is Send Check Your Phone');
            }else{
                return back()->with('error_msg','OTP Is Send Check Your Phone');
            }
        }
    }

    private function createUser($data){
        $user=User::create([
            'mobile' => $data['phone'],
        ]);
        return $user;
    }



    /**
     * ===========================
     * Normal Register
     * ===========================
     */
    public function register(Request $request){
        $request->validate([
            'email' => 'nullable|email|unique:users',
            'username' => 'required|min:6|unique:users',
            'mobile' => 'nullable|digits:11|unique:users,mobile',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $data = $request->all();
        $user = $this->create($data);

        $url=session()->get('url');
        if (empty($url)) {
            $url= route('front.home');
        }

        // Log the new account straight in. Attempting a credential match on a
        // blank email used to fail here and leave an orphan account that then
        // blocked the customer from registering again.
        Auth::login($user);

        return response()->json(['success'=>true,'msg'=>'Successfully Register !', 'url'=>$url]);
    }


    /**
     * Create New User (email)
     */
    public function create(array $data)
    {
        // username and mobile were validated and then thrown away, so every
        // self-registered customer had both columns NULL. Since the whole
        // order pipeline matches customers by mobile, the same person got a
        // second user row created the first time they checked out.
        return User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'username'   => $data['username'] ?? null,
            'mobile'     => $data['mobile'] ?? null,
            'email'      => $data['email'] ?? null,
            'password'   => Hash::make($data['password']),
            'status'     => 1,
        ]);
    }
}
