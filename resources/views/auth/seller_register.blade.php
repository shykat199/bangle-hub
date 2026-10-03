@extends('frontend.app')
@section('content')
<main class="main-wrapper">

    <!-- Start Checkout Area  -->
    <div class="axil-checkout-area axil-section-gap">
        <div class="container">
            <form action="{{ route('front.sellerRegisterPost') }}" method="POST" id="ajax_form">
                @csrf

                <div class="row">
                    <div class="col-lg-6 offset-lg-3">
                        <div class="card-body signin-body" style="border-radius: 0;">
                        <div class="axil-checkout-billing">
                            <h2 class="serif text-center">Seller Account Create</h2>
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>First Name <span>*</span></label>
                                        <input type="text" id="first-name" placeholder="Adam" name="first_name">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Last Name <span>*</span></label>
                                        <input type="text" id="last-name" placeholder="John" name="last_name">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Business Name <span>*</span></label>
                                <input type="text" name="business_name">
                            </div>
                            
                            <div class="form-group">
                                <label>Username <span>*</span></label>
                                <input type="text" name="username">
                            </div>


                            <div class="form-group">
                                <label>Phone <span>*</span></label>
                                <input type="tel" id="phone" name="mobile">
                            </div>

                            <div class="form-group">
                                <label>Email Address <span>*</span></label>
                                <input type="email" id="email" name="email">
                            </div>


                            <div class="form-group">
                                <label>PAssword <span>*</span></label>
                                <input type="password" id="password" name="password" placeholder="Enter Password Here">
                            </div>

                            <div class="form-group">
                                <label>Confirm Password <span>*</span></label>
                                <input type="password" name="password_confirmation" placeholder="Re-Enter Password Here">
                            </div>

                            <div class="d-flex justify-content-between">
                              <a href="{{ route('password.request') }}" class="font-m-sm text-muted" style="text-decoration: underline;">Forgot Password</a>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="p-3 border col-12 mt-2" style="background-color: #041e3a; color: white;">Create Seller Account</button>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
    <!-- End Checkout Area  -->

</main>

@endsection

@push('js')
<script src="{{ asset('frontend/js/checkout.js')}}"></script>

@endpush
