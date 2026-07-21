/**
 * SweetAlert2 Confirmation Helper
 * Provides reusable confirmation dialogs for important actions
 */

import Swal from 'sweetalert2';

/**
 * Show confirmation dialog before executing an action
 * @param {Object} options - Configuration options
 * @param {string} options.title - Dialog title
 * @param {string} options.text - Dialog text/description
 * @param {string} options.confirmButtonText - Text for confirm button
 * @param {string} options.icon - Icon type (warning, error, success, info, question)
 * @param {string} options.confirmButtonColor - Color for confirm button
 * @param {Function} options.onConfirm - Function to execute on confirmation
 * @returns {Promise}
 */
export function confirmAction(options) {
    const defaults = {
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        cancelButtonText: 'Batal',
        reverseButtons: true
    };

    const config = { ...defaults, ...options };

    return Swal.fire({
        title: config.title,
        text: config.text,
        icon: config.icon,
        showCancelButton: config.showCancelButton,
        confirmButtonColor: config.confirmButtonColor,
        cancelButtonColor: config.cancelButtonColor,
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: config.cancelButtonText,
        reverseButtons: config.reverseButtons
    }).then((result) => {
        if (result.isConfirmed && config.onConfirm) {
            return config.onConfirm();
        }
        return result;
    });
}

/**
 * Show success message after action is completed
 * @param {string} title - Success message title
 * @param {string} text - Success message text (optional)
 * @returns {Promise}
 */
export function showSuccess(title, text = '') {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        timer: 2000,
        showConfirmButton: false
    });
}

/**
 * Show error message
 * @param {string} title - Error message title
 * @param {string} text - Error message text (optional)
 * @returns {Promise}
 */
export function showError(title, text = '') {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonText: 'OK',
        confirmButtonColor: '#d33'
    });
}

/**
 * Confirm logout action
 * @param {Function} onConfirm - Function to execute on confirmation
 * @returns {Promise}
 */
export function confirmLogout(onConfirm) {
    return confirmAction({
        title: 'Yakin ingin logout?',
        text: 'Anda akan keluar dari sesi ini.',
        confirmButtonText: 'Ya, Logout',
        confirmButtonColor: '#d33',
        onConfirm: onConfirm
    });
}

/**
 * Confirm delete action
 * @param {string} itemName - Name of item being deleted (optional)
 * @param {Function} onConfirm - Function to execute on confirmation
 * @returns {Promise}
 */
export function confirmDelete(itemName = '', onConfirm) {
    return confirmAction({
        title: 'Yakin ingin menghapus data ini?',
        text: itemName ? `Data ${itemName} yang dihapus tidak dapat dikembalikan.` : 'Data yang dihapus tidak dapat dikembalikan.',
        confirmButtonText: 'Ya, Hapus',
        confirmButtonColor: '#d33',
        onConfirm: onConfirm
    });
}

/**
 * Confirm update/edit action
 * @param {Function} onConfirm - Function to execute on confirmation
 * @returns {Promise}
 */
export function confirmUpdate(onConfirm) {
    return confirmAction({
        title: 'Yakin ingin menyimpan perubahan ini?',
        text: 'Pastikan semua data yang diisi sudah benar.',
        confirmButtonText: 'Ya, Simpan',
        confirmButtonColor: '#3085d6',
        onConfirm: onConfirm
    });
}

/**
 * Confirm create action
 * @param {string} itemName - Name of item being created (optional)
 * @param {Function} onConfirm - Function to execute on confirmation
 * @returns {Promise}
 */
export function confirmCreate(itemName = '', onConfirm) {
    return confirmAction({
        title: 'Yakin ingin menambahkan data ini?',
        text: itemName ? `Data ${itemName} akan ditambahkan ke sistem.` : 'Data akan ditambahkan ke sistem.',
        confirmButtonText: 'Ya, Tambah',
        confirmButtonColor: '#28a745',
        onConfirm: onConfirm
    });
}

/**
 * Confirm reset/clear action
 * @param {Function} onConfirm - Function to execute on confirmation
 * @returns {Promise}
 */
export function confirmReset(onConfirm) {
    return confirmAction({
        title: 'Yakin ingin mereset data?',
        text: 'Semua perubahan yang belum disimpan akan hilang.',
        confirmButtonText: 'Ya, Reset',
        confirmButtonColor: '#ffc107',
        onConfirm: onConfirm
    });
}

/**
 * Confirm end session action (for guru piket)
 * @param {Function} onConfirm - Function to execute on confirmation
 * @returns {Promise}
 */
export function confirmEndSession(onConfirm) {
    return confirmAction({
        title: 'Yakin ingin mengakhiri sesi piket?',
        text: 'Sesi piket akan berakhir dan tidak dapat dibuka kembali.',
        confirmButtonText: 'Ya, Akhiri Sesi',
        confirmButtonColor: '#d33',
        onConfirm: onConfirm
    });
}

/**
 * Setup confirmation for form submission
 * @param {string|HTMLElement} formSelector - Form selector or element
 * @param {Object} options - Confirmation options
 */
export function setupFormConfirmation(formSelector, options = {}) {
    const form = typeof formSelector === 'string' 
        ? document.querySelector(formSelector) 
        : formSelector;
    
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        confirmAction(options).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
}

/**
 * Setup confirmation for delete buttons
 * @param {string} buttonSelector - Selector for delete buttons
 */
export function setupDeleteConfirmations(buttonSelector = '.btn-delete, .delete-btn, [data-action="delete"]') {
    document.querySelectorAll(buttonSelector).forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemName = this.dataset.itemName || '';
            const deleteUrl = this.dataset.deleteUrl || this.href;
            const form = this.closest('form');
            
            confirmDelete(itemName, () => {
                if (form) {
                    form.submit();
                } else if (deleteUrl) {
                    window.location.href = deleteUrl;
                }
            });
        });
    });
}

// Make functions globally available
window.confirmAction = confirmAction;
window.showSuccess = showSuccess;
window.showError = showError;
window.confirmLogout = confirmLogout;
window.confirmDelete = confirmDelete;
window.confirmUpdate = confirmUpdate;
window.confirmCreate = confirmCreate;
window.confirmReset = confirmReset;
window.confirmEndSession = confirmEndSession;
window.setupFormConfirmation = setupFormConfirmation;
window.setupDeleteConfirmations = setupDeleteConfirmations;