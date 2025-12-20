<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />
  <title>Login - MatDash Bootstrap Admin</title>
  <style>
    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .login-sidebar {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .login-form-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .login-card {
      width: 100%;
      max-width: 450px;
    }
    
    .login-logo {
      height: auto;
      width: 100%;
    }
    
    @media (max-width: 991.98px) {
      .login-sidebar {
        min-height: auto;
        padding: 30px 20px;
      }
      
      .login-form-container {
        min-height: auto;
        padding: 20px;
      }
      
      .login-card {
        max-width: 100%;
      }
    }
    
    @media (max-width: 575.98px) {
      .login-wrapper {
        padding: 10px;
      }
      
      .login-sidebar {
        padding: 20px 15px;
      }
      
      .login-form-container {
        padding: 15px 10px;
      }
      
      .login-card .card-body {
        padding: 1.25rem !important;
      }
      
      h4 {
        font-size: 1.25rem;
      }
      
      .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid p-0">
    <div class="row g-0">
      <!-- Sidebar - Hidden on mobile, shown on desktop -->
      <div class="col-lg-3 d-none d-lg-flex login-sidebar">
        <div class="text-center text-white p-4 w-100">
          <div class="mb-5">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="login-logo mb-4" style="max-width: 150px; height: auto; filter: brightness(0) invert(1);" />
          </div>
          <h2 class="fw-bold mb-3">Welcome Back!</h2>
          <p class="fs-5">Sign in to continue to your account</p>
        </div>
      </div>
      
      <!-- Mobile Header - Shown only on mobile -->
      <div class="col-12 d-lg-none login-sidebar">
        <div class="text-center text-white p-4">
          <div class="mb-4">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="login-logo mb-3" style="max-width: 120px; height: auto; filter: brightness(0) invert(1);" />
          </div>
          <h4 class="fw-bold mb-2">Welcome Back!</h4>
          <p class="mb-0">Sign in to continue to your account</p>
        </div>
      </div>
      
      <!-- Login Form -->
      <div class="col-lg-9 col-12 login-form-container">
        <div class="login-card">
          <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-lg-5">
              <h4 class="fw-bold mb-4 text-center text-lg-start">Sign In</h4>
              
              @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  {{ session('status') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              @endif

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

              <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
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

                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="remember" name="remember">
                  <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <div class="d-grid mb-3">
                  <button type="submit" class="btn btn-primary">Sign In</button>
                </div>

                <div class="text-center">
                  <a href="{{ route('password.request') }}" class="text-decoration-none">Forgot Password?</a>
                </div>
              </form>

              <div class="text-center mt-4">
                <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-decoration-none">Sign Up</a></p>
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
