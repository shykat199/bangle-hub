@extends('backend.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Role List</li>
                </ol>
            </div>
            <h4 class="page-title">Role List</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if(Session::has('success'))
                    <div class="alert alert-success">
                        <strong>{{Session::get('success')}}</strong>
                    </div>
                @endif
                <br>
                <div class="row mb-2">
                    <div class="col-xl-4">
                        <div class="text-xl-end mt-xl-0 mt-2">
                                @can('role.create')
                                <a type="button" href="{{route('admin.roles.create')}}" class="btn btn-danger mb-2 me-2"><i class="mdi mdi-basket me-1"></i> Add New Role</a>
                                @endcan
                        
                            <button type="button" class="btn btn-light mb-2">Export</button>
                        </div>
                    </div><!-- end col-->
                </div>

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Role</th>
                                <th style="width: 125px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <td>{{$role->name}}</td>
                                <td>
                                @can('role.edit')
                                <a href="{{ route('admin.roles.edit',[$role->id])}}" class="action-icon"> <i class="mdi mdi-square-edit-outline"></i></a>
                                @endcan
                                @can('role.delete')
                                    @unless(in_array($role->id, [6, 8]))
                                        <a href="{{ route('admin.roles.destroy', [$role->id]) }}" class="delete action-icon">
                                            <i class="mdi mdi-delete"></i>
                                        </a>
                                    @endunless
                                @endcan
                             
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{$roles->links()}}
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 