@props([
    'id' => 'notificationToast',
    'position' => 'top-0 end-0',
    'zIndex' => 9999,
    'autohide' => true,
    'delay' => 4000
])

<div class="toast-container-custom position-fixed top-0 end-0 p-3" style="z-index: {{ $zIndex }};">
    <div id="{{ $id }}" class="toast toast-custom" role="alert" aria-live="assertive" aria-atomic="true"
         data-bs-autohide="{{ $autohide ? 'true' : 'false' }}"
         data-bs-delay="{{ $delay }}">
        <div class="toast-content-wrapper" id="{{ $id }}ContentWrapper">
            <div class="toast-left-bar" id="{{ $id }}LeftBar"></div>
            <div class="toast-main-content">
                <div class="toast-header-custom" id="{{ $id }}Header">
                    <div class="d-flex align-items-center">
                        <div class="toast-icon-wrapper" id="{{ $id }}IconWrapper">
                            <iconify-icon icon="solar:bell-bing-bold-duotone" class="toast-icon" id="{{ $id }}Icon"></iconify-icon>
                        </div>
                        <strong class="toast-title" id="{{ $id }}Title">Notification</strong>
                    </div>
                    <button type="button" class="toast-close-btn" data-bs-dismiss="toast" aria-label="Close">
                        <iconify-icon icon="solar:close-circle-bold" class="close-icon"></iconify-icon>
                    </button>
                </div>
                <div class="toast-body-custom" id="{{ $id }}Message"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.toast-container-custom {
    pointer-events: none;
}

.toast-container-custom > * {
    pointer-events: auto;
}

.toast-custom {
    min-width: 320px;
    max-width: 400px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
    background: white;
    margin-bottom: 12px;
}

.toast-content-wrapper {
    display: flex;
    position: relative;
}

.toast-left-bar {
    width: 4px;
    background-color: var(--bs-primary);
    border-radius: 12px 0 0 12px;
    flex-shrink: 0;
}

.toast-left-bar.success {
    background-color: #10b981;
}

.toast-left-bar.error {
    background-color: #ef4444;
}

.toast-left-bar.warning {
    background-color: #f59e0b;
}

.toast-left-bar.info {
    background-color: #3b82f6;
}

.toast-left-bar.primary {
    background-color: var(--bs-primary);
}

.toast-main-content {
    flex: 1;
    padding: 16px;
}

.toast-header-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    border: none;
    padding: 0;
    background: transparent;
}

.toast-icon-wrapper {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
    background-color: #f3f4f6;
}

.toast-icon-wrapper.success {
    background-color: #d1fae5;
}

.toast-icon-wrapper.error {
    background-color: #fee2e2;
}

.toast-icon-wrapper.warning {
    background-color: #fef3c7;
}

.toast-icon-wrapper.info {
    background-color: #dbeafe;
}

.toast-icon-wrapper.primary {
    background-color: #e0e7ff;
}

.toast-icon {
    font-size: 20px;
}

.toast-icon-wrapper.success .toast-icon {
    color: #10b981;
}

.toast-icon-wrapper.error .toast-icon {
    color: #ef4444;
}

.toast-icon-wrapper.warning .toast-icon {
    color: #f59e0b;
}

.toast-icon-wrapper.info .toast-icon {
    color: #3b82f6;
}

.toast-icon-wrapper.primary .toast-icon {
    color: var(--bs-primary);
}

.toast-title {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.toast-close-btn {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    transition: background-color 0.2s;
    margin-left: auto;
}

.toast-close-btn:hover {
    background-color: #f3f4f6;
}

.close-icon {
    font-size: 16px;
    color: #6b7280;
}

.toast-body-custom {
    padding: 0;
    font-size: 14px;
    line-height: 1.5;
    color: #6b7280;
    margin-left: 48px;
}

/* Animation */
.toast.showing {
    opacity: 0;
    transform: translateX(100%);
}

.toast.show:not(.showing) {
    opacity: 1;
    transform: translateX(0);
}

.toast {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
</style>
@endpush
