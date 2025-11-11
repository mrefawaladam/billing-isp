@props([
    'id',
    'title' => 'Data Table',
    'subtitle' => null,
    'createButton' => null,
    'createButtonText' => 'Add New',
    'createButtonId' => null,
    'columns' => [],
    'ajaxUrl' => null,
    'pageLength' => 10,
    'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
    'order' => [[0, 'desc']],
    'responsive' => true,
    'autoWidth' => false,
    'processing' => true,
    'serverSide' => true,
    'language' => null,
    'config' => []
])

<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">{{ $title }}</h4>
                    @if($subtitle)
                        <p class="card-subtitle mb-3">{{ $subtitle }}</p>
                    @endif
                </div>
                @if($createButton !== false)
                    <button type="button" class="btn btn-primary" id="{{ $createButtonId ?? 'btn-create' }}">
                        <i class="ti ti-plus me-1"></i> {{ $createButtonText }}
                    </button>
                @endif
            </div>
            <div class="table-responsive">
                <table id="{{ $id }}" class="table table-striped table-bordered text-nowrap align-middle">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ is_array($column) ? ($column['title'] ?? $column['data'] ?? '') : $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@php
    $defaultLanguage = [
        'processing' => '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
        'lengthMenu' => 'Show _MENU_ entries',
        'zeroRecords' => 'No matching records found',
        'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
        'infoEmpty' => 'Showing 0 to 0 of 0 entries',
        'infoFiltered' => '(filtered from _MAX_ total entries)',
        'search' => 'Search:',
        'paginate' => [
            'first' => 'First',
            'last' => 'Last',
            'next' => 'Next',
            'previous' => 'Previous'
        ]
    ];
    $languageConfig = $language ?? $defaultLanguage;
@endphp

@push('scripts')
<script>
$(document).ready(function() {
    const defaultConfig = {
        processing: {{ $processing ? 'true' : 'false' }},
        serverSide: {{ $serverSide ? 'true' : 'false' }},
        @if($ajaxUrl)
        ajax: "{{ $ajaxUrl }}",
        @endif
        columns: @json($columns),
        pageLength: {{ $pageLength }},
        lengthMenu: @json($lengthMenu),
        responsive: {{ $responsive ? 'true' : 'false' }},
        autoWidth: {{ $autoWidth ? 'true' : 'false' }},
        order: @json($order),
        language: @json($languageConfig)
    };

    const customConfig = @json($config);
    const finalConfig = { ...defaultConfig, ...customConfig };

    const table = $('#{{ $id }}').DataTable(finalConfig);

    // Store table instance globally for access
    window['{{ $id }}Table'] = table;
});
</script>
@endpush

