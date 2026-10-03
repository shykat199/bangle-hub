@extends('backend.app')

@push('css')
<style>
    /* Custom Toggle Switch CSS */
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; margin-bottom: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);}
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);}
    input:checked + .slider { background-color: #10b981; }
    input:checked + .slider:before { transform: translateX(20px); }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mt-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <i class="uil uil-robot me-2"></i> Status-wise Auto Assign Rules
                    </h5>
                    
                    {{-- ✅ AUTO ASSIGN ON/OFF TOGGLE --}}
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold" id="botStatusText" style="font-size: 13px; color: {{ ($info->is_auto_assign ?? 0) ? '#10b981' : '#ef4444' }};">
                            {{ ($info->is_auto_assign ?? 0) ? 'BOT ON' : 'BOT OFF' }}
                        </span>
                        <label class="switch" title="Toggle Auto Assign">
                            <input type="checkbox" id="masterBotSwitch" {{ ($info->is_auto_assign ?? 0) ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <p class="text-muted" style="font-size: 14px;">
                        নিচের প্রতিটি স্ট্যাটাসের জন্য আপনি আলাদা আলাদা ওয়ার্কার সিলেক্ট করতে পারবেন। যে স্ট্যাটাসের জন্য যাদের সিলেক্ট করবেন, সেই অর্ডারের ক্ষেত্রে শুধু তাদের মাঝেই অর্ডারটি সমানভাবে ভাগ হবে। কোনো স্ট্যাটাসে টিক না দিলে সেটি অটো-অ্যাসাইন হবে না।
                    </p>

                    <form action="{{ route('admin.saveAutoAssignStatus') }}" method="POST">
                        @csrf

                        @foreach($statusList as $statusKey => $statusName)
                            {{-- স্ট্যাটাস কি-কে ছোট হাতের করে নেওয়া হলো যাতে কন্ট্রোলারের strtolower লজিকের সাথে মিলে যায় --}}
                            @php 
                                $lowerStatusKey = strtolower($statusKey); 
                            @endphp

                            {{-- শুধুমাত্র pending এবং incomplete চেক করা হচ্ছে --}}
                            @if(in_array($lowerStatusKey, ['pending', 'incomplete']))
                            
                            <div class="mb-3 p-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <label class="form-label fw-bold text-dark border-bottom pb-2 w-100" style="font-size: 15px;">
                                    <i class="uil uil-tag-alt text-primary"></i> {{ $statusName }} ({{ $statusKey }})
                                </label>
                                
                                @if($workers->count() > 0)
                                    <div class="row mt-2">
                                        @foreach($workers as $worker)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check custom-checkbox">
                                                {{-- name এবং id তে $lowerStatusKey ব্যবহার করা হয়েছে --}}
                                                <input class="form-check-input" type="checkbox" 
                                                       name="rules[{{ $lowerStatusKey }}][]" 
                                                       value="{{ $worker->id }}" 
                                                       id="worker_{{ $lowerStatusKey }}_{{ $worker->id }}" 
                                                    {{ in_array($worker->id, $savedRules[$lowerStatusKey] ?? []) ? 'checked' : '' }}>
                                                
                                                <label class="form-check-label fw-semibold" style="cursor: pointer; font-size: 13px;" for="worker_{{ $lowerStatusKey }}_{{ $worker->id }}">
                                                    {{ $worker->username ?? $worker->first_name }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-danger mb-0" style="font-size: 13px;">কোনো অ্যাক্টিভ ওয়ার্কার পাওয়া যায়নি!</p>
                                @endif
                            </div>
                            
                            @endif
                        @endforeach

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                <i class="uil uil-save me-1"></i> Save Routing Rules
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Master Switch Toggle (AJAX)
        $('#masterBotSwitch').change(function() {
            let isActive = $(this).is(':checked') ? 1 : 0;
            let text = isActive ? 'BOT ON' : 'BOT OFF';
            let color = isActive ? '#10b981' : '#ef4444';
            
            $('#botStatusText').text(text).css('color', color);

            $.ajax({
                url: "{{ route('admin.toggleAutoAssign') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    is_active: isActive
                },
                success: function(res) {
                    if(res.success) {
                        toastr.success(res.msg);
                    } else {
                        toastr.error('Failed to toggle bot status!');
                    }
                },
                error: function() {
                    toastr.error('Server error occurred!');
                }
            });
        });
    });
</script>
@endpush