<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Hash;
use DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->can('user.view'))
        {
            abort(403, 'unauthorized');
        }
        $s=request('q');
        $r=request('role');
        $query = User::with('roles')->whereHas('roles', function($query){
          $query->whereNotNull('roles.name');
        });
        
        if(!empty($s)){
            $query->where(function($row) use($s){
               $row->where('first_name', 'Like','%'.$s.'%') 
                    ->orwhere('last_name', 'Like','%'.$s.'%')
                    ->orwhere('email', 'Like','%'.$s.'%')
                    ->orwhere('username', 'Like','%'.$s.'%'); 
            });
        }  

        if(!empty($r)){
            $query->whereHas('roles', function($q) use ($r){
               $q->where('roles.name', 'Like','%'.$r.'%');
            });
        }
        
        $users=$query->paginate(30);
        $roles = Role::select('id', 'name')->get();
        return view('backend.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('user.create'))
        {
            abort(403, 'unauthorized');
        }

        $roles = Role::all();

        return view('backend.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('user.create'))
        {
            abort(403, 'unauthorized');
        }

        $data = $request->validate([
            'first_name' => 'required|min:3',
            'last_name' =>  'required|min:3',
            'email' =>  'required|unique:users',
            'username' =>  'required|unique:users,username',
            'password' =>  'required|min:8|same:confirm_password',
            'role' =>  'required',
        ]);

        $user = new User;
        $user->business_name = $request->business_name;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->confirm_password);
        
        // নতুন ইউজার ক্রিয়েট করার সময় বাই-ডিফল্ট Active (1) থাকবে
        $user->status = 1; 
        
        $user->save();

        // Form sends the role ID — resolve it to a Role model so Spatie doesn't
        // look it up as a role NAME (caused: "There is no role named `8`").
        $role = is_numeric($request->role)
            ? \Spatie\Permission\Models\Role::findById((int) $request->role, 'web')
            : $request->role;
        $user->assignRole($role);

        return response()->json(['status'=>true ,'msg'=>'User has been created !!','url'=>route('admin.users.index')]);
    }
    
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
    public function edit($id)
    {
        if(!auth()->user()->can('user.edit'))
        {
            abort(403, 'unauthorized');
        }

        // সিকিউরিটি চেক: Main Admin (ID 1) কে অন্য কেউ এডিট করতে পারবে না
        if($id == 1 && auth()->id() != 1) {
            abort(403, 'You are not allowed to edit the Main Admin account!');
        }

        $user = User::findOrFail($id);
        $roles = Role::all();

        return view('backend.users.edit', compact('user', 'roles'));
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
        if(!auth()->user()->can('user.edit'))
        {
            abort(403, 'unauthorized');
        }

        // সিকিউরিটি চেক: Main Admin (ID 1) কে অন্য কেউ আপডেট করতে পারবে না
        if($id == 1 && auth()->id() != 1) {
            return response()->json(['status'=>false ,'msg'=>'You are not allowed to update the Main Admin!']);
        }

        $data = $request->validate([
            'first_name' => 'required|min:3',
            'last_name' =>  'required|min:3',
            'email' =>  'required',
            'username' =>  'required|unique:users,username,'.$id,
            'business_name' =>  '',
            'role' =>  'required',
        ]);

        $user = User::findOrFail($id);
        $user->first_name = $request->first_name;
        $user->business_name = $request->business_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->username = $request->username;
        
        // --- পাসওয়ার্ড আপডেটের রেস্ট্রিকশন ---
        if(!empty($request->password))
        {
            if(auth()->id() == 1) { 
                $user->password = Hash::make($request->password);
            } else {
                return response()->json(['status'=>false ,'msg'=>'Only Main Admin can change passwords!']);
            }
        }
        
        $user->save();
  
        $user->roles()->detach();

        // Form sends the role ID — resolve it to a Role model so Spatie doesn't
        // look it up as a role NAME (caused: "There is no role named `8`").
        $role = is_numeric($request->role)
            ? \Spatie\Permission\Models\Role::findById((int) $request->role, 'web')
            : $request->role;
        $user->assignRole($role);

        return response()->json(['status'=>true ,'msg'=>'User has been Updated !!','url'=>route('admin.users.index')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('user.delete'))
        {
            abort(403, 'unauthorized');
        }

        // সিকিউরিটি চেক: Main Admin কে কেউ ডিলিট করতে পারবে না
        if($id == 1) {
            return response()->json(['status'=>false, 'msg' => 'Main Admin cannot be deleted!']);
        }

        User::destroy($id);

        return response()->json(['status'=> true, 'msg' => 'User has been deleted']);
    }
    
    public function userStatusUpdate(){
        // সিকিউরিটি চেক: Main Admin এর স্ট্যাটাস কেউ চেঞ্জ করতে পারবে না
        if(in_array(1, request('user_ids'))) {
            return response()->json(['status'=> false, 'msg' => 'Main Admin status cannot be changed!']);
        }

        $status = request('status') == '1' ? null : 1;
        DB::table('users')->whereIn('id', request('user_ids'))->update(['status'=>$status]);
        
        return response()->json(['status'=> true, 'msg' => 'User status has been Updated']);
    }
}