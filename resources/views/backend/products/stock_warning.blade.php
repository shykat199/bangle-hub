@extends('backend.app')
@section('content')

<style>
    /* টেবিল অপ্টিমাইজেশন স্টাইল */
    .compact-table thead th {
        font-size: 13px;
        font-weight: 700;
        padding: 10px 5px;
        background-color: #f8f9fa;
        white-space: nowrap; /* হেডার এক লাইনে থাকবে */
    }
    
    .compact-table tbody td {
        font-size: 13px;
        padding: 8px 5px;
        vertical-align: middle;
    }

    /* প্রোডাক্ট ইমেজ সাইজ কন্ট্রোল */
    .product-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    /* টেক্সট র‍্যাপিং (Desktop scroll বন্ধ করার জন্য) */
    .text-wrap-custom {
        white-space: normal !important;
        word-wrap: break-word;
        max-width: 250px; /* নাম বেশি বড় হলে নিচে নামবে */
        line-height: 1.3;
    }

    /* মোবাইল ডিভাইসে ফন্ট আরও ছোট এবং টাইট করা */
    @media (max-width: 767px) {
        .compact-table thead th, 
        .compact-table tbody td {
            font-size: 11px;
            padding: 5px 2px;
        }
        .product-thumb {
            width: 30px;
            height: 30px;
        }
        .text-wrap-custom {
            max-width: 120px;
        }
        .btn-sm-custom {
            padding: 2px 5px;
            font-size: 10px;
        }
        .hide-mobile {
            display: none; /* মোবাইলে কম গুরুত্বপূর্ণ কলাম হাইড করা (যদি লাগে) */
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row mt-3">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="page-title text-danger mb-0" style="font-size: 1.2rem;">
                            <i class="uil uil-exclamation-triangle me-1"></i> Stock Warning
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0" style="font-size: 0.8rem;">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Warning</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-2"> 
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    {{-- ✅✅✅ NEW: Dynamic threshold value added here --}}
                                    <h6 class="text-muted m-0" style="font-size: 0.85rem;">
                                        Low stock items (&le; {{ $threshold ?? 5 }})
                                    </h6>
                                </div>
                                <div>
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm border">
                                        <i class="uil uil-arrow-left"></i> <span class="d-none d-sm-inline">Back</span>
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-centered compact-table mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%;">Product</th>
                                            <th style="width: 15%;">SKU</th>
                                            <th style="width: 20%;">Variant</th>
                                            <th class="text-center" style="width: 10%;">Stock</th>
                                            <th class="text-center" style="width: 10%;">Price</th>
                                            <th class="text-end" style="width: 10%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->product)
                                                        <img src="{{ asset('products/'.$item->product->image) }}" 
                                                             alt="img" class="product-thumb me-2">
                                                        <div class="text-wrap-custom">
                                                            <span class="fw-bold text-dark d-block">{{ $item->product->name }}</span>
                                                            <small class="text-muted" style="font-size: 10px;">ID: {{ $item->product->id }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-danger small">Product Deleted</span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="text-wrap-custom small text-muted">
                                                {{ $item->product->sku ?? '--' }}
                                            </td>

                                            <td>
                                                @if($item->variation)
                                                    <div class="d-flex flex-column gap-1">
                                                        @if(optional($item->variation->size)->name)
                                                            <span class="badge bg-light text-dark border fw-normal" style="font-size: 10px; width: fit-content;">
                                                                S: {{ $item->variation->size->name }}
                                                            </span>
                                                        @endif
                                                        @if(optional($item->variation->color)->name)
                                                            <span class="badge bg-light text-dark border fw-normal" style="font-size: 10px; width: fit-content;">
                                                                C: {{ $item->variation->color->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if($item->quantity == 0)
                                                    <span class="badge bg-danger" style="font-size: 10px;">0</span>
                                                @else
                                                    <span class="badge bg-warning text-dark" style="font-size: 10px;">{{ $item->quantity }}</span>
                                                @endif
                                            </td>

                                            <td class="text-center fw-bold text-dark small">
                                                {{ $item->variation ? $item->variation->price : ($item->product->sell_price ?? 0) }}
                                            </td>

                                            <td class="text-end">
                                                @if($item->product)
                                                <a href="{{ route('admin.products.edit', $item->product->id) }}" 
                                                   class="btn btn-primary btn-sm btn-sm-custom" 
                                                   title="Restock">
                                                    <i class="uil uil-edit-alt"></i>
                                                </a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="uil uil-check-circle text-success" style="font-size: 2rem;"></i>
                                                    <span class="text-muted small mt-1">All stocks are healthy!</span>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div> 
                            <div class="mt-3">
                                {{ $items->links() }}
                            </div>

                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</div>

@endsection