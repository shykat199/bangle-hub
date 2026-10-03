@extends('backend.app')
@section('content')

<style>
 th, td, h4, .pur_list, .form-label {
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
                    <li class="breadcrumb-item active pur_list">Purchase List</li>
                </ol>
            </div>
            <h4 class="page-title">Purchase List</h4>
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
                        <form class="row align-items-center" style="float: right;">
                            <div style="width:60%;margin-top: 0px;">
                                <label for="inputPassword2" class="visually-hidden">Search</label>
                                <input type="search" class="form-control" id="inputPassword2" placeholder="Search..." name="q" value="{{ request('q')??''}}">
                            </div>
                            <div class="col-auto" style="margin-top: 0px;" >
                                <label for="submit" class="visually-hidden">Submit</label>
                                <input type="submit" class="form-control btn btn-sm btn-primary" id="submit" value="Submit">
                            </div>
                        </form>
                    </div>
                    <div class="col-xl-4">
                        <div class="text-xl-end mt-xl-0 mt-2">
                            @can('purchase.create')
                                <a href="{{ route('admin.purchase.create')}}" class="btn btn-danger mb-2 me-2"><i class="mdi mdi-basket me-1"></i> Add New Purchase</a>
                            @endcan
                            <button type="button" class="btn btn-light mb-2" style="color: black;">Export</button>
                        </div>
                    </div><!-- end col-->
                </div>

                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Ref</th>
                               <!-- <th>Supplier</th> -->
                                <th>Status</th>
                                <th>Amount</th>
                                
                                <th>Note</th>
                                <th style="width: 125px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>{{$item->date}} </td>
                                <td><a class="text-body fw-bold">#{{$item->ref}}</a> </td>
                              
                                <td><h5 class="my-0"><span class="badge badge-info-lighten">{{$item->status}}</span></h5></td>
                                <td>{{$item->amount}}</td>
                                <td>{{$item->note}}</td>
                                
                                <td>
                                    <a href="{{ route('admin.purchase.show',[$item->id])}}" class="action-icon"> <i class="mdi mdi-details"></i></a>
                                @can('purchase.edit')
                                    <a href="{{ route('admin.purchase.edit',[$item->id])}}" class="action-icon"> <i class="mdi mdi-square-edit-outline"></i></a>
                                @endcan
                                @can('purchase.delete')
                                    <a href="{{ route('admin.purchase.destroy',[$item->id])}}" class="delete action-icon"> <i class="mdi mdi-delete"></i></a>
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