<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Contact;
use App\User;
use App\Mail\SendOtpMail;

use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use Mail;

class PasswordResetController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_data=session()->get('user_data');
        
        // dd($user_data);
        
        if(empty($user_data)){
            return redirect()->action('PasswordResetController@register');
        }
        return view('auth.otp_verify');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('auth.passwords.mobile');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data=$this->validate($request, [            
          'phone' => 'required|numeric|digits:11|exists:ecom_users|regex:/(01)[0-9]{9}/',
        ]);
        $user=User::where('phone', request('phone'))->first();

        $otp=rand(100000,999999);
        $number=request('phone');
        $msg='Your OTP Numbre Is :'.' '.$otp.'. Visit excellentfood.com.bd';

        $success=sendSMS($number ,$msg);
        $res=json_decode($success);

        if (isset($res->Status) && ($res->Status =='0')) {
            $user->otp=$otp;
            $user->save();
            return redirect()->action('PasswordResetController@resetPage', compact('number'))->with('status','OTP Is Send Check Your Phone and submit for verify your account');
        }else{
            
            return back()->with('success_msg', 'Something Went Wrong . try again Later !');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resetPage()
    {
        return view('auth.passwords.reset_mobile');
    }

    public function resetPassword(Request $request){

        $this->validate($request, [            
          'phone' => 'required|numeric|digits:11|exists:ecom_users|regex:/(01)[0-9]{9}/',
          'otp' => 'required|numeric',
          'password' => 'required|confirmed|min:6',
        ]);


        $user=User::where(['phone'=>request('phone'),'otp'=>request('otp')])->first();

        if ($user) {
            $user->password = Hash::make(request('password'));
            $user->otp=null;
            $user->save();

            return redirect(url('/login'))
                            ->with('success_msg', 'Password Updated. You can Login !');

        }else{
            
            return back()->with('status', 'User Not Found !');
        }


    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function register(){
        date_default_timezone_set("Asia/Dhaka");
        
        
        $date = date('Y-m-d H:i:s');
        $date = strtotime($date);
        $date = strtotime("+2 minute", $date);
        $new_date=date('Y-m-d H:i:s', $date);
        $otp=rand(100000,999999);
        
        $data=request()->validate([
            'phone' => 'required|numeric|digits:11|regex:/(01)[0-9]{9}/',
        ]);
        
        
        $data['otp_verify']=$otp;
        $data['exp_time']=$new_date;
        
        session()->put('user_data', $data);
        $number=request('phone');
        $msg='Your OTP Numbre Is :'.' '.$otp.'. Otp is Expired After 2 Minutes.Visit excellentfood.com.bd';
        $success=sendSMS($number ,$msg);
        $res=json_decode($success);
    
        if (isset($res->Status) && ($res->Status =='0')) {
            
            return redirect()->action('PasswordResetController@index')->with('status','OTP Is Send Please Check Your Phone');
        }else{
            return back()->with('success_msg', 'Something Went Wrong . try again Later !');
        }
        
        
    }
    
    public function optVerify(){
        
        $user_data=session()->get('user_data');
        date_default_timezone_set("Asia/Dhaka");
        
        
        if(empty($user_data)){
            return redirect()->action('PasswordResetController@register');
        }
        
        $exp_date = date('Y-m-d H:i:s');
        if(request('button')=='Save'){
            request()->validate([
                'otp_verify' => 'required',
            ]);
            
            
            if($user_data['otp_verify'] != request('otp_verify')){
                return back()->with('error_msg', 'Opt Is Not Match. please try again !');
            }
            
            if($user_data['exp_time']<$exp_date){
                return back()->with('error_msg', 'Time Is Expired!');
            }
            
            $user=User::where('phone', $user_data['phone'])->first();
            if($user){
                Auth::loginUsingId($user->id);
                session()->put('user_data',[]);
                
                if(auth()->user()->type=='1'){
                    session()->put('cart',[]);
                }
                    
                return redirect(url('/checkout'))->with('success_msg', 'Login Success!');
            }else{
                $user=$this->createUser($user_data);
                if($user){
                    Auth::loginUsingId($user->id);
                    session()->put('user_data',[]);
                    
                    if(auth()->user()->type=='1'){
                        session()->put('cart',[]);
                    }
                    
                    return redirect(url('/checkout'))->with('success_msg', 'Login Success!');
                }else{
                    
                    return back()->with('error_msg', 'Something Went Wrong . try again !');
                }
            }
            
        
            
        } else if(request('button')=='Resend'){
            
                
            $date = date('Y-m-d H:i:s');
            
            
            $date = strtotime($date);
            $date = strtotime("+2 minute", $date);
            
           
            $new_date=date('Y-m-d H:i:s', $date);
            
        
            $otp=rand(100000,999999);
            $user_data['exp_time']=$new_date;
            $user_data['otp_verify']=$otp;
            
            
            session()->put('user_data', $user_data);
            
            $number=$user_data['phone'];
            $msg='Your OTP Numbre Is :'.' '.$otp.'. Otp is Expired After 2 Minutes.Visit excellentfood.com.bd';
    
            $success=sendSMS($number ,$msg);
            $res=json_decode($success);
        
            return redirect()->action('PasswordResetController@index')->with('status','OTP Is Send Check Your Phone And Mail');
            
            
            
        }
    }
    
    private function createUser($data){
        
        
        $customer_array=[
                'business_id' => 9,
                'created_by' => 36,
                'type' => 'customer',
                'customer_type' => 'general',
                'name' =>$data['phone'],
                'mobile' => $data['phone'],
                'contact_id' => $data['phone'],
            ];

        $customer=Contact::create($customer_array);
        
        $user=User::create([
            'customer_id' => $customer->id,
            'phone' => $data['phone'],
        ]);
        
        return $user;
        
        
    }
    
}
