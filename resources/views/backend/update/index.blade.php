@extends('backend.app') 

@section('content')
<div class="container">
    
    {{-- সাকসেস বা এরর মেসেজ দেখানোর সেকশন --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
            <strong>Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
            <strong>Success:</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- মেসেজ সেকশন শেষ --}}

    <div class="card mt-4">
        <div class="card-header">
            <h4>System Update</h4>
        </div>
        <div class="card-body">
            <p><strong>Current Version:</strong> {{ $currentVersion }}</p>

            @if(isset($data['update']) && $data['update'] == true)
                <div class="alert alert-success">
                    <h5>🚀 New Update Available: Version {{ $data['version'] }}</h5>
                    <p><strong>Changelog:</strong> <br> {!! nl2br(e($data['changelog'])) !!}</p>
                    
                    <form action="{{ route('update.process') }}" method="POST">
                        @csrf
                        <input type="hidden" name="download_url" value="{{ $data['download_url'] }}">
                        <input type="hidden" name="version" value="{{ $data['version'] }}"> 
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to update the system? Make sure to backup your database first.')">
                            Install Update Now
                        </button>
                    </form>
                </div>
            @else
                <div class="alert alert-info">
                    Your system is up to date! No new updates available.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection