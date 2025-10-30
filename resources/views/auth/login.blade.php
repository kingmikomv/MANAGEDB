<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MANAGE DB | AQT NETWORK</title>

  <!-- Google Font -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

  <style>
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
    }

    body {
      background-color: #111;
      background-size: cover;
      background-position: center;
      width: 100%;
      height: 100dvh; /* dynamic viewport height */
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background-image 1s ease-in-out;
      color: white;
      overflow-x: hidden;
      overflow-y: auto;
    }

    /* blur hanya untuk card saat loading */
    body.loading .login-box .card {
      filter: blur(10px);
      transition: filter 0.8s ease;
    }

    /* Glassmorphism card */
    .glass-card {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    }

    .login-box .card-header img {
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    /* responsive for smaller screens */
    @media (max-height: 700px) {
      body {
        height: 100vh; /* fallback untuk HP lama */
      }
    }
  </style>
</head>

<body class="hold-transition login-page loading text-white"
      data-bg="{{ asset('dist/img/bg.jpg') }}">

  <div class="login-box">
    <div class="card card-outline card-primary glass-card text-white">
      <div class="card-header text-center border-bottom border-secondary d-flex flex-column align-items-center">
        <img src="https://us.123rf.com/450wm/mopc95/mopc951609/mopc95160900019/65023633-abstract-red-letter-m-logo-design-template-icon-shape-element-you-can-use-logotype-in-energy.jpg"
             alt="Logo"
             class="brand-image img-circle elevation-3 mb-2"
             width="60"
             height="60">
        <a href="#" class="h1 text-white text-center"><b>MANAGE</b> DB</a>
      </div>

      <div class="card-body">
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="input-group mb-3">
            <input type="email" name="email"
              class="form-control bg-transparent text-white border-secondary @error('email') is-invalid @enderror"
              value="{{ old('email') }}" placeholder="Email">
            <div class="input-group-append">
              <div class="input-group-text bg-transparent border-secondary text-white">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
            @error('email')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>

          <div class="input-group mb-3">
            <input type="password" name="password"
              class="form-control bg-transparent text-white border-secondary @error('password') is-invalid @enderror"
              placeholder="Password">
            <div class="input-group-append">
              <div class="input-group-text bg-transparent border-secondary text-white">
                <span class="fas fa-lock"></span>
              </div>
            </div>
            @error('password')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>

          <div class="row">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="text-light">Remember Me</label>
              </div>
            </div>
            <div class="col-4">
              <button type="submit" class="btn btn-outline-light btn-block">Sign In</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

  <script>
    // fungsi set tinggi body sesuai viewport HP/desktop
    function setFullHeight() {
      const vh = window.innerHeight;
      document.body.style.height = `${vh}px`;
    }
    window.addEventListener('resize', setFullHeight);
    window.addEventListener('load', setFullHeight);
    setFullHeight(); // jalankan langsung

    document.addEventListener('DOMContentLoaded', () => {
      const body = document.body;
      const bgUrl = body.getAttribute('data-bg');

      // lazyload background image
      const img = new Image();
      img.src = bgUrl;
      img.loading = 'lazy';
      img.onload = () => {
        body.style.backgroundImage = `url('${bgUrl}')`;

        // beri delay supaya blur terlihat halus
        setTimeout(() => {
          body.classList.remove('loading');
        }, 500);
      };
    });
  </script>

</body>
</html>
