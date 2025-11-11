@props(['title', 'items' => []])

<nav aria-label="breadcrumb" class="ms-auto">
  <ol class="breadcrumb">
    <li class="breadcrumb-item d-flex align-items-center">
      <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
      </a>
    </li>
    @foreach($items as $item)
      <li class="breadcrumb-item">
        @if(isset($item['url']))
          <a href="{{ $item['url'] }}" class="text-muted text-decoration-none">{{ $item['label'] }}</a>
        @else
          <span class="text-muted">{{ $item['label'] }}</span>
        @endif
      </li>
    @endforeach
    <li class="breadcrumb-item" aria-current="page">
      <span class="badge fw-medium fs-2 bg-primary-subtle text-primary">{{ $title }}</span>
    </li>
  </ol>
</nav>
