<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Sistem Cash Advance & Settlement') - PT Technodrives</title>

<!-- Google Fonts: Poppins (headings/numbers) & Fira Sans (body/labels) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">

<!-- Third Party Plugin: OverlayScrollbars (Required for AdminLTE v4 Sidebar) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css" crossorigin="anonymous">

<!-- Bootstrap 5.3 & Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous">

<!-- AdminLTE v4 CSS (Official Bootstrap 5 Admin Template) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/css/adminlte.min.css" crossorigin="anonymous">

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Tailwind CSS CDN (Preflight Disabled so it harmonizes perfectly with Bootstrap 5/AdminLTE) -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="{{ asset('assets/js/tailwind-config.js') }}"></script>

<!-- Alpine.js (Ensures interactive modals in detail views remain fast and reactive) -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

<!-- Custom Application Stylesheet (PT Technodrives Design Tokens & AdminLTE Theme) -->
<link rel="stylesheet" href="{{ asset('assets/css/app-custom.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/loader.css') }}">

@stack('styles')
