/**
 * PT Technodrives - Cash Advance & Settlement System
 * Dedicated Load Loader & Preloader Controller (Terpisah dari Blade)
 */

(function(window, document) {
    'use strict';

    // 1. Baca Konfigurasi dari Script Tag atau Window Config
    var currentScript = document.currentScript;
    var config = window.AppLoaderConfig || {};

    if (currentScript) {
        if (!config.logo && currentScript.getAttribute('data-logo')) {
            config.logo = currentScript.getAttribute('data-logo');
        }
        if (config.justLoggedIn === undefined && currentScript.hasAttribute('data-just-logged-in')) {
            config.justLoggedIn = currentScript.getAttribute('data-just-logged-in') === 'true';
        }
        if (!config.userName && currentScript.getAttribute('data-user-name')) {
            config.userName = currentScript.getAttribute('data-user-name');
        }
        if (!config.userRole && currentScript.getAttribute('data-user-role')) {
            config.userRole = currentScript.getAttribute('data-user-role');
        }
        if (config.autoDismiss === undefined && currentScript.hasAttribute('data-auto-dismiss')) {
            config.autoDismiss = currentScript.getAttribute('data-auto-dismiss') !== 'false';
        }
        if (config.showOnLoad === undefined && currentScript.hasAttribute('data-show-on-load')) {
            config.showOnLoad = currentScript.getAttribute('data-show-on-load') === 'true';
        }
    }

    var logoUrl = config.logo || '/assets/images/logo.png';
    var isJustLoggedIn = Boolean(config.justLoggedIn);
    var userName = config.userName || '';
    var userRole = config.userRole || '';
    var shouldAutoDismiss = config.autoDismiss !== false;
    var showOnLoad = config.showOnLoad !== undefined ? config.showOnLoad : true;

    // 2. Fungsi Konstruksi Template HTML Loader
    function generateLoaderHTML(options) {
        options = options || {};
        var isLogin = options.isJustLoggedIn !== undefined ? options.isJustLoggedIn : isJustLoggedIn;
        var title = options.title;
        var subtext = options.subtext;

        var badgeHTML = '';
        if (isLogin) {
            badgeHTML = `
                <div class="app-loader-badge">
                    <svg class="w-3.5 h-3.5 inline-block mr-1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span>Otentikasi Berhasil</span>
                </div>
            `;
            if (!title) {
                title = userName ? `Selamat Datang, ${userName}!` : 'Selamat Datang!';
            }
            if (!subtext) {
                var roleText = userRole ? `<span class="app-loader-role-highlight">${userRole}</span>` : 'Sistem';
                subtext = `Menyiapkan modul ${roleText} & data operasional PT. Technodrives...`;
            }
        } else {
            if (!title) title = 'Memuat Sistem Cash Advance';
            if (!subtext) subtext = 'PT. Technodrives Indonesia &bull; Menyiapkan data...';
        }

        return `
            <div class="app-loader-card">
                <div class="app-loader-logo-wrap">
                    <div class="app-loader-halo"></div>
                    <div class="app-loader-logo-box">
                        <img src="${logoUrl}" alt="PT Technodrives" class="app-loader-logo-img">
                    </div>
                </div>

                <div class="app-loader-spinner-wrap">
                    <div class="app-loader-spinner-outer"></div>
                    <div class="app-loader-spinner-inner"></div>
                </div>

                ${badgeHTML}
                <h3 class="app-loader-title">${title}</h3>
                <p class="app-loader-subtext">${subtext}</p>

                <div class="app-loader-progress-track">
                    <div class="app-loader-progress-bar"></div>
                </div>

                <span class="app-loader-tagline">
                    Expert Electrical Solutions & Testing
                </span>
            </div>
        `;
    }

    // 3. Render atau Pasang Loader ke DOM
    var loaderEl = document.getElementById('app-loader');

    function ensureLoaderElement() {
        if (!loaderEl) {
            loaderEl = document.getElementById('app-loader');
        }
        if (!loaderEl) {
            loaderEl = document.createElement('div');
            loaderEl.id = 'app-loader';
            loaderEl.className = 'app-loader-overlay';
            loaderEl.innerHTML = generateLoaderHTML();
            
            if (!showOnLoad) {
                loaderEl.classList.add('loader-hidden');
                loaderEl.style.display = 'none';
            }

            if (document.body) {
                document.body.insertAdjacentElement('afterbegin', loaderEl);
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    document.body.insertAdjacentElement('afterbegin', loaderEl);
                });
            }
        }
        return loaderEl;
    }

    // Buat loader bila belum ada
    ensureLoaderElement();

    // 4. Logika Dismiss / Tutup Loader
    function hideLoader() {
        if (!loaderEl) return;
        loaderEl.classList.add('loader-hidden');
        setTimeout(function() {
            if (loaderEl && loaderEl.classList.contains('loader-hidden')) {
                loaderEl.style.display = 'none';
            }
        }, 500);
    }

    function showLoader(options) {
        ensureLoaderElement();
        if (options) {
            loaderEl.innerHTML = generateLoaderHTML(options);
        }
        loaderEl.style.display = 'flex';
        // Force reflow agar transisi CSS berjalan mulus
        void loaderEl.offsetWidth;
        loaderEl.classList.remove('loader-hidden');
    }

    // Expose ke Global Window API
    window.showLoader = showLoader;
    window.hideLoader = hideLoader;
    window.showAppLoader = showLoader; // Kompatibilitas alias

    // 5. Auto Dismiss Saat Halaman Siap (Hanya jika showOnLoad true)
    if (showOnLoad && shouldAutoDismiss) {
        var dismissDelay = isJustLoggedIn ? 950 : 350;

        function scheduleDismiss() {
            setTimeout(function() {
                hideLoader();
            }, dismissDelay);
        }

        if (document.readyState === 'complete') {
            scheduleDismiss();
        } else {
            window.addEventListener('load', scheduleDismiss);
            // Fallback timeout jika CDN / aset lambat
            setTimeout(scheduleDismiss, 2500);
        }
    }

    // 6. Otomatisasi Listener pada Form Login & Form dengan data-show-loader="true"
    document.addEventListener('DOMContentLoaded', function() {
        var loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                showLoader({
                    isJustLoggedIn: false,
                    title: 'Sedang Masuk...',
                    subtext: 'Menghubungkan ke Sistem Cash Advance PT. Technodrives...'
                });
            });
        }

        document.querySelectorAll('form[data-show-loader="true"]').forEach(function(form) {
            form.addEventListener('submit', function() {
                var title = form.getAttribute('data-loader-title') || 'Sedang Memproses...';
                var subtext = form.getAttribute('data-loader-subtext') || 'Mohon tunggu sebentar...';
                showLoader({ isJustLoggedIn: false, title: title, subtext: subtext });
            });
        });
    });

})(window, document);
