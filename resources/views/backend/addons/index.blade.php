@extends('backend.app')
@section('content')
<div class="container-fluid py-4 page-animate">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-4 rounded-4 custom-shadow-sm border-0 header-card">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary-soft text-primary rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 50px; height: 50px;">
                        <i class="mdi mdi-puzzle fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bolder text-dark">Addon Marketplace</h4>
                        <p class="text-muted mb-0 fs-6">Enhance your application with premium modules</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($addons as $index => $addon)
            <div class="col-md-6 col-lg-4 card-animate" style="animation-delay: {{ $index * 0.1 }}s;">
                <div class="card border-0 rounded-4 custom-shadow-sm h-100 premium-card d-flex flex-column">
                    
                    <div class="image-wrapper position-relative w-100">
                        <span class="version-badge shadow-sm">
                            v{{ $addon['version'] ?? '1.0.0' }}
                        </span>
                        
                        <img src="{{ $addon['image'] }}" class="addon-img img-fluid w-100" alt="{{ $addon['name'] }}">
                    </div>
                    
                    <div class="card-body p-4 d-flex flex-column bg-white rounded-bottom-4 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bolder text-dark mb-0 text-truncate pe-2" title="{{ $addon['name'] }}">
                                {{ $addon['name'] }}
                            </h5>
                            <h5 class="text-primary fw-bolder mb-0 bg-primary-soft px-2 py-1 rounded-3">
                                ৳{{ number_format($addon['price'], 0) }}
                            </h5>
                        </div>
                        
                        <div class="addon-description text-secondary small mb-4" title="{{ $addon['description'] }}">
                            {{ $addon['description'] }}
                        </div>
                        
                        <div class="mt-auto w-100 pt-2" id="install-area-{{ $addon['slug'] }}">
                            <input type="hidden" id="version-{{ $addon['slug'] }}" value="{{ $addon['version'] ?? '1.0.0' }}">

                            @if($addon['is_installed'] && !$addon['update_available'])
                                <button class="btn btn-installed w-100 rounded-3 fw-bold py-2 shadow-none" type="button" disabled>
                                    <i class="mdi mdi-check-circle fs-5 align-middle me-1"></i> Installed (v{{ $addon['current_version'] }})
                                </button>

                            @elseif($addon['is_installed'] && $addon['update_available'])
                                <input type="hidden" id="key-{{ $addon['slug'] }}" value="{{ $addon['saved_key'] }}">
                                <button class="btn btn-update w-100 rounded-3 fw-bold text-white py-2 mb-2 custom-shadow-sm" onclick="installAddon('{{ $addon['slug'] }}', this)">
                                    <i class="mdi mdi-cloud-download fs-5 align-middle me-1"></i> Update to v{{ $addon['version'] ?? '1.0.0' }}
                                </button>
                                <div class="text-center mt-2">
                                    <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill fw-medium">Current: v{{ $addon['current_version'] }}</span>
                                </div>

                            @else
                                <div class="license-input-wrapper mb-3 d-flex align-items-center rounded-3 p-1 border">
                                    <i class="mdi mdi-key-variant text-muted ms-2 fs-5"></i>
                                    <input type="text" id="key-{{ $addon['slug'] }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Enter License Key">
                                </div>
                                <button class="btn btn-primary w-100 rounded-3 fw-bold py-2 custom-shadow-sm btn-install" onclick="installAddon('{{ $addon['slug'] }}', this)">
                                    <i class="mdi mdi-download fs-5 align-middle me-1"></i> Install Addon
                                </button>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        No license key? Marketplace unavailable
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="empty-state p-5 bg-white rounded-4 custom-shadow-sm border-0 d-inline-block">
                    <div class="empty-icon-box bg-light rounded-circle mx-auto mb-3 d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                        <i class="mdi mdi-puzzle-remove-outline text-muted fs-1"></i>
                    </div>
                    <h4 class="fw-bolder text-dark">No Addons Available</h4>
                    <p class="text-muted mb-0">Check back later for new exclusive modules.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    /* Clean Color Palette */
    :root {
        --primary-blue: #2563eb;
        --primary-blue-hover: #1d4ed8;
        --primary-soft: #eff6ff;
        --bg-light: #f8fafc;
        --text-dark: #0f172a;
        --text-muted: #64748b;
        --card-border: #e2e8f0;
    }

    .text-primary { color: var(--primary-blue) !important; }
    .bg-primary-soft { background-color: var(--primary-soft) !important; }
    .btn-primary { background-color: var(--primary-blue); border-color: var(--primary-blue); }
    .btn-primary:hover { background-color: var(--primary-blue-hover); border-color: var(--primary-blue-hover); }

    /* Custom Shadows for a flat modern look */
    .custom-shadow-sm {
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }
    
    /* Header Styling */
    .header-card {
        border-top: 4px solid var(--primary-blue) !important;
    }

    /* Image Wrapper */
    .image-wrapper {
        width: 100%;
        background-color: var(--bg-light);
        border-radius: 1rem 1rem 0 0;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Updated Image Class */
    .addon-img {
        width: 100%;
        height: auto; /* গ্যাপ দূর করার জন্য */
        display: block;
        transition: transform 0.4s ease;
    }

    /* Version Badge */
    .version-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        color: var(--text-dark);
        font-size: 0.75rem;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 20px;
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Card Hover Effects */
    .premium-card {
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }
    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.05) !important;
        border-color: var(--primary-soft);
    }
    .premium-card:hover .addon-img {
        transform: scale(1.03);
    }

    /* Text Formatting */
    .addon-description {
        line-height: 1.5;
        max-height: 3em; 
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        word-break: break-word; 
        margin-bottom: 1.5rem !important; 
    }

    /* Inputs and Buttons */
    .license-input-wrapper {
        background-color: var(--bg-light);
        transition: border-color 0.3s;
    }
    .license-input-wrapper:focus-within {
        border-color: var(--primary-blue) !important;
        background-color: #fff;
        box-shadow: 0 0 0 3px var(--primary-soft);
    }
    
    .btn-install { transition: all 0.3s ease; }
    .btn-install:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3) !important; }

    .btn-update {
        background-color: #059669; 
        border: none;
        transition: all 0.3s ease;
    }
    .btn-update:hover {
        background-color: #047857;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(5, 150, 105, 0.3) !important;
    }

    .btn-installed {
        background-color: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .hover-link { transition: color 0.2s; }
    .hover-link:hover { color: var(--primary-blue-hover) !important; text-decoration: underline !important; }

    /* Animations */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .page-animate { animation: fadeSlideUp 0.5s ease-out forwards; }
    .card-animate { opacity: 0; animation: fadeSlideUp 0.5s ease-out forwards; }
</style>

<script>
function installAddon(slug, btnElement) {
    let keyInput = document.getElementById('key-' + slug);
    let versionInput = document.getElementById('version-' + slug);
    
    let key = keyInput ? keyInput.value.trim() : '';
    let version = versionInput ? versionInput.value.trim() : '1.0.0';
    
    if(!key) {
        alert('Please enter a valid license key!');
        if(keyInput && keyInput.type !== 'hidden') keyInput.focus();
        return;
    }

    if(confirm('Proceed with the installation/update? Do not refresh the page during this process.')) {
        let originalText = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="mdi mdi-loading mdi-spin fs-5 align-middle me-1"></i> Processing...';
        btnElement.disabled = true;
        if(keyInput) keyInput.disabled = true;

        fetch("{{ route('admin.addons.install') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ 
                license_key: key, 
                addon_slug: slug,
                version: version
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('Success: ' + data.msg);
                location.reload(); 
            } else {
                alert('Error: ' + data.msg);
                btnElement.innerHTML = originalText;
                btnElement.disabled = false;
                if(keyInput) keyInput.disabled = false;
            }
        })
        .catch(err => {
            alert('Connection error! Please check your network and try again.');
            console.error(err);
            btnElement.innerHTML = originalText;
            btnElement.disabled = false;
            if(keyInput) keyInput.disabled = false;
        });
    }
}
</script>
@endsection