@extends('backend.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Supplier Manage</li>
                </ol>
            </div>
            <h4 class="page-title">Supplier Manage</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">         
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-xl-8">
                        <form class="row gy-2 gx-2 align-items-center justify-content-xl-start justify-content-between">
                            <div class="col-auto">
                                <label for="inputPassword2" class="visually-hidden">Search</label>
                                <input type="search" class="form-control" id="inputPassword2" placeholder="Search..." name="q" value="{{ $q??''}}">
                            </div>
                            
                            <div class="col-auto">
                                <label for="submit" class="visually-hidden">Submit</label>
                                <input type="submit" class="form-control btn btn-sm btn-primary" id="submit" value="Submit">
                                
                            </div>
                        </form>                            
                    </div>
                    <div class="col-xl-4">
                        <div class="text-xl-end mt-xl-0 mt-2">
                        @can('product.create')
                            <a href="{{ route('admin.suppliers.create')}}" class="btn btn-danger mb-2 me-2 btn_modal"><i class="mdi mdi-basket me-1"></i> Add New Supplier</a>
                        @endcan
                        </div>
                    </div><!-- end col-->
                    
                </div>
                
                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th>Id </th>
                                <th style="width: 125px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=> $item)
                            <tr>
                                <td> {{$item->name}} </td>
                                <td> {{$item->mobile}} </td>
                                <td> {{$item->address}} </td>
                                <td> {{$item->contact_id}} </td>
                                <td>
                                @can('size.edit')
                                    <a href="{{ route('admin.suppliers.edit',[$item->id])}}" class="action-icon btn_modal"> 
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                @endcan
                                @can('size.delete')
                                    <a href="{{ route('admin.suppliers.destroy',[$item->id])}}" class="delete action-icon"> <i class="mdi mdi-delete"></i></a>
                                @endcan
                                
                                <a href="{{ route('admin.suppliers.show',[$item->id])}}" class="action-icon"> <i class="mdi mdi-details"></i></a>
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