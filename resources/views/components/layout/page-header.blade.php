@props([
    'title',
    'breadcrumbTitle' => null,
    'breadcrumbItems' => [],
    'action' => null,
    'actionLabel' => null,
    'actionIcon' => 'solar:add-circle-line-duotone'
])

<div class="card card-body py-3">
    <div class="row align-items-center">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-space-between">
                <h4 class="mb-4 mb-sm-0 card-title">{{ $title }}</h4>
                <x-layout.breadcrumb
                    :title="$breadcrumbTitle ?? $title"
                    :items="$breadcrumbItems"
                />
            </div>
        </div>
    </div>
</div>

