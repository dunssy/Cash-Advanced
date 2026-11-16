<!-- Third Party Plugin: Popper.js & Bootstrap 5.3 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- Third Party Plugin: OverlayScrollbars -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>

<!-- Required Plugin: AdminLTE v4 (Bootstrap 5) -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/js/adminlte.min.js" crossorigin="anonymous"></script>

<!-- Configure OverlayScrollbars for AdminLTE 4 Sidebar -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector('.sidebar-wrapper');
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
            OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: 'os-theme-light',
                    autoHide: 'leave',
                    clickScroll: true,
                },
            });
        }
        
        // Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<!-- Dedicated Application Scripts -->
<script src="{{ asset('assets/js/app-scripts.js') }}"></script>

<!-- Dedicated Application Loader Script -->
<script src="{{ asset('assets/js/loader.js') }}"
    data-logo="{{ asset('assets/images/logo.png') }}"
    data-just-logged-in="{{ session('login_loader') || session('just_logged_in') ? 'true' : 'false' }}"
    data-user-name="{{ auth()->user()?->name }}"
    data-user-role="{{ auth()->user()?->role }}"></script>

@stack('scripts')
