<div class="user-details">
    <div class="row mb-3">
        <div class="col-md-6">
            <strong>Name:</strong> {{ $user->name }}
        </div>
        <div class="col-md-6">
            <strong>Email:</strong> {{ $user->email }}
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <strong>Roles:</strong>
            @foreach($user->roles as $role)
                <span class="badge bg-primary me-1">{{ $role->name }}</span>
            @endforeach
            @if($user->roles->isEmpty())
                <span class="text-muted">No roles</span>
            @endif
        </div>
        <div class="col-md-6">
            <strong>Created At:</strong> {{ $user->created_at->format('Y-m-d H:i:s') }}
        </div>
    </div>

    @if($user->permissions->isNotEmpty())
        <div class="row mb-3">
            <div class="col-12">
                <strong>Permissions:</strong>
                @foreach($user->permissions as $permission)
                    <span class="badge bg-info me-1">{{ $permission->name }}</span>
                @endforeach
            </div>
        </div>
    @endif
</div>

