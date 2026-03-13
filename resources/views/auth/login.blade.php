<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MANAGE DB | AQT NETWORK</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Orbitron:wght@500&display=swap">
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

  <style>
    body {
      height: 100vh;
      margin: 0;
      /* Background Dark Gradient */
      background: radial-gradient(circle at center, #1e1e2d 0%, #08080a 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
      overflow: hidden;
      color: #ffffff;
    }

    /* Dekorasi Glow di Background */
    body::before {
      content: "";
      position: absolute;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(201, 162, 39, 0.15), transparent 70%);
      top: -100px;
      right: -100px;
      z-index: -1;
    }

    .login-box {
      width: 420px;
    }

    /* Card Glassmorphism Dark */
    .card-premium {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 24px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 30px 20px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
      transition: all 0.4s ease;
    }

    .card-premium:hover {
      border-color: rgba(201, 162, 39, 0.4);
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.7);
    }

    .logo-circle {
      width: 85px;
      height: 85px;
      border-radius: 22px;
      object-fit: cover;
      margin-bottom: 20px;
      border: 2px solid rgba(201, 162, 39, 0.5);
      padding: 5px;
      background: rgba(0,0,0,0.2);
    }

    .brand {
      font-family: 'Orbitron', sans-serif;
      font-size: 24px;
      font-weight: 700;
      letter-spacing: 2px;
      color: #ffffff;
      margin-bottom: 5px;
    }

    .brand span {
      background: linear-gradient(135deg, #c9a227, #f1c40f);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .subtitle {
      font-size: 11px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.4);
      letter-spacing: 4px;
      text-transform: uppercase;
      margin-bottom: 40px;
    }

    /* Input Styling Dark */
    .input-group-custom {
      background: rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 14px;
      transition: 0.3s;
      display: flex;
      align-items: center;
      padding: 5px 18px;
      margin-bottom: 20px;
    }

    .input-group-custom:focus-within {
      border-color: #c9a227;
      background: rgba(201, 162, 39, 0.05);
      box-shadow: 0 0 15px rgba(201, 162, 39, 0.2);
    }

    .input-group-custom input {
      border: none;
      background: transparent;
      box-shadow: none;
      padding: 14px 12px;
      font-size: 14px;
      width: 100%;
      outline: none;
      color: #ffffff;
    }

    .input-group-custom i {
      color: rgba(255, 255, 255, 0.3);
      transition: 0.3s;
      font-size: 16px;
    }

    .input-group-custom:focus-within i {
      color: #c9a227;
    }

    /* Button Styling */
    .btn-premium {
      background: linear-gradient(135deg, #c9a227, #8e6d13);
      color: #000;
      border: none;
      border-radius: 14px;
      padding: 14px;
      font-weight: 700;
      width: 100%;
      transition: 0.3s;
      box-shadow: 0 10px 20px rgba(201, 162, 39, 0.2);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .btn-premium:hover {
      transform: scale(1.03);
      box-shadow: 0 15px 25px rgba(201, 162, 39, 0.4);
      background: linear-gradient(135deg, #f1c40f, #c9a227);
      color: #000;
    }

    .icheck-primary label {
      font-size: 13px !important;
      color: rgba(255, 255, 255, 0.6) !important;
      font-weight: 400 !important;
    }

    /* Menyesuaikan checkbox agar lebih senada dengan emas */
    .icheck-primary > input:first-child:checked + label::before {
        background-color: #c9a227 !important;
        border-color: #c9a227 !important;
    }

    .error-text {
      font-size: 11px;
      color: #ff4d4d;
      text-align: left;
      margin-top: -15px;
      margin-bottom: 15px;
      padding-left: 10px;
    }

    /* --- CSS TAMBAHAN UNTUK ANIMASI AUTHENTICATING --- */
    #auth-loader {
      display: none; /* Sembunyi secara default */
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(8, 8, 10, 0.8);
      backdrop-filter: blur(10px);
      z-index: 9999;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .spinner-gold {
      width: 50px;
      height: 50px;
      border: 3px solid rgba(201, 162, 39, 0.2);
      border-top: 3px solid #c9a227;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    .auth-text {
      margin-top: 15px;
      font-family: 'Orbitron', sans-serif;
      font-size: 12px;
      color: #c9a227;
      letter-spacing: 4px;
      text-transform: uppercase;
      animation: pulse 1.5s infinite;
    }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
  </style>
</head>

<body class="hold-transition login-page">

  <div id="auth-loader">
    <div class="spinner-gold"></div>
    <div class="auth-text">Authenticating...</div>
  </div>

  <div class="login-box">
    <div class="card card-premium">
      <div class="card-body text-center">

        <img src="https://us.123rf.com/450wm/mopc95/mopc951609/mopc95160900019/65023633-abstract-red-letter-m-logo-design-template-icon-shape-element-you-can-use-logotype-in-energy.jpg"
          class="logo-circle">

        <div class="brand">MANAGE <span>DB</span></div>
        <div class="subtitle">AQT NETWORK</div>

        <form id="loginForm" method="POST" action="{{ route('login') }}">
          @csrf

          <div class="input-group-custom">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required autocomplete="off">
          </div>
          @error('email')
            <div class="error-text">{{ $message }}</div>
          @enderror

          <div class="input-group-custom">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
          </div>
          @error('password')
            <div class="error-text">{{ $message }}</div>
          @enderror

          <div class="row mt-4 align-items-center">
            <div class="col-7 text-left">
              <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Keep me signed in</label>
              </div>
            </div>

            <div class="col-5">
              <button type="submit" class="btn btn-premium">
                Login <i class="fas fa-sign-in-alt ml-2"></i>
              </button>
            </div>
          </div>

        </form>

      </div>
    </div>
    
    <div class="text-center mt-4">
        <p style="color: rgba(255,255,255,0.3); font-size: 12px;">&copy; 2024 AQT NETWORK. All Rights Reserved.</p>
    </div>
  </div>

  <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

  <script>
    $(document).ready(function() {
      $('#loginForm').on('submit', function() {
        // Tampilkan overlay loading
        $('#auth-loader').css('display', 'flex');
        // Disable tombol login agar tidak ditekan berulang kali
        $('.btn-premium').prop('disabled', true);
      });
    });
  </script>

</body>
</html>