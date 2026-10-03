@extends('frontend.app')

@section('content')

{{-- Google Font --}}
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ✅ Z-INDEX FIX: হেডার এবং পপআপের কনফ্লিক্ট দূর করা হলো */
    .modal-backdrop {
        z-index: 1040 !important; /* স্ট্যান্ডার্ড বুটস্ট্র্যাপ ব্যাকড্রপ */
    }
    .modal {
        z-index: 1050 !important; /* স্ট্যান্ডার্ড বুটস্ট্র্যাপ মোডাল */
    }

    /* মেইন ব্যাকগ্রাউন্ড */
    .track-section-wrapper {
        background: #f4f7f6; /* প্রিমিয়াম সফট ব্যাকগ্রাউন্ড */
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 15px;
        font-family: 'Poppins', 'Hind Siliguri', sans-serif;
    }

    /* প্রিমিয়াম ও ছোট ইনপুট কার্ড ডিজাইন */
    .track-input-card {
        background: #ffffff;
        border-radius: 24px; /* রাউন্ডেড প্রিমিয়াম লুক */
        padding: 40px 30px;
        width: 100%;
        max-width: 420px; /* কার্ড ছোট করা হয়েছে */
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06); /* সফট শ্যাডো */
        text-align: center;
        border: 1px solid rgba(0,0,0,0.02);
    }

    .icon-box {
        width: 65px;
        height: 65px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: #2563eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin: 0 auto 20px;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.12);
    }

    .trk-title {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
        font-size: 22px;
    }

    .trk-subtitle {
        color: #64748b;
        margin-bottom: 30px;
        font-size: 14px;
    }

    /* ইনপুট ফিল্ড */
    .input-wrap {
        position: relative;
        margin-bottom: 20px;
    }
    .trk-input {
        width: 100%;
        height: 50px; /* সাইজ অপটিমাইজ করা হয়েছে */
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 0 15px 0 45px;
        font-size: 16px;
        font-weight: 500;
        color: #334155;
        background: #f8fafc;
        transition: all 0.3s ease;
        outline: none;
    }
    .trk-input:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
    }

    /* বাটন */
    .btn-track {
        width: 100%;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8); /* প্রিমিয়াম ব্লু গ্রেডিয়েন্ট */
        color: #fff;
        border: none;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 15px rgba(37, 99, 235, 0.2);
    }
    .btn-track:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(37, 99, 235, 0.3);
    }

    /* Modal Customization */
    .modal-content-clean {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        overflow: hidden;
    }
    .modal-header-clean {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        padding: 18px 24px;
        border-bottom: none;
    }
    .modal-title {
        font-size: 18px;
        font-weight: 600;
    }
    .btn-close-white { filter: brightness(0) invert(1); opacity: 0.8; }
    .btn-close-white:hover { opacity: 1; }
</style>

<div class="track-section-wrapper">
    <div class="track-input-card">
        <div class="icon-box">
            <i class="fas fa-box-open"></i>
        </div>
        
        <h2 class="trk-title">অর্ডার ট্র্যাকিং</h2>
        <p class="trk-subtitle">অর্ডারের আপডেট জানতে মোবাইল নম্বর দিন</p>

        <form id="trackForm">
            <div class="input-wrap">
                <i class="fas fa-phone-alt input-icon"></i>
                <input type="tel" 
                       id="phone" 
                       class="trk-input" 
                       placeholder="017xxxxxxxx" 
                       maxlength="11"
                       required>
            </div>

            <button type="submit" class="btn-track" id="btnSubmit">
                <span id="txtStatic">ট্র্যাক করুন</span>
                <span id="txtLoad" style="display: none;"><i class="fas fa-spinner fa-spin"></i> খুঁজছি...</span>
            </button>
        </form>
    </div>
</div>

{{-- ✅ Result Modal (Popup) --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-clean">
            <div class="modal-header modal-header-clean">
                <h5 class="modal-title">📦 আপনার অর্ডার তালিকা</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light" id="orderContent" style="padding: 24px;">
                {{-- Content will load here --}}
            </div>
            <div class="modal-footer bg-white border-top-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
{{-- Note: Ensure bootstrap.bundle.min.js is only loaded ONCE in your app layout. If it's already in frontend.app, you can remove the next line --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){
    // ✅ Modal টিকে সেফভাবে ডিক্লেয়ার করা হলো যেন ক্লিক করলে ওপেন হয়
    let orderModalElement = document.getElementById('orderModal');
    let orderModal = new bootstrap.Modal(orderModalElement);

    $('#trackForm').on('submit', function(e){
        e.preventDefault();

        let phone = $('#phone').val();
        let btn = $('#btnSubmit');
        let txtStatic = $('#txtStatic');
        let txtLoad = $('#txtLoad');

        // Validation
        if(phone.length < 11){
            Swal.fire({
                icon: 'warning',
                title: 'ভুল নম্বর!',
                text: 'দয়া করে সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন।',
                confirmButtonColor: '#2563eb',
                borderRadius: '15px'
            });
            return;
        }

        // Loading State
        btn.prop('disabled', true);
        txtStatic.hide();
        txtLoad.show();

        // AJAX Request
        $.ajax({
            url: "{{ route('front.order.track') }}",
            type: "GET",
            data: { mobile: phone },
            success: function(res){
                btn.prop('disabled', false);
                txtStatic.show();
                txtLoad.hide();

                if(res.status === true){
                    // Show Modal with Data
                    $('#orderContent').html(res.html);
                    orderModal.show(); // ✅ ফিক্সড: এখন ক্লিক করলেই পপআপ ওপেন হবে
                } else {
                    // Not Found Alert
                    Swal.fire({
                        icon: 'error',
                        title: 'পাওয়া যায়নি!',
                        text: 'দুঃখিত, এই নম্বরে কোনো অর্ডার খুঁজে পাওয়া যায়নি।',
                        confirmButtonColor: '#ef4444'
                    });
                }
            },
            error: function(){
                btn.prop('disabled', false);
                txtStatic.show();
                txtLoad.hide();
                Swal.fire('Error', 'সার্ভারে কোনো সমস্যা হয়েছে! আবার চেষ্টা করুন।', 'error');
            }
        });
    });
});
</script>

@endsection