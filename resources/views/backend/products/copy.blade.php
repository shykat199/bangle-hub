@extends('backend.app')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Product Create From Copy</li>
                </ol>
            </div>
            <h4 class="page-title">Product Create From Copy</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.products.store')}}" id="ajax_form">
                    @csrf
                     
                    <div class="row">
                        
                        <div class="col-lg-4 mb-3">
                            <label  class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Product Name" value="{{ $item->name}}">
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label  class="form-label">Product Brand</label>
                            <select class="form-select" name="type_id">
                                <option value="">Select One</option>
                                @foreach($types as $type)
                                <option value="{{$type->id}}" {{$type->id == $item->type_id ?'selected':''}}> {{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label for="example-email" class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label for="example-email" class="form-label">Malti Image</label>
                            <input type="file" name="images[]" class="form-control" multiple>
                        </div>


                        <div class="col-lg-4 mb-3">
                            <label  class="form-label">Product Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">Select One</option>
                                @foreach($cats as $cat)
                                <option value="{{$cat->id}}" {{$cat->id == $item->category_id ?'selected':''}}> {{$cat->name}}</option>
                                @endforeach
                                    
                            </select>
                        </div>


                        <div class="col-lg-4 mb-3">
                            <label  class="form-label">Sub Category</label>
                            <select class="form-select" name="sub_category_id">
                                <option value="">Select One</option>
                                @foreach($subs as $sub)
                                <option value="{{$sub->id}}" {{$sub->id == $item->sub_category_id ?'selected':''}}> {{$sub->name}}</option>
                                @endforeach
                                
                            </select>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label  class="form-label">Purchase Price</label>
                            <input type="text" name="purchase_price" class="form-control" value="{{ $item->purchase_price}}">
                        </div>

                        <div class="col-lg-4 mb-3">
                            <label  class="form-label">Sell Price</label>
                            <input type="text" name="sell_price" class="form-control" value="{{ $item->sell_price}}">
                        </div>
                        
                        <div class="col-lg-4 mb-3">
                            <label  class="form-label">Regular Price</label>
                            <input type="text" name="regular_price" class="form-control" value="{{ $item->regular_price}}">
                        </div>

                        
                            
                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-8 offset-md-2">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Color</th>
                                            <th width="5">Action</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>

                                        @foreach($item->variations as $v)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="variation_id[]" value="{{$v->id}}">
                                                <select name="size_id[]" class="form-control">
                                                    @foreach($sizes as $size)
                                                    <option {{$size->id==$v->size_id ?'selected':''}} value="{{$size->id}}">{{ $size->title }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <select name="color_id[]" class="form-control">
                                                    @foreach($colors as $color)
                                                    <option {{$color->id==$v->color_id ?'selected':''}} value="{{$color->id}}">{{$color->name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <a class="action-icon btn-primary add_moore"><i class="mdi mdi-plus"></i> </a>
                                                <a class="action-icon btn-danger remove"><i class="mdi mdi-delete"></i> </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    
                                </table>
                            </div>
                        </div>


                        <div class="col-lg-12">
                            <label  class="form-label">Feature</label>
                            <textarea class="form-control" name="feature" rows="5">{!! $item->feature !!}</textarea>
                        </div>

                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label  class="form-label">Product Body</label>
                                <textarea class="form-control" name="body" rows="5">{!! $item->body !!}</textarea>
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
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 

@push('js')
<script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
    CKEDITOR.replace('body', {
        filebrowserUploadUrl: "{{route('admin.ckeditor.upload', ['_token' => csrf_token() ])}}",
        filebrowserUploadMethod: 'form'
    });
    CKEDITOR.replace('feature');

    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });

    $("document").ready(function () {
        $('select[name="category_id"]').on('change', function () {
            var cat_id = $(this).val();
            if (cat_id) {
                $.ajax({
                    url: '{{ route("admin.getSubcategory")}}',
                    type: "GET",
                    dataType: "json",
                    data:{cat_id},
                    success: function (data) {
                        $('select[name="sub_category_id"]').empty();
                        $.each(data, function (key, value) {
                            $('select[name="sub_category_id"]').append('<option value=" ' + key + '">' + value + '</option>');
                        })
                    }

                })
            } else {
                $('select[name="sub_category_id"]').empty();
            }
        });

        // add moore

        $(document).on('click','a.add_moore', function(){
            let type='{{ $item->type}}';

            if (type=='single') {
                toastr.error('For Single Product You Can\'t Add Moore');
                return;
            }
            let row=`<tr>
                        <td>
                            <select name="size_id[]" class="form-control">
                                @foreach($sizes as $size)
                                <option value="{{$size->id}}">{{ $size->title }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <select name="color_id[]" class="form-control">
                                @foreach($colors as $color)
                                <option  value="{{$color->id}}">{{$color->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <a class="action-icon btn-primary add_moore"><i class="mdi mdi-plus"></i> </a>
                            <a class="action-icon btn-danger remove"><i class="mdi mdi-delete"></i> </a>
                        </td>
                    </tr>`;
            $(document).find('.table tbody').append(row);

        });

        $(document).on('click', "a.remove",function(e) {
            var whichtr = $(this).closest("tr");
            whichtr.remove();      
        });


    });

</script>

@endpush