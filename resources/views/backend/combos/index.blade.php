@extends('backend.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Combo OfferManage</li>
                </ol>
            </div>
            <h4 class="page-title">Combo OfferManage</h4>
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
                                <label  class="visually-hidden">Search</label>
                                <input type="search" class="form-control" placeholder="Search..." name="q" value="{{ $q??''}}">
                            </div>
                            
                            <div class="col-auto">
                                <label for="submit" class="visually-hidden">Submit</label>
                                <input type="submit" class="form-control btn btn-sm btn-primary" id="submit" value="Submit">
                                
                            </div>
                        </form>
                        
                    </div>
                    <div class="col-xl-4">
                        <div class="text-xl-end mt-xl-0 mt-2">
                        @can('combo.create')
                            <a href="{{ route('admin.combos.create')}}" class="btn btn-danger mb-2 me-2"><i class="mdi mdi-basket me-1"></i> Add New Combo Product</a>
                        @endcan
                        </div>
                    </div><!-- end col-->
                </div>
                
                <div class="row">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Image</th>
                                    <th>Total Item</th>
                                    <th style="width: 125px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $key=> $item)
                                <tr>
                                    <td> {{$item->product->name}} </td>
                                    <td>
                                        <img src="{{ getImage('products',$item->product->image)}}" width="180">    
                                    </td>
                                    <td> {{$item->products->count()}} </td>
                                    <td>
                                    @can('combo.edit')
                                        <a href="{{ route('admin.combos.edit',[$item->id])}}" class="action-icon"> 
                                            <i class="mdi mdi-square-edit-outline"></i>
                                        </a>
                                    @endcan
                                    @can('combo.delete')
                                        <a href="{{ route('admin.combos.destroy',[$item->id])}}" class="delete action-icon"> <i class="mdi mdi-delete"></i></a>
                                    @endcan
                                    @can('combo.view')
                                    <a href="{{ route('admin.combos.show',[$item->id])}}" class="action-icon btn_modal"> <i class="mdi mdi-details"></i></a>
                                    @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p>{!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}</p>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 