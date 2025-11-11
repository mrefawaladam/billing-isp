<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />

  <!-- Core Css -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @stack('styles')

  <title>@yield('title', 'MatDash Bootstrap Admin')</title>
</head>

<body class="link-sidebar">
  <!-- Preloader -->
  <div class="preloader">
    <img src="{{ asset('assets/images/logos/favicon.png') }}" alt="loader" class="lds-ripple img-fluid" />
  </div>
  
  <div id="main-wrapper">
    <!-- Sidebar Start -->
    @include('components.layout.sidebar')

    <!--  Sidebar End -->
    <div class="page-wrapper">
      <!--  Header Start -->
      @include('components.layout.header')

      <!--  Header End -->

      <div class="body-wrapper">
        <div class="container-fluid">
          @yield('content')
        </div>
      </div>
    </div>

    <!-- Customizer Button -->
    <button class="btn btn-danger p-3 rounded-circle d-flex align-items-center justify-content-center customizer-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
      <i class="icon ti ti-settings fs-7"></i>
    </button>

    <!-- Customizer Offcanvas -->
    @include('components.layout.customizer')

    <!--  Search Bar -->
    @include('components.ui.search-modal')
  </div>

  <div class="dark-transparent sidebartoggler"></div>

  <!-- Scripts -->
  @include('components.layout.scripts')
  @stack('scripts')
</body>

</html>
