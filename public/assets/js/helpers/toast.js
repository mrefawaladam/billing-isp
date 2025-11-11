/**
 * Toast Notification Helper
 * A reusable JavaScript utility for showing toast notifications
 *
 * Usage:
 *   Toast.show('success', 'User created successfully!');
 *   Toast.show('error', 'Failed to delete user.');
 *   Toast.show('warning', 'Please check your input.');
 *   Toast.show('info', 'New update available.');
 */

class ToastNotification {
    constructor(options = {}) {
        this.defaultOptions = {
            id: options.id || 'notificationToast',
            position: options.position || 'top-0 end-0',
            autohide: options.autohide !== false,
            delay: options.delay || 4000,
            zIndex: options.zIndex || 9999
        };

        this.toastConfig = {
            success: {
                icon: 'solar:check-circle-bold-duotone',
                bgColor: 'bg-success',
                title: 'Success',
                textColor: 'text-white'
            },
            error: {
                icon: 'solar:close-circle-bold-duotone',
                bgColor: 'bg-danger',
                title: 'Error',
                textColor: 'text-white'
            },
            warning: {
                icon: 'solar:danger-triangle-bold-duotone',
                bgColor: 'bg-warning',
                title: 'Warning',
                textColor: 'text-dark'
            },
            info: {
                icon: 'solar:info-circle-bold-duotone',
                bgColor: 'bg-info',
                title: 'Info',
                textColor: 'text-white'
            },
            primary: {
                icon: 'solar:bell-bing-bold-duotone',
                bgColor: 'bg-primary',
                title: 'Notification',
                textColor: 'text-white'
            }
        };

        this.init();
    }

    init() {
        // Check if toast element exists, if not create it
        let toastEl = document.getElementById(this.defaultOptions.id);
        if (!toastEl) {
            this.createToastElement();
        } else {
            this.toast = new bootstrap.Toast(toastEl, {
                autohide: this.defaultOptions.autohide,
                delay: this.defaultOptions.delay
            });
        }
    }

    createToastElement() {
        const container = document.createElement('div');
        container.className = `toast-container-custom position-fixed top-0 end-0 p-3`;
        container.style.zIndex = this.defaultOptions.zIndex;

        const toast = document.createElement('div');
        toast.id = this.defaultOptions.id;
        toast.className = 'toast toast-custom';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.setAttribute('data-bs-autohide', this.defaultOptions.autohide);
        toast.setAttribute('data-bs-delay', this.defaultOptions.delay);

        toast.innerHTML = `
            <div class="toast-content-wrapper" id="${this.defaultOptions.id}ContentWrapper">
                <div class="toast-left-bar" id="${this.defaultOptions.id}LeftBar"></div>
                <div class="toast-main-content">
                    <div class="toast-header-custom" id="${this.defaultOptions.id}Header">
                        <div class="d-flex align-items-center">
                            <div class="toast-icon-wrapper" id="${this.defaultOptions.id}IconWrapper">
                                <iconify-icon icon="solar:bell-bing-bold-duotone" class="toast-icon" id="${this.defaultOptions.id}Icon"></iconify-icon>
                            </div>
                            <strong class="toast-title" id="${this.defaultOptions.id}Title">Notification</strong>
                        </div>
                        <button type="button" class="toast-close-btn" data-bs-dismiss="toast" aria-label="Close">
                            <iconify-icon icon="solar:close-circle-bold" class="close-icon"></iconify-icon>
                        </button>
                    </div>
                    <div class="toast-body-custom" id="${this.defaultOptions.id}Message"></div>
                </div>
            </div>
        `;

        container.appendChild(toast);
        document.body.appendChild(container);

        this.toast = new bootstrap.Toast(toast, {
            autohide: this.defaultOptions.autohide,
            delay: this.defaultOptions.delay
        });
    }

    show(type = 'primary', message, title = null, options = {}) {
        const config = this.toastConfig[type] || this.toastConfig.primary;
        const toastEl = document.getElementById(this.defaultOptions.id);

        if (!toastEl) {
            console.error('Toast element not found');
            return;
        }

        const toastTitle = document.getElementById(`${this.defaultOptions.id}Title`);
        const toastMessage = document.getElementById(`${this.defaultOptions.id}Message`);
        const toastIcon = document.getElementById(`${this.defaultOptions.id}Icon`);
        const toastIconWrapper = document.getElementById(`${this.defaultOptions.id}IconWrapper`);
        const toastLeftBar = document.getElementById(`${this.defaultOptions.id}LeftBar`);

        // Set icon
        if (toastIcon) {
            toastIcon.setAttribute('icon', config.icon);
        }

        // Set title
        if (toastTitle) {
            toastTitle.textContent = title || config.title;
        }

        // Set message
        if (toastMessage) {
            toastMessage.textContent = message;
        }

        // Set left bar color
        if (toastLeftBar) {
            toastLeftBar.className = `toast-left-bar ${type}`;
        }

        // Set icon wrapper color
        if (toastIconWrapper) {
            toastIconWrapper.className = `toast-icon-wrapper ${type}`;
        }

        // Show toast
        if (!this.toast) {
            this.toast = new bootstrap.Toast(toastEl, {
                autohide: options.autohide !== undefined ? options.autohide : this.defaultOptions.autohide,
                delay: options.delay || this.defaultOptions.delay
            });
        }

        this.toast.show();
    }

    success(message, title = null, options = {}) {
        this.show('success', message, title, options);
    }

    error(message, title = null, options = {}) {
        this.show('error', message, title, options);
    }

    warning(message, title = null, options = {}) {
        this.show('warning', message, title, options);
    }

    info(message, title = null, options = {}) {
        this.show('info', message, title, options);
    }
}

// Create global instance
window.Toast = new ToastNotification();

