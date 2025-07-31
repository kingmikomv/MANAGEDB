

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MANAGE DB | AQT NETWORK</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
</head>

<body class="hold-transition login-page bg-dark text-white">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary bg-dark text-white shadow-lg">
            <div class="card-header text-center border-bottom border-secondary d-flex flex-column align-items-center">
                <img src="https://png.pngtree.com/png-vector/20211106/ourmid/pngtree-letter-m-logo-png-image_4008793.png"
                    alt="AdminLTE Logo" class="brand-image img-circle elevation-3 mb-2" style="opacity: .8"
                    width="60" height="60">
                <a href="#" class="h1 text-white text-center"><b>MANAGE</b> DB</a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="input-group mb-3">
                        <input type="email" name="email"
                            class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="Email">

                        <div class="input-group-append">
                            <div class="input-group-text bg-dark border-secondary text-white">
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
                            class="form-control bg-dark text-white border-secondary @error('password') is-invalid @enderror"
                            placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text bg-dark border-secondary text-white">
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
                                <input type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember" class="text-light">
                                    Remember Me
                                </label>
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
</body>

</html>
