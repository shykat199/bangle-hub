<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Configuration Cache</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .config-container {
            max-width: 600px;
            margin: 5% auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        .config-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .config-header h2 {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.8rem;
        }
        label {
            font-weight: 600;
            color: #34495e;
        }
        textarea.form-control {
            resize: none;
            border-radius: 12px;
            padding: 10px 15px;
        }
        button.btn-primary {
            background: #007bff;
            border: none;
            border-radius: 10px;
            padding: 10px 30px;
            transition: all 0.2s ease-in-out;
        }
        button.btn-primary:hover {
            background: #0056b3;
        }
        .alert {
            border-radius: 10px;
        }
        footer {
            text-align: center;
            font-size: 0.85rem;
            color: #888;
            margin-top: 25px;
        }
    </style>
</head>
<body>

    <div class="config-container">
        <div class="config-header">
            <h2>System Configuration Cache</h2>
            <p class="text-muted">Manage authorized domains securely</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
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

        <form action="{{ route('system.cache.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ env('LICENSE_ADMIN_TOKEN') }}">
        
            <div class="mb-3">
                <label for="domains" class="form-label">Add Licence Code here</label>
                <textarea 
                    name="domains" 
                    id="domains" 
                    class="form-control" 
                    rows="4" 
                    placeholder="#21sdf512sd2c45df">{{ old('domains', $domains ?? '') }}</textarea>
            </div>
        
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Update Configuration</button>
            </div>
        </form>
    </div>

    <footer>
        <p>© {{ date('Y') }} System Security Panel</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
