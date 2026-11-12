/**
 * PT Technodrives - Cash Advance System
 * Login Page Scripts & Submit Handling
 */

// Initialize Lucide Icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Handle Login Button Submit State
(function() {
    var form = document.getElementById('login-form');
    var btn = document.getElementById('btn-login-submit');

    if (form && btn) {
        form.addEventListener('submit', function() {
            // Ubah tombol ke state memproses
            btn.classList.add('opacity-80', 'cursor-wait');
            btn.setAttribute('disabled', 'disabled');
            
            var btnIcon = document.getElementById('btn-icon');
            var btnText = document.getElementById('btn-text');
            if (btnIcon) {
                btnIcon.innerHTML = `<svg class="animate-spin h-4 w-4 text-white inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>`;
            }
            if (btnText) {
                btnText.textContent = 'Memverifikasi Akun...';
            }
        });
    }
})();
