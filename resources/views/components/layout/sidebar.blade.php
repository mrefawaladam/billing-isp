<aside class="left-sidebar with-vertical">
  <div>
    <!-- Start Vertical Layout Sidebar -->
    <div>
      <div class="brand-logo d-flex align-items-center">
        <a href="{{ route('dashboard') }}" class="text-nowrap logo-img">
          <img src="{{ asset('logo.png') }}" alt="Logo" style="max-height: 35px; width: auto;" />
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

          <!-- Management Section (Admin, Manager, Moderator) -->
          @auth
          @if(auth()->user()->hasAnyRole(['admin', 'manager', 'moderator']))
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Manajemen</span>
          </li>

          <!-- User Management (Admin & Manager only) -->
          @if(auth()->user()->hasAnyRole(['admin', 'manager']))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('users.index') }}">
              <iconify-icon icon="solar:users-group-two-rounded-line-duotone"></iconify-icon>
              <span class="hide-menu">User Management</span>
            </a>
          </li>
          @endif

          <!-- Customer Management (Admin, Manager, Moderator) -->
          @if(auth()->user()->hasAnyRole(['admin', 'manager', 'moderator']))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('customers.index') }}">
              <iconify-icon icon="solar:user-id-line-duotone"></iconify-icon>
              <span class="hide-menu">Customer Management</span>
            </a>
          </li>
          @endif

          <!-- Invoice Management (Admin, Manager, Moderator) -->
          @if(auth()->user()->hasAnyRole(['admin', 'manager', 'moderator']))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('invoices.index') }}">
              <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
              <span class="hide-menu">Invoice Management</span>
            </a>
          </li>
          @endif

          <!-- Inventory Management (Admin only) -->
          @if(auth()->user()->hasRole('admin'))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('inventory.index') }}">
              <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
              <span class="hide-menu">Inventory Management</span>
            </a>
          </li>
          @endif

          <!-- Package Management (Admin, Manager) -->
          @if(auth()->user()->hasAnyRole(['admin', 'manager']))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('packages.index') }}">
              <iconify-icon icon="solar:card-send-line-duotone"></iconify-icon>
              <span class="hide-menu">Package Management</span>
            </a>
          </li>
          @endif

          <!-- Peta Lokasi (Admin, Manager) -->
          @if(auth()->user()->hasAnyRole(['admin', 'manager']))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('map.index') }}">
              <iconify-icon icon="solar:map-point-line-duotone"></iconify-icon>
              <span class="hide-menu">Peta Lokasi</span>
            </a>
          </li>
          @endif

          <!-- Laporan Pembayaran (Admin, Manager) -->
          @if(auth()->user()->hasAnyRole(['admin', 'manager']))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('payments.report') }}">
              <iconify-icon icon="solar:document-medicine-line-duotone"></iconify-icon>
              <span class="hide-menu">Laporan Pembayaran</span>
            </a>
          </li>
          @endif

          <!-- Notifikasi WhatsApp (Admin, Manager) -->
          @if(auth()->user()->hasAnyRole(['admin', 'manager']))
          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('whatsapp.index') }}">
              <iconify-icon icon="solar:chat-round-line-duotone"></iconify-icon>
              <span class="hide-menu">Notifikasi WhatsApp</span>
            </a>
          </li>
          @endif
          @endif
          @endauth

          <!-- Field Officer Section (Only for Staff Role) -->
          @auth
          @if(auth()->user()->hasRole('staff'))
          <li>
            <span class="sidebar-divider lg"></span>
          </li>
          <li class="nav-small-cap">
            <iconify-icon icon="solar:menu-dots-linear" class="mini-icon"></iconify-icon>
            <span class="hide-menu">Tim Staff</span>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('field-officer.dashboard') }}">
              <iconify-icon icon="solar:widget-add-line-duotone"></iconify-icon>
              <span class="hide-menu">Dashboard Staff</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('field-officer.customers') }}">
              <iconify-icon icon="solar:user-id-line-duotone"></iconify-icon>
              <span class="hide-menu">Daftar Pelanggan</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('field-officer.map') }}">
              <iconify-icon icon="solar:map-point-line-duotone"></iconify-icon>
              <span class="hide-menu">Peta Lokasi</span>
            </a>
          </li>
          @endif
          @endauth

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
