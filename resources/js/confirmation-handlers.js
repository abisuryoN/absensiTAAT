/**
 * Global Confirmation Handlers
 * Automatically attaches SweetAlert2 confirmations to all delete forms and important action buttons
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================================================
    // DELETE FORM CONFIRMATIONS
    // ============================================================================
    
    // Find all forms with DELETE method (Laravel uses hidden _method field)
    document.querySelectorAll('form').forEach(function(form) {
        const methodInput = form.querySelector('input[name="_method"]');
        const isDeletion = methodInput && methodInput.value.toUpperCase() === 'DELETE';
        
        if (isDeletion) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get item name from form's data attribute or button text
                const itemName = form.dataset.itemName || 
                                form.querySelector('[data-item-name]')?.dataset.itemName || 
                                '';
                
                confirmDelete(itemName, function() {
                    form.submit();
                });
            });
        }
    });

    // ============================================================================
    // GURU PIKET LOGOUT CONFIRMATION
    // ============================================================================
    
    // Handle guru piket dashboard logout button
    const guruPiketLogoutForm = document.querySelector('form[action*="logout"]');
    if (guruPiketLogoutForm && window.location.pathname.includes('piket')) {
        const logoutButton = guruPiketLogoutForm.querySelector('button[type="submit"]');
        if (logoutButton) {
            logoutButton.addEventListener('click', function(e) {
                e.preventDefault();
                confirmLogout(function() {
                    guruPiketLogoutForm.submit();
                });
            });
        }
    }

    // ============================================================================
    // SPECIFIC BUTTON CONFIRMATIONS
    // ============================================================================
    
    // Handle any standalone delete buttons (not in forms)
    document.querySelectorAll('.btn-delete, .delete-btn, [data-confirm-delete]').forEach(function(button) {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            const href = this.getAttribute('href');
            
            // Only prevent default if it's a link or has a form
            if (form || href) {
                e.preventDefault();
                
                const itemName = this.dataset.itemName || '';
                
                confirmDelete(itemName, function() {
                    if (form) {
                        form.submit();
                    } else if (href) {
                        window.location.href = href;
                    }
                });
            }
        });
    });

    // ============================================================================
    // ACCOUNT DELETION CONFIRMATION (Profile page)
    // ============================================================================
    
    const deleteAccountModal = document.getElementById('confirmDeleteModal');
    if (deleteAccountModal) {
        const deleteAccountForm = deleteAccountModal.querySelector('form');
        if (deleteAccountForm) {
            // Remove the Bootstrap modal behavior and use SweetAlert instead
            const deleteAccountTrigger = document.querySelector('[data-bs-target="#confirmDeleteModal"]');
            if (deleteAccountTrigger) {
                deleteAccountTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    confirmAction({
                        title: 'Yakin ingin menghapus akun Anda?',
                        text: 'Semua data Anda akan dihapus secara permanen dan tidak dapat dikembalikan.',
                        confirmButtonText: 'Ya, Hapus Akun',
                        confirmButtonColor: '#d33',
                        icon: 'warning',
                        input: 'password',
                        inputPlaceholder: 'Masukkan password untuk konfirmasi',
                        inputAttributes: {
                            autocomplete: 'current-password'
                        },
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        preConfirm: (password) => {
                            if (!password) {
                                Swal.showValidationMessage('Password diperlukan untuk konfirmasi');
                                return false;
                            }
                            return password;
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            // Set the password in the hidden form field
                            const passwordInput = deleteAccountForm.querySelector('input[name="password"]');
                            if (passwordInput) {
                                passwordInput.value = result.value;
                            }
                            deleteAccountForm.submit();
                        }
                    });
                });
            }
        }
    }

    // ============================================================================
    // END SESSION CONFIRMATION (Guru Piket)
    // ============================================================================
    
    document.querySelectorAll('[data-action="end-session"], .end-session-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const href = this.getAttribute('href');
            
            confirmEndSession(function() {
                if (form) {
                    form.submit();
                } else if (href) {
                    window.location.href = href;
                }
            });
        });
    });

    // ============================================================================
    // FORM SUBMISSION CONFIRMATIONS (Optional - for critical forms)
    // ============================================================================
    
    // Add confirmation to forms marked with data-confirm-submit
    document.querySelectorAll('form[data-confirm-submit]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const confirmMessage = form.dataset.confirmMessage || 'Yakin ingin menyimpan perubahan ini?';
            const confirmTitle = form.dataset.confirmTitle || 'Konfirmasi';
            
            confirmAction({
                title: confirmTitle,
                text: confirmMessage,
                confirmButtonText: 'Ya, Simpan',
                confirmButtonColor: '#3085d6',
                onConfirm: function() {
                    form.submit();
                }
            });
        });
    });
});

// ============================================================================
// ADDITIONAL HELPERS
// ============================================================================

/**
 * Helper function to replace inline onsubmit confirmations
 * This will catch any old-style confirm() dialogs and replace them
 */
window.addEventListener('load', function() {
    // Override default confirm() for forms to use SweetAlert instead
    const originalConfirm = window.confirm;
    
    window.confirm = function(message) {
        // For form submissions, we'll handle them with our event listeners
        // For other cases, fall back to original confirm
        return originalConfirm.call(this, message);
    };
});