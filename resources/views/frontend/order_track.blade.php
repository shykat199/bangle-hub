@extends('frontend.app')

@section('content')

{{-- ✅ Get Brand Color from Database --}}
@php
use App\Models\Information;
$information = Information::first();
$brandGradient = $information->gradient_code ?? 'linear-gradient(90deg, #0d6efd, #00276C)';
@endphp

<style>
    /* ✅ Global Variables */
    :root {
        --brand-gradient: {!! $brandGradient !!};
    }

    /* ✅ Z-INDEX FIX */
    .modal-backdrop { z-index: 999998 !important; }
    .modal { z-index: 999999 !important; }

    /* Page Layout */
    .track-section-wrapper {
        background: #f8f9fa;
        min-height: 65vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 15px;
        font-family: 'Poppins', sans-serif;
    }

    /* Input Card Design */
    .track-input-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 50px 30px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
        text-align: center;
        border: 1px solid rgba(0,0,0,0.02);
    }

    /* Icon Animation */
    .icon-box {
        width: 80px;
        height: 80px;
        background: var(--brand-gradient);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin: 0 auto 25px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: pulse-icon 2s infinite;
    }

    @keyframes pulse-icon {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(13, 110, 253, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }

    .trk-title {
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
        font-size: 24px;
    }

    .trk-subtitle {
        color: #64748b;
        margin-bottom: 30px;
        font-size: 14px;
    }

    /* Input Field */
    .input-wrap {
        position: relative;
        margin-bottom: 20px;
    }
    .trk-input {
        width: 100%;
        height: 55px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        padding: 0 20px 0 55px;
        font-size: 16px;
        font-weight: 600;
        color: #334155;
        background: #f8fafc;
        transition: all 0.3s ease;
        outline: none;
    }
    .trk-input:focus {
        border-color: #0d6efd;
        border-color: var(--brand-gradient);
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .input-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
    }

    /* Button */
    .btn-track {
        width: 100%;
        height: 55px;
        border-radius: 12px;
        background: var(--brand-gradient);
        color: #fff;
        border: none;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    .btn-track:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
        color: #fff;
    }

    /* Modal Styling */
    .modal-content-clean {
        border-radius: 20px;
        border: none;
        overflow: hidden;
    }
    .modal-header-clean {
        background: var(--brand-gradient);
        color: #fff;
        padding: 20px 25px;
        border-bottom: none;
    }
    .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }

    /* ✅ MOBILE & iPHONE FIXES */
    @media (max-width: 576px) {
        /* পপআপ যাতে স্ক্রিনের বাইরে না যায় */
        .modal-dialog {
            max-width: 92% !important; 
            margin: 1.75rem auto; /* Center horizontally */
        }
        
        .track-input-card {
            padding: 30px 20px; /* Padding কমানো হয়েছে মোবাইলের জন্য */
        }

        .trk-title {
            font-size: 20px;
        }
        
        /* iPhone Safe Area Fix */
        .modal-body {
            padding-bottom: 30px; 
        }
    }
</style>

<div class="track-section-wrapper">
    <div class="track-input-card">
        <div class="icon-box">
            <i class="fas fa-shipping-fast"></i>
        </div>
        
        <h2 class="trk-title">Track Your Order</h2>
        <p class="trk-subtitle">Enter your mobile number to check status</p>

        <form id="trackForm">
            <div class="input-wrap">
                <i class="fas fa-mobile-alt input-icon"></i>
                <input type="tel" 
                       id="phone" 
                       class="trk-input" 
                       placeholder="    e.g. 017xxxxxxxx" 
                       maxlength="11"
                       required>
            </div>

            <button type="submit" class="btn-track" id="btnSubmit">
                <span id="txtStatic">Track Now <i class="fas fa-arrow-right ms-2"></i></span>
                <span id="txtLoad" style="display: none;"><i class="fas fa-circle-notch fa-spin"></i> Finding...</span>
            </button>
        </form>
    </div>
</div>

{{-- ✅ Result Modal (Popup) --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-clean">
            <div class="modal-header modal-header-clean">
                <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2"></i> Your Orders</h5>
                <button type="button" class="btn-close btn-close-white closeModalBtn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light" id="orderContent" style="padding: 20px; min-height: 150px;">
                {{-- Data will load here --}}
            </div>
            <!--<div class="modal-footer bg-white border-0 py-2">-->
            <!--    <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm closeModalBtn" data-bs-dismiss="modal">Close</button>-->
            <!--</div>-->
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){
    // Move modal to body end to avoid CSS conflict
    $('#orderModal').appendTo('body');

    $('#trackForm').on('submit', function(e){
        e.preventDefault();

        let phone = $('#phone').val();
        let btn = $('#btnSubmit');
        let txtStatic = $('#txtStatic');
        let txtLoad = $('#txtLoad');

        if(phone.length < 11){
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Number',
                text: 'Please enter a valid 11-digit mobile number.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Loading
        btn.prop('disabled', true);
        txtStatic.hide();
        txtLoad.show();

        $.ajax({
            url: "{{ route('front.order.track') }}",
            type: "GET",
            data: { mobile: phone },
            success: function(res){
                btn.prop('disabled', false);
                txtStatic.show();
                txtLoad.hide();

                if(res.status === true){
                    $('#orderContent').html(res.html);
                    var myModal = new bootstrap.Modal(document.getElementById('orderModal'));
                    myModal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Found',
                        text: 'No orders found with this number.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            },
            error: function(){
                btn.prop('disabled', false);
                txtStatic.show();
                txtLoad.hide();
                Swal.fire('Error', 'Something went wrong!', 'error');
            }
        });
    });

    // Ensure backdrop removes properly
    $('.closeModalBtn').on('click', function(){
        $('.modal-backdrop').remove();
        $('body').css('overflow', 'auto');
    });
});
</script>

@endsection