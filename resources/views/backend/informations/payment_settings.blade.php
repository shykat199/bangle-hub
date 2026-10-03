@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    :root {
        --primary-color: #556ee6;
        --primary-soft: rgba(85, 110, 230, 0.08);
        --text-dark: #343a40;
        --text-muted: #74788d;
        --bg-body: #f8f8fb;
        --card-bg: #ffffff;
        --border-radius: 16px;
        --transition: all 0.3s ease-in-out;
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
        --shadow-md: 0 8px 20px rgba(18, 38, 63, 0.06);
    }

    .page-title-box h4 {
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    .accordion-item {
        border: 1px solid rgba(0,0,0,0.03);
        border-radius: var(--border-radius);
        background: var(--card-bg);
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: var(--transition);
    }

    .accordion-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .accordion-button {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        background-color: var(--card-bg);
        padding: 1.5rem;
        border: none;
        box-shadow: none !important;
        display: flex;
        align-items: center;
    }

    .header-icon {
        width: 45px;
        height: 45px;
        background: var(--bg-body);
        color: var(--primary-color);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 18px;
        font-size: 20px;
        transition: var(--transition);
    }

    .accordion-button::after { display: none; }

    .custom-chevron {
        margin-left: auto;
        font-size: 1.5rem;
        color: #adb5bd;
        transition: transform 0.3s;
    }

    .accordion-button:not(.collapsed) {
        background-color: #fff;
        color: var(--primary-color);
    }

    .accordion-button:not(.collapsed) .header-icon {
        background: var(--primary-color);
        color: #fff;
        box-shadow: 0 4px 10px rgba(85, 110, 230, 0.3);
    }

    .accordion-button:not(.collapsed) .custom-chevron {
        transform: rotate(180deg);
        color: var(--primary-color);
    }

    .accordion-item:has(.accordion-button:not(.collapsed)) {
        border-left: 4px solid var(--primary-color);
    }

    .accordion-body {
        padding: 0 1.5rem 2rem 1.5rem;
        background-color: #fff;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.88rem;
        margin-bottom: 0.6rem;
    }
    
    .form-control, .form-select {
        border: 1px solid #e2e5e8;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: var(--transition);
        background-color: #fdfdfd;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 4px var(--primary-soft);
    }

    .sticky-actions {
        position: sticky;
        bottom: 25px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 15px 30px;
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        border: 1px solid rgba(255,255,255,0.5);
        z-index: 1000;
        transition: transform 0.3s;
    }

    .sticky-actions:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }

    .btn-save {
        background: linear-gradient(135deg, #556ee6, #34c38f);
        border: none;
        color: white;
        padding: 12px 40px;
        border-radius: 30px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        box-shadow: 0 4px 15px rgba(52, 195, 143, 0.4);
        transition: all 0.3s;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 195, 143, 0.6);
        color: white;
    }

    @media (max-width: 768px) {
        .sticky-actions {
            flex-direction: column;
            bottom: 15px;
            padding: 15px;
            border-radius: 20px;
        }
        .btn-save { width: 100%; }
        .sticky-text { display: none; }
    }
