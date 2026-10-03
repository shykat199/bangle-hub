@extends('backend.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg mt-4" style="border-radius: 16px;">
                
                {{-- Premium Header --}}
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm p-2 rounded-circle bg-light me-3">
                            <i class="dripicons-broadcast text-primary" style="font-size: 24px;"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold text-dark">Facebook Feed Settings</h4>
                            <small class="text-muted">Manage your product XML feed integration</small>
                        </div>
                    </div>
                </div>

                <div class="card-body px-4 pb-5">
                    
                    {{-- Toggle Switch --}}
                    <div class="p-3 mb-4 rounded bg-light border d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="m-0 fw-semibold text-dark">Enable Product Feed</h6>
                            <small class="text-muted">Turn this on to generate XML link</small>
                        </div>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="feedToggle" 
                                style="width: 50px; height: 25px; cursor: pointer;"
                                {{ ($feedStatus ?? 'off') === 'on' ? 'checked' : '' }}
                            >
                        </div>
                    </div>

                    {{-- URL Copy Section --}}
                    <div id="feedBox" style="display: {{ ($feedStatus ?? 'off') === 'on' ? 'block' : 'none' }};" class="animate__animated animate__fadeIn">
                        <label class="form-label fw-medium text-secondary">Facebook XML Feed URL</label>

                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0 text-primary">
                                <i class="dripicons-link"></i>
                            </span>
                            <input 
                                type="text" 
                                id="feedUrl" 
                                class="form-control border-start-0 ps-0" 
                                readonly 
                                value="{{ url('/facebook-product-feed.xml') }}"
                                style="font-size: 14px; color: #555;"
                            >
                            <button class="btn btn-dark" onclick="copyFeed()" type="button" style="min-width: 100px;">
                                <i class="dripicons-copy me-1"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="mdi mdi-information-outline"></i> Copy this URL and paste it into your Facebook Commerce Manager.
                        </small>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    const toggle = document.getElementById('feedToggle');
    const box = document.getElementById('feedBox');

    // Toggle Switch Logic
    toggle.addEventListener('change', function () {
        // Show loading state if needed, or just fetch
        
        fetch("{{ route('facebook_feed.toggle') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                status: this.checked ? 1 : 0
            })
        })
        .then(res => res.json())
        .then(data => {
            // Smooth toggle visibility
            if(this.checked){
                box.style.display = 'block';
                // Optional: Add animation class if using animate.css
            } else {
                box.style.display = 'none';
            }
            
            // Show Toast Notification (using the toastr loaded in layout)
            if(typeof toastr !== 'undefined'){
                toastr.success(data.message || 'Status updated successfully');
            }
        })
        .catch(err => {
            console.error(err);
            if(typeof toastr !== 'undefined'){
                toastr.error('Something went wrong!');
            }
        });
    });

    // Copy to Clipboard Logic
    function copyFeed() {
        const input = document.getElementById('feedUrl');
        input.select();
        input.setSelectionRange(0, 99999); // For Mobile devices
        
        navigator.clipboard.writeText(input.value).then(() => {
            // Modern Toastr Notification
            if(typeof toastr !== 'undefined'){
                toastr.success('URL copied to clipboard!', 'Success');
            } else {
                alert("Facebook Feed URL Copied!");
            }
        });
    }
</script>
@endpush
@endsection