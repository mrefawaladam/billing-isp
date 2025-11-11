@props([
    'id',
    'title' => 'Modal',
    'size' => 'lg', // sm, md, lg, xl, fullscreen
    'contentId' => null,
    'showFooter' => true
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-{{ $size }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="{{ $contentId ?? $id . 'Content' }}">
                {{ $slot }}
            </div>
            @if($showFooter)
                <div class="modal-footer">
                    @isset($footer)
                        {{ $footer }}
                    @else
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @endisset
                </div>
            @endif
        </div>
    </div>
</div>

