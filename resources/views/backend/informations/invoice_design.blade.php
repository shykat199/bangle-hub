@extends('backend.app')
@section('content')

@php
    // Check current invoice type from database
    $currentType = (int)($info->invoice_type ?? 1);
@endphp

<style>
    /* Premium Card Style */
    .design-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border-radius: 16px;
        border: 2px solid #eaecf0; /* Default subtle border */
        background: #fff;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .design-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important;
        border-color: #d0d5dd;
    }

    /* Active State Styling */
    .design-card.active-design {
        border-color: #10b981 !important; /* Green Border */
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.15) !important;
        position: relative;
    }

    /* Active Badge */
    .active-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #10b981;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        z-index: 10;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-transform: uppercase;
    }

    /* Image Wrapper */
    .design-image-wrapper {
        height: 300px; /* Fixed height for image area */
        width: 100%;
        display: flex;
        align-items: center; /* Vertically Center */
        justify-content: center; /* Horizontally Center */
        background: #f8fafc; /* Light soothing background */
        border-bottom: 1px solid #f1f5f9;
        padding: 20px;
    }

    /* Image Styling */
    .design-image-wrapper img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain; /* Show FULL image without cropping */
        filter: drop-shadow(0 8px 15px rgba(0,0,0,0.06));
        transition: transform 0.4s ease;
    }

    /* Hover Zoom Effect */
    .design-card:hover .design-image-wrapper img {
        transform: scale(1.05);
    }

    .card-content {
        padding: 25px;
        flex-grow: 1; /* Pushes content to fill height */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 20px;
    }

    .btn-select {
        border-radius: 10px;
        padding: 12px 0;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.2s;
        font-size: 14px;
        width: 100%;
    }
    
    .design-title { 
        font-size: 20px; 
        font-weight: 800; 
        color: #111827; 
        margin: 0; 
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between py-4">
            <h4 class="page-title mb-0 fw-bold text-dark">Invoice Design</h4>
            <div class="breadcrumb-item d-none d-md-block">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> 
                <span class="mx-2 text-muted">/</span> 
                <span class="text-primary fw-bold">Invoice Design</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    {{-- =======================================================
                            DESIGN 1
         ======================================================= --}}
    <div class="col-md-6 col-xl-3">
        <div class="design-card {{ $currentType === 1 ? 'active-design' : '' }}">
            @if($currentType === 1) <div class="active-badge"><i class="mdi mdi-check"></i> ACTIVE</div> @endif

            <div class="design-image-wrapper">
                <img src="{{ asset('111.PNG') }}" alt="Design 1">
            </div>
            
            <div class="card-content">
                <h5 class="design-title">Design 1</h5>
                
                @if($currentType === 1)
                    <button class="btn btn-select" style="background-color: #ecfdf5; color: #059669; border: 1px solid #d1fae5; cursor: default;">
                        <i class="mdi mdi-check-circle me-1"></i> Selected
                    </button>
                @else
                    <form action="{{ route('admin.invoice_type.update') }}" method="POST" class="w-100">
                        @csrf <input type="hidden" name="type" value="1">
                        <button type="submit" class="btn btn-outline-dark btn-select">Select Design</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- =======================================================
                            DESIGN 2
         ======================================================= --}}
    <div class="col-md-6 col-xl-3">
        <div class="design-card {{ $currentType === 2 ? 'active-design' : '' }}">
            @if($currentType === 2) <div class="active-badge"><i class="mdi mdi-check"></i> ACTIVE</div> @endif

            <div class="design-image-wrapper">
                <img src="{{ asset('1.PNG') }}" alt="Design 2">
            </div>
            
            <div class="card-content">
                <h5 class="design-title">Design 2</h5>
                
                @if($currentType === 2)
                    <button class="btn btn-select" style="background-color: #ecfdf5; color: #059669; border: 1px solid #d1fae5; cursor: default;">
                        <i class="mdi mdi-check-circle me-1"></i> Selected
                    </button>
                @else
                    <form action="{{ route('admin.invoice_type.update') }}" method="POST" class="w-100">
                        @csrf <input type="hidden" name="type" value="2">
                        <button type="submit" class="btn btn-outline-dark btn-select">Select Design</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- =======================================================
                            DESIGN 3
         ======================================================= --}}
    <div class="col-md-6 col-xl-3">
        <div class="design-card {{ $currentType === 3 ? 'active-design' : '' }}">
            @if($currentType === 3) <div class="active-badge"><i class="mdi mdi-check"></i> ACTIVE</div> @endif

            <div class="design-image-wrapper">
                {{-- Requested Image --}}
                <img src="{{ asset('3333.PNG') }}" alt="Design 3">
            </div>
            
            <div class="card-content">
                <h5 class="design-title">Design 3</h5>
                
                @if($currentType === 3)
                    <button class="btn btn-select" style="background-color: #ecfdf5; color: #059669; border: 1px solid #d1fae5; cursor: default;">
                        <i class="mdi mdi-check-circle me-1"></i> Selected
                    </button>
                @else
                    <form action="{{ route('admin.invoice_type.update') }}" method="POST" class="w-100">
                        @csrf <input type="hidden" name="type" value="3">
                        <button type="submit" class="btn btn-outline-dark btn-select">Select Design</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- =======================================================
                            DESIGN 4
         ======================================================= --}}
    <div class="col-md-6 col-xl-3">
        <div class="design-card {{ $currentType === 4 ? 'active-design' : '' }}">
            @if($currentType === 4) <div class="active-badge"><i class="mdi mdi-check"></i> ACTIVE</div> @endif

            <div class="design-image-wrapper">
                {{-- Requested Image --}}
                <img src="{{ asset('4444.PNG') }}" alt="Design 4">
            </div>
            
            <div class="card-content">
                <h5 class="design-title">Design 4</h5>
                
                @if($currentType === 4)
                    <button class="btn btn-select" style="background-color: #ecfdf5; color: #059669; border: 1px solid #d1fae5; cursor: default;">
                        <i class="mdi mdi-check-circle me-1"></i> Selected
                    </button>
                @else
                    <form action="{{ route('admin.invoice_type.update') }}" method="POST" class="w-100">
                        @csrf <input type="hidden" name="type" value="4">
                        <button type="submit" class="btn btn-outline-dark btn-select">Select Design</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection