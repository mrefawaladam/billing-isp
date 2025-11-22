<header class="topbar">
  <div class="with-vertical">
    <!-- Start Vertical Layout Header -->
    <nav class="navbar navbar-expand-lg p-0">
      <ul class="navbar-nav">
        <li class="nav-item nav-icon-hover-bg rounded-circle d-flex">
          <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
            <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-6"></iconify-icon>
          </a>
        </li>
        <li class="nav-item d-none d-xl-flex nav-icon-hover-bg rounded-circle">
          <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exampleModal">
            <iconify-icon icon="solar:magnifer-linear" class="fs-6"></iconify-icon>
          </a>
        </li>
      </ul>

      <div class="d-block d-lg-none py-9 py-xl-0">
        <img src="{{ asset('assets/images/logos/logo.svg') }}" alt="matdash-img" />
      </div>

      <a class="navbar-toggler p-0 border-0 nav-icon-hover-bg rounded-circle" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <iconify-icon icon="solar:menu-dots-bold-duotone" class="fs-6"></iconify-icon>
      </a>

      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <div class="d-flex align-items-center justify-content-between">
          <ul class="navbar-nav flex-row mx-auto ms-lg-auto align-items-center justify-content-center">
            <li class="nav-item">
              <a class="nav-link moon dark-layout nav-icon-hover-bg rounded-circle" href="javascript:void(0)">
                <iconify-icon icon="solar:moon-line-duotone" class="moon fs-6"></iconify-icon>
              </a>
              <a class="nav-link sun light-layout nav-icon-hover-bg rounded-circle" href="javascript:void(0)" style="display: none">
                <iconify-icon icon="solar:sun-2-line-duotone" class="sun fs-6"></iconify-icon>
              </a>
            </li>

            <li class="nav-item d-block d-xl-none">
              <a class="nav-link nav-icon-hover-bg rounded-circle" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <iconify-icon icon="solar:magnifer-line-duotone" class="fs-6"></iconify-icon>
              </a>
            </li>

            <!-- Notification Dropdown -->
            <li class="nav-item dropdown nav-icon-hover-bg rounded-circle">
              <a class="nav-link position-relative" href="javascript:void(0)" id="drop2" aria-expanded="false">
                <iconify-icon icon="solar:bell-bing-line-duotone" class="fs-6"></iconify-icon>
              </a>
              <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                <div class="d-flex align-items-center justify-content-between py-3 px-7">
                  <h5 class="mb-0 fs-5 fw-semibold">Notifications</h5>
                  <span class="badge text-bg-primary rounded-4 px-3 py-1 lh-sm">5 new</span>
                </div>
                <div class="message-body" data-simplebar>
                  <!-- Notification items -->
                  <a href="javascript:void(0)" class="py-6 px-7 d-flex align-items-center dropdown-item gap-3">
                    <span class="flex-shrink-0 bg-danger-subtle rounded-circle round d-flex align-items-center justify-content-center fs-6 text-danger">
                      <iconify-icon icon="solar:widget-3-line-duotone"></iconify-icon>
                    </span>
                    <div class="w-75 d-inline-block">
                      <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-1 fw-semibold">New Notification</h6>
                        <span class="d-block fs-2">9:30 AM</span>
                      </div>
                      <span class="d-block text-truncate text-truncate fs-11">You have a new notification</span>
                    </div>
                  </a>
                </div>
                <div class="py-6 px-7 mb-1">
                  <button class="btn btn-primary w-100">See All Notifications</button>
                </div>
              </div>
            </li>


            <!-- Profile Dropdown -->
            @auth
            <li class="nav-item dropdown">
              <a class="nav-link" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-flex align-items-center gap-2 lh-base">
                  <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 35px; height: 35px; font-weight: bold;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                  </div>
                  <div class="d-none d-md-block">
                    <span class="fw-semibold">{{ auth()->user()->name }}</span>
                  </div>
                  <iconify-icon icon="solar:alt-arrow-down-bold" class="fs-2"></iconify-icon>
                </div>
              </a>
              <div class="dropdown-menu profile-dropdown dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                <div class="position-relative px-4 pt-3 pb-2">
                  <div class="d-flex align-items-center mb-3 pb-3 border-bottom gap-6">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 56px; height: 56px; font-size: 20px; font-weight: bold;">
                      {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                      <h5 class="mb-0 fs-12">{{ auth()->user()->name }}</h5>
                      <p class="mb-0 text-dark">{{ auth()->user()->email }}</p>
                      @if(auth()->user()->roles->count() > 0)
                        <span class="badge bg-success fs-11 mt-1">
                          {{ auth()->user()->roles->first()->name }}
                        </span>
                      @endif
                    </div>
                  </div>
                  <div class="message-body">
                    <a href="{{ route('profile.index') }}" class="p-2 dropdown-item h6 rounded-1">
                      <iconify-icon icon="solar:user-id-line-duotone" class="me-2"></iconify-icon>
                      Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                      @csrf
                      <button type="submit" class="p-2 dropdown-item h6 rounded-1 border-0 bg-transparent w-100 text-start">
                        <iconify-icon icon="solar:logout-2-line-duotone" class="me-2"></iconify-icon>
                        Keluar
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </li>
            @endauth
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Vertical Layout Header -->
  </div>
</header>
