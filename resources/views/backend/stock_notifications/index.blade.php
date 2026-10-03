@extends('backend.app')
@section('content')

<style>
    .sn-table thead th{ font-size: 13px; font-weight: 700; padding: 10px 8px; background: #f8f9fa; white-space: nowrap; }
    .sn-table tbody td{ font-size: 13px; padding: 8px; vertical-align: middle; }
    .sn-thumb{ width: 42px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6; flex: 0 0 auto; }
    .sn-name{ white-space: normal; line-height: 1.3; max-width: 320px; }
    .sn-phone{ font-weight: 600; color: #0f172a; white-space: nowrap; }
    .sn-call{
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 50%;
        background: #16a34a; color: #fff !important; font-size: 17px;
        text-decoration: none; transition: background .2s;
    }
    .sn-call:hover{ background: #15803d; }
    .sn-wa{ background: #25d366; }
    .sn-wa:hover{ background: #1da851; }
    .sn-tabs .btn{ font-size: 13px; }

    /* Mobile: each row becomes a card */
    @media (max-width: 767px){
        .sn-table thead{ display: none; }
        .sn-table, .sn-table tbody, .sn-table tr, .sn-table td{ display: block; width: 100%; }
        .sn-table tr{ border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px; padding: 8px; }
        .sn-table tbody td{ border: 0; padding: 4px 6px; }
        .sn-table td[data-label]:not([data-label="Product"])::before{
            content: attr(data-label) ": "; font-weight: 600; color: #64748b; font-size: 12px;
        }
        .sn-name{ max-width: none; }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row mt-3">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="page-title mb-0" style="font-size: 1.2rem;">
                            <i class="mdi mdi-bell-ring-outline me-1 text-danger"></i> Notify Me Requests
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0" style="font-size: 0.8rem;">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Notify Requests</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-2 p-md-3">

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div class="btn-group sn-tabs">
                                    <a href="{{ route('admin.stock_notifications.index', ['status' => 'pending', 'q' => $q]) }}"
                                       class="btn btn-sm {{ $status === 'pending' ? 'btn-danger' : 'btn-light border' }}">
                                        Pending ({{ $counts['pending'] ?? 0 }})
                                    </a>
                                    <a href="{{ route('admin.stock_notifications.index', ['status' => 'notified', 'q' => $q]) }}"
                                       class="btn btn-sm {{ $status === 'notified' ? 'btn-success' : 'btn-light border' }}">
                                        Notified ({{ $counts['notified'] ?? 0 }})
                                    </a>
                                    <a href="{{ route('admin.stock_notifications.index', ['status' => 'all', 'q' => $q]) }}"
                                       class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-light border' }}">
                                        All
                                    </a>
                                </div>

                                <form method="GET" class="d-flex gap-1">
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm"
                                           placeholder="Search phone / product / SKU" style="min-width: 220px;">
                                    <button class="btn btn-sm btn-primary"><i class="mdi mdi-magnify"></i></button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-centered sn-table mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Variant</th>
                                            <th>Phone</th>
                                            <th class="text-center">Call</th>
                                            <th>Requested</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($items as $item)
                                            @php $intlPhone = '88' . $item->phone; @endphp
                                            <tr>
                                                <td data-label="#">{{ $items->firstItem() + $loop->index }}</td>

                                                <td data-label="Product">
                                                    @if($item->product)
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="{{ getImage('thumb_products', $item->product->image) }}" class="sn-thumb" alt="img">
                                                            <div class="sn-name">
                                                                <a href="{{ route('front.products.show', ['product' => $item->product->slug ?: $item->product->id]) }}"
                                                                   target="_blank" class="fw-bold text-dark">{{ $item->product->name }}</a>
                                                                <small class="text-muted d-block">SKU: {{ $item->product->sku ?: '--' }}</small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-danger small">Product deleted</span>
                                                    @endif
                                                </td>

                                                <td data-label="Variant">{{ $item->variant_name ?: '—' }}</td>

                                                <td data-label="Phone"><span class="sn-phone">{{ $item->phone }}</span></td>

                                                <td data-label="Call" class="text-md-center">
                                                    <a href="tel:+{{ $intlPhone }}" class="sn-call" title="Call {{ $item->phone }}">
                                                        <i class="mdi mdi-phone"></i>
                                                    </a>
                                                    <a href="https://wa.me/{{ $intlPhone }}" target="_blank" rel="noopener" class="sn-call sn-wa ms-1" title="WhatsApp {{ $item->phone }}">
                                                        <i class="mdi mdi-whatsapp"></i>
                                                    </a>
                                                </td>

                                                <td data-label="Requested">
                                                    <span title="{{ $item->created_at }}">{{ $item->created_at->format('d M Y, h:i A') }}</span>
                                                    <small class="text-muted d-block">{{ $item->created_at->diffForHumans() }}</small>
                                                </td>

                                                <td data-label="Status">
                                                    @if($item->status === 'notified')
                                                        <span class="badge bg-success">Notified</span>
                                                        @if($item->notified_at)
                                                            <small class="text-muted d-block">{{ $item->notified_at->format('d M, h:i A') }}</small>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @endif
                                                </td>

                                                <td data-label="Action" class="text-md-end">
                                                    <div class="d-inline-flex gap-1">
                                                        @can('product.edit')
                                                            <form action="{{ route('admin.stock_notifications.toggle', $item->id) }}" method="POST">
                                                                @csrf
                                                                <button class="btn btn-sm {{ $item->status === 'notified' ? 'btn-light border' : 'btn-outline-success' }}"
                                                                        title="{{ $item->status === 'notified' ? 'Move back to pending' : 'Mark as notified' }}">
                                                                    <i class="mdi {{ $item->status === 'notified' ? 'mdi-undo' : 'mdi-check' }}"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                        @can('product.delete')
                                                            <form action="{{ route('admin.stock_notifications.destroy', $item->id) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this request?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="mdi mdi-delete"></i></button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">No requests found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $items->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('js')
<script>
  @if(session('success'))
    if (window.toastr) toastr.success(@json(session('success')));
  @endif
</script>
@endpush
