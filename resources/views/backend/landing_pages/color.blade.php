@extends('backend.app')

@push('css')
<style>
    /* ✅ Premium Custom CSS */
    :root {
        --primary: #556ee6;
        --secondary: #34c38f;
        --bg-light: #f8f9fa;
        --card-border-radius: 16px;
    }

    /* Card Styling */
    .premium-card {
        border: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border-radius: var(--card-border-radius);
        overflow: hidden;
        transition: transform 0.3s ease;
        background: #fff; /* Ensure background is white */
    }

    /* Left Sidebar List */
    .page-list .list-group-item {
        border: 0;
        margin-bottom: 8px;
        border-radius: 10px !important;
        font-weight: 600;
        color: #495057;
        padding: 14px 18px;
        transition: all 0.2s ease;
        background: #fff;
        border: 1px solid transparent;
    }
    
    .page-list .list-group-item:hover {
        background-color: #f1f4ff;
        color: var(--primary);
        transform: translateX(5px);
    }

    .page-list .list-group-item.active {
        background: linear-gradient(135deg, #556ee6 0%, #768bf7 100%);
        color: #fff;
        box-shadow: 0 5px 15px rgba(85, 110, 230, 0.3);
        border: 0;
    }

    /* Section Headers */
    .section-header {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: #74788d;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-header::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #eeffff;
        border-bottom: 1px dashed #ced4da;
    }

    /* Color Picker Input */
    .color-input-group {
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 10px;
        overflow: hidden;
        padding: 4px;
        background: #fff;
    }
    .form-control-color {
        border: 0;
        background: none;
        width: 50px;
        height: 40px;
        padding: 0;
        cursor: pointer;
    }
    .color-text-input {
        border: 0;
        font-weight: 600;
        color: #495057;
        box-shadow: none !important;
    }

    /* Gradient Preview Box */
    .gradient-preview-box {
        height: 48px;
        width: 100%;
        border-radius: 10px;
        border: 2px dashed #ced4da;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        font-weight: bold;
        transition: all 0.3s;
    }

    /* Sticky Save Button for Mobile */
    .sticky-save {
        position: sticky;
        bottom: 20px;
        z-index: 99;
    }

    /* ✅ Sticky Sidebar Fix: Sidebar will stay at top */
    .sticky-sidebar {
        position: -webkit-sticky;
        position: sticky;
        top: 20px; /* Adjust based on your header height */
        z-index: 10;
    }
    
    @media (max-width: 767px) {
        .page-list-container {
            margin-bottom: 20px;
        }
        .page-list {
            display: flex;
            overflow-x: auto;
            padding-bottom: 10px;
            gap: 10px;
        }
        .page-list .list-group-item {
            white-space: nowrap;
            margin-bottom: 0;
        }
        /* Disable sticky on mobile */
        .sticky-sidebar {
            position: static;
        }
    }
</style>
@endpush

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">🎨 Landing Page Designer</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Color Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- ✅ Added align-items-start to ensure items stay at top --}}
<div class="row align-items-start">
    {{-- ========================
         LEFT SIDE: PAGE LIST
         ======================== --}}
    <div class="col-lg-3 col-md-4 page-list-container">
        {{-- ✅ Wrapped in sticky-sidebar to keep it UP --}}
        <div class="sticky-sidebar">
            <div class="card premium-card">
                <div class="card-body bg-light rounded-top">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="mdi mdi-layers-outline me-1"></i> Select Page
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="list-group page-list">
                        @forelse($all_pages as $page)
                            <a href="{{ route('admin.landing_pages.color', $page->id) }}" 
                               class="list-group-item list-group-item-action {{ (isset($ln_pg) && $ln_pg->id == $page->id) ? 'active' : '' }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span><i class="mdi mdi-file-document-edit-outline me-2"></i> {{ $page->title1 }}</span>
                                    @if(isset($ln_pg) && $ln_pg->id == $page->id)
                                        <i class="mdi mdi-check-circle text-white"></i>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-center p-3 text-muted">
                                <i class="mdi mdi-alert-circle-outline fs-3"></i><br>
                                No landing pages created yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================
         RIGHT SIDE: SETTINGS FORM
         ======================== --}}
    <div class="col-lg-9 col-md-8">
        @if(isset($ln_pg))
        <form action="{{ route('admin.landing_pages.color_update', $ln_pg->id) }}" method="POST">
            @csrf
            
            <div class="card premium-card">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="font-size-16 mb-1 text-dark">Color Configuration</h5>
                            <p class="text-muted mb-0 font-size-13">Customizing page: <span class="fw-bold text-primary">{{ $ln_pg->title1 }}</span></p>
                        </div>
                        
                        {{-- ✅ Preview Button with Safety Check --}}
                        <a href="{{ route('front.landing_pages_two.view_page', ['id' => $ln_pg->slug ?: $ln_pg->id]) }}" 
   target="_blank" 
   class="btn btn-soft-info btn-sm">
    <i class="mdi mdi-eye me-1"></i> Live Preview
