@extends('backend.app')
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Role Update</li>
                </ol>
            </div>
            <h4 class="page-title">Role Update</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <a href="{{route('admin.roles.index')}}" class="btn btn-secondary">Back</a><br><br>
                @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <strong>{{$error}}</strong>
                            @endforeach
                        </div>
                @endif
                <form action="{{route('admin.roles.update', $role->id)}}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <strong for="role">Role Name</strong>
                                <input type="text" id="role" class="form-control" name="name" placeholder="Role name..." value="{{$role->name}}">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12 mt-2">
                            <h4>Permissions</h4>
                            <label for="select_all"><input type="checkbox" id="select_all"> All Permissions</label>
                            @foreach($permissions as $permission)
                                <h4><label class="mb-1 mt-2"><input type="checkbox" class="permission-item" name="permissions[]" value="{{$permission->id}}" @if($permission->id == $permission->hasRole($role)) checked @endif> {{$permission->name}}</label></h4>
                            @endforeach
                        </div>
                    </div>
                    <hr>
                    <br>
                    <input type="submit" value="Save" class="btn btn-success">
                    <hr>
                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
        $("#select_all").change(function(){
            $(".permission-item").prop("checked", $(this).prop("checked"));
    
        });

        $(".permission-item").change(function(){
           if($(this).prop("checked") == false)
           {
                $("#select_all").prop("checked", false);
           }

          select_all();
    
        });

        function select_all()
        {
            if($('.permission-item:checked').length == $('.permission-item').length)
            {
                $("#select_all").prop("checked", true);
            }
        }

        select_all();
  
</script>

@endpush