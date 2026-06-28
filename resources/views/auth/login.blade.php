<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — MedArchive</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
  <script src="{{ asset('lottie.min.js') }}"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #c9d8ef;
      padding: 20px;
    }

    .shell {
      display: flex;
      width: 900px;
      min-height: 540px;
      border-radius: 28px;
      overflow: hidden;
      box-shadow:
        0 40px 100px rgba(10,30,80,0.25),
        0 10px 30px rgba(10,30,80,0.15);
      animation: fadeUp 0.7s cubic-bezier(0.34,1.56,0.64,1) both;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(40px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ── LEFT PANEL ── */
    .left {
      width: 46%;
      background: #2946d2;
      position: relative;
      display: flex;
      flex-direction: column;
      padding: 36px 40px 40px;
      overflow: hidden;
    }
    .left .circle-1 {
      position: absolute;
      width: 380px; height: 380px;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
      top: -120px; right: -100px;
      pointer-events: none;
    }
    .left .circle-2 {
      position: absolute;
      width: 260px; height: 260px;
      border-radius: 50%;
      background: rgba(255,255,255,0.05);
      bottom: -80px; left: -60px;
      pointer-events: none;
    }
    .spotlight {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: conic-gradient(
        from 30deg at 70% 30%,
        transparent 0deg,
        rgba(255,255,255,0.07) 15deg,
        transparent 30deg
      );
      pointer-events: none;
    }

    .brand {
      position: relative;
      z-index: 3;
      display: flex;
      align-items: center;
      gap: 11px;
    }
    .brand-icon {
      width: 38px; height: 38px;
      background: rgba(255,255,255,0.18);
      border: 1.5px solid rgba(255,255,255,0.3);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 17px;
    }
    .brand-name { font-size: 15px; font-weight: 800; color: #fff; letter-spacing: -0.2px; }
    .brand-sub  { font-size: 10.5px; color: rgba(255,255,255,0.45); margin-top: 1px; font-weight: 400; }

    .lottie-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 3;
    }
    #lottie-anim {
      width: 280px; height: 280px;
      filter: drop-shadow(0 20px 40px rgba(0,0,0,0.25));
    }

    .left-copy { position: relative; z-index: 3; }
    .left-copy h2 {
      font-size: 22px; font-weight: 800; color: #fff;
      letter-spacing: -0.5px; line-height: 1.3; margin-bottom: 8px;
    }
    .left-copy p {
      font-size: 13px; color: rgba(255,255,255,0.5);
      line-height: 1.65; max-width: 260px;
    }
    .dots { display: flex; gap: 6px; margin-top: 18px; }
    .dot  { height: 6px; border-radius: 3px; background: rgba(255,255,255,0.25); }
    .dot.active  { width: 22px; background: #fff; }
    .dot:not(.active) { width: 6px; }

    /* ── RIGHT PANEL ── */
    .right {
      flex: 1;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px 44px;
    }
    .form-box { width: 100%; max-width: 340px; }

    .form-header { text-align: center; margin-bottom: 34px; }
    .form-header h1 {
      font-size: 28px; font-weight: 800; color: #1e2d5e;
      letter-spacing: -0.6px; margin-bottom: 6px;
    }
    .form-header p { font-size: 13px; color: #94a3b8; font-weight: 400; }
    .form-header p a { color: #2946d2; font-weight: 600; text-decoration: none; }
    .form-header p a:hover { text-decoration: underline; }

    /* Laravel error alert */
    .alert {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #fff5f5;
      border: 1px solid #fed7d7;
      border-radius: 10px;
      padding: 10px 14px;
      color: #c53030;
      font-size: 12.5px;
      margin-bottom: 18px;
      animation: alertIn 0.25s ease;
    }
    @keyframes alertIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

    /* Rate limit / lockout warning */
    .alert-warning {
      background: #fffbeb;
      border-color: #fcd34d;
      color: #92400e;
    }

    .field { margin-bottom: 16px; }
    .field-inner {
      position: relative;
      display: flex;
      align-items: center;
      background: #f1f5fb;
      border: 1.5px solid #e2e8f5;
      border-radius: 12px;
      transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .field-inner:focus-within {
      border-color: #2946d2;
      background: #fff;
      box-shadow: 0 0 0 3.5px rgba(41,70,210,0.1);
    }
    .field-inner.has-error { border-color: #fc8181; }
    .field-icon-left {
      width: 44px;
      display: flex; align-items: center; justify-content: center;
      color: #94a3b8; font-size: 15px; flex-shrink: 0;
    }
    .field-inner input {
      flex: 1;
      padding: 13px 0;
      background: transparent;
      border: none; outline: none;
      font-size: 13.5px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: #1e2d5e; font-weight: 500;
    }
    .field-inner input::placeholder {
      color: #a0aec0; font-weight: 400; font-size: 12.5px;
      letter-spacing: 0.8px; text-transform: uppercase;
    }
    .field-inner input:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 1000px #f1f5fb inset;
      -webkit-text-fill-color: #1e2d5e;
    }
    .toggle-pw {
      width: 40px;
      display: flex; align-items: center; justify-content: center;
      color: #94a3b8; font-size: 15px; cursor: pointer;
      user-select: none; transition: color 0.2s;
    }
    .toggle-pw:hover { color: #2946d2; }

    .row-between {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 24px; margin-top: 2px;
    }
    .remember {
      display: flex; align-items: center; gap: 7px;
      font-size: 12.5px; color: #64748b; cursor: pointer; user-select: none;
    }
    .remember input[type=checkbox] {
      width: 14px; height: 14px; accent-color: #2946d2; cursor: pointer;
    }
    .forgot {
      font-size: 12.5px; color: #2946d2; text-decoration: none;
      font-weight: 600; transition: color 0.2s;
    }
    .forgot:hover { color: #1a34a8; text-decoration: underline; }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: #2946d2;
      color: #fff; border: none;
      border-radius: 12px;
      font-size: 14px; font-weight: 700;
      font-family: 'Plus Jakarta Sans', sans-serif;
      letter-spacing: 2px; text-transform: uppercase;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
      box-shadow: 0 6px 20px rgba(41,70,210,0.38);
      position: relative; overflow: hidden;
    }
    .btn-login:hover {
      background: #1a34a8;
      transform: translateY(-2px);
      box-shadow: 0 10px 28px rgba(41,70,210,0.45);
    }
    .btn-login:active { transform: scale(0.98); }

    .sec-footer {
      display: flex; align-items: center; justify-content: center;
      gap: 6px; margin-top: 22px;
      font-size: 11px; color: #b0bec5;
    }
    .pulse-dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: #0d9f6e;
      animation: pulse 2s ease-in-out infinite;
      flex-shrink: 0;
    }
    @keyframes pulse {
      0%,100% { opacity: 1; transform: scale(1); }
      50%      { opacity: 0.4; transform: scale(0.8); }
    }

    @media (max-width: 700px) {
      .left { display: none; }
      .right { padding: 36px 28px; }
    }
  </style>
</head>
<body>

<div class="shell">

  <!-- LEFT PANEL -->
  <div class="left">
    <div class="circle-1"></div>
    <div class="circle-2"></div>
    <div class="spotlight"></div>

    <div class="brand">
      <div class="brand-icon"><i class="fa-solid fa-heart-pulse" style="color: #fff;"></i></div>
      <div>
        <div class="brand-name">MedArchive</div>
        <div class="brand-sub">Medical Records System</div>
      </div>
    </div>

    <div class="lottie-wrap">
      <div id="lottie-anim"></div>
    </div>

    <div class="left-copy">
      <h2>Your health records,<br>always within reach.</h2>
      <p>Securely access patient data, records, and diagnostics from anywhere, any time.</p>
      <div class="dots">
        <div class="dot active"></div>
        <div class="dot"></div>
        <div class="dot"></div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right">
    <div class="form-box">

      <div class="form-header">
        <h1>Welcome!</h1>
        <p>New here? <a href="#">Create an account</a></p>
      </div>

      {{-- Laravel validation errors --}}
      @if ($errors->any())
        <div class="alert {{ $errors->has('throttle') ? 'alert-warning' : '' }}">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
      @endif

      {{-- Session status (e.g. after password reset) --}}
      @if (session('status'))
        <div class="alert" style="background:#f0fdf4;border-color:#86efac;color:#166534;">
          <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('status') }}</span>
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="field">
          <div class="field-inner {{ $errors->has('email') ? 'has-error' : '' }}">
            <div class="field-icon-left"><i class="fa-regular fa-envelope"></i></div>
            <input
              type="email"
              name="email"
              id="emailInput"
              placeholder="Your E-Mail"
              autocomplete="email"
              value="{{ old('email') }}"
              required
            >
          </div>
        </div>

        <div class="field">
          <div class="field-inner {{ $errors->has('password') ? 'has-error' : '' }}">
            <div class="field-icon-left"><i class="fa-solid fa-lock"></i></div>
            <input
              type="password"
              name="password"
              id="pwInput"
              placeholder="Your Password"
              autocomplete="current-password"
              required
            >
            <div class="toggle-pw" id="togglePw" title="Show / hide">
            <i class="fa-regular fa-eye" id="togglePwIcon"></i>
            </div>
          </div>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            Remember my password
          </label>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="forgot">Forgot your password?</a>
          @endif
        </div>

        <button type="submit" class="btn-login">Login</button>

      </form>

      <div class="sec-footer">
        <span class="pulse-dot"></span>
        256-bit encrypted &nbsp;·&nbsp; HIPAA compliant &nbsp;·&nbsp; Session protected
      </div>

    </div>
  </div>
</div>

<script>
  /* Lottie */
  lottie.loadAnimation({
    container: document.getElementById('lottie-anim'),
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'https://assets7.lottiefiles.com/packages/lf20_7fCbvNSmFD.json'
  });

  /* Password toggle */
  const pwIn        = document.getElementById('pwInput');
  const togglePw    = document.getElementById('togglePw');
  const togglePwIcon = document.getElementById('togglePwIcon');
  let pwVis = false;

  togglePw.addEventListener('click', () => {
    pwVis = !pwVis;
    pwIn.type = pwVis ? 'text' : 'password';
    togglePwIcon.className = pwVis ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
  });
</script>

</body>
</html>