</a>
                    </div>
                </div>

                <div class="card-body p-4">
                    
                    {{-- 1. THEME COLORS --}}
                    <div class="section-header"><i class="mdi mdi-palette text-primary"></i> &nbsp; Theme Colors</div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Primary Brand Color (Solid)</label>
                            <div class="color-input-group">
                                <input type="color" class="form-control form-control-color" id="primaryColorInput" name="theme_primary_col" value="{{ $ln_pg->theme_primary_col ?? '#00276C' }}" title="Choose Color">
                                <input type="text" class="form-control color-text-input" id="primaryColorText" value="{{ $ln_pg->theme_primary_col ?? '#00276C' }}" readonly>
                            </div>
                            <small class="text-muted d-block mt-1">Used for headers, icons & accents.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gradient Background</label>
                            <input type="text" class="form-control" id="gradientInput" name="theme_gradient_col" value="{{ $ln_pg->theme_gradient_col ?? 'linear-gradient(90deg,#0d6efd,#00276C)' }}" placeholder="linear-gradient(...)">
                            
                            {{-- Gradient Preview --}}
                            <div class="mt-2 gradient-preview-box" id="gradientPreview">
                                Gradient Preview
                            </div>
                        </div>
                    </div>

                    {{-- 2. BUTTON STYLES --}}
                    <div class="section-header"><i class="mdi mdi-gesture-tap-button text-success"></i> &nbsp; Button Style</div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Button Background</label>
                            <div class="color-input-group">
                                <input type="color" class="form-control form-control-color" id="btnBgInput" name="btn_bg_color" value="{{ $ln_pg->btn_bg_color ?? '#00276C' }}">
                                <input type="text" class="form-control color-text-input" id="btnBgText" value="{{ $ln_pg->btn_bg_color ?? '#00276C' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Button Text Color</label>
                            <div class="color-input-group">
                                <input type="color" class="form-control form-control-color" id="btnTextInput" name="btn_text_color" value="{{ $ln_pg->btn_text_color ?? '#ffffff' }}">
                                <input type="text" class="form-control color-text-input" id="btnTextText" value="{{ $ln_pg->btn_text_color ?? '#ffffff' }}" readonly>
                            </div>
                        </div>
                        
                        {{-- Button Preview --}}
                        <div class="col-12">
                            <label class="form-label text-muted small">Live Button Preview:</label>
                            <div class="p-3 bg-light rounded text-center border">
                                <button type="button" id="btnPreview" class="btn px-4 py-2 fw-bold shadow-sm" style="border-radius: 50px;">
                                    Sample Button
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. BUTTON TEXTS --}}
                    <div class="section-header"><i class="mdi mdi-format-text text-warning"></i> &nbsp; Button Labels (Bangla / English)</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Hero Section Button</label>
                            <input type="text" class="form-control" name="btn_text_hero" value="{{ $ln_pg->btn_text_hero }}" placeholder="e.g. অর্ডার করতে ক্লিক করুন">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Video Section Button</label>
                            <input type="text" class="form-control" name="btn_text_video" value="{{ $ln_pg->btn_text_video }}" placeholder="e.g. অর্ডার করতে চাই">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Feature Section Button</label>
                            <input type="text" class="form-control" name="btn_text_feature" value="{{ $ln_pg->btn_text_feature }}" placeholder="e.g. অর্ডার করুন">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Order Form Submit Button</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white"><i class="mdi mdi-check"></i></span>
                                <input type="text" class="form-control border-success" name="btn_text_form" value="{{ $ln_pg->btn_text_form }}" placeholder="e.g. অর্ডার কনফার্ম করুন">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Footer Actions --}}
                <div class="card-footer bg-white border-top p-3 text-end sticky-save">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-lg rounded-pill">
                        <i class="mdi mdi-content-save-outline me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
        @else
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center rounded-3 p-4" role="alert" style="background-color: #fff8e1; color: #856404;">
                <i class="mdi mdi-arrow-left-bold-circle-outline fs-1 me-3"></i>
                <div>
                    <h5 class="alert-heading fw-bold">No Page Selected!</h5>
                    <p class="mb-0">Please select a landing page from the list on the left side to start editing colors and texts.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@push('js')
<script>
    // ✅ Real-time Color Sync & Preview
    document.addEventListener('DOMContentLoaded', function() {
        
        // Helper to sync color input with text input
        function syncColor(pickerId, textId) {
            const picker = document.getElementById(pickerId);
            const text = document.getElementById(textId);
            if(picker && text){
                picker.addEventListener('input', () => { text.value = picker.value; updatePreview(); });
                text.addEventListener('input', () => { picker.value = text.value; updatePreview(); });
            }
        }

        syncColor('primaryColorInput', 'primaryColorText');
        syncColor('btnBgInput', 'btnBgText');
        syncColor('btnTextInput', 'btnTextText');

        // Gradient Preview Logic
        const gradInput = document.getElementById('gradientInput');
        const gradPreview = document.getElementById('gradientPreview');

        if(gradInput && gradPreview){
            gradInput.addEventListener('input', function(){
                gradPreview.style.background = this.value;
            });
            // Initial load
            gradPreview.style.background = gradInput.value;
        }

        // Button Preview Logic
        const btnPreview = document.getElementById('btnPreview');
        function updatePreview(){
            if(btnPreview){
                btnPreview.style.backgroundColor = document.getElementById('btnBgInput').value;
                btnPreview.style.color = document.getElementById('btnTextInput').value;
            }
        }
        // Initial load
        updatePreview();
    });
</script>
@endpush