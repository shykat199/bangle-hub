@extends('frontend.app')
@section('content')


<main class="main-wrapper">
    <!-- Start Breadcrumb Area  -->
    <div class="axil-breadcrumb-area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="inner">
                        <ul class="axil-breadcrumb">
                            <li class="axil-breadcrumb-item"><a href="{{ route('front.home')}}">Home</a></li>
                            <li class="separator"></li>
                            <li class="axil-breadcrumb-item active" aria-current="page">Contact</li>
                        </ul>
                        <h1 class="title">Contact Info</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb Area  --> 

    <!-- Start Contact Area  -->
    <div class="axil-contact-page-area">
        <div class="container">
            <div class="axil-contact-page">
                <div class="row row--30">
                    <div class="col-lg-6">
                        <div class="contact-form">
                            
                            <p class="m-0">Location : </p>
                            <p class="m-0">Phone : <a href="tel:" style="color:red"></a></p>
                            <p class="m-0">E-mail Address : <a href="mailto:" style="color:red"></a></p>
                            <h2 class="mt-1">Get In Touch</h2>
                            <form id="ajax_form" method="POST" action="{{ route('front.contact')}}">
                                @csrf
                                <div class="row row--10">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="contact-name">Name <span>*</span></label>
                                            <input type="text" name="name" id="contact-name">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="contact-phone">Phone <span>*</span></label>
                                            <input type="text" name="phone" id="contact-phone">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="contact-email">E-mail <span>*</span></label>
                                            <input type="email" name="email" id="contact-email">
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="contact-name">Subject <span>*</span></label>
                                            <input type="text" name="subject" id="contact-name">
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="contact-message">Your Message</label>
                                            <textarea name="message" id="contact-message" cols="1" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb--0">
                                            <button name="submit" type="submit" id="submit" class="axil-btn btn-bg-primary">Send Message</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mapouter">
                            <div class="gmap_canvas">
                                <iframe width="1080" height="500" id="gmap_canvas" src="https://maps.google.com/maps?q=melbourne&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- End Google Map Area  -->
        </div>
    </div>
    <!-- End Contact Area  -->
</main>

@endsection