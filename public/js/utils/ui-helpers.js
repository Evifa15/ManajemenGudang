/**
 * --------------------------------------------------------------------------
 * UI Helpers
 * Fungsi bantuan untuk elemen antarmuka (Pagination, SweetAlert, dll).
 * --------------------------------------------------------------------------
 */

const UIHelpers = {

    /**
     * Render Pagination (Universal)
     */
    renderPagination: (container, totalPages, currentPage, callback) => {
        if (!container) return;
        currentPage = parseInt(currentPage) || 1;
        totalPages = parseInt(totalPages) || 1;
        if (totalPages < 1) totalPages = 1;

        let html = `<span class="pagination-info">Halaman ${currentPage} dari ${totalPages}</span>`;
        html += '<nav><ul class="pagination">';

        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">&larr; Prev</a></li>`;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (totalPages <= 5) { startPage = 1; endPage = totalPages; }
        else {
            if (currentPage <= 3) { startPage = 1; endPage = 5; }
            else if (currentPage + 2 >= totalPages) { startPage = totalPages - 4; endPage = totalPages; }
        }

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPage ? 'active' : '';
            html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }

        const nextDisabled = currentPage === totalPages ? 'disabled' : '';
        html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next &rarr;</a></li>`;
        html += '</ul></nav>';
        
        container.innerHTML = html;
        container.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const parentLi = e.target.closest('li');
                if (!parentLi.classList.contains('disabled') && !parentLi.classList.contains('active')) {
                    callback(parseInt(e.target.dataset.page));
                }
            });
        });
    },

    /**
     * Tampilkan Dialog Konfirmasi Standar (Returning Promise)
     * Digunakan oleh Hapus Tunggal & Hapus Massal agar styling SAMA.
     */
    showConfirm: (title = 'Yakin Hapus?', text = "Data yang dihapus tidak bisa dikembalikan!") => {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            // Warna dihandle oleh CSS Global (3.8), tapi kita set default di sini untuk backup
            iconColor: '#f8c21a', 
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            focusCancel: true
        });
    },

    /**
     * Helper Cepat untuk Hapus Tunggal (Redirect URL)
     */
    confirmDelete: (url, title, text) => {
        UIHelpers.showConfirm(title, text).then((result) => {
            if (result.isConfirmed) {
                UIHelpers.showLoading('Sedang Menghapus...');
                window.location.href = url;
            }
        });
    },

    /**
     * Tampilkan Loading Spinner
     */
    showLoading: (title = 'Memproses...', text = 'Mohon tunggu sebentar.') => {
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });
    },

    /**
     * Tampilkan Toast Notification
     */
    showToast: (icon, title) => {
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false,
            timer: 3000, timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        Toast.fire({ icon: icon, title: title });
    }
};

window.WMSUI = UIHelpers;