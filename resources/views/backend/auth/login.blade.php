<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php $info = \App\Models\Information::first(); @endphp

  <link rel="shortcut icon" href="{{ asset('uploads/img/'.($info->site_logo ?? 'logo.png')) }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>

  <style>
    :root{
      --bg1:#eaf3ff;
      --bg2:#f6f3ff;
      --ink:#0f172a;
      --muted:#64748b;
      --stroke: rgba(255,255,255,.55);
      --card: rgba(255,255,255,.78);
      --shadow: 0 28px 80px rgba(2, 6, 23, .16);
      --shadow2: 0 12px 28px rgba(2, 6, 23, .10);

      --b1:#2563eb;
      --b2:#0ea5a4;
      --b3:#7c3aed;

      --r: 20px;
      --inputH: 52px;
    }

    body{
      min-height: 100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 24px;
      background:
        radial-gradient(900px 600px at 15% 10%, rgba(37,99,235,.22), transparent 55%),
        radial-gradient(900px 600px at 85% 20%, rgba(14,165,164,.22), transparent 55%),
        radial-gradient(900px 600px at 55% 90%, rgba(124,58,237,.18), transparent 60%),
        linear-gradient(135deg, var(--bg1), var(--bg2));
      color: var(--ink);
    }

    .orb{
      position: fixed;
      width: 340px;
      height: 340px;
      border-radius: 999px;
      filter: blur(45px);
      opacity: .42;
      z-index: 0;
      pointer-events: none;
      animation: floaty 10s ease-in-out infinite;
    }
    .orb.o1{ left:-120px; top:-100px; background: rgba(37,99,235,.55); }
    .orb.o2{ right:-140px; top: 0px; background: rgba(14,165,164,.50); animation-delay:-2.5s; }
    .orb.o3{ left: 28%; bottom:-160px; background: rgba(124,58,237,.45); animation-delay:-5s; }

    @keyframes floaty{
      0%,100%{ transform: translate3d(0,0,0) scale(1); }
      50%{ transform: translate3d(0,-18px,0) scale(1.06); }
    }

    .wrap{ width: 100%; max-width: 460px; position: relative; z-index: 2; }

    .cardx{
      background: var(--card);
      border: 1px solid var(--stroke);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-radius: calc(var(--r) + 8px);
      box-shadow: var(--shadow);
      padding: 34px 28px 26px;
      position: relative;
      overflow: hidden;
    }

    .cardx:before{
      content:"";
      position:absolute;
      left:-35%;
      top:-45%;
      width: 170%;
      height: 240px;
      background: linear-gradient(90deg, rgba(37,99,235,.45), rgba(14,165,164,.45), rgba(124,58,237,.35));
      filter: blur(18px);
      opacity: .32;
      transform: rotate(8deg);
      pointer-events:none;
    }

    .brand{
      position: relative;
      z-index: 2;
      text-align: center;
      margin-bottom: 16px;
    }

    /* ✅ LOGO DIRECT (no circle, no crop) */
    .logo-box{
      width: 100%;
      display:flex;
      justify-content:center;
      margin-bottom: 12px;
    }
    .logo-box img{
      max-width: 180px;         /* premium size */
      max-height: 80px;         /* keep height controlled */
      width: auto;
      height: auto;
      object-fit: contain;      /* no cropping */
      filter: drop-shadow(0 10px 18px rgba(2,6,23,.18));
    }

    .title{
      margin: 0;
      font-size: 1.14rem;
      font-weight: 900;
      letter-spacing: .2px;
      background: linear-gradient(90deg, var(--b1), var(--b2), var(--b3));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      line-height: 1.2;
    }
    .subtitle{
      margin-top: 6px;
      font-size: .92rem;
      color: var(--muted);
      font-weight: 600;
    }

    .divider{
      height: 1px;
      width: 100%;
      background: rgba(2,6,23,.08);
      margin: 16px 0 18px;
      position: relative;
      z-index: 2;
    }

    .form-label{
      font-size: .9rem;
      font-weight: 800;
      color: rgba(15,23,42,.92);
    }

    .field{ position: relative; z-index: 2; }

    /* ✅ Icon perfectly centered */
    .field .ico{
      position: absolute;
      left: 14px;
      top: 36px;               /* label height compensation */
      height: var(--inputH);
      width: 34px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(100,116,139,.95);
      pointer-events: none;
      font-size: 14px;
    }

    .form-control{
      height: var(--inputH);
      border-radius: 16px !important;
      padding-left: 50px;
      padding-right: 46px;
      border: 1px solid rgba(2,6,23,.12) !important;
      background: rgba(255,255,255,.86);
      box-shadow: 0 10px 20px rgba(2,6,23,.06);
      transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .form-control:focus{
      border-color: rgba(37,99,235,.55) !important;
      box-shadow: 0 16px 34px rgba(37,99,235,.14);
      transform: translateY(-1px);
    }

    .toggle{
      position:absolute;
      right: 10px;
      top: 36px;
      height: var(--inputH);
      width: 42px;
      display:flex;
      align-items:center;
      justify-content:center;
      border: 0;
      background: transparent;
      color: rgba(100,116,139,.95);
      border-radius: 14px;
      transition: background .15s ease, transform .15s ease;
    }
    .toggle:hover{
      background: rgba(2,6,23,.06);
      transform: scale(1.03);
    }

    .meta{
      position: relative;
      z-index: 2;
      display:flex;
      align-items:center;
      justify-content:flex-start;
      margin-top: 6px;
      margin-bottom: 14px;
    }
    .form-check-label{
      color: rgba(15,23,42,.85);
      font-weight: 700;
      font-size: .92rem;
    }

    .btnx{
      position: relative;
      z-index: 2;
      border: none;
      width: 100%;
      height: 52px;
      border-radius: 16px;
      font-weight: 900;
      letter-spacing: .2px;
      color: #fff;
      background: linear-gradient(90deg, var(--b1), var(--b2));
      box-shadow: 0 20px 46px rgba(37,99,235,.24);
      transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    }
    .btnx:hover{
      filter: brightness(1.02);
      transform: translateY(-1px);
      box-shadow: 0 26px 58px rgba(37,99,235,.28);
    }
    .btnx:active{
      transform: translateY(0) scale(.99);
      box-shadow: 0 16px 36px rgba(37,99,235,.22);
    }

    .note{
      position: relative;
      z-index: 2;
      text-align:center;
      margin-top: 14px;
      font-size: .85rem;
      color: rgba(100,116,139,.95);
      font-weight: 600;
    }

    .alert{
      border-radius: 16px;
      border: 1px solid rgba(239,68,68,.15);
      background: rgba(239,68,68,.08);
      color: rgba(153,27,27,.95);
    }

    @media (max-width: 380px){
      .cardx{ padding: 28px 18px 20px; }
      .logo-box img{ max-width: 160px; max-height: 72px; }
    }
  </style>
</head>

<body>
  <div class="orb o1"></div>
  <div class="orb o2"></div>
  <div class="orb o3"></div>

  <div class="wrap">
    <div class="cardx">

      <div class="brand">
        <div class="logo-box">
          <img src="{{ asset('uploads/img/'.($info->site_logo ?? 'logo.png')) }}" alt="Logo">
        </div>

        <h1 class="title">{{ $info->site_name ?? config('app.name') }}</h1>
        <div class="subtitle">Secure Admin Access</div>
      </div>

      <div class="divider"></div>

      <form method="POST" action="{{ route('admin.postLogin') }}">
        @csrf

        @if(session()->has('success'))
          <div class="alert alert-danger mb-3">{{ session('success') }}</div>
        @endif

        <!-- ✅ Username only -->
        <div class="mb-3 text-start field">
          <label for="username" class="form-label">Username</label>
          <span class="ico"><i class="fas fa-user"></i></span>

          <input type="text" id="username" name="username"
                 class="form-control @error('username') is-invalid @enderror"
                 placeholder="Enter your username"
                 value="{{ isset($_COOKIE['user']) ? $_COOKIE['user'] : old('username') }}">

          @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Password -->
        <div class="mb-2 text-start field">
          <label for="password" class="form-label">Password</label>
          <span class="ico"><i class="fas fa-lock"></i></span>

          <input type="password" id="password" name="password"
                 class="form-control @error('password') is-invalid @enderror"
                 placeholder="Enter your password"
                 value="{{ isset($_COOKIE['pass']) ? $_COOKIE['pass'] : '' }}">

          <button type="button" class="toggle" id="togglePass" aria-label="Show/Hide password">
            <i class="far fa-eye"></i>
          </button>

          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="meta">
          <div class="form-check m-0">
            <input type="checkbox" class="form-check-input" id="remember"
                   name="remember"
                   @if(isset($_COOKIE['user']) && isset($_COOKIE['pass'])) checked @endif>
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
        </div>

        <button type="submit" class="btnx">
          <i class="fas fa-shield-alt me-2"></i> Login
        </button>

        <div class="note">
          © {{ date('Y') }} {{ $info->site_name ?? config('app.name') }}
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ✅ Password show/hide
    (function(){
      const btn = document.getElementById('togglePass');
      const input = document.getElementById('password');
      if(!btn || !input) return;

      btn.addEventListener('click', function(){
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        btn.innerHTML = isPass ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
      });
    })();
  </script>
</body>
</html>
