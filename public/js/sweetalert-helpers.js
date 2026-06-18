/**
 * SweetAlert2 Helper Functions
 */

if (typeof Swal === 'undefined') {
    console.error('SweetAlert2 is not loaded. Please include SweetAlert2 library.');
}

function showSuccess(title, message, callback) {
    return Swal.fire({
        icon: 'success',
        title: title || 'Success!',
        text: message || '',
        confirmButtonColor: document.querySelector('meta[name="brand-color"]')?.content || '#940000',
        timer: 3000,
        timerProgressBar: true,
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

function showError(title, message, callback) {
    return Swal.fire({
        icon: 'error',
        title: title || 'Error!',
        text: message || 'An error occurred',
        confirmButtonColor: '#dc3545',
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

function showWarning(title, message, callback) {
    return Swal.fire({
        icon: 'warning',
        title: title || 'Warning!',
        text: message || '',
        confirmButtonColor: '#ffc107',
        confirmButtonText: 'OK',
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

function showInfo(title, message, callback) {
    return Swal.fire({
        icon: 'info',
        title: title || 'Information',
        text: message || '',
        confirmButtonColor: '#0dcaf0',
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

function showConfirm(title, message, confirmText, cancelText, callback) {
    return Swal.fire({
        title: title || 'Are you sure?',
        text: message || "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmText || 'Yes, do it!',
        cancelButtonText: cancelText || 'Cancel',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed && callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

function showDeleteConfirm(title, message, callback) {
    return showConfirm(
        title || 'Delete?',
        message || 'This action cannot be undone!',
        'Yes, delete it!',
        'Cancel',
        callback
    );
}

function showLoading(title, text) {
    Swal.fire({
        title: title || 'Loading...',
        text: text || 'Please wait',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
}

function closeAlert() {
    Swal.close();
}

function showToast(icon, title, timer) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer || 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });

    return Toast.fire({
        icon: icon || 'success',
        title: title || 'Action completed',
    });
}

function showSuccessToast(title) {
    return showToast('success', title);
}

function showErrorToast(title) {
    return showToast('error', title);
}

window.SwalHelpers = {
    success: showSuccess,
    error: showError,
    warning: showWarning,
    info: showInfo,
    confirm: showConfirm,
    deleteConfirm: showDeleteConfirm,
    loading: showLoading,
    close: closeAlert,
    toast: showToast,
    successToast: showSuccessToast,
    errorToast: showErrorToast,
};
