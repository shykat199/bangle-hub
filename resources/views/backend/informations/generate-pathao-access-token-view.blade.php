@extends('backend.app')
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Generate Pathao Courier Access Token</li>
                </ol>
            </div>
            <h4 class="page-title">Generate Pathao Courier Access Token</h4>
        </div>
    </div>
</div>   
<!-- end page title --> 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary mb-2" id="copyBtn">Copy Token</button>
                <pre id="tokenText" style="user-select: all;">
                    {{ $tokenData['access_token'] ?? json_encode($tokenData, JSON_PRETTY_PRINT) }}
                </pre>
            </div>
        </div>
    </div>
</div>

@endsection 

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">

document.getElementById('copyBtn').addEventListener('click', function() {
    const tokenText = document.getElementById('tokenText').innerText;
    const tempInput = document.createElement('textarea');
    tempInput.value = tokenText;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999);

    document.execCommand('copy');
    document.body.removeChild(tempInput);
    alert('Token copied to clipboard!');
});

  
</script>

@endpush