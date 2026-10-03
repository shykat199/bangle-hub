@extends('backend.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Worker Activity Logs</h4>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.activity.logs') }}" method="GET" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="user_id" class="form-label">Filter by Worker</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">All Workers (সবার কাজ একসাথে)</option>
                                    @foreach($workers as $worker)
                                        <option value="{{ $worker->id }}" {{ request('user_id') == $worker->id ? 'selected' : '' }}>
                                            {{ $worker->name }} ({{ $worker->mobile }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.activity.logs') }}" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date & Time</th>
                                    <th>Worker Name</th>
                                    <th>Action</th>
                                    <th>Note (Details)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $key => $log)
                                    <tr>
                                        <td>{{ $logs->firstItem() + $key }}</td>
                                        <td>
                                            <span class="text-primary fw-bold">{{ $log->created_at->format('d M, Y') }}</span><br>
                                            <span class="text-muted">{{ $log->created_at->format('h:i A') }}</span>
                                        </td>
                                        <td>
                                            @if($log->user)
                                                <span class="badge bg-success">{{ $log->user->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">System / Unknown</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info text-dark">{{ $log->action }}</span></td>
                                        <td>{{ $log->note }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No activity logs found!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection