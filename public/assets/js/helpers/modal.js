/**
 * Modal Helper
 * A reusable JavaScript utility for managing Bootstrap modals
 *
 * Usage:
 *   Modal.load('userModal', '/users/create', 'Create New User');
 *   Modal.show('userModal');
 *   Modal.hide('userModal');
 */

class ModalHelper {
    constructor() {
        this.modals = {};
    }

    /**
     * Get or create modal instance
     */
    getModal(id) {
        if (!this.modals[id]) {
            const modalEl = document.getElementById(id);
            if (modalEl) {
                this.modals[id] = new bootstrap.Modal(modalEl);
            } else {
                console.error(`Modal with id "${id}" not found`);
                return null;
            }
        }
        return this.modals[id];
    }

    /**
     * Show modal
     */
    show(id) {
        const modal = this.getModal(id);
        if (modal) {
            modal.show();
        }
    }

    /**
     * Hide modal
     */
    hide(id) {
        const modal = this.getModal(id);
        if (modal) {
            modal.hide();
        }
    }

    /**
     * Toggle modal
     */
    toggle(id) {
        const modal = this.getModal(id);
        if (modal) {
            modal.toggle();
        }
    }

    /**
     * Load content into modal via AJAX
     */
    load(id, url, title = null, options = {}) {
        const modal = this.getModal(id);
        if (!modal) return;

        const modalEl = document.getElementById(id);
        const titleEl = modalEl.querySelector('.modal-title');
        const bodyEl = modalEl.querySelector('.modal-body');

        // Set title if provided
        if (title && titleEl) {
            titleEl.textContent = title;
        }

        // Show loading indicator
        if (bodyEl) {
            bodyEl.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        }

        // Show modal
        modal.show();

        // Load content via AJAX
        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            success: function(response) {
                if (bodyEl) {
                    // Handle different response formats
                    if (typeof response === 'string') {
                        bodyEl.innerHTML = response;
                    } else if (response.html) {
                        bodyEl.innerHTML = response.html;
                    } else if (response.view) {
                        bodyEl.innerHTML = response.view;
                    } else {
                        bodyEl.innerHTML = response;
                    }
                }
            },
            error: function(xhr) {
                if (bodyEl) {
                    let errorMessage = 'Failed to load content.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    bodyEl.innerHTML = `<div class="alert alert-danger">${errorMessage}</div>`;
                }
                if (window.Toast) {
                    window.Toast.error('Failed to load content.');
                }
            }
        });
    }

    /**
     * Clear modal content
     */
    clear(id) {
        const modalEl = document.getElementById(id);
        if (modalEl) {
            const bodyEl = modalEl.querySelector('.modal-body');
            if (bodyEl) {
                bodyEl.innerHTML = '';
            }
        }
    }

    /**
     * Set modal title
     */
    setTitle(id, title) {
        const modalEl = document.getElementById(id);
        if (modalEl) {
            const titleEl = modalEl.querySelector('.modal-title');
            if (titleEl) {
                titleEl.textContent = title;
            }
        }
    }
}

// Create global instance
window.Modal = new ModalHelper();

