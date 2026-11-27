<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - PT Technodrives</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon/favicon-dark.ico') }}">

    <!-- Google Fonts: Poppins & Fira Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Error Page Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/css/errors.css') }}">
</head>
<body>
    @php
        $homeUrl = auth()->check()
            ? route('dashboard')
            : (request()->hasCookie(config('session.cookie')) ? url('/') : route('login'));
    @endphp

    <main class="error-stage" role="main">
        <div class="error-container">
            <!-- Raccoon Visual Asset -->
            <div class="error-visual">
                <div class="error-visual-frame">
                    <img
                        src="@yield('image')"
                        alt="@yield('image_alt')"
                        class="error-visual-img"
                        decoding="async"
                    >
                </div>
            </div>

            <!-- Native HTML Error Copy & Accessible Action -->
            <section class="error-content" aria-labelledby="error-page-heading">
                <span class="error-brand-tag">
                    <span class="error-brand-dot" aria-hidden="true"></span>
                    <span>@yield('badge')</span>
                </span>

                <h1 id="error-page-heading" class="error-title">
                    <span class="error-title-code">@yield('code')</span>
                    <span>@yield('heading_text')</span>
                </h1>

                <p class="error-description">
                    @yield('message')
                </p>

                <div class="error-actions">
                    <a href="{{ $homeUrl }}" class="error-btn-home">
                        <svg class="error-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                            <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>

                <p class="error-footer-note">
                    PT Technodrives Indonesia &bull; Sistem Cash Advance &amp; Settlement
                </p>
            </section>
        </div>
    </main>
</body>
</html>
