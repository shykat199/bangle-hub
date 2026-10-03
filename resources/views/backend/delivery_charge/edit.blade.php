@extends('backend.app')
@section('content')

<style>
    .card-modern {
        border: 0;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15,23,42,.08);
        overflow: hidden;
    }
    .card-modern .card-header {
        background: linear-gradient(135deg,#eff6ff,#dbeafe);
        padding: 15px 20px;
        border-bottom: 1px solid rgba(15,23,42,.05);
    }
</style>

<div class="row mt-4">
    <div class="col-lg-6 col-md-8 mx-auto">
        <div class="card card-modern">
            <div class="card-header bg-primary" style="background: linear-gradient(135deg, #0ea5e9, #2563eb);">
                <h4 class="text-white mb-0">Edit Delivery Charge</h4>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.delivery_charge.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Delivery Charge Title</label>
                        <input type="text" class="form-control" name="title" value="{{ $item->title }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Delivery Charge Amount</label>
                        <input type="number" step="any" name="amount" class="form-control" value="{{ $item->amount }}" required>
                    </div>
                  
                    <div class="mb-4">
                        <div class="form-check">
                          <input type="checkbox" name="status" class="form-check-input" id="statusCheck" value="1" {{ $item->status == '1' ? 'checked' : '' }}>
                          <label class="form-check-label fw-bold" for="statusCheck">Active</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.delivery_charge.index') }}" class="btn btn-secondary px-4" style="border-radius: 10px;">Back</a>
                        <button type="submit" class="btn btn-primary px-4" style="border:none; border-radius:10px; background:linear-gradient(to right,#0ea5e9,#2563eb);">Update Change</button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>

@endsection