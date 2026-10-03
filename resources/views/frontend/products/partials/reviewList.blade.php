{{-- resources/views/frontend/products/partials/reviewList.blade.php --}}

{{-- ✅ Inline CSS (only for review list) --}}
<style>
    #review .comment-list{ padding-left:0 !important; margin:0; }
    #review .comment-list li{
        list-style:none !important;
        background: transparent !important;
        border:0 !important;
        box-shadow:none !important;
        padding:0 !important;
    }

    #review .review-card{
        background:#fff;
        border:1px solid rgba(2,6,23,.10);
        border-radius:16px;
        padding:12px 12px;
        margin-bottom:10px;
        box-shadow:0 12px 26px rgba(0,0,0,.06);
        transition: all .25s ease;
        font-family:'Hind Siliguri', sans-serif;
    }
    #review .review-card:hover{
        transform: translateY(-1px);
        box-shadow:0 18px 40px rgba(0,0,0,.08);
        border-color: rgba(0,39,108,.18);
    }

    #review .review-head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:10px;
        padding-bottom:10px;
        border-bottom:1px dashed rgba(2,6,23,.10);
    }

    #review .review-user{
        display:flex;
        align-items:center;
        gap:10px;
        min-width:0;
    }
    #review .review-avatar{
        width:48px;
        height:48px;
        border-radius:14px;
        border:1px solid rgba(0,39,108,.12);
        background: rgba(0,39,108,.04);
        object-fit: cover;
        flex:0 0 auto;
    }
    #review .review-user-meta{ min-width:0; }

    #review .review-name-row{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap: wrap;
    }
    #review .review-name{
        font-weight:900;
        color:#0f172a;
        font-size:14.5px;
        line-height:1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }

    #review .review-stars{
        display:inline-flex;
        align-items:center;
        gap:4px;
        background: rgba(245,158,11,.10);
        border:1px solid rgba(245,158,11,.22);
        padding:4px 8px;
        border-radius:999px;
        white-space: nowrap;
    }
    #review .review-stars i{
        font-size:13px;
        color:#f59e0b;
        font-family:'Font Awesome 5 Free' !important;
        font-weight:900 !important;
        font-style: normal !important;
    }
    #review .review-stars i.far{
        color: rgba(245,158,11,.55);
        font-weight:400 !important;
    }

    #review .review-date{
        margin-top:4px;
        font-size:12.5px;
        color:#64748b;
        font-weight:700;
        display:flex;
        align-items:center;
        gap:6px;
    }
    #review .review-date i{
        font-family:'Font Awesome 5 Free' !important;
        font-weight:900 !important;
    }

    #review .review-badge{
        font-size:12px;
        font-weight:900;
        color:#0f5132;
        background: rgba(34,197,94,.10);
        border:1px solid rgba(34,197,94,.22);
        padding:6px 10px;
        border-radius:999px;
        white-space: nowrap;
    }

    #review .review-body{ padding-top:10px; }

    #review .review-text{
        margin:0;
        font-size:14px;
        line-height:1.55;
        color:#0f172a;
        font-weight:700;
        white-space: pre-line;
    }

    #review .review-image-wrap{
        display:block;
        width: 140px;
        margin-top:10px;
        position:relative;
        border-radius:14px;
        overflow:hidden;
        border:1px solid rgba(2,6,23,.10);
        box-shadow:0 10px 22px rgba(0,0,0,.06);
        text-decoration:none !important;
    }

    /* ✅ এই ক্লাসটিই ছবির সাইজ ঠিক করে রাখছে */
    #review .review-image{
        width: 100%;
        height: 100%;
        min-height: 120px;
        max-height: 140px;
        object-fit: cover;
        display: block;
    }
    
    #review .review-image-zoom{
        position:absolute;
        inset:0;
        display:flex;
        align-items:center;
        justify-content:center;
        background: rgba(0,0,0,.35);
        opacity:0;
        transition: all .25s ease;
    }
    #review .review-image-zoom i{
        color:#fff;
        font-size:18px;
        font-family:'Font Awesome 5 Free' !important;
        font-weight:900 !important;
    }
    #review .review-image-wrap:hover .review-image-zoom{ opacity: 1; }

    @media(max-width:575px){
        #review .review-avatar{ width:44px; height:44px; border-radius:12px; }
        #review .review-badge{ display:none; }
        #review .review-image-wrap{ width:100%; }
        #review .review-image{ max-height: 250px; } /* মোবাইলের জন্য ছবির হাইট এডজাস্টমেন্ট */
        #review .review-name{ max-width: 160px; }
    }
</style>

{{-- ✅ Review Cards --}}
@foreach($singleProduct->reviews as $review)
@php
    $rDate = !empty($review->created_at)
        ? \Carbon\Carbon::parse($review->created_at)->format('d M, Y')
        : '';
@endphp

<li class="comment d-block">

    <div class="review-card">

        <div class="review-head">
            <div class="review-user">
                <img class="review-avatar"
                     src="{{ asset('images/users.png') }}"
                     alt="User Avatar">

                <div class="review-user-meta">
                    <div class="review-name-row">
                        <span class="review-name">{{ $review->name }}</span>

                        <span class="review-stars" aria-label="Rating">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= (int) $review->review)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </span>
                    </div>

                    @if($rDate)
                        <div class="review-date">
                            <i class="far fa-clock"></i> {{ $rDate }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="review-badge">Verified Review</div>
        </div>

        <div class="review-body">
            @if(!empty($review->message))
                <p class="review-text">{{ $review->message }}</p>
            @endif

            @if(!empty($review->image))
                {{-- ✅ ছবির সাইজ ইনলাইন স্টাইল দিয়েও ১০০% নিশ্চিত করা হলো --}}
                <a href="{{ asset($review->image) }}" target="_blank" class="review-image-wrap" aria-label="Open review image">
                    <img class="review-image" style="width: 100%; height: 100%; object-fit: cover;" src="{{ asset($review->image) }}" alt="Review Image">
                    <span class="review-image-zoom">
                        <i class="fas fa-search-plus"></i>
                    </span>
                </a>
            @endif
        </div>

    </div>

</li>
@endforeach