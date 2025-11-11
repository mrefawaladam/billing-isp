<div class="modal fade" id="exampleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <input type="search" class="form-control" placeholder="Search here" id="search" />
        <a href="javascript:void(0)" data-bs-dismiss="modal" class="lh-1">
          <i class="ti ti-x fs-5 ms-3"></i>
        </a>
      </div>
      <div class="modal-body message-body" data-simplebar="">
        <h5 class="mb-0 fs-5 p-1">Quick Page Links</h5>
        <ul class="list mb-0 py-2">
          <li class="p-1 mb-1 bg-hover-light-black rounded px-2">
            <a href="{{ route('dashboard') }}">
              <span class="text-dark fw-semibold d-block">Dashboard</span>
              <span class="fs-2 d-block text-body-secondary">/dashboard</span>
            </a>
          </li>
          <li class="p-1 mb-1 bg-hover-light-black rounded px-2">
            <a href="{{ route('chat.index') }}">
              <span class="text-dark fw-semibold d-block">Chat</span>
              <span class="fs-2 d-block text-body-secondary">/chat</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
