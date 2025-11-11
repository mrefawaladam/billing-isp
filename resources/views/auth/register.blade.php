<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />
  <title>Register - MatDash Bootstrap Admin</title>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 d-flex align-items-center justify-content-center" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="text-center text-white p-4">
          <img src="{{ asset('assets/images/logos/logo.svg') }}" alt="Logo" class="mb-4" style="filter: brightness(0) invert(1);" />
          <h2 class="fw-bold mb-3">Join Us!</h2>
          <p>Create your account to get started</p>
        </div>
      </div>
      <div class="col-lg-9 d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="w-100" style="max-width: 450px;">
          <div class="card shadow-lg border-0">
            <div class="card-body p-5">
              <h4 class="fw-bold mb-4">Sign Up</h4>
              
              @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              @endif

              <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                  <label for="name" class="form-label">Full Name</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="d-grid mb-3">
                  <button type="submit" class="btn btn-primary">Sign Up</button>
                </div>
              </form>

              <div class="text-center mt-4">
                <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="text-decoration-none">Sign In</a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
  <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
