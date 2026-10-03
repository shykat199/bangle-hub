<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      	$q=request()->q;
        $query = Permission::query();
      			if(!empty($q)){
                    $query->where(function($row) use ($q){
                        $row->where('name','Like','%'.$q.'%');
                    });
                }
      
      	$permissions=$query->orderBy('name')->paginate(30);
        return view('backend.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->can('permission.create'))
        {
            abort(403, 'unauthorized');
        }

        return view('backend.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('permission.create'))
        {
            abort(403, 'unauthorized');
        }

        $request->validate([
            'name' => 'required|unique:permissions',
        ], [
            'name.required' => 'The permission name field is required.',
            'name.unique' => 'The permission name has already been taken.',
        ]);

       Permission::create(['name' => $request->name]);
       
      return response()->json(['status'=>true ,'msg'=>'Permission  Is Created !!']);
        
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
    public function edit($id)
    {
        if(!auth()->user()->can('permission.edit'))
        {
            abort(403, 'unauthorized');
        }

        $permission = Permission::findOrFail($id);

        return view('backend.permissions.edit', compact('permission'));
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
        if(!auth()->user()->can('permission.edit'))
        {
            abort(403, 'unauthorized');
        }

        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'The permission name field is required.',
        ]);

       Permission::where('id', $id)->update(['name' => $request->name]);
       
       return redirect()->route('admin.permissions.index')->with(['permission_updated' => 'Permission updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('permission.delete'))
        {
            abort(403, 'unauthorized');
        }

        Permission::destroy($id);

        return response()->json(['status'=> true, 'msg' => 'Permission deleted']);
    }
}
