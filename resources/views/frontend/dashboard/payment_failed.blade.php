@extends('frontend.app')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card border-0 shadow-lg rounded-4 p-5">
                <div class="mb-4">
                    {{-- লাল ক্রস আইকন --}}
                    <div style="width: 80px; height: 80px; background: #ffe6e6; color: #ff0000; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 40px;">
                        <i class="fas fa-times"></i>
                    </div>
                </div>

                <h2 class="fw-bold text-danger mb-3" style="font-family: 'Hind Siliguri', sans-serif;">পেমেন্ট ব্যর্থ হয়েছে!</h2>
                <p class="text-muted mb-4" style="font-family: 'Hind Siliguri', sans-serif; font-size: 16px;">
                    দুঃখিত, কোনো কারণে আপনার পেমেন্ট সম্পন্ন হয়নি। দয়া করে আবার চেষ্টা করুন।
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-home me-2"></i> হোম পেজ
                    </a>
                    <a href="{{ route('front.checkouts.index') }}" class="btn btn-danger rounded-pill px-4">
                        আবার চেষ্টা করুন <i class="fas fa-redo ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection s