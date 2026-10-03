@extends('backend.app')
@section('content')

<style>
 th, td, h4, .sl_manage, .form-label {
  	color: black !important;
  }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active sl_manage">Social  Manage</li>
                </ol>
            </div>
            <h4 class="page-title">Social  Manage</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-5">            
        @can('size.create')
        <div class="card">
            <div class="card-header">
                <h4> Social  Create</h4>
            </div>
            <div class="card-body">
   
                <form method="POST" action="{{ route('admin.social_icons.store')}}" id="ajax_form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label  class="form-label">Social  Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Social  Name">
                            </div>

                        </div>
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label  class="form-label">Social  Icon</label>
                                <input type="file" name="icon" class="form-control">
                            </div>

                        </div>
                        
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label  class="form-label">Social  Link</label>
                                <input type="text" name="link" class="form-control" placeholder="Social  Link">
                            </div>

                        </div>
                        

                        <div class="col-lg-12">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>

                </form>
            
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div>   
    @endcan
    <div class="col-7">
        <div class="card">
            <div class="card-body">
   

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Image</th>
                                <th>Link</th>
                                <th style="width: 125px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=> $item)
                            <tr>
                                <td> {{$item->title}} </td>
                                <td>
                                    <img src="{{ getImage('social_icons',$item->image)}}" width="220">    
                                </td>
                                <td> {{$item->link}} </td>
                                <td>
                                @can('size.edit')
                                    <a href="{{ route('admin.social_icons.edit',[$item->id])}}" class="action-icon btn_modal"> 
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                @endcan
                                @can('size.delete')
                                    <a href="{{ route('admin.social_icons.destroy',[$item->id])}}" class="delete action-icon"> <i class="mdi mdi-delete"></i></a>
                                @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 