</style>
@endpush

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-0">Payment Gateways Setup</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                    <li class="breadcrumb-item active">Payment Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.update', [$information->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @if(Session::has('msg'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-decagram fs-3 me-2"></i>
                <div>{{ Session::get('msg') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="accordion" id="settingsAccordion">

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingCod">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCod" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-cash-multiple"></i></span> 
                    Cash on Delivery (COD) Settings
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseCod" class="accordion-collapse collapse" aria-labelledby="headingCod" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="p-3 border rounded-3 bg-white mb-4" style="border-left: 4px solid #34c38f !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Gateway Status</h6>
                                <small class="text-muted">Enable or Disable Cash on Delivery option.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="cod_active" value="0">
                                <input class="form-check-input" type="checkbox" id="codActiveSwitch" name="cod_active" value="1" {{ ($information->cod_active ?? 1) == 1 ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label fw-bold ms-2 mt-1" for="codActiveSwitch" id="codActiveLabel">
                                    {{ ($information->cod_active ?? 1) == 1 ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSsl">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSsl" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-credit-card-outline"></i></span> 
                    SSLCommerz Payment Gateway
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseSsl" class="accordion-collapse collapse" aria-labelledby="headingSsl" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="p-3 border rounded-3 bg-white mb-4" style="border-left: 4px solid #556ee6 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Gateway Status</h6>
                                <small class="text-muted">Enable or Disable SSLCommerz payment on checkout.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="ssl_active" value="0">
                                <input class="form-check-input" type="checkbox" id="sslActiveSwitch" name="ssl_active" value="1" {{ ($information->ssl_active ?? 1) == 1 ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label fw-bold ms-2 mt-1" for="sslActiveSwitch" id="sslActiveLabel">
                                    {{ ($information->ssl_active ?? 1) == 1 ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded-3 bg-white mb-4" style="border-left: 4px solid #50a5f1 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Terms & Conditions Checkbox</h6>
                                <small class="text-muted">Show/Hide "I agree to Terms & Conditions" checkbox on checkout.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="ssl_terms_active" value="0">
                                <input class="form-check-input" type="checkbox" id="sslTermsSwitch" name="ssl_terms_active" value="1" {{ ($information->ssl_terms_active ?? 0) == 1 ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label fw-bold ms-2 mt-1" for="sslTermsSwitch" id="sslTermsLabel">
                                    {{ ($information->ssl_terms_active ?? 0) == 1 ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded-3 bg-light mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Active Payment Mode</h6>
                                <small class="text-muted">Select which credentials to use for transactions.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="ssl_sandbox" value="0">
                                <input class="form-check-input" type="checkbox" id="sandboxMode" name="ssl_sandbox" value="1" {{ ($information->ssl_sandbox ?? 1) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="sandboxMode" id="sandboxLabel">
                                    {{ ($information->ssl_sandbox ?? 1) == 1 ? 'Sandbox (Test Mode)' : 'Live Mode' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 border-end">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-warning fw-bold mb-0"><i class="mdi mdi-flask-outline me-1"></i> Sandbox (Test) Config</h6>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Test Store ID</label>
                                <input type="text" id="ssl_sandbox_store_id" class="form-control bg-light" name="ssl_sandbox_store_id" value="{{ $information->ssl_sandbox_store_id ?? '' }}" placeholder="">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Test Store Password</label>
                                <input type="text" id="ssl_sandbox_store_password" class="form-control bg-light" name="ssl_sandbox_store_password" value="{{ $information->ssl_sandbox_store_password ?? '' }}" placeholder="">
                            </div>
                        </div>

                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-success fw-bold mb-3"><i class="mdi mdi-lock-check-outline me-1"></i> Live (Real) Config</h6>
                            <div class="mb-3">
                                <label class="form-label">Live Store ID</label>
                                <input type="text" class="form-control" name="ssl_store_id" value="{{ $information->ssl_store_id ?? '' }}" placeholder="Enter Live Store ID">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Live Store Password</label>
                                <input type="text" class="form-control" name="ssl_store_password" value="{{ $information->ssl_store_password ?? '' }}" placeholder="Enter Live Store Password">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingBkash">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBkash" aria-expanded="false">
                    <span class="header-icon"><i class="mdi mdi-cellphone-link"></i></span> 
                    bKash Payment Gateway
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseBkash" class="accordion-collapse collapse" aria-labelledby="headingBkash" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="p-3 border rounded-3 bg-white mb-4" style="border-left: 4px solid #E2136E !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Gateway Status</h6>
                                <small class="text-muted">Enable or Disable bKash payment on checkout.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="bkash_active" value="0">
                                <input class="form-check-input" type="checkbox" id="bkashActiveSwitch" name="bkash_active" value="1" {{ ($information->bkash_active ?? 0) == 1 ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label fw-bold ms-2 mt-1" for="bkashActiveSwitch" id="bkashActiveLabel">
                                    {{ ($information->bkash_active ?? 0) == 1 ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded-3 bg-light mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Active Payment Mode</h6>
                                <small class="text-muted">Select which credentials to use for bKash transactions.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="bkash_sandbox" value="0">
                                <input class="form-check-input" type="checkbox" id="bkashSandboxMode" name="bkash_sandbox" value="1" {{ ($information->bkash_sandbox ?? 1) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="bkashSandboxMode" id="bkashSandboxLabel">
                                    {{ ($information->bkash_sandbox ?? 1) == 1 ? 'Sandbox (Test Mode)' : 'Live Mode' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">App Key</label>
                                <input type="text" class="form-control" name="bkash_app_key" value="{{ $information->bkash_app_key ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">App Secret</label>
                                <input type="text" class="form-control" name="bkash_app_secret" value="{{ $information->bkash_app_secret ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="bkash_username" value="{{ $information->bkash_username ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="bkash_password" value="{{ $information->bkash_password ?? '' }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingNagad">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNagad" aria-expanded="false">
                    <span class="header-icon" style="color: #ED1C24;"><i class="mdi mdi-cellphone-nfc"></i></span> 
                    Nagad Payment Gateway
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseNagad" class="accordion-collapse collapse" aria-labelledby="headingNagad" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="p-3 border rounded-3 bg-white mb-4" style="border-left: 4px solid #ED1C24 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Gateway Status</h6>
                                <small class="text-muted">Enable or Disable Nagad payment on checkout.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="nagad_active" value="0">
                                <input class="form-check-input" type="checkbox" id="nagadActiveSwitch" name="nagad_active" value="1" {{ ($information->nagad_active ?? 0) == 1 ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label fw-bold ms-2 mt-1" for="nagadActiveSwitch" id="nagadActiveLabel">
                                    {{ ($information->nagad_active ?? 0) == 1 ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded-3 bg-light mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Active Payment Mode</h6>
                                <small class="text-muted">Select which credentials to use for Nagad transactions.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="nagad_sandbox" value="0">
                                <input class="form-check-input" type="checkbox" id="nagadSandboxMode" name="nagad_sandbox" value="1" {{ ($information->nagad_sandbox ?? 1) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="nagadSandboxMode" id="nagadSandboxLabel">
                                    {{ ($information->nagad_sandbox ?? 1) == 1 ? 'Sandbox (Test Mode)' : 'Live Mode' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 border-end">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-warning fw-bold mb-0"><i class="mdi mdi-flask-outline me-1"></i> Sandbox (Test) Config</h6>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Merchant ID (Sandbox)</label>
                                <input type="text" class="form-control bg-light" name="nagad_sandbox_merchant_id" value="{{ $information->nagad_sandbox_merchant_id ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Merchant Number (Sandbox)</label>
                                <input type="text" class="form-control bg-light" name="nagad_sandbox_merchant_number" value="{{ $information->nagad_sandbox_merchant_number ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Public Key (Sandbox)</label>
                                <textarea class="form-control bg-light" name="nagad_sandbox_public_key" rows="4">{{ $information->nagad_sandbox_public_key ?? '' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Private Key (Sandbox)</label>
                                <textarea class="form-control bg-light" name="nagad_sandbox_private_key" rows="4">{{ $information->nagad_sandbox_private_key ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-success fw-bold mb-3"><i class="mdi mdi-lock-check-outline me-1"></i> Live (Real) Config</h6>
                            <div class="mb-3">
                                <label class="form-label">Merchant ID (Live)</label>
                                <input type="text" class="form-control" name="nagad_merchant_id" value="{{ $information->nagad_merchant_id ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Merchant Number (Live)</label>
                                <input type="text" class="form-control" name="nagad_merchant_number" value="{{ $information->nagad_merchant_number ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Public Key (Live)</label>
                                <textarea class="form-control" name="nagad_public_key" rows="4">{{ $information->nagad_public_key ?? '' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Private Key (Live)</label>
                                <textarea class="form-control" name="nagad_private_key" rows="4">{{ $information->nagad_private_key ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingEps">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEps" aria-expanded="false">
                    <span class="header-icon" style="color: #17a2b8;"><i class="mdi mdi-credit-card-wireless"></i></span> 
                    EPS Payment Gateway
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseEps" class="accordion-collapse collapse" aria-labelledby="headingEps" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="p-3 border rounded-3 bg-white mb-4" style="border-left: 4px solid #17a2b8 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Gateway Status</h6>
                                <small class="text-muted">Enable or Disable EPS payment on checkout.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="eps_active" value="0">
                                <input class="form-check-input" type="checkbox" id="epsActiveSwitch" name="eps_active" value="1" {{ ($information->eps_active ?? 0) == 1 ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label fw-bold ms-2 mt-1" for="epsActiveSwitch" id="epsActiveLabel">
                                    {{ ($information->eps_active ?? 0) == 1 ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded-3 bg-light mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Active Payment Mode</h6>
                                <small class="text-muted">Select which credentials to use for EPS transactions.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="eps_sandbox" value="0">
                                <input class="form-check-input" type="checkbox" id="epsSandboxMode" name="eps_sandbox" value="1" {{ ($information->eps_sandbox ?? 1) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="epsSandboxMode" id="epsSandboxLabel">
                                    {{ ($information->eps_sandbox ?? 1) == 1 ? 'Sandbox (Test Mode)' : 'Live Mode' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 border-end">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-warning fw-bold mb-0"><i class="mdi mdi-flask-outline me-1"></i> Sandbox (Test) Config</h6>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Merchant ID</label>
                                <input type="text" class="form-control bg-light" name="eps_sandbox_merchant_id" value="{{ $information->eps_sandbox_merchant_id ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Store ID</label>
                                <input type="text" class="form-control bg-light" name="eps_sandbox_store_id" value="{{ $information->eps_sandbox_store_id ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control bg-light" name="eps_sandbox_username" value="{{ $information->eps_sandbox_username ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control bg-light" name="eps_sandbox_password" value="{{ $information->eps_sandbox_password ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hash Key</label>
                                <input type="text" class="form-control bg-light" name="eps_sandbox_hash_key" value="{{ $information->eps_sandbox_hash_key ?? '' }}">
                            </div>
                        </div>

                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-success fw-bold mb-3"><i class="mdi mdi-lock-check-outline me-1"></i> Live (Real) Config</h6>
                            <div class="mb-3">
                                <label class="form-label">Merchant ID</label>
                                <input type="text" class="form-control" name="eps_merchant_id" value="{{ $information->eps_merchant_id ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Store ID</label>
                                <input type="text" class="form-control" name="eps_store_id" value="{{ $information->eps_store_id ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="eps_username" value="{{ $information->eps_username ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="eps_password" value="{{ $information->eps_password ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hash Key</label>
                                <input type="text" class="form-control" name="eps_hash_key" value="{{ $information->eps_hash_key ?? '' }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingUddoktapay">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUddoktapay" aria-expanded="false">
                    <span class="header-icon" style="color: #28a745;"><i class="mdi mdi-wallet-outline"></i></span> 
                    UddoktaPay Payment Gateway
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseUddoktapay" class="accordion-collapse collapse" aria-labelledby="headingUddoktapay" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="p-3 border rounded-3 bg-white mb-4" style="border-left: 4px solid #28a745 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Gateway Status</h6>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="uddoktapay_active" value="0">
                                <input class="form-check-input" type="checkbox" id="uddoktapayActiveSwitch" name="uddoktapay_active" value="1" {{ ($information->uddoktapay_active ?? 0) == 1 ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                <label class="form-check-label fw-bold ms-2 mt-1" for="uddoktapayActiveSwitch" id="uddoktapayActiveLabel">
                                    {{ ($information->uddoktapay_active ?? 0) == 1 ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Base URL / Panel URL</label>
                                <input type="text" class="form-control" name="uddoktapay_base_url" value="{{ $information->uddoktapay_base_url ?? '' }}" placeholder="https://sandbox.uddoktapay.com/">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">API Key</label>
                                <input type="text" class="form-control" name="uddoktapay_api_key" value="{{ $information->uddoktapay_api_key ?? '' }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingManual">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManual" aria-expanded="false">
                    <span class="header-icon" style="color: #6f42c1;"><i class="mdi mdi-cash-register"></i></span> 
                    Manual Payment Setup
                    <i class="mdi mdi-chevron-down custom-chevron"></i>
                </button>
            </h2>
            <div id="collapseManual" class="accordion-collapse collapse" aria-labelledby="headingManual" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    
                    <div class="p-3 border rounded-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Manage Accounts</h6>
                                <small class="text-muted">আপনার ম্যানুয়াল পেমেন্ট নম্বরগুলো (যেমন- বিকাশ পার্সোনাল, নগদ পার্সোনাল ইত্যাদি) এখানে পরিচালনা করুন।</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addManualPaymentModal">
                                <i class="mdi mdi-plus"></i> Add New Method
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered bg-white mb-0 text-center align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Method Name</th>
                                        <th>Number</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments ?? [] as $payment)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $payment->name }}</td>
                                        <td>{{ $payment->number }}</td>
                                        <td>{{ $payment->type }}</td>
                                        
                                        <td>
                                            <div class="form-check form-switch d-flex justify-content-center align-items-center">
                                                <input type="hidden" name="manual_status[{{ $payment->id }}]" value="0">
                                                <input class="form-check-input manual-item-switch" type="checkbox" 
                                                       id="manualItemSwitch{{ $payment->id }}" 
                                                       name="manual_status[{{ $payment->id }}]" 
                                                       value="1" 
                                                       {{ $payment->status == 1 ? 'checked' : '' }} 
                                                       style="width: 3rem; height: 1.5rem; cursor: pointer; margin: 0;">
                                                       
                                                <label class="form-check-label fw-bold ms-2 mb-0" for="manualItemSwitch{{ $payment->id }}" id="manualItemLabel{{ $payment->id }}" style="min-width: 65px; text-align: left;">
                                                    @if($payment->status == 1)
                                                        <span class="text-success">Active</span>
                                                    @else
                                                        <span class="text-danger">Inactive</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </td>
                            
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-danger px-3" onclick="deleteManualPayment({{ $payment->id }})">
                                                <i class="mdi mdi-trash-can-outline"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No manual payment methods added yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="sticky-actions">
        <div class="sticky-text text-muted">
            <i class="mdi mdi-information-outline"></i> Ensure all API keys are valid before saving.
        </div>
        <button type="submit" class="btn-save">
            Save Payment Settings
        </button>
    </div>

</form>

<div class="modal fade" id="addManualPaymentModal" tabindex="-1" aria-labelledby="addManualPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.manual_payments.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="addManualPaymentModalLabel"><i class="mdi mdi-plus-circle-outline"></i> Add New Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="form-label text-dark fw-bold">Method Name (e.g. bKash, Nagad)</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter method name">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label text-dark fw-bold">Receiver Number</label>
                        <input type="text" name="number" class="form-control" required placeholder="Enter phone/account number">
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label text-dark fw-bold">Account Type (Personal/Agent)</label>
                        <input type="text" name="type" class="form-control" value="Personal">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Save Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(isset($payments))
    @foreach($payments as $payment)
        <form id="delete-form-{{ $payment->id }}" action="{{ route('admin.manual_payments.destroy', $payment->id) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    function deleteManualPayment(id) {
        if(confirm('Are you sure you want to delete this payment method?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }

    document.getElementById('sandboxMode')?.addEventListener('change', function() {
        let label = document.getElementById('sandboxLabel');
        if(this.checked) {
            label.innerText = 'Sandbox (Test Mode)';
            label.classList.remove('text-success');
            label.classList.add('text-warning');
        } else {
            label.innerText = 'Live Mode';
            label.classList.remove('text-warning');
            label.classList.add('text-success');
        }
    });

    document.getElementById('bkashSandboxMode')?.addEventListener('change', function() {
        let label = document.getElementById('bkashSandboxLabel');
        if(this.checked) {
            label.innerText = 'Sandbox (Test Mode)';
            label.classList.remove('text-success');
            label.classList.add('text-warning');
        } else {
            label.innerText = 'Live Mode';
            label.classList.remove('text-warning');
            label.classList.add('text-success');
        }
    });

    document.getElementById('epsSandboxMode')?.addEventListener('change', function() {
        let label = document.getElementById('epsSandboxLabel');
        if(this.checked) {
            label.innerText = 'Sandbox (Test Mode)';
            label.classList.remove('text-success');
            label.classList.add('text-warning');
        } else {
            label.innerText = 'Live Mode';
            label.classList.remove('text-warning');
            label.classList.add('text-success');
        }
    });

    document.getElementById('nagadSandboxMode')?.addEventListener('change', function() {
        let label = document.getElementById('nagadSandboxLabel');
        if(this.checked) {
            label.innerText = 'Sandbox (Test Mode)';
            label.classList.remove('text-success');
            label.classList.add('text-warning');
        } else {
            label.innerText = 'Live Mode';
            label.classList.remove('text-warning');
            label.classList.add('text-success');
        }
    });

    function setupSwitch(switchId, labelId) {
        const switchEl = document.getElementById(switchId);
        const labelEl = document.getElementById(labelId);
        
        if(switchEl && labelEl){
            if(switchEl.checked) {
                labelEl.innerText = 'Enabled';
                labelEl.classList.remove('text-danger');
                labelEl.classList.add('text-success');
            } else {
                labelEl.innerText = 'Disabled';
                labelEl.classList.remove('text-success');
                labelEl.classList.add('text-danger');
            }

            switchEl.addEventListener('change', function() {
                if(this.checked) {
                    labelEl.innerText = 'Enabled';
                    labelEl.classList.remove('text-danger');
                    labelEl.classList.add('text-success');
                } else {
                    labelEl.innerText = 'Disabled';
                    labelEl.classList.remove('text-success');
                    labelEl.classList.add('text-danger');
                }
            });
        }
    }

    setupSwitch('sslActiveSwitch', 'sslActiveLabel');
    setupSwitch('codActiveSwitch', 'codActiveLabel');
    setupSwitch('sslTermsSwitch', 'sslTermsLabel');
    setupSwitch('bkashActiveSwitch', 'bkashActiveLabel');
    setupSwitch('epsActiveSwitch', 'epsActiveLabel');
    setupSwitch('nagadActiveSwitch', 'nagadActiveLabel');
    setupSwitch('uddoktapayActiveSwitch', 'uddoktapayActiveLabel');

    // Manual Payment Item Switch Handler
    document.querySelectorAll('.manual-item-switch').forEach(function(switchEl) {
        switchEl.addEventListener('change', function() {
            let paymentId = this.id.replace('manualItemSwitch', '');
            let labelEl = document.getElementById('manualItemLabel' + paymentId);
            
            if(this.checked) {
                labelEl.innerHTML = '<span class="text-success">Active</span>';
            } else {
                labelEl.innerHTML = '<span class="text-danger">Inactive</span>';
            }
        });
    });

</script>
@endpush