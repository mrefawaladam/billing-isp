<aside class="left-sidebar with-vertical">
  <div>
    <!-- Start Vertical Layout Sidebar -->
    <div>
      <div class="brand-logo d-flex align-items-center">
        <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
          <img src="{{ asset('assets/images/logos/logo.svg') }}" alt="Logo" />
        </a>
      </div>

      <!-- Dashboard -->
      <nav class="sidebar-nav scroll-sidebar" data-simplebar>
        <ul class="sidebar-menu" id="sidebarnav">
          <!-- Dashboards -->
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Dashboards</span>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('dashboard') }}" id="get-url" aria-expanded="false">
              <iconify-icon icon="solar:widget-add-line-duotone" class=""></iconify-icon>
              <span class="hide-menu">Dashboard</span>
            </a>
          </li>

          <!-- Apps -->
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Apps</span>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('chat.index') }}">
              <iconify-icon icon="solar:chat-round-line-line-duotone"></iconify-icon>
              <span class="hide-menu">Chat</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="#">
              <iconify-icon icon="solar:letter-line-duotone"></iconify-icon>
              <span class="hide-menu">Email</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="#">
              <iconify-icon icon="solar:calendar-mark-line-duotone"></iconify-icon>
              <span class="hide-menu">Calendar</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="#">
              <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
              <span class="hide-menu">Notes</span>
            </a>
          </li>

          <!-- Pages -->
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Pages</span>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('users.index') }}">
              <iconify-icon icon="solar:users-group-two-rounded-line-duotone"></iconify-icon>
              <span class="hide-menu">User Management</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a href="{{ route('blank') }}" class="sidebar-link">
              <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
              <span class="hide-menu">Blank Page</span>
            </a>
          </li>

          <!-- Auth -->
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Auth</span>
          </li>

          <li class="sidebar-item">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="sidebar-link border-0 bg-transparent w-100 text-start">
                <iconify-icon icon="solar:logout-2-line-duotone"></iconify-icon>
                <span class="hide-menu">Logout</span>
              </button>
            </form>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</aside>
