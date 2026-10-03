@extends('backend.app')

@php
    // Information টেবিল থেকে কালার ডাটাগুলো নিয়ে আসার জন্য
    $info = \App\Models\Information::first();
@endphp

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Dynamic Frontend Text & Colors</h4>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- ✅ ROUTE FIXED --}}
            <form method="POST" action="{{ route('admin.dynamic_text.update') }}">
                @csrf

                <h5 class="mb-3 border-bottom pb-2">General Texts</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>জনপ্রিয় ক্যাটাগরি</label>
                        <input type="text" class="form-control"
                               name="popular_category_title"
                               value="{{ $text->popular_category_title ?? '' }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>View All Text</label>
                        <input type="text" class="form-control"
                               name="view_all_text"
                               value="{{ $text->view_all_text ?? '' }}">
                    </div>
                </div>

                <h5 class="mb-3 mt-4 border-bottom pb-2">Button & Tab Texts</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Add to Cart Text</label>
                        <input type="text" class="form-control"
                               name="add_to_cart_text"
                               value="{{ $text->add_to_cart_text ?? '' }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Order Now Text</label>
                        <input type="text" class="form-control"
                               name="order_now_text"
                               value="{{ $text->order_now_text ?? '' }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Call Button Text</label>
                        <input type="text" class="form-control" 
                               name="call_btn_text" 
                               value="{{ $text->call_btn_text ?? 'Call' }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Whatsapp Button Text</label>
                        <input type="text" class="form-control" 
                               name="whatsapp_btn_text" 
                               value="{{ $text->whatsapp_btn_text ?? 'Whatsapp' }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Submit Review Text</label>
                        <input type="text" class="form-control" 
                               name="submit_review_btn_text" 
                               value="{{ $text->submit_review_btn_text ?? 'Submit Review' }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Details Tab Text</label>
                        <input type="text" class="form-control" 
                               name="details_tab_text" 
                               value="{{ $text->details_tab_text ?? 'Details' }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Reviews Tab Text</label>
                        <input type="text" class="form-control" 
                               name="reviews_tab_text" 
                               value="{{ $text->reviews_tab_text ?? 'Reviews' }}">
                    </div>
                </div>

                <h5 class="mb-3 mt-4 border-bottom pb-2">Button Colors Settings</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Common Button BG</label>
                        <input type="color" name="common_btn_color" class="form-control" style="height: 45px; padding: 5px;" value="{{ $info->common_btn_color ?? '#198754' }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label>Common Button Text</label>
                        <input type="color" name="common_btn_text_color" class="form-control" style="height: 45px; padding: 5px;" value="{{ $info->common_btn_text_color ?? '#ffffff' }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label>Order Now BG</label>
                        <input type="color" name="order_now_btn_color" class="form-control" style="height: 45px; padding: 5px;" value="{{ $info->order_now_btn_color ?? '#0f172a' }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label>Order Now Text</label>
                        <input type="color" name="order_now_btn_text_color" class="form-control" style="height: 45px; padding: 5px;" value="{{ $info->order_now_btn_text_color ?? '#ffffff' }}">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-5">Save Changes</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection