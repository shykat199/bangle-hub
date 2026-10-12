@extends('backend.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Home Bottom Strip</h4>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.home_strip.update') }}">
                @csrf

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="strip_active" name="active" value="1"
                           {{ old('_token') ? (old('active') ? 'checked' : '') : ($strip['active'] ? 'checked' : '') }}>
                    <label class="form-check-label" for="strip_active">Show this section on the homepage</label>
                </div>

                <h5 class="mb-3 border-bottom pb-2">Shop Info</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Small Label</label>
                        <input type="text" class="form-control" name="shop_label" maxlength="80"
                               value="{{ old('shop_label', $strip['shop_label']) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Shop Name</label>
                        <input type="text" class="form-control" name="shop_name" maxlength="120"
                               value="{{ old('shop_name', $strip['shop_name']) }}"
                               placeholder="{{ $info->site_name ?? '' }}">
                        <small class="text-muted">Leave blank to use the site name.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Address</label>
                        <input type="text" class="form-control" name="shop_address" maxlength="255"
                               value="{{ old('shop_address', $strip['shop_address']) }}"
                               placeholder="{{ $info->address ?? '' }}">
                        <small class="text-muted">Leave blank to use the site address.</small>
                    </div>
                </div>

                <h5 class="mb-3 mt-4 border-bottom pb-2">Feature Items</h5>
                <p class="text-muted">Leave a title blank to hide that item.</p>
                @for($i = 0; $i < 3; $i++)
                    @php $item = $strip['items'][$i] ?? ['icon' => 'fa-check-circle', 'title' => '', 'text' => '']; @endphp
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Item {{ $i + 1 }} Icon</label>
                            <select class="form-control" name="items[{{ $i }}][icon]">
                                @foreach($icons as $class => $label)
                                    <option value="{{ $class }}" {{ old("items.$i.icon", $item['icon']) === $class ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Item {{ $i + 1 }} Title</label>
                            <input type="text" class="form-control" name="items[{{ $i }}][title]" maxlength="60"
                                   value="{{ old("items.$i.title", $item['title']) }}">
                        </div>

                        <div class="col-md-5 mb-3">
                            <label>Item {{ $i + 1 }} Subtitle</label>
                            <input type="text" class="form-control" name="items[{{ $i }}][text]" maxlength="120"
                                   value="{{ old("items.$i.text", $item['text']) }}">
                        </div>
                    </div>
                @endfor

                <h5 class="mb-3 mt-4 border-bottom pb-2">Contact Button</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Button Label</label>
                        <input type="text" class="form-control" name="contact_label" maxlength="40"
                               value="{{ old('contact_label', $strip['contact_label']) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone Number</label>
                        <input type="text" class="form-control" name="contact_phone" maxlength="30"
                               value="{{ old('contact_phone', $strip['contact_phone']) }}"
                               placeholder="{{ $info->owner_phone ?? '' }}">
                        <small class="text-muted">Leave blank to use the site phone number.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-5">Save Changes</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
