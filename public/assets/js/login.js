/**
 * PT Technodrives - Cash Advance System
 * Login Page Scripts & Submit Handling
 */

// Initialize Lucide Icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Handle Login Button Submit State (Terintegrasi dengan loader.js agar tidak bentrok)
(function() {
    var form = document.getElementById('login-form');
    var btn = document.getElementById('btn-login-submit');
    var btnIcon = document.getElementById('btn-icon');
    var btnText = document.getElementById('btn-text');

    if (!form || !btn) {
        return;
    }

    var originalIconHtml = btnIcon ? btnIcon.innerHTML : '';
    var originalBtnText = btnText ? btnText.textContent : '';

    function resetSubmitButton() {
        btn.classList.remove('opacity-80', 'cursor-wait');
        btn.removeAttribute('disabled');
        if (btnIcon) {
            btnIcon.innerHTML = originalIconHtml;
        }
        if (btnText) {
            btnText.textContent = originalBtnText;
        }
    }

    form.addEventListener('submit', function(event) {
        if (btn.disabled) {
            event.preventDefault();
            return;
        }

        // Cegah double submit
        btn.classList.add('opacity-80', 'cursor-wait');
        btn.setAttribute('disabled', 'disabled');

        // Gunakan spinner tombol hanya sebagai fallback jika global overlay loader tidak tersedia
        if (typeof window.showLoader !== 'function') {
            if (btnIcon) {
                btnIcon.innerHTML = `<svg class="animate-spin h-4 w-4 text-white inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>`;
            }
            if (btnText) {
                btnText.textContent = 'Memverifikasi Akun...';
            }
        }
    });

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            resetSubmitButton();
        }
    });
})();
