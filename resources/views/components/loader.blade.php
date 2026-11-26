@php
    $isJustLoggedIn = $isJustLoggedIn ?? (session('login_loader') || session('just_logged_in'));
    $showOnLoad = $showOnLoad ?? true;
    $autoDismiss = $autoDismiss ?? true;
@endphp

<!-- PT Technodrives Load Loader Component (Assets Terpisah di public/assets) -->
@once('app-loader-css')
<link rel="stylesheet" href="{{ asset('assets/css/loader.css') }}">
@endonce

@once('app-loader-js')
<script src="{{ asset('assets/js/loader.js') }}"
    data-logo="{{ asset('assets/images/logo.png') }}"
    data-show-on-load="{{ $showOnLoad ? 'true' : 'false' }}"
    data-auto-dismiss="{{ $autoDismiss ? 'true' : 'false' }}"
    data-just-logged-in="{{ $isJustLoggedIn ? 'true' : 'false' }}"
    data-user-name="{{ auth()->user()?->name ?? '' }}"
    data-user-role="{{ auth()->user()?->role ?? '' }}"></script>
@endonce
