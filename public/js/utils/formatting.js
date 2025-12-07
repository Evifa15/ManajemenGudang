/**
 * --------------------------------------------------------------------------
 * Formatting Utilities
 * Kumpulan fungsi bantu untuk format Angka, Mata Uang, dan Tanggal.
 * --------------------------------------------------------------------------
 */

const Formatting = {
    
    /**
     * Format ke Mata Uang Rupiah
     * Contoh: 10000 -> "Rp 10.000"
     */
    formatRupiah: (number) => {
        if (number === null || number === undefined) return "Rp 0";
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    },

    /**
     * Format Angka Biasa (Pemisah Ribuan)
     * Contoh: 1500 -> "1.500"
     */
    formatNumber: (number) => {
        if (number === null || number === undefined) return "0";
        return new Intl.NumberFormat('id-ID').format(number);
    },

    /**
     * Format Tanggal Lengkap (Indonesia)
     * Contoh: "2025-12-25" -> "25 Desember 2025"
     */
    formatDateIndo: (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        // Validasi tanggal valid
        if (isNaN(date.getTime())) return dateString;

        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    },

    /**
     * Format Tanggal Pendek
     * Contoh: "2025-12-25" -> "25/12/2025"
     */
    formatDateShort: (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;

        return date.toLocaleDateString('id-ID');
    },

    /**
     * Format Tanggal & Waktu
     * Contoh: "2025-12-25 14:30:00" -> "25/12/2025 14.30"
     */
    formatDateTime: (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;

        return date.toLocaleString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).replace(/\./g, ':'); // Opsional: ganti pemisah waktu jadi titik dua
    },

    /**
     * Helper untuk membersihkan string input HTML (XSS Prevention sederhana)
     * Berguna saat menampilkan data user ke innerHTML
     */
    escapeHtml: (text) => {
        if (!text) return "";
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
};

// Export ke Global Scope agar bisa dipanggil (misal: WMSFormatting.formatRupiah(5000))
window.WMSFormatting = Formatting;