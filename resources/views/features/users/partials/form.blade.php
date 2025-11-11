@php
    $isEdit = isset($user) && $user !== null;
@endphp

<form id="user-form" action="{{ $formAction }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="{{ $isEdit ? $user->name : old('name') }}"
            required
        >
        <div class="invalid-feedback d-none" id="name-error"></div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input
            type="email"
            class="form-control"
            id="email"
            name="email"
            value="{{ $isEdit ? $user->email : old('email') }}"
            required
        >
        <div class="invalid-feedback d-none" id="email-error"></div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">
            Password
            @if($isEdit)
                <small class="text-muted">(leave blank to keep current password)</small>
            @else
                <span class="text-danger">*</span>
            @endif
        </label>
        <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            {{ !$isEdit ? 'required' : '' }}
        >
        <div class="invalid-feedback d-none" id="password-error"></div>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">
            Confirm Password
            @if(!$isEdit)
                <span class="text-danger">*</span>
            @endif
        </label>
        <input
            type="password"
            class="form-control"
            id="password_confirmation"
            name="password_confirmation"
            {{ !$isEdit ? 'required' : '' }}
        >
    </div>

    <div class="mb-3">
        <label class="form-label">Roles</label>
        <div class="row">
            @foreach($roles as $role)
                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->id }}"
                            id="role_{{ $role->id }}"
                            {{ ($isEdit && $user->hasRole($role->name)) ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-danger small d-none" id="roles-error"></div>
    </div>
</form>

