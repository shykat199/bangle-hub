@extends('backend.layouts.app')

@section('content')
@php
    $feedStatus = $feedStatus ?? 'off'; // on/off
@endphp

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Facebook Feed Settings</h4>

            <span id="feedBadge" class="badge {{ $feedStatus === 'on' ? 'bg-success' : 'bg-secondary' }}">
                {{ $feedStatus === 'on' ? 'Enabled' : 'Disabled' }}
            </span>
        </div>

        <div class="card-body">

            <div class="form-check form-switch mb-3">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="feedToggle"
                    {{ $feedStatus === 'on' ? 'checked' : '' }}
                >
                <label class="form-check-label" for="feedToggle">
                    Enable Facebook Product Feed
                </label>
            </div>

            <div id="feedBox" style="display: {{ $feedStatus === 'on' ? 'block' : 'none' }};">
                <label class="mb-1">Facebook XML Feed URL</label>

                <div class="input-group mb-2">
                    <input
                        type="text"
                        id="feedUrl"
                        class="form-control"
                        readonly
                        value="{{ url('/facebook-product-feed.xml') }}"
                    >
                    <button class="btn btn-primary" type="button" id="copyBtn">
                        Copy
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection


@push('js')
<script>
(function () {
    const toggle  = document.getElementById('feedToggle');
    const box     = document.getElementById('feedBox');
    const badge   = document.getElementById('feedBadge');
    const copyBtn = document.getElementById('copyBtn');
    const feedUrl = document.getElementById('feedUrl');

    function setBadge(isOn){
        badge.classList.remove('bg-success','bg-secondary');
        badge.classList.add(isOn ? 'bg-success' : 'bg-secondary');
        badge.textContent = isOn ? 'Enabled' : 'Disabled';
    }

    function setUI(isOn){
        box.style.display = isOn ? 'block' : 'none';
        setBadge(isOn);
    }

    function ok(msg){
        if (window.toastr) toastr.success(msg);
        else alert(msg);
    }

    function err(msg){
        if (window.toastr) toastr.error(msg);
        else alert(msg);
    }

    // ✅ Toggle (AJAX)
    toggle.addEventListener('change', function () {
        const intended = this.checked;

        // optimistic UI
        setUI(intended);

        fetch("{{ route('facebook_feed.toggle') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ status: intended ? 1 : 0 })
        })
        .then(async (res) => {
            if(!res.ok){
                const t = await res.text();
                throw new Error(t || 'Request failed');
            }
            return res.json();
        })
        .then((data) => {
            // backend যদি 'on/off' return করে
            const serverOn = (data.status === 'on' || data.status === 1 || data.status === true);

            toggle.checked = serverOn;
            setUI(serverOn);

            ok(serverOn ? 'Facebook Feed Enabled' : 'Facebook Feed Disabled');
        })
        .catch((e) => {
            // rollback
            toggle.checked = !intended;
            setUI(!intended);

            err('Toggle failed. Please try again.');
            console.error(e);
        });
    });

    // ✅ Copy (modern + fallback)
    copyBtn.addEventListener('click', async function(){
        const text = feedUrl.value;

        try{
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                feedUrl.removeAttribute('readonly');
                feedUrl.select();
                feedUrl.setSelectionRange(0, 99999);
                document.execCommand('copy');
                feedUrl.setAttribute('readonly', 'readonly');
                window.getSelection().removeAllRanges();
            }
            ok("Facebook Feed URL Copied!");
        }catch(e){
            err("Copy failed!");
        }
    });
})();
</script>
@endpush
