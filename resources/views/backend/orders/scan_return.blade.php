@extends('backend.app')

@push('css')
<style>
    :root {
        --primary: #0ea5e9;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f97316;
        --bg-soft: #f8fafc;
        --text-dark: #0f172a;
    }

    .scanner-card {
        background: #fff;
        border: none;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(15,23,42,0.08);
        overflow: hidden;
    }

    .scanner-header {
        background: linear-gradient(135deg, var(--primary), #3b82f6);
        padding: 25px;
        text-align: center;
        color: #fff;
    }

    .scanner-header.bg-missing {
        background: linear-gradient(135deg, var(--danger), var(--warning));
    }

    .scanner-header i { font-size: 40px; margin-bottom: 5px; display: inline-block; }
    .scanner-header h3 { margin: 0; font-weight: 800; letter-spacing: 1px; font-size: 20px;}
    
    .scanner-body { padding: 30px; text-align: center; background: var(--bg-soft); }

    .barcode-input {
        width: 100%; height: 80px; border: 3px solid #cbd5e1; border-radius: 16px;
        font-size: 28px; font-weight: 700; text-align: center; color: var(--text-dark);
        box-shadow: 0 8px 16px rgba(0,0,0,0.05); transition: all 0.3s ease;
    }

    .barcode-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 5px rgba(14, 165, 233, 0.2); }
    .barcode-input::placeholder { color: #94a3b8; font-size: 20px; font-weight: 500; }

    .pulse-animation { animation: pulseBorder 2s infinite; }
    @keyframes pulseBorder {
        0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(14, 165, 233, 0); }
        100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); }
    }

    .history-card {
        background: #fff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        margin-top: 20px; border: 1px solid #e2e8f0;
    }
    .history-card th { background: #f1f5f9; font-size: 12px; text-transform: uppercase; color: #64748b; padding: 15px; }
    .history-card td { vertical-align: middle; padding: 15px; font-size: 14px; font-weight: 500; border-bottom: 1px solid #f1f5f9; }

    .badge-status {
        padding: 6px 12px; border-radius: 50px; font-size: 11px; font-weight: 700;
        background: #dcfce7; color: #166534; display: inline-flex; align-items: center; gap: 5px;
    }
    
    .manifest-textarea {
        border-radius: 16px; border: 2px solid #cbd5e1; padding: 15px; font-size: 14px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); resize: none; font-family: monospace;
    }
    .manifest-textarea:focus { border-color: var(--danger); outline: none; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="scanner-card">
                <div class="scanner-header">
                    <i class="mdi mdi-barcode-scan"></i>
                    <h3>SMART RETURN SCANNER</h3>
                    <p class="mb-0 mt-1 text-white-50">Scan parcel barcode to auto-receive and update stock</p>
                </div>
                <div class="scanner-body">
                    <form id="scannerForm">
                        @csrf
                        <div class="position-relative max-w-500 mx-auto">
                            <input type="text" id="barcode" name="barcode" class="barcode-input pulse-animation" placeholder="Point scanner here & click..." autocomplete="off">
                        </div>
                        <div class="mt-4 text-muted fw-bold">
                            <i class="mdi mdi-keyboard-outline me-1"></i> Ready for scan. Keyboard 'Enter' submits automatically.
                        </div>
                    </form>
                </div>
            </div>

            <div class="history-card">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light" style="border-radius: 16px 16px 0 0;">
                    <h5 class="m-0 fw-bold text-dark"><i class="mdi mdi-history text-primary"></i> Scanned Today</h5>
                    <span class="badge bg-primary rounded-pill fs-6" id="scanCount">0</span>
                </div>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table mb-0 text-center">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Action Taken</th>
                            </tr>
                        </thead>
                        <tbody id="scanHistory">
                            <tr id="emptyRow">
                                <td colspan="3" class="text-muted py-5">
                                    <i class="mdi mdi-package-variant-closed fs-1 d-block mb-2 text-light"></i>
                                    No parcels scanned yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            
            <div class="card shadow-sm border-0 rounded-3 mb-4" style="background-color: #f8fafc; border: 2px dashed #cbd5e1 !important;">
                <div class="card-body p-4 text-center">
                    <i class="mdi mdi-file-excel text-success" style="font-size: 40px;"></i>
                    <h5 class="fw-bold text-dark mt-2">Return Reconciliation (CSV Check)</h5>
                    <p class="text-muted mb-3" style="font-size: 13px;">
                        কুরিয়ার থেকে ডাউনলোড করা রিটার্ন রিপোর্ট (CSV) আপলোড করুন। <br>
                        <strong class="text-danger">নিয়ম:</strong> ফাইলের প্রথম কলামে (Column A) ইনভয়েস বা ট্র্যাকিং নাম্বার থাকতে হবে।
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success fw-bold text-start" style="font-size: 13px; padding: 10px;">
                            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger fw-bold text-start" style="font-size: 13px; padding: 10px;">
                            <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.scan_return.missing_csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3 text-start">
                            <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" onclick="this.innerHTML='<i class=\'mdi mdi-spin mdi-loading\'></i> Processing...'; this.classList.add('disabled'); this.form.submit();">
                            <i class="mdi mdi-cloud-upload"></i> Upload & Auto Check Missing
                        </button>
                    </form>
                </div>
            </div>

            <div class="scanner-card border border-danger">
                <div class="scanner-header bg-missing">
                    <i class="mdi mdi-clipboard-text-search-outline"></i>
                    <h3>MISSING CHECKER (MANUAL)</h3>
                    <p class="mb-0 mt-1 text-white-50">Paste courier list to find & mark unscanned parcels</p>
                </div>
                <div class="scanner-body text-start" style="background: #fff;">
                    <label class="fw-bold text-dark mb-2"><i class="mdi mdi-format-list-numbered"></i> Paste Invoice/Tracking IDs (One per line):</label>
                    <textarea id="manifestList" class="form-control manifest-textarea" rows="8" placeholder="e.g.&#10;INV-12345&#10;INV-12346&#10;INV-12347"></textarea>
                    
                    <div class="alert alert-warning mt-3 border-0" style="background:#fff7ed; color:#c2410c; border-radius:12px; font-size:13px;">
                        <i class="mdi mdi-information"></i> <b>How it works:</b> Paste the list here. Scan your physical parcels on the left. Then click the button below to auto-mark the remaining as <b>"Return Missing"</b>.
                    </div>

                    <button id="btnMarkMissing" class="btn btn-danger w-100 py-3 fw-bold shadow-sm mt-2" type="button" style="border-radius: 14px; font-size:16px;">
                        <i class="mdi mdi-alert-decagram fs-5 align-middle me-1"></i> Submit & Check Missing
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let scanCount = 0;

        $('#barcode').focus();
        
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playSound(type) {
            if(audioCtx.state === 'suspended') audioCtx.resume();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            if(type === 'success') {
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(800, audioCtx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                oscillator.start(); oscillator.stop(audioCtx.currentTime + 0.1);
            } else {
                oscillator.type = 'sawtooth';
                oscillator.frequency.setValueAtTime(200, audioCtx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(150, audioCtx.currentTime + 0.3);
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                oscillator.start(); oscillator.stop(audioCtx.currentTime + 0.3);
            }
        }

        $('#scannerForm').on('submit', function(e) {
            e.preventDefault();
            let barcode = $('#barcode').val().trim();
            if(!barcode) return;

            $('#barcode').removeClass('pulse-animation').prop('readonly', true);

            $.ajax({
                url: "{{ route('admin.scan_return.submit') }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}", barcode: barcode },
                success: function(res) {
                    $('#barcode').val('').prop('readonly', false).addClass('pulse-animation').focus();
                    if(res.status) {
                        playSound('success');
                        toastr.success(res.msg);
                        
                        $('#emptyRow').remove();
                        scanCount++;
                        $('#scanCount').text(scanCount);

                        let row = `<tr style="background:#f0fdf4; transition: 1s;" class="new-row">
                            <td class="text-primary fw-bold">#${res.invoice}</td>
                            <td class="fw-bold">${res.customer}</td>
                            <td><span class="badge-status"><i class="mdi mdi-check-circle"></i> Returned & Stock Added</span></td>
                        </tr>`;
                        $('#scanHistory').prepend(row);
                        
                        setTimeout(() => { $('.new-row').css('background', '#fff').removeClass('new-row'); }, 1000);
                    } else {
                        playSound('error'); toastr.error(res.msg);
                    }
                },
                error: function() {
                    $('#barcode').val('').prop('readonly', false).addClass('pulse-animation').focus();
                    playSound('error'); toastr.error('Server Error! Cannot process scan.');
                }
            });
        });

        $('#btnMarkMissing').click(function() {
            let listText = $('#manifestList').val();
            let lines = listText.split('\n').map(item => item.trim()).filter(item => item);
            
            if(lines.length === 0) {
                toastr.warning('Please paste the courier list first!');
                $('#manifestList').focus();
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                html: `You pasted <b>${lines.length}</b> parcels.<br>Scanned ones are already marked received.<br>Unscanned ones will be marked as <b>Return Missing</b>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Find Missing!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let btn = $('#btnMarkMissing');
                    btn.html('<i class="mdi mdi-spin mdi-loading"></i> Checking...').prop('disabled', true);

                    $.ajax({
                        url: "{{ route('admin.scan_return.missing') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            invoices: lines
                        },
                        success: function(res) {
                            btn.html('<i class="mdi mdi-alert-decagram fs-5 align-middle me-1"></i> Submit & Check Missing').prop('disabled', false);
                            
                            if(res.status) {
                                if(res.missing_count > 0) {
                                    playSound('error');
                                    Swal.fire('Alert!', `Found ${res.missing_count} missing parcels! Marked as "Return Missing".`, 'warning');
                                } else {
                                    playSound('success');
                                    Swal.fire('Excellent!', 'No missing parcels found. You successfully scanned everything on the list.', 'success');
                                }
                                $('#manifestList').val('');
                            } else {
                                toastr.error(res.msg);
                            }
                        },
                        error: function() {
                            btn.html('<i class="mdi mdi-alert-decagram fs-5 align-middle me-1"></i> Submit & Check Missing').prop('disabled', false);
                            toastr.error('Server Error!');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush