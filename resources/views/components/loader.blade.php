@php
    $isJustLoggedIn = $isJustLoggedIn ?? (session('login_loader') || session('just_logged_in'));
@endphp

<!-- PT Technodrives Load Loader Component (Assets Terpisah di public/assets) -->
<link rel="stylesheet" href="{{ asset('assets/css/loader.css') }}">
<script src="{{ asset('assets/js/loader.js') }}"
    data-logo="{{ asset('assets/images/logo.png') }}"
    data-just-logged-in="{{ $isJustLoggedIn ? 'true' : 'false' }}"
    data-user-name="{{ auth()->check() ? auth()->user()->name : '' }}"
    data-user-role="{{ auth()->check() ? auth()->user()->role : '' }}"></script>
