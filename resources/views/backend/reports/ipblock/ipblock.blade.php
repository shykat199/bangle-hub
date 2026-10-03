@extends('backend.app')
@section('content')

<style>
  :root{
    --bg:#f3f4f6;
    --card:#ffffff;
    --primary:#0ea5e9;
    --primary-soft:rgba(14,165,233,.12);
    --border:#e5e7eb;
    --text:#111827;
    --muted:#6b7280;
    --shadow-soft:0 10px 30px rgba(15,23,42,.08);
  }

  body{
    background:var(--bg);
  }

  .page-title-box{
    margin-bottom:.75rem;
  }
  .page-title-box h4.page-title{
    font-weight:700;
    letter-spacing:.3px;
  }
  .breadcrumb{
    background:transparent;
    padding:0;
    margin-bottom:0;
  }

  .card-modern{
    border:0;
    border-radius:18px;
    background:var(--card);
    box-shadow:var(--shadow-soft);
    overflow:hidden;
  }
  .card-modern .card-header{
    border-bottom:1px solid rgba(15,23,42,.06);
    background:linear-gradient(135deg,#eff6ff,#ecfeff);
    padding:.85rem 1.25rem;
  }
  .card-modern .card-header h4{
    margin:0;
    font-size:1rem;
    font-weight:700;
    color:#0f172a;
  }
  .card-modern .card-body{
    padding:1rem 1.25rem 1.15rem;
  }

  .form-label{
    font-size:.85rem;
    font-weight:600;
    color:var(--muted);
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:.25rem;
  }
  .form-control{
    border-radius:.75rem;
    border-color:var(--border);
    font-size:.9rem;
  }
  .form-control:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 .15rem var(--primary-soft);
  }

  .btn-primary{
    border-radius:.75rem;
    padding:.45rem 1.2rem;
    font-weight:600;
    font-size:.9rem;
    border:none;
    background:linear-gradient(135deg,#0ea5e9,#2563eb);
  }
  .btn-primary:hover,
  .btn-primary:focus{
    box-shadow:0 10px 20px rgba(37,99,235,.25);
  }

  .btn-danger{
    border-radius:.75rem;
  }

  .table-modern{
    margin-bottom:0;
  }
  .table-modern thead{
    background:#f9fafb;
  }
  .table-modern thead th{
    border-bottom:1px solid var(--border) !important;
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    font-weight:600;
    color:var(--muted) !important;
  }
  .table-modern tbody tr{
    vertical-align:middle;
  }
  .table-modern tbody td{
    font-size:.9rem;
    border-top:1px solid #f1f5f9;
  }

  .badge-sl{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:32px;
    padding:.12rem .6rem;
    border-radius:999px;
    background:var(--primary-soft);
    color:#0369a1;
    font-weight:600;
    font-size:.8rem;
  }

  .table-container{
    border-radius:16px;
    border:1px solid rgba(148,163,184,.25);
    overflow:hidden;
    background:#f9fafb;
  }

  /* Mobile: table → card */
  @media (max-width: 767.98px){
    .table-responsive{
      border:0;
    }
    .table-modern thead{
      display:none;
    }
    .table-modern tbody tr{
      display:block;
      margin-bottom:.85rem;
      border-radius:14px;
      border:1px solid #e5e7eb;
      background:#ffffff;
      box-shadow:0 4px 12px rgba(15,23,42,.05);
      padding:.55rem .75rem;
    }
    .table-modern tbody td{
      display:grid;
      grid-template-columns: 120px 1fr;
      gap:4px;
      border-top:0 !important;
      padding:.25rem 0 !important;
    }
    .table-modern tbody td::before{
      content: attr(data-label);
      font-size:.78rem;
      text-transform:uppercase;
      letter-spacing:.05em;
      color:var(--muted);
      font-weight:600;
    }
    .table-modern tbody td:last-child{
      margin-top:.15rem;
    }
    .table-modern tbody td:last-child::before{
      content:"Action";
    }
  }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="page-title mb-1">IP Block Manage</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">IP Block</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Left: Create / Block form --}}
    <div class="col-lg-5 col-md-12 mb-3">
        <div class="card card-modern">
            <div class="card-header">
                <h4>Block New IP</h4>
            </div>
            <div class="card-body">
                <form id="order_report_form" method="POST" action="{{ route('admin.ipblock.submit') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="Ipaddress" class="form-label">IP Address</label>
                        <input type="text"
                               class="form-control"
                               id="Ipaddress"
                               name="ip_address"
                               placeholder="Which IP you want to block">
                    </div>

                    <div class="form-group mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea name="reason"
                                  id="reason"
                                  class="form-control"
                                  cols="30"
                                  rows="4"
                                  placeholder="Why do you want to block this IP?"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: List --}}
    <div class="col-lg-7 col-md-12 mb-3">
        <div class="card card-modern">
            <div class="card-header">
                <h4>Blocked IP List</h4>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-striped table-modern">
                            <thead>
                              <tr>
                                <th>SL</th>
                                <th>IP Address</th>
                                <th>Reason</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                              @php $serialNumber = 1; @endphp   
                              @foreach($AllIp as $ips)
                              <tr data-id="{{ $ips->id }}">
                                <td data-label="SL">
                                    <span class="badge-sl">{{ $serialNumber++ }}</span>
                                </td>
                                <td data-label="IP Address" class="ip-address">
                                    {{ $ips->ip_address }}
                                </td>
                                <td data-label="Reason" class="reason">
                                    {{ $ips->reason }}
                                </td>
                                <td data-label="Action">
                                    <a href="{{route('admin.ipblock.delete', ['id' => $ips->id])}}"
                                       class="btn btn-danger btn-sm">
                                        Delete
                                    </a>
                                    <a href="#"
                                       data-toggle="modal"
                                       data-target="#editIpBlockModal{{ $ips->id }}"
                                       class="btn btn-primary btn-sm">
                                        Edit
                                    </a>
                                </td>
                              </tr>
                              @endforeach    
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals (alাদা করে, টেবিলের বাইরে রাখা হলো) --}}
@foreach($AllIp as $ips)
<div class="modal fade" id="editIpBlockModal{{ $ips->id }}" tabindex="-1" role="dialog" aria-labelledby="editIpBlockModalLabel{{ $ips->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIpBlockModalLabel{{ $ips->id }}">Edit IP Block</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.ipblock.update', ['id' => $ips->id]) }}" method="POST" class="edit-form">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-3">
                        <label for="editIp{{ $ips->id }}" class="form-label">IP Address</label>
                        <input type="text"
                               class="form-control"
                               id="editIp{{ $ips->id }}"
                               name="ip_address"
                               value="{{ $ips->ip_address }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="editReason{{ $ips->id }}" class="form-label">Reason</label>
                        <textarea class="form-control"
                                  id="editReason{{ $ips->id }}"
                                  name="reason"
                                  rows="3">{{ $ips->reason }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include Bootstrap JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('.edit-form').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();
            
            $.ajax({
                type: 'PUT',
                url: url,
                data: data,
                success: function(response) {
                    if (response.success) {
                        // টেবিলের row আপডেট করার জন্য ip-address / reason ক্লাস রাখা হয়েছে
                        var row = form.closest('tr');
                        row.find('.ip-address').text(response.ip_address);
                        row.find('.reason').text(response.reason);
                        
                        // Close the modal
                        form.closest('.modal').modal('hide');
                    } else {
                        // যদি success flag না থাকে, চাইলে আলাদা হ্যান্ডেল করতে পারো
                        form.closest('.modal').modal('hide');
                        location.reload();
                    }
                },
                error: function() {
                    alert('Error updating IP block.');
                }
            });
        });
    });
</script>
@endpush
