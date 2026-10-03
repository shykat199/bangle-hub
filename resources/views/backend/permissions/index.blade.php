@extends('backend.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Permission List</li>
                </ol>
            </div>
            <h4 class="page-title">Permission List</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
  	<div class="col-6">
        <div class="card">
            <div class="card-body">
                <h4> Permission Create</h4>
                
                <form action="{{route('admin.permissions.store')}}" method="POST" id="ajax_form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <input type="text" id="permission" class="form-control" name="name" placeholder="Enter permission name...">
                            </div>
                        </div>
                    </div>
                    <input type="submit" value="Save" class="btn btn-success">
                    <hr>
                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
    <div class="col-6">
        <div class="card">
            <div class="card-body">
             	 <h4> Permission List</h4>
                <div class="row mb-2">
                    <div class="col-xl-8">
                        <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between">
                            <div class="col-auto">
                                <label for="inputPassword2" class="visually-hidden">Search</label>
                                <input type="search" class="form-control" id="inputPassword2" placeholder="Search..." name="q" value="{{ request('q')??''}}">
                            </div>
                            
                            <div class="col-auto">
                                <label for="submit" class="visually-hidden">Submit</label>
                                <input type="submit" class="form-control btn btn-sm btn-primary" id="submit" value="Submit">
                                
                            </div>
                        </form>
                        
                    </div>
                    
                    <div class="col-xl-4">
                        <div class="text-xl-end mt-xl-0 mt-2">
                             @can('permission.create')
                                <a type="button" href="{{route('admin.permissions.create')}}" class="btn btn-danger mb-2 me-2"><i class="mdi mdi-basket me-1"></i> Add Permission</a>
                             @endcan
                            
                        </div>
                    </div><!-- end col-->
                </div>

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Permission</th>
                                <th style="width: 125px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                            <tr>
                                <td>{{$permission->name}}</td>
                                <td>
                                @can('permission.edit')
                                <a href="{{ route('admin.permissions.edit',[$permission->id])}}" class="action-icon"> <i class="mdi mdi-square-edit-outline"></i></a>
                                @endcan
                                @can('permission.delete')
                                    <a href="{{ route('admin.permissions.destroy',[$permission->id])}}" class="delete action-icon"> <i class="mdi mdi-delete"></i></a>
                                @endcan
                               
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{$permissions->links()}}
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 